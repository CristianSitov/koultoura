<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Front2026Controller;
use App\Mail\AgendaInvite;
use App\Models\AgendaEvent;
use App\Models\AgendaRecipient;
use App\Models\Person;
use App\Models\ProgrammeDay;
use App\Models\Session;
use App\Support\Agenda;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

/*
 * The internal agenda's backoffice: the events that are not in the public
 * programme, the people it is sent to, and a preview of the page they get.
 */
class AgendaController extends Controller
{
    /*
     * A break is free time, so an event set across one is not a clash — a
     * speakers' lunch belongs in the lunch break. Told by the title, the same
     * way the public programme tells them (see Sections/2026/Programme.vue).
     */
    private const BREAKS = ['Coffee Break', 'Lunch Break'];

    public function index(): Response
    {
        // Before anything is shown: the list must match the speakers as they
        // are now, since this is the screen the sending is done from.
        AgendaRecipient::syncSpeakers();

        $programme = ProgrammeDay::with('sessions.translations')->get()
            ->keyBy(fn (ProgrammeDay $day) => $day->date->toDateString());
        $events = AgendaEvent::orderBy('starts_at')->get()
            ->groupBy(fn (AgendaEvent $event) => $event->date->toDateString());
        $recipients = AgendaRecipient::orderBy('name')->get();
        $linked = $recipients->whereNotNull('person_id')->keyBy('person_id');

        return Inertia::render('Admin/2026/Agenda', [
            'days' => Agenda::dates()->map(fn (string $date) => [
                'date' => $date,
                'label' => Carbon::parse($date)->format('D j M'),
                // Not one of the symposium's own days: the day guests arrive,
                // or the day they leave.
                'extra' => ! $programme->has($date),
                'events' => ($events->get($date) ?? collect())->map(fn (AgendaEvent $e) => [
                    'id' => $e->id,
                    'date' => $date,
                    // HH:MM, which is what a time input reads and writes.
                    'starts_at' => substr($e->starts_at, 0, 5),
                    'ends_at' => $e->ends_at ? substr($e->ends_at, 0, 5) : '',
                    'title' => $e->title,
                    'title_ro' => $e->title_ro ?? '',
                    'location' => $e->location ?? '',
                    'location_ro' => $e->location_ro ?? '',
                    'description' => $e->description ?? '',
                    'description_ro' => $e->description_ro ?? '',
                ])->values(),
                // What the programme has on that day, for the overlap check.
                'busy' => $programme->has($date) ? $this->busy($programme->get($date)) : [],
            ])->values(),
            // Every speaker, with or without an address — the ones without are
            // listed so an address can be typed straight in.
            'speakers' => Person::orderBy('full_name')->get()->map(fn (Person $person) => [
                'id' => $person->id,
                'name' => $person->full_name,
                'email' => $person->email ?? '',
                'locale' => $person->email_locale ?: 'en',
                'recipient' => $linked->has($person->id) ? $this->row($linked->get($person->id)) : null,
            ])->values(),
            // Guests and team: on the list, but not speakers.
            'extras' => $recipients->whereNull('person_id')
                ->map(fn (AgendaRecipient $recipient) => $this->row($recipient))
                ->values(),
            'publicBase' => Front2026Controller::base(),
        ]);
    }

    /**
     * The page exactly as a recipient gets it, in either language. The locale
     * goes in as a prop of its own: the shared one was already settled before
     * this ran, from the office's own session.
     */
    public function preview(string $locale): Response
    {
        App::setLocale($locale);

        return Inertia::render('2026/Agenda', [
            'base' => Front2026Controller::base(),
            'name' => '',
            'days' => Agenda::days($locale),
            'locale' => $locale,
            'preview' => true,
        ]);
    }

    public function storeEvent(Request $request): RedirectResponse
    {
        AgendaEvent::create($this->eventData($request));

        return back()->with('flash', 'Event added.');
    }

    public function updateEvent(Request $request, AgendaEvent $event): RedirectResponse
    {
        $event->update($this->eventData($request));

        return back()->with('flash', 'Event saved.');
    }

    public function destroyEvent(AgendaEvent $event): RedirectResponse
    {
        $event->delete();

        return back()->with('flash', 'Event removed.');
    }

    /**
     * A speaker's address and language, set from the send list. It is saved on
     * the speaker — the list follows from there the next time it is shown.
     */
    public function updateSpeaker(Request $request, Person $person): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['nullable', 'email:rfc', 'max:255', Rule::unique('wcm_2026.people', 'email')->ignore($person->id)],
            'locale' => ['required', Rule::in(['en', 'ro'])],
        ], [
            'email.unique' => 'Another speaker already has that address.',
        ]);

        $person->email = filled($data['email'] ?? null) ? $data['email'] : null;
        $person->email_locale = $data['locale'];
        $person->save();

        return back()->with('flash', $person->email
            ? $person->full_name.' — address saved.'
            : $person->full_name.' — address removed, so they are off the list.');
    }

    public function storeRecipient(Request $request): RedirectResponse
    {
        $data = $this->recipientData($request);

        AgendaRecipient::create($data);

        return back()->with('flash', $data['name'].' added.');
    }

    /** A guest or team member. A speaker is changed on the speaker instead. */
    public function updateRecipient(Request $request, AgendaRecipient $recipient): RedirectResponse
    {
        abort_if($recipient->person_id !== null, 404);

        $recipient->update($this->recipientData($request, $recipient));

        return back()->with('flash', $recipient->name.' saved.');
    }

    public function destroyRecipient(AgendaRecipient $recipient): RedirectResponse
    {
        // A speaker leaves the list by losing their address, not by this: the
        // row would only be put back the next time the list is shown.
        abort_if($recipient->person_id !== null, 404);

        $recipient->delete();

        // The token went with the row, so the link in their inbox is now a 404.
        return back()->with('flash', $recipient->name.' removed — their link no longer works.');
    }

    /**
     * One email. The screen calls this once per person and waits in between,
     * rather than asking for everyone in a single request: the mail provider
     * takes only a couple a second, and a request has thirty seconds to live —
     * neither survives thirty speakers at once. One at a time also means one
     * bad address fails alone instead of taking the rest with it.
     */
    public function send(AgendaRecipient $recipient): JsonResponse
    {
        try {
            $this->deliver($recipient);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }

        return response()->json(['ok' => true]);
    }

    /*
     * A day's sessions as stretches of time. Most carry only a start, so one
     * without an end is taken to run until the next thing starts — which is how
     * the printed programme reads. "Next" means a later start: sessions that
     * share a slot run side by side, and neither ends the other.
     *
     * Drafts are in: a session not yet published is still one the office means
     * to hold, and it is the office this warns.
     */
    private function busy(ProgrammeDay $day): array
    {
        $starts = $day->sessions
            ->map(fn (Session $session) => substr($session->starts_at, 0, 5))
            ->unique()
            ->sort()
            ->values();

        return $day->sessions->map(function (Session $session) use ($starts) {
            $start = substr($session->starts_at, 0, 5);
            $title = $session->translate('en')?->title ?? '';

            return [
                'title' => $title,
                'start' => $start,
                'end' => $session->ends_at
                    ? substr($session->ends_at, 0, 5)
                    : ($starts->first(fn (string $time) => $time > $start) ?? self::anHourAfter($start)),
                'break' => in_array($title, self::BREAKS, true),
                'draft' => ! $session->published,
            ];
        })->values()->all();
    }

    // ponytail: the day's last session, if it has no end, is given an hour.
    // Give it a real end time in Programme and that is used instead.
    private static function anHourAfter(string $time): string
    {
        $later = Carbon::createFromFormat('H:i', $time)->addHour()->format('H:i');

        // Past midnight would read as before the start.
        return $later < $time ? '23:59' : $later;
    }

    /** A recipient as the send list shows them. */
    private function row(AgendaRecipient $recipient): array
    {
        return [
            'id' => $recipient->id,
            'name' => $recipient->name,
            'email' => $recipient->email,
            'locale' => $recipient->locale,
            'sent_count' => $recipient->sent_count,
            'last_sent' => $recipient->last_sent_at?->format('j M, H:i'),
            'pending' => $recipient->isPending(),
            'changed' => $recipient->addressChanged(),
            'url' => $this->link($recipient),
        ];
    }

    private function deliver(AgendaRecipient $recipient): void
    {
        // Already has it at this address: this one is news of a change, not an
        // invitation. A new address has never had it, however often the old did.
        $update = $recipient->sent_count > 0 && ! $recipient->addressChanged();

        Mail::to($recipient->email)->send(new AgendaInvite($recipient, $this->link($recipient), $update));

        $recipient->update([
            'last_sent_at' => now(),
            'sent_count' => $recipient->sent_count + 1,
            'sent_email' => $recipient->email,
        ]);
    }

    private function link(AgendaRecipient $recipient): string
    {
        return url(Front2026Controller::base().'/agenda/'.$recipient->token);
    }

    private function recipientData(Request $request, ?AgendaRecipient $recipient = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique(AgendaRecipient::class, 'email')->ignore($recipient?->id)],
            'locale' => ['required', Rule::in(['en', 'ro'])],
        ], [
            'email.unique' => 'That address is already on the list.',
        ]);
    }

    private function eventData(Request $request): array
    {
        return $request->validate([
            // One of the agenda's days — the symposium's, or the day either side.
            'date' => ['required', Rule::in(Agenda::dates()->all())],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['nullable', 'date_format:H:i', 'after:starts_at'],
            'title' => ['required', 'string', 'max:200'],
            'title_ro' => ['nullable', 'string', 'max:200'],
            'location' => ['nullable', 'string', 'max:200'],
            'location_ro' => ['nullable', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:2000'],
            'description_ro' => ['nullable', 'string', 'max:2000'],
        ], [
            // The stock wording names the columns: "The ends at must be a date
            // after starts at."
            'ends_at.after' => 'The end must be later than the start.',
            'starts_at.date_format' => 'Enter the start as a time, like 14:30.',
            'ends_at.date_format' => 'Enter the end as a time, like 14:30.',
            'date.in' => 'Pick one of the agenda’s days.',
        ], [
            'date' => 'day',
            'starts_at' => 'start time',
        ]);
    }
}
