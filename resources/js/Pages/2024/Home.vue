<script setup>
import AppLayout from '../../Layouts/Layout2024.vue';
import MainMenu from '../../Sections/2024/MainMenu.vue';
import HomeHero from '../../Sections/2024/HomeHero.vue';
import Event from '../../Sections/2024/Event.vue';
import Schedule from '../../Sections/2024/Schedule.vue';
import Speakers from '../../Sections/2024/Speakers.vue';
import Venues from '../../Sections/2024/Venues.vue';
import Sponsors from '../../Sections/2024/Sponsors.vue';

import { onBeforeRouteLeave } from 'vue-router'

// same as beforeRouteLeave option but with no access to `this`
onBeforeRouteLeave((to, from) => {
    const answer = window.confirm(
        'Do you really want to leave? you have unsaved changes!'
    )
    // cancel the navigation and stay on the same page
    if (!answer) return false
})
</script>


<template>
    <MainMenu />

    <AppLayout title="Home">
        <HomeHero />

        <Event />

        <Schedule />

        <Speakers />
    </AppLayout>
</template>
