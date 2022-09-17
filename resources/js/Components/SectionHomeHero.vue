<script setup>
import {CalendarIcon, LocationMarkerIcon} from '@heroicons/vue/outline'
</script>

<template>
    <div class="snap-start scroll-m-0 snap-both">
        <div class="bg-white overflow-hidden min-h-screen"
             style="background: url(/assets/images/propunere_background_site_wcm_2.png) no-repeat; background-size: cover;">
            <div class="w-full mx-auto">
                <div class="relative z-10 mb-8 sm:mb-16 md:mb-20 lg:mb-28 xl:mb-32 h-4/5">
                    <div class="max-w-7xl my-10 mx-auto px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
                        <div class="text-center">
                            <h1 class="text-6xl my-44 md:text-8xl tracking-wide text-white">
                                <span class="block text-xl sm:text-2xl font-brave font-bold my-6 uppercase">{{ $t('International Symposium') }}</span>
                                <span class="block text-9xl leading-tight font-brave font-bold">why culture matters?</span>
                                <span class="block text-xl md:text-4xl font-brave font-bold my-6 md:mt-14">
                                    <span class="inline-flex"><CalendarIcon class="h-7 md:h-9 sm:h-7 w-7 md:w-9 sm:w-7"
                                                                            aria-hidden="true"/>{{ $t('6-7-8 October 2022') }}</span>
                                </span>
                                <span class="block text-xl md:text-4xl font-brave font-bold my-6 md:mb-28">
                                    <span class="inline-flex"><LocationMarkerIcon
                                        class="h-7 md:h-9 sm:h-7 w-7 md:w-9 sm:w-7" aria-hidden="true"/>Timișoara, Romania</span>
                                </span>
                            </h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
