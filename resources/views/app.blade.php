<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', session()->get('locale')) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', '?') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@1,300;1,400;1,600&family=Nunito+Sans:wght@300;400;700&family=Poppins:wght@200;400;700&display=swap" rel="stylesheet">

        <!-- 3rd parties -->
        <link href="https://api.mapbox.com/mapbox-gl-js/v2.9.2/mapbox-gl.css" rel="stylesheet" />

        <!-- Scripts -->
@routes
@vite('resources/js/app.js')
@inertiaHead

    </head>
    <body class="font-sans antialiased">

@inertia

        <script async src="https://www.googletagmanager.com/gtag/js?id=G-WYGPJKWNT1"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            gtag('config', 'G-WYGPJKWNT1');
        </script>
    </body>
</html>
