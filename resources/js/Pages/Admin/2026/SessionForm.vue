<script setup>
import { Link, useForm } from '@inertiajs/inertia-vue3';
import { computed, ref } from 'vue';
import Admin2026 from '../../../Layouts/Admin2026.vue';
import { knownKinds } from '../../../Sections/2026/kinds';
import RichText from './RichText.vue';

const props = defineProps({
    session: { type: Object, required: true },
    days: { type: Array, default: () => [] },
    people: { type: Array, default: () => [] },
    publicBase: { type: String, default: '' },
});

const editing = !!props.session.id;

// The kinds that carry a Romanian translation (see lang files). Free text still
// works — this just steers new entries toward one that is translated.

const form = useForm({
    programme_day_id: props.session.programme_day_id,
    starts_at: props.session.starts_at,
    ends_at: props.session.ends_at,
    kind: props.session.kind,
    type: props.session.type || 'slot',
    school: props.session.school,
    published: props.session.published,
    position: props.session.position,
    capacity: props.session.capacity,
    slug: props.session.slug,
    image: null,
    speakers: [...props.session.speakers],
    new_person: { first: '', last: '', image: null },
    en: { ...props.session.en },
    ro: { ...props.session.ro },
});

// A workshop or a tour is the exception: clickable, with a picture and a form.
const isException = computed(() => form.type === 'workshop' || form.type === 'tour');
const trainerWord = computed(() => (form.type === 'tour' ? 'Guide' : 'Trainer'));
const noSpeakers = computed(() => form.speakers.length === 0 && !form.new_person.first);

const preview = ref(props.session.image);

function pickImage(event) {
    const file = event.target.files[0];
    form.image = file ?? null;
    preview.value = file ? URL.createObjectURL(file) : props.session.image;
}

// A trainer added by name is a person of their own, so they can carry a photo.
const trainerPreview = ref(null);

function pickTrainerImage(event) {
    const file = event.target.files[0];
    form.new_person.image = file ?? null;
    trainerPreview.value = file ? URL.createObjectURL(file) : null;
}

function submit() {
    // POST either way (multipart carries the picture); the update route is POST.
    form.post(editing ? `/dashboard/programme/sessions/${props.session.id}` : '/dashboard/programme/sessions', {
        forceFormData: true,
    });
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
                        <label class="block text-sm font-medium mb-1">Type</label>
                        <div class="flex flex-wrap gap-2">
                            <label
                                v-for="opt in [
                                    { v: 'slot', label: 'Programme slot' },
                                    { v: 'workshop', label: 'Workshop' },
                                    { v: 'tour', label: 'Guided tour' },
                                ]"
                                :key="opt.v"
                                :class="[
                                    'cursor-pointer rounded border px-3 py-1.5 text-sm',
                                    form.type === opt.v ? 'border-red-500 bg-red-50 text-red-700' : 'border-gray-300 text-gray-600',
                                ]"
                            >
                                <input v-model="form.type" type="radio" :value="opt.v" class="sr-only" />
                                {{ opt.label }}
                            </label>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            A slot is a plain item you turn up to. A workshop or tour opens a details panel and takes sign-ups.
                        </p>
                    </div>

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
                        <input v-model="form.kind" list="session-kinds" type="text" placeholder="Talk, Conversation, Workshop 3…" class="w-full rounded border-gray-300 text-sm" />
                        <datalist id="session-kinds">
                            <option v-for="k in knownKinds" :key="k" :value="k" />
                        </datalist>
                        <p class="mt-1 text-xs text-gray-500">Pick a known one so it shows in Romanian too; a new one falls back to English.</p>
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
                            <label class="block text-sm font-medium mb-1">Subtitle <span class="text-gray-400">tagline</span></label>
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
                            <p class="mt-1 text-xs text-gray-500">Shown only when nobody is attached.</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Description
                            <span v-if="isException" class="text-gray-400">— shown in the details panel</span>
                        </label>
                        <RichText v-model="form[locale].description" />
                        <p class="mt-1 text-xs text-gray-500">Paragraphs, and <strong>bold</strong> · <em>italic</em> · <u>underline</u>.</p>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <!-- The picture, capacity and address only mean something for a
                     workshop or a tour, so they appear with one. -->
                <div v-if="isException" class="bg-white rounded border border-gray-200 p-5 space-y-4">
                    <p class="text-sm font-medium">{{ form.type === 'tour' ? 'Guided tour' : 'Workshop' }} details</p>

                    <div>
                        <label class="block text-sm font-medium mb-2">Picture</label>
                        <img v-if="preview" :src="preview" alt="" class="w-full rounded bg-gray-100 object-cover aspect-[3/2]" />
                        <div v-else class="w-full rounded bg-gray-100 aspect-[3/2]"></div>
                        <input type="file" accept="image/*" class="mt-3 w-full text-sm" @change="pickImage" />
                        <p v-if="form.errors.image" class="mt-1 text-sm text-red-600">{{ form.errors.image }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Capacity <span class="text-gray-400">max attendees</span></label>
                        <input v-model="form.capacity" type="number" min="1" class="w-full rounded border-gray-300 text-sm" />
                        <p v-if="session.taken" class="mt-1 text-xs text-gray-500">{{ session.taken }} already booked.</p>
                        <p v-if="form.errors.capacity" class="mt-1 text-sm text-red-600">{{ form.errors.capacity }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">URL slug</label>
                        <input v-model="form.slug" type="text" placeholder="from the title" class="w-full rounded border-gray-300 text-sm" />
                        <p v-if="form.slug" class="mt-1 text-xs text-gray-500 break-all">{{ publicBase }}/sessions/{{ form.slug }}</p>
                        <p v-if="form.errors.slug" class="mt-1 text-sm text-red-600">{{ form.errors.slug }}</p>
                    </div>
                </div>

                <div class="bg-white rounded border border-gray-200 p-5">
                    <p class="text-sm font-medium mb-2">{{ isException ? `${trainerWord}s / speakers` : 'Speakers' }}</p>
                    <div class="max-h-56 overflow-y-auto space-y-1 pr-1">
                        <label v-for="person in people" :key="person.id" class="flex items-center gap-2 text-sm">
                            <input v-model="form.speakers" type="checkbox" :value="person.id" class="rounded border-gray-300 text-red-600" />
                            {{ person.name }}
                            <span v-if="!person.onGrid" class="rounded bg-gray-100 px-1.5 text-xs text-gray-500">not on grid</span>
                        </label>
                    </div>

                    <!-- Add someone who leads this but is not a listed speaker:
                         made as a hidden person, attached here. -->
                    <div class="mt-3 border-t border-gray-100 pt-3">
                        <p class="text-xs font-medium text-gray-600 mb-1">Add a {{ trainerWord.toLowerCase() }} not on the speakers list</p>
                        <div class="grid grid-cols-2 gap-2">
                            <input v-model="form.new_person.first" type="text" placeholder="First name" class="rounded border-gray-300 text-sm" />
                            <input v-model="form.new_person.last" type="text" placeholder="Last name" class="rounded border-gray-300 text-sm" />
                        </div>
                        <p v-if="form.errors['new_person.first'] || form.errors['new_person.last']" class="mt-1 text-sm text-red-600">
                            Both names are needed.
                        </p>

                        <!-- The picture the details panel shows for them, since a
                             person added here has no profile of their own. -->
                        <div v-if="form.new_person.first" class="mt-2 flex items-center gap-3">
                            <img v-if="trainerPreview" :src="trainerPreview" alt="" class="h-12 w-12 rounded object-cover bg-gray-100" />
                            <span v-else class="h-12 w-12 rounded bg-gray-100"></span>
                            <label class="text-sm text-gray-600">
                                <span class="cursor-pointer underline">{{ trainerPreview ? 'Change photo' : 'Add a photo' }}</span>
                                <input type="file" accept="image/*" class="sr-only" @change="pickTrainerImage" />
                            </label>
                        </div>
                        <p v-if="form.errors['new_person.image']" class="mt-1 text-sm text-red-600">{{ form.errors['new_person.image'] }}</p>

                        <p class="mt-1 text-xs text-gray-500">They will not appear on the public speakers grid.</p>
                    </div>
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
