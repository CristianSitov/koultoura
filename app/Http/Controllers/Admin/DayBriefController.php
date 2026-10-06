<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\DayBrief;
use App\Models\ProgrammeDay;
use App\Models\Registration;
use App\Support\Delivery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

/*
 * The email the evening before a day of the symposium: its preview, who it
 * goes to, and the sending — one email per request.
 *
 * The screen drives the sending, one call per person with a pause between,
 * for the same reason the old agenda did: the mail provider takes a couple a
 * second and a request lives thirty seconds, so a loop over eighty people on
 * the server would not finish. Each send is recorded, so a run that stops
 * halfway resumes where it left off and nobody gets it twice — with Resend's
 * id, so "Check delivery" can ask what became of it. A send that failed is
 * kept too, and is tried again on the next run.
 */
class DayBriefController extends Controller
{
    /** The email as a participant would get it. Nothing sends from here. */
    public function preview(ProgrammeDay $day, string $locale): string
    {
        return (new DayBrief($day, $locale, auth()->user()->name ?? null))->render();
    }

    /** Who it goes to, who has had it, and who is still to get it. */
    public function status(ProgrammeDay $day): JsonResponse
    {
        $recipients = self::recipients($day);
        $sent = $this->sent($day);
        $pending = $recipients->reject(fn (Registration $r) => $sent->has(self::key($r->email)));
        $rows = $this->rows($day);

        return response()->json([
            // The words, in the shape the reminder window edits (it is the same window).
            'reminder' => $day->emailText(),
            // A day no registration covers (the workshop Saturday) has no list
            // of its own: its people are the workshops' and tours' bookings.
            'covered' => self::number($day) !== null,
            'total' => $recipients->count(),
            'sent' => $recipients->count() - $pending->count(),
            'languages' => $recipients->countBy('locale'),
            // How the sends stand, by Resend's last word — failed ones included.
            'statuses' => $rows->countBy('status'),
            // Still worth looking up at Resend: has an id, not at a final state.
            'checkable' => $rows->filter(fn ($row) => $row->resend_id && ! in_array($row->status, Delivery::FINAL, true))
                ->pluck('id')->values(),
            'pending' => $pending->map(fn (Registration $r) => [
                'id' => $r->id,
                'name' => $r->name,
                'email' => $r->email,
            ])->values(),
        ]);
    }

    /** The office's words for the day: subject and text, English and Romanian. */
    public function save(Request $request, ProgrammeDay $day): JsonResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'subject_ro' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'body_ro' => ['nullable', 'string'],
        ]);

        $day->update([
            'email_subject' => $data['subject'],
            'email_subject_ro' => $data['subject_ro'] ?? null,
            'email_body' => $data['body'],
            'email_body_ro' => $data['body_ro'] ?? null,
        ]);

        return $this->status($day);
    }

    public function send(ProgrammeDay $day, Registration $registration): JsonResponse
    {
        $email = self::key($registration->email);

        if ($this->sent($day)->has($email)) {
            return response()->json(['ok' => true, 'skipped' => true]);
        }

        // One row per day and address: a failed try is overwritten by the retry.
        $record = fn (array $fields) => DB::connection('wcm_2026')->table('day_brief_sends')->updateOrInsert(
            ['programme_day_id' => $day->id, 'email' => $email],
            $fields + ['registration_id' => $registration->id, 'sent_at' => now(), 'checked_at' => null],
        );

        try {
            $sent = Mail::to($registration->email)->send(new DayBrief($day, $registration->locale ?: 'en', $registration->name));
        } catch (Throwable $e) {
            report($e);
            $record(['status' => 'failed', 'error' => $e->getMessage(), 'resend_id' => null]);

            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }

        $id = Delivery::id($sent);
        $record(['status' => Delivery::initial($id), 'error' => null, 'resend_id' => $id]);

        return response()->json(['ok' => true]);
    }

    /** What Resend last saw of one day email: delivered, bounced, complained… */
    public function check(ProgrammeDay $day, int $send): JsonResponse
    {
        $query = DB::connection('wcm_2026')->table('day_brief_sends')->where('programme_day_id', $day->id)->where('id', $send);
        $row = $query->first() ?? abort(404);

        if (! $row->resend_id) {
            return response()->json(['ok' => true, 'status' => $row->status]);
        }

        try {
            $status = Delivery::lookup($row->resend_id) ?? $row->status;
            $query->update(['status' => $status, 'checked_at' => now()]);
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }

        return response()->json(['ok' => true, 'status' => $status]);
    }

    /** To the person signed in, in the language asked for. Not recorded. */
    public function test(ProgrammeDay $day, string $locale): JsonResponse
    {
        $user = auth()->user();

        try {
            Mail::to($user->email)->send(new DayBrief($day, $locale, $user->name));
        } catch (Throwable $e) {
            report($e);

            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }

        return response()->json(['ok' => true, 'email' => $user->email]);
    }

    /**
     * Everyone registered for the day, confirmed or not — some forget to
     * confirm, and they are coming all the same. Once per address: a second
     * registration from the same inbox is the same person.
     *
     * @return Collection<int, Registration>
     */
    public static function recipients(ProgrammeDay $day): Collection
    {
        $n = self::number($day);

        if ($n === null) {
            return collect();
        }

        return Registration::orderBy('id')->get()
            ->filter(fn (Registration $r) => in_array($n, array_map('intval', (array) $r->days), true))
            ->unique(fn (Registration $r) => self::key($r->email))
            ->values();
    }

    /**
     * The day's number in a registration (1–3 for 7–9 October), or null for
     * a day registration does not cover. Days count in programme order.
     */
    public static function number(ProgrammeDay $day): ?int
    {
        $n = DayBrief::dayNumber($day);

        return in_array($n, Registration::DAYS, true) ? $n : null;
    }

    /** The addresses that have it — a failed try does not count. */
    private function sent(ProgrammeDay $day): Collection
    {
        return $this->rows($day)->where('status', '!=', 'failed')->pluck('sent_at', 'email');
    }

    private function rows(ProgrammeDay $day): Collection
    {
        return DB::connection('wcm_2026')->table('day_brief_sends')->where('programme_day_id', $day->id)->get();
    }

    private static function key(?string $email): string
    {
        return mb_strtolower(trim((string) $email));
    }
}
