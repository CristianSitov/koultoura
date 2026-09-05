<script setup>
import SectionHead from './SectionHead.vue';

const days = [
    { weekday: 'Wednesday', num: '07', label: 'Day 1' },
    { weekday: 'Thursday', num: '08', label: 'Day 2' },
    { weekday: 'Friday', num: '09', label: 'Day 3' },
    { weekday: 'Saturday', num: '10', label: 'Day 4' },
];
</script>

<template>
    <section id="programme" class="wcm26-section">
        <SectionHead n="05" title="Programme" />

        <div class="wcm26-days">
            <div v-for="day in days" :key="day.num" class="wcm26-day">
                <p class="wcm26-label wcm26-label-13">{{ day.weekday }}</p>
                <p class="wcm26-day-n">{{ day.num }}</p>
                <p class="wcm26-day-label">{{ day.label }}</p>
            </div>
        </div>

        <p class="wcm26-soon">Full programme coming soon.</p>
        <p class="wcm26-soon-note">Talks, conversations and workshops across four days. October 2026.</p>
    </section>
</template>
