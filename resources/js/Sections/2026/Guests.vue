<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import ImageSlot from './ImageSlot.vue';
import SectionHead from './SectionHead.vue';
import { rgbShift } from './rgbShift';

const props = defineProps({
    guests: { type: Array, required: true },
    // The guest profile lives at its own address; the card is a real link to
    // it, and localised, so the base carries the locale segment.
    base: { type: String, default: '/2026' },
});

const emit = defineEmits(['open']);

const href = (guest) => `${props.base}/guests/${guest.id}`;

/*
 * A real link that still opens the overlay in place. A plain click is caught
 * and turned into the overlay; a modified click — new tab, new window, or the
 * middle button — is left alone, so the profile can be opened for real the way
 * any link can. Same reason the address is a real one: it can be copied and
 * shared, and a crawler can follow it.
 */
function openProfile(event, guest) {
    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0) {
        return;
    }

    event.preventDefault();
    emit('open', guest.id);
}

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
                <a
                    :href="href(guest)"
                    class="wcm26-guest-photo"
                    :aria-label="$t('Open profile: :name', { name: guest.name })"
                    @click="openProfile($event, guest)"
                >
                    <ImageSlot :src="guest.portrait" :alt="guest.name" :placeholder="$t('Portrait')" />
                </a>

                <!-- What they do sits above the name; where they are from sits
                     under it, as a link out to the institution. -->
                <p v-if="guest.role" class="wcm26-guest-role">{{ guest.role }}</p>
                <!-- Given name over family name: two short lines read better
                     in this column than one that has to shrink to fit. -->
                <h3 class="wcm26-guest-name">
                    <a :href="href(guest)" @click="openProfile($event, guest)">
                        <span>{{ guest.name.split(' ')[0] }}</span>
                        <span>{{ guest.name.split(' ').slice(1).join(' ') }}</span>
                    </a>
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
