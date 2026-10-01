<script setup>
import { useForm } from '@inertiajs/inertia-vue3';
import { Inertia } from '@inertiajs/inertia';
import { computed, reactive, ref, watch } from 'vue';
import Admin2026 from '../../../Layouts/Admin2026.vue';
import { conflictsFor } from '../../../agendaConflicts';

/*
 * The internal agenda: the events that are not in the public programme, the
 * people the agenda is sent to, and a preview of the page they open.
 *
 * The official sessions are not edited here — they come from Programme, and
 * the agenda page weaves the two together by the clock. The days run one past
 * the symposium at each end, for arrivals and departures.
 *
 * The send list is the speakers who have an address, plus whoever is added by
 * hand. A speaker's address is kept on the speaker; this screen is just the
 * quickest place to type thirty of them in.
 */
const props = defineProps({
    days: { type: Array, default: () => [] },
    speakers: { type: Array, default: () => [] },
    extras: { type: Array, default: () => [] },
    publicBase: { type: String, default: '' },
});

const blank = {
    date: null, starts_at: '', ends_at: '',
    title: '', title_ro: '', location: '', location_ro: '', description: '', description_ro: '',
};

const editing = ref(null); // null, 'new', or the event's id
const eventForm = useForm({ ...blank });
const recipientForm = useForm({ name: '', email: '', locale: 'en' });
const action = useForm({});

/*
 * Overlaps are worked out from what the page already holds, on every render —
 * so an event is flagged whether the clash came from adding it, or from a
 * session being moved onto it in Programme afterwards. They never stop a save.
 */
const clashes = computed(() => Object.fromEntries(
    props.days.flatMap((day) => day.events.map((event) => [
        event.id,
        conflictsFor(day, event.starts_at, event.ends_at, event.id),
    ])),
));
const clashing = computed(() => Object.values(clashes.value).filter((list) => list.length).length);

// The same check, live, against whatever is in the form right now.
const formClashes = computed(() => conflictsFor(
    props.days.find((day) => day.date === eventForm.date),
    eventForm.starts_at,
    eventForm.ends_at,
    editing.value === 'new' ? null : editing.value,
));

const short = (title) => (title.length > 44 ? `${title.slice(0, 42)}…` : title);
const describe = (list) => list.map((clash) => `${clash.time} ${short(clash.title)}`).join(' · ');

/*
 * The previewer is the real page in a frame, so what the office sees is what
 * is sent. It is reloaded after every change to an event — the key changes,
 * and the frame is built again.
 */
const previewLocale = ref('en');
const previewKey = ref(0);
const previewUrl = computed(() => `/dashboard/agenda/preview/${previewLocale.value}`);
const refreshPreview = () => (previewKey.value += 1);

function openEvent(event, date) {
    editing.value = event?.id ?? 'new';
    eventForm.defaults(event
        ? { ...blank, ...event }
        : { ...blank, date: date ?? props.days[0]?.date ?? null });
    eventForm.reset();
    eventForm.clearErrors();
}

function saveEvent() {
    const done = {
        preserveScroll: true,
        onSuccess: () => {
            editing.value = null;
            refreshPreview();
        },
    };

    editing.value === 'new'
        ? eventForm.post('/dashboard/agenda/events', done)
        : eventForm.put(`/dashboard/agenda/events/${editing.value}`, done);
}

function removeEvent(event) {
    if (confirm(`Remove “${event.title}” from the agenda?`)) {
        action.delete(`/dashboard/agenda/events/${event.id}`, { preserveScroll: true, onSuccess: refreshPreview });
    }
}

// ── who it is sent to ─────────────────────────────────────────────────────

// Everyone who can be written to: the speakers with an address, then the rest.
const everyone = computed(() => [
    ...props.speakers.filter((speaker) => speaker.recipient).map((speaker) => speaker.recipient),
    ...props.extras,
]);
// Not had it at the address they have now — never sent, or the address changed.
const pending = computed(() => everyone.value.filter((recipient) => recipient.pending));
/*
 * Speakers on the list first, then the ones still without an address: thirty
 * names in one alphabet scatter the handful that matter through a scrolling
 * box. Each half keeps the order the server gave it, which is by name.
 */
const listed = computed(() => props.speakers.filter((speaker) => speaker.recipient));
const unlisted = computed(() => props.speakers.filter((speaker) => ! speaker.recipient));
const ordered = computed(() => [...listed.value, ...unlisted.value]);

const people = (n) => (n === 1 ? '1 person' : `${n} people`);
const status = (r) => (r.changed
    ? 'Address changed — not sent to this one yet'
    : r.sent_count ? `Sent ${r.sent_count}× · last ${r.last_sent}` : 'Not sent yet');
const statusClass = (r) => (r.changed ? 'font-medium text-amber-700' : r.sent_count ? 'text-green-700' : 'text-gray-400');

/*
 * A speaker's address, typed straight into the list. Each row keeps its own
 * draft, taken from what the server last said — so after a save, or when
 * someone else's row is saved, the fields show what is actually stored.
 */
const editingSpeaker = ref(null);
const drafts = reactive({});
const draftErrors = reactive({});

const takeDrafts = () => props.speakers.forEach((speaker) => {
    if (editingSpeaker.value !== speaker.id) {
        drafts[speaker.id] = { email: speaker.email, locale: speaker.locale };
    }
});
takeDrafts();
watch(() => props.speakers, takeDrafts);

function saveSpeaker(speaker) {
    delete draftErrors[speaker.id];

    Inertia.put(`/dashboard/agenda/speakers/${speaker.id}`, drafts[speaker.id], {
        preserveScroll: true,
        onSuccess: () => (editingSpeaker.value = null),
        onError: (errors) => (draftErrors[speaker.id] = errors.email || errors.locale || 'That could not be saved.'),
    });
}

function cancelSpeaker(speaker) {
    editingSpeaker.value = null;
    delete draftErrors[speaker.id];
    drafts[speaker.id] = { email: speaker.email, locale: speaker.locale };
}

function clearSpeaker(speaker) {
    if (confirm(`Take ${speaker.name}’s address off? They leave the send list, and the link they were sent stops working.`)) {
        drafts[speaker.id].email = '';
        saveSpeaker(speaker);
    }
}

// Guests and team: added by hand, and edited in the same form.
const editingExtra = ref(null);

function editExtra(recipient) {
    editingExtra.value = recipient.id;
    recipientForm.clearErrors();
    recipientForm.name = recipient.name;
    recipientForm.email = recipient.email;
    recipientForm.locale = recipient.locale;
}

function cancelExtra() {
    editingExtra.value = null;
    recipientForm.reset();
    recipientForm.clearErrors();
}

function saveExtra() {
    const done = { preserveScroll: true, onSuccess: cancelExtra };

    editingExtra.value
        ? recipientForm.put(`/dashboard/agenda/recipients/${editingExtra.value}`, done)
        : recipientForm.post('/dashboard/agenda/recipients', done);
}

function removeRecipient(recipient) {
    if (confirm(`Remove ${recipient.name}? The link in their inbox will stop working.`)) {
        action.delete(`/dashboard/agenda/recipients/${recipient.id}`, { preserveScroll: true });
    }
}

/*
 * Sending. One email per request, one after another, with a breath between:
 * the mail provider takes only a couple a second, and a single request for
 * thirty people would not live long enough to finish. It also means one bad
 * address fails alone — the rest still go, and the failures are listed.
 */
const sending = ref(null); // { done, total } while a run is under way
const outcome = ref(null); // { sent, failed: [{ name, email, error }] } once it ends

async function sendTo(list, question) {
    if (sending.value || ! list.length || ! confirm(question)) {
        return;
    }

    const failed = [];
    outcome.value = null;
    sending.value = { done: 0, total: list.length };

    for (const recipient of list) {
        let error = null;

        try {
            const { data } = await window.axios.post(`/dashboard/agenda/recipients/${recipient.id}/send`);

            if (! data.ok) {
                error = data.error || 'The mail provider refused it.';
            }
        } catch (e) {
            error = e.response?.data?.message || e.message;
        }

        if (error) {
            failed.push({ name: recipient.name, email: recipient.email, error });
        }

        sending.value.done += 1;

        if (sending.value.done < list.length) {
            await new Promise((resolve) => setTimeout(resolve, 600));
        }
    }

    outcome.value = { sent: list.length - failed.length, failed };
    sending.value = null;
    Inertia.reload({ preserveScroll: true });
}

// An email leaves the building, so every send asks first.
const sendOne = (recipient) => sendTo([recipient], recipient.pending
    ? `Send the programme to ${recipient.email}?`
    : `Send ${recipient.email} the programme again? It goes as an update — “the programme has changed”.`);

const sendPending = () => sendTo(pending.value,
    `Send the programme to the ${people(pending.value.length)} who have not had it at their current address?`);

const sendEveryone = () => sendTo(everyone.value,
    `Send to all ${people(everyone.value.length)} now? Anyone who already has it gets it as an update — “the programme has changed”.`);

const copied = ref(null);

function copyLink(recipient) {
    navigator.clipboard?.writeText(recipient.url);
    copied.value = recipient.id;
    setTimeout(() => (copied.value = null), 1500);
}
</script>

<template>
    <Admin2026 title="Agenda" :public-base="publicBase">
        <p class="-mt-3 mb-6 max-w-3xl text-sm text-gray-500">
            The programme as speakers, guests and the team get it: the published sessions from Programme, plus the
            events you add here — meetings, meals, round tables, outings. None of this appears on the public site.
        </p>

        <p v-if="clashing" class="mb-6 rounded border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <span class="font-semibold">{{ clashing }} {{ clashing === 1 ? 'event overlaps' : 'events overlap' }} something else on the day.</span>
            Each is marked below. Nothing is blocked — check that the overlap is meant.
        </p>

        <div class="grid gap-6 lg:grid-cols-5">
            <!-- Events, by day -->
            <div class="space-y-4 lg:col-span-3">
                <div v-if="!days.length" class="rounded border border-gray-200 bg-white p-5 text-sm text-gray-500">
                    Add the days in Programme first — the agenda takes its dates from them.
                </div>

                <div v-for="day in days" :key="day.date" class="rounded border border-gray-200 bg-white">
                    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3">
                        <h2 class="text-sm font-semibold">
                            {{ day.label }}
                            <span v-if="day.extra" class="ml-2 rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-gray-500">
                                outside the programme
                            </span>
                        </h2>
                        <button type="button" class="text-sm font-semibold text-red-600 hover:text-red-800" @click="openEvent(null, day.date)">
                            + Add event
                        </button>
                    </div>

                    <p v-if="!day.events.length" class="px-5 py-4 text-sm text-gray-400">
                        {{ day.extra ? 'Nothing yet — for arrivals and departures: a transfer, a dinner, a meeting point.' : 'No internal events on this day.' }}
                    </p>

                    <ul v-else class="divide-y divide-gray-100">
                        <li v-for="event in day.events" :key="event.id" class="flex items-start gap-4 px-5 py-3">
                            <span class="w-24 flex-none pt-0.5 font-mono text-xs text-gray-500">
                                {{ event.starts_at }}<template v-if="event.ends_at">–{{ event.ends_at }}</template>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-medium">{{ event.title }}</span>
                                <span v-if="event.location" class="block text-xs text-gray-500">{{ event.location }}</span>
                                <span v-if="clashes[event.id].length" class="mt-1 block text-xs font-medium text-amber-700">
                                    ⚠ Overlaps {{ describe(clashes[event.id]) }}
                                </span>
                            </span>
                            <span class="flex flex-none gap-3 text-sm">
                                <button type="button" class="text-gray-600 underline hover:text-gray-900" @click="openEvent(event)">Edit</button>
                                <button type="button" class="text-red-600 underline hover:text-red-800" @click="removeEvent(event)">Remove</button>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Who it is sent to -->
            <div class="space-y-4 lg:col-span-2">
                <!-- Sending -->
                <div class="rounded border border-gray-200 bg-white p-5">
                    <h2 class="text-sm font-semibold">Send the programme <span class="font-normal text-gray-400">{{ people(everyone.length) }} on the list</span></h2>
                    <p class="mt-1 text-xs text-gray-500">
                        Each person gets their own link, in their language, and it always shows the latest agenda.
                        After a change, “Send to everyone” tells them the programme has been updated.
                    </p>

                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <button
                            type="button"
                            :disabled="!pending.length || !!sending"
                            class="rounded bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-40"
                            @click="sendPending"
                        >{{ pending.length ? `Send to ${pending.length} not yet sent` : 'Everyone has it' }}</button>
                        <button
                            type="button"
                            :disabled="!everyone.length || !!sending"
                            class="rounded border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40"
                            @click="sendEveryone"
                        >Send to everyone</button>
                    </div>

                    <div v-if="sending" class="mt-4">
                        <p class="text-sm font-medium text-gray-700">Sending {{ Math.min(sending.done + 1, sending.total) }} of {{ sending.total }}… keep this page open.</p>
                        <div class="mt-2 h-1.5 overflow-hidden rounded bg-gray-100">
                            <div class="h-full bg-red-600 transition-all" :style="{ width: `${(sending.done / sending.total) * 100}%` }"></div>
                        </div>
                    </div>

                    <div
                        v-if="outcome"
                        class="mt-4 rounded border px-4 py-3 text-sm"
                        :class="outcome.failed.length ? 'border-amber-300 bg-amber-50 text-amber-900' : 'border-green-200 bg-green-50 text-green-800'"
                    >
                        <p class="font-semibold">
                            Sent to {{ people(outcome.sent) }}<template v-if="outcome.failed.length"> — {{ outcome.failed.length }} could not be sent</template>.
                        </p>
                        <ul v-if="outcome.failed.length" class="mt-1 list-disc space-y-0.5 pl-5 text-xs">
                            <li v-for="failure in outcome.failed" :key="failure.email">{{ failure.name }} &lt;{{ failure.email }}&gt; — {{ failure.error }}</li>
                        </ul>
                    </div>
                </div>

                <!-- Speakers -->
                <div class="rounded border border-gray-200 bg-white">
                    <div class="border-b border-gray-100 px-5 py-3">
                        <h2 class="text-sm font-semibold">Speakers <span class="font-normal text-gray-400">{{ listed.length }} of {{ speakers.length }} have an address</span></h2>
                        <p class="mt-0.5 text-xs text-gray-500">A speaker with an address is on the list. The address is theirs — it is also on their page in Speakers, and never on the site.</p>
                    </div>

                    <ul class="max-h-[34rem] divide-y divide-gray-100 overflow-y-auto">
                        <li v-for="speaker in ordered" :key="speaker.id" class="px-5 py-3">
                            <p
                                v-if="unlisted.length && speaker.id === unlisted[0].id"
                                class="-mx-5 -mt-3 mb-3 bg-gray-50 px-5 py-1.5 text-[11px] font-semibold uppercase tracking-wide text-gray-500"
                            >No address yet · {{ unlisted.length }}</p>
                            <!-- On the list -->
                            <template v-if="speaker.recipient && editingSpeaker !== speaker.id">
                                <div class="flex items-start justify-between gap-3">
                                    <span class="min-w-0">
                                        <span class="block text-sm font-medium">
                                            {{ speaker.name }}
                                            <span class="ml-1 rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-gray-500">{{ speaker.recipient.locale }}</span>
                                        </span>
                                        <span class="block truncate text-xs text-gray-500">{{ speaker.recipient.email }}</span>
                                    </span>
                                    <button
                                        type="button"
                                        :disabled="!!sending"
                                        class="flex-none rounded border px-3 py-1 text-sm font-semibold disabled:opacity-50"
                                        :class="speaker.recipient.pending ? 'border-red-600 text-red-600 hover:bg-red-50' : 'border-gray-300 text-gray-700 hover:bg-gray-50'"
                                        @click="sendOne(speaker.recipient)"
                                    >{{ speaker.recipient.pending ? 'Send programme' : 'Send again' }}</button>
                                </div>
                                <div class="mt-1.5 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs">
                                    <span :class="statusClass(speaker.recipient)">{{ status(speaker.recipient) }}</span>
                                    <a :href="speaker.recipient.url" target="_blank" class="text-gray-500 underline hover:text-gray-900">Open their page ↗</a>
                                    <button type="button" class="text-gray-500 underline hover:text-gray-900" @click="copyLink(speaker.recipient)">
                                        {{ copied === speaker.recipient.id ? 'Copied' : 'Copy link' }}
                                    </button>
                                    <button type="button" class="text-gray-500 underline hover:text-gray-900" @click="editingSpeaker = speaker.id">Change address</button>
                                </div>
                            </template>

                            <!-- No address yet, or changing it -->
                            <template v-else>
                                <p class="text-sm font-medium" :class="speaker.email ? '' : 'text-gray-500'">{{ speaker.name }}</p>
                                <form class="mt-1.5 flex gap-2" @submit.prevent="saveSpeaker(speaker)">
                                    <input v-model="drafts[speaker.id].email" type="email" placeholder="add their email" :aria-label="`Email for ${speaker.name}`" class="min-w-0 flex-1 rounded border-gray-300 py-1.5 text-sm" />
                                    <select v-model="drafts[speaker.id].locale" :aria-label="`Language for ${speaker.name}`" class="w-[4.75rem] flex-none rounded border-gray-300 py-1.5 text-sm">
                                        <option value="en">EN</option>
                                        <option value="ro">RO</option>
                                    </select>
                                    <button type="submit" class="flex-none rounded border border-gray-300 px-3 py-1.5 text-sm font-semibold hover:bg-gray-50">Save</button>
                                </form>
                                <p v-if="draftErrors[speaker.id]" class="mt-1 text-sm text-red-600">{{ draftErrors[speaker.id] }}</p>
                                <p v-if="editingSpeaker === speaker.id" class="mt-1.5 flex gap-4 text-xs">
                                    <button type="button" class="text-gray-500 underline hover:text-gray-900" @click="cancelSpeaker(speaker)">Cancel</button>
                                    <button type="button" class="text-red-600 underline hover:text-red-800" @click="clearSpeaker(speaker)">Remove their address</button>
                                </p>
                            </template>
                        </li>
                    </ul>
                </div>

                <!-- Guests and team -->
                <div class="rounded border border-gray-200 bg-white">
                    <div class="border-b border-gray-100 px-5 py-3">
                        <h2 class="text-sm font-semibold">Guests &amp; team <span class="font-normal text-gray-400">{{ extras.length }}</span></h2>
                        <p class="mt-0.5 text-xs text-gray-500">Anyone else who should get the programme and is not a speaker.</p>

                        <!-- Two by two: the column is narrow, and four controls in a
                             row left the language picker too thin to read. -->
                        <form class="mt-3 grid grid-cols-2 gap-2" @submit.prevent="saveExtra">
                            <input v-model="recipientForm.name" type="text" placeholder="Name" class="min-w-0 rounded border-gray-300 text-sm" />
                            <input v-model="recipientForm.email" type="email" placeholder="Email" class="min-w-0 rounded border-gray-300 text-sm" />
                            <select v-model="recipientForm.locale" aria-label="Language" class="rounded border-gray-300 text-sm">
                                <option value="en">English</option>
                                <option value="ro">Romanian</option>
                            </select>
                            <button type="submit" :disabled="recipientForm.processing" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold hover:bg-gray-50 disabled:opacity-50">
                                {{ editingExtra ? 'Save changes' : 'Add recipient' }}
                            </button>
                        </form>
                        <p v-for="(message, field) in recipientForm.errors" :key="field" class="mt-1 text-sm text-red-600">{{ message }}</p>
                        <button v-if="editingExtra" type="button" class="mt-1.5 text-xs text-gray-500 underline hover:text-gray-900" @click="cancelExtra">Cancel the edit</button>
                    </div>

                    <ul v-if="extras.length" class="divide-y divide-gray-100">
                        <li v-for="recipient in extras" :key="recipient.id" class="px-5 py-3" :class="editingExtra === recipient.id ? 'bg-gray-50' : ''">
                            <div class="flex items-start justify-between gap-3">
                                <span class="min-w-0">
                                    <span class="block text-sm font-medium">
                                        {{ recipient.name }}
                                        <span class="ml-1 rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-gray-500">{{ recipient.locale }}</span>
                                    </span>
                                    <span class="block truncate text-xs text-gray-500">{{ recipient.email }}</span>
                                </span>
                                <button
                                    type="button"
                                    :disabled="!!sending"
                                    class="flex-none rounded border px-3 py-1 text-sm font-semibold disabled:opacity-50"
                                    :class="recipient.pending ? 'border-red-600 text-red-600 hover:bg-red-50' : 'border-gray-300 text-gray-700 hover:bg-gray-50'"
                                    @click="sendOne(recipient)"
                                >{{ recipient.pending ? 'Send programme' : 'Send again' }}</button>
                            </div>
                            <div class="mt-1.5 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs">
                                <span :class="statusClass(recipient)">{{ status(recipient) }}</span>
                                <a :href="recipient.url" target="_blank" class="text-gray-500 underline hover:text-gray-900">Open their page ↗</a>
                                <button type="button" class="text-gray-500 underline hover:text-gray-900" @click="copyLink(recipient)">
                                    {{ copied === recipient.id ? 'Copied' : 'Copy link' }}
                                </button>
                                <button type="button" class="text-gray-500 underline hover:text-gray-900" @click="editExtra(recipient)">Edit</button>
                                <button type="button" class="text-red-600 underline hover:text-red-800" @click="removeRecipient(recipient)">Remove</button>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Previewer: the real page, framed -->
        <div class="mt-6 rounded border border-gray-200 bg-white">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-3">
                <h2 class="text-sm font-semibold">Preview <span class="font-normal text-gray-400">what recipients see</span></h2>
                <div class="flex items-center gap-3 text-sm">
                    <span class="inline-flex overflow-hidden rounded border border-gray-300">
                        <button
                            v-for="locale in ['en', 'ro']"
                            :key="locale"
                            type="button"
                            class="px-3 py-1 font-semibold uppercase"
                            :class="previewLocale === locale ? 'bg-gray-900 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'"
                            @click="previewLocale = locale"
                        >{{ locale }}</button>
                    </span>
                    <button type="button" class="text-gray-600 underline hover:text-gray-900" @click="refreshPreview">Refresh</button>
                    <a :href="previewUrl" target="_blank" class="text-gray-600 underline hover:text-gray-900">Open in a new tab ↗</a>
                </div>
            </div>
            <iframe :key="`${previewLocale}-${previewKey}`" :src="previewUrl" title="Agenda preview" class="block h-[760px] w-full bg-white"></iframe>
        </div>

        <!-- Add / edit an event -->
        <div v-if="editing" class="fixed inset-0 z-10 flex items-center justify-center overflow-y-auto bg-black/40 p-4" @click.self="editing = null">
            <form class="w-full max-w-2xl space-y-4 rounded bg-white p-6" @submit.prevent="saveEvent">
                <h2 class="text-lg font-bold">{{ editing === 'new' ? 'Add event' : 'Edit event' }}</h2>

                <div class="grid gap-4 sm:grid-cols-4">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium">Day</label>
                        <select v-model="eventForm.date" class="w-full rounded border-gray-300 text-sm">
                            <option v-for="day in days" :key="day.date" :value="day.date">
                                {{ day.label }}{{ day.extra ? ' — outside the programme' : '' }}
                            </option>
                        </select>
                        <p v-if="eventForm.errors.date" class="mt-1 text-sm text-red-600">{{ eventForm.errors.date }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Starts</label>
                        <input v-model="eventForm.starts_at" type="time" class="w-full rounded border-gray-300 text-sm" />
                        <p v-if="eventForm.errors.starts_at" class="mt-1 text-sm text-red-600">{{ eventForm.errors.starts_at }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Ends <span class="font-normal text-gray-400">optional</span></label>
                        <input v-model="eventForm.ends_at" type="time" class="w-full rounded border-gray-300 text-sm" />
                        <p v-if="eventForm.errors.ends_at" class="mt-1 text-sm text-red-600">{{ eventForm.errors.ends_at }}</p>
                    </div>
                </div>

                <!-- A heads-up, never a refusal: the Save button below still works. -->
                <div v-if="formClashes.length" class="rounded border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    <p class="font-semibold">⚠ At this time it overlaps:</p>
                    <ul class="mt-1 list-disc space-y-0.5 pl-5">
                        <li v-for="clash in formClashes" :key="`${clash.kind}-${clash.time}-${clash.title}`">
                            {{ clash.time }} — {{ clash.title }}
                            <span class="text-amber-700">({{ clash.kind === 'agenda' ? 'another agenda event' : 'programme' }})</span>
                        </li>
                    </ul>
                    <p class="mt-1.5 text-xs text-amber-800">You can still save it — this is only so an overlap is never a surprise.</p>
                </div>
                <p v-else-if="eventForm.starts_at && !eventForm.ends_at" class="text-xs text-gray-500">
                    With no end time, only the start is checked against the programme. Add an end for a full check.
                </p>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div v-for="lang in [{ key: '', label: 'English' }, { key: '_ro', label: 'Romanian' }]" :key="lang.key" class="space-y-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                            {{ lang.label }}
                            <span v-if="lang.key" class="ml-1 font-normal normal-case tracking-normal text-gray-400">left empty, falls back to English</span>
                        </p>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Title</label>
                            <input v-model="eventForm[`title${lang.key}`]" type="text" :placeholder="lang.key ? 'Cina speakerilor' : 'Speakers’ dinner'" class="w-full rounded border-gray-300 text-sm" />
                            <p v-if="eventForm.errors[`title${lang.key}`]" class="mt-1 text-sm text-red-600">{{ eventForm.errors[`title${lang.key}`] }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Location</label>
                            <input v-model="eventForm[`location${lang.key}`]" type="text" class="w-full rounded border-gray-300 text-sm" />
                            <p v-if="eventForm.errors[`location${lang.key}`]" class="mt-1 text-sm text-red-600">{{ eventForm.errors[`location${lang.key}`] }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Description</label>
                            <textarea v-model="eventForm[`description${lang.key}`]" rows="4" class="w-full rounded border-gray-300 text-sm"></textarea>
                            <p v-if="eventForm.errors[`description${lang.key}`]" class="mt-1 text-sm text-red-600">{{ eventForm.errors[`description${lang.key}`] }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" class="rounded px-4 py-2 text-sm text-gray-600 hover:text-gray-900" @click="editing = null">Cancel</button>
                    <button type="submit" :disabled="eventForm.processing" class="rounded bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50">
                        {{ editing === 'new' ? 'Add event' : 'Save' }}
                    </button>
                </div>
            </form>
        </div>
    </Admin2026>
</template>
