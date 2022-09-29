<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
defineProps({
    termsTitle: String,
    terms: String,
});
</script>

<template>
    <AppLayout :title="termsTitle">
        <template #header>
            <div class="font-semibold text-xl text-white leading-tight flex-none"
                 v-html="termsTitle"></div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="w-full mx-auto mt-6 p-6 bg-white shadow-lg overflow-hidden sm:rounded-lg prose" v-html="terms" />
            </div>
        </div>
    </AppLayout>
</template>
