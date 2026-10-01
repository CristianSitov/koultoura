<script setup>
import { Head } from '@inertiajs/inertia-vue3';
import '../../../css/wcm2026.css';
import PageShell from '../../Sections/2026/PageShell.vue';
import SectionHead from '../../Sections/2026/SectionHead.vue';
import { translateKind } from '../../Sections/2026/kinds';

/*
 * The internal agenda: the public programme and, woven into it by the clock,
 * what was arranged only for speakers, guests and the team. Reached from a
 * personal link in an email — or, with `preview`, from the backoffice, which
 * shows the office exactly this page.
 *
 * The two kinds of row are told apart at a glance: an official session is a
 * quiet card, an internal event a filled one with its own badge and a place.
 */
defineProps({
    base: { type: String, default: '/2026' },
    name: { type: String, default: '' },
    days: { type: Array, default: () => [] },
    preview: { type: Boolean, default: false },
});
</script>

<template>
    <Head :title="`${$t('Your programme')} · Why Culture Matters 2026`">
        <meta head-key="robots" name="robots" content="noindex, nofollow" />
    </Head>

    <PageShell :base="base">
        <section class="wcm26-section wcm26-agenda">
            <!-- Backoffice only, so it stays in the backoffice's language. -->
            <p v-if="preview" class="wcm26-agenda-preview">Preview — this is the page recipients open from their email.</p>

            <SectionHead n="→" :title="$t('Your programme')" />

            <div class="wcm26-agenda-intro">
                <p v-if="name" class="wcm26-agenda-hello">{{ $t('Hello :name,', { name }) }}</p>
                <p class="wcm26-agenda-lead">{{ $t('agenda.lead') }}</p>
                <p class="wcm26-label wcm26-agenda-legend">
                    <span class="wcm26-agenda-key wcm26-agenda-key-session"></span>{{ $t('Programme') }}
                    <span class="wcm26-agenda-key wcm26-agenda-key-event"></span>{{ $t('Guests & team') }}
                </p>
            </div>

            <p v-if="!days.length" class="wcm26-agenda-empty">{{ $t('agenda.empty') }}</p>

            <div v-for="day in days" :key="day.date" class="wcm26-agenda-day">
                <header class="wcm26-agenda-dayhead">
                    <!-- "Day 2" is a place in the symposium; the day before it
                         and the day after have a weekday and nothing more. -->
                    <p class="wcm26-label">{{ day.name }}<template v-if="day.n"> · {{ $t('Day :n', { n: day.n }) }}</template></p>
                    <p class="wcm26-day-n">{{ day.num }}<span class="wcm26-day-month">{{ day.month }}</span></p>
                    <p v-if="day.theme" class="wcm26-agenda-theme">
                        <span class="wcm26-square"></span>
                        <span>{{ day.theme.numeral }} · {{ day.theme.title }}</span>
                    </p>
                    <p v-if="day.moderator" class="wcm26-day-moderator">{{ $t('Moderator') }}: <strong>{{ day.moderator }}</strong></p>
                </header>

                <ol class="wcm26-agenda-items">
                    <li
                        v-for="item in day.items"
                        :key="item.id"
                        class="wcm26-agenda-item"
                        :class="item.type === 'event' ? 'is-event' : 'is-session'"
                    >
                        <p class="wcm26-agenda-time">{{ item.time }}<span v-if="item.end"> – {{ item.end }}</span></p>

                        <div class="wcm26-agenda-card">
                            <template v-if="item.type === 'event'">
                                <p class="wcm26-agenda-badge">{{ $t('Guests & team') }}</p>
                                <h3 class="wcm26-agenda-title">{{ item.title }}</h3>
                                <p v-if="item.location" class="wcm26-agenda-where">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s7-6.1 7-11.5A7 7 0 0 0 5 9.5C5 14.9 12 21 12 21Z" /><circle cx="12" cy="9.5" r="2.5" /></svg>
                                    <span>{{ item.location }}</span>
                                </p>
                                <p v-if="item.description" class="wcm26-agenda-desc">{{ item.description }}</p>
                            </template>

                            <template v-else>
                                <p v-if="item.kind || item.school" class="wcm26-agenda-kind">
                                    <template v-if="item.kind">{{ translateKind(item.kind) }}</template><template v-if="item.kind && item.school"> · </template><template v-if="item.school">{{ $t('Cultural Heritage School') }}</template>
                                </p>
                                <h3 class="wcm26-agenda-title">{{ item.title }}</h3>
                                <p v-if="item.who.length" class="wcm26-agenda-who">
                                    <template v-for="(person, i) in item.who" :key="i"><template v-if="i">, </template>{{ person.name }}<span v-if="person.org" class="wcm26-agenda-org"> · {{ person.org }}</span></template>
                                </p>
                                <p v-if="item.audience" class="wcm26-agenda-who">{{ item.audience }}</p>
                            </template>
                        </div>
                    </li>
                </ol>
            </div>
        </section>
    </PageShell>
</template>
