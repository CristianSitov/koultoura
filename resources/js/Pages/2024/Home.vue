<script setup>
import AppLayout from '../../Layouts/Layout2024.vue';
import MainMenu from '../../Sections/2024/MainMenu.vue';
import HomeHero from '../../Sections/2024/HomeHero.vue';
import Event from '../../Sections/2024/Event.vue';
import Schedule from '../../Sections/2024/Schedule.vue';
import Speakers from '../../Sections/2024/Speakers.vue';
import Sponsors from "../../Sections/2024/Sponsors.vue";
import Venues from "../../Sections/2024/Venues.vue";
import Bottom from "../../Sections/2024/Bottom.vue";
</script>
<script>
import {pageview} from "vue-gtag";

export default {
    data() {
        return {
            observer: null,
            sections: ['home', 'schedule', 'speakers', 'venues', 'partners'], // IDs of sections to observe
        };
    },
    methods: {
        updateUrlByDom(entry) {
            // Update the hash if the element is intersecting (visible)
            // to obtain a hash that can be used for reporting to GA
            if (entry.isIntersecting) {
                const idName = entry.target.id;
                this.$gtag.pageview(this.$inertia.page.url)
                this.$gtag.event('Home/'+idName)
            }
        },
        observeSections() {
            this.observer = new IntersectionObserver(
                (entries) => {
                    entries.forEach((entry) => {
                        this.updateUrlByDom(entry);
                    });
                },
                {
                    root: null, // Use the viewport as root
                    threshold: 0.05, // Trigger when 50% of the section is visible
                }
            );

            // Observe each section
            this.sections.forEach((id) => {
                const section = document.getElementById(id);
                if (section) {
                    this.observer.observe(section);
                }
            });
        },
    },
    mounted() {
        this.observeSections();
    },
    beforeDestroy() {
        // Clean up observer when the component is destroyed
        if (this.observer) {
            this.observer.disconnect();
        }
    },
}
</script>

<template>
    <MainMenu />

    <AppLayout title="Home">
        <HomeHero />

        <Event />

        <Schedule />

        <Speakers />

        <Venues />

        <Sponsors />

        <Bottom />
    </AppLayout>
</template>
