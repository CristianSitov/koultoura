<script setup>
import AppLayout from '@/Layouts/Layout2022.vue';
import HomeHero from '@/Sections/HomeHero.vue';
import Event from '@/Sections/Event.vue';
import Schedule from '@/Sections/Schedule.vue';
import Speakers from '@/Sections/Speakers.vue';
import Venues from '@/Sections/Venues.vue';
import Sponsors from '@/Sections/Sponsors.vue';
import Ucf from '@/Sections/Ucf.vue';
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
