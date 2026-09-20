<?php

namespace App\Support;

use App\Http\Controllers\Front2026Controller;
use App\Models\Session;
use App\Models\SessionPlace;
use Illuminate\Support\Carbon;

/**
 * The workshop as a calendar event — the same details as an .ics file and as a
 * "add to Google Calendar" link, so a confirmed guest can keep it either way.
 */
class PlaceCalendar
{
    private const VENUE = 'FABER, Splaiul Peneș Curcanul 4-5, Timișoara';
    private const TZ = 'Europe/Bucharest';

    /** [start, end] in UTC, the end two hours on when none is set. */
    private static function window(Session $session): array
    {
        $date = $session->day->date->format('Y-m-d');
        $start = Carbon::parse($date.' '.substr($session->starts_at, 0, 5), self::TZ);
        $end = filled($session->ends_at)
            ? Carbon::parse($date.' '.substr($session->ends_at, 0, 5), self::TZ)
            : $start->copy()->addHours(2);

        return [$start->utc(), $end->utc()];
    }

    private static function title(Session $session): string
    {
        return $session->translate('en')?->title ?? 'Why Culture Matters workshop';
    }

    public static function ics(SessionPlace $place): string
    {
        $session = $place->session;
        [$start, $end] = self::window($session);
        $stamp = fn (Carbon $t) => $t->format('Ymd\THis\Z');

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Why Culture Matters//2026//EN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:place-'.$place->id.'@whyculturematters.eu',
            'DTSTAMP:'.$stamp(Carbon::now()->utc()),
            'DTSTART:'.$stamp($start),
            'DTEND:'.$stamp($end),
            'SUMMARY:'.self::escape(self::title($session)),
            'LOCATION:'.self::escape(self::VENUE),
            'DESCRIPTION:'.self::escape('Your place code: '.$place->code),
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        // iCalendar folds on CRLF.
        return implode("\r\n", $lines)."\r\n";
    }

    /** The address of the .ics above, for the confirmation email. */
    public static function icsUrl(SessionPlace $place): string
    {
        return url(Front2026Controller::base().'/places/'.$place->token.'/calendar.ics');
    }

    /** A "add to Google Calendar" link with the same event pre-filled. */
    public static function googleUrl(Session $session): string
    {
        [$start, $end] = self::window($session);

        return 'https://calendar.google.com/calendar/render?'.http_build_query([
            'action' => 'TEMPLATE',
            'text' => self::title($session),
            'dates' => $start->format('Ymd\THis\Z').'/'.$end->format('Ymd\THis\Z'),
            'location' => self::VENUE,
            'details' => 'Why Culture Matters 2026',
        ]);
    }

    private static function escape(string $text): string
    {
        return str_replace([',', ';'], ['\,', '\;'], $text);
    }
}
