<script setup>
import { Link, useForm } from '@inertiajs/inertia-vue3';
import { ref } from 'vue';
import Admin2026 from '../../../Layouts/Admin2026.vue';

const props = defineProps({
    days: { type: Array, default: () => [] },
    themes: { type: Array, default: () => [] },
    publicBase: { type: String, default: '' },
});

const editingDay = ref(null);
const editingTheme = ref(null);

const dayForm = useForm({ date: '', theme_id: null, position: 0, published: false, name: '', name_ro: '' });
const themeForm = useForm({ numeral: '', position: 0, title: '', title_ro: '', description: '', description_ro: '' });
const toggle = useForm({});

function openDay(day) {
    editingDay.value = day?.id ?? 'new';
    dayForm.defaults({
        date: day?.date ?? '',
        theme_id: day?.theme_id ?? null,
        position: day?.position ?? props.days.length + 1,
        published: day?.published ?? false,
        name: day?.name ?? '',
        name_ro: day?.name_ro ?? '',
    });
    dayForm.reset();
}

function saveDay() {
    const done = { onSuccess: () => (editingDay.value = null) };

    editingDay.value === 'new'
        ? dayForm.post('/dashboard/2026/programme/days', done)
        : dayForm.put(`/dashboard/2026/programme/days/${editingDay.value}`, done);
}

function removeDay(day) {
    if (confirm(`Remove ${day.date}? Its ${day.sessions.length} session(s) go with it.`)) {
        toggle.delete(`/dashboard/2026/programme/days/${day.id}`);
    }
}

function openTheme(theme) {
    editingTheme.value = theme?.id ?? 'new';
    themeForm.defaults({
        numeral: theme?.numeral ?? '',
        position: theme?.position ?? props.themes.length + 1,
        title: theme?.title ?? '',
        title_ro: theme?.title_ro ?? '',
        description: theme?.description ?? '',
        description_ro: theme?.description_ro ?? '',
    });
    themeForm.reset();
}

function saveTheme() {
    const done = { onSuccess: () => (editingTheme.value = null) };

    editingTheme.value === 'new'
        ? themeForm.post('/dashboard/2026/programme/themes', done)
        : themeForm.put(`/dashboard/2026/programme/themes/${editingTheme.value}`, done);
}
</script>

<template>
    <Admin2026 title="Programme" :public-base="publicBase">
        <template #actions>
            <button type="button" class="rounded bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700" @click="openDay(null)">
                Add day
            </button>
        </template>

        <!-- Themes first: a day points at one, so they have to exist before the
             days can be arranged. -->
        <section class="mb-8">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Themes</h2>
                <button type="button" class="text-sm text-gray-500 hover:text-gray-900" @click="openTheme(null)">Add theme</button>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
                <button
                    v-for="theme in themes"
                    :key="theme.id"
                    type="button"
                    class="text-left bg-white rounded border border-gray-200 p-4 hover:border-red-300"
                    @click="openTheme(theme)"
                >
                    <p class="text-xs font-bold text-red-600">{{ theme.numeral }}</p>
                    <p class="font-semibold text-sm mt-1">{{ theme.title }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ theme.title_ro || 'no Romanian title' }}</p>
                </button>
            </div>
        </section>

        <section class="space-y-6">
            <article v-for="day in days" :key="day.id" class="bg-white rounded border border-gray-200">
                <header class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4">
                    <div>
                        <p class="font-bold">
                            {{ day.date }}
                            <span class="ml-2 font-normal text-gray-500">{{ day.name }}</span>
                            <span
                                :class="[
                                    'ml-3 rounded px-2 py-0.5 text-xs',
                                    day.published ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800',
                                ]"
                            >{{ day.published ? 'published' : 'draft' }}</span>
                        </p>
                        <p class="text-sm text-gray-500 mt-0.5">{{ day.theme || 'no theme yet' }}</p>
                    </div>

                    <div class="flex items-center gap-3 text-sm">
                        <button type="button" class="text-gray-500 hover:text-gray-900" @click="openDay(day)">Edit day</button>
                        <button type="button" class="text-gray-500 hover:text-red-600" @click="removeDay(day)">Remove</button>
                        <Link
                            :href="`/dashboard/2026/programme/sessions/new?day=${day.id}`"
                            class="rounded border border-gray-300 px-3 py-1.5 hover:border-red-400"
                        >Add session</Link>
                    </div>
                </header>

                <table class="min-w-full text-sm">
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="session in day.sessions" :key="session.id" class="hover:bg-gray-50">
                            <td class="px-5 py-3 w-20 font-mono text-gray-600">{{ session.time }}</td>
                            <td class="px-2 py-3 w-32 text-gray-500">{{ session.kind || '—' }}</td>
                            <td class="px-2 py-3">
                                <Link :href="`/dashboard/2026/programme/sessions/${session.id}`" class="font-medium hover:text-red-600">
                                    {{ session.title }}
                                </Link>
                                <span v-if="session.school" class="ml-2 rounded bg-red-50 px-1.5 py-0.5 text-xs text-red-700">School</span>
                                <span v-if="session.bookable" class="ml-2 rounded bg-blue-50 px-1.5 py-0.5 text-xs text-blue-700">
                                    {{ session.taken }}/{{ session.capacity }} booked
                                </span>
                                <p class="text-xs text-gray-500">{{ session.who }}</p>
                            </td>
                            <td class="px-2 py-3 w-28 text-right">
                                <!-- One click, because publishing is the thing
                                     done most often and least worth a form. -->
                                <Link
                                    :href="`/dashboard/2026/programme/sessions/${session.id}/published`"
                                    method="put"
                                    as="button"
                                    type="button"
                                    :class="[
                                        'rounded px-2 py-0.5 text-xs',
                                        session.published ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800',
                                    ]"
                                >{{ session.published ? 'published' : 'draft' }}</Link>
                            </td>
                        </tr>
                        <tr v-if="!day.sessions.length">
                            <td colspan="4" class="px-5 py-6 text-center text-gray-500">Nothing scheduled yet.</td>
                        </tr>
                    </tbody>
                </table>
            </article>
        </section>

        <!-- Day editor -->
        <div v-if="editingDay" class="fixed inset-0 bg-black/40 flex items-center justify-center p-4" @click.self="editingDay = null">
            <form class="bg-white rounded w-full max-w-lg p-6 space-y-4" @submit.prevent="saveDay">
                <h2 class="font-bold text-lg">{{ editingDay === 'new' ? 'Add day' : 'Edit day' }}</h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium mb-1">Date</label>
                        <input v-model="dayForm.date" type="date" class="w-full rounded border-gray-300 text-sm" />
                        <p v-if="dayForm.errors.date" class="mt-1 text-sm text-red-600">{{ dayForm.errors.date }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Theme</label>
                        <select v-model="dayForm.theme_id" class="w-full rounded border-gray-300 text-sm">
                            <option :value="null">— none —</option>
                            <option v-for="theme in themes" :key="theme.id" :value="theme.id">{{ theme.numeral }} · {{ theme.title }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Name (EN)</label>
                        <input v-model="dayForm.name" type="text" placeholder="Wednesday" class="w-full rounded border-gray-300 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Name (RO)</label>
                        <input v-model="dayForm.name_ro" type="text" placeholder="Miercuri" class="w-full rounded border-gray-300 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Order</label>
                        <input v-model="dayForm.position" type="number" min="0" class="w-full rounded border-gray-300 text-sm" />
                    </div>
                    <label class="flex items-center gap-2 text-sm mt-6">
                        <input v-model="dayForm.published" type="checkbox" class="rounded border-gray-300 text-red-600" />
                        Published
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" class="text-sm text-gray-500" @click="editingDay = null">Cancel</button>
                    <button type="submit" :disabled="dayForm.processing" class="rounded bg-red-600 px-4 py-2 text-sm font-semibold text-white">Save</button>
                </div>
            </form>
        </div>

        <!-- Theme editor -->
        <div v-if="editingTheme" class="fixed inset-0 bg-black/40 flex items-center justify-center p-4" @click.self="editingTheme = null">
            <form class="bg-white rounded w-full max-w-2xl p-6 space-y-4 max-h-[90vh] overflow-y-auto" @submit.prevent="saveTheme">
                <h2 class="font-bold text-lg">{{ editingTheme === 'new' ? 'Add theme' : 'Edit theme' }}</h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium mb-1">Numeral</label>
                        <input v-model="themeForm.numeral" type="text" placeholder="I" class="w-full rounded border-gray-300 text-sm" />
                        <p v-if="themeForm.errors.numeral" class="mt-1 text-sm text-red-600">{{ themeForm.errors.numeral }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Order</label>
                        <input v-model="themeForm.position" type="number" min="0" class="w-full rounded border-gray-300 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Title (EN)</label>
                        <input v-model="themeForm.title" type="text" class="w-full rounded border-gray-300 text-sm" />
                        <p v-if="themeForm.errors.title" class="mt-1 text-sm text-red-600">{{ themeForm.errors.title }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Title (RO)</label>
                        <input v-model="themeForm.title_ro" type="text" class="w-full rounded border-gray-300 text-sm" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Description (EN)</label>
                    <textarea v-model="themeForm.description" rows="4" class="w-full rounded border-gray-300 text-sm"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Description (RO)</label>
                    <textarea v-model="themeForm.description_ro" rows="4" class="w-full rounded border-gray-300 text-sm"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" class="text-sm text-gray-500" @click="editingTheme = null">Cancel</button>
                    <button type="submit" :disabled="themeForm.processing" class="rounded bg-red-600 px-4 py-2 text-sm font-semibold text-white">Save</button>
                </div>
            </form>
        </div>
    </Admin2026>
</template>
