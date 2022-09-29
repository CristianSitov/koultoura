<script setup>
import {CalendarIcon, LocationMarkerIcon} from '@heroicons/vue/outline'
import {Link} from '@inertiajs/inertia-vue3'
</script>

<template>
    <div class="snap-start scroll-m-0 snap-both">
        <div class="bg-red-700 bg-cover bg-no-repeat bg-[url('@/../assets/images/wall_1.jpg')]">
            <div class="w-full mx-auto">
                <div class="">
                    <div class="max-w-7xl mx-auto my-auto px-4 py-44">
                        <div class="flex flex-col align-middle text-center">
                            <h1 class="text-2xl sm:text-6xl md:text-8xl tracking-wide text-white">
                                <span class="block text-2xl sm:text-xl  md:text-2xl font-brave my-6 uppercase">{{ $t('International Symposium') }}</span>
                                <span class="block text-6xl sm:text-5xl md:text-9xl font-brave font-bold uppercase">why culture matters?</span>
                                <span class="block sm:text-xl  md:text-4xl font-brave font-bold my-6 md:mt-14">
                                    <span class="inline-flex"><CalendarIcon class="h-7 md:h-9 sm:h-7 w-7 md:w-9 sm:w-7 mr-3"
                                                                            aria-hidden="true"/>{{ $t('6-7-8 October 2022') }}</span>
                                </span>
                                <span class="block sm:text-xl  md:text-4xl font-brave font-bold my-6 md:mb-18">
                                    <span class="inline-flex"><LocationMarkerIcon class="h-7 md:h-9 sm:h-7 w-7 md:w-9 sm:w-7 mr-3"
                                                                                  aria-hidden="true"/>Timișoara, Romania</span>
                                </span>
                            </h1>
                            <div class="my-14">
                                <Link
                                    :href="route('register')"
                                    replace
                                    class="text-2xl md:text-3xl uppercase text-white font-bold py-4 px-12 lg:px-52 rounded-lg border-white border-4 bg-transparent hover:text-red-600 hover:bg-white"
                                >{{ $t('Register') }}</Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
