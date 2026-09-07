<script setup>
import SectionHead from './SectionHead.vue';

defineProps({
    expanded: { type: Boolean, default: false },
});

defineEmits(['toggle']);

/*
 * Three themes, one per day. The Heritage School used to sit here as a fourth
 * card; it is not a theme but an umbrella over sessions across days 2–4, so it
 * has its own block below the grid and its own mark — a hollow square where a
 * theme has a filled one, the same pair the Programme section uses.
 */
const themes = [
    { numeral: 'I', key: 1, grow: true, more: false },
    { numeral: 'II', key: 2, grow: true, more: false },
    // The Heritage School belongs to the education strand, so its note hangs
    // off that card rather than the middle one.
    { numeral: 'III', key: 3, grow: false, more: true },
];
</script>

<template>
    <section id="themes" class="wcm26-section">
        <div class="wcm26-head-split">
            <SectionHead n="03" :title="$t('2026 Themes')" />
            <button type="button" class="btn btn-ghost btn-flush" style="height: 40px" @click="$emit('toggle')">
                {{ expanded ? $t('Show less') : $t('Read the full themes') }}
            </button>
        </div>

        <div class="wcm26-themes">
            <article v-for="theme in themes" :key="theme.key" class="wcm26-theme">
                <p class="wcm26-theme-n"><span class="wcm26-square"></span>{{ theme.numeral }}</p>
                <h3>{{ $t(`theme.${theme.key}.title`) }}</h3>
                <p class="wcm26-theme-q">{{ $t(`theme.${theme.key}.question`) }}</p>
                <p class="wcm26-theme-p" :class="{ 'wcm26-theme-p-grow': theme.grow }">
                    {{ $t(`theme.${theme.key}.body`) }}
                </p>
                <template v-if="expanded">
                    <p class="wcm26-theme-p">{{ $t(`theme.${theme.key}.body2`) }}</p>
                    <p v-if="theme.more" class="wcm26-theme-p">{{ $t(`theme.${theme.key}.more`) }}</p>
                </template>
            </article>
        </div>

        <article class="wcm26-school">
            <div class="wcm26-school-head">
                <p class="wcm26-theme-n">
                    <span class="wcm26-square-open"></span>{{ $t('Across the programme') }}
                </p>
                <span class="tag tag-accent">{{ $t('New in 2026') }}</span>
            </div>

            <div class="wcm26-school-body">
                <div>
                    <h3>{{ $t('school.title') }}</h3>
                    <p class="wcm26-theme-q">{{ $t('school.question') }}</p>
                </div>

                <div>
                    <p class="wcm26-theme-p">{{ $t('school.body') }}</p>
                    <div v-if="expanded" class="wcm26-theme-more">
                        <p>{{ $t('school.more1') }}</p>
                        <p>{{ $t('school.more2') }}</p>
                        <p>{{ $t('school.more3') }}</p>
                    </div>
                    <button type="button" class="btn btn-ghost btn-flush" @click="$emit('toggle')">
                        {{ expanded ? $t('Show less') : $t('Read the full themes') }}
                    </button>
                </div>
            </div>
        </article>
    </section>
</template>
