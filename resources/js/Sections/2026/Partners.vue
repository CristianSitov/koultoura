<script setup>
import ImageSlot from './ImageSlot.vue';
import SectionHead from './SectionHead.vue';

/*
 * Who runs the symposium, who pays for it, and who it is made with — the list
 * as the organisers gave it, in their order.
 *
 * A partner with no logo file yet is named rather than left out: the list is
 * the fact, the mark is the decoration. Drop the file into
 * public/assets/2026/partners/ and add `logo` to the row.
 */
const organisers = [
    { name: 'Asociația Prin Banat', logo: '/assets/2026/partners/prin-banat.svg' },
    { name: 'Heritage of Timișoara', logo: '/assets/2026/partners/heritage-of-timisoara.svg' },
];

const financers = [
    { name: 'Centrul de Proiecte al Municipiului Timișoara', logo: '/assets/2026/partners/centrul-de-proiecte.png' },
];

const supporters = [
    { name: 'Institutul Național al Patrimoniului', logo: '/assets/2026/partners/inp.png' },
    { name: 'Direcția Județeană pentru Cultură Timiș', logo: '/assets/2026/partners/djc.svg' },
    { name: 'UVT — Facultatea de Arte și Design', logo: '/assets/2026/partners/uvt-arte.svg' },
    { name: 'CICASP', logo: '/assets/2026/partners/cicasp.svg' },
    { name: 'OAR — Filiala Teritorială Timiș', logo: '/assets/2026/partners/oar-timis.svg' },
    { name: 'KÉK — Contemporary Architecture Centre', logo: '/assets/2026/partners/kek.svg' },
    { name: 'Oradea Heritage', logo: null },
    { name: 'Imagine Heritage', logo: '/assets/2026/partners/imagine-heritage.png' },
    { name: 'Youth.Heritage.Europe', logo: '/assets/2026/partners/youth-heritage-europe.png' },
    { name: 'FAINA', logo: '/assets/2026/partners/faina.png' },
    { name: 'Institutul Polonez București', logo: '/assets/2026/partners/institutul-polonez.svg' },
    { name: 'Visit Timișoara', logo: '/assets/2026/partners/visit-timisoara.jpg' },
    { name: 'Librăriile Cărturești Timișoara', logo: '/assets/2026/partners/carturesti.svg' },
    { name: 'Fundația Culturală Jazz Banat', logo: null },
];
</script>

<template>
    <section id="partners" class="wcm26-section">
        <SectionHead n="07" :title="$t('Partners')" />

        <div class="wcm26-partners">
            <div>
                <p class="wcm26-partner-role">{{ $t('Organized by') }}</p>
                <div class="wcm26-partner-marks">
                    <div v-for="partner in organisers" :key="partner.name" class="wcm26-partner-logo">
                        <ImageSlot
                            :src="partner.logo"
                            :alt="partner.name"
                            :placeholder="$t(':name logo', { name: partner.name })"
                            fit="contain"
                        />
                    </div>
                </div>
            </div>

            <div>
                <p class="wcm26-partner-role">{{ $t('Financed by') }}</p>
                <div class="wcm26-partner-marks">
                    <div v-for="partner in financers" :key="partner.name" class="wcm26-partner-logo">
                        <ImageSlot
                            :src="partner.logo"
                            :alt="partner.name"
                            :placeholder="$t(':name logo', { name: partner.name })"
                            fit="contain"
                        />
                    </div>
                </div>
            </div>
        </div>

        <p class="wcm26-partner-role wcm26-supporters-label">{{ $t('Partners') }}</p>
        <ul class="wcm26-supporters">
            <li v-for="supporter in supporters" :key="supporter.name">
                <img v-if="supporter.logo" :src="supporter.logo" :alt="supporter.name" loading="lazy" />
                <span v-else class="wcm26-supporter-name">{{ supporter.name }}</span>
            </li>
        </ul>
    </section>
</template>
