<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

/*
 * Attendance sheets for the workshops and tours: who holds a place, by last
 * name, with room to sign — one workshop, or all of them in one go, each
 * starting on a page of its own with its title, day and time on every page.
 *
 * Printed the way the entrance list is: the browser's own dialog (Save as PDF
 * is in it), a print-only copy put at the end of <body>, everything else
 * hidden while it prints, and the rows sized from how many go on a page.
 */
const props = defineProps({
    sessions: { type: Array, required: true }, // the ones to print, as the Workshops page has them
});
const emit = defineEmits(['close']);

const perPage = ref(20);
const blankPages = ref(0);

const rows = computed(() => Math.min(40, Math.max(5, Number(perPage.value) || 20)));
const blanks = computed(() => Math.min(10, Math.max(0, Math.floor(Number(blankPages.value) || 0))));

// "Popescu Ion" written as one name: the last word is taken as the surname.
const split = (name) => {
    const words = (name || '').trim().split(/\s+/).filter(Boolean);
    const last = words.length > 1 ? words.pop() : '';
    return [words.join(' '), last];
};

const byLast = (a, b) => a.last.localeCompare(b.last, 'ro', { sensitivity: 'base' })
    || a.first.localeCompare(b.first, 'ro', { sensitivity: 'base' });

/*
 * Each workshop as a sheet: its people (released places left out), and its
 * columns. An internal workshop knows its places by code and address only.
 */
const sheets = computed(() => props.sessions.map((s) => {
    if (s.internal) {
        const people = (s.places || [])
            .filter((p) => p.email)
            .map((p) => ({ id: `p${p.id}`, code: p.code, email: p.email, status: p.confirmed ? 'confirmed' : p.status }))
            .sort((a, b) => String(a.code).localeCompare(String(b.code), undefined, { numeric: true }));

        return { session: s, internal: true, people };
    }

    const people = (s.bookings || [])
        .filter((b) => ! b.cancelled)
        .map((b) => {
            const [first, last] = b.first_name ? [b.first_name, b.last_name || ''] : split(b.name);
            return { ...b, first, last };
        })
        .sort(byLast);

    return { session: s, internal: false, people };
}));

// The printed pages: each workshop's people in pages, then its blank pages.
const pages = computed(() => sheets.value.flatMap((sheet) => {
    const out = [];
    for (let i = 0; i < sheet.people.length; i += rows.value) {
        out.push({ sheet, rows: sheet.people.slice(i, i + rows.value), start: i });
    }
    if (! sheet.people.length) {
        out.push({ sheet, rows: [], start: 0 });
    }
    for (let b = 0; b < blanks.value; b++) {
        out.push({ sheet, rows: [], start: 0, blank: true });
    }
    // Page n of N, counted within the workshop.
    return out.map((page, n) => ({ ...page, n: n + 1, of: out.length }));
}));

const columns = (sheet) => (sheet.internal ? 3 : (sheet.session.youth ? 6 : 5));
const filler = (page) => Math.max(0, rows.value - page.rows.length);
const ageOf = (b) => (b.age ? `${b.age}` : '');

const printedOn = new Date().toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });

const done = () => document.body.classList.remove('attendance-printing');
onMounted(() => window.addEventListener('afterprint', done));
onBeforeUnmount(() => {
    window.removeEventListener('afterprint', done);
    done();
});

function print() {
    document.body.classList.add('attendance-printing');
    setTimeout(() => window.print(), 50);
}
</script>

<template>
    <div class="fixed inset-0 z-30 overflow-y-auto bg-white">
        <div class="sticky top-0 z-10 border-b border-gray-200 bg-white">
            <div class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-3 px-6 py-4">
                <h2 class="text-lg font-bold">
                    Attendance {{ sessions.length === 1 ? 'list' : 'lists' }}
                    <span class="ml-1 text-sm font-normal text-gray-500">
                        {{ sessions.length === 1 ? sessions[0].title : `${sessions.length} workshops & tours` }} · {{ pages.length }} {{ pages.length === 1 ? 'page' : 'pages' }}
                    </span>
                </h2>
                <div class="flex flex-wrap items-center gap-3 text-sm">
                    <label class="flex items-center gap-2 text-gray-600">
                        Per page
                        <input v-model="perPage" type="number" min="5" max="40" class="w-20 rounded border-gray-300 text-sm" />
                    </label>
                    <label class="flex items-center gap-2 text-gray-600" title="Empty pages with the same columns, for walk-ins">
                        Blank pages
                        <input v-model="blankPages" type="number" min="0" max="10" class="w-16 rounded border-gray-300 text-sm" />
                    </label>
                    <button type="button" class="rounded bg-red-600 px-4 py-2 font-semibold text-white hover:bg-red-700" @click="print">Print / PDF</button>
                    <button type="button" class="rounded px-3 py-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900" @click="emit('close')">Close ✕</button>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-5xl space-y-8 px-6 py-6">
            <p class="text-xs text-gray-500">
                Who holds a place, by last name — released places are left out. Each workshop prints on pages of its own, with
                {{ rows }} rows a page<template v-if="blanks"> and {{ blanks }} blank {{ blanks === 1 ? 'page' : 'pages' }} after it for walk-ins</template>.
            </p>

            <section v-for="sheet in sheets" :key="sheet.session.id">
                <div class="mb-2 flex flex-wrap items-baseline justify-between gap-2">
                    <h3 class="font-semibold">
                        {{ sheet.session.title }}
                        <span class="ml-1 text-sm font-normal text-gray-500">{{ sheet.session.day }} · {{ sheet.session.time }} · {{ sheet.people.length }} {{ sheet.internal ? 'invited' : 'attending' }}</span>
                    </h3>
                    <a :href="`/dashboard/sessions/${sheet.session.id}/attendance.csv`" class="ml-auto text-sm text-gray-600 underline hover:text-gray-900">Download CSV</a>
                </div>

                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-xs uppercase tracking-wide text-gray-500">
                            <th class="w-10 py-1.5 font-medium">#</th>
                            <template v-if="sheet.internal">
                                <th class="py-1.5 font-medium">Code</th>
                                <th class="py-1.5 font-medium">Email</th>
                                <th class="py-1.5 font-medium">Status</th>
                            </template>
                            <template v-else>
                                <th class="py-1.5 font-medium">Last name</th>
                                <th class="py-1.5 font-medium">First name</th>
                                <th class="py-1.5 font-medium">Phone</th>
                                <th v-if="sheet.session.youth" class="py-1.5 font-medium">Age · guardian</th>
                                <th class="py-1.5 font-medium">Confirmed</th>
                            </template>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="(p, i) in sheet.people" :key="p.id">
                            <td class="py-1.5 text-gray-400">{{ i + 1 }}</td>
                            <template v-if="sheet.internal">
                                <td class="py-1.5 font-mono">{{ p.code }}</td>
                                <td class="py-1.5">{{ p.email }}</td>
                                <td class="py-1.5 text-gray-500">{{ p.status }}</td>
                            </template>
                            <template v-else>
                                <td class="py-1.5 font-medium">{{ p.last }}</td>
                                <td class="py-1.5">{{ p.first }}</td>
                                <td class="py-1.5 text-gray-600">{{ p.phone }}</td>
                                <td v-if="sheet.session.youth" class="py-1.5 text-gray-600">
                                    {{ ageOf(p) }}<template v-if="p.guardian_name"> · {{ p.guardian_name }}<span v-if="p.guardian_phone"> ({{ p.guardian_phone }})</span></template>
                                </td>
                                <td class="py-1.5">
                                    <span v-if="p.confirmed" class="rounded bg-green-100 px-1.5 py-0.5 text-xs text-green-800">yes</span>
                                    <span v-else class="text-xs text-gray-400">—</span>
                                </td>
                            </template>
                        </tr>
                        <tr v-if="!sheet.people.length">
                            <td colspan="6" class="py-4 text-center text-gray-400">Nobody yet.</td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </div>
    </div>

    <!-- What prints: each workshop on its own pages, rows sized from the number per page. -->
    <Teleport to="body">
        <div class="attendance-print" :style="{ '--rows': rows }">
            <section v-for="(page, p) in pages" :key="p" class="attendance-page">
                <header class="attendance-head">
                    <div>
                        <strong>{{ page.sheet.session.title }}</strong>
                        <span>{{ page.sheet.session.day }} · {{ page.sheet.session.time }}<template v-if="page.sheet.session.trainerNames"> · {{ page.sheet.session.trainerNames }}</template><template v-if="page.blank"> · walk-ins</template></span>
                    </div>
                    <span class="attendance-meta">Why Culture Matters 2026 · attendance<br />{{ printedOn }} · page {{ page.n }} of {{ page.of }}</span>
                </header>
                <table>
                    <thead>
                        <tr v-if="page.sheet.internal">
                            <th class="a-code">Code</th>
                            <th class="a-wide">Email</th>
                            <th class="a-sign">Signature</th>
                        </tr>
                        <tr v-else>
                            <th class="a-n">#</th>
                            <th class="a-last">Last name</th>
                            <th class="a-first">First name</th>
                            <th v-if="page.sheet.session.youth" class="a-guard">Age · guardian</th>
                            <th class="a-phone">Phone</th>
                            <th class="a-sign">Signature</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="!page.blank">
                            <tr v-for="(person, i) in page.rows" :key="person.id">
                                <template v-if="page.sheet.internal">
                                    <td class="a-code"><div>{{ person.code }}</div></td>
                                    <td class="a-wide"><div>{{ person.email }}</div></td>
                                    <td class="a-sign"></td>
                                </template>
                                <template v-else>
                                    <td class="a-n"><div>{{ page.start + i + 1 }}</div></td>
                                    <td class="a-last"><div><strong>{{ person.last }}</strong></div></td>
                                    <td class="a-first"><div>{{ person.first }}</div></td>
                                    <td v-if="page.sheet.session.youth" class="a-guard"><div>{{ ageOf(person) }}<template v-if="person.guardian_name"> · {{ person.guardian_name }}</template></div></td>
                                    <!-- A minor gives no phone of their own: the guardian's stands in. -->
                                    <td class="a-phone"><div>{{ person.phone || person.guardian_phone }}</div></td>
                                    <td class="a-sign"></td>
                                </template>
                            </tr>
                        </template>
                        <!-- The rest of the page ruled, for walk-ins. -->
                        <tr v-for="k in (page.blank ? rows : filler(page))" :key="`blank-${k}`">
                            <td v-for="c in columns(page.sheet)" :key="c" :class="{ 'a-sign': c === columns(page.sheet) }"></td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </div>
    </Teleport>
</template>

<style>
/* Never on screen; only ever printed. */
.attendance-print {
    display: none;
}

@media print {
    @page {
        size: A4;
        margin: 10mm;
    }

    body.attendance-printing > *:not(.attendance-print) {
        display: none !important;
    }

    body.attendance-printing .attendance-print {
        display: block;
        color: #000;
        font-family: Arial, Helvetica, sans-serif;
        /* The page under its two-line header and the column titles, shared out. */
        --row: calc(240mm / var(--rows));
    }

    .attendance-page {
        break-after: page;
    }

    .attendance-page:last-child {
        break-after: auto;
    }

    .attendance-head {
        display: flex;
        justify-content: space-between;
        gap: 6mm;
        height: 18mm;
        padding-bottom: 1mm;
        box-sizing: border-box;
        border-bottom: 1.5pt solid #000;
        font-size: 9pt;
    }

    .attendance-head strong {
        display: block;
        font-size: 12pt;
        line-height: 1.2;
        max-height: 2.4em;
        overflow: hidden;
    }

    .attendance-meta {
        flex: none;
        text-align: right;
        color: #444;
    }

    .attendance-print table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .attendance-print th {
        height: 7mm;
        font-size: 8pt;
        text-align: left;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-bottom: 0.75pt solid #000;
    }

    .attendance-print td {
        height: var(--row);
        font-size: clamp(8pt, calc(var(--row) * 0.38), 16pt);
        line-height: 1.1;
        border-bottom: 0.5pt solid #999;
        padding: 0 2mm 0 0;
        overflow-wrap: anywhere;
    }

    /* A cell never grows its row, so every page holds exactly its share. */
    .attendance-print td > div {
        max-height: calc(var(--row) - 1mm);
        overflow: hidden;
    }

    .attendance-print tr {
        break-inside: avoid;
    }

    .a-n { width: 8mm; color: #555; }
    .a-last { width: 24%; }
    .a-first { width: 20%; }
    .a-guard { width: 20%; }
    .a-phone { width: 16%; }
    .a-code { width: 22mm; }
    .a-wide { width: 45%; }

    .attendance-print td.a-n,
    .attendance-print td.a-phone,
    .attendance-print td.a-guard {
        font-size: clamp(7pt, calc(var(--row) * 0.3), 12pt);
    }

    .attendance-print td.a-sign {
        border-left: 0.5pt solid #999;
    }
}
</style>
