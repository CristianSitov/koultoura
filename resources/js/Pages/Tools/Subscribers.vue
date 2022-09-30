<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import {Link} from '@inertiajs/inertia-vue3';
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
                <div class="w-full flex flex-row py-3">
                    <div class="grow justify-content-start">
                        <Link :href="route('dashboard_subscribers_by_day', 1)"
                              :class="{ 'bg-white text-red-600': $page.props.day_id === 1, 'bg-red-600 text-white': $page.props.day_id !== 1 }"
                              class="text-sm font-bold border-red-600 border-2 rounded p-2 ml-2 sm:mx-2">Day 1</Link>
                        <Link :href="route('dashboard_subscribers_by_day', 2)"
                              :class="{ 'bg-white text-red-600': $page.props.day_id === 2, 'bg-red-600 text-white': $page.props.day_id !== 2 }"
                              class="text-sm font-bold border-red-600 border-2 rounded p-2 ml-2 sm:mx-2">Day 2</Link>
                        <Link :href="route('dashboard_subscribers_by_day', 3)"
                              :class="{ 'bg-white text-red-600': $page.props.day_id === 3, 'bg-red-600 text-white': $page.props.day_id !== 3 }"
                              class="text-sm font-bold border-red-600 border-2 rounded p-2 ml-2 sm:mx-2">Day 3</Link>
                    </div>
                    <div class="none">
                        <a :href="route('dashboard_subscribers_pdf', {day: $page.props.day_id || null})"
                           target="_blank"
                           class="text-sm text-white font-bold bg-red-600 rounded p-2 mr-2 sm:mr-0">Download</a>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="overflow-x-auto relative">
                        <table class="table flex table-auto w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-200">
                            <tr class="hidden md:table-row">
                                <th class="py-3 px-6">Subscribe Date</th>
                                <th class="py-3 px-6">Full name</th>
                                <th class="py-3 px-6">Organization / Job</th>
                                <th class="py-3 px-6 md:w-30">Days</th>
                            </tr>
                            </thead>
                            <tbody class="flex-1 sm:flex-none">
                            <tr class="flex md:table-row flex-col w-full flex-wrap bg-white border-b"
                                v-for="subscriber in $page.props.subscribers">
                                <td class="align-top py-4 px-6">
                                    <span class="block">{{ subscriber.created_diff }}</span>
                                    <span class="block text-sm">{{ subscriber.created_date }}</span>
                                </td>
                                <td class="align-top py-4 px-6">
                                    <a class="block text-lg text-gray-700"
                                       target="_blank"
                                       :href="route('confirmation', {id: subscriber.slug })">{{ subscriber.last_name }} {{ subscriber.first_name }}</a>
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
