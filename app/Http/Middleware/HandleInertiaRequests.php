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
     * @param \Illuminate\Http\Request $request
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
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function share(Request $request): array
    {
        $locale = $request->session()->get('locale', 'en');
        $navigation = [
            'en' => [
                ['name' => 'schedule', 'href' => '#schedule'],
                ['name' => 'speakers', 'href' => '#speakers'],
                ['name' => 'venues', 'href' => '#venues'],
                ['name' => 'partners', 'href' => '#partners'],
            ],
            'ro' => [
                ['name' => 'program', 'href' => '#schedule'],
                ['name' => 'invitati', 'href' => '#speakers'],
                ['name' => 'locatii', 'href' => '#venues'],
                ['name' => 'parteneri', 'href' => '#parteneri'],
            ],
        ];

        return array_merge(parent::share($request), [
            'locale' => $locale,
            'navigation' => $navigation[$locale],
        ]);
    }
}
