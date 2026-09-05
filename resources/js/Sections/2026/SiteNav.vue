<script setup>
import Logo from './Logo.vue';

defineProps({
    hidden: { type: Boolean, default: false },
    isDark: { type: Boolean, default: false },
    menuOpen: { type: Boolean, default: false },
    otherLocale: { type: String, default: 'ro' },
    otherLocaleUrl: { type: String, default: '/2026/ro' },
});

defineEmits(['toggle-theme', 'open-menu']);
</script>

<template>
    <header class="wcm26-header" :class="{ 'wcm26-header-hidden': hidden && !menuOpen }">
        <div class="wcm26-bar">
            <a href="#top" class="wcm26-brand">
                <Logo />
                <span class="wcm26-tagline">
                    <span>{{ $t('International Symposium · 3rd edition · Timișoara') }}</span>
                    <span>{{ $t('by Prin Banat Association') }}</span>
                </span>
            </a>

            <a
                :href="otherLocaleUrl"
                class="btn btn-secondary btn-icon wcm26-lang"
                style="width: 40px; height: 40px"
                :aria-label="$t('Switch language')"
                :title="$t('Switch language')"
                :lang="otherLocale"
            >{{ otherLocale.toUpperCase() }}</a>

            <button
                type="button"
                class="btn btn-secondary btn-icon"
                style="width: 40px; height: 40px"
                :aria-label="isDark ? $t('Switch to light mode') : $t('Switch to dark mode')"
                :title="isDark ? $t('Switch to light mode') : $t('Switch to dark mode')"
                @click="$emit('toggle-theme')"
            >
                <svg v-if="isDark" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="4"></circle>
                    <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"></path>
                </svg>
                <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"></path>
                </svg>
            </button>

            <a href="#register" class="btn btn-primary btn-flush" style="height: 40px">{{ $t('Register interest') }}</a>

            <button
                type="button"
                class="btn btn-secondary btn-flush"
                style="height: 40px; gap: 10px"
:aria-label="$t('Open menu')"
                :aria-expanded="menuOpen"
                @click="$emit('open-menu')"
            >
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
                    <path d="M3 6h18M3 12h18M3 18h18"></path>
                </svg>
                <span class="wcm26-btn-label">{{ $t('Menu') }}</span>
            </button>
        </div>
    </header>
</template>
