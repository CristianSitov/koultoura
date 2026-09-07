<script setup>
import { Link, useForm } from '@inertiajs/inertia-vue3';
import { computed } from 'vue';
import Admin2026 from '../../../Layouts/Admin2026.vue';

const props = defineProps({
    session: { type: Object, required: true },
    days: { type: Array, default: () => [] },
    people: { type: Array, default: () => [] },
    publicBase: { type: String, default: '' },
});

const editing = !!props.session.id;

const form = useForm({
    programme_day_id: props.session.programme_day_id,
    starts_at: props.session.starts_at,
    ends_at: props.session.ends_at,
    kind: props.session.kind,
    school: props.session.school,
    published: props.session.published,
    position: props.session.position,
    bookable: props.session.bookable,
    capacity: props.session.capacity,
    slug: props.session.slug,
    speakers: [...props.session.speakers],
    en: { ...props.session.en },
    ro: { ...props.session.ro },
});

// Nobody named means the line under the title is the audience instead.
const noSpeakers = computed(() => form.speakers.length === 0);

function submit() {
    editing
        ? form.put(`/dashboard/programme/sessions/${props.session.id}`)
        : form.post('/dashboard/programme/sessions');
}
</script>

<template>
    <Admin2026 :title="editing ? session.en.title || 'Session' : 'New session'" :public-base="publicBase">
        <template #actions>
            <Link href="/dashboard/programme" class="text-sm text-gray-500 hover:text-gray-900">← Programme</Link>
        </template>

        <form class="grid gap-6 lg:grid-cols-3" @submit.prevent="submit">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded border border-gray-200 p-5 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium mb-1">Day</label>
                        <select v-model="form.programme_day_id" class="w-full rounded border-gray-300 text-sm">
                            <option v-for="day in days" :key="day.id" :value="day.id">{{ day.label }}</option>
                        </select>
                        <p v-if="form.errors.programme_day_id" class="mt-1 text-sm text-red-600">{{ form.errors.programme_day_id }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Starts</label>
                        <input v-model="form.starts_at" type="time" class="w-full rounded border-gray-300 text-sm" />
                        <p v-if="form.errors.starts_at" class="mt-1 text-sm text-red-600">{{ form.errors.starts_at }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Ends <span class="text-gray-400">optional</span></label>
                        <input v-model="form.ends_at" type="time" class="w-full rounded border-gray-300 text-sm" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Kind</label>
                        <input v-model="form.kind" type="text" placeholder="Talk, Conversation, Workshop 3…" class="w-full rounded border-gray-300 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Order within the day</label>
                        <input v-model="form.position" type="number" min="0" class="w-full rounded border-gray-300 text-sm" />
                        <p class="mt-1 text-xs text-gray-500">Only breaks ties — the list sorts by time first.</p>
                    </div>

                    <label class="flex items-center gap-2 text-sm">
                        <input v-model="form.school" type="checkbox" class="rounded border-gray-300 text-red-600" />
                        Part of the Heritage School
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input v-model="form.published" type="checkbox" class="rounded border-gray-300 text-red-600" />
                        Published on the public page
                    </label>
                </div>

                <div v-for="locale in ['en', 'ro']" :key="locale" class="bg-white rounded border border-gray-200 p-5 space-y-4">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                        {{ locale === 'en' ? 'English' : 'Romanian' }}
                        <span v-if="locale === 'ro'" class="ml-2 font-normal normal-case tracking-normal text-gray-400">
                            left empty, the page falls back to English
                        </span>
                    </h2>

                    <div>
                        <label class="block text-sm font-medium mb-1">Title</label>
                        <input v-model="form[locale].title" type="text" class="w-full rounded border-gray-300 text-sm" />
                        <p v-if="form.errors[`${locale}.title`]" class="mt-1 text-sm text-red-600">{{ form.errors[`${locale}.title`] }}</p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium mb-1">Subtitle</label>
                            <input v-model="form[locale].subtitle" type="text" class="w-full rounded border-gray-300 text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Audience</label>
                            <input
                                v-model="form[locale].audience"
                                type="text"
                                placeholder="Children, 8–12"
                                :disabled="!noSpeakers"
                                class="w-full rounded border-gray-300 text-sm disabled:bg-gray-100 disabled:text-gray-400"
                            />
                            <p class="mt-1 text-xs text-gray-500">Shown only when no speaker is attached.</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Description</label>
                        <textarea v-model="form[locale].description" rows="5" class="w-full rounded border-gray-300 text-sm"></textarea>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded border border-gray-200 p-5">
                    <p class="text-sm font-medium mb-2">Speakers</p>
                    <div class="max-h-64 overflow-y-auto space-y-1 pr-1">
                        <label v-for="person in people" :key="person.id" class="flex items-center gap-2 text-sm">
                            <input v-model="form.speakers" type="checkbox" :value="person.id" class="rounded border-gray-300 text-red-600" />
                            {{ person.name }}
                        </label>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Order follows this list.</p>
                </div>

                <div class="bg-white rounded border border-gray-200 p-5 space-y-3">
                    <label class="flex items-center gap-2 text-sm font-medium">
                        <input v-model="form.bookable" type="checkbox" class="rounded border-gray-300 text-red-600" />
                        Places are limited
                    </label>
                    <p class="text-xs text-gray-500">
                        Gives this session a sign-up form of its own. The day registration does not cover it.
                    </p>

                    <template v-if="form.bookable">
                        <div>
                            <label class="block text-sm font-medium mb-1">Places</label>
                            <input v-model="form.capacity" type="number" min="1" class="w-full rounded border-gray-300 text-sm" />
                            <p v-if="session.taken" class="mt-1 text-xs text-gray-500">{{ session.taken }} already taken.</p>
                            <p v-if="form.errors.capacity" class="mt-1 text-sm text-red-600">{{ form.errors.capacity }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">URL slug</label>
                            <input v-model="form.slug" type="text" placeholder="from the title" class="w-full rounded border-gray-300 text-sm" />
                            <p v-if="form.slug" class="mt-1 text-xs text-gray-500 break-all">{{ publicBase }}/sessions/{{ form.slug }}</p>
                            <p v-if="form.errors.slug" class="mt-1 text-sm text-red-600">{{ form.errors.slug }}</p>
                        </div>
                    </template>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full rounded bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50"
                >{{ editing ? 'Save' : 'Add session' }}</button>
            </div>
        </form>
    </Admin2026>
</template>
