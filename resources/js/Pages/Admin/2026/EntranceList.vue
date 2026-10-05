<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref } from 'vue';

/*
 * The entrance list: everyone registered, confirmed or not, by last name —
 * the sheet the people at the door tick off. Names can be corrected here
 * (each row saves when a field is left, or at once on a swap), and the list
 * prints a set number of people per page, its type sized to fill the page.
 *
 * Printing goes through the browser's own dialog — "Save as PDF" is in it —
 * so there is no PDF library: a print-only copy of the list is put at the end
 * of <body>, and while it prints everything else is hidden.
 */
const emit = defineEmits(['close']);

const dayLabels = { 1: '7', 2: '8', 3: '9', 4: '10' };
const daysOf = (person) => [...(person.days || [])].map(Number).sort((a, b) => a - b).map((d) => dayLabels[d]).join(' · ');

const people = ref(null); // null while loading

// Which day's door this list is for — each day has its own sheet. "All" is
// everyone registered for any day.
const dayTabs = [
    { value: 0, label: 'All days', long: 'All days' },
    { value: 1, label: 'Wed 7 Oct', long: 'Wednesday, 7 October' },
    { value: 2, label: 'Thu 8 Oct', long: 'Thursday, 8 October' },
    { value: 3, label: 'Fri 9 Oct', long: 'Friday, 9 October' },
];
const day = ref(0);
const dayName = computed(() => dayTabs.find((t) => t.value === day.value).long);
const shown = computed(() => (people.value || [])
    .filter((p) => ! day.value || (p.days || []).map(Number).includes(day.value)));
const countFor = (value) => (people.value || []).filter((p) => ! value || (p.days || []).map(Number).includes(value)).length;
const state = reactive({}); // id → 'saving' | 'saved' | error message
const perPage = ref(25);

onMounted(async () => {
    const { data } = await window.axios.get('/dashboard/registrations/entrance');
    people.value = data.sort(byLastName);
});

// By last name, then first — on screen and on paper, the same order.
const byLastName = (a, b) => a.last_name.localeCompare(b.last_name, 'ro', { sensitivity: 'base' })
    || a.first_name.localeCompare(b.first_name, 'ro', { sensitivity: 'base' });

/*
 * A corrected or swapped name moves the row to its place in the order — once
 * it is saved, never while it is typed. Moving a row takes the focus with it,
 * so a cursor that was in it is put back; a swapped row is scrolled to, since
 * it may land far from where it was.
 */
const moved = ref(null); // the row just put in its new place, briefly marked

async function resort(person, follow) {
    const focused = document.activeElement;

    people.value.sort(byLastName);
    await nextTick();

    if (focused && document.contains(focused) && document.activeElement !== focused) {
        focused.focus({ preventScroll: true });
    }

    moved.value = person.id;
    setTimeout(() => moved.value === person.id && (moved.value = null), 1600);

    if (follow) {
        document.getElementById(`entrance-row-${person.id}`)?.scrollIntoView({ block: 'center', behavior: 'smooth' });
    }
}

async function save(person, follow = false) {
    if (! person.first_name.trim()) {
        state[person.id] = 'A first name is needed.';
        return;
    }

    state[person.id] = 'saving';

    try {
        await window.axios.put(`/dashboard/registrations/${person.id}/name`, {
            first_name: person.first_name,
            last_name: person.last_name,
        });
        state[person.id] = 'saved';
        delete swapped[person.id];
        resort(person, follow);
    } catch (e) {
        state[person.id] = e.response?.data?.message || 'Not saved.';
    }
}

/*
 * A swap is only on screen until Save is pressed — spot the mix-up, swap,
 * look, then save. Swapping again puts it back, and there is nothing to save.
 */
const swapped = reactive({}); // id → true while a swap waits for Save
const unsaved = computed(() => Object.keys(swapped).length);

function swap(person) {
    [person.first_name, person.last_name] = [person.last_name, person.first_name];

    if (swapped[person.id]) {
        delete swapped[person.id];
    } else {
        swapped[person.id] = true;
    }

    delete state[person.id];
}

const unsavedQuestion = (doing) => `${unsaved.value} swapped ${unsaved.value === 1 ? 'name is' : 'names are'} not saved yet. ${doing} anyway?`;

function close() {
    if (! unsaved.value || window.confirm(unsavedQuestion('Close'))) {
        emit('close');
    }
}

// ── printing ─────────────────────────────────────────────────────────────────

const rows = computed(() => Math.min(60, Math.max(5, Number(perPage.value) || 25)));
/*
 * Blank pages for late arrivals: the same columns and rows, nobody in them, to
 * be filled in by hand at the door — added after the list, or printed alone.
 */
const blankPages = ref(0);
const blankOnly = ref(false);
const blankCount = computed(() => {
    const n = Math.min(20, Math.max(0, Math.floor(Number(blankPages.value) || 0)));

    return blankOnly.value ? Math.max(1, n) : n;
});

const pages = computed(() => {
    const out = [];

    if (! blankOnly.value) {
        for (let i = 0; i < shown.value.length; i += rows.value) {
            out.push({ blank: false, people: shown.value.slice(i, i + rows.value) });
        }
    }

    for (let b = 0; b < blankCount.value; b++) {
        out.push({ blank: true, people: Array.from({ length: rows.value }, (_, i) => ({ id: `blank-${b}-${i}`, last_name: '', first_name: '', organisation: '' })) });
    }

    return out;
});

const printedOn = new Date().toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });

const done = () => document.body.classList.remove('entrance-printing');
onMounted(() => window.addEventListener('afterprint', done));
onBeforeUnmount(() => {
    window.removeEventListener('afterprint', done);
    done();
});

function print() {
    if (unsaved.value && ! blankOnly.value && ! window.confirm(unsavedQuestion('Print'))) {
        return;
    }

    document.body.classList.add('entrance-printing');
    // A tick for the print copy to take the current names before the dialog.
    setTimeout(() => window.print(), 50);
}
</script>

<template>
    <div class="fixed inset-0 z-30 overflow-y-auto bg-white">
        <div class="sticky top-0 z-10 border-b border-gray-200 bg-white">
            <div class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-3 px-6 py-4">
                <h2 class="text-lg font-bold">
                    Entrance list
                    <span v-if="people" class="ml-1 text-sm font-normal text-gray-500">{{ dayName }} · {{ shown.length }} people, by last name</span>
                </h2>
                <div class="flex items-center gap-3 text-sm">
                    <label class="flex items-center gap-2 text-gray-600">
                        Per page
                        <input v-model="perPage" type="number" min="5" max="60" class="w-20 rounded border-gray-300 text-sm" />
                    </label>
                    <!-- Blank pages for late arrivals, after the list or on their own. -->
                    <label class="flex items-center gap-2 text-gray-600" title="Empty pages with the same columns, for people who were not registered">
                        Blank pages
                        <input v-model="blankPages" type="number" min="0" max="20" class="w-16 rounded border-gray-300 text-sm" />
                    </label>
                    <label class="flex items-center gap-1.5 text-gray-600">
                        <input v-model="blankOnly" type="checkbox" class="rounded border-gray-300 text-red-600" />
                        Only blank
                    </label>
                    <button type="button" class="rounded bg-red-600 px-4 py-2 font-semibold text-white hover:bg-red-700" :disabled="!people" @click="print">Print / PDF</button>
                    <button type="button" class="rounded px-3 py-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900" @click="close">Close ✕</button>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-5xl px-6 py-6">
            <p v-if="!people" class="text-sm text-gray-400">Loading…</p>

            <template v-else>
                <!-- The day first: each day's door has its own sheet. -->
                <div class="mb-4 inline-flex overflow-hidden rounded border border-gray-300 text-sm">
                    <button
                        v-for="tab in dayTabs"
                        :key="tab.value"
                        type="button"
                        class="border-r border-gray-300 px-4 py-2 font-semibold last:border-r-0"
                        :class="day === tab.value ? 'bg-gray-900 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'"
                        @click="day = tab.value"
                    >{{ tab.label }} <span class="font-normal opacity-70">{{ countFor(tab.value) }}</span></button>
                </div>

                <p class="mb-4 text-xs text-gray-500">
                    Correct a name in place — it saves when you leave the field, and the row moves to its place by last name. ⇄ swaps last and first name, for anyone who
                    wrote them the other way round — check it, then press the row’s Save. <template v-if="blankOnly">Printing gives {{ blankCount }} blank {{ blankCount === 1 ? 'page' : 'pages' }} for late arrivals, {{ rows }} rows each.</template><template v-else>Printing puts {{ rows }} people on a page, the text sized to fill it<template v-if="blankCount">, plus {{ blankCount }} blank {{ blankCount === 1 ? 'page' : 'pages' }} for late arrivals</template>: {{ pages.length }} {{ pages.length === 1 ? 'page' : 'pages' }} in all.</template>
                </p>

                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-xs uppercase tracking-wide text-gray-500">
                            <th class="w-10 py-2 pr-2 font-medium">#</th>
                            <th class="py-2 pr-2 font-medium">Last name</th>
                            <th class="w-10"></th>
                            <th class="py-2 pr-2 font-medium">First name</th>
                            <th class="py-2 pr-2 font-medium">Organization</th>
                            <th class="py-2 pr-2 font-medium">Days</th>
                            <th class="w-16"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr
                            v-for="(person, i) in shown"
                            :id="`entrance-row-${person.id}`"
                            :key="person.id"
                            class="transition-colors duration-700"
                            :class="moved === person.id ? 'bg-amber-100' : ''"
                        >
                            <td class="py-1.5 pr-2 text-gray-400 tabular-nums">{{ i + 1 }}</td>
                            <td class="py-1.5 pr-1">
                                <input v-model="person.last_name" type="text" :aria-label="`Last name, row ${i + 1}`" class="w-full rounded border-gray-300 py-1 text-sm" @change="save(person)" />
                            </td>
                            <td class="py-1.5 text-center">
                                <button type="button" title="Swap last and first name" class="rounded px-2 py-1 font-mono text-gray-500 hover:bg-gray-100 hover:text-gray-900" @click="swap(person)">⇄</button>
                            </td>
                            <td class="py-1.5 pr-2">
                                <input v-model="person.first_name" type="text" :aria-label="`First name, row ${i + 1}`" class="w-full rounded border-gray-300 py-1 text-sm" @change="save(person)" />
                            </td>
                            <td class="py-1.5 pr-2 text-gray-500">{{ person.organisation }}</td>
                            <td class="py-1.5 pr-2 whitespace-nowrap text-gray-500">{{ daysOf(person) }}</td>
                            <td class="py-1.5 text-xs whitespace-nowrap">
                                <button
                                    v-if="swapped[person.id]"
                                    type="button"
                                    class="rounded bg-red-600 px-2.5 py-1 font-semibold text-white hover:bg-red-700"
                                    @click="save(person, true)"
                                >Save</button>
                                <span v-else-if="state[person.id] === 'saving'" class="text-gray-400">saving…</span>
                                <span v-else-if="state[person.id] === 'saved'" class="text-green-700">✓ saved</span>
                                <span v-else-if="state[person.id]" class="text-red-600">{{ state[person.id] }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="mt-6 flex items-center justify-end gap-3 border-t border-gray-100 pt-4 text-sm">
                    <label class="flex items-center gap-2 text-gray-600">
                        Per page
                        <input v-model="perPage" type="number" min="5" max="60" class="w-20 rounded border-gray-300 text-sm" />
                    </label>
                    <!-- Blank pages for late arrivals, after the list or on their own. -->
                    <label class="flex items-center gap-2 text-gray-600" title="Empty pages with the same columns, for people who were not registered">
                        Blank pages
                        <input v-model="blankPages" type="number" min="0" max="20" class="w-16 rounded border-gray-300 text-sm" />
                    </label>
                    <label class="flex items-center gap-1.5 text-gray-600">
                        <input v-model="blankOnly" type="checkbox" class="rounded border-gray-300 text-red-600" />
                        Only blank
                    </label>
                    <button type="button" class="rounded bg-red-600 px-4 py-2 font-semibold text-white hover:bg-red-700" @click="print">Print / PDF</button>
                </div>
            </template>
        </div>
    </div>

    <!-- What prints: N people a page, the row height and type worked out from N. -->
    <Teleport to="body">
        <div class="entrance-print" :style="{ '--rows': rows }">
            <section v-for="(page, p) in pages" :key="p" class="entrance-page">
                <header class="entrance-head">
                    <strong>Why Culture Matters 2026 — Entrance list · {{ dayName }}<template v-if="page.blank"> · late arrivals</template></strong>
                    <span>{{ printedOn }} · page {{ p + 1 }} of {{ pages.length }}</span>
                </header>
                <table>
                    <thead>
                        <tr>
                            <th class="entrance-last">Last name</th>
                            <th class="entrance-first">First name</th>
                            <th class="entrance-org">Organization</th>
                            <th class="entrance-sign">Signature</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="person in page.people" :key="person.id">
                            <td class="entrance-last"><div><strong>{{ person.last_name }}</strong></div></td>
                            <td class="entrance-first"><div>{{ person.first_name }}</div></td>
                            <td class="entrance-org"><div>{{ person.organisation }}</div></td>
                            <!-- Left empty: signed at the door. -->
                            <td class="entrance-sign"></td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </div>
    </Teleport>
</template>

<style>
/* Never on screen; only ever printed. */
.entrance-print {
    display: none;
}

@media print {
    @page {
        size: A4;
        margin: 10mm;
    }

    /* While the list prints, nothing else on the page does. */
    body.entrance-printing > *:not(.entrance-print) {
        display: none !important;
    }

    body.entrance-printing .entrance-print {
        display: block;
        color: #000;
        font-family: Arial, Helvetica, sans-serif;
        /* The page's height under its header, shared out between the rows. */
        --row: calc(252mm / var(--rows));
    }

    .entrance-page {
        break-after: page;
    }

    .entrance-page:last-child {
        break-after: auto;
    }

    .entrance-head {
        display: flex;
        justify-content: space-between;
        height: 9mm;
        font-size: 10pt;
        border-bottom: 1.5pt solid #000;
    }

    .entrance-print table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .entrance-print th {
        height: 7mm;
        font-size: 8pt;
        text-align: left;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-bottom: 0.75pt solid #000;
    }

    .entrance-print td {
        height: var(--row);
        /* About half the row: 30 a page is ~11pt, 10 a page ~18pt. A long
           name wraps onto a second line rather than being cut. */
        font-size: clamp(8pt, calc(var(--row) * 0.4), 18pt);
        line-height: 1.1;
        border-bottom: 0.5pt solid #999;
        overflow-wrap: anywhere;
        padding: 0 2mm 0 0;
    }

    /* A cell never grows its row: what does not fit in the row's height is
       cut, so every page holds exactly its share and nothing spills over. */
    .entrance-print td > div {
        max-height: calc(var(--row) - 1mm);
        overflow: hidden;
    }

    .entrance-print tr {
        break-inside: avoid;
    }

    .entrance-last {
        width: 27%;
    }

    .entrance-first {
        width: 23%;
    }

    .entrance-org {
        width: 22%;
    }

    .entrance-print td.entrance-org {
        color: #444;
        font-size: clamp(7pt, calc(var(--row) * 0.3), 14pt);
    }

    /* The widest: a signature needs the room. */
    .entrance-sign {
        width: 28%;
    }

    .entrance-print td.entrance-sign {
        border-left: 0.5pt solid #999;
    }
}
</style>
