<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { usePage } from '@inertiajs/inertia-vue3';
import Logo from './Logo.vue';
import Bottom from './Bottom.vue';
import MenuOverlay from './MenuOverlay.vue';

/*
 * The chrome the secondary pages sit in: the landing page's bar and footer,
 * with the same theme and language switch.
 *
 * The bar carries the menu but not the Register button the landing page has —
 * the menu already offers it, and a CTA for the form you are looking at is
 * noise. The menu's own entries point back at the landing page rather than at
 * fragments of a page you are not on.
 */
const props = defineProps({
    base: { type: String, default: '/2026' },
});

const page = usePage();
const locale = computed(() => page.props.value.locale || 'en');
const otherLocale = computed(() => (locale.value === 'ro' ? 'en' : 'ro'));

const menuOpen = ref(false);

// Shared from the server for every 2026 page — see HandleInertiaRequests.
const programmeVisible = computed(() => page.props.value.programmeVisible === true);

// The sections live on the landing page, in the language being read.
const landing = computed(() => (locale.value === 'ro' ? `${props.base}/ro` : props.base));

/*
 * The switch swaps the locale segment of the page you are on, rather than
 * pointing at the registration form from every page.
 *
 * It also names the language in both directions. Only the landing page's own
 * addresses are authoritatively English; everywhere else an address without a
 * locale keeps whatever you were last reading — so linking "EN" at
 * /register from the Romanian page left you in Romanian.
 */
const otherLocaleUrl = computed(() => {
    const [path, query] = (page.url.value || `${props.base}/register`).split('?');
    const rest = path.startsWith(props.base) ? path.slice(props.base.length) : path;

    return props.base
        + '/' + otherLocale.value
        + rest.replace(/^\/(en|ro)(?=\/|$)/, '')
        + (query ? `?${query}` : '');
});

/*
 * The contribution step has no address of its own per language — it reads the
 * language of the registration it belongs to — so there is nothing to switch to.
 */
const canSwitchLocale = computed(() => ! (page.url.value || '').includes('/contribute/'));

// The page behind the menu does not scroll, as on the landing page.
watch(menuOpen, (open) => {
    document.body.style.overflow = open ? 'hidden' : '';
});

// Leaving with the menu open would strand the lock on the next page.
onBeforeUnmount(() => {
    document.body.style.overflow = '';
});

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
        <header class="wcm26-header">
            <div class="wcm26-bar">
                <a :href="base" class="wcm26-brand">
                    <Logo />
                    <span class="wcm26-tagline">
                        <span>{{ $t('International Symposium · 3rd edition · Timișoara') }}</span>
                        <span>{{ $t('by Prin Banat Association') }}</span>
                    </span>
                </a>

                <a
                    v-if="canSwitchLocale"
                    :href="otherLocaleUrl"
                    class="btn btn-secondary btn-icon wcm26-lang"
                    style="width: 40px; height: 40px; margin-left: auto"
                    :lang="otherLocale"
                >{{ otherLocale.toUpperCase() }}</a>

                <button
                    type="button"
                    class="btn btn-secondary btn-icon"
                    :style="{ width: '40px', height: '40px', marginLeft: canSwitchLocale ? '' : 'auto' }"
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

                <button
                    type="button"
                    class="btn btn-secondary btn-flush"
                    style="height: 40px; gap: 10px"
                    :aria-label="$t('Open menu')"
                    :aria-expanded="menuOpen"
                    @click="menuOpen = true"
                >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
                        <path d="M3 6h18M3 12h18M3 18h18"></path>
                    </svg>
                    <span class="wcm26-btn-label">{{ $t('Menu') }}</span>
                </button>
            </div>
        </header>

        <MenuOverlay
            v-if="menuOpen"
            :base="base"
            :landing="landing"
            :programme-visible="programmeVisible"
            :other-locale="otherLocale"
            :other-locale-url="otherLocaleUrl"
            @close="menuOpen = false"
        />

        <div class="wcm26-shell wcm26-page">
            <!-- Only the content column is narrowed; the bar above and the
                 footer below keep the full width the landing page uses. -->
            <div class="wcm26-page-main">
                <slot />
            </div>

            <hr class="wcm26-rule" />
            <Bottom :base="base" />
        </div>
    </div>
</template>
