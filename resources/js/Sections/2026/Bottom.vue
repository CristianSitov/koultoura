<script setup>
/*
 * Social handles are still open items from the handoff — swap the two
 * placeholder hrefs for the symposium's real Instagram and Facebook pages.
 */
defineProps({
    // The cookie policy lives under the edition's own path.
    base: { type: String, default: '/2026' },
});
</script>

<template>
    <footer class="wcm26-footer">
        <div>
            <p class="wcm26-footer-brand">Why Culture Matters</p>
            <p>{{ $t('International Symposium · Timișoara') }}</p>
        </div>

        <div class="wcm26-footer-links">
            <a href="https://www.instagram.com/" target="_blank" rel="noopener">Instagram</a>
            <a href="https://www.facebook.com/" target="_blank" rel="noopener">Facebook</a>
            <a href="/2024">{{ $t(':year edition', { year: 2024 }) }}</a>
            <a href="/2022">{{ $t(':year edition', { year: 2022 }) }}</a>
            <a :href="`${base}/support`">{{ $t('Support us') }}</a>
            <a :href="`${base}/cookies`">{{ $t('Privacy & cookies') }}</a>
        </div>
    </footer>
</template>
