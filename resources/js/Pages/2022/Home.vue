<script setup>
import AppLayout from '@/Layouts/Layout2022.vue';
import HomeHero from '@/Sections/2022/HomeHero.vue';
import Event from '@/Sections/2022/Event.vue';
import Schedule from '@/Sections/2022/Schedule.vue';
import Speakers from '@/Sections/2022/Speakers.vue';
import Venues from '@/Sections/2022/Venues.vue';
import Sponsors from '@/Sections/2022/Sponsors.vue';
import Ucf from '@/Sections/2022/Ucf.vue';
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
