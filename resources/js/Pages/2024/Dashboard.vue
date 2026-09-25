<script setup>
import AppLayout from '../../Layouts/Layout2024.vue';
import Welcome from '../../Components/Welcome.vue';
import MainMenu from "../../Sections/2024/MainMenu.vue";

// Which edition's subscribers this dashboard shows — its links stay in-year.
defineProps({ year: { type: String, default: '2024' } });
</script>

<template>
    <MainMenu></MainMenu>

    <AppLayout title="Dashboard">
        <template #header>
            <div class="font-semibold text-xl text-white leading-tight flex-none">
                Home
            </div>
        </template>

        <div class="py-24">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <Welcome :year="year" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
