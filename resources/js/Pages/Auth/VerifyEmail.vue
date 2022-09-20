<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/inertia-vue3';
import JetAuthenticationCard from '@/Components/AuthenticationCard.vue';
import JetAuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import JetButton from '@/Components/Button.vue';

const props = defineProps({
    status: String,
});

const form = useForm();

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <Head title="Email verification" />

    <JetAuthenticationCard width="small">
        <template #logo>
            <JetAuthenticationCardLogo title="Email verification" />
        </template>

        <div class="mb-4 text-sm text-gray-600" v-html="$t('Email verification explanation')"></div>

        <div v-if="verificationLinkSent" class="mb-4 font-medium text-sm text-green-600">
            {{ $t('Verification emaail resent') }}
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4 flex items-center justify-between">
                <JetButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    {{ $t('Resend verification email') }}
                </JetButton>

                <div>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="px-4 py-2 rounded-md font-semibold text-xs text-white uppercase tracking-widest bg-red-600 hover:bg-red-800 ml-2"
                    >
                        {{ $t('Cancel registration') }}
                    </Link>
                </div>
            </div>
        </form>
    </JetAuthenticationCard>
</template>
