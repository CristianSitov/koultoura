<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Front2026Controller;
use App\Mail\BookingConfirmed;
use App\Mail\RegistrationConfirmation;
use App\Mail\RegistrationConfirmed;
use App\Models\Contribution;
use App\Models\Registration;
use App\Models\Person;
use App\Mail\PlaceConfirmed;
use App\Mail\PlaceInvite;
use App\Mail\SessionReminder;
use App\Models\Session;
use App\Models\SessionBooking;
use App\Models\SessionPlace;
use App\Support\PlaceCalendar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Throwable;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Inertia\Inertia;
use Inertia\Response;

/*
 * Who is coming.
 *
 * Registration is per day. Capped sessions are booked separately and counted
 * separately — a place in a workshop is not the same promise as turning up on
 * the day, and conflating them would make both numbers wrong.
 */
class RegistrationsController extends Controller
{
    /*
     * All four days, because a registration made before the 10th became the
     * workshop day may still carry it and has to render.
     */
    private const DAYS = [1 => '07', 2 => '08', 3 => '09', 4 => '10'];

    /** The three the form offers; the 10th is booked through its workshops. */
    private const REGISTRATION_DAYS = [1, 2, 3];

    public function index(Request $request): Response
    {
        $day = $request->integer('day') ?: null;
        $status = $request->string('status')->toString();

        $registrations = Registration::query()
            // Removed ones are listed only when asked for, to be restored.
            ->when($status === 'removed', fn ($q) => $q->onlyTrashed())
            ->when($day, fn ($q) => $q->whereJsonContains('days', $day))
            ->when($status === 'confirmed', fn ($q) => $q->whereNotNull('confirmed_at'))
            ->when($status === 'waiting', fn ($q) => $q->whereNull('confirmed_at')->where('sent_count', '>', 0))
            // The state worth being able to list: registered, never written to.
            ->when($status === 'unsent', fn ($q) => $q->whereNull('confirmed_at')->where('sent_count', 0))
            ->orderByDesc('id')
            ->get();

        // The current reminder's status for each person, for the Reminder column.
        $reminder = \App\Models\Reminder::latest('id')->first();
        $reminded = $reminder
            ? $reminder->sends()->get(['registration_id', 'status', 'sent_at'])->keyBy('registration_id')
            : collect();

        $twice = Registration::pluck('name')
            ->map(fn ($name) => self::nameKey($name))
            ->countBy()
            ->filter(fn ($n) => $n > 1);

        return Inertia::render('Admin/2026/Registrations', [
            'registrations' => $registrations->map(fn (Registration $r) => [
                'id' => $r->id,
                'name' => $r->name,
                'email' => $r->email,
                'organisation' => $r->organisation,
                'country' => $r->country,
                'phone' => $r->phone,
                'days' => $r->days,
                'workshop_interest' => (bool) $r->workshop_interest,
                'locale' => $r->locale,
                'confirmed' => $r->confirmed_at !== null,
                'created' => $r->created_at->toDateTimeString(),
                'sent_count' => $r->sent_count,
                // Said in words rather than dates: the question this page
                // answers is "how long have they been waiting", and Carbon
                // knows the server's timezone where the browser does not.
                'sent_ago' => $r->last_sent_at?->diffForHumans(),
                'confirmed_ago' => $r->confirmed_at?->diffForHumans(),
                'unsubscribed' => $r->trashed(),
                'unsubscribed_ago' => $r->deleted_at?->diffForHumans(),
                // Same name as another registration — likely registered twice.
                'duplicate' => $twice->has(self::nameKey($r->name)),
                'reminder' => $reminded->get($r->id)?->status,
            ]),
            'filters' => ['day' => $day, 'status' => $status ?: 'all'],
            'counts' => $this->counts(),
            'publicBase' => Front2026Controller::base(),
        ]);
    }

    /**
     * Everyone registered, confirmed or not — the people who may turn up at
     * the door — for the entrance list. Fetched when the list opens, since the
     * page itself may be filtered.
     */
    public function entrance(): JsonResponse
    {
        $registered = Registration::orderBy('id')->get()->map(function (Registration $r) {
            [$first, $last] = $r->nameParts();

            return [
                'id' => $r->id,
                'first_name' => $first,
                'last_name' => $last,
                'organisation' => $r->organisation,
                'days' => $r->days,
            ];
        });

        /*
         * The speakers too, on every day's list, so the door has them. Not
         * editable here — their name is the one on the site, changed in
         * Speakers. One who also registered is already on the list by name.
         */
        $key = fn (string $first, string $last) => Str::lower(Str::ascii(trim($first.' '.$last)));
        $known = $registered->map(fn (array $r) => $key($r['first_name'], $r['last_name']))->flip();

        $speakers = Person::where('published', true)->with('translations')->get()
            ->map(function (Person $p) {
                [$first, $last] = Registration::splitName($p->full_name);
                $text = $p->translate('en') ?? $p->translate('ro');

                return [
                    'id' => 'speaker-'.$p->id,
                    'first_name' => $first,
                    'last_name' => $last,
                    'organisation' => $text?->institution,
                    'days' => Registration::DAYS,
                    'speaker' => true,
                ];
            })
            ->reject(fn (array $s) => $known->has($key($s['first_name'], $s['last_name'])));

        return response()->json($registered->concat($speakers)->values());
    }

    /**
     * A name corrected in the entrance list. The one-line `name` everything
     * else prints (emails, the CSV) is rebuilt from the two halves.
     */
    public function updateName(Request $request, Registration $registration): JsonResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
        ]);

        $first = trim($data['first_name']);
        $last = trim((string) ($data['last_name'] ?? ''));

        $registration->update([
            'first_name' => $first,
            'last_name' => $last,
            'name' => trim($first.' '.$last),
        ]);

        return response()->json(['ok' => true, 'name' => $registration->name]);
    }

    /** Headcounts, which is the question actually being asked of this page. */
    private function counts(): array
    {
        $confirmed = Registration::whereNotNull('confirmed_at');

        $perDay = [];

        foreach (self::REGISTRATION_DAYS as $day) {
            $perDay[$day] = [
                'date' => self::DAYS[$day],
                'all' => Registration::whereJsonContains('days', $day)->count(),
                'confirmed' => (clone $confirmed)->whereJsonContains('days', $day)->count(),
            ];
        }

        return [
            'total' => Registration::count(),
            'confirmed' => Registration::whereNotNull('confirmed_at')->count(),
            'unsent' => Registration::whereNull('confirmed_at')->where('sent_count', 0)->count(),
            'unsubscribed' => Registration::onlyTrashed()->count(),
            'workshopInterest' => Registration::where('workshop_interest', true)->count(),
            'perDay' => $perDay,
            'contributions' => [
                'count' => Contribution::where('status', 'paid')->count(),
                'total' => Contribution::where('status', 'paid')->sum('amount') / 100,
            ],
        ];
    }

    /**
     * Resends the confirmation email, for the ones that never arrived.
     *
     * Not named `resend`: Laravel resolves a bare action string with a
     * case-insensitive class_exists(), and the Resend SDK owns that name.
     */
    public function resendConfirmation(Registration $registration): RedirectResponse
    {
        if ($registration->isConfirmed()) {
            return back()->with('flash', 'Already confirmed — nothing sent.');
        }

        try {
            Mail::to($registration->email)->send(
                new RegistrationConfirmation($registration, $registration->confirmUrl())
            );
        } catch (Throwable $e) {
            Log::error('2026 confirmation resend failed', [
                'registration' => $registration->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['resend' => 'The email did not go out: '.$e->getMessage()]);
        }

        $registration->forceFill([
            'last_sent_at' => now(),
            'sent_count' => $registration->sent_count + 1,
        ])->save();

        return back()->with('flash', 'Confirmation resent to '.$registration->email);
    }

    /*
     * Sends the "you are registered" email again — the one with the venue and
     * the calendar file, which the resend above does not cover: that one asks
     * for a confirmation this person has already given.
     *
     * Wanted the day FABER's address turned out to be wrong in it. Anything of
     * that kind changes what people already have in their inbox and in their
     * calendar, and there was no way to put it right from here.
     */
    public function resendConfirmed(Registration $registration): RedirectResponse
    {
        if (! $registration->isConfirmed()) {
            return back()->with('flash', 'Not confirmed yet — send the confirmation instead.');
        }

        try {
            Mail::to($registration->email)->send(new RegistrationConfirmed($registration));
        } catch (Throwable $e) {
            Log::error('2026 confirmed email resend failed', [
                'registration' => $registration->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['resend' => 'The email did not go out: '.$e->getMessage()]);
        }

        $registration->forceFill([
            'last_sent_at' => now(),
            'sent_count' => $registration->sent_count + 1,
        ])->save();

        return back()->with('flash', 'Details resent to '.$registration->email);
    }

    /**
     * Takes a registration off — a second registration of the same person, or
     * someone who asked. It leaves the counts, the entrance list, the CSV and
     * every email; nothing is sent to them. Restorable.
     */
    public function unsubscribe(Registration $registration): RedirectResponse
    {
        $registration->delete();

        return back()->with('flash', $registration->name.' ('.$registration->email.') removed — find them under “removed” to bring them back.');
    }

    public function restore(int $id): RedirectResponse
    {
        $registration = Registration::onlyTrashed()->findOrFail($id);
        $registration->restore();

        return back()->with('flash', $registration->name.' is registered again.');
    }

    /** A name compared loosely: case, accents and word order aside. */
    private static function nameKey(?string $name): string
    {
        $words = preg_split('/[\s\-]+/u', Str::lower(Str::ascii(trim((string) $name))), -1, PREG_SPLIT_NO_EMPTY);
        sort($words);

        return implode(' ', $words);
    }

    /** Marks an address confirmed by hand, for the ones that never will be. */
    public function confirm(Registration $registration): RedirectResponse
    {
        $registration->confirm();

        return back()->with('flash', $registration->name.' marked as confirmed.');
    }

    public function export(): StreamedResponse
    {
        $rows = Registration::orderBy('id')->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            // Excel reads a UTF-8 CSV as Latin-1 without this, and every
            // Romanian name comes out mangled.
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Name', 'Email', 'Organisation', 'Country', 'Phone', 'Days', 'Workshop interest', 'Language', 'Confirmed', 'Registered']);

            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->name, $r->email, $r->organisation, $r->country, $r->phone,
                    collect($r->days)->map(fn ($d) => self::DAYS[$d] ?? $d)->implode(' '),
                    $r->workshop_interest ? 'yes' : '',
                    $r->locale,
                    $r->confirmed_at?->toDateTimeString() ?? '',
                    $r->created_at->toDateTimeString(),
                ]);
            }

            fclose($out);
        }, 'wcm2026-registrations-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /* ------------------------------------------------------------ bookings */

    /**
     * One workshop's or tour's attendance as a spreadsheet: who holds a place
     * (released ones left out), by last name. An internal one lists its places.
     */
    public function attendanceCsv(Session $session): StreamedResponse
    {
        $title = $session->translate('en')?->title ?? $session->slug ?? 'session';
        $file = 'attendance-'.Str::slug(Str::limit($title, 60, '')).'.csv';

        return response()->streamDownload(function () use ($session) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // so Excel reads the diacritics

            if ($session->internal) {
                fputcsv($out, ['Code', 'Email', 'Status']);
                // Only the places someone was invited to — the rest are unused codes.
                    foreach ($session->places()->whereNotNull('email')->where('email', '!=', '')->orderBy('code')->get() as $p) {
                    fputcsv($out, [$p->code, $p->email, $p->isConfirmed() ? 'confirmed' : $p->status]);
                }
            } else {
                fputcsv($out, ['Last name', 'First name', 'Email', 'Phone', 'Age', 'Guardian', 'Guardian phone', 'Confirmed', 'Booked']);
                $rows = $session->bookings()->whereNull('cancelled_at')->get()
                    ->map(function (SessionBooking $b) {
                        [$first, $last] = filled($b->first_name) ? [$b->first_name, (string) $b->last_name] : Registration::splitName($b->name);

                        return [$last, $first, $b->email, $b->phone, $b->age, $b->guardian_name, $b->guardian_phone,
                            $b->confirmed_at?->toDateTimeString() ?? '', $b->created_at->toDateTimeString()];
                    })
                    ->sortBy(fn ($row) => Str::lower(Str::ascii($row[0].' '.$row[1])));
                foreach ($rows as $row) {
                    fputcsv($out, $row);
                }
            }

            fclose($out);
        }, $file, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function bookings(): Response
    {
        return Inertia::render('Admin/2026/Bookings', [
            'sessions' => Session::with(['translations', 'day.translations', 'bookings', 'places', 'speakers'])
                ->where('bookable', true)
                ->orderBy('programme_day_id')
                ->orderBy('starts_at')
                ->get()
                ->map(fn (Session $s) => [
                    'id' => $s->id,
                    'type' => $s->type,
                    'title' => $s->translate('en')?->title ?? '',
                    'day' => $s->day?->date->format('D d M'),
                    'time' => substr($s->starts_at, 0, 5),
                    'capacity' => $s->capacity,
                    'taken' => $s->taken(),
                    'slug' => $s->slug,
                    'image' => $s->image,
                    'published' => $s->published,
                    'internal' => (bool) $s->internal,
                    'auto_confirm' => (bool) $s->auto_confirm,
                    // Asks an age, and under 18 a guardian — both on the attendance list.
                    'youth' => (bool) $s->youth,
                    // An internal workshop hands out places rather than taking sign-ups.
                    'places' => $s->places->map(fn (SessionPlace $p) => [
                        'id' => $p->id,
                        'code' => $p->code,
                        'email' => $p->email,
                        'status' => $p->status,
                        'confirmed' => $p->isConfirmed(),
                        'reminded' => $p->reminder_sent_at?->format('d M H:i'),
                    ]),
                    // Editable identity, both languages.
                    'en' => ['title' => $s->translate('en')?->title ?? '', 'subtitle' => $s->translate('en')?->subtitle ?? ''],
                    'ro' => ['title' => $s->translate('ro')?->title ?? '', 'subtitle' => $s->translate('ro')?->subtitle ?? ''],
                    'trainers' => $s->speakers->pluck('id')->all(),
                    'trainerNames' => $s->speakers->pluck('full_name')->implode(', '),
                    'new_person' => ['first' => '', 'last' => ''],
                    'bookings' => $s->bookings->sortBy('id')->values()->map(fn (SessionBooking $b) => [
                        'id' => $b->id,
                        'name' => $b->name,
                        'first_name' => $b->first_name,
                        'last_name' => $b->last_name,
                        'email' => $b->email,
                        'phone' => $b->phone,
                        'age' => $b->age,
                        'guardian_name' => $b->guardian_name,
                        'guardian_phone' => $b->guardian_phone,
                        'guardian_consent' => $b->guardian_consent,
                        'locale' => $b->locale,
                        'cancelled' => $b->isCancelled(),
                        'confirmed' => $b->isConfirmed(),
                        // When they were last asked, and how many times.
                        'asked' => $b->confirmation_sent_at?->format('d M H:i'),
                        'asked_count' => $b->confirmation_sent_count,
                        'reminded' => $b->reminder_sent_at?->format('d M H:i'),
                        'created' => $b->created_at->toDateTimeString(),
                    ]),
                ]),
            'publicBase' => Front2026Controller::base(),
        ]);
    }

    /** Assign (or clear) the person invited to hold an internal place. */
    public function updatePlace(Request $request, SessionPlace $place): RedirectResponse
    {
        $data = $request->validate(['email' => ['nullable', 'email:rfc', 'max:255']]);
        $email = $data['email'] ?? null;

        // A change of address undoes an invitation meant for someone else.
        if ($email !== $place->email) {
            $place->fill([
                'email' => $email,
                'status' => 'open',
                'invited_at' => null,
                'confirmed_at' => null,
            ]);
        }

        $place->save();

        return back()->with('flash', 'Place saved.');
    }

    /** Send the invitation for a place, so its holder can confirm from email. */
    public function invitePlace(SessionPlace $place): RedirectResponse
    {
        if (blank($place->email)) {
            return back()->withErrors(['place' => 'Add an email before sending the invitation.']);
        }

        // Already confirmed: they get the confirmation again — date, code,
        // description, calendar — not an invitation to confirm once more.
        if ($place->isConfirmed()) {
            Mail::to($place->email)->send(new PlaceConfirmed(
                $place->load('session.day', 'session.translations'),
                PlaceCalendar::icsUrl($place),
                PlaceCalendar::googleUrl($place->session),
            ));

            return back()->with('flash', 'Confirmation sent again to '.$place->email.'.');
        }

        $url = url(Front2026Controller::base().'/places/confirm/'.$place->token);
        Mail::to($place->email)->send(new PlaceInvite(
            $place->load('session.day.translations', 'session.translations'),
            $url,
        ));

        $place->update(['status' => 'invited', 'invited_at' => now()]);

        return back()->with('flash', 'Invitation sent to '.$place->email.'.');
    }

    /**
     * Frees the place without losing the evidence that it was wanted.
     */
    public function cancelBooking(SessionBooking $booking): RedirectResponse
    {
        $booking->cancelled_at = $booking->isCancelled() ? null : now();
        $booking->save();

        return back()->with('flash', $booking->isCancelled()
            ? $booking->name.'’s place released.'
            : $booking->name.'’s place restored.');
    }

    /** The office books someone in by hand, or brings a cancelled seat back. */
    public function addBooking(Request $request): RedirectResponse
    {
        $data = $this->bookingData($request, null, (int) $request->input('session_id'));
        $session = Session::where('bookable', true)->findOrFail($data['session_id']);

        // Reuse the row for this address if there is one — cancelled or not —
        // so the (session, email) unique key never collides.
        $session->bookings()->updateOrCreate(
            ['email' => $data['email']],
            $data + [
                'cancelled_at' => null,
                'locale' => 'ro',
                'registration_id' => Registration::where('email', $data['email'])->value('id'),
            ]
        );

        return back()->with('flash', $data['name'].' booked.');
    }

    public function updateBooking(Request $request, SessionBooking $booking): RedirectResponse
    {
        $booking->update($this->bookingData($request, $booking, $booking->session_id));

        return back()->with('flash', $booking->name.'’s booking updated.');
    }

    /**
     * The place, in writing, to the address on the booking — for when the
     * office has just corrected a mistyped one, or someone asks for it.
     */
    public function sendBookingConfirmation(Request $request, SessionBooking $booking): RedirectResponse|JsonResponse
    {
        // One button on a row is a page visit; the "everyone not confirmed"
        // run calls this once per person and wants a plain answer back.
        $answer = fn (bool $ok, string $message) => $request->header('X-Inertia')
            ? ($ok ? back()->with('flash', $message) : back()->withErrors(['booking' => $message]))
            : response()->json(['ok' => $ok, 'error' => $ok ? null : $message]);

        if ($booking->isCancelled()) {
            return $answer(false, 'That place has been released — restore it before asking again.');
        }

        try {
            BookingConfirmed::sendTo($booking);
        } catch (Throwable $e) {
            Log::error('2026 booking confirmation failed', ['booking' => $booking->id, 'error' => $e->getMessage()]);

            return $answer(false, 'The email did not go out: '.$e->getMessage());
        }

        return $answer(true, 'Confirmation request sent to '.$booking->email.'.');
    }

    /** The reminder before the workshop, to one person — the run calls it once per person. */
    public function sendBookingReminder(Request $request, SessionBooking $booking): RedirectResponse|JsonResponse
    {
        $answer = fn (bool $ok, string $message) => $request->header('X-Inertia')
            ? ($ok ? back()->with('flash', $message) : back()->withErrors(['booking' => $message]))
            : response()->json(['ok' => $ok, 'error' => $ok ? null : $message]);

        if ($booking->isCancelled()) {
            return $answer(false, 'That place has been released — restore it before reminding.');
        }

        try {
            SessionReminder::sendTo($booking);
        } catch (Throwable $e) {
            Log::error('2026 booking reminder failed', ['booking' => $booking->id, 'error' => $e->getMessage()]);

            return $answer(false, 'The email did not go out: '.$e->getMessage());
        }

        return $answer(true, 'Reminder sent to '.$booking->email.'.');
    }

    /** The reminder to one confirmed place of an internal workshop. */
    public function sendPlaceReminder(Request $request, SessionPlace $place): RedirectResponse|JsonResponse
    {
        $answer = fn (bool $ok, string $message) => $request->header('X-Inertia')
            ? ($ok ? back()->with('flash', $message) : back()->withErrors(['place' => $message]))
            : response()->json(['ok' => $ok, 'error' => $ok ? null : $message]);

        if (blank($place->email) || ! $place->isConfirmed()) {
            return $answer(false, 'Only a confirmed place gets the reminder.');
        }

        try {
            SessionReminder::sendTo($place);
        } catch (Throwable $e) {
            Log::error('2026 place reminder failed', ['place' => $place->id, 'error' => $e->getMessage()]);

            return $answer(false, 'The email did not go out: '.$e->getMessage());
        }

        return $answer(true, 'Reminder sent to '.$place->email.'.');
    }

    /** The reminder as someone who booked in that language would get it. Nothing sends. */
    public function reminderPreview(Session $session, string $locale): string
    {
        return (new SessionReminder($this->sample($session, $locale)))->render();
    }

    /** The reminder to the person signed in. Not recorded. */
    public function reminderTest(Session $session, string $locale): JsonResponse
    {
        $email = auth()->user()->email;

        try {
            Mail::to($email)->send(new SessionReminder($this->sample($session, $locale)));
        } catch (Throwable $e) {
            report($e);

            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }

        return response()->json(['ok' => true, 'email' => $email]);
    }

    /**
     * An unsaved booking in the signed-in admin's name — or, for an internal
     * workshop, a place with a made-up code — for a preview or a test.
     */
    private function sample(Session $session, string $locale): SessionBooking|SessionPlace
    {
        abort_unless(in_array($locale, ['en', 'ro'], true), 404);

        $holder = $session->internal
            ? new SessionPlace(['code' => 'ABC234'])
            : new SessionBooking(['name' => auth()->user()->name, 'locale' => $locale, 'token' => 'preview']);

        return $holder->setRelation('session', $session->load('translations', 'day.translations'));
    }

    /** This workshop's switch: email new bookings a request to confirm, or not. */
    public function toggleAutoConfirm(Session $session): RedirectResponse
    {
        $session->update(['auto_confirm' => ! $session->auto_confirm]);

        return back()->with('flash', $session->auto_confirm
            ? 'New bookings for this one are now asked to confirm, by email.'
            : 'New bookings for this one are no longer emailed.');
    }

    /** A hard delete, for a row entered by mistake — Release is the soft one. */
    public function deleteBooking(SessionBooking $booking): RedirectResponse
    {
        $name = $booking->name;
        $booking->delete();

        return back()->with('flash', $name.'’s booking deleted.');
    }

    /** The shared, validated booking fields, with `name` composed from the two. */
    private function bookingData(Request $request, ?SessionBooking $booking, int $sessionId): array
    {
        $data = $request->validate([
            'session_id' => ['sometimes', Rule::exists('wcm_2026.sessions', 'id')],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email:rfc', 'max:255',
                // One place per address per session — ignoring this same row.
                Rule::unique('wcm_2026.session_bookings', 'email')
                    ->where('session_id', $sessionId)
                    ->ignore($booking?->id),
            ],
            'phone' => ['required', 'string', 'max:40', 'regex:/^[0-9\s\-\+\(\)]+$/'],
        ]);

        $data['name'] = trim($data['first_name'].' '.$data['last_name']);
        $data['session_id'] = $sessionId;

        return $data;
    }
}
