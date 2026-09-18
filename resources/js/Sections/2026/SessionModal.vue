<script setup>
import { computed, onBeforeUnmount, onMounted } from 'vue';
import ImageSlot from './ImageSlot.vue';

/*
 * The panel behind a workshop or a guided tour. A plain slot never opens one;
 * these do, because they carry more than a card can hold — a picture, who
 * leads it, a described session, and a place you have to sign up for.
 */
const props = defineProps({
    session: { type: Object, required: true },
    base: { type: String, default: '/2026' },
});

const emit = defineEmits(['close']);

const detail = computed(() => props.session.detail || {});
const label = computed(() => (props.session.type === 'tour' ? 'Guided tour' : 'Workshop'));
const booking = computed(() => props.session.booking);

function onKey(event) {
    if (event.key === 'Escape') {
        emit('close');
    }
}

onMounted(() => {
    document.body.style.overflow = 'hidden';
    window.addEventListener('keydown', onKey);
});

onBeforeUnmount(() => {
    document.body.style.overflow = '';
    window.removeEventListener('keydown', onKey);
});
</script>

<template>
    <div class="wcm26-sheet" role="dialog" aria-modal="true" :aria-label="session.title" @click="$emit('close')">
        <div class="wcm26-sheet-inner" @click.stop>
            <button type="button" class="wcm26-sheet-close btn btn-secondary btn-flush" :aria-label="$t('Close')" @click="$emit('close')">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
                    <path d="M5 5l14 14M19 5L5 19"></path>
                </svg>
                <span class="wcm26-btn-label">{{ $t('Close') }}</span>
            </button>

            <div v-if="detail.image" class="wcm26-sheet-photo">
                <ImageSlot :src="detail.image" :alt="session.title" :placeholder="$t(label)" />
            </div>

            <div class="wcm26-sheet-body">
                <p class="wcm26-label wcm26-label-13">
                    {{ session.time }} · {{ $t(label) }}<template v-if="session.school"> · {{ $t('Heritage School') }}</template>
                </p>

                <h2 class="wcm26-sheet-title">{{ session.title }}</h2>
                <p v-if="detail.subtitle" class="wcm26-sheet-subtitle">{{ detail.subtitle }}</p>

                <p v-if="detail.people && detail.people.length" class="wcm26-sheet-people">
                    <template v-for="(person, i) in detail.people" :key="i">
                        <a v-if="person.url" :href="person.url">{{ person.name }}</a>
                        <span v-else>{{ person.name }}</span><template v-if="i < detail.people.length - 1">, </template>
                    </template>
                </p>

                <div v-if="detail.description" class="wcm26-sheet-desc" v-html="detail.description"></div>

                <div class="wcm26-sheet-foot">
                    <p v-if="detail.capacity" class="wcm26-sheet-places">
                        <template v-if="detail.full">{{ $t('Fully booked') }}</template>
                        <template v-else-if="detail.placesLeft !== null">{{ $t(':n places left', { n: detail.placesLeft }) }}</template>
                    </p>

                    <a
                        v-if="booking && !detail.full"
                        :href="booking.url"
                        class="btn btn-primary btn-flush wcm26-sheet-book"
                    >{{ $t('Book a place') }}</a>
                </div>
            </div>
        </div>
    </div>
</template>
