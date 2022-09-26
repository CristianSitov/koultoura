<?php

namespace App\Http\Middleware;

use Closure;
use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use MaxMind\Db\Reader\InvalidDatabaseException;

class Localization
{
    public function handle(Request $request, Closure $next): Response|RedirectResponse|JsonResponse
    {
        if (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        } else {
            try {
                $geoIpReader = new Reader(resource_path() . '/app/GeoLite2-Country.mmdb');
                $userLocation = $geoIpReader->country($request->ip());
                $locale = strtolower($userLocation->country->isoCode ?? 'en');
                App::setLocale($locale);
                Session::put('locale', $locale);
            } catch (AddressNotFoundException | InvalidDatabaseException $e) {
                App::setLocale('en');
            }
        }

        return $next($request);
    }
}
