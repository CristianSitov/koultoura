<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import FlyingLines from './FlyingLines.vue';

/*
 * "what we" holds still while the tail types and erases in red:
 *   inherit. → understand. → choose to protect. → why culture matters
 * On the fourth pass the lead word drops away and the full line types out.
 */
const phrases = ['inherit.', 'understand.', 'choose to protect.', 'why culture matters'];

const typed = ref('');
const step = ref(0);
const caretOn = ref(true);

let timer = null;
let caretTimer = null;
let cursor = { len: 0, phase: 'type' };

const reducedMotion = () =>
    typeof window !== 'undefined' &&
    window.matchMedia &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function tick() {
    const word = phrases[step.value];

    if (cursor.phase === 'type') {
        cursor.len++;
        typed.value = word.slice(0, cursor.len);

        if (cursor.len >= word.length) {
            cursor.phase = 'erase';
            timer = setTimeout(tick, step.value === 3 ? 4600 : 2000);
        } else {
            timer = setTimeout(tick, 65 + Math.random() * 55);
        }

        return;
    }

    cursor.len--;
    typed.value = word.slice(0, Math.max(0, cursor.len));

    if (cursor.len <= 0) {
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
        typed.value = phrases[3];
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

        <p class="wcm26-eyebrow">International Symposium · 3rd Edition · Timișoara</p>

        <h1
            class="wcm26-hero-h1"
            aria-label="what we inherit. what we understand. what we choose to protect. why culture matters"
        >
            <span aria-hidden="true" class="wcm26-hero-line">
                <span v-if="step !== 3" class="wcm26-hero-lead">what&nbsp;we&nbsp;</span>
                <span class="wcm26-hero-tail">
                    {{ typed }}<span class="wcm26-caret" :style="{ opacity: caretOn ? 1 : 0 }"></span>
                </span>
            </span>
        </h1>

        <div class="wcm26-strip">
            <div>
                <p class="wcm26-label">Dates</p>
                <p class="wcm26-strip-v" style="font-feature-settings: 'tnum' 1">7–10 October 2026</p>
            </div>
            <div>
                <p class="wcm26-label">Venue</p>
                <p class="wcm26-strip-v">FABER · Timișoara, Romania</p>
            </div>
            <div>
                <p class="wcm26-label">Entry</p>
                <p class="wcm26-strip-v">Free, registration required</p>
            </div>
        </div>
    </section>
</template>
