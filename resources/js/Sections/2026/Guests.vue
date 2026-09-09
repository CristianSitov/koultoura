<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import ImageSlot from './ImageSlot.vue';
import SectionHead from './SectionHead.vue';
import { rgbShift } from './rgbShift';

defineProps({
    guests: { type: Array, required: true },
});

defineEmits(['open']);

const grid = ref(null);
let stopShift = null;

onMounted(() => {
    stopShift = rgbShift(grid.value);
});

onBeforeUnmount(() => stopShift?.());
</script>

<template>
    <section id="speakers" class="wcm26-section">
        <!-- The same split as the Programme heading: the list is still growing
             and saying so is better than a visitor assuming it is complete. -->
        <div class="wcm26-head-split">
            <SectionHead n="04" :title="$t('Guests')" />
            <p class="wcm26-label wcm26-label-13">{{ $t('Updates in progress') }}</p>
        </div>

        <div ref="grid" class="wcm26-guests">
            <article v-for="guest in guests" :key="guest.id" class="wcm26-guest">
                <button
                    type="button"
                    class="wcm26-guest-photo"
                    :aria-label="$t('Open profile: :name', { name: guest.name })"
                    @click="$emit('open', guest.id)"
                >
                    <ImageSlot :src="guest.portrait" :alt="guest.name" :placeholder="$t('Portrait')" />
                </button>

                <!-- What they do sits above the name; where they are from sits
                     under it, as a link out to the institution. -->
                <p v-if="guest.role" class="wcm26-guest-role">{{ guest.role }}</p>
                <!-- Given name over family name: two short lines read better
                     in this column than one that has to shrink to fit. -->
                <h3 class="wcm26-guest-name">
                    <span>{{ guest.name.split(' ')[0] }}</span>
                    <span>{{ guest.name.split(' ').slice(1).join(' ') }}</span>
                </h3>
                <p v-if="guest.org" class="wcm26-guest-org">
                    <a v-if="guest.orgUrl" :href="guest.orgUrl" target="_blank" rel="noopener">{{ guest.org }}</a>
                    <template v-else>{{ guest.org }}</template>
                </p>
            </article>

            <article class="wcm26-guest">
                <div class="wcm26-guest-more">
                    <p>{{ $t('More guests to be announced') }}</p>
                </div>
            </article>
        </div>
    </section>
</template>
