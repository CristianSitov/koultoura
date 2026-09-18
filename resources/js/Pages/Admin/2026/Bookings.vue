<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/inertia-vue3';
import Admin2026 from '../../../Layouts/Admin2026.vue';

defineProps({
    sessions: { type: Array, default: () => [] },
    publicBase: { type: String, default: '' },
});

const action = useForm({});

// One modal, for both adding a booking to a session and editing an existing one.
const editing = ref(null); // { sessionId, sessionTitle, id|null }
const form = useForm({ first_name: '', last_name: '', email: '', phone: '' });

function openAdd(session) {
    editing.value = { sessionId: session.id, sessionTitle: session.title, id: null };
    form.clearErrors();
    form.first_name = form.last_name = form.email = form.phone = '';
}

function openEdit(session, booking) {
    editing.value = { sessionId: session.id, sessionTitle: session.title, id: booking.id };
    form.clearErrors();
    // Older rows have only the composed name; split it as a fallback.
    form.first_name = booking.first_name || booking.name.split(' ').slice(0, -1).join(' ') || booking.name;
    form.last_name = booking.last_name || booking.name.split(' ').slice(-1).join(' ');
    form.email = booking.email;
    form.phone = booking.phone || '';
}

function save() {
    if (editing.value.id) {
        form.put(`/dashboard/bookings/${editing.value.id}`, { preserveScroll: true, onSuccess: () => (editing.value = null) });
    } else {
        form.transform((d) => ({ ...d, session_id: editing.value.sessionId }))
            .post('/dashboard/bookings', { preserveScroll: true, onSuccess: () => (editing.value = null) });
    }
}

function toggleBooking(booking) {
    const question = booking.cancelled
        ? `Give ${booking.name} their place back?`
        : `Release ${booking.name}’s place? They keep the row; the place goes back to the pool.`;

    if (confirm(question)) {
        action.post(`/dashboard/bookings/${booking.id}/cancel`, { preserveScroll: true });
    }
}

function removeBooking(booking) {
    if (confirm(`Delete ${booking.name}’s booking for good? This cannot be undone — use Release to just free the place.`)) {
        action.delete(`/dashboard/bookings/${booking.id}`, { preserveScroll: true });
    }
}
</script>

<template>
    <Admin2026 title="Bookings" :public-base="publicBase">
        <p v-if="!sessions.length" class="bg-white rounded border border-gray-200 p-8 text-center text-gray-500">
            No session takes bookings yet. Open one in the programme and set its type to Workshop or Guided tour.
        </p>

        <section v-for="session in sessions" :key="session.id" class="bg-white rounded border border-gray-200 mb-6">
            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4">
                <div>
                    <p class="font-bold">
                        {{ session.title }}
                        <span v-if="!session.published" class="ml-2 rounded bg-amber-100 px-2 py-0.5 text-xs text-amber-800">draft</span>
                    </p>
                    <p class="text-sm text-gray-500">{{ session.day }} · {{ session.time }}</p>
                </div>

                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <p class="text-2xl font-bold" :class="session.taken >= session.capacity ? 'text-red-600' : ''">
                            {{ session.taken }}<span class="text-base font-normal text-gray-500">/{{ session.capacity }}</span>
                        </p>
                        <a :href="`${publicBase}/sessions/${session.slug}`" target="_blank" class="text-xs text-gray-500 hover:text-gray-900">
                            booking form ↗
                        </a>
                    </div>
                    <button
                        type="button"
                        class="rounded bg-red-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-red-700"
                        @click="openAdd(session)"
                    >Add booking</button>
                </div>
            </header>

            <table class="min-w-full text-sm">
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="booking in session.bookings" :key="booking.id" :class="booking.cancelled ? 'text-gray-400' : ''">
                        <td class="px-5 py-3">
                            <span :class="booking.cancelled ? 'line-through' : 'font-medium'">{{ booking.name }}</span>
                            <p class="text-xs text-gray-500">{{ booking.email }}<span v-if="booking.phone"> · {{ booking.phone }}</span></p>
                        </td>
                        <td class="px-2 py-3 text-gray-500 whitespace-nowrap">{{ booking.created.slice(0, 16) }}</td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            <button type="button" class="text-gray-500 hover:text-gray-900" @click="openEdit(session, booking)">Edit</button>
                            <button type="button" class="ml-3 text-gray-500 hover:text-red-600" @click="toggleBooking(booking)">
                                {{ booking.cancelled ? 'Restore' : 'Release' }}
                            </button>
                            <button type="button" class="ml-3 text-gray-400 hover:text-red-600" @click="removeBooking(booking)">Delete</button>
                        </td>
                    </tr>
                    <tr v-if="!session.bookings.length">
                        <td colspan="3" class="px-5 py-6 text-center text-gray-500">Nobody has booked yet.</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <!-- Add / edit a booking. -->
        <div v-if="editing" class="fixed inset-0 bg-black/40 flex items-center justify-center p-4" @click.self="editing = null">
            <form class="bg-white rounded p-5 w-full max-w-md space-y-4" @submit.prevent="save">
                <h2 class="font-bold text-lg">{{ editing.id ? 'Edit booking' : 'Add booking' }}</h2>
                <p class="text-sm text-gray-500">{{ editing.sessionTitle }}</p>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium mb-1">First name</label>
                        <input v-model="form.first_name" type="text" class="w-full rounded border-gray-300 text-sm" />
                        <p v-if="form.errors.first_name" class="mt-1 text-sm text-red-600">{{ form.errors.first_name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Last name</label>
                        <input v-model="form.last_name" type="text" class="w-full rounded border-gray-300 text-sm" />
                        <p v-if="form.errors.last_name" class="mt-1 text-sm text-red-600">{{ form.errors.last_name }}</p>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input v-model="form.email" type="email" class="w-full rounded border-gray-300 text-sm" />
                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Phone</label>
                    <input v-model="form.phone" type="tel" class="w-full rounded border-gray-300 text-sm" />
                    <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
                </div>

                <div class="flex justify-end gap-3 pt-1">
                    <button type="button" class="text-sm text-gray-500" @click="editing = null">Cancel</button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50"
                    >{{ editing.id ? 'Save' : 'Add' }}</button>
                </div>
            </form>
        </div>
    </Admin2026>
</template>
