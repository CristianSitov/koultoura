<script setup>
import SectionHead from './SectionHead.vue';

/*
 * Four days, three themes, and the Heritage School as an umbrella across days
 * 2–4. The spans row above the grid says it at a glance; inside the columns
 * each Heritage School session carries its own tag, so the umbrella reads both
 * ways. A filled square is a theme, a hollow one is the School.
 *
 * All of it comes from the database now, already in the right language — so
 * nothing here goes through $t(), unlike the rest of the page.
 */
defineProps({
    days: { type: Array, default: () => [] },
    themeBars: { type: Array, default: () => [] },
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
            <p class="wcm26-span wcm26-span-school">
                <span class="wcm26-square-open"></span>{{ $t('Heritage School — eight pilot workshops, days 2–4') }}
            </p>
        </div>

        <div class="wcm26-days">
            <div v-for="day in days" :key="day.id" class="wcm26-day">
                <div>
                    <p class="wcm26-label">{{ day.name }}</p>
                    <p class="wcm26-day-n">{{ day.num }}</p>
                    <p class="wcm26-day-label">{{ $t('Day :n', { n: day.day }) }}</p>
                </div>

                <p v-if="day.theme" class="wcm26-day-theme">
                    <span class="wcm26-square"></span>{{ day.theme.numeral }} · {{ day.theme.title }}
                </p>

                <ol class="wcm26-sessions">
                    <li v-for="session in day.sessions" :key="session.id">
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
