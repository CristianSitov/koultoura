<script setup>
import { Head } from '@inertiajs/inertia-vue3';
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

const theme = ref('light');
const menuOpen = ref(false);
const navHidden = ref(false);
const themesExpanded = ref(false);
const openGuest = ref(null);

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
    // Follow the system by default; a visitor's own choice wins and is remembered.
    const saved = localStorage.getItem('wcm-theme');
    const system = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    theme.value = saved || system;

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('keydown', onKeydown);
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', onScroll);
    window.removeEventListener('keydown', onKeydown);
    document.body.style.overflow = '';
});

watch(overlayOpen, (open) => {
    document.body.style.overflow = open ? 'hidden' : '';
});
</script>

<template>
    <Head title="International Symposium · 7–10 October 2026 · Timișoara">
        <!--
            Unlisted while the page is in review: nothing on the site links here
            and search engines are told to leave it alone. Drop this meta — and
            add a canonical — when the page goes live.
        -->
        <meta head-key="robots" name="robots" content="noindex, nofollow" />
    </Head>

    <div class="wcm26" :data-theme="theme">
        <SiteNav
            :hidden="navHidden"
            :is-dark="isDark"
            :menu-open="menuOpen"
            @toggle-theme="toggleTheme"
            @open-menu="menuOpen = true"
        />

        <MenuOverlay v-if="menuOpen" @close="menuOpen = false" />

        <div class="wcm26-shell">
            <Hero />

            <About />
            <hr class="wcm26-rule" />

            <Format />
            <hr class="wcm26-rule" />

            <Themes :expanded="themesExpanded" @toggle="themesExpanded = !themesExpanded" />
            <hr class="wcm26-rule" />

            <Guests @open="openGuest = $event" />
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

        <GuestProfile v-if="openGuest" :guest-id="openGuest" @close="openGuest = null" />
    </div>
</template>
