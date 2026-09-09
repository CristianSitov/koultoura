<script setup>
import Logo from './Logo.vue';

/*
 * The association's accounts: the symposium does not have its own.
 */
defineProps({
    // The cookie policy lives under the edition's own path.
    base: { type: String, default: '/2026' },
});
</script>

<template>
    <footer class="wcm26-footer">
        <!-- The same mark and the same ruled tagline as the bar at the top,
             rather than the name set as text. -->
        <div class="wcm26-footer-mark">
            <Logo />
            <span class="wcm26-tagline">
                <span>{{ $t('International Symposium · 3rd edition · Timișoara') }}</span>
                <span>{{ $t('by Prin Banat Association') }}</span>
            </span>
        </div>

        <div class="wcm26-footer-links">
            <a href="https://instagram.com/PrinBanat" target="_blank" rel="noopener">Instagram</a>
            <a href="https://facebook.com/PrinBanat" target="_blank" rel="noopener">Facebook</a>
            <a href="/2024">{{ $t(':year edition', { year: 2024 }) }}</a>
            <a href="/2022">{{ $t(':year edition', { year: 2022 }) }}</a>
            <a :href="`${base}/support`">{{ $t('Support us') }}</a>
            <a :href="`${base}/cookies`">{{ $t('Privacy & cookies') }}</a>
        </div>
    </footer>
</template>
