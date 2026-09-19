<script setup>
import Logo from './Logo.vue';
import { computed } from 'vue';
import { menuItems, menuPages } from './menu';
import { sectionSlug } from './sections.js';

const props = defineProps({
    // Hidden sections are not offered: a menu entry that scrolls nowhere is
    // worse than a shorter menu.
    programmeVisible: { type: Boolean, default: false },
    base: { type: String, default: '/2026' },
    /*
     * The landing page's own address, carrying the locale segment. The section
     * links hang off it — `/2026/themes`, `/2026/ro/themes` — rather than being
     * bare `#themes` fragments, so each section has a real, shareable address.
     */
    landing: { type: String, default: '/2026' },
});

const emit = defineEmits(['close']);

// The address is in the language being read — /2026/ro/program, not the
// English anchor the page scrolls by.
const locale = computed(() => (props.landing.endsWith('/ro') ? 'ro' : 'en'));

const items = computed(() => [
    ...menuItems
        .filter((item) => item.anchor !== 'programme' || props.programmeVisible)
        .map((item) => ({ ...item, url: props.landing + '/' + sectionSlug(item.anchor, locale.value) })),
    // Last, and off the numbered sequence: these are pages of their own, so
    // they hang off `base` and are marked as the odd ones out.
    ...menuPages.map((page) => ({ ...page, url: props.base + page.href })),
]);

/*
 * A section link is a real link: it can be opened in a new tab, copied, or
 * followed by a crawler, and typing it loads the landing page already scrolled
 * to that section. But when the section is on the page you are already reading,
 * a plain click should glide to it rather than reload — so the plain click is
 * caught and turned into a smooth scroll, and a modified or middle click is
 * left alone. On any page that does not hold the section (Register, Support),
 * there is nothing to catch, so the link simply navigates.
 */
function go(event, item) {
    if (! item.anchor) {
        return;
    }

    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0) {
        return;
    }

    const target = document.getElementById(item.anchor);

    if (! target) {
        return;
    }

    event.preventDefault();
    window.history.replaceState(window.history.state, '', item.url);
    emit('close');
    // A task, not a frame: the overlay has to unmount and give the body its
    // scroll back first, and a frame callback is starved while the tab is in
    // the background — a timeout is not.
    setTimeout(() => target.scrollIntoView({ behavior: 'smooth', block: 'start' }), 0);
}
</script>

<template>
    <div class="wcm26-menu" role="dialog" aria-modal="true" :aria-label="$t('Site menu')">
        <div class="wcm26-bar">
            <span class="wcm26-brand" style="margin-right: auto">
                <Logo />
            </span>
            <button
                type="button"
                class="btn btn-secondary btn-flush"
                style="height: 40px; gap: 10px"
                :aria-label="$t('Close menu')"
                @click="$emit('close')"
            >
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
                    <path d="M5 5l14 14M19 5L5 19"></path>
                </svg>
                <span class="wcm26-btn-label">{{ $t('Close') }}</span>
            </button>
        </div>

        <nav class="wcm26-menu-body">
            <ol class="wcm26-menu-list">
                <li v-for="item in items" :key="item.n">
                    <a
                        :href="item.url"
                        class="wcm26-menu-link"
                        :class="{ 'wcm26-menu-link-support': item.support }"
                        @click="item.anchor ? go($event, item) : $emit('close')"
                    >
                        <span>{{ item.n }}</span>{{ $t(item.label) }}
                    </a>
                </li>
                <li></li>
            </ol>
        </nav>
    </div>
</template>
