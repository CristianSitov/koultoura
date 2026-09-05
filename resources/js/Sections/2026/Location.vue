<script setup>
import SectionHead from './SectionHead.vue';

defineProps({
    isDark: { type: Boolean, default: false },
});

const mapsUrl = 'https://www.openstreetmap.org/?mlat=45.7545&mlon=21.2395#map=17/45.7545/21.2395';
</script>

<template>
    <section id="location" class="wcm26-section">
        <SectionHead n="06" title="Location" />

        <div class="wcm26-split">
            <div>
                <p class="wcm26-venue">FABER</p>
                <p class="wcm26-address">Splaiul Peneș Curcanul 4–5<br />300124 Timișoara<br />Romania</p>
                <p class="wcm26-venue-note">
                    An independent cultural centre on the banks of the Bega, in the Fabric district.
                </p>
                <a
                    :href="mapsUrl"
                    target="_blank"
                    rel="noopener"
                    class="btn btn-secondary btn-flush"
                    style="margin-top: 32px; height: 44px"
                >
                    Open in maps
                </a>
            </div>

            <div class="wcm26-map grayscale-photo">
                <iframe
                    src="/assets/2026/wcm-map.html"
                    title="Map: FABER, Timișoara"
                    loading="lazy"
                    :style="{ filter: isDark ? 'invert(1) hue-rotate(180deg) brightness(0.9)' : 'none' }"
                ></iframe>
            </div>
        </div>
    </section>
</template>
