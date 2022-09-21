<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', session()->get('locale')) }}" class="scroll-smooth">
    <head>
        <link rel="apple-touch-icon" sizes="180x180" href="assets/images/fav/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="assets/images/fav/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="assets/images/fav/favicon-16x16.png">
        <link rel="manifest" href="assets/images/fav/site.webmanifest">

        <meta charset="utf-8">

        <meta name="viewport" content="width=device-width, initial-scale=1">

        <meta property="og:url" content="{{ env('APP_URL') }}">
        <meta property="og:type" content="website">
        <meta property="og:title" content="{!! __('Why Culture Matters? International Symposium') !!}">
        <meta property="og:image" content="{{ env('APP_URL') }}/assets/images/event_banner.jpg">
        <meta property="og:description" content="{!! __('Event') !!}">

        <title inertia>{{ config('app.name', '?') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;700&family=Playfair+Display:ital,wght@0,400;0,500;1,400;1,500&family=Poppins:wght@200;400;700&display=swap" rel="stylesheet">
        <!-- 3rd parties -->
        <link href="https://api.mapbox.com/mapbox-gl-js/v2.9.2/mapbox-gl.css" rel="stylesheet" />

        <!-- Scripts -->
@routes
@vite('resources/js/app.js')
@inertiaHead

        <script src="https://unpkg.com/flowbite@1.5.3/dist/flowbite.js"></script>
    </head>
    <body class="font-sans antialiased">

@inertia

    </body>
</html>
