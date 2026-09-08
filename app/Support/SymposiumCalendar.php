<?php

namespace App\Support;

use App\Http\Controllers\Front2026Controller;

/*
 * The symposium as a calendar entry.
 *
 * Written by hand rather than pulled in as a dependency: it is one all-day
 * event over four fixed days, and an iCalendar file for that is a dozen lines
 * of text with strict line endings.
 */
class SymposiumCalendar
{
    /** 7–10 October 2026. DTEND is exclusive, hence the 11th. */
    public const START = '20261007';

    public const END = '20261011';

    public const VENUE = 'FABER, Str. Anton Seiler 2, Timișoara, Romania';

    /** The .ics body, for attaching to an email. */
    public static function ics(string $locale = 'en'): string
    {
        $summary = $locale === 'ro'
            ? 'Why Culture Matters 2026 · Simpozion internațional'
            : 'Why Culture Matters 2026 · International Symposium';

        $description = $locale === 'ro'
            ? 'A treia ediție a simpozionului Why Culture Matters, organizat de Asociația Prin Banat. Programul complet: '
            : 'The third edition of the Why Culture Matters symposium, organised by Asociația Prin Banat. Full programme: ';

        $url = url(Front2026Controller::BASE.($locale === 'ro' ? '/ro' : '').'#programme');

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Asociatia Prin Banat//Why Culture Matters 2026//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            // Stable, so importing twice updates the entry instead of doubling it.
            'UID:wcm2026@whyculturematters.eu',
            'DTSTAMP:'.gmdate('Ymd\THis\Z'),
            'DTSTART;VALUE=DATE:'.self::START,
            'DTEND;VALUE=DATE:'.self::END,
            'SUMMARY:'.self::escape($summary),
            'DESCRIPTION:'.self::escape($description.$url),
            'LOCATION:'.self::escape(self::VENUE),
            'URL:'.$url,
            'TRANSP:TRANSPARENT',
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        // CRLF: the spec says so, and Outlook is the one that checks.
        return implode("\r\n", $lines)."\r\n";
    }

    /** The same event as a Google Calendar address, for people who live there. */
    public static function googleUrl(string $locale = 'en'): string
    {
        $summary = $locale === 'ro'
            ? 'Why Culture Matters 2026 · Simpozion internațional'
            : 'Why Culture Matters 2026 · International Symposium';

        return 'https://calendar.google.com/calendar/render?'.http_build_query([
            'action' => 'TEMPLATE',
            'text' => $summary,
            'dates' => self::START.'/'.self::END,
            'location' => self::VENUE,
            'details' => url(Front2026Controller::BASE.($locale === 'ro' ? '/ro' : '').'#programme'),
        ]);
    }

    /** Escapes the few characters iCalendar treats as syntax. */
    private static function escape(string $value): string
    {
        return str_replace(["\\", ';', ',', "\n"], ['\\\\', '\;', '\,', '\n'], $value);
    }
}
