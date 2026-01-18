<script setup>
import { Head } from '@inertiajs/inertia-vue3';
</script>

<template>
    <Head title="Coming Soon" />

    <div class="bg-brand-dark min-h-screen text-white font-sans selection:bg-brand-red selection:text-white overflow-hidden relative">
        
        <!-- 3D Curvy Wave Stack -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none perspective-container bg-black">
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
            <header class="flex justify-between items-start border-b border-white/10 pb-6 mb-12 pointer-events-auto">
                <div>
                    <h1 class="font-peclet text-brand-red text-4xl md:text-5xl tracking-tight leading-none">
                        Why<br>Culture<br>Matters
                    </h1>
                </div>
                <div class="text-right flex flex-col items-end">
                    <span class="block text-zinc-400 text-sm uppercase tracking-widest mb-1">Edition</span>
                    <span class="font-peclet text-2xl md:text-3xl text-white mb-2">V.2026</span>
                    <span class="text-xs text-zinc-500 max-w-[150px] leading-tight">
                        Organized by<br>
                        PrinBanat x Heritage of Timișoara<br>
                        x Moving Fireplaces
                    </span>
                </div>
            </header>

            <!-- Center Content -->
            <main class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-end pointer-events-auto">
                <!-- Large Date Display -->
                <div class="lg:col-span-8">
                     <p class="text-warm-mahogany uppercase tracking-widest text-sm font-bold mb-4">Save the Date</p>
                    <div class="font-peclet text-6xl md:text-8xl lg:text-9xl leading-[1.1] md:leading-[0.8] lg:leading-[0.6] tracking-tighter flex flex-col">
                        <span class="block -mb-0 md:-mb-4 lg:-mb-8 relative z-30 date-line-1">07-10</span>
                        <span class="block -mb-0 md:-mb-4 lg:-mb-8 relative z-20 date-line-2">October</span>
                        <span class="block relative z-10 date-line-3">2026</span>
                        <span class="block text-3xl md:text-5xl text-white mt-8 tracking-normal leading-tight mx-1 opacity-80">Timișoara, RO</span>
                    </div>
                </div>

                <!-- Narrative Text -->
                <div class="lg:col-span-4 lg:mb-4">
                     <div class="max-w-md ml-auto lg:ml-0 border-l-2 border-warm-mahogany pl-6">
                        <p class="text-lg md:text-xl text-zinc-300 font-light leading-relaxed">
                            A convergence of heritage and future horizons. We explore the unseen threads that weave our collective identity, challenging perspectives and celebrating the resilience of culture in a shifting world.
                        </p>
                    </div>
                </div>
            </main>

            <!-- Footer / Links -->
            <footer class="mt-20 pt-8 border-t border-white/10 flex flex-col md:flex-row justify-between items-center gap-6 pointer-events-auto">
                <div class="text-zinc-500 text-sm">
                    &copy; 2026 Why Culture Matters
                </div>
                
                <div class="flex gap-8">
                     <a href="/2024" class="text-zinc-400 hover:text-warm-mahogany transition-colors duration-300 flex items-center gap-2 group font-sans text-sm font-bold uppercase tracking-widest">
                        <span class="w-1.5 h-1.5 rounded-full bg-zinc-600 group-hover:bg-warm-mahogany transition-colors"></span>
                        Archive 2024
                     </a>
                     <a href="/2022" class="text-zinc-400 hover:text-warm-mahogany transition-colors duration-300 flex items-center gap-2 group font-sans text-sm font-bold uppercase tracking-widest">
                        <span class="w-1.5 h-1.5 rounded-full bg-zinc-600 group-hover:bg-warm-mahogany transition-colors"></span>
                        Archive 2022
                     </a>
                </div>
            </footer>
        </div>
    </div>
</template>

<style scoped>
.perspective-container {
    perspective: 1000px;
    background: radial-gradient(circle at center, #110303 0%, #000000 100%);
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
