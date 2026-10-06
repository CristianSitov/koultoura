<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import RichText from './RichText.vue';
import { statusClass, statusLabel } from '../../../deliveryStatus';

/*
 * The reminder email to everyone registered: write it (English, and Romanian
 * for those who registered in Romanian — left empty, they get the English),
 * send yourself a test, send it to everyone, then look up at Resend how each
 * one fared. Sending and looking up both go one person per request, with a
 * pause: Resend takes a couple of requests a second.
 */
const emit = defineEmits(['close']);

const state = ref(null); // from the server: reminder, counts, pending, checkable
const form = reactive({ subject: '', subject_ro: '', body: '', body_ro: '' });
const saved = ref(true);
const errors = ref({});
const note = ref(null); // last test / save message
const run = ref(null); // { what, done, total } while sending or checking
const outcome = ref(null); // { what, ok, failed: [] } once a run ends

const load = (data) => {
    state.value = data;
    Object.assign(form, {
        subject: data.reminder.subject || '',
        subject_ro: data.reminder.subject_ro || '',
        body: data.reminder.body || '',
        body_ro: data.reminder.body_ro || '',
    });
    saved.value = true;
};

onMounted(async () => load((await window.axios.get('/dashboard/reminder')).data));

const touched = () => (saved.value = false);

async function save() {
    errors.value = {};
    try {
        load((await window.axios.put('/dashboard/reminder', form)).data);
        return true;
    } catch (e) {
        errors.value = e.response?.data?.errors || {};
        note.value = 'Not saved — see the fields marked in red.';
        return false;
    }
}

async function test(locale) {
    if (! saved.value && ! await save()) return;
    note.value = 'Sending a test…';
    const { data } = await window.axios.post(`/dashboard/reminder/test/${locale}`);
    note.value = data.ok ? `Test sent to ${data.email} (${locale.toUpperCase()}).` : `Test not sent: ${data.error}`;
}

const pause = () => new Promise((resolve) => setTimeout(resolve, 600));
const people = (n) => (n === 1 ? '1 person' : `${n} people`);

async function sendAll() {
    if (! saved.value && ! await save()) return;
    const list = state.value.pending;

    if (run.value || ! list.length || ! confirm(`Send “${form.subject}” to ${people(list.length)} now?`)) return;

    const failed = [];
    outcome.value = null;
    run.value = { what: 'Sending', done: 0, total: list.length };

    for (const person of list) {
        try {
            const { data } = await window.axios.post(`/dashboard/reminder/send/${person.id}`);
            if (! data.ok) failed.push({ ...person, error: data.error });
        } catch (e) {
            failed.push({ ...person, error: e.response?.data?.message || e.message });
        }
        run.value.done += 1;
        if (run.value.done < list.length) await pause();
    }

    outcome.value = { what: 'Sent to', ok: list.length - failed.length, failed };
    run.value = null;
    load((await window.axios.get('/dashboard/reminder')).data);
}

async function checkAll() {
    const ids = state.value.checkable;
    if (run.value || ! ids.length) return;

    const failed = [];
    outcome.value = null;
    run.value = { what: 'Checking', done: 0, total: ids.length };

    for (const id of ids) {
        try {
            const { data } = await window.axios.post(`/dashboard/reminder/check/${id}`);
            if (! data.ok) failed.push({ id, error: data.error });
        } catch (e) {
            failed.push({ id, error: e.response?.data?.message || e.message });
        }
        run.value.done += 1;
        if (run.value.done < ids.length) await pause();
    }

    outcome.value = { what: 'Checked', ok: ids.length - failed.length, failed };
    run.value = null;
    load((await window.axios.get('/dashboard/reminder')).data);
}

async function startNew() {
    if (! confirm('Start a new reminder? It begins with this text and with nobody sent. The statuses of this one stay as they are.')) return;
    load((await window.axios.post('/dashboard/reminder/new')).data);
    outcome.value = null;
}

function close() {
    if (run.value) return;
    if (! saved.value && ! confirm('The text has changes that are not saved. Close anyway?')) return;
    emit('close');
}

const statusList = computed(() => Object.entries(state.value?.statuses || {}));
</script>

<template>
    <div class="fixed inset-0 z-30 overflow-y-auto bg-white">
        <div class="sticky top-0 z-10 border-b border-gray-200 bg-white">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-6 py-4">
                <h2 class="text-lg font-bold">Reminder email</h2>
                <button type="button" class="rounded px-3 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900 disabled:opacity-40" :disabled="!!run" @click="close">Close ✕</button>
            </div>
        </div>

        <div class="mx-auto max-w-5xl space-y-6 px-6 py-6">
            <p v-if="!state" class="text-sm text-gray-400">Loading…</p>

            <template v-else>
                <!-- 1. Write -->
                <section class="space-y-4">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">1 · Write it</h3>
                    <div class="grid gap-4 lg:grid-cols-2">
                        <div v-for="lang in [{ key: '', label: 'English' }, { key: '_ro', label: 'Romanian' }]" :key="lang.key" class="space-y-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                {{ lang.label }}
                                <span v-if="lang.key" class="ml-1 font-normal normal-case tracking-normal text-gray-400">for those who registered in Romanian — empty, they get the English</span>
                            </p>
                            <div>
                                <label class="mb-1 block text-sm font-medium">Subject</label>
                                <input v-model="form[`subject${lang.key}`]" type="text" class="w-full rounded border-gray-300 text-sm" @input="touched" />
                                <p v-if="errors[`subject${lang.key}`]" class="mt-1 text-sm text-red-600">{{ errors[`subject${lang.key}`][0] }}</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium">Text</label>
                                <RichText v-model="form[`body${lang.key}`]" @update:model-value="touched" />
                                <p v-if="errors[`body${lang.key}`]" class="mt-1 text-sm text-red-600">{{ errors[`body${lang.key}`][0] }}</p>
                            </div>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500">
                        <strong>{name}</strong> is replaced by each person’s name, and <strong>{days}</strong> by the days they registered for —
                        on a line of its own, as a list (one day a line); inside a sentence or the subject, as “Thursday 8 and Friday 9 October”. A test shows all three days. To link words, select
                        them and press the link button. The email ends with “See you soon, Asociația Prin Banat”.
                    </p>
                    <div class="flex items-center gap-3">
                        <button type="button" class="rounded bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700 disabled:opacity-40" :disabled="saved" @click="save">
                            {{ saved ? 'Saved' : 'Save' }}
                        </button>
                        <span v-if="!saved" class="text-xs text-amber-700">Changes not saved yet.</span>
                    </div>
                </section>

                <!-- 2. Test -->
                <section class="space-y-2 border-t border-gray-100 pt-5">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">2 · Test it</h3>
                    <div class="flex flex-wrap items-center gap-2 text-sm">
                        <span class="text-gray-600">Send a test to yourself:</span>
                        <button type="button" class="rounded border border-gray-300 px-3 py-1.5 font-semibold hover:bg-gray-50" @click="test('en')">EN</button>
                        <button type="button" class="rounded border border-gray-300 px-3 py-1.5 font-semibold hover:bg-gray-50" @click="test('ro')">RO</button>
                        <span v-if="note" class="text-gray-500">{{ note }}</span>
                    </div>
                </section>

                <!-- 3. Send -->
                <section class="space-y-3 border-t border-gray-100 pt-5">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">3 · Send it to everyone</h3>
                    <p class="text-sm">
                        <strong>{{ people(state.total) }}</strong> registered (confirmed or not, removed ones left out) ·
                        <span :class="state.sent ? 'text-green-700' : 'text-gray-500'">{{ state.sent ? `sent to ${people(state.sent)}` : 'not sent yet' }}</span>
                    </p>
                    <button
                        type="button"
                        :disabled="!state.pending.length || !!run"
                        class="w-full rounded bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-40"
                        @click="sendAll"
                    >{{ state.pending.length ? `Send to ${people(state.pending.length)}${state.sent ? ' not sent yet' : ''}` : 'Everyone has it' }}</button>
                </section>

                <!-- 4. Track -->
                <section class="space-y-3 border-t border-gray-100 pt-5">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">4 · Check delivery</h3>
                    <p v-if="!state.sent" class="text-sm text-gray-500">Once it is sent, look up here what Resend says happened to each email.</p>
                    <template v-else>
                        <div class="flex flex-wrap gap-2 text-sm">
                            <span
                                v-for="[status, n] in statusList"
                                :key="status"
                                class="rounded px-2 py-0.5"
                                :class="statusClass(status)"
                            >{{ n }} {{ statusLabel(status) }}</span>
                        </div>
                        <button
                            type="button"
                            :disabled="!state.checkable.length || !!run"
                            class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40"
                            @click="checkAll"
                        >{{ state.checkable.length ? `Check delivery (${state.checkable.length} to look up)` : 'Nothing left to look up' }}</button>
                        <p class="text-xs text-gray-500">Each person’s status also shows in the Reminder column of the registrations list.</p>
                    </template>
                </section>

                <div v-if="run">
                    <p class="text-sm font-medium text-gray-700">{{ run.what }} {{ Math.min(run.done + 1, run.total) }} of {{ run.total }}… keep this window open.</p>
                    <div class="mt-2 h-1.5 overflow-hidden rounded bg-gray-100">
                        <div class="h-full bg-red-600 transition-all" :style="{ width: `${(run.done / run.total) * 100}%` }"></div>
                    </div>
                </div>
                <div
                    v-if="outcome"
                    class="rounded border px-4 py-3 text-sm"
                    :class="outcome.failed.length ? 'border-amber-300 bg-amber-50 text-amber-900' : 'border-green-200 bg-green-50 text-green-800'"
                >
                    <p class="font-semibold">{{ outcome.what }} {{ people(outcome.ok) }}<template v-if="outcome.failed.length"> — {{ outcome.failed.length }} did not go through</template>.</p>
                    <ul v-if="outcome.failed.length" class="mt-1 list-disc pl-5 text-xs">
                        <li v-for="f in outcome.failed" :key="f.id">{{ f.name || `#${f.id}` }}<template v-if="f.email"> &lt;{{ f.email }}&gt;</template> — {{ f.error }}</li>
                    </ul>
                </div>

                <div v-if="state.sent" class="border-t border-gray-100 pt-5">
                    <button type="button" class="text-sm text-gray-600 underline hover:text-gray-900" :disabled="!!run" @click="startNew">Start a new reminder</button>
                    <span class="ml-2 text-xs text-gray-500">— for a later round: same text to start from, nobody sent.</span>
                </div>
            </template>
        </div>
    </div>
</template>
