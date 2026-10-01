<script setup>
import { useForm } from '@inertiajs/inertia-vue3';
import { computed, ref } from 'vue';
import Admin2026 from '../../../Layouts/Admin2026.vue';

/*
 * The internal agenda: the events that are not in the public programme, the
 * people the agenda is sent to, and a preview of the page they open.
 *
 * The official sessions are not edited here — they come from Programme, and
 * the agenda page weaves the two together by the clock.
 */
const props = defineProps({
    days: { type: Array, default: () => [] },
    recipients: { type: Array, default: () => [] },
    speakers: { type: Array, default: () => [] },
    publicBase: { type: String, default: '' },
});

const blank = {
    programme_day_id: null, starts_at: '', ends_at: '',
    title: '', title_ro: '', location: '', location_ro: '', description: '', description_ro: '',
};

const editing = ref(null); // null, 'new', or the event's id
const eventForm = useForm({ ...blank });
const recipientForm = useForm({ name: '', email: '', locale: 'en' });
const action = useForm({});

/*
 * The previewer is the real page in a frame, so what the office sees is what
 * is sent. It is reloaded after every change to an event — the key changes,
 * and the frame is built again.
 */
const previewLocale = ref('en');
const previewKey = ref(0);
const previewUrl = computed(() => `/dashboard/agenda/preview/${previewLocale.value}`);
const refreshPreview = () => (previewKey.value += 1);

function openEvent(event, dayId) {
    editing.value = event?.id ?? 'new';
    eventForm.defaults(event
        ? { ...blank, ...event }
        : { ...blank, programme_day_id: dayId ?? props.days[0]?.id ?? null });
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

function addRecipient() {
    recipientForm.post('/dashboard/agenda/recipients', {
        preserveScroll: true,
        onSuccess: () => recipientForm.reset(),
    });
}

function removeRecipient(recipient) {
    if (confirm(`Remove ${recipient.name}? The link in their inbox will stop working.`)) {
        action.delete(`/dashboard/agenda/recipients/${recipient.id}`, { preserveScroll: true });
    }
}

// An email leaves the building, so every send asks first.
function send(recipient) {
    const verb = recipient.sent_count ? 'Send the programme again' : 'Send the programme';

    if (confirm(`${verb} to ${recipient.email}?`)) {
        action.post(`/dashboard/agenda/recipients/${recipient.id}/send`, { preserveScroll: true });
    }
}

const pending = computed(() => props.recipients.filter((r) => ! r.sent_count).length);

function sendAll() {
    const who = pending.value === 1 ? '1 person' : `${pending.value} people`;

    if (confirm(`Send the programme to the ${who} who have not had it yet?`)) {
        action.post('/dashboard/agenda/send', { preserveScroll: true });
    }
}

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

        <div class="grid gap-6 lg:grid-cols-5">
            <!-- Events, by day -->
            <div class="space-y-4 lg:col-span-3">
                <div v-if="!days.length" class="rounded border border-gray-200 bg-white p-5 text-sm text-gray-500">
                    Add the days in Programme first — an event belongs to a day.
                </div>

                <div v-for="day in days" :key="day.id" class="rounded border border-gray-200 bg-white">
                    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3">
                        <h2 class="text-sm font-semibold">{{ day.label }}</h2>
                        <button type="button" class="text-sm font-semibold text-red-600 hover:text-red-800" @click="openEvent(null, day.id)">
                            + Add event
                        </button>
                    </div>

                    <p v-if="!day.events.length" class="px-5 py-4 text-sm text-gray-400">No internal events on this day.</p>

                    <ul v-else class="divide-y divide-gray-100">
                        <li v-for="event in day.events" :key="event.id" class="flex items-start gap-4 px-5 py-3">
                            <span class="w-24 flex-none pt-0.5 font-mono text-xs text-gray-500">
                                {{ event.starts_at }}<template v-if="event.ends_at">–{{ event.ends_at }}</template>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-medium">{{ event.title }}</span>
                                <span v-if="event.location" class="block text-xs text-gray-500">{{ event.location }}</span>
                            </span>
                            <span class="flex flex-none gap-3 text-sm">
                                <button type="button" class="text-gray-600 underline hover:text-gray-900" @click="openEvent(event)">Edit</button>
                                <button type="button" class="text-red-600 underline hover:text-red-800" @click="removeEvent(event)">Remove</button>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Recipients -->
            <div class="space-y-4 lg:col-span-2">
                <div class="rounded border border-gray-200 bg-white p-5">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold">Recipients <span class="font-normal text-gray-400">{{ recipients.length }}</span></h2>
                        <button
                            type="button"
                            :disabled="!pending || action.processing"
                            class="rounded bg-red-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-40"
                            @click="sendAll"
                        >Send programme<template v-if="pending"> to {{ pending }} new</template></button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        Each person gets their own link, in their language. The link always shows the latest agenda, so
                        send again only to nudge — not after every change.
                    </p>

                    <!-- Two by two: the column is narrow, and four controls in a
                         row left the language picker too thin to read. -->
                    <form class="mt-4 grid grid-cols-2 gap-2" @submit.prevent="addRecipient">
                        <input v-model="recipientForm.name" type="text" placeholder="Name" list="agenda-speakers" class="min-w-0 rounded border-gray-300 text-sm" />
                        <input v-model="recipientForm.email" type="email" placeholder="Email" class="min-w-0 rounded border-gray-300 text-sm" />
                        <select v-model="recipientForm.locale" aria-label="Language" class="rounded border-gray-300 text-sm">
                            <option value="en">English</option>
                            <option value="ro">Romanian</option>
                        </select>
                        <button type="submit" :disabled="recipientForm.processing" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold hover:bg-gray-50 disabled:opacity-50">Add recipient</button>
                        <datalist id="agenda-speakers">
                            <option v-for="speaker in speakers" :key="speaker" :value="speaker" />
                        </datalist>
                    </form>
                    <p v-for="(message, field) in recipientForm.errors" :key="field" class="mt-1 text-sm text-red-600">{{ message }}</p>
                </div>

                <div v-if="recipients.length" class="rounded border border-gray-200 bg-white">
                    <ul class="divide-y divide-gray-100">
                        <li v-for="recipient in recipients" :key="recipient.id" class="px-5 py-3">
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
                                    :disabled="action.processing"
                                    class="flex-none rounded border px-3 py-1 text-sm font-semibold disabled:opacity-50"
                                    :class="recipient.sent_count ? 'border-gray-300 text-gray-700 hover:bg-gray-50' : 'border-red-600 text-red-600 hover:bg-red-50'"
                                    @click="send(recipient)"
                                >{{ recipient.sent_count ? 'Send again' : 'Send programme' }}</button>
                            </div>
                            <div class="mt-1.5 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs">
                                <span :class="recipient.sent_count ? 'text-green-700' : 'text-gray-400'">
                                    {{ recipient.sent_count ? `Sent ${recipient.sent_count}× · last ${recipient.last_sent}` : 'Not sent yet' }}
                                </span>
                                <a :href="recipient.url" target="_blank" class="text-gray-500 underline hover:text-gray-900">Open their page ↗</a>
                                <button type="button" class="text-gray-500 underline hover:text-gray-900" @click="copyLink(recipient)">
                                    {{ copied === recipient.id ? 'Copied' : 'Copy link' }}
                                </button>
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
                        <select v-model="eventForm.programme_day_id" class="w-full rounded border-gray-300 text-sm">
                            <option v-for="day in days" :key="day.id" :value="day.id">{{ day.label }}</option>
                        </select>
                        <p v-if="eventForm.errors.programme_day_id" class="mt-1 text-sm text-red-600">{{ eventForm.errors.programme_day_id }}</p>
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
