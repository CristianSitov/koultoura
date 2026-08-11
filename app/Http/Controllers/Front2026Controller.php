<?php

namespace App\Http\Controllers;

use GeoIp2\Database\Reader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class Front2026Controller extends Controller
{
    /**
     * Landing route: sends Romanian visitors (by IP or browser language) to
     * /ro on their first visit this session, everyone else sees the English
     * page here directly (no redirect).
     */
    public function index(Request $request): Response|RedirectResponse
    {
        if (! Session::has('locale_2026_resolved') && $this->isLikelyRomanian($request)) {
            return redirect('/ro');
        }

        return $this->render('en');
    }

    public function en(): Response
    {
        return $this->render('en');
    }

    public function ro(): Response
    {
        return $this->render('ro');
    }

    private function render(string $locale): Response
    {
        App::setLocale($locale);
        Session::put('locale', $locale);
        Session::put('locale_2026_resolved', true);

        return Inertia::render('2026/Home');
    }

    private function isLikelyRomanian(Request $request): bool
    {
        $browserLanguage = strtolower(substr($request->server('HTTP_ACCEPT_LANGUAGE', ''), 0, 2));
        if ($browserLanguage === 'ro') {
            return true;
        }

        try {
            $reader = new Reader(resource_path('app/GeoLite2-Country.mmdb'));
            $record = $reader->country($request->ip());

            return strtoupper($record->country->isoCode ?? '') === 'RO';
        } catch (Throwable $e) {
            // Unknown/local IPs, missing DB, malformed address, etc. — never let
            // geo-lookup failures break the homepage, just fall back to English.
            Log::debug('GeoIP lookup failed for locale detection', ['ip' => $request->ip(), 'error' => $e->getMessage()]);

            return false;
        }
    }
}
