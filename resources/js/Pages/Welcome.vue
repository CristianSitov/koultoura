<script setup>
import FrontLayout from "./../Layouts/FrontLayout.vue";
import HomeHero from './../Components/SectionHomeHero.vue';
import Schedule from "./../Components/SectionSchedule.vue";
import Speakers from "./../Components/SectionSpeakers.vue";
import Venues from "./../Components/SectionVenues.vue";
import Sponsors from "./../Components/SectionSponsors.vue";
</script>

<template>
    <FrontLayout title="Dashboard">
        <HomeHero />

        <Schedule />

        <Speakers />

        <Venues />

        <Sponsors />
    </FrontLayout>
</template>
