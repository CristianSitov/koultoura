<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
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
                ['name' => 'schedule', 'href' => route('2024.home').'#schedule', 'method' => 'flipToTab', 'argument' => 0],
                ['name' => 'workshops', 'href' => route('2024.home').'#workshops', 'method' => 'flipToTab', 'argument' => 2],
                ['name' => 'speakers', 'href' => route('2024.home').'#speakers'],
                ['name' => 'venues', 'href' => route('2024.home').'#venues'],
                ['name' => 'partners', 'href' => route('2024.home').'#partners'],
            ],
            'ro' => [
                ['name' => 'program', 'href' => route('2024.home').'#schedule', 'method' => 'flipToTab', 'argument' => 0],
                ['name' => 'ateliere', 'href' => route('2024.home').'#ateliere', 'method' => 'flipToTab', 'argument' => 2],
                ['name' => 'invitați', 'href' => route('2024.home').'#speakers'],
                ['name' => 'locații', 'href' => route('2024.home').'#venues'],
                ['name' => 'parteneri', 'href' => route('2024.home').'#partners'],
            ],
        ];

        $locale = Session::has('locale')
            ? Session::get('locale')
            : App::getLocale();
        $translation = array_values(array_diff(config('translatable.locales'), [$locale]))[0];

        return array_merge(parent::share($request), [
            'locale' => $locale,
            'translation' => $translation,
            'navigation' => $navigation[$locale],
            'year' => date('Y'),
        ]);
    }
}
