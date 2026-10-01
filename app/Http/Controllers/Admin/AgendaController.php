<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Front2026Controller;
use App\Models\AgendaEvent;
use App\Models\ProgrammeDay;
use App\Support\Agenda;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/*
 * The agenda builder: the events around the programme, the note and hours on
 * each day's programme box, the secret link the office sends out, and a
 * preview of the page behind it.
 */
class AgendaController extends Controller
{
    public function index(): Response
    {
        $programme = ProgrammeDay::with('sessions')->get()
            ->keyBy(fn (ProgrammeDay $day) => $day->date->toDateString());
        $events = AgendaEvent::orderBy('starts_at')->get()
            ->groupBy(fn (AgendaEvent $event) => $event->date->toDateString());
        $link = url(Front2026Controller::base());
        $token = Agenda::token();

        return Inertia::render('Admin/2026/Agenda', [
            // The same page in either language; the page has a switch of its
            // own, so whichever is sent, the reader can change it.
            'link' => [
                'en' => $link.'/agenda/'.$token,
                'ro' => $link.'/ro/agenda/'.$token,
            ],
            'days' => Agenda::dates()->map(fn (string $date) => [
                'date' => $date,
                'label' => Carbon::parse($date)->format('D j M'),
                // Not one of the symposium's own days: the day guests arrive,
                // or the day they leave.
                'extra' => ! $programme->has($date),
                'programme' => $programme->has($date) ? $this->box($programme->get($date)) : null,
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
            ])->values(),
            'publicBase' => Front2026Controller::base(),
        ]);
    }

    /**
     * The page exactly as a guest gets it, in either language. The locale goes
     * in as a prop of its own: the shared one was already settled before this
     * ran, from the office's own session.
     */
    public function preview(string $locale): Response
    {
        App::setLocale($locale);

        return Inertia::render('2026/Agenda', [
            'base' => Front2026Controller::base(),
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

    /** The note on a day's programme box, and its hours if the office sets them. */
    public function updateDay(Request $request, ProgrammeDay $day): RedirectResponse
    {
        $data = $request->validate([
            'starts_at' => ['nullable', 'date_format:H:i'],
            'ends_at' => ['nullable', 'date_format:H:i'],
            'note' => ['nullable', 'string', 'max:2000'],
            'note_ro' => ['nullable', 'string', 'max:2000'],
        ], [
            'starts_at.date_format' => 'Enter the start as a time, like 09:00.',
            'ends_at.date_format' => 'Enter the end as a time, like 19:00.',
        ]);

        // Either hour may be left to follow the programme, so the two that are
        // checked against each other are the ones that would actually be shown.
        $auto = Agenda::hours($day->load('sessions'))['auto'] ?? ['start' => null, 'end' => null];
        $start = $data['starts_at'] ?? $auto['start'];
        $end = $data['ends_at'] ?? $auto['end'];

        if ($start && $end && $end <= $start) {
            throw ValidationException::withMessages(['ends_at' => 'The end must be later than the start.']);
        }

        $day->update([
            'agenda_starts_at' => $data['starts_at'] ?? null,
            'agenda_ends_at' => $data['ends_at'] ?? null,
            'agenda_note' => $data['note'] ?? null,
            'agenda_note_ro' => $data['note_ro'] ?? null,
        ]);

        return back()->with('flash', $day->date->format('D j M').' — programme box saved.');
    }

    /** A new code in the link. The one already sent out stops working. */
    public function resetLink(): RedirectResponse
    {
        Agenda::resetToken();

        return back()->with('flash', 'The link has been reset — the old one no longer works. Send the new one out.');
    }

    /** A day's programme box as the builder shows it. */
    private function box(ProgrammeDay $day): ?array
    {
        $hours = Agenda::hours($day);

        if (! $hours) {
            return null;
        }

        return [
            'id' => $day->id,
            'start' => $hours['start'],
            'end' => $hours['end'],
            'auto' => $hours['auto'],
            'sessions' => $hours['sessions'],
            // What the office typed; empty means "follow the programme".
            'starts_at' => $day->agenda_starts_at ? substr($day->agenda_starts_at, 0, 5) : '',
            'ends_at' => $day->agenda_ends_at ? substr($day->agenda_ends_at, 0, 5) : '',
            'note' => $day->agenda_note ?? '',
            'note_ro' => $day->agenda_note_ro ?? '',
        ];
    }

    private function eventData(Request $request): array
    {
        $data = $request->validate([
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

        /*
         * Events go around the programme, not over it. The programme is one
         * box on the page, and a second box on top of it has nowhere to be
         * drawn — what happens during it is written on the box, as its note.
         */
        if ($hours = Agenda::clash($data['date'], $data['starts_at'], $data['ends_at'] ?? null)) {
            throw ValidationException::withMessages([
                'starts_at' => "That falls during the programme ({$hours['start']}–{$hours['end']}). Write it in the programme box’s note for that day, or move it outside those hours.",
            ]);
        }

        return $data;
    }
}
