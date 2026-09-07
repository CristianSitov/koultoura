<script setup>
import { Head, useForm } from '@inertiajs/inertia-vue3';
import { ref } from 'vue';
import '../../../css/wcm2026.css';
import PageShell from '../../Sections/2026/PageShell.vue';
import SectionHead from '../../Sections/2026/SectionHead.vue';

const props = defineProps({
    base: { type: String, default: '/2026' },
    email: { type: String, default: '' },
    contributeUrl: { type: String, default: null },
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
            <SectionHead n="→" :title="$t('Check your email')" />

            <div class="wcm26-split">
                <div>
                    <p class="wcm26-lead" style="max-width: 22ch">{{ $t('One step left.') }}</p>
                </div>

                <div class="wcm26-about-copy">
                    <p v-if="email">
                        {{ $t('We have written to :email. Open it and confirm, and your registration is done.', { email }) }}
                    </p>
                    <p v-else>{{ $t('We have written to the address you gave. Open it and confirm, and your registration is done.') }}</p>

                    <p>{{ $t('Nothing arrived? It may take a minute, and it may have landed in spam.') }}</p>

                    <p v-if="resent" class="wcm26-note-sent">{{ $t('Sent again. Give it a minute.') }}</p>
                    <button
                        v-else-if="email"
                        type="button"
                        class="btn btn-ghost btn-flush"
                        :disabled="form.processing"
                        @click="resend"
                    >
                        {{ $t('Send it again') }}
                    </button>

                    <div v-if="contributeUrl" class="wcm26-contribute">
                        <p class="wcm26-about-close">{{ $t('submitted.contribution') }}</p>
                        <p class="wcm26-form-actions">
                            <a :href="contributeUrl" class="btn btn-primary btn-flush" style="height: 48px">
                                {{ $t('Contribute') }}
                            </a>
                            <span class="wcm26-hint">{{ $t('Any amount, or none — your place is already held.') }}</span>
                        </p>
                    </div>
                    <p v-else class="wcm26-about-close">{{ $t('submitted.contribution') }}</p>
                </div>
            </div>
        </section>
    </PageShell>
</template>
