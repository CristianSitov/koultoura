<script setup>
import { Link, useForm } from '@inertiajs/inertia-vue3';
import Admin2026 from '../../../Layouts/Admin2026.vue';

defineProps({
    speakers: { type: Array, default: () => [] },
    publicBase: { type: String, default: '' },
});

const form = useForm({});

function remove(speaker) {
    if (confirm(`Remove ${speaker.name}? Their sessions stay, without them attached.`)) {
        form.delete(`/dashboard/2026/speakers/${speaker.id}`);
    }
}
</script>

<template>
    <Admin2026 title="Speakers" :public-base="publicBase">
        <template #actions>
            <Link href="/dashboard/2026/speakers/new" class="rounded bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                Add speaker
            </Link>
        </template>

        <div class="bg-white rounded border border-gray-200 overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="px-4 py-3 font-medium">Role</th>
                        <th class="px-4 py-3 font-medium">Text</th>
                        <th class="px-4 py-3 font-medium">Sessions</th>
                        <th class="px-4 py-3 font-medium">Order</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="speaker in speakers" :key="speaker.id" class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <img v-if="speaker.avatar" :src="speaker.avatar" alt="" class="h-9 w-9 rounded object-cover bg-gray-100" />
                                <span v-else class="h-9 w-9 rounded bg-gray-100"></span>
                                <div>
                                    <Link :href="`/dashboard/2026/speakers/${speaker.id}`" class="font-semibold hover:text-red-600">{{ speaker.name }}</Link>
                                    <p class="text-xs text-gray-500">{{ speaker.institution }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ speaker.role }}</td>
                        <td class="px-4 py-3">
                            <!-- Which languages have a biography, so a gap is
                                 visible without opening every guest. -->
                            <span
                                v-for="locale in ['en', 'ro']"
                                :key="locale"
                                :class="[
                                    'mr-1 rounded px-1.5 py-0.5 text-xs uppercase',
                                    speaker.locales.includes(locale) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-400',
                                ]"
                            >{{ locale }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ speaker.sessions || '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ speaker.position }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a :href="`${publicBase}/guests/${speaker.slug}`" target="_blank" class="text-gray-500 hover:text-gray-900">View ↗</a>
                            <button type="button" class="ml-3 text-gray-500 hover:text-red-600" @click="remove(speaker)">Remove</button>
                        </td>
                    </tr>
                    <tr v-if="!speakers.length">
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">No speakers yet.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="mt-4 text-sm text-gray-500">
            Guests imported from Drive are updated by <code class="text-xs">php artisan guests:sync</code>. It leaves an edited
            description alone, so anything rewritten here survives the next sync.
        </p>
    </Admin2026>
</template>
