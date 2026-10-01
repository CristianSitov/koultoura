<?php

namespace App\Support;

use App\Models\AgendaEvent;
use App\Models\Person;
use App\Models\ProgrammeDay;
use App\Models\Session;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/*
 * The internal agenda as the page draws it: each day's published sessions and
 * its internal events in one list, in the order they happen.
 *
 * One builder for the page a recipient opens and the preview in the backoffice,
 * so the office sees exactly what is sent.
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

    public static function days(string $locale): array
    {
        $programme = ProgrammeDay::with([
            'translations',
            'theme.translations',
            'moderator',
            // Published only: a draft session is the office's business, and a
            // link in an inbox is not a signed-in reader.
            'sessions' => fn ($q) => $q->published(),
            'sessions.translations',
            'sessions.speakers.translations',
        ])
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

        $items = collect($day?->sessions ?? [])
            ->map(fn (Session $session) => self::session($session, $locale))
            ->concat($events->map(fn (AgendaEvent $event) => self::event($event, $locale)))
            // By the clock; at the same minute the official session leads.
            ->sortBy(fn (array $item) => $item['time'].($item['type'] === 'event' ? '1' : '0'))
            ->values()
            ->all();

        return [
            'date' => $date,
            'n' => $day ? $numbers[$day->id] : null,
            'num' => $when->format('d'),
            'month' => rtrim($when->translatedFormat('M'), '.'),
            'name' => ($day ? self::text($day, $locale)?->name : null) ?: $when->translatedFormat('l'),
            'theme' => $day?->theme ? [
                'numeral' => $day->theme->numeral,
                'title' => self::text($day->theme, $locale)?->title ?? '',
            ] : null,
            'moderator' => $day?->moderator?->full_name,
            'items' => $items,
        ];
    }

    private static function session(Session $session, string $locale): array
    {
        $text = self::text($session, $locale);

        return [
            'type' => 'session',
            'id' => 's'.$session->id,
            'time' => substr($session->starts_at, 0, 5),
            'end' => $session->ends_at ? substr($session->ends_at, 0, 5) : null,
            'kind' => $session->kind,
            'title' => $text?->title ?? '',
            'audience' => $text?->audience ?? '',
            'school' => $session->school,
            'who' => $session->speakers->map(function (Person $person) use ($locale) {
                $org = self::text($person, $locale)?->institution;

                return ['name' => $person->full_name, 'org' => filled($org) ? $org : null];
            })->all(),
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
        ];
    }

    /** The translation in the language asked for, falling back to English. */
    private static function text($model, string $locale)
    {
        return $model->translate($locale) ?? $model->translate('en');
    }
}
