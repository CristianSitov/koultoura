<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\DayBrief;
use App\Models\ProgrammeDay;
use App\Models\Registration;
use Illuminate\Http\JsonResponse;
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
 * halfway resumes where it left off and nobody gets it twice.
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

        return response()->json([
            // A day no registration covers (the workshop Saturday) has no list
            // of its own: its people are the workshops' and tours' bookings.
            'covered' => self::number($day) !== null,
            'total' => $recipients->count(),
            'sent' => $recipients->count() - $pending->count(),
            'languages' => $recipients->countBy('locale'),
            'pending' => $pending->map(fn (Registration $r) => [
                'id' => $r->id,
                'name' => $r->name,
                'email' => $r->email,
            ])->values(),
        ]);
    }

    public function send(ProgrammeDay $day, Registration $registration): JsonResponse
    {
        $email = self::key($registration->email);

        if ($this->sent($day)->has($email)) {
            return response()->json(['ok' => true, 'skipped' => true]);
        }

        try {
            Mail::to($registration->email)->send(new DayBrief($day, $registration->locale ?: 'en', $registration->name));
        } catch (Throwable $e) {
            report($e);

            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }

        DB::connection('wcm_2026')->table('day_brief_sends')->insertOrIgnore([
            'programme_day_id' => $day->id,
            'registration_id' => $registration->id,
            'email' => $email,
            'sent_at' => now(),
        ]);

        return response()->json(['ok' => true]);
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

    private function sent(ProgrammeDay $day): Collection
    {
        return DB::connection('wcm_2026')->table('day_brief_sends')
            ->where('programme_day_id', $day->id)
            ->pluck('sent_at', 'email');
    }

    private static function key(?string $email): string
    {
        return mb_strtolower(trim((string) $email));
    }
}
