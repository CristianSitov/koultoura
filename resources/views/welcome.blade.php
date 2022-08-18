<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Why Culture Matters</title>

        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('/apple-touch-icon.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('/favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('/favicon-16x16.png') }}">
        <link rel="manifest" href="{{ asset('/site.webmanifest') }}">

        <!-- Fonts -->
        <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

        <style>
            body {
                background: #ed1c24 !important;
                font-family: 'Nunito', sans-serif;
            }
            .red{fill:#EC2227;}
            .green{fill:#046937;}
            .white{fill:#FFFFFF;}
            .black{fill:#010101;}
            .wcm-font{font-family:'Peclet-Regular', sans-serif;}
            .wcm-logo{font-size:70px; transition: transform 0.5s;}
            .wcm-tag{font-size:20px;}
            .parallax-wcm {
                max-height: 50vh;
                max-width: 90vw;
            }
        </style>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <script>
            document.addEventListener("mousemove", parallax);
            let counter = 0;
            const updateRate = 13;

            function parallax(event) {
                this.querySelectorAll(".parallax-wcm .wcm-logo").forEach((shift) => {
                    if (isTimeToUpdate()) {
                        const rando = Math.random() * 5 | 0;
                        const x = (event.pageX * rando) / 100;
                        const y = (event.pageY * rando) / 100;

                        shift.style.transform = `translateX(${x}px) translateY(${y}px)`;
                    }
                });
            }
            function isTimeToUpdate() {
                return counter++ % updateRate === 0;
            }
        </script>
    </head>
    <body class="antialiased">
        <div class="relative flex items-center justify-center min-h-screen sm:items-center py-4 sm:pt-0">
            <div id="container" class="w-full mx-auto flex justify-center items-center flex-col">
                <div class="my-1 grid grid-cols-2">
                    <div class="col-span-2">
                        <svg class="parallax-wcm" viewBox="0 0 842 526">
                            <rect x="0" class="red" width="842" height="526"/>

                            <text x="76" y="152" class="green wcm-font wcm-logo">why culture matters?</text>
                            <text x="76" y="228" class="green wcm-font wcm-logo">why culture matters?</text>
                            <text x="76" y="304" class="green wcm-font wcm-logo">why culture matters?</text>
                            <text x="76" y="380" class="green wcm-font wcm-logo">why culture matters?</text>

                            <text x="52" y="132" class="black wcm-font wcm-logo">why culture matters?</text>
                            <text x="52" y="208" class="black wcm-font wcm-logo">why culture matters?</text>
                            <text x="52" y="284" class="black wcm-font wcm-logo">why culture matters?</text>
                            <text x="52" y="360" class="black wcm-font wcm-logo">why culture matters?</text>

                            <text x="64" y="140" class="white wcm-font wcm-logo">why culture matters?</text>
                            <text x="64" y="216" class="white wcm-font wcm-logo">why culture matters?</text>
                            <text x="64" y="292" class="white wcm-font wcm-logo">why culture matters?</text>
                            <text x="64" y="368" class="white wcm-font wcm-logo">why culture matters?</text>

                            <text x="64" y="428" class="white wcm-font wcm-tag">international symposium</text>
                            <text x="484" y="428" class="white wcm-font wcm-tag">6-7-8 oct 2022, timișoara, ro</text>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
