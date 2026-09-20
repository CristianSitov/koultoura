<script setup>
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/inertia-vue3';
import Admin2026 from '../../../Layouts/Admin2026.vue';

defineProps({
    sessions: { type: Array, default: () => [] },
    publicBase: { type: String, default: '' },
});

const action = useForm({});
const open = ref({}); // session id -> attendee list expanded

function toggleList(id) {
    open.value = { ...open.value, [id]: !open.value[id] };
}

// ── Attendees CRUD ──────────────────────────────────────────────────────────
const editingBooking = ref(null);
const bookingForm = useForm({ first_name: '', last_name: '', email: '', phone: '' });

function openAddBooking(session) {
    editingBooking.value = { sessionId: session.id, sessionTitle: session.title, id: null };
    bookingForm.clearErrors();
    bookingForm.first_name = bookingForm.last_name = bookingForm.email = bookingForm.phone = '';
}

function openEditBooking(session, booking) {
    editingBooking.value = { sessionId: session.id, sessionTitle: session.title, id: booking.id };
    bookingForm.clearErrors();
    bookingForm.first_name = booking.first_name || booking.name.split(' ').slice(0, -1).join(' ') || booking.name;
    bookingForm.last_name = booking.last_name || booking.name.split(' ').slice(-1).join(' ');
    bookingForm.email = booking.email;
    bookingForm.phone = booking.phone || '';
}

function saveBooking() {
    if (editingBooking.value.id) {
        bookingForm.put(`/dashboard/bookings/${editingBooking.value.id}`, { preserveScroll: true, onSuccess: () => (editingBooking.value = null) });
    } else {
        bookingForm.transform((d) => ({ ...d, session_id: editingBooking.value.sessionId }))
            .post('/dashboard/bookings', { preserveScroll: true, onSuccess: () => (editingBooking.value = null) });
    }
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
            <div class="px-5 py-3 flex items-center justify-between">
                <button type="button" class="text-sm font-medium text-gray-700 hover:text-red-600" @click="toggleList(session.id)">
                    <span class="inline-block w-3">{{ open[session.id] ? '▾' : '▸' }}</span>
                    {{ session.bookings.length }} {{ session.bookings.length === 1 ? 'attendee' : 'attendees' }}
                </button>
                <button type="button" class="text-sm text-gray-500 hover:text-gray-900" @click="openAddBooking(session)">+ Add attendee</button>
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
        </section>

        <!-- Add / edit an attendee -->
        <div v-if="editingBooking" class="fixed inset-0 bg-black/40 flex items-center justify-center p-4" @click.self="editingBooking = null">
            <form class="bg-white rounded p-5 w-full max-w-md space-y-4" @submit.prevent="saveBooking">
                <h2 class="font-bold text-lg">{{ editingBooking.id ? 'Edit attendee' : 'Add attendee' }}</h2>
                <p class="text-sm text-gray-500">{{ editingBooking.sessionTitle }}</p>

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
