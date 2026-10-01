<script setup>
import { Head } from '@inertiajs/inertia-vue3';
import '../../../css/wcm2026.css';
import PageShell from '../../Sections/2026/PageShell.vue';
import SectionHead from '../../Sections/2026/SectionHead.vue';

/*
 * The agenda for speakers and guests, behind its secret link — or, with
 * `preview`, framed in the backoffice, which shows the office exactly this.
 *
 * A day is a column of boxes by the clock. One of them is the public programme
 * as a whole: its hours, the office's note, and a way through to the day's
 * sessions. The others are what was arranged around it.
 */
defineProps({
    base: { type: String, default: '/2026' },
    days: { type: Array, default: () => [] },
    preview: { type: Boolean, default: false },
});
</script>

<template>
    <Head :title="`${$t('Agenda')} · Why Culture Matters 2026`">
        <meta head-key="robots" name="robots" content="noindex, nofollow" />
    </Head>

    <PageShell :base="base">
        <section class="wcm26-section wcm26-agenda">
            <!-- Backoffice only, so it stays in the backoffice's language. -->
            <p v-if="preview" class="wcm26-agenda-preview">Preview — this is the page behind the link you send.</p>

            <SectionHead n="→" :title="$t('Agenda')" />

            <p class="wcm26-agenda-lead">{{ $t('agenda.lead') }}</p>

            <p v-if="!days.length" class="wcm26-agenda-empty">{{ $t('agenda.empty') }}</p>

            <div v-for="day in days" :key="day.date" class="wcm26-agenda-day">
                <header class="wcm26-agenda-dayhead">
                    <!-- "Day 2" is a place in the symposium; the day before it
                         and the day after have a weekday and nothing more. -->
                    <p class="wcm26-label">{{ day.name }}<template v-if="day.n"> · {{ $t('Day :n', { n: day.n }) }}</template></p>
                    <p class="wcm26-day-n">{{ day.num }}<span class="wcm26-day-month">{{ day.month }}</span></p>
                </header>

                <ol class="wcm26-agenda-items">
                    <li
                        v-for="item in day.items"
                        :key="item.id"
                        class="wcm26-agenda-item"
                        :class="item.type === 'programme' ? 'is-programme' : 'is-event'"
                    >
                        <p class="wcm26-agenda-time">{{ item.time }}<span v-if="item.end"> – {{ item.end }}</span></p>

                        <!-- The programme: one box for the whole of it, and the
                             box is the way in — a new tab, so the agenda stays. -->
                        <a
                            v-if="item.type === 'programme'"
                            :href="item.url"
                            target="_blank"
                            rel="noopener"
                            class="wcm26-agenda-card wcm26-agenda-programme"
                        >
                            <p class="wcm26-agenda-kind">{{ $t('Official programme') }}</p>
                            <h3 class="wcm26-agenda-title">
                                {{ item.time }} – {{ item.end }}
                            </h3>
                            <p v-if="item.theme" class="wcm26-agenda-theme">{{ item.theme.numeral }} · {{ item.theme.title }}</p>
                            <p v-if="item.moderator" class="wcm26-agenda-moderator">{{ $t('Moderator') }}: <strong>{{ item.moderator }}</strong></p>
                            <p v-if="item.note" class="wcm26-agenda-desc">{{ item.note }}</p>
                            <p class="wcm26-agenda-more">{{ $t('See the day’s programme') }} ↗</p>
                        </a>

                        <div v-else class="wcm26-agenda-card">
                            <h3 class="wcm26-agenda-title">{{ item.title }}</h3>
                            <p v-if="item.location" class="wcm26-agenda-where">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s7-6.1 7-11.5A7 7 0 0 0 5 9.5C5 14.9 12 21 12 21Z" /><circle cx="12" cy="9.5" r="2.5" /></svg>
                                <span>{{ item.location }}</span>
                            </p>
                            <p v-if="item.description" class="wcm26-agenda-desc">{{ item.description }}</p>
                        </div>
                    </li>
                </ol>
            </div>
        </section>
    </PageShell>
</template>
