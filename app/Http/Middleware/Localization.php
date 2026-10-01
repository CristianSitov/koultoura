<?php

namespace App\Http\Middleware;

use Closure;
use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use MaxMind\Db\Reader\InvalidDatabaseException;
use Symfony\Component\HttpFoundation\Response;

class Localization
{
    /*
     * The Symfony base type, not a list of Laravel ones: a streamed download
     * (the registrations CSV) or a file response is neither a Response, a
     * redirect nor JSON, and a narrower type here turned those into a 500.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Session::has('locale')) {
            App::setLocale(Session::get('locale'));

            return $next($request);
        }

        // read browser language
        $browserLanguage = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : 'en';
        if (in_array($browserLanguage, config('app.locales'))) {
            App::setLocale($browserLanguage);
            Session::put('locale', $browserLanguage);

            return $next($request);
        }

        return $next($request);
    }
}
