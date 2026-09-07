<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\ProgrammeDay;
use App\Models\Session;
use App\Models\Setting;
use App\Models\Theme;
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
            'programme' => $this->programme(),
            'themeBars' => $this->themeBars(),
            'programmeVisible' => $this->programmeVisible(),
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
            'programme' => $this->programme(),
            'themeBars' => $this->themeBars(),
            'programmeVisible' => $this->programmeVisible(),
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
                    // The institution's own site, so the credit can be followed
                    // rather than only read.
                    'orgUrl' => $person->institution_url,
                    'role' => $text->role ?? '',
                    'portrait' => $person->avatar,
                    'bio' => $text->description ?? '',
                ];
            })
            ->all();
    }

    /**
     * The programme as the page consumes it. Only published days and sessions:
     * the schedule is edited for weeks before it is fit to show.
     *
     * Text is written in English and a locale without its own translation
     * falls back to it, the same rule the guest list follows.
     */
    /**
     * Whether the section is on the page at all — a switch of its own, apart
     * from what is published inside it.
     *
     * Hidden by default: the schedule is drafted long before there is anything
     * worth showing, and a section that appears half-built is worse than one
     * that has not appeared yet.
     */
    private function programmeVisible(): bool
    {
        return Setting::bool(Setting::PROGRAMME_VISIBLE);
    }

    private function programme(): array
    {
        if (! $this->programmeVisible()) {
            return [];
        }

        return ProgrammeDay::with(['theme.translations', 'sessions' => fn ($q) => $q->published(), 'sessions.translations', 'sessions.speakers'])
            ->where('published', true)
            ->orderBy('position')
            ->orderBy('date')
            ->get()
            ->map(fn (ProgrammeDay $day, int $i) => [
                'id' => $day->id,
                'num' => $day->date->format('d'),
                'day' => $i + 1,
                'name' => $this->text($day)->name ?? '',
                'theme' => $day->theme ? [
                    'numeral' => $day->theme->numeral,
                    'title' => $this->text($day->theme)->title ?? '',
                ] : null,
                'sessions' => $day->sessions->map(fn (Session $session) => [
                    'id' => $session->id,
                    // 10:00, not 10:00:00 — the page prints this as it comes.
                    'time' => substr($session->starts_at, 0, 5),
                    'kind' => $session->kind,
                    'title' => $this->text($session)->title ?? '',
                    // Who is speaking, or who it is for when nobody is named.
                    'who' => $session->speakers->pluck('full_name')->implode(', ')
                        ?: ($this->text($session)->audience ?? ''),
                    'school' => $session->school,
                    'booking' => $session->bookable && $session->slug
                        ? ['url' => self::BASE.'/sessions/'.$session->slug, 'full' => $session->isFull()]
                        : null,
                ])->all(),
            ])
            ->all();
    }

    /** The three theme bars above the grid, in their own order. */
    private function themeBars(): array
    {
        return Theme::with('translations')
            ->orderBy('position')
            ->get()
            ->map(fn (Theme $theme) => [
                'numeral' => $theme->numeral,
                'title' => $this->text($theme)->title ?? '',
            ])
            ->all();
    }

    /** The row for the current locale, or the English one it falls back to. */
    private function text($model)
    {
        return $model->translate(app()->getLocale()) ?? $model->translate('en');
    }
}
