<script setup>
import ImageSlot from './ImageSlot.vue';
import SectionHead from './SectionHead.vue';

/*
 * The two role slots carry the marks from heritageoftimisoara.ro. FABER used to
 * sit here as a third, venue-partner slot; it has its own Location section, so
 * this row is now just who runs the symposium and who pays for it. The list
 * below is "03. sigle parteneri", best format per partner (svg > png > jpg),
 * pulled into public/assets/2026/partners/.
 */
const partners = [
    { role: 'Organized by', name: 'Asociația Prin Banat', logo: '/assets/2026/partners/prin-banat.svg' },
    {
        role: 'Financed by',
        name: 'Centrul de Proiecte al Municipiului Timișoara',
        logo: '/assets/2026/partners/centrul-de-proiecte.png',
    },
];

const supporters = [
    { name: 'Arhabito', logo: '/assets/2026/partners/arhabito.svg' },
    { name: 'Asociația TEMPORAR', logo: '/assets/2026/partners/temporar.png' },
    { name: 'Cărturești', logo: '/assets/2026/partners/carturesti.svg' },
    { name: 'CICASP', logo: '/assets/2026/partners/cicasp.svg' },
    { name: 'Consiliul Județean Timiș', logo: '/assets/2026/partners/cjt.svg' },
    { name: 'Direcția Județeană pentru Cultură Timiș', logo: '/assets/2026/partners/djc.svg' },
    { name: 'Imagine Heritage', logo: '/assets/2026/partners/imagine-heritage.png' },
    { name: 'KÉK — Contemporary Architecture Centre', logo: '/assets/2026/partners/kek.svg' },
    { name: 'Muzeul Național al Banatului', logo: '/assets/2026/partners/mnab.svg' },
    { name: 'OAR Timiș', logo: '/assets/2026/partners/oar-timis.svg' },
    { name: 'Scârț', logo: '/assets/2026/partners/scart.svg' },
    { name: 'UVT — Facultatea de Arte și Design', logo: '/assets/2026/partners/uvt-arte.svg' },
    { name: 'Visit Timișoara', logo: '/assets/2026/partners/visit-timisoara.jpg' },
];
</script>

<template>
    <section id="partners" class="wcm26-section">
        <SectionHead n="07" :title="$t('Partners')" />

        <div class="wcm26-partners">
            <div v-for="partner in partners" :key="partner.name">
                <p class="wcm26-partner-role">{{ $t(partner.role) }}</p>
                <div class="wcm26-partner-logo">
                    <ImageSlot
                        :src="partner.logo"
                        :alt="partner.name"
                        :placeholder="$t(':name logo', { name: partner.name })"
                        fit="contain"
                    />
                </div>
            </div>
        </div>

        <p class="wcm26-partner-role wcm26-supporters-label">{{ $t('Partners') }}</p>
        <ul class="wcm26-supporters">
            <li v-for="supporter in supporters" :key="supporter.name">
                <img :src="supporter.logo" :alt="supporter.name" loading="lazy" />
            </li>
        </ul>
    </section>
</template>
