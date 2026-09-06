<script setup>
import SectionHead from './SectionHead.vue';

defineProps({
    isDark: { type: Boolean, default: false },
});

const mapsUrl = 'https://www.openstreetmap.org/?mlat=45.7613981&mlon=21.2515238#map=17/45.7613981/21.2515238';
</script>

<template>
    <section id="location" class="wcm26-section">
        <SectionHead n="06" :title="$t('Location')" />

        <div class="wcm26-split">
            <div>
                <p class="wcm26-venue">FABER</p>
                <p class="wcm26-address">Splaiul Peneș Curcanul 4–5<br />300124 Timișoara<br />{{ $t('Romania') }}</p>
                <p class="wcm26-venue-note">{{ $t('location.note') }}</p>
                <a
                    :href="mapsUrl"
                    target="_blank"
                    rel="noopener"
                    class="btn btn-secondary btn-flush"
                    style="margin-top: 32px; height: 44px"
                >
                    {{ $t('Open in maps') }}
                </a>
            </div>

            <div class="wcm26-map grayscale-photo">
                <iframe
                    src="/assets/2026/wcm-map.html"
                    :title="$t('Map: FABER, Timișoara')"
                    loading="lazy"
                    :style="{ filter: isDark ? 'invert(1) hue-rotate(180deg) brightness(0.9)' : 'none' }"
                ></iframe>
            </div>
        </div>
    </section>
</template>
