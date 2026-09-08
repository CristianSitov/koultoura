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

        /*
         * The 2026 pages carry their locale as a route parameter, so read it
         * from there rather than listing route names — a list silently fails
         * open on the next route added, rendering English at a /ro address.
         */
        $locale = $request->route('locale') ?? match ($name) {
            'home.en' => 'en',
            'home.ro' => 'ro',
            default => null,
        };

        if ($locale === null) {
            /*
             * Only the landing page's own addresses are authoritatively
             * English — they are what hreflang points at. Everything else
             * without a locale in the URL (form posts, the pages you are
             * redirected to after one) keeps the language you were already
             * reading in; forcing English there sent a Romanian visitor an
             * English email.
             */
            $canonical = in_array($name, ['home', '2026.home', '2026.guest'], true);

            if (! $canonical) {
                $locale = Session::get('locale', 'en');
            } elseif (! Session::has('locale_2026_resolved') && $this->isLikelyRomanian($request)) {
                return redirect($this->romanianUrl($request, $name));
            } else {
                $locale = 'en';
            }
        }

        App::setLocale($locale);
        Session::put('locale', $locale);
        Session::put('locale_2026_resolved', true);

        return $next($request);
    }

    /**
     * Where a first-time Romanian visitor should land, keeping whatever page
     * they asked for rather than dropping them on a homepage: the same path
     * with /ro spliced in after the landing page's base.
     */
    private function romanianUrl(Request $request, ?string $name): string
    {
        if (! str_starts_with((string) $name, '2026.')) {
            return '/ro';
        }

        $base = Front2026Controller::base();
        $rest = substr('/'.ltrim($request->path(), '/'), strlen($base));

        return $base.'/ro'.$rest;
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
