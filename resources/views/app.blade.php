<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', session()->get('locale')) }}" class="scroll-smooth">
    <head>
        <link rel="apple-touch-icon" sizes="180x180" href="/assets/images/fav/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/assets/images/fav/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/assets/images/fav/favicon-16x16.png">
        <link rel="manifest" href="/assets/images/fav/site.webmanifest">

        <meta name="theme-color" content="#e02424">
        <meta name="msapplication-navbutton-color" content="#e02424">
        <meta name="apple-mobile-web-app-status-bar-style" content="#e02424">

        <meta charset="utf-8">

        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{--
            The sharing card.

            Everything here is escaped and plain: a scraper strips the markup
            out of a meta attribute, which is how the old description reached
            Facebook with the words inside its <span>s missing — "The second
            edition of the addresses two main topics".

            The addresses are built by the helpers rather than glued to
            APP_URL. That concatenation needed a trailing slash nobody
            guarantees, and produced ".euassets/images/..." without one — an
            address no scraper can fetch, so there was no thumbnail at all.
            env() is gone with it: it reads nothing once the config is cached.
        --}}
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:type" content="website">
        <meta property="og:title" content="{{ __('Why Culture Matters International Symposium') }}">
        <meta property="og:description" content="{{ __('og.description') }}">
        <meta property="og:image" content="{{ asset('assets/images/og/wcm-2026.png') }}">
        <meta property="og:image:type" content="image/png">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:image:alt" content="{{ __('og.image_alt') }}">

        {{-- Without this X shows a small square crop instead of the card. --}}
        <meta name="twitter:card" content="summary_large_image">

        <title inertia>{{ config('app.name', '?') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&family=Nunito+Sans:wght@300;400;700&family=Playfair+Display:ital,wght@0,400;0,500;1,400;1,500&family=Poppins:wght@200;400;700&display=swap" rel="stylesheet">
        <!-- 3rd parties -->
        <link href="https://api.mapbox.com/mapbox-gl-js/v2.9.2/mapbox-gl.css" rel="stylesheet" />

        <!-- Scripts -->
@routes

@vite('resources/js/app.js')

@inertiaHead

    </head>
    <body class="font-sans antialiased">

@inertia

    </body>
</html>
