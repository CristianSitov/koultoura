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
export default {
    data() {
        return {
            observer: null,
            sections: ['home', 'schedule', 'speakers', 'venues', 'partners'], // IDs of sections to observe
            gTracker: this.$gtag
        };
    },
    methods: {
        updateURLHash(entry) {
            // Update the hash if the element is intersecting (visible)
            if (entry.isIntersecting) {
                const newHash = `#${entry.target.id}`;
                if (window.location.hash !== newHash) {
                    history.replaceState(null, null, newHash);
                }
            }
        },
        observeSections() {
            this.observer = new IntersectionObserver(
                (entries) => {
                    entries.forEach((entry) => {
                        this.updateURLHash(entry);
                        console.log(entry.target.id, entry.isIntersecting);
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
