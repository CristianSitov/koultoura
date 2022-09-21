<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import JetApplicationLogo from '@/Components/ApplicationLogo.vue'
import RegistrationConfirmation from '@/Sections/RegistrationConfirmation.vue';
</script>

<template>
    <AppLayout title="Registration Confirmation">
        <template #header>
            <JetApplicationLogo />
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <RegistrationConfirmation />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
