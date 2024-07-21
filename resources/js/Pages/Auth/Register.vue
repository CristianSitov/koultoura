<script setup>
import {Head, Link, useForm} from '@inertiajs/inertia-vue3';
import {CalendarIcon, LocationMarkerIcon} from '@heroicons/vue/outline'
import JetAuthenticationCard from '@/Components/AuthenticationCard.vue';
import JetButton from '@/Components/Button.vue';
import JetInput from '@/Components/Input.vue';
import JetInputError from '@/Components/InputError.vue';
import JetCheckbox from '@/Components/Checkbox.vue';
import JetLabel from '@/Components/Label.vue';

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    email_confirmation: '',
    phone: '',
    job: '',
    organization: '',
    country: '',
    event_details: [],
});

const submit = () => {
    form.post(route('event-registration'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>
<script>
import { consentOptions } from "@/consent";
import emitter from "@/emitter";

export default {
    beforeCreate() {
        this.$cc.on('consent-changed', () => {
            console.log('cookie consent changed, new user preferences:',
                this.$cc.getUserPreferences())
        })
    },
    created() {
        this.$cc.run(consentOptions);
        emitter.on("consentAccepted", function (consent) {
            console.log(consent)
            this.$gtag.optIn()
        });
    },
    mounted() {
        this.track()
    },
    updated() {
        console.log(this.$route)
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
    <Head title="Register" />

    <JetAuthenticationCard width="large">
        <div class="text-center my-8">
            <span class="text-3xl text-black font-sans font-bold uppercase"><Link :href="route('root')">why culture matters<span class="text-red-600">?</span></Link></span><br />
            <span class="text-xl text-black font-sans font-bold">{{ $t('International Symposium') }}</span><br />
            <span class="text-xl text-black font-sans font-bold">{{ $t('6-7-8 October 2022') }}</span>
        </div>

        <div class="mt-10 sm:mt-0">
            <form @submit.prevent="submit">
            <div class="md:grid md:grid-cols-3 md:gap-6">
                <div class="md:col-span-1">
                    <div class="px-4 sm:px-0">
                        <h3 class="text-2xl text-center uppercase font-medium leading-6 my-10 text-gray-900">{{ $t('Registration form') }}</h3>
                        <p class="mt-1 text-center text-sm text-gray-600" v-html="$t('Personal Information details')"></p>
                    </div>
                </div>
                <div class="mt-5 md:col-span-2 md:mt-0">
                    <div class="overflow-hidden shadow sm:rounded-md">
                        <div class="bg-white px-4 py-5 sm:p-6">
                            <div class="grid grid-cols-6 gap-6">
                                <div class="col-span-6 sm:col-span-3">
                                    <JetLabel for="first-name" :value="$t('First name')"
                                        class="block text-sm font-medium text-gray-700"/>
                                    <JetInput
                                        id="first-name"
                                        v-model="form.first_name"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                        required
                                        autofocus
                                        autocomplete="given-name"
                                    />
                                    <JetInputError class="mt-2" :message="form.errors.first_name" />
                                </div>

                                <div class="col-span-6 sm:col-span-3">
                                    <JetLabel for="last-name" :value="$t('Last name')"
                                              class="block text-sm font-medium text-gray-700"/>
                                    <JetInput
                                        id="last-name"
                                        v-model="form.last_name"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                        required
                                        autocomplete="family-name"
                                    />
                                    <JetInputError class="mt-2" :message="form.errors.last_name" />
                                </div>

                                <div class="col-span-6 sm:col-span-3">
                                    <JetLabel for="email" :value="$t('Email address')"
                                              class="block text-sm font-medium text-gray-700"/>
                                    <JetInput
                                        id="email"
                                        v-model="form.email"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                        required
                                        autocomplete="email"
                                    />
                                    <JetInputError class="mt-2" :message="form.errors.email" />
                                </div>

                                <div class="col-span-6 sm:col-span-3">
                                    <JetLabel for="confirm-email" :value="$t('Confirm email')"
                                              class="block text-sm font-medium text-gray-700"/>
                                    <JetInput
                                        id="confirm-email"
                                        v-model="form.email_confirmation"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                        required
                                        autocomplete="email"
                                    />
                                    <JetInputError class="mt-2" :message="form.errors.email_confirmation" />
                                </div>

                                <div class="col-span-6 sm:col-span-3">
                                    <JetLabel for="phone" :value="$t('Phone number')"
                                              class="block text-sm font-medium text-gray-700"/>
                                    <JetInput
                                        id="phone"
                                        v-model="form.phone"
                                        type="tel"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                        required
                                        autocomplete="phone"
                                    />
                                    <JetInputError class="mt-2" :message="form.errors.phone" />
                                </div>
                                <div class="col-span-6 sm:col-span-3"></div>


                                <div class="col-span-6 sm:col-span-3">
                                    <JetLabel for="organization" :value="$t('Organisation')"
                                              class="block text-sm font-medium text-gray-700"/>
                                    <JetInput
                                        id="organization"
                                        v-model="form.organization"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                        required
                                        autocomplete="organization"
                                    />
                                    <JetInputError class="mt-2" :message="form.errors.organization"/>
                                </div>

                                <div class="col-span-6 sm:col-span-3">
                                    <JetLabel for="job" :value="$t('Job title')"
                                              class="block text-sm font-medium text-gray-700"/>
                                    <JetInput
                                        id="job"
                                        v-model="form.job"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                        required
                                        autocomplete="job"
                                    />
                                    <JetInputError class="mt-2" :message="form.errors.job" />
                                </div>

                                <div class="col-span-6 sm:col-span-3">
                                    <label for="country" class="block text-sm font-medium text-gray-700">{{ $t('Country') }}</label>
                                    <select id="country" name="country" autocomplete="country"
                                            v-model="form.country"
                                            class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                                        <option v-for="(country, index) in $page.props.countries"
                                                v-bind:selected="index.toLowerCase() === $page.props.locale"
                                                :value="index">{{ country }}</option>
                                    </select>
                                    <JetInputError class="mt-2" :message="form.errors.country" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-1">
                    <div class="px-4 sm:px-0">
                        <h3 class="text-lg text-center font-medium leading-6 text-gray-900">{{ $t('Preferences') }}</h3>
                    </div>
                </div>
                <div class="mt-5 md:col-span-2 md:mt-0">
                    <div class="overflow-hidden shadow sm:rounded-md">
                        <div class="space-y-6 bg-white px-4 py-5 sm:p-6">
                            <fieldset>
                                <div class="space-y-4">
                                    <div class="flex items-start"
                                        v-for="idx in ['1', '2', '3']">
                                        <div class="flex h-5 items-center">
                                            <JetCheckbox
                                                :id="'event_details_day'+idx"
                                                :value="idx"
                                                v-model:checked="form.event_details"
                                                :disabled="idx === '1'"
                                                :class="{'appearance-none bg-gray-300': idx === '1'}"
                                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"></JetCheckbox>
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label :for="'event_details_day'+idx"
                                                   class="font-medium text-gray-700 text-xl leading-5"
                                                   :class="{'text-gray-300': idx === '1'}">{{ $t('Day '+idx) }}
                                                <span v-if="idx === '1'"
                                                      class="text-red-600"><br />{{ $t('occupied') }}</span></label>
                                            <div class="mt-3 mb-5">
                                                <span class="block">
                                                    <span class="text-sm inline-flex align-middle leading-6"><CalendarIcon
                                                        class="h-5 md:h-6 sm:h-4 w-5 md:w-7 sm:w-5 mr-3"
                                                        aria-hidden="true"
                                                    />{{ $t('schedule short '+idx+' hours') }}</span>
                                                </span>
                                                <span class="block">
                                                    <span class="text-sm inline-flex align-middle leading-6"><LocationMarkerIcon
                                                        class="h-5 md:h-6 sm:h-4 w-5 md:w-7 sm:w-5 mr-3"
                                                        aria-hidden="true"
                                                    />{{ $t('schedule short '+idx+' location') }}</span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <JetInputError class="mt-2" :message="form.errors.event_details" />
                                </div>
                            </fieldset>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 text-right sm:px-6">
                            <JetLabel for="terms">
                                <div class="flex items-center">
                                    <div class="ml-2 text-left" v-html="$t('GDPR')">
                                    </div>
                                </div>
                            </JetLabel>

                            <div class="flex items-center mt-4">
                                <JetButton class="px-15 py-5 bg-red-600 text-xl mx-auto my-10"
                                           :class="{ 'opacity-25': form.processing }"
                                           :disabled="form.processing">
                                    {{ $t('Register me') }}
                                </JetButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </form>
        </div>

        <div class="mt-10 sm:mt-0">
            <div class="md:grid md:grid-cols-3 md:gap-6">

            </div>
        </div>
    </JetAuthenticationCard>
</template>
