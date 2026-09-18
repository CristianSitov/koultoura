<script setup>
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/inertia-vue3';
import Admin2026 from '../../../Layouts/Admin2026.vue';

const props = defineProps({
    session: { type: Object, required: true },
    people: { type: Array, default: () => [] },
    publicBase: { type: String, default: '' },
});

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

// The server redirects back to the bookings list on success.
function submit() {
    form.post(`/dashboard/bookings/workshop/${props.session.id}`, { forceFormData: true });
}
</script>

<template>
    <Admin2026 :title="`Edit ${session.type}`" :public-base="publicBase">
        <template #actions>
            <Link href="/dashboard/bookings" class="text-sm text-gray-500 hover:text-gray-900">← Bookings</Link>
        </template>

        <form class="max-w-lg space-y-5 bg-white rounded border border-gray-200 p-5" @submit.prevent="submit">
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
    </Admin2026>
</template>
