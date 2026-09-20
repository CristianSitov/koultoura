<?php

namespace App\Http\Controllers;

use App\Mail\PlaceConfirmed;
use App\Models\SessionPlace;
use App\Support\PlaceCalendar;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

/*
 * A place in an internal workshop, claimed from the invitation email. The token
 * in the link is the whole key — no login, because the people invited are not
 * on the site, and the link was sent to their address and nowhere else.
 */
class Front2026PlaceController extends Controller
{
    public function confirm(Request $request): Response
    {
        $place = $this->place($request);

        // The first confirmation sends the calendar; a reload only re-shows it.
        if (! $place->isConfirmed()) {
            $place->update(['status' => 'confirmed', 'confirmed_at' => now()]);

            if (filled($place->email)) {
                Mail::to($place->email)->send(new PlaceConfirmed(
                    $place,
                    PlaceCalendar::icsUrl($place),
                    PlaceCalendar::googleUrl($place->session),
                ));
            }
        }

        $session = $place->session;
        $text = $session->translate(app()->getLocale()) ?? $session->translate('en');

        return Inertia::render('2026/PlaceConfirmed', [
            'base' => Front2026Controller::base(),
            'title' => $text->title ?? '',
            'when' => $session->day->date->translatedFormat('j F Y').' · '.substr($session->starts_at, 0, 5),
            'code' => $place->code,
            'icsUrl' => PlaceCalendar::icsUrl($place),
            'googleUrl' => PlaceCalendar::googleUrl($session),
        ]);
    }

    public function calendar(Request $request): HttpResponse
    {
        return response(PlaceCalendar::ics($this->place($request)), 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="why-culture-matters.ics"',
        ]);
    }

    private function place(Request $request): SessionPlace
    {
        return SessionPlace::with(['session.day.translations', 'session.translations'])
            ->where('token', $request->route('token'))
            ->firstOrFail();
    }
}
