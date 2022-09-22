<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import {CalendarIcon, LocationMarkerIcon} from '@heroicons/vue/outline'
</script>
<script>
import { consentOptions } from "../consent";
import emitter from "../emitter";

export default {
    beforeCreate() {
        this.$cc.on('consent-changed', () => {
            console.log('cookie consent changed, new user preferences:',
                this.$cc.getUserPreferences())
        })
    },
    created() {
        this.$cc.run(consentOptions);
        emitter.on("consentAccepted", () => this.$gtag.optIn());
    },
    mounted() {
        this.track()
    },
    data() {
        return {
            gTracker: this.$gtag
        }
    },
    methods: {
        track() {
            this.gTracker.pageview(this.$inertia.page.url)
            this.gTracker.event(this.$inertia.page.component)
        }
    }
}
</script>

<template>
    <AppLayout title="Registration Confirmation">
        <template #header>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-8 bg-white border-b border-gray-200">
                        <div class="text-2xl">
                            <h3 v-html="$t('hello', {name: $page.props.user.name})"></h3>
                            <p v-html="$t('thank you title')"></p>
                        </div>

                        <div class="mt-6 text-gray-500">
                            <p v-html="$t('thank you subscription')"></p>
                        </div>
                    </div>

                    <div class="flex flex-col bg-gray-200 bg-opacity-25"
                         v-for="idx in $page.props.profile.event_details.days">
                        <div class="w-7xl p-4 px-8 pt-12">
                            <h3 class="font-medium text-gray-700 text-xl leading-5">{{ $t('Day ' + idx) }}</h3>
                            <div class="mt-3 mb-5">
                                <span class="block">
                                    <span class="text-sm inline-flex align-middle leading-6"><CalendarIcon
                                        class="h-5 md:h-6 sm:h-4 w-5 md:w-7 sm:w-5 mr-3"
                                        aria-hidden="true"/>{{ $t('schedule short ' + idx + ' hours') }}</span>
                                </span>
                                <span class="block">
                                    <span class="text-sm inline-flex align-middle leading-6"><LocationMarkerIcon
                                        class="h-5 md:h-6 sm:h-4 w-5 md:w-7 sm:w-5 mr-3"
                                        aria-hidden="true"/>{{ $t('schedule short ' + idx + ' location') }}</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="p-8 bg-white border-t border-gray-200">
                        <p v-html="$t('thank you closing')"></p>
                        <br/>
                        <p v-html="$t('thank you footer', {'home': route('home')})"></p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
