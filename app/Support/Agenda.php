<?php

namespace App\Support;

use App\Models\AgendaEvent;
use App\Models\Person;
use App\Models\ProgrammeDay;
use App\Models\Session;

/*
 * The internal agenda as the page draws it: each day's published sessions and
 * its internal events in one list, in the order they happen.
 *
 * One builder for the page a recipient opens and the preview in the backoffice,
 * so the office sees exactly what is sent.
 */
class Agenda
{
    public static function days(string $locale): array
    {
        return ProgrammeDay::with([
            'translations',
            'theme.translations',
            'moderator',
            // Published only: a draft session is the office's business, and a
            // link in an inbox is not a signed-in reader.
            'sessions' => fn ($q) => $q->published(),
            'sessions.translations',
            'sessions.speakers.translations',
            'agendaEvents',
        ])
            ->orderBy('position')
            ->orderBy('date')
            ->get()
            // The day number is its place in the symposium, so it is taken
            // before the empty days are dropped.
            ->map(fn (ProgrammeDay $day, int $i) => self::day($day, $i + 1, $locale))
            ->filter(fn (array $day) => count($day['items']) > 0)
            ->values()
            ->all();
    }

    private static function day(ProgrammeDay $day, int $n, string $locale): array
    {
        $date = $day->date->locale($locale);

        $items = $day->sessions
            ->map(fn (Session $session) => self::session($session, $locale))
            ->concat($day->agendaEvents->map(fn (AgendaEvent $event) => self::event($event, $locale)))
            // By the clock; at the same minute the official session leads.
            ->sortBy(fn (array $item) => $item['time'].($item['type'] === 'event' ? '1' : '0'))
            ->values()
            ->all();

        return [
            'id' => $day->id,
            'n' => $n,
            'num' => $date->format('d'),
            'month' => rtrim($date->translatedFormat('M'), '.'),
            'name' => self::text($day, $locale)?->name ?? $date->translatedFormat('l'),
            'theme' => $day->theme ? [
                'numeral' => $day->theme->numeral,
                'title' => self::text($day->theme, $locale)?->title ?? '',
            ] : null,
            'moderator' => $day->moderator?->full_name,
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
