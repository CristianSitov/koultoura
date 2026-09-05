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
        <div class="wcm26-head-split">
            <SectionHead n="04" :title="$t('Guests')" />
            <p class="wcm26-label wcm26-label-13">{{ $t('First announcement · more to come') }}</p>
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
                    <span v-if="guest.role" class="wcm26-guest-tag">{{ guest.role }}</span>
                </button>
                <h3 class="wcm26-guest-name">{{ guest.name }}</h3>
                <p class="wcm26-guest-org">{{ guest.org }}</p>
            </article>

            <article class="wcm26-guest">
                <div class="wcm26-guest-more">
                    <p>{{ $t('More guests to be announced') }}</p>
                </div>
            </article>
        </div>
    </section>
</template>
