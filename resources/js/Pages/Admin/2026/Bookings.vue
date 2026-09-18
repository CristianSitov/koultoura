<script setup>
import { Link } from '@inertiajs/inertia-vue3';
import Admin2026 from '../../../Layouts/Admin2026.vue';

defineProps({
    sessions: { type: Array, default: () => [] },
    publicBase: { type: String, default: '' },
});
</script>

<template>
    <Admin2026 title="Workshops" :public-base="publicBase">
        <p v-if="!sessions.length" class="bg-white rounded border border-gray-200 p-8 text-center text-gray-500">
            No workshop or tour yet. Open one in the programme and set its type to Workshop or Guided tour.
        </p>

        <Link
            v-for="session in sessions"
            :key="session.id"
            :href="`/dashboard/bookings/workshop/${session.id}/edit`"
            class="flex flex-wrap items-start gap-4 bg-white rounded border border-gray-200 p-5 mb-4 hover:border-gray-300"
        >
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
                <p class="text-xs text-gray-500 mt-1">{{ session.bookings.length }} {{ session.bookings.length === 1 ? 'attendee' : 'attendees' }}</p>
            </div>

            <div class="text-right">
                <p class="text-2xl font-bold" :class="session.taken >= session.capacity ? 'text-red-600' : ''">
                    {{ session.taken }}<span class="text-base font-normal text-gray-500">/{{ session.capacity }}</span>
                </p>
                <span class="text-sm text-gray-500">Edit workshop →</span>
            </div>
        </Link>
    </Admin2026>
</template>
