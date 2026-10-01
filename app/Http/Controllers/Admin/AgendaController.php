<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Front2026Controller;
use App\Mail\AgendaInvite;
use App\Models\AgendaEvent;
use App\Models\AgendaRecipient;
use App\Models\Person;
use App\Models\ProgrammeDay;
use App\Support\Agenda;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
    public function index(): Response
    {
        return Inertia::render('Admin/2026/Agenda', [
            'days' => ProgrammeDay::with(['translations', 'agendaEvents'])
                ->orderBy('position')
                ->orderBy('date')
                ->get()
                ->map(fn (ProgrammeDay $day) => [
                    'id' => $day->id,
                    'label' => $day->date->format('D j M'),
                    'events' => $day->agendaEvents->map(fn (AgendaEvent $e) => [
                        'id' => $e->id,
                        'programme_day_id' => $e->programme_day_id,
                        // HH:MM, which is what a time input reads and writes.
                        'starts_at' => substr($e->starts_at, 0, 5),
                        'ends_at' => $e->ends_at ? substr($e->ends_at, 0, 5) : '',
                        'title' => $e->title,
                        'title_ro' => $e->title_ro ?? '',
                        'location' => $e->location ?? '',
                        'location_ro' => $e->location_ro ?? '',
                        'description' => $e->description ?? '',
                        'description_ro' => $e->description_ro ?? '',
                    ]),
                ]),
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
            'programme_day_id' => ['required', Rule::exists(ProgrammeDay::class, 'id')],
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
        ], [
            'programme_day_id' => 'day',
            'starts_at' => 'start time',
        ]);
    }
}
