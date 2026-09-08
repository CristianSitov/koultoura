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

const page = usePage();
const locale = computed(() => page.props.value.locale || 'en');
const otherLocale = computed(() => (locale.value === 'ro' ? 'en' : 'ro'));

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
            </div>
        </header>

        <div class="wcm26-shell wcm26-page">
            <!-- Only the content column is narrowed; the bar above and the
                 footer below keep the full width the landing page uses. -->
            <div class="wcm26-page-main">
                <slot />
            </div>

            <hr class="wcm26-rule" />
            <Bottom />
        </div>
    </div>
</template>
