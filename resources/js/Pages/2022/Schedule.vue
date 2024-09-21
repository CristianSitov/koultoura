<script setup>
import AppLayout from '@/Layouts/Layout2022.vue';
import HomeHero from '@/Sections/2022/HomeHero.vue';
import Schedule from '@/Sections/2022/Schedule.vue';
import Speakers from '@/Sections/2022/Speakers.vue';
</script>

<template>
    <AppLayout title="Home">
        <HomeHero />
        <Schedule />
        <Speakers />
    </AppLayout>
</template>
