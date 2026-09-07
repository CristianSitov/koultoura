<script setup>
import { Head, useForm } from '@inertiajs/inertia-vue3';
import '../../../css/wcm2026.css';
import PageShell from '../../Sections/2026/PageShell.vue';
import SectionHead from '../../Sections/2026/SectionHead.vue';

defineProps({
    base: { type: String, default: '/2026' },
    session: { type: Object, required: true },
    // Set once a place is held, so a reload does not re-offer the form.
    booked: { type: Object, default: null },
});

const form = useForm({
    name: '',
    email: '',
    phone: '',
    consent: false,
    website: '',
});
</script>

<template>
    <Head :title="`${session.title} · Why Culture Matters 2026`">
        <meta head-key="robots" name="robots" content="noindex, nofollow" />
    </Head>

    <PageShell :base="base">
        <section class="wcm26-section">
            <SectionHead n="→" :title="booked ? $t('Your place is held') : $t('Book a place')" />

            <div class="wcm26-split">
                <div>
                    <p class="wcm26-lead">{{ session.title }}</p>
                    <p class="wcm26-label" style="margin-top: 12px">
                        {{ session.date }} · {{ session.time }}<template v-if="session.kind"> · {{ session.kind }}</template>
                    </p>
                    <p v-if="session.speakers" class="wcm26-label">{{ session.speakers }}</p>
                    <p v-else-if="session.audience" class="wcm26-label">{{ session.audience }}</p>
                </div>

                <div class="wcm26-about-copy">
                    <p v-if="session.description">{{ session.description }}</p>

                    <template v-if="booked">
                        <p>{{ $t('booking.held', { name: booked.name }) }}</p>
                        <p class="wcm26-hint">{{ $t('Changed your mind? Write to us and we will free the place for someone else.') }}</p>
                    </template>

                    <template v-else-if="session.full">
                        <p>{{ $t('This one is fully booked.') }}</p>
                        <p class="wcm26-hint">{{ $t('The rest of the programme is open — registering for the day needs no booking.') }}</p>
                    </template>

                    <form v-else class="wcm26-form" novalidate @submit.prevent="form.post(session.bookUrl)">
                        <p class="wcm26-contribute-note">
                            {{ $t('booking.places_left', { n: session.placesLeft }) }}
                        </p>

                        <div class="wcm26-field">
                            <label class="wcm26-label" for="b-name">{{ $t('Full name') }}</label>
                            <input id="b-name" v-model="form.name" type="text" required />
                            <p v-if="form.errors.name" class="wcm26-error">{{ form.errors.name }}</p>
                        </div>

                        <div class="wcm26-field">
                            <label class="wcm26-label" for="b-email">{{ $t('Your email address') }}</label>
                            <input id="b-email" v-model="form.email" type="email" required />
                            <p v-if="form.errors.email" class="wcm26-error">{{ form.errors.email }}</p>
                        </div>

                        <div class="wcm26-field">
                            <label class="wcm26-label" for="b-phone">{{ $t('Phone') }}</label>
                            <input id="b-phone" v-model="form.phone" type="tel" />
                            <p class="wcm26-hint">{{ $t('So we can reach you if the workshop moves. Optional.') }}</p>
                        </div>

                        <label class="wcm26-check wcm26-check-row">
                            <input v-model="form.consent" type="checkbox" required />
                            <span>{{ $t('register.consent') }}</span>
                        </label>
                        <p v-if="form.errors.consent" class="wcm26-error">{{ form.errors.consent }}</p>

                        <!-- Not for people: hidden from view and from assistive tech. -->
                        <div class="wcm26-trap" aria-hidden="true">
                            <label for="b-website">Website</label>
                            <input id="b-website" v-model="form.website" type="text" tabindex="-1" autocomplete="off" />
                        </div>

                        <div class="wcm26-form-actions">
                            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                {{ $t('Book a place') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </PageShell>
</template>
