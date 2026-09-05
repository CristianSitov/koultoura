<script setup>
import { Head, usePage } from '@inertiajs/inertia-vue3';
import { computed, onMounted, ref } from 'vue';

const locale = computed(() => usePage().props.value.locale);

/*
 * Light unless the visitor has asked for dark — the same rule and the same
 * stored key as the 2026 landing page, so the choice carries between them.
 * The system preference is deliberately ignored: dark is a choice made here.
 *
 * The palette is a straight swap of the ink and the ground; the reds are brand
 * and do not move.
 */
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
    <Head title="Coming Soon">
        <link rel="alternate" hreflang="en" :href="route('home')">
        <link rel="alternate" hreflang="ro" :href="route('home.ro')">
        <link rel="alternate" hreflang="x-default" :href="route('home')">
    </Head>

    <div
        :data-theme="theme"
        class="wcm-splash min-h-screen font-sans selection:bg-brand-red selection:text-white overflow-hidden relative"
    >
        
        <!-- 3D Curvy Wave Stack -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none perspective-container">
            <div class="wave-stack">
                <!-- Multiple layers of curvy waves in 3D space -->
                <div v-for="i in 15" :key="i"
                     class="wave-layer"
                     :style="{ 
                         '--initial-z': `${(i - 8) * 60}px`,
                         '--initial-y': `${i * 15}px`,
                         '--delay': `${i * -2}s`,
                         '--opacity': (1 - (Math.abs(i - 8) * 0.1)) * 0.3
                     }">
                     <svg class="w-full h-full" viewBox="0 0 1000 100" preserveAspectRatio="none">
                        <path 
                            d="M-100,50 C150,150 350,-50 600,50 S1100,100 1200,50"
                            fill="none" 
                            stroke="#ed1c24" 
                            stroke-width="1.5"
                            class="animated-curve"
                        />
                     </svg>
                </div>
            </div>
        </div>

        <!-- Background Typography Effect -->
        <div class="absolute inset-0 flex items-center justify-center opacity-5 pointer-events-none select-none overflow-hidden z-0">
            <span class="font-peclet text-[40vw] leading-none text-warm-mahogany whitespace-nowrap animate-pulse-slow">
                WCM
            </span>
        </div>

        <!-- Main Content Grid -->
        <div class="relative z-20 max-w-7xl mx-auto px-6 py-12 md:py-20 min-h-screen flex flex-col justify-between pointer-events-none">
            
            <!-- Header / Top Bar (Enable pointer events for links/interactions) -->
            <header class="flex justify-between items-start wcm-rule border-b pb-6 mb-12 pointer-events-auto">
                <div>
                    <h1 class="font-peclet text-brand-red text-4xl md:text-5xl tracking-tight leading-none">
                        Why<br>Culture<br>Matters
                    </h1>
                </div>
                <div class="text-right flex flex-col items-end">
                    <div class="flex items-center gap-3 text-xs wcm-dim uppercase tracking-widest mb-3">
                        <a href="/en" :class="['transition-colors', locale === 'en' ? 'wcm-ink font-bold' : 'hover:text-warm-mahogany']">EN</a>
                        <span class="wcm-faint">/</span>
                        <a href="/ro" :class="['transition-colors', locale === 'ro' ? 'wcm-ink font-bold' : 'hover:text-warm-mahogany']">RO</a>
                        <button
                            type="button"
                            class="wcm-theme-toggle transition-colors"
                            :aria-label="isDark ? $t('Switch to light mode') : $t('Switch to dark mode')"
                            :title="isDark ? $t('Switch to light mode') : $t('Switch to dark mode')"
                            @click="toggleTheme"
                        >
                            <svg v-if="isDark" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="4"></circle>
                                <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"></path>
                            </svg>
                            <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"></path>
                            </svg>
                        </button>
                    </div>
                    <span class="block wcm-muted text-sm uppercase tracking-widest mb-1">{{ $t('Edition') }}</span>
                    <span class="font-peclet text-2xl md:text-3xl wcm-ink mb-2">V.2026</span>
                    <span class="text-xs wcm-dim max-w-[150px] leading-tight">
                        {{ $t('Organized by') }}<br>
                        {{ $t('association') }}
                    </span>
                </div>
            </header>

            <!-- Center Content -->
            <main class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-end pointer-events-auto">
                <!-- Large Date Display -->
                <div class="lg:col-span-8">
                     <p class="text-warm-mahogany uppercase tracking-widest text-sm font-bold mb-4">{{ $t('Save the Date') }}</p>
                    <div class="font-peclet text-6xl md:text-8xl lg:text-9xl leading-[1.1] md:leading-[0.6] tracking-tighter flex flex-col">
                        <span class="block -mb-0 md:-mb-4 lg:-mb-8 relative z-30 date-line-1">07-10</span>
                        <span class="block -mb-0 md:-mb-4 lg:-mb-8 relative z-20 date-line-2">{{ $t('October') }}</span>
                        <span class="block relative z-10 date-line-3">2026</span>
                        <span class="block text-3xl md:text-5xl wcm-ink mt-8 tracking-normal leading-tight mx-1 opacity-80">Timișoara, RO</span>
                    </div>
                </div>

                <!-- Narrative Text -->
                <div class="lg:col-span-4 lg:mb-4">
                     <div class="max-w-md ml-auto lg:ml-0 border-l-2 border-warm-mahogany pl-6 space-y-4">
                        <p class="text-lg md:text-xl wcm-body font-light leading-relaxed">
                            {{ $t('What we inherit. What we understand. What we choose to protect.') }}
                        </p>
                        <p class="text-lg md:text-xl wcm-body font-light leading-relaxed">
                            {{ $t('Why Culture Matters 2026 explores heritage under threat, heritage education and the narratives that shape our relationship with the past — and our responsibility for its future.') }}
                        </p>
                    </div>
                </div>
            </main>

            <!-- Footer / Links -->
            <footer class="mt-20 pt-8 wcm-rule border-t flex flex-col md:flex-row justify-between items-center gap-6 pointer-events-auto">
                <div class="wcm-dim text-sm">
                    &copy; 2026 Why Culture Matters
                </div>
                
                <div class="flex gap-8">
                     <a href="/2024" class="wcm-muted hover:text-warm-mahogany transition-colors duration-300 flex items-center gap-2 group font-sans text-sm font-bold uppercase tracking-widest">
                        <span class="wcm-dot w-1.5 h-1.5 rounded-full group-hover:bg-warm-mahogany transition-colors"></span>
                        {{ $t('Archive 2024') }}
                     </a>
                     <a href="/2022" class="wcm-muted hover:text-warm-mahogany transition-colors duration-300 flex items-center gap-2 group font-sans text-sm font-bold uppercase tracking-widest">
                        <span class="wcm-dot w-1.5 h-1.5 rounded-full group-hover:bg-warm-mahogany transition-colors"></span>
                        {{ $t('Archive 2022') }}
                     </a>
                </div>
            </footer>
        </div>
    </div>
</template>

<style scoped>
/*
 * One palette, two settings. Only the ink and the ground swap — the reds are
 * brand colours and stay where they are in both.
 */
.wcm-splash {
    --ground: #010101;
    --ground-in: #110303;
    --ink: #ffffff;
    --body: #d4d4d8;
    --muted: #a1a1aa;
    --dim: #71717a;
    --faint: #3f3f46;
    --rule: rgba(255, 255, 255, 0.1);
    --dot: #52525b;

    background: var(--ground);
    color: var(--ink);
    transition: background 0.3s, color 0.3s;
}

.wcm-splash[data-theme='light'] {
    --ground: #ffffff;
    --ground-in: #fbf8f8;
    --ink: #111111;
    --body: #3f3f46;
    --muted: #52525b;
    --dim: #71717a;
    --faint: #d4d4d8;
    --rule: rgba(0, 0, 0, 0.12);
    --dot: #a1a1aa;
}

.wcm-ink { color: var(--ink); }
.wcm-body { color: var(--body); }
.wcm-muted { color: var(--muted); }
.wcm-dim { color: var(--dim); }
.wcm-faint { color: var(--faint); }
.wcm-rule { border-color: var(--rule); }
.wcm-dot { background: var(--dot); }

.wcm-theme-toggle {
    color: var(--dim);
    display: inline-flex;
    align-items: center;
    padding: 2px;
}

.wcm-theme-toggle:hover {
    color: #9d3e2e;
}

.perspective-container {
    perspective: 1000px;
    background: radial-gradient(circle at center, var(--ground-in) 0%, var(--ground) 100%);
}

.wave-stack {
    position: absolute;
    width: 120%;
    height: 120%;
    left: -10%;
    top: -10%;
    transform-style: preserve-3d;
    transform: rotateX(60deg) rotateZ(-10deg);
}

.wave-layer {
    position: absolute;
    width: 100%;
    height: 200px;
    top: 40%;
    left: 0;
    opacity: var(--opacity);
    backface-visibility: hidden;
    transform-style: preserve-3d;
    /* Combine static initial position with 3D flow animation */
    animation: flow-3d 30s ease-in-out infinite alternate;
    animation-delay: var(--delay);
}

.animated-curve {
    stroke-dasharray: 1000;
    stroke-dashoffset: 1000;
    /* Reduced speed of linear flow */
    animation: curve-flow 20s linear infinite;
    animation-delay: var(--delay);
    filter: drop-shadow(0 0 3px rgba(237, 28, 36, 0.3));
}

.date-line-1 {
    color: #f12c33; /* Slightly brighter red */
    text-shadow: 
        0 0 10px rgba(241, 44, 51, 0.8),
        0 0 20px rgba(241, 44, 51, 0.4);
}

.date-line-2 {
    color: #ed1c24; /* Brand Red */
    text-shadow: 
        0 0 12px rgba(237, 28, 36, 0.8),
        0 0 25px rgba(237, 28, 36, 0.4);
}

.date-line-3 {
    color: #d91a21; /* Slightly deeper red */
    text-shadow: 
        0 0 15px rgba(217, 26, 33, 0.8),
        0 0 30px rgba(217, 26, 33, 0.4);
}

@keyframes flow-3d {
    0% {
        transform: translate3d(0, 0, var(--initial-z)) translateY(var(--initial-y));
    }
    33% {
        transform: translate3d(30px, -20px, calc(var(--initial-z) + 50px)) translateY(var(--initial-y));
    }
    66% {
        transform: translate3d(-30px, 20px, calc(var(--initial-z) - 50px)) translateY(var(--initial-y));
    }
    100% {
        transform: translate3d(0, 0, var(--initial-z)) translateY(var(--initial-y));
    }
}

@keyframes curve-flow {
    0% {
        stroke-dashoffset: 1000;
    }
    100% {
        stroke-dashoffset: -1000;
    }
}

/* Slow pulse animation for the background text */
@keyframes pulse-slow {
  0%, 100% { opacity: 0.05; }
  50% { opacity: 0.1; }
}
.animate-pulse-slow {
  animation: pulse-slow 8s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
