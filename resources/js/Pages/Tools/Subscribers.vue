<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight mt-10">
                Subscribers
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="overflow-x-auto relative">
                        <table class="w-full table-fixed text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="py-3 px-6">
                                    Subscribe Date
                                </th>
                                <th scope="col" class="py-3 px-6">
                                    Full name
                                </th>
                                <th scope="col" class="py-3 px-6">
                                    Organization / Job
                                </th>
                                <th scope="col-2" class="py-3 px-6 w-30">
                                    Days
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700"
                                v-for="subscriber in $page.props.subscribers">
                                <td class="align-top py-4 px-6">
                                    <span class="block">{{ subscriber.created_diff }}</span>
                                    <span class="block text-sm">{{ subscriber.created_date }}</span>
                                </td>
                                <td class="align-top py-4 px-6">
                                    <a class="block text-lg text-gray-700"
                                       target="_blank"
                                       :href="route('confirmation', {id: subscriber.slug })">{{ subscriber.name }}</a>
                                    <span class="block text-sm">Email: {{ subscriber.email }}</span>
                                    <span class="block text-sm">Phone: {{ subscriber.profile.phone }}</span>
                                </td>
                                <td class="align-top py-4 px-6 text-gray-700">
                                    <span class="block text-lg">{{ subscriber.profile.organization }} ({{ subscriber.profile.country }})</span>
                                    <span class="block text-md">{{ subscriber.profile.job }}</span>
                                </td>
                                <td class="align-top py-4 px-6">
                                    <div class="flex flex-row">
                                        <div v-for="day in ['1', '2', '3']">
                                            <div class="text-md text-center font-bold rounded w-10 p-2 mr-2 border-2 border-red-600 bg-red-600 text-white"
                                                 v-if="subscriber.profile.event_details.days?.includes(day)"
                                                 v-html="day"></div>
                                            <div class="text-md text-center font-bold rounded w-10 p-2 mr-2 border-2 border-gray-200 bg-white text-gray-200"
                                                 v-else="subscriber.profile.event_details.days?.includes(day)"
                                                 v-html="day"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
