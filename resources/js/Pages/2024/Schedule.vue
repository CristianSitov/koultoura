<script setup>
import AppLayout from '@/Layouts/Layout2024.vue';
import HomeHero from '@/Sections/HomeHero.vue';
import Schedule from '@/Sections/Schedule.vue';
import Speakers from '@/Sections/Speakers.vue';
</script>

<template>
    <AppLayout title="Home">
        <HomeHero />
        <Schedule />
        <Speakers />
    </AppLayout>
</template>
