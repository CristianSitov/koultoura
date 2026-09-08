<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';

defineProps({
    n: { type: String, required: true },
    title: { type: String, required: true },
});

/*
 * The head arrives as you reach it. The hidden state is added by this script
 * rather than written into the stylesheet, so a page with no JavaScript — or
 * one where this never runs — shows the heading outright instead of leaving it
 * invisible waiting for a class that is never coming.
 *
 * A head already on screen at load is caught by the observer's first callback,
 * which fires on observe, so the section you land on animates like the rest.
 */
const head = ref(null);
let observer = null;

const still = () =>
    typeof window === 'undefined' ||
    ! window.IntersectionObserver ||
    (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);

onMounted(() => {
    if (! head.value || still()) {
        return;
    }

    head.value.classList.add('wcm26-head-anim');

    observer = new IntersectionObserver(([entry]) => {
        if (! entry.isIntersecting) {
            return;
        }

        entry.target.classList.add('is-in');
        observer.disconnect();
    }, { rootMargin: '0px 0px -12% 0px' });

    observer.observe(head.value);
});

onBeforeUnmount(() => observer?.disconnect());
</script>

<template>
    <div ref="head" class="wcm26-head">
        <span class="wcm26-head-n">{{ n }}</span>
        <h2 class="wcm26-head-t">{{ title }}</h2>
    </div>
</template>
