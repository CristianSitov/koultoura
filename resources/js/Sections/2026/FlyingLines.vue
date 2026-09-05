<script setup>
/*
 * The flying lines from the 2026 announcement page, carried over unchanged:
 * fifteen curves stacked in 3D space, drifting and flowing behind the hero.
 *
 * Two things differ from the announcement page, both so it can sit under a
 * light-themed page: the black radial ground is gone, and the stroke is the
 * brand red the rest of the page uses. Layer opacity, geometry, timing and
 * the keyframes are the originals (see .wcm26-lines in wcm2026.css).
 */
</script>

<template>
    <div class="wcm26-lines" aria-hidden="true">
        <div class="wave-stack">
            <div
                v-for="i in 15"
                :key="i"
                class="wave-layer"
                :style="{
                    '--initial-z': `${(i - 8) * 60}px`,
                    '--initial-y': `${i * 15}px`,
                    '--delay': `${i * -2}s`,
                    '--opacity': (1 - Math.abs(i - 8) * 0.1) * 0.3,
                }"
            >
                <svg class="w-full h-full" viewBox="0 0 1000 100" preserveAspectRatio="none">
                    <path
                        d="M-100,50 C150,150 350,-50 600,50 S1100,100 1200,50"
                        fill="none"
                        stroke="#bf1a2c"
                        stroke-width="1.5"
                        class="animated-curve"
                    />
                </svg>
            </div>
        </div>
    </div>
</template>
