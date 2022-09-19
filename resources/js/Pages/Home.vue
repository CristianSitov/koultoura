<script setup>
import FrontLayout from '@/Layouts/FrontLayout.vue';
import HomeHero from '@/Sections/HomeHero.vue';
import Event from '@/Sections/Event.vue';
import Schedule from '@/Sections/Schedule.vue';
import Speakers from '@/Sections/Speakers.vue';
import Venues from '@/Sections/Venues.vue';
import Sponsors from '@/Sections/Sponsors.vue';
</script>

<template>
    <FrontLayout title="Home">
        <HomeHero />

        <Event />

        <Schedule />

        <Speakers />

        <Venues />

        <Sponsors />
    </FrontLayout>
</template>
