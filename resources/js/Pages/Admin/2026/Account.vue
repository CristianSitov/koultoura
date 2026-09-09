<script setup>
import { usePage } from '@inertiajs/inertia-vue3';
import { computed } from 'vue';
import Admin2026 from '../../../Layouts/Admin2026.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';

defineProps({
    publicBase: { type: String, default: '' },
});

const page = usePage();
const user = computed(() => page.props.value.auth?.user);

/*
 * Jetstream's own profile page exists, but it wears the 2022 public layout —
 * whole site chrome, language switcher and all — which is a strange place to
 * land from the backoffice. The form itself is the part worth reusing, so it
 * is dropped into this layout instead of the page being rebuilt.
 */
const canUpdatePassword = computed(() => page.props.value.jetstream?.canUpdatePassword);
</script>

<template>
    <Admin2026 title="Account" :public-base="publicBase">
        <div v-if="user" class="bg-white rounded border border-gray-200 p-5 mb-6">
            <p class="text-xs uppercase tracking-wide text-gray-500">Signed in as</p>
            <p class="text-lg font-semibold mt-1">{{ user.name }}</p>
            <p class="text-sm text-gray-500">{{ user.email }}</p>
        </div>

        <UpdatePasswordForm v-if="canUpdatePassword" />

        <p v-else class="text-sm text-gray-500">
            Password changes are switched off. Ask whoever runs the server.
        </p>
    </Admin2026>
</template>
