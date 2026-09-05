<script setup>
import SectionHead from './SectionHead.vue';

const formats = [
    {
        label: 'Talks',
        description: 'International perspectives, 20-minute presentations from invited speakers.',
    },
    {
        label: 'Conversations',
        description: 'Moderated discussions between the speakers of each section.',
    },
    {
        label: 'Workshops',
        description:
            'Hands-on learning. Eight pilot workshops of the Heritage School, for children, young people and adults.',
    },
];
</script>

<template>
    <section id="format" class="wcm26-section">
        <SectionHead n="02" title="Format" />

        <div class="wcm26-split wcm26-format">
            <div class="wcm26-figures">
                <div>
                    <p class="wcm26-figure">4</p>
                    <p class="wcm26-figure-label">Days</p>
                </div>
                <div>
                    <p class="wcm26-figure">4</p>
                    <p class="wcm26-figure-label">Themes</p>
                </div>
                <p class="wcm26-figures-note">
                    Why Culture Matters is conceived not only as a space for debate, but as a meeting point for people
                    working with heritage from different disciplines and European contexts — a place to exchange
                    experiences, test ideas and build new connections.
                </p>
            </div>

            <ol class="wcm26-formats">
                <li v-for="format in formats" :key="format.label">
                    <p class="wcm26-formats-label">{{ format.label }}</p>
                    <p class="wcm26-formats-desc">{{ format.description }}</p>
                </li>
            </ol>
        </div>
    </section>
</template>
