<script setup>
import ImageSlot from './ImageSlot.vue';
import SectionHead from './SectionHead.vue';
</script>

<template>
    <section id="about" class="wcm26-section">
        <SectionHead n="01" title="About" />

        <div class="wcm26-split">
            <div>
                <p class="wcm26-lead" style="max-width: 18ch">Four days where research meets practice.</p>
                <div class="wcm26-about-photo grayscale-photo">
                    <ImageSlot placeholder="Photograph from a past edition" />
                </div>
            </div>

            <div class="wcm26-about-copy">
                <p>
                    Why Culture Matters returns to Timișoara for its third edition, bringing together cultural and
                    heritage professionals, researchers, educators and practitioners for four days of conversations,
                    exchange and hands-on learning.
                </p>
                <p>
                    The 2026 edition looks at cultural heritage through three interconnected questions: What threatens
                    it? How do we learn to understand and care for it? And how do the stories we tell shape what we
                    choose to remember and protect?
                </p>
                <p>
                    The programme combines talks, moderated discussions and practical workshops, connecting
                    international perspectives with local experiences and creating a space where research meets
                    practice.
                </p>
                <p class="wcm26-about-close">
                    Because heritage is not only what we inherit. It is what we understand, what we choose to protect,
                    and what we pass on.
                </p>
            </div>
        </div>
    </section>
</template>
