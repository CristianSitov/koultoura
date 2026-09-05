<script setup>
import Logo from './Logo.vue';
import { menuItems } from './menu';

defineEmits(['close']);
</script>

<template>
    <div class="wcm26-menu" role="dialog" aria-modal="true" aria-label="Site menu">
        <div class="wcm26-bar">
            <span class="wcm26-brand" style="margin-right: auto">
                <Logo />
            </span>
            <button
                type="button"
                class="btn btn-secondary btn-flush"
                style="height: 40px; gap: 10px"
                aria-label="Close menu"
                @click="$emit('close')"
            >
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
                    <path d="M5 5l14 14M19 5L5 19"></path>
                </svg>
                <span class="wcm26-btn-label">Close</span>
            </button>
        </div>

        <nav class="wcm26-menu-body">
            <ol class="wcm26-menu-list">
                <li v-for="item in menuItems" :key="item.n">
                    <a :href="item.href" class="wcm26-menu-link" @click="$emit('close')">
                        <span>{{ item.n }}</span>{{ item.label }}
                    </a>
                </li>
                <li></li>
            </ol>

            <div class="wcm26-menu-aside">
                <div>
                    <p>Dates</p>
                    <p>7–10 October 2026</p>
                </div>
                <div>
                    <p>Venue</p>
                    <p>FABER, Timișoara</p>
                </div>
                <a
                    href="#register"
                    class="btn btn-primary btn-flush"
                    style="height: 48px; font-size: 16px; align-self: flex-start"
                    @click="$emit('close')"
                >
                    Register your interest
                </a>
            </div>
        </nav>
    </div>
</template>
