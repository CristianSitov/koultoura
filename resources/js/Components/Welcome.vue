<script setup>
import {CalendarIcon, LocationMarkerIcon} from '@heroicons/vue/outline'
</script>

<template>
    <div>
        <div class="p-8 bg-white border-b border-gray-200">
            <div class="text-2xl">
                <h3 v-html="$t('hello', {name: $page.props.user.name})"></h3>
                <p v-html="$t('thank you title')"></p>
            </div>

            <div class="mt-6 text-gray-500">
                <p v-html="$t('thank you subscription')"></p>
            </div>
        </div>

        <div class="flex flex-col bg-gray-200 bg-opacity-25" v-if="$page.props.profile.event_details.days.includes('1')">
            <div class="w-7xl p-4 px-8 pt-12">
                <h3 class="font-medium text-gray-700 text-xl leading-5">{{ $t('Day :day', {day: 1}) }} &mdash; 6 oct &mdash; {{ $t('schedule short one title') }}</h3>
                <div class="mt-3 mb-5">
                <span class="block">
                    <span class="text-sm inline-flex align-middle leading-6"><CalendarIcon
                        class="h-5 md:h-6 sm:h-4 w-5 md:w-7 sm:w-5 mr-3"
                        aria-hidden="true"
                    />{{ $t('schedule short one hours') }}</span>
                </span>
                    <span class="block">
                    <span class="text-sm inline-flex align-middle leading-6"><LocationMarkerIcon
                        class="h-5 md:h-6 sm:h-4 w-5 md:w-7 sm:w-5 mr-3"
                        aria-hidden="true"
                    />{{ $t('schedule short one location') }}</span>
                </span>
                </div>
            </div>
            <div class="w-7xl p-4 px-8" v-if="$page.props.profile.event_details.days.includes('2')">
                <h3 class="font-medium text-gray-700 text-xl leading-5">{{ $t('Day :day', {day: 2}) }} &mdash; 7 oct &mdash; {{ $t('schedule short two title') }}</h3>
                <div class="mt-3 mb-5">
                <span class="block">
                    <span class="text-sm inline-flex align-middle leading-6"><CalendarIcon
                        class="h-5 md:h-6 sm:h-4 w-5 md:w-7 sm:w-5 mr-3"
                        aria-hidden="true"
                    />{{ $t('schedule short two hours') }}</span>
                </span>
                    <span class="block">
                    <span class="text-sm inline-flex align-middle leading-6"><LocationMarkerIcon
                        class="h-5 md:h-6 sm:h-4 w-5 md:w-7 sm:w-5 mr-3"
                        aria-hidden="true"
                    />{{ $t('schedule short two location') }}</span>
                </span>
                </div>
            </div>
            <div class="w-7xl p-4 px-8" v-if="$page.props.profile.event_details.days.includes('3')">
                <h3 for="event_details_day3"
                       class="font-medium text-gray-700 text-xl leading-5">{{ $t('Day :day', {day: 3}) }} &mdash; 8 oct &mdash; {{ $t('schedule short three title') }}</h3>
                <div class="mt-3 mb-5">
                <span class="block">
                    <span class="text-sm inline-flex align-middle leading-6"><CalendarIcon
                        class="h-5 md:h-6 sm:h-4 w-5 md:w-7 sm:w-5 mr-3"
                        aria-hidden="true"
                    />{{ $t('schedule short three hours') }}</span>
                </span>
                    <span class="block">
                    <span class="text-sm inline-flex align-middle leading-6"><LocationMarkerIcon
                        class="h-5 md:h-6 sm:h-4 w-5 md:w-7 sm:w-5 mr-3"
                        aria-hidden="true"
                    />{{ $t('schedule short three location') }}</span>
                </span>
                </div>
            </div>
        </div>

        <div class="p-8 bg-white border-t border-gray-200">
            <p v-html="$t('thank you closing')"></p>
            <br />
            <p v-html="$t('thank you footer')"></p>
        </div>
    </div>
</template>
