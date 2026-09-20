<script setup>
import { Head, useForm } from '@inertiajs/inertia-vue3';
import { computed } from 'vue';
import '../../../css/wcm2026.css';
import PageShell from '../../Sections/2026/PageShell.vue';
import SectionHead from '../../Sections/2026/SectionHead.vue';
import { translateKind } from '../../Sections/2026/kinds';

const props = defineProps({
    base: { type: String, default: '/2026' },
    session: { type: Object, required: true },
    // Set once a place is held, so a reload does not re-offer the form.
    booked: { type: Object, default: null },
});

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    age: '',
    guardian_name: '',
    guardian_phone: '',
    guardian_consent: '',
    consent: false,
    website: '',
});

// The ages a youth workshop offers, and whether the one picked needs a parent.
const ages = Array.from({ length: 95 }, (_, i) => i + 5);
const minor = computed(() => props.session.youth && form.age !== '' && Number(form.age) < 18);
</script>

<template>
    <Head :title="`${session.title} · Why Culture Matters 2026`">
        <meta head-key="robots" name="robots" content="noindex, nofollow" />
    </Head>

    <PageShell :base="base">
        <section class="wcm26-section">
            <SectionHead n="→" :title="booked ? $t('Your place is held') : $t('Book a place')" />

            <div class="wcm26-booking">
                <div v-if="session.image" class="wcm26-booking-photo">
                    <img :src="session.image" :alt="session.title" />
                </div>

                <h2 class="wcm26-lead wcm26-booking-title">{{ session.title }}</h2>

                <p class="wcm26-label wcm26-booking-meta">
                    {{ session.date }} · {{ session.time }}<template v-if="session.kind"> · {{ translateKind(session.kind) }}</template>
                </p>

                <ul v-if="session.people && session.people.length" class="wcm26-booking-people">
                    <li v-for="(person, i) in session.people" :key="i" class="wcm26-booking-person">
                        <img v-if="person.photo" :src="person.photo" alt="" class="wcm26-booking-avatar" />
                        <span v-else class="wcm26-booking-avatar wcm26-booking-avatar-empty"></span>
                        <span class="wcm26-booking-person-name">{{ person.name }}</span>
                    </li>
                </ul>
                <p v-else-if="session.audience" class="wcm26-label wcm26-booking-meta">{{ session.audience }}</p>

                <div v-if="session.description" class="wcm26-rte wcm26-booking-desc" v-html="session.description"></div>

                <template v-if="booked">
                        <p>{{ $t('booking.held', { name: booked.name }) }}</p>
                        <p class="wcm26-hint">{{ $t('Changed your mind? Write to us and we will free the place for someone else.') }}</p>
                    </template>

                    <template v-else-if="session.full">
                        <p>{{ $t('This one is fully booked.') }}</p>
                        <p class="wcm26-hint">{{ $t('The rest of the programme is open — registering for the day needs no booking.') }}</p>
                    </template>

                    <form v-else class="wcm26-form" novalidate @submit.prevent="form.post(session.bookUrl)">
                        <p class="wcm26-contribute-note">{{ $t('Places are limited.') }}</p>

                        <div class="wcm26-fields-2">
                            <div class="wcm26-field">
                                <label class="wcm26-label" for="b-first">{{ $t('First name') }}</label>
                                <input id="b-first" v-model="form.first_name" type="text" required />
                                <p v-if="form.errors.first_name" class="wcm26-error">{{ form.errors.first_name }}</p>
                            </div>
                            <div class="wcm26-field">
                                <label class="wcm26-label" for="b-last">{{ $t('Last name') }}</label>
                                <input id="b-last" v-model="form.last_name" type="text" required />
                                <p v-if="form.errors.last_name" class="wcm26-error">{{ form.errors.last_name }}</p>
                            </div>
                        </div>

                        <div v-if="session.youth" class="wcm26-field">
                            <label class="wcm26-label" for="b-age">{{ $t('Age of the participant') }}</label>
                            <select id="b-age" v-model="form.age" class="wcm26-select" required>
                                <option value="" disabled>—</option>
                                <option v-for="a in ages" :key="a" :value="a">{{ a }}</option>
                            </select>
                            <p v-if="form.errors.age" class="wcm26-error">{{ form.errors.age }}</p>
                        </div>

                        <div class="wcm26-field">
                            <label class="wcm26-label" for="b-email">{{ $t('Your email address') }}</label>
                            <input id="b-email" v-model="form.email" type="email" required />
                            <p v-if="form.errors.email" class="wcm26-error">{{ form.errors.email }}</p>
                        </div>

                        <div v-if="!minor" class="wcm26-field">
                            <label class="wcm26-label" for="b-phone">{{ $t('Phone') }}</label>
                            <input id="b-phone" v-model="form.phone" type="tel" required />
                            <p class="wcm26-hint">{{ $t('So we can reach you if the workshop moves.') }}</p>
                            <p v-if="form.errors.phone" class="wcm26-error">{{ form.errors.phone }}</p>
                        </div>

                        <!-- Under 18: a parent or guardian books. The child above
                             stays the participant; the parent gives their details
                             and a written consent. -->
                        <fieldset v-if="minor" class="wcm26-guardian">
                            <legend class="wcm26-label">{{ $t('Parent or guardian') }}</legend>
                            <p class="wcm26-hint">{{ $t('The young person above is the participant; a parent or guardian signs them up.') }}</p>

                            <div class="wcm26-field">
                                <label class="wcm26-label" for="b-gname">{{ $t("Parent or guardian's full name") }}</label>
                                <input id="b-gname" v-model="form.guardian_name" type="text" required />
                                <p v-if="form.errors.guardian_name" class="wcm26-error">{{ form.errors.guardian_name }}</p>
                            </div>

                            <div class="wcm26-field">
                                <label class="wcm26-label" for="b-gphone">{{ $t("Parent or guardian's phone") }}</label>
                                <input id="b-gphone" v-model="form.guardian_phone" type="tel" required />
                                <p class="wcm26-hint">{{ $t('So we can reach you if the workshop moves.') }}</p>
                                <p v-if="form.errors.guardian_phone" class="wcm26-error">{{ form.errors.guardian_phone }}</p>
                            </div>

                            <div class="wcm26-field">
                                <label class="wcm26-label" for="b-gconsent">{{ $t('Written consent — type “De acord”') }}</label>
                                <input id="b-gconsent" v-model="form.guardian_consent" type="text" placeholder="De acord" required />
                                <p v-if="form.errors.guardian_consent" class="wcm26-error">{{ form.errors.guardian_consent }}</p>
                            </div>
                        </fieldset>

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
        </section>
    </PageShell>
</template>
