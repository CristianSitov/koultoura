<script setup>
import { computed } from 'vue';
import { trans } from 'laravel-vue-i18n';
import SectionHead from './SectionHead.vue';

/*
 * Photographs from the 2024 edition. They cross-fade on a CSS animation with
 * staggered delays — no timer, no state, and nothing to tear down; the whole
 * thing stops under prefers-reduced-motion, leaving the first frame up.
 */
// The slot is portrait and the photographs are landscape, so each is cropped
// to its middle. The opening frame is the only one whose subject sits off to
// the right and needs the crop nudged across.
const photos = [
    { file: 1, position: '68% center' },
    { file: 2, position: 'center' },
    { file: 3, position: 'center' },
    { file: 4, position: 'center' },
    { file: 5, position: 'center' },
    { file: 6, position: 'center' },
    { file: 7, position: 'center' },
    { file: 8, position: 'center' },
    { file: 9, position: 'center' },
    { file: 10, position: 'center' },
    { file: 11, position: 'center' },
    { file: 12, position: 'center' },
];

/*
 * "See the previous editions … – 2022 & 2024", with each year a link to the
 * edition that is still online. The years are placeholders in the string so
 * the sentence can be worded differently in each language.
 */
const archiveLine = computed(() =>
    trans('about.archive')
        .replace(':y2022', '<a href="/2022">2022</a>')
        .replace(':y2024', '<a href="/2024">2024</a>')
);
</script>

<template>
    <section id="about" class="wcm26-section">
        <SectionHead n="01" :title="$t('About')" />

        <div class="wcm26-split">
            <div>
                <p class="wcm26-lead" style="max-width: 18ch">{{ $t('Four days where research meets practice.') }}</p>
                <div class="wcm26-about-photo grayscale-photo">
                    <img
                        v-for="(photo, i) in photos"
                        :key="photo.file"
                        :src="`/assets/2026/about/wcm2024-${photo.file}.jpg`"
                        :alt="i === 0 ? $t('Photograph from a past edition') : ''"
                        :style="{ animationDelay: `${i * -5}s`, objectPosition: photo.position }"
                        loading="lazy"
                    />
                </div>
            </div>

            <div class="wcm26-about-copy">
                <!-- v-html because the copy carries the emphasis the source
                     document has; it is our own text, not anything typed in. -->
                <p v-html="$t('about.p1')"></p>
                <p>{{ $t('about.p2') }}</p>
                <p v-html="$t('about.p3')"></p>
                <p v-html="$t('about.p4')"></p>
                <p v-html="$t('about.p5')"></p>
                <p class="wcm26-about-close">{{ $t('about.close') }}</p>

                <p class="wcm26-about-archive" v-html="archiveLine"></p>
            </div>
        </div>
    </section>
</template>
