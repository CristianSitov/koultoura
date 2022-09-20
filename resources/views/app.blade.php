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
        <meta property="og:title" content="{!! __('Why Culture Matters? Internation Symposium') !!}">
        <meta property="og:image" content="{{ env('APP_URL') }}assets/images/event_banner.jpg">
        <meta property="og:description" content="{!! __('Event') !!}">

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

        <script src="https://unpkg.com/flowbite@1.5.3/dist/flowbite.js"></script>
    </head>
    <body class="font-sans antialiased">

@inertia

        <div id="banner" tabindex="-1" aria-hidden="false" class="overflow-y-auto overflow-x-hidden fixed z-50 w-full md:inset-0 h-modal md:h-full">
            <div class="relative w-full h-full md:h-auto">
                <div class="relative bg-white shadow dark:bg-gray-800">
                    <div class="justify-between items-center p-5 lg:flex">
                        <p class="mb-4 text-sm text-gray-500 dark:text-white lg:mb-0">{!! __('cookie policy') !!}</p>
                        <div class="items-center space-y-4 sm:space-y-0 sm:space-x-4 sm:flex lg:pl-10 shrink-0">
                            <button id="accept-cookies" type="button"
                                    class="py-2 px-4 w-full text-sm rounded-sm bg-white font-medium text-center text-red-600 rounded-lg bg-primary-700 sm:w-auto hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300">Accept</button>
                            <button id="close-modal" type="button" class="hidden md:flex text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // set cookie according to you
            const cookieName= "WhyCultureMatters";
            const cookieValue="CookieConsent";
            const cookieExpireDays= 30;

            const bannerEl = document.getElementById('banner');
            const banner = new Modal(bannerEl, {
                placement: 'bottom-left'
            });

            const closeModalEl = document.getElementById('close-modal');
            closeModalEl.addEventListener('click', function() {
                banner.hide();
            });

            const acceptCookiesEl = document.getElementById('accept-cookies');
            acceptCookiesEl.addEventListener('click', function() {
                createCookie(cookieName, cookieValue, cookieExpireDays);
                banner.hide();
            });

            // function to set cookie in web browser
            let createCookie = function(cookieName, cookieValue, cookieExpireDays){
                let currentDate = new Date();
                currentDate.setTime(currentDate.getTime() + (cookieExpireDays*24*60*60*1000));
                let expires = "expires=" + currentDate.toGMTString();
                document.cookie = cookieName + "=" + cookieValue + ";" + expires + ";path=/";
                if(document.cookie){
                    banner.hide();
                } else {
                    console.log("Unable to set cookie. Please allow all cookies site from cookie setting of your browser");
                }
            }
            // get cookie from the web browser
            let getCookie= function(cookieName){
                let name = cookieName + "=";
                let decodedCookie = decodeURIComponent(document.cookie);
                let ca = decodedCookie.split(';');
                for(let i = 0; i < ca.length; i++) {
                    let c = ca[i];
                    while (c.charAt(0) === ' ') {
                        c = c.substring(1);
                    }
                    if (c.indexOf(name) === 0) {
                        return c.substring(name.length, c.length);
                    }
                }
                return "";
            }
            // check cookie is set or not
            let checkCookie= function(){
                let check = getCookie(cookieName);
                if(check === ""){
                    banner.show();
                } else {
                    banner.hide();
                }
            }
            checkCookie();
        </script>

        <script async src="https://www.googletagmanager.com/gtag/js?id=G-WYGPJKWNT1"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            gtag('config', 'G-WYGPJKWNT1');
        </script>
    </body>
</html>
