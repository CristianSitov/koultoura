<script setup>
import { onBeforeUnmount, onMounted, ref, computed } from 'vue';
import { trans } from 'laravel-vue-i18n';
import FlyingLines from './FlyingLines.vue';

/*
 * One line is typed and erased at a time:
 *   what we inherit. → what we understand. → what we choose to protect.
 *   → why culture matters
 *
 * "what we" holds still across the first three, because erasing stops at the
 * lead instead of running back to nothing. The fourth line has no lead, so that
 * transition erases the whole line — and the wrap back to the first types
 * "what we" out again from scratch.
 */
const lead = computed(() => trans('hero.lead'));

const lines = computed(() => [
    lead.value + trans('hero.inherit'),
    lead.value + trans('hero.understand'),
    lead.value + trans('hero.protect'),
    trans('hero.tagline'),
]);

// How far back an erase goes before the next line starts typing: to the lead
// while the next line still shares it, otherwise all the way.
const floors = computed(() => [lead.value.length, lead.value.length, 0, 0]);

const typed = ref('');
const step = ref(0);
const caretOn = ref(true);

// The lead is only styled apart while the line actually has one.
const leadLength = computed(() => (step.value === 3 ? 0 : lead.value.length));
const leadText = computed(() => typed.value.slice(0, leadLength.value));
const tailText = computed(() => typed.value.slice(leadLength.value));

let timer = null;
let caretTimer = null;
let cursor = { len: 0, phase: 'type' };

const reducedMotion = () =>
    typeof window !== 'undefined' &&
    window.matchMedia &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function tick() {
    const line = lines.value[step.value];

    if (cursor.phase === 'type') {
        cursor.len++;
        typed.value = line.slice(0, cursor.len);

        if (cursor.len >= line.length) {
            cursor.phase = 'erase';
            timer = setTimeout(tick, step.value === 3 ? 4600 : 2000);
        } else {
            timer = setTimeout(tick, 65 + Math.random() * 55);
        }

        return;
    }

    const floor = floors.value[step.value];

    cursor.len--;
    typed.value = line.slice(0, Math.max(floor, cursor.len));

    if (cursor.len <= floor) {
        cursor.len = floor;
        cursor.phase = 'type';
        step.value = (step.value + 1) % 4;
        timer = setTimeout(tick, 420);

        return;
    }

    timer = setTimeout(tick, 28);
}

onMounted(() => {
    if (reducedMotion()) {
        // No looping animation: rest on the line the sequence ends on.
        step.value = 3;
        typed.value = lines.value[3];
        caretOn.value = false;

        return;
    }

    timer = setTimeout(tick, 700);
    caretTimer = setInterval(() => (caretOn.value = !caretOn.value), 530);
});

onBeforeUnmount(() => {
    clearTimeout(timer);
    clearInterval(caretTimer);
});
</script>

<template>
    <section id="top" class="wcm26-hero">
        <FlyingLines />

        <p class="wcm26-eyebrow">{{ $t('International Symposium · 3rd Edition · Timișoara') }}</p>

        <h1
            class="wcm26-hero-h1"
            :aria-label="$t('hero.aria')"
        >
            <span aria-hidden="true" class="wcm26-hero-line"
                ><span class="wcm26-hero-lead">{{ leadText }}</span
                ><span class="wcm26-hero-tail"
                    >{{ tailText }}<span class="wcm26-caret" :style="{ opacity: caretOn ? 1 : 0 }"></span
                ></span
            ></span>
        </h1>

        <div class="wcm26-strip">
            <div>
                <p class="wcm26-label">{{ $t('Dates') }}</p>
                <p class="wcm26-strip-v" style="font-feature-settings: 'tnum' 1">{{ $t('7–10 October 2026') }}</p>
            </div>
            <div>
                <p class="wcm26-label">{{ $t('Venue') }}</p>
                <p class="wcm26-strip-v">{{ $t('FABER · Timișoara, Romania') }}</p>
            </div>
            <div>
                <p class="wcm26-label">{{ $t('Entry') }}</p>
                <p class="wcm26-strip-v">{{ $t('Pay what you can, registration required') }}</p>
            </div>
        </div>
    </section>
</template>
