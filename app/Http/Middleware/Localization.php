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
        }

        try {
            $geoIpReader = new Reader(resource_path() . '/app/GeoLite2-Country.mmdb');
            $userLocation = $geoIpReader->country($request->ip()); // '2a02:2f09:3419:af00:fd10:4ef8:8c40:d405'
            App::setLocale(strtolower($userLocation->country->isoCode ?? 'en'));
        } catch (AddressNotFoundException | InvalidDatabaseException $e) {
            App::setLocale('en');
        }

        return $next($request);
    }
}
