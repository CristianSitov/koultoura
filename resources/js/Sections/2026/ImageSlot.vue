<script setup>
/*
 * Production stand-in for the prototype's <image-slot>: renders the image when
 * one has been supplied, otherwise the grey slot with its caption, so the page
 * reads correctly while portraits and logos are still being collected.
 */
defineProps({
    src: { type: String, default: '' },
    alt: { type: String, default: '' },
    placeholder: { type: String, default: 'Drop an image' },
    fit: { type: String, default: 'cover' }, // cover | contain
});
</script>

<template>
    <div class="wcm26-slot" :class="fit === 'contain' ? 'wcm26-slot-contain' : 'wcm26-slot-cover'">
        <img v-if="src" :src="src" :alt="alt" />
        <p v-else class="wcm26-slot-empty">{{ placeholder }}</p>
    </div>
</template>
