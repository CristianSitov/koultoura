<script setup>
import { Head } from '@inertiajs/inertia-vue3';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import '../../../css/wcm2026.css';
import PageShell from '../../Sections/2026/PageShell.vue';
import SectionHead from '../../Sections/2026/SectionHead.vue';

const props = defineProps({
    base: { type: String, default: '/2026' },
    email: { type: String, default: '' },
    skipUrl: { type: String, required: true },
    publishableKey: { type: String, default: null },
    clientSecret: { type: String, default: null },
    redirectUrl: { type: String, required: true },
});

/*
 * Stripe's form is mounted here rather than redirected to, so nobody leaves
 * the site halfway through registering. Card details go from that iframe
 * straight to Stripe; this page never sees them.
 *
 * If the script or the session is unavailable, the page falls back to the
 * button that opens Stripe's own page — never a dead end.
 */
const failed = ref(false);
let checkout = null;

function loadStripeJs() {
    if (window.Stripe) {
        return Promise.resolve();
    }

    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = 'https://js.stripe.com/v3/';
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
    });
}

onMounted(async () => {
    if (!props.clientSecret || !props.publishableKey) {
        return;
    }

    try {
        await loadStripeJs();
        checkout = await window.Stripe(props.publishableKey).initEmbeddedCheckout({
            clientSecret: props.clientSecret,
        });
        checkout.mount('#wcm-checkout');
    } catch (error) {
        failed.value = true;
    }
});

onBeforeUnmount(() => checkout?.destroy());
</script>

<template>
    <Head :title="$t('Contribute · Why Culture Matters 2026')">
        <meta head-key="robots" name="robots" content="noindex, nofollow" />
    </Head>

    <PageShell :base="base">
        <section class="wcm26-section">
            <SectionHead n="→" :title="$t('Contribute')" />

            <!-- One column, one measure: the words and the payment form share
                 a left edge.

                 Skipping sits above the form rather than below it. On a phone
                 the embedded checkout is around 880px tall, which put the way
                 out two screens down, behind the thing it declines — and on
                 10 September two people out of four left from here rather
                 than scroll that far. -->
            <div class="wcm26-contribute-col">
                <!-- An envelope, not a tick: nothing is done yet. The place
                     counts once the address is confirmed (see register.intro),
                     and "You are registered" is the other email's heading. -->
                <p class="wcm26-contribute-done">
                    <svg class="wcm26-contribute-done-mark" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 6h18v12H3z"></path>
                        <path d="M3 7l9 6 9-6"></path>
                    </svg>
                    <span>
                        {{ $t('contribute.registered') }}<span v-if="email" class="wcm26-contribute-done-at">{{ email }}</span>
                        <span class="wcm26-contribute-done-next">{{ $t('Open it and confirm, and your registration is done.') }}</span>
                    </span>
                </p>

                <p class="wcm26-lead">{{ $t('contribute.lead') }}</p>
                <p class="wcm26-body wcm26-contribute-intro" v-html="$t('contribute.intro')"></p>

                <p class="wcm26-contribute-skip">
                    <a :href="skipUrl" class="btn btn-secondary wcm26-skip">{{ $t('Skip this step') }}</a>
                </p>

                <div v-if="clientSecret && !failed" id="wcm-checkout" class="wcm26-checkout"></div>

                <div v-else class="wcm26-checkout-fallback">
                    <p class="wcm26-body">{{ $t('contribute.fallback') }}</p>
                    <p class="wcm26-form-actions">
                        <a :href="redirectUrl" class="btn btn-primary btn-flush" style="height: 48px">
                            {{ $t('Contribute') }}
                        </a>
                    </p>
                </div>
            </div>
        </section>
    </PageShell>
</template>
