<script setup>
import AppLayout from '@/Layouts/Layout2024.vue';
import HomeHero from '@/Sections/2024/HomeHero.vue';
import Event from '@/Sections/2024/Event.vue';
import Schedule from '@/Sections/2024/Schedule.vue';
import Speakers from '@/Sections/2024/Speakers.vue';
import Venues from '@/Sections/2024/Venues.vue';
import Sponsors from '@/Sections/2024/Sponsors.vue';
import Ucf from '@/Sections/2024/Ucf.vue';
</script>

<template>
    <AppLayout title="Home">
        <HomeHero />

        <Event />

        <Schedule />

        <Speakers />

        <Venues />

        <Sponsors />

        <Ucf />
    </AppLayout>
</template>
