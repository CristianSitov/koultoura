<script setup>
import { computed } from 'vue';
import { trans } from 'laravel-vue-i18n';
import SectionHead from './SectionHead.vue';

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
});

// "7, 9 and 10" — the last separator is a word, and not the same word in both
// languages, so it is joined here rather than on the server.
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
 * they are no longer unbroken: the 8th sits between the 7th and the 9th, and a
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
</script>

<template>
    <section id="programme" class="wcm26-section">
        <div class="wcm26-head-split">
            <SectionHead n="05" :title="$t('Programme')" />
            <p class="wcm26-label wcm26-label-13">{{ $t('Draft · subject to change') }}</p>
        </div>

        <p class="wcm26-label wcm26-spans-label">{{ $t('Three themes, one umbrella') }}</p>

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

                <p v-if="day.theme" class="wcm26-day-theme">
                    <span class="wcm26-square"></span>{{ day.theme.numeral }} · {{ day.theme.title }}
                </p>

                <p v-if="!day.sessions.length" class="wcm26-day-soon">{{ $t('Coming soon') }}</p>

                <ol v-else class="wcm26-sessions">
                    <li v-for="session in day.sessions" :key="session.id" :class="{ 'is-draft': session.draft }">
                        <span v-if="session.draft" class="wcm26-draft-flag">{{ $t('Draft') }}</span>
                        <span v-if="session.school" class="tag tag-accent wcm26-session-tag">
                            <span class="wcm26-square-open"></span>{{ $t('Heritage School') }}
                        </span>
                        <p class="wcm26-session-time">
                            {{ session.time }}<template v-if="session.kind"> · {{ $t(session.kind) }}</template>
                        </p>
                        <p class="wcm26-session-title">{{ session.title }}</p>
                        <p class="wcm26-session-who">{{ session.who }}</p>

                        <!-- Places are limited on this one, so it has a form of
                             its own rather than being covered by the day. -->
                        <p v-if="session.booking" class="wcm26-session-book">
                            <a v-if="!session.booking.full" :href="session.booking.url" class="btn btn-secondary btn-flush">
                                {{ $t('Book a place') }}
                            </a>
                            <span v-else class="wcm26-hint">{{ $t('Fully booked') }}</span>
                        </p>
                    </li>
                </ol>
            </div>
        </div>

        <p class="wcm26-label wcm26-legend">
            <span class="wcm26-square"></span>{{ $t('Theme of the day') }}
            <span class="wcm26-square-open"></span>{{ $t('Heritage School session') }}
        </p>
    </section>
</template>
