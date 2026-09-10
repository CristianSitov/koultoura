<script setup>
import { Head } from '@inertiajs/inertia-vue3';
import { Mail, MailX } from 'lucide-vue-next';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import '../../../css/wcm2026.css';
import PageShell from '../../Sections/2026/PageShell.vue';
import SectionHead from '../../Sections/2026/SectionHead.vue';

const props = defineProps({
    base: { type: String, default: '/2026' },
    email: { type: String, default: '' },
    // False when the mailer refused. The registration is saved either way, so
    // this page is where someone finds out no email is coming.
    sent: { type: Boolean, default: true },
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
                     and "You are registered" is the other email's heading.

                     A crossed envelope when the send failed — telling someone
                     to go and open a message that never left is worse than
                     saying nothing, and they are still registered. -->
                <p class="wcm26-contribute-done">
                    <span class="wcm26-contribute-done-mark" aria-hidden="true">
                        <component :is="sent ? Mail : MailX" :size="24" :stroke-width="2" />
                    </span>
                    <span>
                        {{ sent ? $t('contribute.registered') : $t('send.failed') }}<span v-if="email" class="wcm26-contribute-done-at">{{ email }}</span>
                        <span class="wcm26-contribute-done-next">
                            {{ sent ? $t('Open it and confirm, and your registration is done.') : $t('send.failed.contribute') }}
                        </span>
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
