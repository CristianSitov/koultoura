<?php

namespace App\Http\Controllers;

use App\Models\AgendaRecipient;
use App\Support\Agenda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

/*
 * The internal agenda, opened from the email. The token in the link is the
 * whole key — no login, because the people it is sent to are not on the site,
 * and the link went to their address and nowhere else. Anything else is a 404,
 * so the page cannot be found by guessing or by a crawler.
 */
class Front2026AgendaController extends Controller
{
    public function show(Request $request): Response
    {
        $recipient = AgendaRecipient::where('token', $request->route('token'))->firstOrFail();

        // Resolve2026Locale has already switched to the recipient's language
        // for the shared props; this keeps the data built below in step.
        App::setLocale($recipient->locale);

        /*
         * Told to stay out of every index in the header as well as the page:
         * the <meta> is written by the browser, and a crawler that does not
         * run scripts never sees it. A forwarded link should lead nowhere
         * public either.
         */
        return Inertia::render('2026/Agenda', [
            'base' => Front2026Controller::base(),
            'name' => $recipient->name,
            'days' => Agenda::days($recipient->locale),
        ])->toResponse($request)->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
