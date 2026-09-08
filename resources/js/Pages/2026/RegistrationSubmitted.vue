<script setup>
import { Head, useForm, usePage } from '@inertiajs/inertia-vue3';
import { computed, ref } from 'vue';
import '../../../css/wcm2026.css';
import { CalendarDays, Check, HeartHandshake, Mail } from 'lucide-vue-next';
import PageShell from '../../Sections/2026/PageShell.vue';
import SectionHead from '../../Sections/2026/SectionHead.vue';

const props = defineProps({
    base: { type: String, default: '/2026' },
    email: { type: String, default: '' },
    // What this address is down for. Shown, never editable here.
    days: { type: Array, default: () => [] },
    contributeUrl: { type: String, default: null },
    // Set by Stripe's success_url. A hint for the visitor, not a record —
    // what was actually paid arrives on the webhook.
    paid: { type: Boolean, default: false },
    confirmed: { type: Boolean, default: false },
    // { amount: minor units, currency } — for the thank-you only.
    contribution: { type: Object, default: null },
});

// Formatted where the visitor is reading it, so Romanian gets its comma.
const contributed = computed(() => {
    if (!props.contribution) {
        return null;
    }

    const locale = usePage().props.value.locale === 'ro' ? 'ro-RO' : 'en-GB';

    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency: props.contribution.currency,
    }).format(props.contribution.amount / 100);
});

// 7–10 October; the numbers are what the form and the database speak in.
const dayLabels = { 1: '07', 2: '08', 3: '09', 4: '10' };

const resent = ref(false);
const form = useForm({ email: props.email });

function resend() {
    form.post(`${props.base}/resend`, {
        preserveScroll: true,
        onSuccess: () => (resent.value = true),
    });
}
</script>

<template>
    <Head :title="$t('Confirm your registration · Why Culture Matters 2026')">
        <meta head-key="robots" name="robots" content="noindex, nofollow" />
    </Head>

    <PageShell :base="base">
        <section class="wcm26-section">
            <SectionHead n="→" :title="confirmed ? $t('You are registered') : $t('Check your email')" />

            <div class="wcm26-split">
                <div>
                    <p class="wcm26-lead" style="max-width: 22ch">
                        {{ confirmed ? $t('Nothing left to do.') : $t('One step left.') }}
                    </p>
                </div>

                <div class="wcm26-about-copy">
                    <!-- Confirmed: the heading says so, and repeating it here
                         only pushed the one thing still on offer further down. -->
                    <template v-if="! confirmed">
                        <div class="wcm26-cued">
                            <span class="wcm26-cue" aria-hidden="true">
                                <Mail :size="50" :stroke-width="1.25" />
                            </span>

                            <div>
                                <p>
                                    {{ $t('We have written to') }} <strong class="wcm26-email">{{ email }}</strong>{{ '.' }}
                                    {{ $t('Open it and confirm, and your registration is done.') }}
                                </p>

                                <p class="wcm26-hint">{{ $t('Nothing arrived? It may take a minute, and it may have landed in spam.') }}</p>

                                <p v-if="resent" class="wcm26-note-sent">{{ $t('Sent again. Give it a minute.') }}</p>
                                <p v-else class="wcm26-form-actions">
                                    <button
                                        type="button"
                                        class="btn btn-secondary btn-flush"
                                        style="height: 44px"
                                        :disabled="form.processing"
                                        @click="resend"
                                    >
                                        {{ $t('Send it again') }}
                                    </button>
                                </p>
                            </div>
                        </div>
                    </template>

                    <!-- Someone filling the form in again with the same
                         address lands here: this is what they are down for,
                         and it is not changed by asking twice. -->
                    <div v-if="days.length" class="wcm26-cued wcm26-booked">
                        <span class="wcm26-cue" aria-hidden="true">
                            <CalendarDays :size="50" :stroke-width="1.25" />
                        </span>

                        <p class="wcm26-booked-days">
                            <span class="wcm26-label">{{ $t('You are coming on') }}</span>
                            <span v-for="day in days" :key="day" class="wcm26-booked-day">{{ dayLabels[day] }}</span>
                            <span class="wcm26-booked-month">{{ $t('October 2026') }}</span>
                        </p>
                    </div>

                    <div v-if="paid" class="wcm26-cued wcm26-contribute">
                        <span class="wcm26-cue" aria-hidden="true">
                            <Check :size="50" :stroke-width="1.25" />
                        </span>

                        <div>
                        <p class="wcm26-contribute-note">
                            {{ contributed
                                ? $t('Thank you — your contribution of :amount has gone through.', { amount: contributed })
                                : $t('Thank you for your contribution.') }}
                        </p>
                        <p class="wcm26-hint">{{ confirmed ? $t('Stripe will email you a receipt.') : $t('Stripe will email you a receipt. Your registration still needs confirming from the email above.') }}</p>
                        </div>
                    </div>
                    <!--
                        A quiet second chance for anyone who skipped the
                        contribution step. Deliberately understated: this page
                        has one job, and it is getting them into their inbox.
                    -->
                    <div v-else-if="contributeUrl" class="wcm26-cued wcm26-contribute">
                        <span class="wcm26-cue" aria-hidden="true">
                            <HeartHandshake :size="50" :stroke-width="1.25" />
                        </span>

                        <div>
                            <p class="wcm26-contribute-note" v-html="$t('submitted.contribution.short')"></p>
                            <p class="wcm26-form-actions">
                                <a :href="contributeUrl" class="btn btn-secondary btn-flush wcm26-donate" style="height: 44px">
                                    {{ $t('Donate') }}
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </PageShell>
</template>
