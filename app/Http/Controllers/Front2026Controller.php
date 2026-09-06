<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class Front2026Controller extends Controller
{
    /*
     * The landing page lives behind an unguessable segment while it is
     * unlisted — /2026 on its own is a 404, so a crawler walking the obvious
     * years finds nothing. Change this one value to change the address; the
     * routes, the locale switch and the profile URLs all read it.
     */
    public const PATH = '2026-mulberry';

    public const BASE = '/'.self::PATH;

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
        return Inertia::render('2026/Landing', [
            'guests' => $this->guests(),
            'base' => self::BASE,
        ]);
    }

    /**
     * A guest profile at /2026/guests/{slug}: the same landing page with the
     * profile overlay already open. An unknown slug is a 404.
     */
    public function guest(Request $request): Response
    {
        // Read by name: the localised route is /2026/{locale}/guests/{slug},
        // so a positional argument would pick up the locale instead.
        $guest = Person::where('slug', $request->route('slug'))->firstOrFail();

        return Inertia::render('2026/Landing', [
            'guests' => $this->guests(),
            'guest' => $guest->slug,
            'base' => self::BASE,
        ]);
    }

    /**
     * The guest list as the page consumes it. Content is written in English;
     * a locale with no translation of its own falls back to it rather than
     * rendering a card with empty text.
     */
    private function guests(): array
    {
        return Person::with('translations')
            ->orderBy('position')
            ->orderBy('id')
            ->get()
            ->map(function (Person $person) {
                $text = $person->translate(app()->getLocale()) ?? $person->translate('en');

                return [
                    'id' => $person->slug,
                    'name' => $person->full_name,
                    'org' => $text->institution ?? '',
                    'role' => $text->role ?? '',
                    'portrait' => $person->avatar,
                    'bio' => $text->description ?? '',
                ];
            })
            ->all();
    }
}
