<script setup>
import ImageSlot from './ImageSlot.vue';
import SectionHead from './SectionHead.vue';

/*
 * Logos are still to be supplied — each slot shows its caption until `logo`
 * points at a file. The 2024 marks already in public/assets/images/logos/ can
 * be dropped in here once the organiser confirms they are current.
 */
const partners = [
    { role: 'Organized by', name: 'Asociația Prin Banat', placeholder: 'Asociația Prin Banat logo', logo: '' },
    {
        role: 'Financed by',
        name: 'Centrul de Proiecte al Municipiului Timișoara',
        placeholder: 'Centrul de Proiecte logo',
        logo: '',
    },
    { role: 'Venue partner', name: 'FABER', placeholder: 'FABER logo', logo: '' },
];
</script>

<template>
    <section id="partners" class="wcm26-section">
        <SectionHead n="07" title="Partners" />

        <div class="wcm26-partners">
            <div v-for="partner in partners" :key="partner.name">
                <p class="wcm26-partner-role">{{ partner.role }}</p>
                <div class="wcm26-partner-logo grayscale-photo">
                    <ImageSlot :src="partner.logo" :alt="partner.name" :placeholder="partner.placeholder" fit="contain" />
                </div>
                <p class="wcm26-partner-name">{{ partner.name }}</p>
            </div>

            <div>
                <p class="wcm26-partner-role">Partners</p>
                <div class="wcm26-partner-empty">
                    <p>To be announced</p>
                </div>
            </div>
        </div>
    </section>
</template>
