<script setup>
import {CalendarIcon, LocationMarkerIcon} from '@heroicons/vue/outline'
</script>

<template>
    <div class="snap-start scroll-m-0 snap-both">
        <div class="bg-white overflow-hidden"
             style="background: url(https://preview.colorlib.com/theme/wemeet/img/banner/banner_1.png.webp) no-repeat; background-size: cover;">
            <div class="w-full mx-auto">
                <div class="relative z-10 mb-8 sm:mb-16 md:mb-20 lg:mb-28 xl:mb-32 h-4/5">
                    <div class="my-10 mx-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
                        <div class="sm:text-left">
                            <h1 class="text-6xl my-44 md:text-8xl tracking-wide text-white">
                                <span class="block text-xl sm:text-2xl font-brave font-bold my-6 uppercase">International Symposium</span>
                                <span class="block xl:inline leading-tight font-brave font-bold">why culture matters?</span>
                                <span class="block text-xl md:text-4xl font-brave font-bold my-6 md:mt-14">
                                    <span class="inline-flex"><CalendarIcon class="h-7 md:h-9 sm:h-7 w-7 md:w-9 sm:w-7"
                                                                            aria-hidden="true"/>6-7-8 October 2022</span>
                                </span>
                                <span class="block text-xl md:text-4xl font-brave font-bold my-6 md:mb-28">
                                    <span class="inline-flex"><LocationMarkerIcon
                                        class="h-7 md:h-9 sm:h-7 w-7 md:w-9 sm:w-7" aria-hidden="true"/>Timișoara, Romania</span>
                                </span>
                            </h1>
                            <p class="my-5 text-base text-white sm:mt-5 sm:text-lg sm:max-w-xl md:mt-5 md:text-xl lg:mx-0">
                                Conference focused on material and immaterial heritage, relationships between center and
                                periphery, and migration.</p>
                            <div class="my-5 sm:my-8 sm:flex justify-start">
                                <a href="/"
                                   class="inline-block px-6 py-2 border-2 text-white font-bold bg-transparent border-white leading-snug uppercase rounded hover:bg-white hover:bg-opacity-50 focus:outline-none focus:ring-0 transition duration-250 ease-in-out">
                                    Make reservation</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
