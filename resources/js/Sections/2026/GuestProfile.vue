<script setup>
import { computed } from 'vue';
import ImageSlot from './ImageSlot.vue';
import { guests } from './guests';

const props = defineProps({
    guestId: { type: String, required: true },
});

defineEmits(['close']);

const guest = computed(() => guests.find((g) => g.id === props.guestId) || null);
const index = computed(() => {
    const at = guests.findIndex((g) => g.id === props.guestId);

    return at < 0 ? '' : String(at + 1).padStart(2, '0');
});
</script>

<template>
    <div
        v-if="guest"
        class="wcm26-profile"
        role="dialog"
        aria-modal="true"
        :aria-label="guest.name"
        @click="$emit('close')"
    >
        <div class="wcm26-profile-inner" @click.stop>
            <div class="wcm26-profile-bar">
                <p>Guest · {{ index }}</p>
                <button
                    type="button"
                    class="btn btn-secondary btn-flush"
                    style="height: 40px; gap: 10px"
                    aria-label="Close"
                    @click="$emit('close')"
                >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
                        <path d="M5 5l14 14M19 5L5 19"></path>
                    </svg>
                    Close
                </button>
            </div>

            <div class="wcm26-split wcm26-profile-split">
                <div class="wcm26-profile-photo grayscale-photo">
                    <ImageSlot :src="guest.portrait" :alt="guest.name" placeholder="Portrait" />
                </div>

                <div class="wcm26-profile-text">
                    <p class="wcm26-profile-org">{{ guest.org }}</p>
                    <h2 class="wcm26-head-t">{{ guest.name }}</h2>
                    <p v-if="guest.role" class="wcm26-profile-role">{{ guest.role }}</p>
                    <p class="wcm26-profile-bio">{{ guest.bio }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
