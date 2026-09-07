<script setup>
import {CalendarIcon, LocationMarkerIcon} from '@heroicons/vue/outline'
import {Link} from '@inertiajs/inertia-vue3'
</script>

<template>
    <div id="home" class="snap-start scroll-m-0 snap-both max-w-7xl mx-auto my-auto px-4 py-48">
        <div class="flex flex-col align-middle text-center">
            <h1 class="text-2xl sm:text-6xl md:text-8xl tracking-wide text-white z-10">
                <span class="block text-2xl sm:text-xl  md:text-2xl font-brave my-6 uppercase">{{ $t('International Symposium') }}</span>
                <span class="block text-6xl sm:text-5xl md:text-9xl font-brave font-bold drop-shadow-md uppercase">why culture matters</span>
                <span class="block sm:text-xl  md:text-4xl font-brave font-bold my-6 md:mt-14">
                                    <span class="inline-flex"><CalendarIcon class="h-7 md:h-9 sm:h-7 w-7 md:w-9 sm:w-7 mr-3"
                                                                            aria-hidden="true"/>{{ $t('17-18-19 October 2024') }}</span>
                                </span>
                <span class="block sm:text-xl  md:text-4xl font-brave font-bold my-6 md:mb-18">
                                    <span class="inline-flex"><LocationMarkerIcon class="h-7 md:h-9 sm:h-7 w-7 md:w-9 sm:w-7 mr-3"
                                                                                  aria-hidden="true"/>Timișoara, Romania</span>
                                </span>
            </h1>
        </div>
    </div>
</template>
