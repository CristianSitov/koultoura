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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

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
        $programme = ProgrammeDay::with('sessions.translations')->get()
            ->keyBy(fn (ProgrammeDay $day) => $day->date->toDateString());
        $events = AgendaEvent::orderBy('starts_at')->get()
            ->groupBy(fn (AgendaEvent $event) => $event->date->toDateString());

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
            'recipients' => AgendaRecipient::orderBy('name')->get()
                ->map(fn (AgendaRecipient $r) => [
                    'id' => $r->id,
                    'name' => $r->name,
                    'email' => $r->email,
                    'locale' => $r->locale,
                    'sent_count' => $r->sent_count,
                    'last_sent' => $r->last_sent_at?->format('j M, H:i'),
                    'url' => $this->link($r),
                ]),
            // Names to pick from when adding someone — a speaker has no email
            // on record, so the address is still typed.
            'speakers' => Person::orderBy('full_name')->pluck('full_name'),
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

    public function storeRecipient(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique(AgendaRecipient::class, 'email')],
            'locale' => ['required', Rule::in(['en', 'ro'])],
        ], [
            'email.unique' => 'That address is already on the list.',
        ]);

        AgendaRecipient::create($data);

        return back()->with('flash', $data['name'].' added.');
    }

    public function destroyRecipient(AgendaRecipient $recipient): RedirectResponse
    {
        $recipient->delete();

        // The token went with the row, so the link in their inbox is now a 404.
        return back()->with('flash', $recipient->name.' removed — their link no longer works.');
    }

    public function send(AgendaRecipient $recipient): RedirectResponse
    {
        $this->deliver($recipient);

        return back()->with('flash', 'Programme sent to '.$recipient->email.'.');
    }

    /** Everyone who has not had it yet. Anyone else is resent one at a time. */
    public function sendAll(): RedirectResponse
    {
        $pending = AgendaRecipient::where('sent_count', 0)->get();

        $pending->each(fn (AgendaRecipient $recipient) => $this->deliver($recipient));

        return back()->with('flash', $pending->isEmpty()
            ? 'Everyone on the list has already been sent the programme.'
            : 'Programme sent to '.$pending->count().' '.($pending->count() === 1 ? 'person' : 'people').'.');
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

    private function deliver(AgendaRecipient $recipient): void
    {
        Mail::to($recipient->email)->send(new AgendaInvite($recipient, $this->link($recipient)));

        $recipient->update([
            'last_sent_at' => now(),
            'sent_count' => $recipient->sent_count + 1,
        ]);
    }

    private function link(AgendaRecipient $recipient): string
    {
        return url(Front2026Controller::base().'/agenda/'.$recipient->token);
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
