<script setup>
import { Head } from '@inertiajs/inertia-vue3';
import '../../../css/wcm2026.css';
import PageShell from '../../Sections/2026/PageShell.vue';
import SectionHead from '../../Sections/2026/SectionHead.vue';

/*
 * A document, rendered from markdown. The prose styling lives in the
 * stylesheet under .wcm26-doc rather than here, so it matches the rest of the
 * page's type rather than the browser's defaults.
 */
defineProps({
    base: { type: String, default: '/2026' },
    title: { type: String, required: true },
    body: { type: String, required: true },
});
</script>

<template>
    <Head :title="`${title} · Why Culture Matters 2026`" />

    <PageShell :base="base">
        <section class="wcm26-section">
            <SectionHead n="→" :title="title" />

            <div class="wcm26-doc" v-html="body"></div>
        </section>
    </PageShell>
</template>
