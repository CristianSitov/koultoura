<?php

namespace App\Support;

use App\Http\Controllers\Front2026Controller;
use App\Models\AgendaEvent;
use App\Models\ProgrammeDay;
use App\Models\Session;
use App\Models\Setting;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/*
 * The agenda for speakers and guests: one page, behind one secret link.
 *
 * A day on it is a short column of boxes in the order they happen. The public
 * programme is one of them — a single box from its first session's start to
 * its last one's end, carrying the office's note and leading to the programme
 * itself — and the rest are what the office adds around it: transfers, meals,
 * meetings. The sessions are not listed here; that is what the programme is for.
 *
 * One builder for the page a guest opens and the preview in the backoffice, so
 * the office sees exactly what is sent.
 */
class Agenda
{
    /**
     * The dates the agenda covers, as Y-m-d: the symposium's own days and one
     * either side. Guests land the day before and leave the day after, and
     * those days have dinners and transfers of their own.
     *
     * @return Collection<int, string>
     */
    public static function dates(): Collection
    {
        $days = ProgrammeDay::orderBy('date')->get(['id', 'date'])->map->date;

        if ($days->isEmpty()) {
            return collect();
        }

        // ponytail: one day of padding each side. If guests start staying
        // longer, widen it here — every screen reads its days from this.
        return collect(CarbonPeriod::create($days->first()->copy()->subDay(), $days->last()->copy()->addDay()))
            ->map(fn ($date) => $date->toDateString())
            ->values();
    }

    /** The code in the page's address. Made the first time it is asked for. */
    public static function token(): string
    {
        return Setting::text(Setting::AGENDA_TOKEN) ?? self::resetToken();
    }

    /** A new code: the address already sent out stops working at once. */
    public static function resetToken(): string
    {
        $token = Str::random(48);

        Setting::store(Setting::AGENDA_TOKEN, $token);

        return $token;
    }

    /**
     * The hours of a day's programme box, or null when the day has nothing
     * published to stand for.
     *
     * Left alone they follow the programme: from the first session's start to
     * the last one's end. Most sessions carry no end, so one without is given
     * an hour — which is what "the day ends at seven" means when the last talk
     * starts at six. The office can set either hour itself; `auto` is what it
     * would be otherwise, for the form to show.
     */
    public static function hours(ProgrammeDay $day): ?array
    {
        $sessions = $day->sessions->where('published', true);

        if (! $day->published || $sessions->isEmpty()) {
            return null;
        }

        $auto = [
            'start' => substr($sessions->min('starts_at'), 0, 5),
            'end' => $sessions
                ->map(fn (Session $session) => $session->ends_at
                    ? substr($session->ends_at, 0, 5)
                    : self::anHourAfter(substr($session->starts_at, 0, 5)))
                ->max(),
        ];

        return [
            'start' => $day->agenda_starts_at ? substr($day->agenda_starts_at, 0, 5) : $auto['start'],
            'end' => $day->agenda_ends_at ? substr($day->agenda_ends_at, 0, 5) : $auto['end'],
            'auto' => $auto,
            'sessions' => $sessions->count(),
        ];
    }

    public static function days(string $locale): array
    {
        $programme = ProgrammeDay::with(['translations', 'theme.translations', 'moderator', 'sessions'])
            ->orderBy('position')
            ->orderBy('date')
            ->get();

        // "Day 2" is a day's place in the symposium itself, so the numbers come
        // from the programme alone — the days either side have none.
        $numbers = $programme->values()->mapWithKeys(fn (ProgrammeDay $day, int $i) => [$day->id => $i + 1]);
        $byDate = $programme->keyBy(fn (ProgrammeDay $day) => $day->date->toDateString());
        $events = AgendaEvent::orderBy('starts_at')->get()->groupBy(fn (AgendaEvent $event) => $event->date->toDateString());

        return self::dates()
            ->map(fn (string $date) => self::day(
                $date,
                $byDate->get($date),
                $events->get($date) ?? collect(),
                $numbers,
                $locale,
            ))
            // A day with nothing on it is not drawn — which is what keeps the
            // days either side off the page until something is put on them.
            ->filter(fn (array $day) => count($day['items']) > 0)
            ->values()
            ->all();
    }

    private static function day(string $date, ?ProgrammeDay $day, Collection $events, Collection $numbers, string $locale): array
    {
        $when = Carbon::parse($date)->locale($locale);
        $hours = $day ? self::hours($day) : null;

        $items = $events
            ->map(fn (AgendaEvent $event) => self::event($event, $locale))
            ->when($hours, fn (Collection $items) => $items->push(self::programme($day, $hours, $numbers[$day->id], $when, $locale)))
            ->sortBy('time')
            ->values()
            ->all();

        return [
            'date' => $date,
            'n' => $day ? $numbers[$day->id] : null,
            'num' => $when->format('d'),
            'month' => rtrim($when->translatedFormat('M'), '.'),
            'name' => ($day ? self::text($day, $locale)?->name : null) ?: $when->translatedFormat('l'),
            'items' => $items,
        ];
    }

    /** The whole of a day's public programme, as the one box it is here. */
    private static function programme(ProgrammeDay $day, array $hours, int $n, Carbon $when, string $locale): array
    {
        return [
            'type' => 'programme',
            'id' => 'p'.$day->id,
            'time' => $hours['start'],
            'end' => $hours['end'],
            'n' => $n,
            'theme' => $day->theme ? [
                'numeral' => $day->theme->numeral,
                'title' => self::text($day->theme, $locale)?->title ?? '',
            ] : null,
            'moderator' => $day->moderator?->full_name,
            'note' => $locale === 'ro' && filled($day->agenda_note_ro) ? $day->agenda_note_ro : $day->agenda_note,
            // The public programme, at that day — in the language being read.
            'url' => Front2026Controller::base()
                .($locale === 'ro' ? '/ro' : '')
                .'/'.Front2026Controller::sectionSlug('programme', $locale)
                .'#day-'.$when->format('d'),
        ];
    }

    private static function event(AgendaEvent $event, string $locale): array
    {
        return [
            'type' => 'event',
            'id' => 'e'.$event->id,
            'time' => substr($event->starts_at, 0, 5),
            'end' => $event->ends_at ? substr($event->ends_at, 0, 5) : null,
            'title' => $event->text('title', $locale),
            'location' => $event->text('location', $locale),
            'description' => $event->text('description', $locale),
            // A map is a place, not a language: one pasted only into the English
            // text still counts when the Romanian has words of its own.
            'english' => $locale === 'en' ? null : [
                'location' => $event->location,
                'description' => $event->description,
            ],
        ];
    }

    private static function anHourAfter(string $time): string
    {
        $later = Carbon::createFromFormat('H:i', $time)->addHour()->format('H:i');

        // Past midnight would read as before the start.
        return $later < $time ? '23:59' : $later;
    }

    /** The translation in the language asked for, falling back to English. */
    private static function text($model, string $locale)
    {
        return $model->translate($locale) ?? $model->translate('en');
    }
}
