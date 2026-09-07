<script setup>
import { Link } from '@inertiajs/inertia-vue3';
import Admin2026 from '../../../Layouts/Admin2026.vue';

const props = defineProps({
    stats: { type: Object, required: true },
    publicBase: { type: String, default: '' },
});

// The gap between drafted and published is the work still to do, so it is
// stated rather than left to be worked out from two numbers.
const draft = props.stats.sessions - props.stats.sessionsPublished;
</script>

<template>
    <Admin2026 title="Overview" :public-base="publicBase">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Link href="/dashboard/2026/speakers" class="block bg-white rounded border border-gray-200 p-5 hover:border-red-300">
                <p class="text-xs uppercase tracking-wide text-gray-500">Speakers</p>
                <p class="text-3xl font-bold mt-1">{{ stats.speakers }}</p>
            </Link>

            <Link href="/dashboard/2026/programme" class="block bg-white rounded border border-gray-200 p-5 hover:border-red-300">
                <p class="text-xs uppercase tracking-wide text-gray-500">Programme</p>
                <p class="text-3xl font-bold mt-1">{{ stats.sessions }}<span class="text-base font-normal text-gray-500"> sessions</span></p>
                <p class="text-sm text-gray-500 mt-1">
                    {{ stats.sessionsPublished }} published<span v-if="draft">, {{ draft }} still draft</span> · {{ stats.days }} days
                </p>
            </Link>

            <Link href="/dashboard/2026/registrations" class="block bg-white rounded border border-gray-200 p-5 hover:border-red-300">
                <p class="text-xs uppercase tracking-wide text-gray-500">Registrations</p>
                <p class="text-3xl font-bold mt-1">{{ stats.registrations }}</p>
                <p class="text-sm text-gray-500 mt-1">{{ stats.confirmed }} confirmed their address</p>
            </Link>

            <Link href="/dashboard/2026/bookings" class="block bg-white rounded border border-gray-200 p-5 hover:border-red-300">
                <p class="text-xs uppercase tracking-wide text-gray-500">Capped sessions</p>
                <p class="text-3xl font-bold mt-1">{{ stats.bookable }}</p>
                <p class="text-sm text-gray-500 mt-1">with a booking form of their own</p>
            </Link>

            <div class="bg-white rounded border border-gray-200 p-5">
                <p class="text-xs uppercase tracking-wide text-gray-500">Contributions</p>
                <p class="text-3xl font-bold mt-1">{{ stats.contributed.toLocaleString('ro-RO') }} <span class="text-base font-normal text-gray-500">RON</span></p>
                <p class="text-sm text-gray-500 mt-1">from {{ stats.contributions }} payment(s)</p>
            </div>
        </div>
    </Admin2026>
</template>
