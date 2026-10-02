<script setup>
import { Head } from '@inertiajs/inertia-vue3';
import '../../../css/wcm2026.css';
import PageShell from '../../Sections/2026/PageShell.vue';
import SectionHead from '../../Sections/2026/SectionHead.vue';
import { translateKind } from '../../Sections/2026/kinds';

/*
 * One day of the programme on a page of its own — where the eve-of-day email
 * leads. The day, the brief the office wrote for it, then what is on, and a
 * way back into the full programme at that day.
 */
defineProps({
    base: { type: String, default: '/2026' },
    isPublic: { type: Boolean, default: false },
    day: { type: Object, required: true },
});
</script>

<template>
    <Head :title="`${day.date} · ${$t('Day :n', { n: day.day })} · Why Culture Matters 2026`">
        <meta head-key="robots" name="robots" :content="isPublic ? 'index, follow' : 'noindex, nofollow'" />
    </Head>

    <PageShell :base="base">
        <section class="wcm26-section wcm26-agenda wcm26-daypage">
            <p class="wcm26-label">{{ day.name }} · {{ $t('Day :n', { n: day.day }) }}</p>
            <SectionHead n="→" :title="day.date" />

            <p v-if="day.theme" class="wcm26-daypage-theme">{{ day.theme.numeral }} · {{ day.theme.title }}</p>
            <p v-if="day.moderator" class="wcm26-daypage-moderator">{{ $t('Moderator') }}: <strong>{{ day.moderator.name }}</strong></p>

            <!-- Written in the backoffice and cleaned to a safe subset there. -->
            <div v-if="day.brief" class="wcm26-daypage-brief" v-html="day.brief"></div>

            <ol class="wcm26-daypage-items">
                <li v-for="session in day.sessions" :key="session.id" class="wcm26-daypage-item">
                    <p class="wcm26-agenda-time">{{ session.time }}<template v-if="session.kind"> · {{ translateKind(session.kind) }}</template></p>
                    <div>
                        <p class="wcm26-daypage-title">
                            <a v-if="session.link" :href="session.link" target="_blank" rel="noopener" class="wcm26-session-link">{{ session.title }}<svg class="wcm26-session-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7" /><path d="M8 7h9v9" /></svg></a>
                            <template v-else>{{ session.title }}</template>
                        </p>
                        <p v-if="session.who && session.who.length" class="wcm26-daypage-who">
                            <template v-for="(person, i) in session.who" :key="i"><template v-if="i">, </template>{{ person.name }}<span v-if="person.org" class="wcm26-session-org"> · {{ person.org }}</span></template>
                        </p>
                        <a v-if="session.booking" :href="session.booking.url" class="wcm26-daypage-more">{{ $t('Details & subscribe') }} →</a>
                    </div>
                </li>
            </ol>

            <a :href="day.programmeUrl" class="wcm26-daypage-back">{{ $t('See this day in the full programme') }} →</a>
        </section>
    </PageShell>
</template>
