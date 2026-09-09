<script setup>
import Logo from './Logo.vue';
import { computed } from 'vue';
import { menuItems, menuSupport } from './menu';

const props = defineProps({
    // Hidden sections are not offered: a menu entry that scrolls nowhere is
    // worse than a shorter menu.
    programmeVisible: { type: Boolean, default: false },
    base: { type: String, default: '/2026' },
    /*
     * Where the sections live. Empty on the landing page, where the entries are
     * bare fragments pointing at the page you are already on; the address of
     * the landing page anywhere else, since `#about` on /register scrolls to
     * nothing at all.
     */
    landing: { type: String, default: '' },
    otherLocale: { type: String, default: 'ro' },
    otherLocaleUrl: { type: String, default: '/2026/ro' },
});

const items = computed(() => [
    ...menuItems
        .filter((item) => item.href !== '#programme' || props.programmeVisible)
        .map((item) => ({ ...item, url: props.landing + item.href })),
    // Last, and off the numbered sequence: it leaves the page rather than
    // scrolling it, so it hangs off `base` and is marked as the odd one out.
    { ...menuSupport, url: props.base + menuSupport.href, support: true },
]);

defineEmits(['close']);
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
                        @click="$emit('close')"
                    >
                        <span>{{ item.n }}</span>{{ $t(item.label) }}
                    </a>
                </li>
                <li></li>
            </ol>

            <div class="wcm26-menu-aside">
                <div>
                    <p>{{ $t('Dates') }}</p>
                    <p>{{ $t('7–10 October 2026') }}</p>
                </div>
                <div>
                    <p>{{ $t('Venue') }}</p>
                    <p>FABER, Timișoara</p>
                </div>
                <div>
                    <p>{{ $t('Language') }}</p>
                    <p><a class="wcm26-menu-lang" :href="otherLocaleUrl">{{ otherLocale === 'ro' ? 'Română' : 'English' }}</a></p>
                </div>
                <!-- Support us used to sit here too; it is a row of the menu
                     itself now, and one way in is enough for one overlay. -->
                <div class="wcm26-menu-actions">
                    <a
                        :href="`${base}/register`"
                        class="btn btn-primary btn-flush"
                        style="height: 48px; font-size: 16px"
                        @click="$emit('close')"
                    >
                        {{ $t('register.submit') }}
                    </a>
                </div>
            </div>
        </nav>
    </div>
</template>
