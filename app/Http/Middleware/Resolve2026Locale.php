<?php

namespace App\Http\Middleware;

use Closure;
use GeoIp2\Database\Reader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Throwable;

/**
 * Scoped to the 2026 homepage routes (/, /en, /ro) only — does not touch
 * the session-based locale switching used by the 2022/2024 pages.
 *
 * Must run before HandleInertiaRequests, which shares the current session
 * locale as a prop: setting it from inside the controller would be one
 * request too late.
 */
class Resolve2026Locale
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->routeIs('home', 'home.en', 'home.ro')) {
            return $next($request);
        }

        $locale = match ($request->route()->getName()) {
            'home.en' => 'en',
            'home.ro' => 'ro',
            default => null,
        };

        if ($locale === null) {
            if (! Session::has('locale_2026_resolved') && $this->isLikelyRomanian($request)) {
                return redirect('/ro');
            }
            $locale = 'en';
        }

        App::setLocale($locale);
        Session::put('locale', $locale);
        Session::put('locale_2026_resolved', true);

        return $next($request);
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
