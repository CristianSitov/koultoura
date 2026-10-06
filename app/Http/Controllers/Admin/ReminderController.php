<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ReminderEmail;
use App\Models\Registration;
use App\Models\Reminder;
use App\Models\ReminderSend;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Resend;
use Throwable;

/*
 * The reminder email to everyone registered, from the Registrations page:
 * write it, send yourself a test, send it to everyone, then look up how each
 * one fared at Resend.
 *
 * Sending and checking are both driven by the screen, one call per person
 * with a pause between: Resend takes a couple of requests a second — looking
 * an email up counts too — and a request here lives thirty seconds.
 */
class ReminderController extends Controller
{
    /** The reminder being worked on, and where its sending stands. */
    public function show(): JsonResponse
    {
        return response()->json($this->state(Reminder::current()));
    }

    public function save(Request $request): JsonResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'subject_ro' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'body_ro' => ['nullable', 'string'],
        ]);

        $reminder = Reminder::current();
        $reminder->update($data);

        return response()->json($this->state($reminder));
    }

    /** A new reminder, starting from the last one's text, with nobody sent. */
    public function fresh(): JsonResponse
    {
        $last = Reminder::current();
        $reminder = Reminder::create($last->only(['subject', 'subject_ro', 'body', 'body_ro']));

        return response()->json($this->state($reminder));
    }

    /** To the person signed in, in the language asked for. Not recorded. */
    public function test(string $locale): JsonResponse
    {
        $user = auth()->user();

        try {
            Mail::to($user->email)->send(new ReminderEmail(Reminder::current(), $locale, $user->name ?? ''));
        } catch (Throwable $e) {
            report($e);

            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }

        return response()->json(['ok' => true, 'email' => $user->email]);
    }

    /** One person. Already sent for this reminder: skipped, never twice. */
    public function send(Registration $registration): JsonResponse
    {
        $reminder = Reminder::current();

        if ($reminder->sends()->where('registration_id', $registration->id)->where('status', '!=', 'failed')->exists()) {
            return response()->json(['ok' => true, 'skipped' => true]);
        }

        // One row per person and reminder: a failed try is overwritten by the retry.
        $record = fn (array $fields) => ReminderSend::updateOrCreate(
            ['reminder_id' => $reminder->id, 'registration_id' => $registration->id],
            $fields + ['email' => $registration->email, 'sent_at' => now(), 'checked_at' => null],
        );

        try {
            $sent = Mail::to($registration->email)->send(
                new ReminderEmail($reminder, $registration->locale === 'ro' ? 'ro' : 'en', $registration->name, (array) $registration->days)
            );
        } catch (Throwable $e) {
            Log::error('2026 reminder failed', ['registration' => $registration->id, 'error' => $e->getMessage()]);
            $record(['status' => 'failed', 'error' => $e->getMessage(), 'resend_id' => null]);

            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }

        // Laravel's Resend transport stamps the id Resend gave the email on
        // it. Mail that is only logged (locally) has none to look up.
        $id = $sent?->getOriginalMessage()->getHeaders()->get('X-Resend-Email-ID')?->getBodyAsString();
        $record(['status' => $id ? 'sent' : 'logged', 'resend_id' => $id, 'error' => null]);

        return response()->json(['ok' => true]);
    }

    /** What Resend last saw of one email: delivered, bounced, complained… */
    public function check(ReminderSend $send): JsonResponse
    {
        if (! $send->resend_id) {
            return response()->json(['ok' => true, 'status' => $send->status]);
        }

        try {
            $email = Resend::client(config('services.resend.key'))->emails->get($send->resend_id);
            $send->update(['status' => $email->last_event ?: $send->status, 'checked_at' => now()]);
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }

        return response()->json(['ok' => true, 'status' => $send->status]);
    }

    private function state(Reminder $reminder): array
    {
        $sends = $reminder->sends()->get();
        // A failed try does not count as having it: it is offered again.
        $sent = $sends->where('status', '!=', 'failed')->pluck('registration_id')->flip();

        return [
            'reminder' => $reminder->only(['id', 'subject', 'subject_ro', 'body', 'body_ro']),
            'total' => Registration::count(),
            'sent' => $sent->count(),
            // How the sent ones stand, by Resend's last word.
            'statuses' => $sends->countBy('status'),
            // Everyone registered, confirmed or not, who has not had this one.
            'pending' => Registration::orderBy('id')->get(['id', 'name', 'email'])
                ->reject(fn (Registration $r) => $sent->has($r->id))
                ->values(),
            // Still worth looking up at Resend: has an id, not at a final state.
            'checkable' => $sends->filter(fn (ReminderSend $s) => $s->resend_id && ! in_array($s->status, ReminderSend::FINAL, true))
                ->pluck('id')->values(),
        ];
    }
}
