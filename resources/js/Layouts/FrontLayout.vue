<script setup>
import { Head } from '@inertiajs/inertia-vue3';
import MainMenu from '../Components/SectionMainMenu.vue';
import Bottom from "../Components/SectionBottom.vue";

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
