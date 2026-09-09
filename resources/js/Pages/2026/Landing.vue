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
    programme: { type: Array, default: () => [] },
    themeBars: { type: Array, default: () => [] },
    schoolDays: { type: Array, default: () => [] },
    // The section is off until there is a schedule worth showing.
    programmeVisible: { type: Boolean, default: false },
    // Set when the page was entered at a guest profile.
    guest: { type: String, default: '' },
    // The page's own address, which carries a secret segment while unlisted.
    base: { type: String, default: '/2026' },
    // Whether the edition is public — WCM_2026_PUBLIC, via config/wcm.php.
    isPublic: { type: Boolean, default: false },
});

const locale = computed(() => usePage().props.value.locale || 'en');
const otherLocale = computed(() => (locale.value === 'ro' ? 'en' : 'ro'));

// The same page in the other language, profile and all.
const localeBase = (which) => (which === 'en' ? props.base : `${props.base}/ro`);

// Once the page is indexable it should say which of its two addresses is the
// one to keep; before that there is nothing to be canonical about.
const canonical = computed(() => localeBase(locale.value));
const otherLocaleUrl = computed(() =>
    openGuest.value ? `${localeBase(otherLocale.value)}/guests/${openGuest.value}` : localeBase(otherLocale.value)
);

const theme = ref('light');
const menuOpen = ref(false);
const navHidden = ref(false);
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
        <link rel="alternate" hreflang="en" :href="base" />
        <link rel="alternate" hreflang="ro" :href="`${base}/ro`" />
        <link rel="alternate" hreflang="x-default" :href="base" />
        <!--
            Unlisted while the page is in review: nothing on the site links here
            and search engines are told to leave it alone. Public, it says so
            instead and names its canonical address — one switch, no edit here.
        -->
        <meta
            head-key="robots"
            name="robots"
            :content="isPublic ? 'index, follow' : 'noindex, nofollow'"
        />
        <!--
            Always rendered, never behind v-if: this Inertia's <Head> compares
            node types against Vue 3.2's names for them, so a v-if that is false
            hands it a comment node it does not recognise and it throws while
            building the tag. Harmless to point at an unlisted page — it is
            noindex until the switch says otherwise.
        -->
        <link head-key="canonical" rel="canonical" :href="canonical" />
    </Head>

    <div class="wcm26" :data-theme="theme" :lang="locale">
        <SiteNav
            :base="base"
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
            :base="base"
            :programme-visible="programmeVisible"
            @close="menuOpen = false"
        />

        <div class="wcm26-shell">
            <Hero />

            <About />
            <hr class="wcm26-rule" />

            <Format />
            <hr class="wcm26-rule" />

            <Themes />
            <hr class="wcm26-rule" />

            <Guests :guests="guests" @open="openGuest = $event" />

            <template v-if="programmeVisible">
                <hr class="wcm26-rule" />

                <Programme :days="programme" :theme-bars="themeBars" :school-days="schoolDays" />
            </template>
        </div>

        <Register :base="base" />

        <div class="wcm26-shell">
            <Location :is-dark="isDark" />
            <hr class="wcm26-rule" />

            <Partners />
            <hr class="wcm26-rule" />

            <Bottom :base="base" />
        </div>

        <GuestProfile v-if="openGuest" :guests="guests" :guest-id="openGuest" @close="openGuest = null" @open="openGuest = $event" />
    </div>
</template>
