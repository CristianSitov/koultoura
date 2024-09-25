<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param Request $request
     * @return string|null
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param Request $request
     * @return array
     */
    public function share(Request $request): array
    {
        $navigation = [
            'en' => [
                ['name' => 'schedule', 'href' => '#schedule', 'method' => 'changeTab', 'argument' => 0],
                ['name' => 'workshops', 'href' => '#workshops', 'method' => 'changeTab', 'argument' => 2],
                ['name' => 'speakers', 'href' => '#speakers'],
                ['name' => 'venues', 'href' => '#venues'],
                ['name' => 'partners', 'href' => '#partners'],
            ],
            'ro' => [
                ['name' => 'program', 'href' => '#schedule', 'method' => 'changeTab', 'argument' => 0],
                ['name' => 'ateliere', 'href' => '#ateliere', 'method' => 'changeTab', 'argument' => 2],
                ['name' => 'invitați', 'href' => '#speakers'],
                ['name' => 'locații', 'href' => '#venues'],
                ['name' => 'parteneri', 'href' => '#partners'],
            ],
        ];

        $locale = app()->getLocale();
        $locale = in_array($locale, config('translatable.locales'), true) ? $locale : 'en';
        $translation = array_values(array_diff(config('translatable.locales'), [$locale]))[0];

        return array_merge(parent::share($request), [
            'locale' => $locale,
            'translation' => $translation,
            'navigation' => $navigation[$locale],
        ]);
    }
}
