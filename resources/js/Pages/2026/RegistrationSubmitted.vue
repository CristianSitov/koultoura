<script setup>
import { Head, useForm, usePage } from '@inertiajs/inertia-vue3';
import { computed, ref } from 'vue';
import '../../../css/wcm2026.css';
import PageShell from '../../Sections/2026/PageShell.vue';
import SectionHead from '../../Sections/2026/SectionHead.vue';

const props = defineProps({
    base: { type: String, default: '/2026' },
    email: { type: String, default: '' },
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
                        <p>{{ $t('We have written to :email. Open it and confirm, and your registration is done.', { email }) }}</p>

                        <p>{{ $t('Nothing arrived? It may take a minute, and it may have landed in spam.') }}</p>

                        <p v-if="resent" class="wcm26-note-sent">{{ $t('Sent again. Give it a minute.') }}</p>
                        <button
                            v-else
                            type="button"
                            class="btn btn-ghost btn-flush"
                            :disabled="form.processing"
                            @click="resend"
                        >
                            {{ $t('Send it again') }}
                        </button>
                    </template>

                    <div v-if="paid" class="wcm26-contribute">
                        <p class="wcm26-contribute-note">
                            {{ contributed
                                ? $t('Thank you — your contribution of :amount has gone through.', { amount: contributed })
                                : $t('Thank you for your contribution.') }}
                        </p>
                        <p class="wcm26-hint">{{ confirmed ? $t('Stripe will email you a receipt.') : $t('Stripe will email you a receipt. Your registration still needs confirming from the email above.') }}</p>
                    </div>
                    <!--
                        A quiet second chance for anyone who skipped the
                        contribution step. Deliberately understated: this page
                        has one job, and it is getting them into their inbox.
                    -->
                    <div v-else-if="contributeUrl" class="wcm26-contribute">
                        <p class="wcm26-contribute-note">{{ $t('submitted.contribution.short') }}</p>
                        <p class="wcm26-form-actions">
                            <a :href="contributeUrl" class="btn btn-secondary btn-flush" style="height: 44px">
                                {{ $t('Contribute') }}
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </PageShell>
</template>
