<script setup>
import { Link, useForm } from '@inertiajs/inertia-vue3';
import { ref } from 'vue';
import Admin2026 from '../../../Layouts/Admin2026.vue';

const props = defineProps({
    speaker: { type: Object, required: true },
    publicBase: { type: String, default: '' },
});

const editing = !!props.speaker.id;

const form = useForm({
    full_name: props.speaker.full_name,
    slug: props.speaker.slug,
    position: props.speaker.position,
    en: { ...props.speaker.en },
    ro: { ...props.speaker.ro },
    photo: null,
});

const preview = ref(props.speaker.avatar);

function pickPhoto(event) {
    const file = event.target.files[0];
    form.photo = file ?? null;
    preview.value = file ? URL.createObjectURL(file) : props.speaker.avatar;
}

function submit() {
    // POST either way: a multipart body is only parsed on POST, and this form
    // can carry a photo.
    form.post(editing ? `/dashboard/speakers/${props.speaker.id}` : '/dashboard/speakers');
}
</script>

<template>
    <Admin2026 :title="editing ? speaker.full_name : 'New speaker'" :public-base="publicBase">
        <template #actions>
            <Link href="/dashboard/speakers" class="text-sm text-gray-500 hover:text-gray-900">← All speakers</Link>
        </template>

        <form class="grid gap-6 lg:grid-cols-3" @submit.prevent="submit">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded border border-gray-200 p-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Name</label>
                        <input v-model="form.full_name" type="text" class="w-full rounded border-gray-300 text-sm" />
                        <p v-if="form.errors.full_name" class="mt-1 text-sm text-red-600">{{ form.errors.full_name }}</p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium mb-1">URL slug</label>
                            <input v-model="form.slug" type="text" placeholder="from the name" class="w-full rounded border-gray-300 text-sm" />
                            <p class="mt-1 text-xs text-gray-500">
                                Their profile address. Changing it breaks links already shared.
                            </p>
                            <p v-if="form.errors.slug" class="mt-1 text-sm text-red-600">{{ form.errors.slug }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Order</label>
                            <input v-model="form.position" type="number" min="0" class="w-full rounded border-gray-300 text-sm" />
                            <p class="mt-1 text-xs text-gray-500">Lower first on the public grid.</p>
                        </div>
                    </div>
                </div>

                <div v-for="locale in ['en', 'ro']" :key="locale" class="bg-white rounded border border-gray-200 p-5 space-y-4">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                        {{ locale === 'en' ? 'English' : 'Romanian' }}
                        <span v-if="locale === 'ro'" class="ml-2 font-normal normal-case tracking-normal text-gray-400">
                            left empty, the page falls back to English
                        </span>
                    </h2>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium mb-1">Role</label>
                            <input v-model="form[locale].role" type="text" class="w-full rounded border-gray-300 text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Institution</label>
                            <input v-model="form[locale].institution" type="text" class="w-full rounded border-gray-300 text-sm" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Biography</label>
                        <textarea v-model="form[locale].description" rows="7" class="w-full rounded border-gray-300 text-sm"></textarea>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded border border-gray-200 p-5">
                    <label class="block text-sm font-medium mb-2">Portrait</label>
                    <img v-if="preview" :src="preview" alt="" class="w-full rounded bg-gray-100 object-cover aspect-[3/4]" />
                    <div v-else class="w-full rounded bg-gray-100 aspect-[3/4]"></div>
                    <input type="file" accept="image/*" class="mt-3 w-full text-sm" @change="pickPhoto" />
                    <p class="mt-2 text-xs text-gray-500">
                        Scaled down and saved as JPEG, the same as the Drive sync does. A new Drive photo replaces it.
                    </p>
                    <p v-if="form.errors.photo" class="mt-1 text-sm text-red-600">{{ form.errors.photo }}</p>
                </div>

                <div v-if="speaker.sessions.length" class="bg-white rounded border border-gray-200 p-5">
                    <p class="text-sm font-medium mb-2">In the programme</p>
                    <ul class="text-sm text-gray-600 list-disc pl-5 space-y-1">
                        <li v-for="(title, i) in speaker.sessions" :key="i">{{ title }}</li>
                    </ul>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full rounded bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50"
                >{{ editing ? 'Save' : 'Add speaker' }}</button>
            </div>
        </form>
    </Admin2026>
</template>
