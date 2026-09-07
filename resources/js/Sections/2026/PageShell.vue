<script setup>
import { computed, onMounted, ref } from 'vue';
import { usePage } from '@inertiajs/inertia-vue3';
import Logo from './Logo.vue';
import Bottom from './Bottom.vue';

/*
 * The chrome the registration pages sit in: the landing page's bar without its
 * menu or CTA — those lead back into a page you are already halfway through
 * leaving — plus the same footer, theme and language switch.
 */
const props = defineProps({
    base: { type: String, default: '/2026' },
});

const locale = computed(() => usePage().props.value.locale || 'en');
const otherLocale = computed(() => (locale.value === 'ro' ? 'en' : 'ro'));
const otherLocaleUrl = computed(() =>
    otherLocale.value === 'en' ? `${props.base}/register` : `${props.base}/ro/register`
);

const theme = ref('light');
const isDark = computed(() => theme.value === 'dark');

function toggleTheme() {
    theme.value = isDark.value ? 'light' : 'dark';
    localStorage.setItem('wcm-theme', theme.value);
}

onMounted(() => {
    theme.value = localStorage.getItem('wcm-theme') === 'dark' ? 'dark' : 'light';
});
</script>

<template>
    <div class="wcm26" :data-theme="theme" :lang="locale">
        <header class="wcm26-header wcm26-header-narrow">
            <div class="wcm26-bar">
                <a :href="base" class="wcm26-brand">
                    <Logo />
                    <span class="wcm26-tagline">
                        <span>{{ $t('International Symposium · 3rd edition · Timișoara') }}</span>
                        <span>{{ $t('by Prin Banat Association') }}</span>
                    </span>
                </a>

                <a
                    :href="otherLocaleUrl"
                    class="btn btn-secondary btn-icon wcm26-lang"
                    style="width: 40px; height: 40px; margin-left: auto"
                    :lang="otherLocale"
                >{{ otherLocale.toUpperCase() }}</a>

                <button
                    type="button"
                    class="btn btn-secondary btn-icon"
                    style="width: 40px; height: 40px"
                    :aria-label="isDark ? $t('Switch to light mode') : $t('Switch to dark mode')"
                    @click="toggleTheme"
                >
                    <svg v-if="isDark" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="4"></circle>
                        <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"></path>
                    </svg>
                    <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"></path>
                    </svg>
                </button>
            </div>
        </header>

        <div class="wcm26-shell wcm26-page">
            <slot />
            <hr class="wcm26-rule" />
            <Bottom />
        </div>
    </div>
</template>
