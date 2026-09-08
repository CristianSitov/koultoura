<script setup>
import ImageSlot from './ImageSlot.vue';
import SectionHead from './SectionHead.vue';

/*
 * Who runs the symposium, who pays for it, and who it is made with — the list
 * as the organisers gave it, in their order.
 *
 * A row with a `url` becomes a link out, opening in its own tab. The ones
 * without are the addresses nobody has given us yet: a wrong link under a
 * partner's mark is worse than none, so they stay plain until asked for.
 */
const organisers = [
    { name: 'Asociația Prin Banat', logo: '/assets/2026/partners/prin-banat.svg', url: 'https://prinbanat.ro' },
    { name: 'Heritage of Timișoara', logo: '/assets/2026/partners/heritage-of-timisoara.svg', url: 'https://heritageoftimisoara.ro' },
];

const financers = [
    { name: 'Centrul de Proiecte al Municipiului Timișoara', logo: '/assets/2026/partners/centrul-de-proiecte.png' },
];

const supporters = [
    { name: 'Institutul Național al Patrimoniului', logo: '/assets/2026/partners/inp.png', url: 'https://patrimoniu.ro' },
    { name: 'Direcția Județeană pentru Cultură Timiș', logo: '/assets/2026/partners/djc.svg', url: 'https://culturatimis.ro/' },
    { name: 'UVT — Facultatea de Arte și Design', logo: '/assets/2026/partners/uvt-arte.svg', url: 'https://arte.uvt.ro/' },
    { name: 'CICASP', logo: '/assets/2026/partners/cicasp.svg', url: 'https://cicasp.uvt.ro' },
    { name: 'OAR — Filiala Teritorială Timiș', logo: '/assets/2026/partners/oar-timis.svg', url: 'https://oartimis.ro/' },
    { name: 'KÉK — Contemporary Architecture Centre', logo: '/assets/2026/partners/kek.svg', url: 'https://www.kek.org.hu' },
    { name: 'Oradea Heritage', logo: '/assets/2026/partners/oradea-heritage.png', url: 'https://www.oradeaheritage.ro/' },
    { name: 'Imagine Heritage', logo: '/assets/2026/partners/imagine-heritage.png', url: 'https://imagineheritage.com/' },
    { name: 'Youth.Heritage.Europe', logo: '/assets/2026/partners/youth-heritage-europe.png', url: 'https://www.youth-heritage-europe.org' },
    { name: 'FAINA', logo: '/assets/2026/partners/faina.png', url: 'https://www.facebook.com/faina.ua.community/' },
    { name: 'Institutul Polonez București', logo: '/assets/2026/partners/institutul-polonez.svg', url: 'https://instytutpolski.pl/bucuresti/' },
    { name: 'Visit Timișoara', logo: '/assets/2026/partners/visit-timisoara.jpg', url: 'https://visit-timisoara.com/' },
    { name: 'Librăriile Cărturești Timișoara', logo: '/assets/2026/partners/carturesti.svg', url: 'http://carturesti.ro/' },
    { name: 'Fundația Culturală Jazz Banat', logo: '/assets/2026/partners/jazz-banat.png' },
];
</script>

<template>
    <section id="partners" class="wcm26-section">
        <SectionHead n="07" :title="$t('Partners')" />

        <div class="wcm26-partners">
            <div>
                <p class="wcm26-partner-role">{{ $t('Organized by') }}</p>
                <div class="wcm26-partner-marks wcm26-partner-marks-organisers">
                    <component
                        :is="partner.url ? 'a' : 'div'"
                        v-for="partner in organisers"
                        :key="partner.name"
                        :href="partner.url"
                        :target="partner.url ? '_blank' : null"
                        :rel="partner.url ? 'noopener' : null"
                        class="wcm26-partner-logo"
                    >
                        <ImageSlot
                            :src="partner.logo"
                            :alt="partner.name"
                            :placeholder="$t(':name logo', { name: partner.name })"
                            fit="contain"
                        />
                    </component>
                </div>
            </div>

            <div>
                <p class="wcm26-partner-role">{{ $t('Financed by') }}</p>
                <div class="wcm26-partner-marks">
                    <component
                        :is="partner.url ? 'a' : 'div'"
                        v-for="partner in financers"
                        :key="partner.name"
                        :href="partner.url"
                        :target="partner.url ? '_blank' : null"
                        :rel="partner.url ? 'noopener' : null"
                        class="wcm26-partner-logo"
                    >
                        <ImageSlot
                            :src="partner.logo"
                            :alt="partner.name"
                            :placeholder="$t(':name logo', { name: partner.name })"
                            fit="contain"
                        />
                    </component>
                </div>
            </div>
        </div>

        <p class="wcm26-partner-role wcm26-supporters-label">{{ $t('Partners') }}</p>
        <ul class="wcm26-supporters">
            <li v-for="supporter in supporters" :key="supporter.name">
                <a v-if="supporter.url" :href="supporter.url" target="_blank" rel="noopener">
                    <img :src="supporter.logo" :alt="supporter.name" loading="lazy" />
                </a>
                <img v-else-if="supporter.logo" :src="supporter.logo" :alt="supporter.name" loading="lazy" />
                <span v-else class="wcm26-supporter-name">{{ supporter.name }}</span>
            </li>
        </ul>
    </section>
</template>
