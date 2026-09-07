<script setup>
import { Head } from '@inertiajs/inertia-vue3';
import '../../../css/wcm2026.css';
import PageShell from '../../Sections/2026/PageShell.vue';
import SectionHead from '../../Sections/2026/SectionHead.vue';

defineProps({
    base: { type: String, default: '/2026' },
    name: { type: String, default: '' },
    alreadyConfirmed: { type: Boolean, default: false },
});
</script>

<template>
    <Head :title="$t('Registration confirmed · Why Culture Matters 2026')">
        <meta head-key="robots" name="robots" content="noindex, nofollow" />
    </Head>

    <PageShell :base="base">
        <section class="wcm26-section">
            <SectionHead n="✓" :title="$t('You are registered')" />

            <div class="wcm26-split">
                <div>
                    <p class="wcm26-lead" style="max-width: 20ch">
                        {{ alreadyConfirmed ? $t('Already confirmed.') : $t('See you in October.') }}
                    </p>
                </div>

                <div class="wcm26-about-copy">
                    <p>{{ $t('Thank you, :name. Your place at Why Culture Matters 2026 is confirmed — 7–10 October, FABER, Timișoara.', { name }) }}</p>
                    <p>{{ $t('We will write again closer to the date with the full programme.') }}</p>

                    <p class="wcm26-form-actions">
                        <a :href="base" class="btn btn-secondary btn-flush" style="height: 44px">{{ $t('Back to the symposium') }}</a>
                    </p>
                </div>
            </div>
        </section>
    </PageShell>
</template>
