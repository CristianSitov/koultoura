<script setup>
import { Head, useForm } from '@inertiajs/inertia-vue3';
import { computed } from 'vue';
import '../../../css/wcm2026.css';
import PageShell from '../../Sections/2026/PageShell.vue';
import SectionHead from '../../Sections/2026/SectionHead.vue';

const props = defineProps({
    base: { type: String, default: '/2026' },
    days: { type: Array, default: () => [1, 2, 3] },
});

// 7–9 October; the 10th is the workshop day and is booked separately.
const weekdays = ['wednesday', 'thursday', 'friday'];
const dates = ['07', '08', '09'];

const form = useForm({
    name: '',
    email: '',
    email_confirmation: '',
    organisation: '',
    country: '',
    phone: '',
    days: [],
    consent: false,
    // Left empty by people, filled by bots.
    website: '',
});

const chosen = computed(() => form.days.length);

function submit() {
    form.post(`${props.base}/register`, { preserveScroll: true });
}
</script>

<template>
    <Head :title="$t('Register · Why Culture Matters 2026')">
        <meta head-key="robots" name="robots" content="noindex, nofollow" />
    </Head>

    <PageShell :base="base">
        <section class="wcm26-section">
            <SectionHead n="→" :title="$t('register.title')" />

            <div class="wcm26-split">
                <div>
                    <!-- The document's ÎNSCRIERE copy, the same words the
                         landing page invites people with — then the one
                         practical sentence about what happens next. -->
                    <p class="wcm26-lead" style="max-width: 22ch">{{ $t('register.band.heading') }}</p>
                    <p class="wcm26-body" style="margin-top: 28px; max-width: 46ch" v-html="$t('register.band.terms')"></p>
                    <p class="wcm26-body" style="margin-top: 20px; max-width: 46ch">
                        <span v-html="$t('register.intro')"></span>
                    </p>
                </div>

                <form class="wcm26-form" novalidate @submit.prevent="submit">
                    <div class="wcm26-field">
                        <label class="wcm26-label" for="name">{{ $t('Full name') }}</label>
                        <input id="name" v-model="form.name" class="input" type="text" autocomplete="name" required />
                        <p v-if="form.errors.name" class="wcm26-error">{{ form.errors.name }}</p>
                    </div>

                    <div class="wcm26-field-pair">
                        <div class="wcm26-field">
                            <label class="wcm26-label" for="email">{{ $t('Your email address') }}</label>
                            <input id="email" v-model="form.email" class="input" type="email" autocomplete="email" required />
                            <p v-if="form.errors.email" class="wcm26-error">{{ form.errors.email }}</p>
                        </div>
                        <div class="wcm26-field">
                            <label class="wcm26-label" for="email_confirmation">{{ $t('Repeat your email address') }}</label>
                            <input id="email_confirmation" v-model="form.email_confirmation" class="input" type="email" required />
                            <p v-if="form.errors.email_confirmation" class="wcm26-error">{{ form.errors.email_confirmation }}</p>
                        </div>
                    </div>

                    <div class="wcm26-field-pair">
                        <div class="wcm26-field">
                            <label class="wcm26-label" for="organisation">{{ $t('Organisation') }}</label>
                            <input id="organisation" v-model="form.organisation" class="input" type="text" autocomplete="organization" />
                        </div>
                        <div class="wcm26-field">
                            <label class="wcm26-label" for="country">{{ $t('Country') }}</label>
                            <input id="country" v-model="form.country" class="input" type="text" autocomplete="country-name" />
                        </div>
                    </div>

                    <div class="wcm26-field">
                        <label class="wcm26-label" for="phone">{{ $t('Phone') }}</label>
                        <input id="phone" v-model="form.phone" class="input" type="tel" autocomplete="tel" />
                        <p class="wcm26-hint">{{ $t('So we can reach you if anything changes. Optional.') }}</p>
                        <p v-if="form.errors.phone" class="wcm26-error">{{ form.errors.phone }}</p>
                    </div>

                    <fieldset class="wcm26-field wcm26-fieldset">
                        <legend class="wcm26-label">{{ $t('Which days are you coming?') }}</legend>
                        <div class="wcm26-days-pick">
                            <label v-for="(day, i) in days" :key="day" class="wcm26-check">
                                <input v-model="form.days" type="checkbox" :value="day" />
                                <span>
                                    <strong>{{ dates[i] }}</strong>
                                    {{ $t(`weekday.${weekdays[i]}`) }}
                                </span>
                            </label>
                        </div>
                        <p v-if="form.errors.days" class="wcm26-error">{{ form.errors.days }}</p>
                        <p class="wcm26-hint">{{ $t('days.workshops') }}</p>
                    </fieldset>

                    <label class="wcm26-check wcm26-check-row">
                        <input v-model="form.consent" type="checkbox" required />
                        <span>{{ $t('register.consent') }}</span>
                    </label>
                    <p v-if="form.errors.consent" class="wcm26-error">{{ form.errors.consent }}</p>

                    <!-- Not for people: hidden from view and from assistive tech. -->
                    <div class="wcm26-trap" aria-hidden="true">
                        <label for="website">Website</label>
                        <input id="website" v-model="form.website" type="text" tabindex="-1" autocomplete="off" />
                    </div>

                    <div class="wcm26-form-actions">
                        <button type="submit" class="btn btn-primary btn-flush" style="height: 48px" :disabled="form.processing">
                            {{ form.processing ? $t('Sending…') : $t('register.submit') }}
                        </button>
                        <p class="wcm26-hint">{{ $t('You will get an email to confirm this address.') }}</p>
                    </div>
                </form>
            </div>
        </section>
    </PageShell>
</template>
