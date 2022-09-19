<script setup>
import {CalendarIcon, LocationMarkerIcon} from '@heroicons/vue/outline'
import {InertiaLink} from '@inertiajs/inertia-vue3'
</script>

<template>
    <div class="snap-start scroll-m-0 snap-both">
        <div class="bg-white"
             style="background: url(/why-culture-matters/assets/images/propunere_background_site_wcm_2.png) no-repeat; background-size: cover;">
            <div class="w-full mx-auto">
                <div class="">
                    <div class="max-w-7xl mx-auto my-auto px-4 py-44">
                        <div class="flex flex-col align-middle text-center">
                            <h1 class="text-6xl md:text-8xl tracking-wide text-white">
                                <span class="block text-xl sm:text-2xl font-brave my-6 uppercase">{{ $t('International Symposium') }}</span>
                                <span class="block text-9xl font-brave font-bold wcm front">why culture matters?</span>
                                <span class="block text-xl md:text-4xl font-brave font-bold my-6 md:mt-14">
                                    <span class="inline-flex"><CalendarIcon class="h-7 md:h-9 sm:h-7 w-7 md:w-9 sm:w-7 mr-3"
                                                                            aria-hidden="true"/>{{ $t('6-7-8 October 2022') }}</span>
                                </span>
                                <span class="block text-xl md:text-4xl font-brave font-bold my-6 md:mb-18">
                                    <span class="inline-flex"><LocationMarkerIcon class="h-7 md:h-9 sm:h-7 w-7 md:w-9 sm:w-7 mr-3"
                                                                                  aria-hidden="true"/>Timișoara, Romania</span>
                                </span>
                            </h1>
                            <div class="my-14">
                                <InertiaLink
                                    :href="route('register')"
                                    class="text-3xl uppercase text-white font-bold py-4 px-52 rounded-lg border-white border-4 bg-transparent hover:text-red-600 hover:bg-white"
                                >{{ $t('Register') }}</InertiaLink>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
