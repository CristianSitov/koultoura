<script setup>
import ImageSlot from './ImageSlot.vue';
import SectionHead from './SectionHead.vue';
import { guests } from './guests';

defineEmits(['open']);
</script>

<template>
    <section id="speakers" class="wcm26-section">
        <div class="wcm26-head-split">
            <SectionHead n="04" title="Guests" />
            <p class="wcm26-label wcm26-label-13">First announcement · more to come</p>
        </div>

        <div class="wcm26-guests">
            <div v-for="guest in guests" :key="guest.id" class="wcm26-guest">
                <button
                    type="button"
                    class="wcm26-guest-photo grayscale-photo"
                    :aria-label="`Open profile: ${guest.name}`"
                    @click="$emit('open', guest.id)"
                >
                    <ImageSlot :src="guest.portrait" :alt="guest.name" placeholder="Portrait" />
                </button>
                <div>
                    <p class="wcm26-guest-name">{{ guest.name }}</p>
                    <p class="wcm26-guest-org">{{ guest.org }}</p>
                    <p v-if="guest.role" class="wcm26-guest-role">{{ guest.role }}</p>
                </div>
            </div>

            <div class="wcm26-guest">
                <div class="wcm26-guest-more">
                    <p>More guests to be announced</p>
                </div>
            </div>
        </div>
    </section>
</template>
