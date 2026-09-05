<script setup>
import SectionHead from './SectionHead.vue';

const formats = ['talks', 'conversations', 'workshops'];
</script>

<template>
    <section id="format" class="wcm26-section">
        <SectionHead n="02" :title="$t('Format')" />

        <div class="wcm26-split wcm26-format">
            <div class="wcm26-figures">
                <div>
                    <p class="wcm26-figure">4</p>
                    <p class="wcm26-figure-label">{{ $t('Days') }}</p>
                </div>
                <div>
                    <p class="wcm26-figure">3</p>
                    <p class="wcm26-figure-label">{{ $t('Themes') }}</p>
                </div>
                <p class="wcm26-figures-note">{{ $t('format.note') }}</p>
            </div>

            <ol class="wcm26-formats">
                <li v-for="format in formats" :key="format">
                    <p class="wcm26-formats-label">{{ $t(`format.${format}.label`) }}</p>
                    <p class="wcm26-formats-desc">{{ $t(`format.${format}.description`) }}</p>
                </li>
            </ol>
        </div>
    </section>
</template>
