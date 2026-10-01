<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Support\Agenda;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

/*
 * The agenda for speakers and guests. One page, one secret link: the office
 * sends the address from its own mail, and the code in it is the whole key —
 * no login, because the people it is for are not on the site. A wrong code is
 * a 404, the same as a page that was never there, so it cannot be found by
 * guessing or by a crawler. Resetting the code in the backoffice shuts the
 * old address for good.
 */
class Front2026AgendaController extends Controller
{
    public function show(Request $request): Response
    {
        // Read by name: under /2026/{locale}/agenda/{token} a positional
        // argument would pick up the locale instead.
        $token = Setting::text(Setting::AGENDA_TOKEN);

        abort_unless(filled($token) && hash_equals($token, (string) $request->route('token')), 404);

        /*
         * Told to stay out of every index in the header as well as the page:
         * the <meta> is written by the browser, and a crawler that does not
         * run scripts never sees it. A forwarded link should lead nowhere
         * public either.
         */
        return Inertia::render('2026/Agenda', [
            'base' => Front2026Controller::base(),
            'days' => Agenda::days(app()->getLocale()),
        ])->toResponse($request)->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
