<script setup>
import AppLayout from '@/Layouts/Layout2024.vue';
import HomeHero from '@/Sections/2024/HomeHero.vue';
import Schedule from '@/Sections/2024/Schedule.vue';
import Speakers from '@/Sections/2024/Speakers.vue';
</script>

<template>
    <AppLayout title="Home">
        <HomeHero />
        <Schedule />
        <Speakers />
    </AppLayout>
</template>
