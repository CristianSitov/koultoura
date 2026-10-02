<script setup>
import { Link, useForm } from '@inertiajs/inertia-vue3';
import DraftNotice from '../../../Components/DraftNotice.vue';
import RichText from './RichText.vue';
import { useDraft } from '../../../formDraft';
import { backdrop } from '../../../backdrop';
import { reactive, ref } from 'vue';
import Admin2026 from '../../../Layouts/Admin2026.vue';
import { tooLarge, tooLargeMessage } from '../../../imageGuard';

const props = defineProps({
    days: { type: Array, default: () => [] },
    themes: { type: Array, default: () => [] },
    people: { type: Array, default: () => [] },
    // Whether the section is on the public page at all.
    visible: { type: Boolean, default: false },
    publicBase: { type: String, default: '' },
});

/*
 * The email the evening before a day. The panel asks the server who it goes
 * to, then sends one person per request with a pause between: the mail
 * provider takes a couple a second, and a request lives thirty seconds, so the
 * server could not send eighty in one go. Every send is recorded there — a run
 * that stops halfway resumes with whoever is left.
 */
const briefDay = ref(null);
const brief = reactive({ status: null, sending: null, outcome: null, test: null });
const briefBackdrop = backdrop(() => {
    if (! brief.sending) {
        briefDay.value = null;
    }
});
const longDate = (iso) => new Date(`${iso}T12:00:00`).toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short' });
const headcount = (n) => (n === 1 ? '1 person' : `${n} people`);

async function openBrief(day) {
    briefDay.value = day;
    Object.assign(brief, { status: null, sending: null, outcome: null, test: null });
    brief.status = (await window.axios.get(`/dashboard/programme/days/${day.id}/brief`)).data;
}

async function sendTest(locale) {
    brief.test = 'Sending…';
    try {
        const { data } = await window.axios.post(`/dashboard/programme/days/${briefDay.value.id}/brief/test/${locale}`);
        brief.test = data.ok ? `Test sent to ${data.email} (${locale.toUpperCase()}).` : `Not sent: ${data.error}`;
    } catch (e) {
        brief.test = `Not sent: ${e.response?.data?.message || e.message}`;
    }
}

async function sendBrief() {
    const list = brief.status.pending;

    if (brief.sending || ! list.length
        || ! confirm(`Send the email for ${longDate(briefDay.value.date)} to ${headcount(list.length)} now?`)) {
        return;
    }

    const failed = [];
    brief.outcome = null;
    brief.sending = { done: 0, total: list.length };

    for (const person of list) {
        let error = null;

        try {
            const { data } = await window.axios.post(`/dashboard/programme/days/${briefDay.value.id}/brief/${person.id}`);
            error = data.ok ? null : (data.error || 'The mail provider refused it.');
        } catch (e) {
            error = e.response?.data?.message || e.message;
        }

        if (error) {
            failed.push({ ...person, error });
        }

        brief.sending.done += 1;

        if (brief.sending.done < list.length) {
            await new Promise((resolve) => setTimeout(resolve, 600));
        }
    }

    brief.outcome = { sent: list.length - failed.length, failed };
    brief.sending = null;
    brief.status = (await window.axios.get(`/dashboard/programme/days/${briefDay.value.id}/brief`)).data;
}

const editingDay = ref(null);
const editingTheme = ref(null);

const dayForm = useForm({
    date: '', theme_id: null, moderator_id: null, position: 0, published: false, name: '', name_ro: '',
    description: '', description_ro: '',
    new_moderator: { first: '', last: '', image: null },
});
const themeForm = useForm({ numeral: '', position: 0, title: '', title_ro: '', description: '', description_ro: '' });
const toggle = useForm({});
// Typed but not saved survives closing the window — see formDraft.js.
const dayDraft = useDraft(dayForm, 'programme-day');
const themeDraft = useDraft(themeForm, 'programme-theme');
// Clicking beside a window closes it — only a click that started there.
const dayBackdrop = backdrop(() => (editingDay.value = null));
const themeBackdrop = backdrop(() => (editingTheme.value = null));

function openDay(day) {
    editingDay.value = day?.id ?? 'new';
    dayForm.defaults({
        date: day?.date ?? '',
        theme_id: day?.theme_id ?? null,
        moderator_id: day?.moderator_id ?? null,
        position: day?.position ?? props.days.length + 1,
        published: day?.published ?? false,
        name: day?.name ?? '',
        name_ro: day?.name_ro ?? '',
        description: day?.description ?? '',
        description_ro: day?.description_ro ?? '',
    });
    dayForm.reset();
    dayForm.new_moderator = { first: '', last: '', image: null };
    dayDraft.start(day?.id ?? 'new');
}

function pickModeratorImage(event) {
    const file = event.target.files[0];
    if (tooLarge(file)) {
        dayForm.setError('new_moderator.image', tooLargeMessage);
        event.target.value = '';
        return;
    }
    dayForm.clearErrors('new_moderator.image');
    dayForm.new_moderator.image = file ?? null;
}

function saveDay() {
    const done = { forceFormData: true, onSuccess: () => { dayDraft.finish(); editingDay.value = null; } };

    editingDay.value === 'new'
        ? dayForm.post('/dashboard/programme/days', done)
        : dayForm.post(`/dashboard/programme/days/${editingDay.value}`, done);
}

function removeDay(day) {
    if (confirm(`Remove ${day.date}? Its ${day.sessions.length} session(s) go with it.`)) {
        toggle.delete(`/dashboard/programme/days/${day.id}`);
    }
}

function openTheme(theme) {
    editingTheme.value = theme?.id ?? 'new';
    themeForm.defaults({
        numeral: theme?.numeral ?? '',
        position: theme?.position ?? props.themes.length + 1,
        title: theme?.title ?? '',
        title_ro: theme?.title_ro ?? '',
        description: theme?.description ?? '',
        description_ro: theme?.description_ro ?? '',
    });
    themeForm.reset();
    themeDraft.start(theme?.id ?? 'new');
}

function saveTheme() {
    const done = { onSuccess: () => { themeDraft.finish(); editingTheme.value = null; } };

    editingTheme.value === 'new'
        ? themeForm.post('/dashboard/programme/themes', done)
        : themeForm.put(`/dashboard/programme/themes/${editingTheme.value}`, done);
}
</script>

<template>
    <Admin2026 title="Programme" :public-base="publicBase">
        <template #actions>
            <button type="button" class="rounded bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700" @click="openDay(null)">
                Add day
            </button>
        </template>

        <!-- One switch above everything else, because while it is off nothing
             below it is on the site regardless of what is published. -->
        <div
            :class="[
                'mb-6 flex flex-wrap items-center justify-between gap-3 rounded border p-4',
                visible ? 'border-green-200 bg-green-50' : 'border-amber-200 bg-amber-50',
            ]"
        >
            <div>
                <p class="font-semibold">
                    {{ visible ? 'The programme is on the public page.' : 'The programme is hidden from the public page.' }}
                </p>
                <p class="text-sm text-gray-600 mt-0.5">
                    {{ visible
                        ? 'Visitors see the published days and sessions below, and the menu links to them.'
                        : 'Nothing below is on the site — not even published sessions — and the menu has no Programme entry.' }}
                </p>
            </div>

            <Link
                href="/dashboard/programme/visibility"
                method="put"
                as="button"
                type="button"
                :class="[
                    'rounded px-4 py-2 text-sm font-semibold text-white',
                    visible ? 'bg-gray-700 hover:bg-gray-800' : 'bg-green-700 hover:bg-green-800',
                ]"
            >{{ visible ? 'Hide the programme' : 'Show the programme' }}</Link>
        </div>

        <!-- Themes first: a day points at one, so they have to exist before the
             days can be arranged. -->
        <section class="mb-8">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Themes</h2>
                <button type="button" class="text-sm text-gray-500 hover:text-gray-900" @click="openTheme(null)">Add theme</button>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
                <div
                    v-for="theme in themes"
                    :key="theme.id"
                    class="bg-white rounded border border-gray-200 p-4"
                >
                    <p class="text-xs font-bold text-red-600">{{ theme.numeral }}</p>
                    <p class="font-semibold text-sm mt-1">{{ theme.title }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ theme.title_ro || 'no Romanian title' }}</p>
                    <button type="button" class="mt-2 text-xs text-gray-500 hover:text-red-600" @click="openTheme(theme)">Edit</button>
                </div>
            </div>
        </section>

        <section class="space-y-6">
            <article v-for="day in days" :key="day.id" class="bg-white rounded border border-gray-200">
                <header class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4">
                    <div>
                        <p class="font-bold">
                            {{ day.date }}
                            <span class="ml-2 font-normal text-gray-500">{{ day.name }}</span>
                            <span
                                :class="[
                                    'ml-3 rounded px-2 py-0.5 text-xs',
                                    day.published ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800',
                                ]"
                            >{{ day.published ? 'published' : 'draft' }}</span>
                        </p>
                        <p class="text-sm text-gray-500 mt-0.5">{{ day.theme || 'no theme yet' }}</p>
                    </div>

                    <div class="flex items-center gap-3 text-sm">
                        <button type="button" class="text-gray-500 hover:text-gray-900" @click="openBrief(day)">Email the day before</button>
                        <button type="button" class="text-gray-500 hover:text-gray-900" @click="openDay(day)">Edit day</button>
                        <button type="button" class="text-gray-500 hover:text-red-600" @click="removeDay(day)">Remove</button>
                        <Link
                            :href="`/dashboard/programme/sessions/new?day=${day.id}`"
                            class="rounded border border-gray-300 px-3 py-1.5 hover:border-red-400"
                        >Add session</Link>
                    </div>
                </header>

                <table class="min-w-full text-sm">
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="session in day.sessions" :key="session.id" class="hover:bg-gray-50">
                            <td class="px-5 py-3 w-20 font-mono text-gray-600">{{ session.time }}</td>
                            <td class="px-2 py-3 w-32 text-gray-500">{{ session.kind || '—' }}</td>
                            <td class="px-2 py-3">
                                <span class="font-medium">{{ session.title }}</span>
                                <span v-if="session.school" class="ml-2 rounded bg-red-50 px-1.5 py-0.5 text-xs text-red-700">School</span>
                                <span v-if="session.bookable" class="ml-2 rounded bg-blue-50 px-1.5 py-0.5 text-xs text-blue-700">
                                    {{ session.taken }}/{{ session.capacity }} booked
                                </span>
                                <p class="text-xs text-gray-500">{{ session.who }}</p>
                            </td>
                            <td class="px-2 py-3 w-36 text-right whitespace-nowrap">
                                <Link :href="`/dashboard/programme/sessions/${session.id}`" class="mr-3 text-xs text-gray-500 hover:text-red-600">Edit</Link>
                                <!-- One click, because publishing is the thing
                                     done most often and least worth a form. -->
                                <Link
                                    :href="`/dashboard/programme/sessions/${session.id}/published`"
                                    method="put"
                                    as="button"
                                    type="button"
                                    :class="[
                                        'rounded px-2 py-0.5 text-xs',
                                        session.published ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800',
                                    ]"
                                >{{ session.published ? 'published' : 'draft' }}</Link>
                            </td>
                        </tr>
                        <tr v-if="!day.sessions.length">
                            <td colspan="4" class="px-5 py-6 text-center text-gray-500">Nothing scheduled yet.</td>
                        </tr>
                    </tbody>
                </table>
            </article>
        </section>

        <!-- Day editor -->
        <div v-if="editingDay" class="fixed inset-0 bg-black/40 flex items-center justify-center p-4" v-on="dayBackdrop">
            <form class="bg-white rounded w-full max-w-3xl p-6 space-y-4 max-h-[90vh] overflow-y-auto" @submit.prevent="saveDay">
                <h2 class="font-bold text-lg">{{ editingDay === 'new' ? 'Add day' : 'Edit day' }}</h2>
                <DraftNotice :draft="dayDraft" />

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium mb-1">Date</label>
                        <input v-model="dayForm.date" type="date" class="w-full rounded border-gray-300 text-sm" />
                        <p v-if="dayForm.errors.date" class="mt-1 text-sm text-red-600">{{ dayForm.errors.date }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Theme</label>
                        <select v-model="dayForm.theme_id" class="w-full rounded border-gray-300 text-sm">
                            <option :value="null">— none —</option>
                            <option v-for="theme in themes" :key="theme.id" :value="theme.id">{{ theme.numeral }} · {{ theme.title }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Name (EN)</label>
                        <input v-model="dayForm.name" type="text" placeholder="Wednesday" class="w-full rounded border-gray-300 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Name (RO)</label>
                        <input v-model="dayForm.name_ro" type="text" placeholder="Miercuri" class="w-full rounded border-gray-300 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Order</label>
                        <input v-model="dayForm.position" type="number" min="0" class="w-full rounded border-gray-300 text-sm" />
                    </div>
                    <label class="flex items-center gap-2 text-sm mt-6">
                        <input v-model="dayForm.published" type="checkbox" class="rounded border-gray-300 text-red-600" />
                        Published
                    </label>
                </div>

                <div class="border-t border-gray-100 pt-4">
                    <label class="block text-sm font-medium mb-1">Moderator of the day</label>
                    <select v-model="dayForm.moderator_id" class="w-full rounded border-gray-300 text-sm">
                        <option :value="null">— none —</option>
                        <option v-for="person in people" :key="person.id" :value="person.id">
                            {{ person.name }}<template v-if="!person.onGrid"> (not on grid)</template>
                        </option>
                    </select>

                    <!-- Or a moderator who is not a listed speaker: a hidden
                         person, made here, with a photo of their own. -->
                    <div class="mt-3 border-t border-gray-100 pt-3">
                        <p class="text-xs font-medium text-gray-600 mb-1">Or add someone not on the list</p>
                        <div class="grid grid-cols-2 gap-2">
                            <input v-model="dayForm.new_moderator.first" type="text" placeholder="First name" class="rounded border-gray-300 text-sm" />
                            <input v-model="dayForm.new_moderator.last" type="text" placeholder="Last name" class="rounded border-gray-300 text-sm" />
                        </div>
                        <p v-if="dayForm.errors['new_moderator.first'] || dayForm.errors['new_moderator.last']" class="mt-1 text-sm text-red-600">Both names are needed.</p>
                        <div v-if="dayForm.new_moderator.first" class="mt-2 flex items-center gap-3">
                            <label class="text-sm text-gray-600">
                                <span class="cursor-pointer underline">{{ dayForm.new_moderator.image ? 'Change photo' : 'Add a photo' }}</span>
                                <input type="file" accept="image/*" class="sr-only" @change="pickModeratorImage" />
                            </label>
                            <span v-if="dayForm.new_moderator.image" class="text-xs text-gray-500">{{ dayForm.new_moderator.image.name }}</span>
                        </div>
                        <p v-if="dayForm.errors['new_moderator.image']" class="mt-1 text-sm text-red-600">{{ dayForm.errors['new_moderator.image'] }}</p>
                        <p class="mt-1 text-xs text-gray-500">Adding a name here overrides the choice above. They stay off the public speakers grid.</p>
                    </div>
                </div>

                <!-- The day's brief: what to expect, opening the day's own page
                     and the email sent the evening before. -->
                <div class="border-t border-gray-100 pt-4">
                    <p class="text-sm font-medium">About this day</p>
                    <p class="mt-0.5 text-xs text-gray-500">
                        A few paragraphs on what to expect. Shown at the top of the day’s own page and in the email the evening before.
                        <template v-if="editingDay !== 'new'">
                            <a :href="`${publicBase}/programme/${days.find((d) => d.id === editingDay)?.slug}`" target="_blank" class="underline">Day page ↗</a>
                            · Email <a :href="`/dashboard/programme/days/${editingDay}/email/en`" target="_blank" class="underline">EN ↗</a>
                            ·
                            <a :href="`/dashboard/programme/days/${editingDay}/email/ro`" target="_blank" class="underline">RO ↗</a>
                        </template>
                    </p>
                    <div class="mt-3 grid gap-4 lg:grid-cols-2">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">English</label>
                            <RichText v-model="dayForm.description" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Romanian <span class="font-normal normal-case tracking-normal text-gray-400">empty falls back to English</span></label>
                            <RichText v-model="dayForm.description_ro" />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" class="text-sm text-gray-500" @click="editingDay = null">Cancel</button>
                    <button type="submit" :disabled="dayForm.processing" class="rounded bg-red-600 px-4 py-2 text-sm font-semibold text-white">Save</button>
                </div>
            </form>
        </div>

        <!-- The email the evening before a day -->
        <div v-if="briefDay" class="fixed inset-0 bg-black/40 flex items-center justify-center p-4" v-on="briefBackdrop">
            <div class="bg-white rounded w-full max-w-xl p-6 space-y-4 max-h-[90vh] overflow-y-auto">
                <h2 class="font-bold text-lg">Email the day before · {{ longDate(briefDay.date) }}</h2>
                <p class="text-sm text-gray-500">
                    A few lines about the day — “About this day” in Edit day — and a button to the day’s own page,
                    in each person’s language.
                    <a :href="`/dashboard/programme/days/${briefDay.id}/email/en`" target="_blank" class="underline">Preview EN ↗</a> ·
                    <a :href="`/dashboard/programme/days/${briefDay.id}/email/ro`" target="_blank" class="underline">RO ↗</a> ·
                    <a :href="`${publicBase}/programme/${briefDay.slug}`" target="_blank" class="underline">Day page ↗</a>
                </p>

                <p v-if="!brief.status" class="text-sm text-gray-400">Counting who it goes to…</p>

                <!-- The workshop Saturday: nobody registers for it, so it has no list here. -->
                <p v-else-if="!brief.status.covered" class="rounded border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    Nobody registers for this day — it is the workshops’ and tours’ day, and their people are in the bookings.
                    A reminder for each workshop and tour is planned; until then there is nothing to send from here.
                </p>

                <template v-else>
                    <div class="rounded border border-gray-200 px-4 py-3 text-sm">
                        <p>
                            <span class="font-semibold">{{ headcount(brief.status.total) }}</span> registered for this day,
                            confirmed or not — each address once.
                            <span class="text-gray-500">
                                <template v-for="(n, lang, i) in brief.status.languages" :key="lang"><template v-if="i"> · </template>{{ n }} {{ lang.toUpperCase() }}</template>
                            </span>
                        </p>
                        <p class="mt-1" :class="brief.status.sent ? 'text-green-700' : 'text-gray-500'">
                            {{ brief.status.sent ? `Already sent to ${headcount(brief.status.sent)}.` : 'Not sent yet.' }}
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 text-sm">
                        <span class="text-gray-600">Send a test to yourself:</span>
                        <button type="button" class="rounded border border-gray-300 px-3 py-1.5 font-semibold hover:bg-gray-50" @click="sendTest('en')">EN</button>
                        <button type="button" class="rounded border border-gray-300 px-3 py-1.5 font-semibold hover:bg-gray-50" @click="sendTest('ro')">RO</button>
                        <span v-if="brief.test" class="text-gray-500">{{ brief.test }}</span>
                    </div>

                    <button
                        type="button"
                        :disabled="!brief.status.pending.length || !!brief.sending"
                        class="w-full rounded bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-40"
                        @click="sendBrief"
                    >{{ brief.status.pending.length ? `Send to ${headcount(brief.status.pending.length)}${brief.status.sent ? ' not sent yet' : ''}` : 'Everyone has it' }}</button>

                    <div v-if="brief.sending">
                        <p class="text-sm font-medium text-gray-700">Sending {{ Math.min(brief.sending.done + 1, brief.sending.total) }} of {{ brief.sending.total }}… keep this window open.</p>
                        <div class="mt-2 h-1.5 overflow-hidden rounded bg-gray-100">
                            <div class="h-full bg-red-600 transition-all" :style="{ width: `${(brief.sending.done / brief.sending.total) * 100}%` }"></div>
                        </div>
                    </div>

                    <div
                        v-if="brief.outcome"
                        class="rounded border px-4 py-3 text-sm"
                        :class="brief.outcome.failed.length ? 'border-amber-300 bg-amber-50 text-amber-900' : 'border-green-200 bg-green-50 text-green-800'"
                    >
                        <p class="font-semibold">
                            Sent to {{ headcount(brief.outcome.sent) }}<template v-if="brief.outcome.failed.length"> — {{ brief.outcome.failed.length }} could not be sent</template>.
                        </p>
                        <ul v-if="brief.outcome.failed.length" class="mt-1 list-disc space-y-0.5 pl-5 text-xs">
                            <li v-for="f in brief.outcome.failed" :key="f.id">{{ f.name }} &lt;{{ f.email }}&gt; — {{ f.error }}</li>
                        </ul>
                        <p v-if="brief.outcome.failed.length" class="mt-1 text-xs">Press send again to retry just those.</p>
                    </div>
                </template>

                <div class="flex justify-end">
                    <button type="button" class="text-sm text-gray-500 disabled:opacity-40" :disabled="!!brief.sending" @click="briefDay = null">Close</button>
                </div>
            </div>
        </div>

        <!-- Theme editor -->
        <div v-if="editingTheme" class="fixed inset-0 bg-black/40 flex items-center justify-center p-4" v-on="themeBackdrop">
            <form class="bg-white rounded w-full max-w-2xl p-6 space-y-4 max-h-[90vh] overflow-y-auto" @submit.prevent="saveTheme">
                <h2 class="font-bold text-lg">{{ editingTheme === 'new' ? 'Add theme' : 'Edit theme' }}</h2>
                <DraftNotice :draft="themeDraft" />

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium mb-1">Numeral</label>
                        <input v-model="themeForm.numeral" type="text" placeholder="I" class="w-full rounded border-gray-300 text-sm" />
                        <p v-if="themeForm.errors.numeral" class="mt-1 text-sm text-red-600">{{ themeForm.errors.numeral }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Order</label>
                        <input v-model="themeForm.position" type="number" min="0" class="w-full rounded border-gray-300 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Title (EN)</label>
                        <input v-model="themeForm.title" type="text" class="w-full rounded border-gray-300 text-sm" />
                        <p v-if="themeForm.errors.title" class="mt-1 text-sm text-red-600">{{ themeForm.errors.title }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Title (RO)</label>
                        <input v-model="themeForm.title_ro" type="text" class="w-full rounded border-gray-300 text-sm" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Description (EN)</label>
                    <textarea v-model="themeForm.description" rows="4" class="w-full rounded border-gray-300 text-sm"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Description (RO)</label>
                    <textarea v-model="themeForm.description_ro" rows="4" class="w-full rounded border-gray-300 text-sm"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" class="text-sm text-gray-500" @click="editingTheme = null">Cancel</button>
                    <button type="submit" :disabled="themeForm.processing" class="rounded bg-red-600 px-4 py-2 text-sm font-semibold text-white">Save</button>
                </div>
            </form>
        </div>
    </Admin2026>
</template>
