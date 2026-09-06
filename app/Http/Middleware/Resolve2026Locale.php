<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Front2026Controller;
use Closure;
use GeoIp2\Database\Reader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Throwable;

/**
 * Scoped to the 2026 pages — the announcement homepage (/, /en, /ro) and the
 * landing page (/2026, /2026/{locale}, and the guest profiles under both).
 * Does not touch the session-based locale switching used by 2022/2024.
 *
 * Must run before HandleInertiaRequests, which shares the current session
 * locale as a prop: setting it from inside the controller would be one
 * request too late.
 */
class Resolve2026Locale
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->routeIs('home', 'home.en', 'home.ro', '2026.*')) {
            return $next($request);
        }

        $name = $request->route()->getName();

        // The landing page carries its locale in the URL: /2026/ro/...
        $locale = match ($name) {
            'home.en' => 'en',
            'home.ro' => 'ro',
            '2026.locale', '2026.locale.guest' => $request->route('locale'),
            default => null,
        };

        if ($locale === null) {
            if (! Session::has('locale_2026_resolved') && $this->isLikelyRomanian($request)) {
                return redirect($this->romanianUrl($request, $name));
            }
            $locale = 'en';
        }

        App::setLocale($locale);
        Session::put('locale', $locale);
        Session::put('locale_2026_resolved', true);

        return $next($request);
    }

    /**
     * Where a first-time Romanian visitor should land, keeping whatever page
     * they asked for rather than dropping them on a homepage.
     */
    private function romanianUrl(Request $request, ?string $name): string
    {
        return match ($name) {
            '2026.home' => Front2026Controller::BASE.'/ro',
            '2026.guest' => Front2026Controller::BASE.'/ro/guests/'.$request->route('slug'),
            default => '/ro',
        };
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
