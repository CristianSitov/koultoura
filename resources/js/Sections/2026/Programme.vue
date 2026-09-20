<script setup>
import { computed, ref } from 'vue';
import { trans } from 'laravel-vue-i18n';
import SectionHead from './SectionHead.vue';
import { translateKind } from './kinds';
import SessionModal from './SessionModal.vue';
import ImageSlot from './ImageSlot.vue';

/*
 * Four days, three themes, and the Heritage School as an umbrella over three of
 * them. The spans row above the grid says it at a glance; inside the columns
 * each Heritage School session carries its own tag, so the umbrella reads both
 * ways. A filled square is a theme, a hollow one is the School.
 *
 * Sessions and days marked `draft` only ever reach here for a signed-in reader
 * — the controller does not send them to anybody else — so the yellow band is
 * a note to the office, not something a visitor can stumble into.
 *
 * All of it comes from the database now, already in the right language — so
 * only the fixed furniture goes through $t().
 */
const props = defineProps({
    days: { type: Array, default: () => [] },
    themeBars: { type: Array, default: () => [] },
    schoolDays: { type: Array, default: () => [] },
    base: { type: String, default: '/2026' },
});

// A workshop or a tour opens a panel; a plain slot does not.
const openSession = ref(null);

// "7, 8 and 10" — the last separator is a word, and not the same word in both
// languages, so it is joined here rather than on the server.
/*
 * Two title states get their own look, from the text itself — no flag, so the
 * moment a real title is typed over "TBA" the styling goes with it, and nothing
 * in the database has to change.
 */
const COFFEE_BREAKS = ['Coffee Break', 'Pauză de cafea'];
const LUNCH_BREAKS = ['Lunch Break', 'Pauză de prânz'];

// A slot still to be announced, however it was typed: "TBA", "În curând",
// "In curand", a stray full stop. Diacritics and trailing punctuation are
// stripped before matching, so every spelling is one placeholder.
const PLACEHOLDERS = new Set(['tba', 'in curand']);
const bare = (s) =>
    (s || '')
        .trim()
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[.\s]+$/, '');

function titleKind(title) {
    const t = (title || '').trim();
    if (PLACEHOLDERS.has(bare(t))) return 'tba';
    if (COFFEE_BREAKS.includes(t)) return 'coffee';
    if (LUNCH_BREAKS.includes(t)) return 'lunch';
    return null;
}

/*
 * The tint under a session's body — title, who, note. One axis is whether it is
 * still to be announced, the other whether it belongs to the Heritage School;
 * their four combinations read white / grey / pink / greyed-pink. A draft wins
 * over all of them, in amber, because it is a note to the office first.
 */
function bodyTint(session) {
    if (session.draft) {
        return 'tint-draft';
    }

    const tba = titleKind(session.title) === 'tba';

    if (session.school && tba) return 'tint-school-tba';
    if (session.school) return 'tint-school';
    if (tba) return 'tint-tba';

    return '';
}

const schoolDayList = computed(() => {
    const days = [...props.schoolDays];

    if (days.length < 2) {
        return days.join('');
    }

    const last = days.pop();

    return `${days.join(', ')} ${trans('and')} ${last}`;
});

/*
 * The umbrella is drawn as one bar per unbroken run of School days, because
 * they are no longer unbroken: the 9th sits between the 8th and the 10th, and a
 * single bar stretched across all three would claim a day the School does not
 * run on.
 *
 * The widest run carries the wording; the others are marked quiet and show the
 * open square alone, so the sentence is not repeated across the row. Below the
 * four-column layout only the wording survives — see the stylesheet, where the
 * bars stack and lining them up with anything stops being possible.
 */
const schoolSpans = computed(() => {
    const runs = [];

    props.days.forEach((day, i) => {
        if (! props.schoolDays.includes(Number(day.num))) {
            return;
        }

        const open = runs[runs.length - 1];

        if (open && open.to === i + 1) {
            open.to = i + 2;
        } else {
            runs.push({ from: i + 1, to: i + 2 });
        }
    });

    const widest = runs.reduce((a, b) => (b.to - b.from > a.to - a.from ? b : a), runs[0]);

    return runs.map((run) => ({ ...run, primary: run === widest }));
});

/*
 * Every workshop and guided tour, pulled out of the day columns into one list
 * below the grid — the two kinds that carry a picture, a trainer and a sign-up
 * (a plain slot has no `detail`). Days are already in order; within a day they
 * sort by the clock, and "HH:MM" sorts as text.
 */
const workshops = computed(() => {
    const out = [];

    props.days.forEach((day, di) => {
        day.sessions
            .filter((s) => s.detail)
            .forEach((s) => out.push({ ...s, di, dayName: day.name, date: `${day.num} ${day.month}` }));
    });

    return out.sort((a, b) => a.di - b.di || a.time.localeCompare(b.time));
});
</script>

<template>
    <section id="programme" class="wcm26-section">
        <div class="wcm26-head-split">
            <SectionHead n="05" :title="$t('Programme')" />
            <p class="wcm26-label wcm26-label-13">{{ $t('Soon') }}</p>
        </div>

        <div class="wcm26-spans">
            <p v-for="bar in themeBars" :key="bar.numeral" class="wcm26-span">
                <span class="wcm26-square"></span>{{ bar.numeral }} · {{ bar.title }}
            </p>
            <p
                v-for="span in schoolSpans"
                :key="span.from"
                class="wcm26-span wcm26-span-school"
                :class="{ 'wcm26-span-school-quiet': !span.primary }"
                :style="{ '--span-from': span.from, '--span-to': span.to }"
            >
                <span class="wcm26-square-open"></span>
                <span class="wcm26-span-text">{{ $t('Heritage School — eight pilot workshops, :days October', { days: schoolDayList }) }}</span>
            </p>
        </div>

        <div class="wcm26-days">
            <div v-for="day in days" :key="day.id" class="wcm26-day">
                <div>
                    <p class="wcm26-label">
                        {{ day.name }}
                        <span v-if="day.draft" class="wcm26-draft-flag">{{ $t('Draft') }}</span>
                    </p>
                    <p class="wcm26-day-n">{{ day.num }}<span class="wcm26-day-month">{{ day.month }}</span></p>
                    <p class="wcm26-day-label">{{ $t('Day :n', { n: day.day }) }}</p>
                </div>

                <p v-if="day.theme || day.moderator" class="wcm26-day-theme">
                    <span v-if="day.theme" class="wcm26-square"></span>
                    <span><template v-if="day.theme">{{ day.theme.numeral }} · {{ day.theme.title }}</template><span v-if="day.moderator" class="wcm26-day-moderated"><template v-if="day.theme">, </template>{{ $t('moderated by :name', { name: day.moderator.name }) }}</span></span>
                </p>

                <p v-if="!day.sessions.length" class="wcm26-day-soon">{{ $t('Coming soon') }}</p>

                <ol v-else class="wcm26-sessions">
                    <li
                        v-for="session in day.sessions"
                        :key="session.id"
                        :class="{ 'is-break': ['coffee', 'lunch'].includes(titleKind(session.title)) }"
                    >
                        <!-- A break is not a session to read: it sits on the hour
                             line, where the kind would be, and carries nothing else. -->
                        <p v-if="['coffee', 'lunch'].includes(titleKind(session.title))" class="wcm26-session-time wcm26-session-break-line">
                            <span class="wcm26-break-time">{{ session.time }}</span>
                            <span class="wcm26-break-label"><svg
                                v-if="titleKind(session.title) === 'coffee'"
                                class="wcm26-break-icon"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
                            ><path d="M17 8h1a4 4 0 1 1 0 8h-1" /><path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z" /><line x1="6" y1="2" x2="6" y2="4" /><line x1="10" y1="2" x2="10" y2="4" /><line x1="14" y1="2" x2="14" y2="4" /></svg><svg
                                v-else
                                class="wcm26-break-icon"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
                            ><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2" /><path d="M7 2v20" /><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7" /></svg>{{ session.title }}</span>
                        </p>

                        <template v-else>
                            <!-- A workshop or a tour is the whole card a button: it
                                 opens the panel with the picture, the trainer and
                                 the sign-up. A plain slot is just read. Every
                                 session reads the same way: hour, labels, then the
                                 body — title, who, note — which alone takes a tint. -->
                            <component
                                :is="session.detail ? 'button' : 'div'"
                                :type="session.detail ? 'button' : null"
                                :class="['wcm26-session-item', { 'wcm26-session-open': session.detail }]"
                                @click="session.detail ? (openSession = session) : null"
                            >
                                <p class="wcm26-session-time">
                                    {{ session.time }}<template v-if="session.kind"> · {{ translateKind(session.kind) }}</template>
                                </p>

                                <div v-if="session.school || session.draft" class="wcm26-session-tags">
                                    <span v-if="session.draft" class="wcm26-draft-flag">{{ $t('Draft') }}</span>
                                    <span v-if="session.school" class="wcm26-session-tag">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 10 12 5 2 10l10 5 10-5Z" /><path d="M6 12v5c0 1 2.7 2.5 6 2.5s6-1.5 6-2.5v-5" /></svg>{{ $t('Cultural Heritage School') }}
                                    </span>
                                </div>

                                <div class="wcm26-session-body" :class="bodyTint(session)">
                                    <p class="wcm26-session-title" :class="{ 'wcm26-session-tba': titleKind(session.title) === 'tba' }">
                                        <template v-if="titleKind(session.title) === 'tba'">{{ $t('TBA') }}</template><template v-else>{{ session.title }}</template>
                                        <span v-if="session.detail" class="wcm26-session-more">{{ $t('Details & subscribe') }} →</span>
                                    </p>
                                    <p v-if="session.who && session.who.length" class="wcm26-session-who">
                                        <template v-for="(person, i) in session.who" :key="i"><template v-if="i">, </template>{{ person.name }}<span v-if="person.org" class="wcm26-session-org"> · {{ person.org }}</span></template>
                                    </p>
                                    <p v-if="session.audience" class="wcm26-session-who wcm26-session-audience">{{ session.audience }}</p>
                                </div>

                                <p v-if="session.booking && session.booking.full" class="wcm26-session-full">{{ $t('Fully booked') }}</p>
                            </component>
                        </template>
                    </li>
                </ol>
            </div>
        </div>

        <p class="wcm26-label wcm26-legend">
            <span class="wcm26-square"></span>{{ $t('Theme of the day') }}
            <span class="wcm26-square-open"></span>{{ $t('Heritage School session') }}
        </p>

        <!-- The workshops and tours again, laid out in full below the grid: the
             picture, who leads it, when, the description and a way in. -->
        <div v-if="workshops.length" class="wcm26-workshops">
            <h3 class="wcm26-workshops-head">{{ $t('Workshops & guided tours') }}</h3>

            <ol class="wcm26-workshops-list">
                <li v-for="w in workshops" :key="w.id" class="wcm26-workshop">
                    <div class="wcm26-workshop-photo">
                        <ImageSlot :src="w.detail.image" :alt="w.title" :placeholder="$t(w.type === 'tour' ? 'Guided tour' : 'Workshop')" />
                    </div>

                    <div class="wcm26-workshop-body">
                        <p class="wcm26-label wcm26-workshop-when">
                            {{ w.date }} · {{ w.time }} · {{ $t(w.type === 'tour' ? 'Guided tour' : 'Workshop') }}<template v-if="w.school"> · {{ $t('Heritage School') }}</template>
                            <span v-if="w.draft" class="wcm26-draft-flag">{{ $t('Draft') }}</span>
                        </p>

                        <h4 class="wcm26-workshop-title">{{ w.title }}</h4>

                        <ul v-if="w.detail.people && w.detail.people.length" class="wcm26-workshop-people">
                            <li v-for="(person, i) in w.detail.people" :key="i" class="wcm26-workshop-person">
                                <img v-if="person.photo" :src="person.photo" alt="" class="wcm26-workshop-avatar" />
                                <span v-else class="wcm26-workshop-avatar wcm26-workshop-avatar-empty"></span>
                                <span>{{ person.name }}</span>
                            </li>
                        </ul>

                        <div class="wcm26-workshop-foot">
                            <button type="button" class="wcm26-workshop-link" @click="openSession = w">{{ $t('Details & subscribe') }} →</button>
                            <span v-if="w.detail.full" class="wcm26-workshop-full">{{ $t('Fully booked') }}</span>
                        </div>
                    </div>
                </li>
            </ol>
        </div>

        <SessionModal v-if="openSession" :session="openSession" :base="base" @close="openSession = null" />
    </section>
</template>
