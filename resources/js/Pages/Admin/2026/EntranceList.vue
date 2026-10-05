<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue';

/*
 * The entrance list: everyone registered, confirmed or not, by first name —
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
    // Sorted once, as it opens: re-sorting while a name is being typed would
    // move the row out from under the cursor.
    people.value = data.sort((a, b) => byFirstName(a, b));
});

const byFirstName = (a, b) => a.first_name.localeCompare(b.first_name, 'ro', { sensitivity: 'base' })
    || a.last_name.localeCompare(b.last_name, 'ro', { sensitivity: 'base' });

async function save(person) {
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
    } catch (e) {
        state[person.id] = e.response?.data?.message || 'Not saved.';
    }
}

function swap(person) {
    [person.first_name, person.last_name] = [person.last_name, person.first_name];
    save(person);
}

// ── printing ─────────────────────────────────────────────────────────────────

const rows = computed(() => Math.min(60, Math.max(5, Number(perPage.value) || 25)));
const pages = computed(() => {
    const list = shown.value;
    const out = [];

    for (let i = 0; i < list.length; i += rows.value) {
        out.push(list.slice(i, i + rows.value));
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
                    <span v-if="people" class="ml-1 text-sm font-normal text-gray-500">{{ dayName }} · {{ shown.length }} people, by first name</span>
                </h2>
                <div class="flex items-center gap-3 text-sm">
                    <label class="flex items-center gap-2 text-gray-600">
                        Per page
                        <input v-model="perPage" type="number" min="5" max="60" class="w-20 rounded border-gray-300 text-sm" />
                    </label>
                    <button type="button" class="rounded bg-red-600 px-4 py-2 font-semibold text-white hover:bg-red-700" :disabled="!people" @click="print">Print / PDF</button>
                    <button type="button" class="rounded px-3 py-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900" @click="emit('close')">Close ✕</button>
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
                    Correct a name in place — it saves when you leave the field. ⇄ swaps first and last name, for anyone who
                    wrote their surname first. Printing puts {{ rows }} people on a page ({{ pages.length }} {{ pages.length === 1 ? 'page' : 'pages' }}), the text sized to fill it.
                </p>

                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-xs uppercase tracking-wide text-gray-500">
                            <th class="w-10 py-2 pr-2 font-medium">#</th>
                            <th class="py-2 pr-2 font-medium">First name</th>
                            <th class="w-10"></th>
                            <th class="py-2 pr-2 font-medium">Last name</th>
                            <th class="py-2 pr-2 font-medium">Organisation</th>
                            <th class="py-2 pr-2 font-medium">Days</th>
                            <th class="w-16"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="(person, i) in shown" :key="person.id">
                            <td class="py-1.5 pr-2 text-gray-400 tabular-nums">{{ i + 1 }}</td>
                            <td class="py-1.5 pr-1">
                                <input v-model="person.first_name" type="text" :aria-label="`First name, row ${i + 1}`" class="w-full rounded border-gray-300 py-1 text-sm" @change="save(person)" />
                            </td>
                            <td class="py-1.5 text-center">
                                <button type="button" title="Swap first and last name" class="rounded px-2 py-1 font-mono text-gray-500 hover:bg-gray-100 hover:text-gray-900" @click="swap(person)">⇄</button>
                            </td>
                            <td class="py-1.5 pr-2">
                                <input v-model="person.last_name" type="text" :aria-label="`Last name, row ${i + 1}`" class="w-full rounded border-gray-300 py-1 text-sm" @change="save(person)" />
                            </td>
                            <td class="py-1.5 pr-2 text-gray-500">{{ person.organisation }}</td>
                            <td class="py-1.5 pr-2 whitespace-nowrap text-gray-500">{{ daysOf(person) }}</td>
                            <td class="py-1.5 text-xs whitespace-nowrap">
                                <span v-if="state[person.id] === 'saving'" class="text-gray-400">saving…</span>
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
                    <strong>Why Culture Matters 2026 — Entrance list · {{ dayName }}</strong>
                    <span>{{ printedOn }} · page {{ p + 1 }} of {{ pages.length }}</span>
                </header>
                <table>
                    <thead>
                        <tr>
                            <th class="entrance-n">#</th>
                            <th>First name</th>
                            <th>Last name</th>
                            <th>Organisation</th>
                            <th class="entrance-days">Days</th>
                            <th class="entrance-tick">✓</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(person, i) in page" :key="person.id">
                            <td class="entrance-n">{{ p * rows + i + 1 }}</td>
                            <td><strong>{{ person.first_name }}</strong></td>
                            <td>{{ person.last_name }}</td>
                            <td class="entrance-org">{{ person.organisation }}</td>
                            <td class="entrance-days">{{ daysOf(person) }}</td>
                            <td class="entrance-tick"><span></span></td>
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
        --row: calc(258mm / var(--rows));
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
        overflow: hidden;
        overflow-wrap: anywhere;
        padding: 0 2mm 0 0;
    }

    .entrance-print tr {
        break-inside: avoid;
    }

    .entrance-n {
        width: 11mm;
        color: #555;
    }

    .entrance-print td.entrance-n {
        font-size: clamp(7pt, calc(var(--row) * 0.28), 12pt);
    }

    .entrance-print td.entrance-org {
        color: #444;
        font-size: clamp(7pt, calc(var(--row) * 0.3), 14pt);
    }

    .entrance-days {
        width: 22mm;
    }

    .entrance-print td.entrance-days {
        font-size: clamp(7pt, calc(var(--row) * 0.3), 14pt);
    }

    .entrance-tick {
        width: 12mm;
        text-align: center;
    }

    /* A box to tick at the door. */
    .entrance-tick span {
        display: inline-block;
        width: clamp(3.5mm, calc(var(--row) * 0.5), 8mm);
        height: clamp(3.5mm, calc(var(--row) * 0.5), 8mm);
        border: 0.75pt solid #000;
        vertical-align: middle;
    }
}
</style>
