<script setup>
import { Link, useForm } from '@inertiajs/inertia-vue3';
import Admin2026 from '../../../Layouts/Admin2026.vue';

const props = defineProps({
    registrations: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
    counts: { type: Object, required: true },
    publicBase: { type: String, default: '' },
});

const action = useForm({});

const dayLabels = { 1: '07', 2: '08', 3: '09', 4: '10' };

function filterUrl(day, status) {
    const params = new URLSearchParams();
    if (day) params.set('day', day);
    if (status && status !== 'all') params.set('status', status);
    const query = params.toString();

    return `/dashboard/2026/registrations${query ? `?${query}` : ''}`;
}

function resend(registration) {
    action.post(`/dashboard/2026/registrations/${registration.id}/resend`, { preserveScroll: true });
}

function confirmByHand(registration) {
    if (confirm(`Mark ${registration.name} as confirmed without them clicking the email?`)) {
        action.post(`/dashboard/2026/registrations/${registration.id}/confirm`, { preserveScroll: true });
    }
}
</script>

<template>
    <Admin2026 title="Registrations" :public-base="publicBase">
        <template #actions>
            <a href="/dashboard/2026/registrations.csv" class="rounded border border-gray-300 px-4 py-2 text-sm hover:border-red-400">
                Download CSV
            </a>
        </template>

        <!-- Headcounts first: it is the question this page is opened to answer. -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
            <div class="bg-white rounded border border-gray-200 p-4">
                <p class="text-xs uppercase tracking-wide text-gray-500">Registered</p>
                <p class="text-2xl font-bold">{{ counts.total }}</p>
                <p class="text-sm text-gray-500">{{ counts.confirmed }} confirmed</p>
            </div>
            <div v-for="(row, day) in counts.perDay" :key="day" class="bg-white rounded border border-gray-200 p-4">
                <p class="text-xs uppercase tracking-wide text-gray-500">{{ row.date }} October</p>
                <p class="text-2xl font-bold">{{ row.all }}</p>
                <p class="text-sm text-gray-500">{{ row.confirmed }} confirmed</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 mb-4 text-sm">
            <Link
                v-for="option in [{ label: 'All days', day: null }, ...Object.entries(dayLabels).map(([d, l]) => ({ label: `${l} Oct`, day: Number(d) }))]"
                :key="option.label"
                :href="filterUrl(option.day, filters.status)"
                :class="[
                    'rounded px-3 py-1.5 border',
                    filters.day === option.day ? 'border-red-500 text-red-700 bg-red-50' : 'border-gray-300 text-gray-600',
                ]"
            >{{ option.label }}</Link>

            <span class="mx-2 text-gray-300">|</span>

            <Link
                v-for="status in ['all', 'confirmed', 'pending']"
                :key="status"
                :href="filterUrl(filters.day, status)"
                :class="[
                    'rounded px-3 py-1.5 border capitalize',
                    filters.status === status ? 'border-red-500 text-red-700 bg-red-50' : 'border-gray-300 text-gray-600',
                ]"
            >{{ status }}</Link>

            <span class="ml-auto text-gray-500">{{ registrations.length }} shown · {{ counts.workshopInterest }} interested in a workshop</span>
        </div>

        <div class="bg-white rounded border border-gray-200 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="px-4 py-3 font-medium">Days</th>
                        <th class="px-4 py-3 font-medium">Organisation</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Registered</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="row in registrations" :key="row.id" class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <p class="font-medium">
                                {{ row.name }}
                                <span v-if="row.workshop_interest" class="ml-2 rounded bg-red-50 px-1.5 py-0.5 text-xs text-red-700">workshop</span>
                            </p>
                            <p class="text-xs text-gray-500">{{ row.email }} · {{ row.locale.toUpperCase() }}</p>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span v-for="day in row.days" :key="day" class="mr-1 rounded bg-gray-100 px-1.5 py-0.5 text-xs">{{ dayLabels[day] }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ row.organisation || '—' }}
                            <span v-if="row.country" class="text-gray-400">· {{ row.country }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span
                                :class="[
                                    'rounded px-2 py-0.5 text-xs',
                                    row.confirmed ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800',
                                ]"
                            >{{ row.confirmed ? 'confirmed' : 'pending' }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ row.created.slice(0, 16) }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <template v-if="!row.confirmed">
                                <button type="button" class="text-gray-500 hover:text-gray-900" @click="resend(row)">
                                    Resend<span v-if="row.sent_count > 1" class="text-gray-400"> ({{ row.sent_count }})</span>
                                </button>
                                <button type="button" class="ml-3 text-gray-500 hover:text-green-700" @click="confirmByHand(row)">Confirm</button>
                            </template>
                            <span v-else class="text-gray-300">—</span>
                        </td>
                    </tr>
                    <tr v-if="!registrations.length">
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">Nobody matches this filter.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="mt-4 text-sm text-gray-500">
            Contributions so far: <strong>{{ counts.contributions.total.toLocaleString('ro-RO') }} RON</strong>
            from {{ counts.contributions.count }} payment(s). Giving is optional and separate from registering.
        </p>
    </Admin2026>
</template>
