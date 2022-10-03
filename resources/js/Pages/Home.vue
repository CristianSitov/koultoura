<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import HomeHero from '@/Sections/HomeHero.vue';
import Event from '@/Sections/Event.vue';
import News from '@/Sections/News.vue';
import Schedule from '@/Sections/Schedule.vue';
import Speakers from '@/Sections/Speakers.vue';
import Venues from '@/Sections/Venues.vue';
import Sponsors from '@/Sections/Sponsors.vue';
</script>

<template>
    <AppLayout title="Home">
        <HomeHero />

        <Event />

        <News />

        <Schedule />

        <Speakers />

        <Venues />

        <Sponsors />
    </AppLayout>
</template>
