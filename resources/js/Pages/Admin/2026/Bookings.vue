<script setup>
import { useForm } from '@inertiajs/inertia-vue3';
import Admin2026 from '../../../Layouts/Admin2026.vue';

defineProps({
    sessions: { type: Array, default: () => [] },
    publicBase: { type: String, default: '' },
});

const action = useForm({});

function toggleBooking(booking) {
    const question = booking.cancelled
        ? `Give ${booking.name} their place back?`
        : `Release ${booking.name}’s place? They keep the row; the place goes back to the pool.`;

    if (confirm(question)) {
        action.post(`/dashboard/2026/bookings/${booking.id}/cancel`, { preserveScroll: true });
    }
}
</script>

<template>
    <Admin2026 title="Bookings" :public-base="publicBase">
        <p v-if="!sessions.length" class="bg-white rounded border border-gray-200 p-8 text-center text-gray-500">
            No session has limited places yet. Open one in the programme and tick “Places are limited”.
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

                <div class="text-right">
                    <p class="text-2xl font-bold" :class="session.taken >= session.capacity ? 'text-red-600' : ''">
                        {{ session.taken }}<span class="text-base font-normal text-gray-500">/{{ session.capacity }}</span>
                    </p>
                    <a :href="`${publicBase}/sessions/${session.slug}`" target="_blank" class="text-xs text-gray-500 hover:text-gray-900">
                        booking form ↗
                    </a>
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
                        <td class="px-5 py-3 text-right">
                            <button type="button" class="text-gray-500 hover:text-red-600" @click="toggleBooking(booking)">
                                {{ booking.cancelled ? 'Restore' : 'Release place' }}
                            </button>
                        </td>
                    </tr>
                    <tr v-if="!session.bookings.length">
                        <td colspan="3" class="px-5 py-6 text-center text-gray-500">Nobody has booked yet.</td>
                    </tr>
                </tbody>
            </table>
        </section>
    </Admin2026>
</template>
