<script setup>
import { Head, usePage } from '@inertiajs/inertia-vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import '../../../css/wcm2026.css';

import SiteNav from '../../Sections/2026/SiteNav.vue';
import MenuOverlay from '../../Sections/2026/MenuOverlay.vue';
import Hero from '../../Sections/2026/Hero.vue';
import About from '../../Sections/2026/About.vue';
import Format from '../../Sections/2026/Format.vue';
import Themes from '../../Sections/2026/Themes.vue';
import Guests from '../../Sections/2026/Guests.vue';
import GuestProfile from '../../Sections/2026/GuestProfile.vue';
import Programme from '../../Sections/2026/Programme.vue';
import Register from '../../Sections/2026/Register.vue';
import Location from '../../Sections/2026/Location.vue';
import Partners from '../../Sections/2026/Partners.vue';
import Bottom from '../../Sections/2026/Bottom.vue';

const props = defineProps({
    guests: { type: Array, default: () => [] },
    // Set when the page was entered at /2026/guests/{slug}.
    guest: { type: String, default: '' },
});

const locale = computed(() => usePage().props.value.locale || 'en');
const otherLocale = computed(() => (locale.value === 'ro' ? 'en' : 'ro'));

// The same page in the other language, profile and all.
const localeBase = (which) => (which === 'en' ? '/2026' : '/2026/ro');
const otherLocaleUrl = computed(() =>
    openGuest.value ? `${localeBase(otherLocale.value)}/guests/${openGuest.value}` : localeBase(otherLocale.value)
);

const theme = ref('light');
const menuOpen = ref(false);
const navHidden = ref(false);
const themesExpanded = ref(false);
const openGuest = ref(props.guests.some((g) => g.id === props.guest) ? props.guest : null);

const isDark = computed(() => theme.value === 'dark');
const overlayOpen = computed(() => menuOpen.value || openGuest.value !== null);

let lastY = 0;

function onScroll() {
    const y = window.scrollY;
    navHidden.value = y > lastY && y > 120;
    lastY = y;
}

function onKeydown(event) {
    if (event.key !== 'Escape') {
        return;
    }

    menuOpen.value = false;
    openGuest.value = null;
}

function toggleTheme() {
    theme.value = isDark.value ? 'light' : 'dark';
    localStorage.setItem('wcm-theme', theme.value);
}

onMounted(() => {
    // Light unless the visitor has asked for dark. The system preference is
    // deliberately ignored: dark is a choice made here, and it is remembered.
    theme.value = localStorage.getItem('wcm-theme') === 'dark' ? 'dark' : 'light';

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('keydown', onKeydown);
    window.addEventListener('popstate', onPopState);
    window.history.replaceState({ guest: openGuest.value }, '', window.location.pathname);
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', onScroll);
    window.removeEventListener('keydown', onKeydown);
    window.removeEventListener('popstate', onPopState);
    document.body.style.overflow = '';
});

watch(overlayOpen, (open) => {
    document.body.style.overflow = open ? 'hidden' : '';
});

/*
 * An open profile has its own address, so it can be linked and the back button
 * closes it. The overlay is part of this page, so the URL is swapped in place
 * rather than routed — an Inertia visit would tear the landing page down and
 * rebuild it behind the overlay.
 */
watch(openGuest, (id, was) => {
    const base = localeBase(locale.value);
    const url = id ? `${base}/guests/${id}` : base;

    if (window.location.pathname === url) {
        return;
    }

    // Opening from the grid is a new step back; stepping between guests replaces it.
    if (id && was) {
        window.history.replaceState({ guest: id }, '', url);
    } else {
        window.history.pushState({ guest: id }, '', url);
    }
});

function onPopState(event) {
    const id = event.state?.guest ?? null;
    openGuest.value = props.guests.some((g) => g.id === id) ? id : null;
}
</script>

<template>
    <Head :title="$t('International Symposium · 7–10 October 2026 · Timișoara')">
        <link rel="alternate" hreflang="en" href="/2026" />
        <link rel="alternate" hreflang="ro" href="/2026/ro" />
        <link rel="alternate" hreflang="x-default" href="/2026" />
        <!--
            Unlisted while the page is in review: nothing on the site links here
            and search engines are told to leave it alone. Drop this meta — and
            add a canonical — when the page goes live.
        -->
        <meta head-key="robots" name="robots" content="noindex, nofollow" />
    </Head>

    <div class="wcm26" :data-theme="theme" :lang="locale">
        <SiteNav
            :hidden="navHidden"
            :is-dark="isDark"
            :menu-open="menuOpen"
            :other-locale="otherLocale"
            :other-locale-url="otherLocaleUrl"
            @toggle-theme="toggleTheme"
            @open-menu="menuOpen = true"
        />

        <MenuOverlay
            v-if="menuOpen"
            :other-locale="otherLocale"
            :other-locale-url="otherLocaleUrl"
            @close="menuOpen = false"
        />

        <div class="wcm26-shell">
            <Hero />

            <About />
            <hr class="wcm26-rule" />

            <Format />
            <hr class="wcm26-rule" />

            <Themes :expanded="themesExpanded" @toggle="themesExpanded = !themesExpanded" />
            <hr class="wcm26-rule" />

            <Guests :guests="guests" @open="openGuest = $event" />
            <hr class="wcm26-rule" />

            <Programme />
        </div>

        <Register />

        <div class="wcm26-shell">
            <Location :is-dark="isDark" />
            <hr class="wcm26-rule" />

            <Partners />
            <hr class="wcm26-rule" />

            <Bottom />
        </div>

        <GuestProfile v-if="openGuest" :guests="guests" :guest-id="openGuest" @close="openGuest = null" @open="openGuest = $event" />
    </div>
</template>
