<script setup>
import { Head } from '@inertiajs/inertia-vue3';
import MainMenu from '@/Sections/MainMenu.vue';
import Bottom from '@/Sections/Bottom.vue';

defineProps({
    title: String,
});
</script>

<template>
    <div>
        <Head :title="title" />

        <MainMenu />

        <main class="snap-y">
            <slot />
        </main>

        <Bottom />
    </div>
</template>
