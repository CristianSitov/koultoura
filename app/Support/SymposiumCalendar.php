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

    /*
     * As the venue writes it, and as a map will find it. This goes into the
     * .ics people save and into the Google Calendar link, so it is the address
     * a phone will navigate to on the morning — it was wrong, naming a street
     * FABER is not on.
     */
    public const VENUE = 'FABER, Splaiul Peneș Curcanul 4-5, 300124 Timișoara, Romania';

    /** The .ics body, for attaching to an email. */
    public static function ics(string $locale = 'en'): string
    {
        $summary = $locale === 'ro'
            ? 'Why Culture Matters 2026 · Simpozion internațional'
            : 'Why Culture Matters 2026 · International Symposium';

        /*
         * The event's own page, not the programme anchor: there is nothing
         * under it yet, and a calendar entry is read months later, when a link
         * that went nowhere is the only thing left to go on.
         */
        $description = $locale === 'ro'
            ? 'A treia ediție a simpozionului Why Culture Matters, organizat de Asociația Prin Banat. Detalii: '
            : 'The third edition of the Why Culture Matters symposium, organised by Asociația Prin Banat. Details: ';

        $url = url(Front2026Controller::base().($locale === 'ro' ? '/ro' : ''));

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
            'details' => url(Front2026Controller::base().($locale === 'ro' ? '/ro' : '')),
        ]);
    }

    /** Escapes the few characters iCalendar treats as syntax. */
    private static function escape(string $value): string
    {
        return str_replace(["\\", ';', ',', "\n"], ['\\\\', '\;', '\,', '\n'], $value);
    }
}
