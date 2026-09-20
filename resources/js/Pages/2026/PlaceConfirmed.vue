<script setup>
import { Head } from '@inertiajs/inertia-vue3';
import '../../../css/wcm2026.css';
import PageShell from '../../Sections/2026/PageShell.vue';
import SectionHead from '../../Sections/2026/SectionHead.vue';

defineProps({
    base: { type: String, default: '/2026' },
    title: { type: String, default: '' },
    when: { type: String, default: '' },
    code: { type: String, default: '' },
    icsUrl: { type: String, default: '' },
    googleUrl: { type: String, default: '' },
});
</script>

<template>
    <Head :title="`${$t('Your place is confirmed')} · Why Culture Matters 2026`">
        <meta head-key="robots" name="robots" content="noindex, nofollow" />
    </Head>

    <PageShell :base="base">
        <section class="wcm26-section">
            <SectionHead n="→" :title="$t('Your place is confirmed')" />

            <div class="wcm26-booking">
                <h2 class="wcm26-lead wcm26-booking-title">{{ title }}</h2>
                <p v-if="when" class="wcm26-label wcm26-booking-meta">{{ when }}</p>
                <p class="wcm26-label wcm26-booking-meta">{{ $t('Your place code') }}: <strong>{{ code }}</strong></p>

                <div class="wcm26-booking-desc">
                    <p>{{ $t('Add the workshop to your calendar:') }}</p>
                    <p class="wcm26-form-actions" style="gap: 12px; flex-wrap: wrap">
                        <a :href="googleUrl" target="_blank" rel="noopener" class="btn btn-primary">{{ $t('Add to Google Calendar') }}</a>
                        <a :href="icsUrl" class="btn btn-secondary">{{ $t('Download .ics') }}</a>
                    </p>
                </div>
            </div>
        </section>
    </PageShell>
</template>
