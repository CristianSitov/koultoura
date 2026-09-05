<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class Front2026Controller extends Controller
{
    /**
     * Locale (redirect/session) is resolved by Resolve2026Locale middleware
     * before this runs — index/en/ro all just render the same page.
     */
    public function index(): Response
    {
        return Inertia::render('2026/Home');
    }

    public function en(): Response
    {
        return Inertia::render('2026/Home');
    }

    public function ro(): Response
    {
        return Inertia::render('2026/Home');
    }

    /**
     * The full 2026 landing page at /2026. Public but unlisted while it is
     * reviewed: nothing links to it and the page sends `noindex, nofollow`.
     *
     * When it replaces the announcement page, redirect '/' here (see the note
     * in routes/web.php) and drop the noindex from Landing.vue.
     */
    public function landing(): Response
    {
        return Inertia::render('2026/Landing');
    }
}
