<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import ImageSlot from './ImageSlot.vue';

const props = defineProps({
    guests: { type: Array, required: true },
    guestId: { type: String, required: true },
});

defineEmits(['close', 'open']);

const at = computed(() => props.guests.findIndex((g) => g.id === props.guestId));
const guest = computed(() => props.guests[at.value] || null);
const index = computed(() => (at.value < 0 ? '' : String(at.value + 1).padStart(2, '0')));

// The list wraps, so there is always somewhere to go next.
const previous = computed(() => props.guests[(at.value - 1 + props.guests.length) % props.guests.length] || null);
const next = computed(() => props.guests[(at.value + 1) % props.guests.length] || null);

/*
 * The name assembles itself: every letter is in place from the first frame and
 * only its colour changes, from the page background to the text colour, in a
 * random order. Nothing is added to or removed from the flow, so the heading
 * never reflows and there is no flicker — the letters simply arrive.
 */
const REVEAL_MS = 26;

const letters = ref([]);
let reveal = null;

// Words stay whole: each is an inline-block, so a line can only break between
// them and never in the middle of a name.
const words = computed(() => {
    const grouped = [];

    letters.value.forEach((letter) => {
        if (letter.word >= grouped.length) {
            grouped.push([]);
        }

        grouped[letter.word].push(letter);
    });

    return grouped;
});

function start(name) {
    clearInterval(reveal);

    let cursor = 0;

    letters.value = [...(name ?? '')].reduce((all, char) => {
        if (char === ' ') {
            cursor++;

            return all;
        }

        all.push({ char, word: cursor, i: all.length, shown: false });

        return all;
    }, []);

    const reduced = typeof window !== 'undefined'
        && window.matchMedia
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (reduced) {
        letters.value.forEach((letter) => (letter.shown = true));

        return;
    }

    const queue = letters.value.map((letter) => letter.i);

    for (let i = queue.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [queue[i], queue[j]] = [queue[j], queue[i]];
    }

    reveal = setInterval(() => {
        const nextIndex = queue.pop();

        if (nextIndex === undefined) {
            clearInterval(reveal);
            reveal = null;

            return;
        }

        letters.value[nextIndex].shown = true;
    }, REVEAL_MS);
}

// Paging to another guest deals the new name out again.
watch(() => guest.value?.name, (name) => start(name), { immediate: true });

onBeforeUnmount(() => clearInterval(reveal));
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
                <p>{{ $t('Guest') }} · {{ index }}</p>
                <button
                    type="button"
                    class="btn btn-secondary btn-flush"
                    style="height: 40px; gap: 10px"
                    :aria-label="$t('Close')"
                    @click="$emit('close')"
                >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
                        <path d="M5 5l14 14M19 5L5 19"></path>
                    </svg>
                    {{ $t('Close') }}
                </button>
            </div>

            <div class="wcm26-split wcm26-profile-split">
                <div class="wcm26-profile-photo grayscale-photo">
                    <ImageSlot :src="guest.portrait" :alt="guest.name" :placeholder="$t('Portrait')" />
                </div>

                <div class="wcm26-profile-text">
                    <p class="wcm26-profile-org">{{ guest.org }}</p>
                    <h2 class="wcm26-head-t wcm26-profile-name" :aria-label="guest.name">
                        <template v-for="(word, w) in words" :key="w"
                            ><span aria-hidden="true" class="wcm26-profile-word"
                                ><span
                                    v-for="letter in word"
                                    :key="letter.i"
                                    :class="{ 'is-pending': !letter.shown }"
                                    >{{ letter.char }}</span
                                ></span
                            ><template v-if="w < words.length - 1">{{ ' ' }}</template></template
                        >
                    </h2>
                    <p v-if="guest.role" class="wcm26-profile-role">{{ guest.role }}</p>
                    <p class="wcm26-profile-bio">{{ guest.bio }}</p>
                </div>
            </div>

            <nav v-if="guests.length > 1" class="wcm26-profile-pager" :aria-label="$t('Guests')">
                <button type="button" class="wcm26-profile-pager-btn" @click="$emit('open', previous.id)">
                    <span class="wcm26-profile-pager-label">{{ $t('Previous') }}</span>
                    <span class="wcm26-profile-pager-name">{{ previous.name }}</span>
                </button>
                <button type="button" class="wcm26-profile-pager-btn wcm26-profile-pager-next" @click="$emit('open', next.id)">
                    <span class="wcm26-profile-pager-label">{{ $t('Next') }}</span>
                    <span class="wcm26-profile-pager-name">{{ next.name }}</span>
                </button>
            </nav>
        </div>
    </div>
</template>
