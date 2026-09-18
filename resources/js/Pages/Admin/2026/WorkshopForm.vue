<script setup>
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/inertia-vue3';
import Admin2026 from '../../../Layouts/Admin2026.vue';

const props = defineProps({
    session: { type: Object, required: true },
    people: { type: Array, default: () => [] },
    publicBase: { type: String, default: '' },
});

// ── The workshop's identity ─────────────────────────────────────────────────
const form = useForm({
    en: { ...props.session.en },
    ro: { ...props.session.ro },
    trainers: [...props.session.trainers],
    new_person: { first: '', last: '' },
    image: null,
});

const imagePreview = ref(props.session.image);

function pickImage(event) {
    const file = event.target.files[0];
    form.image = file ?? null;
    imagePreview.value = file ? URL.createObjectURL(file) : props.session.image;
}

// The server redirects back to the workshops list on success.
function submit() {
    form.post(`/dashboard/bookings/workshop/${props.session.id}`, { forceFormData: true });
}

// ── Attendees ───────────────────────────────────────────────────────────────
const action = useForm({});
const editingBooking = ref(null);
const bookingForm = useForm({ first_name: '', last_name: '', email: '', phone: '' });

function openAddBooking() {
    editingBooking.value = { id: null };
    bookingForm.clearErrors();
    bookingForm.first_name = bookingForm.last_name = bookingForm.email = bookingForm.phone = '';
}

function openEditBooking(booking) {
    editingBooking.value = { id: booking.id };
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
        bookingForm.transform((d) => ({ ...d, session_id: props.session.id }))
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
    <Admin2026 :title="`Edit ${session.type}`" :public-base="publicBase">
        <template #actions>
            <Link href="/dashboard/bookings" class="text-sm text-gray-500 hover:text-gray-900">← Workshops</Link>
        </template>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem] items-start">
            <!-- Identity -->
            <form class="space-y-5 bg-white rounded border border-gray-200 p-5" @submit.prevent="submit">
                <h2 class="font-bold text-lg capitalize">{{ session.title || `Edit ${session.type}` }}</h2>

                <div v-for="locale in ['en', 'ro']" :key="locale" class="space-y-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ locale === 'en' ? 'English' : 'Romanian' }}</p>
                    <div>
                        <label class="block text-sm font-medium mb-1">Title</label>
                        <input v-model="form[locale].title" type="text" class="w-full rounded border-gray-300 text-sm" />
                        <p v-if="form.errors[`${locale}.title`]" class="mt-1 text-sm text-red-600">{{ form.errors[`${locale}.title`] }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Subtitle</label>
                        <input v-model="form[locale].subtitle" type="text" class="w-full rounded border-gray-300 text-sm" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Picture</label>
                    <img v-if="imagePreview" :src="imagePreview" alt="" class="w-full rounded bg-gray-100 object-cover aspect-[3/2]" />
                    <div v-else class="w-full rounded bg-gray-100 aspect-[3/2]"></div>
                    <input type="file" accept="image/*" class="mt-2 w-full text-sm" @change="pickImage" />
                    <p v-if="form.errors.image" class="mt-1 text-sm text-red-600">{{ form.errors.image }}</p>
                </div>

                <div>
                    <p class="text-sm font-medium mb-1">Trainer / guide</p>
                    <div class="max-h-40 overflow-y-auto rounded border border-gray-200 p-2 space-y-1">
                        <label v-for="person in people" :key="person.id" class="flex items-center gap-2 text-sm">
                            <input v-model="form.trainers" type="checkbox" :value="person.id" class="rounded border-gray-300 text-red-600" />
                            {{ person.name }}
                            <span v-if="!person.onGrid" class="rounded bg-gray-100 px-1.5 text-xs text-gray-500">not on grid</span>
                        </label>
                    </div>
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        <input v-model="form.new_person.first" type="text" placeholder="New — first name" class="rounded border-gray-300 text-sm" />
                        <input v-model="form.new_person.last" type="text" placeholder="Last name" class="rounded border-gray-300 text-sm" />
                    </div>
                    <p class="mt-1 text-xs text-gray-500">A new person here stays off the public speakers grid.</p>
                </div>

                <div class="flex justify-end gap-3 pt-1">
                    <Link href="/dashboard/bookings" class="text-sm text-gray-500">Cancel</Link>
                    <button type="submit" :disabled="form.processing" class="rounded bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50">Save</button>
                </div>
            </form>

            <!-- Attendees for this workshop -->
            <aside class="bg-white rounded border border-gray-200">
                <header class="flex items-center justify-between border-b border-gray-100 p-4">
                    <p class="font-semibold">
                        Attendees
                        <span class="ml-1 text-sm font-normal" :class="session.taken >= session.capacity ? 'text-red-600' : 'text-gray-500'">
                            {{ session.taken }}<span v-if="session.capacity">/{{ session.capacity }}</span>
                        </span>
                    </p>
                    <button type="button" class="text-sm text-gray-500 hover:text-gray-900" @click="openAddBooking">+ Add</button>
                </header>

                <ul v-if="session.bookings.length" class="divide-y divide-gray-100">
                    <li v-for="booking in session.bookings" :key="booking.id" class="p-4" :class="booking.cancelled ? 'text-gray-400' : ''">
                        <p :class="booking.cancelled ? 'line-through' : 'font-medium'">{{ booking.name }}</p>
                        <p class="text-xs text-gray-500">{{ booking.email }}<span v-if="booking.phone"> · {{ booking.phone }}</span></p>
                        <p class="mt-2 text-sm space-x-3">
                            <button type="button" class="text-gray-500 hover:text-gray-900" @click="openEditBooking(booking)">Edit</button>
                            <button type="button" class="text-gray-500 hover:text-red-600" @click="toggleBooking(booking)">{{ booking.cancelled ? 'Restore' : 'Release' }}</button>
                            <button type="button" class="text-gray-400 hover:text-red-600" @click="removeBooking(booking)">Delete</button>
                        </p>
                    </li>
                </ul>
                <p v-else class="p-6 text-center text-sm text-gray-500">Nobody has booked yet.</p>
            </aside>
        </div>

        <!-- Add / edit an attendee -->
        <div v-if="editingBooking" class="fixed inset-0 bg-black/40 flex items-center justify-center p-4" @click.self="editingBooking = null">
            <form class="bg-white rounded p-5 w-full max-w-md space-y-4" @submit.prevent="saveBooking">
                <h2 class="font-bold text-lg">{{ editingBooking.id ? 'Edit attendee' : 'Add attendee' }}</h2>
                <p class="text-sm text-gray-500">{{ session.title }}</p>

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
