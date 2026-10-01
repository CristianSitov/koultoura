<script setup>
import { useForm } from '@inertiajs/inertia-vue3';
import { computed, ref } from 'vue';
import Admin2026 from '../../../Layouts/Admin2026.vue';
import DraftNotice from '../../../Components/DraftNotice.vue';
import { useDraft } from '../../../formDraft';
import { place } from '../../../agendaText';

/*
 * The agenda builder. The agenda is one page behind one secret link, which the
 * office sends out from its own mail.
 *
 * A day on it is a few boxes by the clock. The public programme is one of them
 * — its hours follow Programme unless set here, and its note is written here —
 * and the rest are the events added around it. The days run one past the
 * symposium at each end, for arrivals and departures.
 */
const props = defineProps({
    link: { type: Object, default: () => ({ en: '', ro: '' }) },
    days: { type: Array, default: () => [] },
    publicBase: { type: String, default: '' },
});

const blank = {
    date: null, starts_at: '', ends_at: '',
    title: '', title_ro: '', location: '', location_ro: '', description: '', description_ro: '',
};

const editing = ref(null); // null, 'new', or the event's id
const eventForm = useForm({ ...blank });
const boxDay = ref(null); // the day whose programme box is being edited
const boxForm = useForm({ starts_at: '', ends_at: '', note: '', note_ro: '' });
const action = useForm({});
// Typed but not saved survives closing the window — see formDraft.js.
const eventDraft = useDraft(eventForm, 'agenda-event');
const boxDraft = useDraft(boxForm, 'agenda-box');

// A day's boxes in the order the page shows them: the programme among the events.
const boxes = (day) => [
    ...day.events.map((event) => ({ kind: 'event', time: event.starts_at, event })),
    ...(day.programme ? [{ kind: 'programme', time: day.programme.start }] : []),
].sort((a, b) => a.time.localeCompare(b.time));

// The programme's hours on the day the event form points at, for the hint.
const formDay = computed(() => props.days.find((day) => day.date === eventForm.date));

/*
 * The previewer is the real page in a frame, so what the office sees is what
 * the link shows. It is reloaded after every change — the key changes, and the
 * frame is built again.
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
    // A new event's draft belongs to the day it was started on.
    eventDraft.start(event?.id ?? `new-${eventForm.date}`);
}

function saveEvent() {
    const done = {
        preserveScroll: true,
        onSuccess: () => {
            eventDraft.finish();
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

function openBox(day) {
    boxDay.value = day;
    boxForm.defaults({
        starts_at: day.programme.starts_at,
        ends_at: day.programme.ends_at,
        note: day.programme.note,
        note_ro: day.programme.note_ro,
    });
    boxForm.reset();
    boxForm.clearErrors();
    boxDraft.start(day.programme.id);
}

function saveBox() {
    boxForm.put(`/dashboard/agenda/days/${boxDay.value.programme.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            boxDraft.finish();
            boxDay.value = null;
            refreshPreview();
        },
    });
}

function resetLink() {
    if (confirm('Reset the link? The one already sent out stops working at once, for everyone — you will need to send the new one.')) {
        action.post('/dashboard/agenda/link/reset', { preserveScroll: true });
    }
}

const copied = ref(null);

function copyLink(locale) {
    navigator.clipboard?.writeText(props.link[locale]);
    copied.value = locale;
    setTimeout(() => (copied.value = null), 1500);
}
</script>

<template>
    <Admin2026 title="Agenda" :public-base="publicBase">
        <p class="-mt-3 mb-6 max-w-3xl text-sm text-gray-500">
            One page for speakers and guests, behind a secret link you send by email. Each day shows the official
            programme as a single box — with your note on it — and around it the events you add here: transfers, meals,
            meetings. None of this appears on the public site.
        </p>

        <!-- The secret link -->
        <div class="mb-6 rounded border border-gray-200 bg-white p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-sm font-semibold">The secret link</h2>
                    <p class="mt-1 max-w-2xl text-xs text-gray-500">
                        Copy it into your email. Anyone who has it can open the agenda — no login — and it always shows
                        the latest version. The page has its own EN/RO switch; the two links only differ in the language
                        it opens in.
                    </p>
                </div>
                <button type="button" class="text-sm text-red-600 underline hover:text-red-800" @click="resetLink">Reset link</button>
            </div>

            <div v-for="locale in ['en', 'ro']" :key="locale" class="mt-3 flex items-center gap-2">
                <span class="w-8 flex-none text-xs font-semibold uppercase text-gray-500">{{ locale }}</span>
                <input :value="link[locale]" readonly :aria-label="`Agenda link, ${locale}`" class="min-w-0 flex-1 rounded border-gray-300 bg-gray-50 font-mono text-xs text-gray-700" @focus="$event.target.select()" />
                <button type="button" class="w-20 flex-none rounded border border-gray-300 px-3 py-2 text-sm font-semibold hover:bg-gray-50" @click="copyLink(locale)">
                    {{ copied === locale ? 'Copied' : 'Copy' }}
                </button>
                <a :href="link[locale]" target="_blank" class="flex-none text-sm text-gray-600 underline hover:text-gray-900">Open ↗</a>
            </div>
        </div>

        <!-- The days -->
        <div class="space-y-4">
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

                <p v-if="!boxes(day).length" class="px-5 py-4 text-sm text-gray-400">
                    {{ day.extra
                        ? 'Nothing yet — for arrivals and departures: a transfer, a dinner, a meeting point. An empty day is left off the page.'
                        : 'Nothing published in Programme for this day yet, and no events.' }}
                </p>

                <ul v-else class="divide-y divide-gray-100">
                    <template v-for="box in boxes(day)" :key="box.kind === 'event' ? box.event.id : 'programme'">
                        <!-- The programme, as the one box it is on the page -->
                        <li v-if="box.kind === 'programme'" class="flex items-start gap-4 border-l-4 border-red-600 bg-red-50/60 px-5 py-4">
                            <span class="w-24 flex-none pt-0.5 font-mono text-xs font-semibold text-red-700">
                                {{ day.programme.start }}–{{ day.programme.end }}
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-semibold">
                                    Official programme
                                    <span class="ml-1 font-normal text-gray-500">
                                        one box · {{ day.programme.sessions }} {{ day.programme.sessions === 1 ? 'session' : 'sessions' }} behind it
                                    </span>
                                </span>
                                <span class="mt-0.5 block text-xs text-gray-500">
                                    {{ day.programme.starts_at || day.programme.ends_at
                                        ? `Hours set by hand — Programme itself runs ${day.programme.auto.start}–${day.programme.auto.end}.`
                                        : 'Hours follow Programme: first session’s start to the last one’s end.' }}
                                </span>
                                <span v-if="day.programme.note" class="mt-2 block whitespace-pre-line text-sm text-gray-700">{{ day.programme.note }}</span>
                                <span v-else class="mt-2 block text-sm italic text-gray-400">No note yet — when to arrive, where speakers eat…</span>
                            </span>
                            <button type="button" class="flex-none text-sm text-gray-600 underline hover:text-gray-900" @click="openBox(day)">Edit note &amp; hours</button>
                        </li>

                        <li v-else class="flex items-start gap-4 px-5 py-3">
                            <span class="w-24 flex-none pt-0.5 font-mono text-xs text-gray-500">
                                {{ box.event.starts_at }}<template v-if="box.event.ends_at">–{{ box.event.ends_at }}</template>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-medium">{{ box.event.title }}</span>
                                <span v-if="place(box.event.location, box.event.description).name || place(box.event.location, box.event.description).map" class="block text-xs text-gray-500">
                                    {{ place(box.event.location, box.event.description).name }}
                                    <span v-if="place(box.event.location, box.event.description).map" class="font-medium text-green-700">📍 on the map</span>
                                </span>
                            </span>
                            <span class="flex flex-none gap-3 text-sm">
                                <button type="button" class="text-gray-600 underline hover:text-gray-900" @click="openEvent(box.event)">Edit</button>
                                <button type="button" class="text-red-600 underline hover:text-red-800" @click="removeEvent(box.event)">Remove</button>
                            </span>
                        </li>
                    </template>
                </ul>
            </div>
        </div>

        <!-- Previewer: the real page, framed -->
        <div class="mt-6 rounded border border-gray-200 bg-white">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-3">
                <h2 class="text-sm font-semibold">Preview <span class="font-normal text-gray-400">the page behind the link</span></h2>
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
                <DraftNotice :draft="eventDraft" />

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
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Ends <span class="font-normal text-gray-400">optional</span></label>
                        <input v-model="eventForm.ends_at" type="time" class="w-full rounded border-gray-300 text-sm" />
                    </div>
                </div>
                <p v-if="eventForm.errors.starts_at" class="text-sm text-red-600">{{ eventForm.errors.starts_at }}</p>
                <p v-if="eventForm.errors.ends_at" class="text-sm text-red-600">{{ eventForm.errors.ends_at }}</p>
                <p v-if="formDay?.programme" class="text-xs text-gray-500">
                    The programme takes {{ formDay.programme.start }}–{{ formDay.programme.end }} on this day. An event can fall
                    during it — it is listed by its time next to the programme box.
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

                <p class="text-xs text-gray-500">
                    Web addresses in the location or description become links. Paste a map link — Google Maps, Apple Maps,
                    OpenStreetMap, Waze — and it becomes the event’s place: the location opens the map, so a restaurant or a
                    museum is one tap away.
                    <span v-if="place(eventForm.location, eventForm.description).map" class="font-medium text-green-700">📍 Map found.</span>
                </p>

                <div class="flex justify-end gap-3">
                    <button type="button" class="rounded px-4 py-2 text-sm text-gray-600 hover:text-gray-900" @click="editing = null">Cancel</button>
                    <button type="submit" :disabled="eventForm.processing" class="rounded bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50">
                        {{ editing === 'new' ? 'Add event' : 'Save' }}
                    </button>
                </div>
            </form>
        </div>

        <!-- A day's programme box: its note, and its hours -->
        <div v-if="boxDay" class="fixed inset-0 z-10 flex items-center justify-center overflow-y-auto bg-black/40 p-4" @click.self="boxDay = null">
            <form class="w-full max-w-2xl space-y-4 rounded bg-white p-6" @submit.prevent="saveBox">
                <h2 class="text-lg font-bold">Programme box · {{ boxDay.label }}</h2>
                <DraftNotice :draft="boxDraft" />

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium">Starts</label>
                        <input v-model="boxForm.starts_at" type="time" class="w-full rounded border-gray-300 text-sm" />
                        <p v-if="boxForm.errors.starts_at" class="mt-1 text-sm text-red-600">{{ boxForm.errors.starts_at }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Ends</label>
                        <input v-model="boxForm.ends_at" type="time" class="w-full rounded border-gray-300 text-sm" />
                        <p v-if="boxForm.errors.ends_at" class="mt-1 text-sm text-red-600">{{ boxForm.errors.ends_at }}</p>
                    </div>
                </div>
                <p class="text-xs text-gray-500">
                    Leave an hour empty and it follows Programme — {{ boxDay.programme.auto.start }}–{{ boxDay.programme.auto.end }} today
                    (first session’s start to the last one’s end; a session with no end counts as an hour).
                </p>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium">Note <span class="font-normal text-gray-400">English</span></label>
                        <textarea v-model="boxForm.note" rows="7" placeholder="Please be at the venue by 08:45.&#10;Lunch for speakers: 13:00, at …" class="w-full rounded border-gray-300 text-sm"></textarea>
                        <p v-if="boxForm.errors.note" class="mt-1 text-sm text-red-600">{{ boxForm.errors.note }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Note <span class="font-normal text-gray-400">Romanian · empty falls back to English</span></label>
                        <textarea v-model="boxForm.note_ro" rows="7" class="w-full rounded border-gray-300 text-sm"></textarea>
                        <p v-if="boxForm.errors.note_ro" class="mt-1 text-sm text-red-600">{{ boxForm.errors.note_ro }}</p>
                    </div>
                </div>
                <p class="text-xs text-gray-500">Shown on the box exactly as typed, line breaks included; web addresses become links, and a map link reads “See on map”. The rest of the box opens that day’s public programme in a new tab.</p>

                <div class="flex justify-end gap-3">
                    <button type="button" class="rounded px-4 py-2 text-sm text-gray-600 hover:text-gray-900" @click="boxDay = null">Cancel</button>
                    <button type="submit" :disabled="boxForm.processing" class="rounded bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50">Save</button>
                </div>
            </form>
        </div>
    </Admin2026>
</template>
