<script setup>
import { ref, reactive } from 'vue';
import { Link, useForm } from '@inertiajs/inertia-vue3';
import { Inertia } from '@inertiajs/inertia';
import DraftNotice from '../../../Components/DraftNotice.vue';
import { useDraft } from '../../../formDraft';
import { backdrop } from '../../../backdrop';
import Admin2026 from '../../../Layouts/Admin2026.vue';

const props = defineProps({
    sessions: { type: Array, default: () => [] },
    publicBase: { type: String, default: '' },
});

const action = useForm({});

const toggleAuto = (session) => action.put(`/dashboard/sessions/${session.id}/auto-confirm`, { preserveScroll: true });

// Everyone holding a place who has not confirmed — a second run is the reminder.
const unconfirmed = (session) => session.bookings.filter((b) => ! b.cancelled && ! b.confirmed);

/*
 * The run for a whole workshop: one email per request, a pause between — the
 * mail provider takes a couple a second and a request lives thirty seconds.
 */
const runs = reactive({}); // session id → { done, total } while sending, { sent, failed } once done

async function sendToUnconfirmed(session) {
    const list = unconfirmed(session);

    if (runs[session.id]?.total || ! list.length
        || ! confirm(`Ask ${list.length} ${list.length === 1 ? 'person' : 'people'} to confirm their place at “${session.title}”? Anyone already asked gets it again, as a reminder.`)) {
        return;
    }

    const failed = [];
    runs[session.id] = { done: 0, total: list.length };

    for (const booking of list) {
        try {
            const { data } = await window.axios.post(`/dashboard/bookings/${booking.id}/send`);
            if (! data.ok) failed.push({ ...booking, error: data.error });
        } catch (e) {
            failed.push({ ...booking, error: e.response?.data?.message || e.message });
        }

        runs[session.id].done += 1;

        if (runs[session.id].done < list.length) {
            await new Promise((resolve) => setTimeout(resolve, 600));
        }
    }

    runs[session.id] = { sent: list.length - failed.length, failed };
    Inertia.reload({ preserveScroll: true });
}

// The request to confirm, to one person — e.g. after correcting a mistyped address in Edit.
function sendConfirmation(booking) {
    if (confirm(`Ask ${booking.name} to confirm their place, at ${booking.email}?`)) {
        action.post(`/dashboard/bookings/${booking.id}/send`, {
            preserveScroll: true,
            onError: (errors) => alert(errors.booking || 'The email did not go out.'),
        });
    }
}
const open = ref({}); // session id -> attendee list expanded

function toggleList(id) {
    open.value = { ...open.value, [id]: !open.value[id] };
}

// ── Internal-workshop places ────────────────────────────────────────────────
// The email being edited per place, seeded from what is saved.
const emails = ref({});
props.sessions.forEach((s) => (s.places || []).forEach((p) => { emails.value[p.id] = p.email || ''; }));
const placeAction = useForm({ email: '' });

// Persist the email as it is typed, so the Send button always has it.
function savePlace(place) {
    placeAction.email = emails.value[place.id] ?? '';
    placeAction.put(`/dashboard/places/${place.id}`, { preserveScroll: true });
}

function invitePlace(place) {
    const email = emails.value[place.id];
    if (!email) {
        return;
    }
    // Save the current email first, so the invitation always goes to it.
    placeAction.email = email;
    placeAction.put(`/dashboard/places/${place.id}`, {
        preserveScroll: true,
        onSuccess: () => action.post(`/dashboard/places/${place.id}/invite`, { preserveScroll: true }),
    });
}

// ── Attendees CRUD ──────────────────────────────────────────────────────────
const editingBooking = ref(null);
const bookingForm = useForm({ first_name: '', last_name: '', email: '', phone: '' });
const bookingDraft = useDraft(bookingForm, 'booking');
// Clicking beside the window closes it — only a click that started there.
const bookingBackdrop = backdrop(() => (editingBooking.value = null));

function openAddBooking(session) {
    editingBooking.value = { sessionId: session.id, sessionTitle: session.title, id: null };
    bookingForm.clearErrors();
    bookingForm.first_name = bookingForm.last_name = bookingForm.email = bookingForm.phone = '';
    bookingDraft.start(`new-${session.id}`);
}

function openEditBooking(session, booking) {
    editingBooking.value = { sessionId: session.id, sessionTitle: session.title, id: booking.id };
    bookingForm.clearErrors();
    bookingForm.first_name = booking.first_name || booking.name.split(' ').slice(0, -1).join(' ') || booking.name;
    bookingForm.last_name = booking.last_name || booking.name.split(' ').slice(-1).join(' ');
    bookingForm.email = booking.email;
    bookingForm.phone = booking.phone || '';
    bookingDraft.start(booking.id);
}

function saveBooking() {
    if (editingBooking.value.id) {
        bookingForm.put(`/dashboard/bookings/${editingBooking.value.id}`, { preserveScroll: true, onSuccess: saved });
    } else {
        bookingForm.transform((d) => ({ ...d, session_id: editingBooking.value.sessionId }))
            .post('/dashboard/bookings', { preserveScroll: true, onSuccess: saved });
    }
}

function saved() {
    bookingDraft.finish();
    editingBooking.value = null;
}

function toggleBooking(booking) {
    const q = booking.cancelled
        ? `Give ${booking.name} their place back?`
        : `Release ${booking.name}’s place? The place goes back to the pool.`;
    if (confirm(q)) action.post(`/dashboard/bookings/${booking.id}/cancel`, { preserveScroll: true });
}

function removeBooking(booking) {
    if (confirm(`Delete ${booking.name}’s booking for good? Use Release to just free the place.`)) {
        action.delete(`/dashboard/bookings/${booking.id}`, { preserveScroll: true });
    }
}
</script>

<template>
    <Admin2026 title="Workshops" :public-base="publicBase">
        <p v-if="!sessions.length" class="bg-white rounded border border-gray-200 p-8 text-center text-gray-500">
            No workshop or tour yet. Open one in the programme and set its type to Workshop or Guided tour.
        </p>

        <section v-for="session in sessions" :key="session.id" class="bg-white rounded border border-gray-200 mb-6">
            <header class="flex flex-wrap items-start gap-4 border-b border-gray-100 p-5">
                <img v-if="session.image" :src="session.image" alt="" class="h-20 w-28 rounded object-cover bg-gray-100" />
                <div v-else class="h-20 w-28 rounded bg-gray-100"></div>

                <div class="min-w-0 flex-1">
                    <p class="font-bold">
                        {{ session.title }}
                        <span class="ml-1 rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-500 capitalize">{{ session.type }}</span>
                        <span v-if="session.internal" class="ml-1 rounded bg-gray-800 px-1.5 py-0.5 text-xs text-white">🔒 internal</span>
                        <span v-if="!session.published" class="ml-1 rounded bg-amber-100 px-2 py-0.5 text-xs text-amber-800">draft</span>
                    </p>
                    <p v-if="session.en.subtitle" class="text-sm text-gray-500">{{ session.en.subtitle }}</p>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ session.day }} · {{ session.time }}<span v-if="session.trainerNames"> · {{ session.trainerNames }}</span>
                    </p>
                </div>

                <div class="text-right">
                    <p class="text-2xl font-bold" :class="session.taken >= session.capacity ? 'text-red-600' : ''">
                        {{ session.taken }}<span class="text-base font-normal text-gray-500">/{{ session.capacity }}</span>
                    </p>
                    <!-- The workshop's own content (title, picture, trainer, capacity)
                         is edited in the programme, where every session lives. -->
                    <Link :href="`/dashboard/programme/sessions/${session.id}`" class="text-sm text-gray-500 hover:text-gray-900">Edit in programme →</Link>
                </div>
            </header>

            <!-- Attendees, folded away until asked for. -->
            <template v-if="!session.internal">
            <div class="px-5 py-3 flex items-center justify-between">
                <button type="button" class="text-sm font-medium text-gray-700 hover:text-red-600" @click="toggleList(session.id)">
                    <span class="inline-block w-3">{{ open[session.id] ? '▾' : '▸' }}</span>
                    {{ session.bookings.length }} {{ session.bookings.length === 1 ? 'attendee' : 'attendees' }}
                </button>
                <button type="button" class="text-sm text-gray-500 hover:text-gray-900" @click="openAddBooking(session)">+ Add attendee</button>
            </div>

            <!-- Confirmations, per workshop: off until the office turns it on. -->
            <div class="border-t border-gray-100 px-5 py-3 text-sm">
                <label class="flex items-center gap-2">
                    <input type="checkbox" :checked="session.auto_confirm" class="rounded border-gray-300 text-red-600" @change="toggleAuto(session)" />
                    <span>Send booking confirmations automatically</span>
                    <span class="text-xs text-gray-500">— each new booking is emailed a link to confirm or release the place</span>
                </label>

                <div v-if="session.auto_confirm" class="mt-3 flex flex-wrap items-center gap-3">
                    <button
                        type="button"
                        :disabled="!unconfirmed(session).length || !!runs[session.id]?.total"
                        class="rounded bg-red-600 px-3 py-1.5 font-semibold text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-40"
                        @click="sendToUnconfirmed(session)"
                    >Send confirmations to users who have not confirmed ({{ unconfirmed(session).length }})</button>
                    <span class="text-xs text-gray-500">
                        {{ session.bookings.filter((b) => b.confirmed && !b.cancelled).length }} confirmed ·
                        {{ unconfirmed(session).filter((b) => b.asked).length }} asked, waiting ·
                        {{ unconfirmed(session).filter((b) => !b.asked).length }} not asked
                    </span>
                </div>

                <div v-if="runs[session.id]?.total" class="mt-3">
                    <p class="text-gray-700">Sending {{ Math.min(runs[session.id].done + 1, runs[session.id].total) }} of {{ runs[session.id].total }}… keep this page open.</p>
                    <div class="mt-1.5 h-1.5 overflow-hidden rounded bg-gray-100">
                        <div class="h-full bg-red-600 transition-all" :style="{ width: `${(runs[session.id].done / runs[session.id].total) * 100}%` }"></div>
                    </div>
                </div>
                <div
                    v-else-if="runs[session.id]"
                    class="mt-3 rounded border px-3 py-2"
                    :class="runs[session.id].failed.length ? 'border-amber-300 bg-amber-50 text-amber-900' : 'border-green-200 bg-green-50 text-green-800'"
                >
                    Sent to {{ runs[session.id].sent }}<template v-if="runs[session.id].failed.length"> — {{ runs[session.id].failed.length }} could not be sent:</template>.
                    <ul v-if="runs[session.id].failed.length" class="mt-1 list-disc pl-5 text-xs">
                        <li v-for="f in runs[session.id].failed" :key="f.id">{{ f.name }} &lt;{{ f.email }}&gt; — {{ f.error }}</li>
                    </ul>
                </div>
            </div>

            <table v-if="open[session.id] && session.bookings.length" class="min-w-full border-t border-gray-100 text-sm">
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="booking in session.bookings" :key="booking.id" :class="booking.cancelled ? 'text-gray-400' : ''">
                        <td class="px-5 py-3">
                            <span :class="booking.cancelled ? 'line-through' : 'font-medium'">{{ booking.name }}</span>
                            <span v-if="booking.age != null" class="ml-2 rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-600">age {{ booking.age }}</span>
                            <p class="text-xs text-gray-500">{{ booking.email }}<span v-if="booking.phone"> · {{ booking.phone }}</span></p>
                            <!-- Under 18: the parent who booked, and their written consent. -->
                            <p v-if="booking.guardian_name" class="text-xs text-gray-500">
                                Guardian: {{ booking.guardian_name }}<span v-if="booking.guardian_phone"> · {{ booking.guardian_phone }}</span>
                                <span v-if="booking.guardian_consent" class="ml-1 rounded bg-green-100 px-1.5 py-0.5 text-green-800">“{{ booking.guardian_consent }}”</span>
                            </p>
                        </td>
                        <td class="px-2 py-3 text-gray-500 whitespace-nowrap">{{ booking.created.slice(0, 16) }}</td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            <!-- Where they stand: confirmed, asked and waiting, or not asked. -->
                            <span v-if="booking.cancelled"></span>
                            <span v-else-if="booking.confirmed" class="mr-3 rounded bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Confirmed</span>
                            <template v-else>
                                <span
                                    class="mr-2 rounded px-2 py-0.5 text-xs font-medium"
                                    :class="booking.asked ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-600'"
                                    :title="booking.asked ? `Asked ${booking.asked_count}× — last ${booking.asked}` : ''"
                                >{{ booking.asked ? `Asked ${booking.asked}` : 'Not asked' }}</span>
                                <button type="button" class="mr-3 text-gray-500 hover:text-gray-900" @click="sendConfirmation(booking)">Send confirmation</button>
                            </template>
                            <button type="button" class="text-gray-500 hover:text-gray-900" @click="openEditBooking(session, booking)">Edit</button>
                            <button type="button" class="ml-3 text-gray-500 hover:text-red-600" @click="toggleBooking(booking)">
                                {{ booking.cancelled ? 'Restore' : 'Release' }}
                            </button>
                            <button type="button" class="ml-3 text-gray-400 hover:text-red-600" @click="removeBooking(booking)">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-else-if="open[session.id]" class="border-t border-gray-100 px-5 py-6 text-center text-sm text-gray-500">Nobody has booked yet.</p>
            </template>

            <!-- Internal: places with codes, an email to invite, and Send —
                 folded away like the attendees above. -->
            <template v-else>
                <div class="px-5 py-3">
                    <button type="button" class="text-sm font-medium text-gray-700 hover:text-red-600" @click="toggleList(session.id)">
                        <span class="inline-block w-3">{{ open[session.id] ? '▾' : '▸' }}</span>
                        {{ session.places.length }} {{ session.places.length === 1 ? 'place' : 'places' }}
                        <span class="font-normal text-gray-500">
                            · {{ session.places.filter((p) => p.status === 'invited' && !p.confirmed).length }} invited
                            · {{ session.places.filter((p) => p.confirmed).length }} confirmed
                        </span>
                    </button>
                </div>
                <table v-if="open[session.id]" class="min-w-full border-t border-gray-100 text-sm">
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="place in session.places" :key="place.id">
                            <td class="px-5 py-3 w-28">
                                <span class="font-mono font-semibold tracking-wider">{{ place.code }}</span>
                            </td>
                            <td class="px-2 py-3">
                                <input
                                    v-model="emails[place.id]"
                                    type="email"
                                    placeholder="email to invite"
                                    class="w-full max-w-xs rounded border-gray-300 text-sm"
                                    @change="savePlace(place)"
                                />
                            </td>
                            <td class="px-2 py-3 whitespace-nowrap">
                                <span
                                    :class="[
                                        'rounded px-2 py-0.5 text-xs',
                                        place.confirmed ? 'bg-green-100 text-green-800' : place.status === 'invited' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-500',
                                    ]"
                                >{{ place.confirmed ? 'confirmed' : place.status }}</span>
                            </td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                <button
                                    type="button"
                                    class="text-gray-500 hover:text-gray-900 disabled:opacity-40 disabled:cursor-not-allowed"
                                    :disabled="!emails[place.id]"
                                    @click="invitePlace(place)"
                                >{{ place.confirmed ? 'Resend confirmation' : place.status === 'open' ? 'Send invite' : 'Resend invite' }}</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p v-if="open[session.id] && !session.places.length" class="border-t border-gray-100 px-5 py-6 text-center text-sm text-gray-500">
                    Set a capacity on this workshop to generate places.
                </p>
            </template>
        </section>

        <!-- Add / edit an attendee -->
        <div v-if="editingBooking" class="fixed inset-0 bg-black/40 flex items-center justify-center p-4" v-on="bookingBackdrop">
            <form class="bg-white rounded p-5 w-full max-w-md space-y-4" @submit.prevent="saveBooking">
                <h2 class="font-bold text-lg">{{ editingBooking.id ? 'Edit attendee' : 'Add attendee' }}</h2>
                <p class="text-sm text-gray-500">{{ editingBooking.sessionTitle }}</p>
                <DraftNotice :draft="bookingDraft" />

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium mb-1">First name</label>
                        <input v-model="bookingForm.first_name" type="text" class="w-full rounded border-gray-300 text-sm" />
                        <p v-if="bookingForm.errors.first_name" class="mt-1 text-sm text-red-600">{{ bookingForm.errors.first_name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Last name</label>
                        <input v-model="bookingForm.last_name" type="text" class="w-full rounded border-gray-300 text-sm" />
                        <p v-if="bookingForm.errors.last_name" class="mt-1 text-sm text-red-600">{{ bookingForm.errors.last_name }}</p>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input v-model="bookingForm.email" type="email" class="w-full rounded border-gray-300 text-sm" />
                    <p v-if="bookingForm.errors.email" class="mt-1 text-sm text-red-600">{{ bookingForm.errors.email }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Phone</label>
                    <input v-model="bookingForm.phone" type="tel" class="w-full rounded border-gray-300 text-sm" />
                    <p v-if="bookingForm.errors.phone" class="mt-1 text-sm text-red-600">{{ bookingForm.errors.phone }}</p>
                </div>

                <div class="flex justify-end gap-3 pt-1">
                    <button type="button" class="text-sm text-gray-500" @click="editingBooking = null">Cancel</button>
                    <button type="submit" :disabled="bookingForm.processing" class="rounded bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50">{{ editingBooking.id ? 'Save' : 'Add' }}</button>
                </div>
            </form>
        </div>
    </Admin2026>
</template>
