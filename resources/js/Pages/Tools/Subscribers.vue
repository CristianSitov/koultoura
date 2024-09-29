<script setup>
import {Link} from '@inertiajs/inertia-vue3';
import AppLayout from '../../Layouts/Layout2024.vue';
import MainMenu from "../../Sections/2024/MainMenu.vue";
</script>

<template>
    <MainMenu></MainMenu>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight mt-10">
                Subscribers
            </h2>
        </template>

        <div class="py-24">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="w-full flex flex-row py-3">
                    <div class="grow justify-content-start">
                        <Link :href="route('dashboard_subscribers', {day: 0, volunteers: 1})"
                              :class="{ 'bg-white text-red-600 border-white': $page.props.volunteers === 1, 'bg-white border-red-600 text-gray-600': $page.props.volunteers === 0 }"
                              class="text-sm font-bold border-red-600 border-2 rounded p-2 ml-2 sm:mx-2">Vol</Link>
                        <Link :href="route('dashboard_subscribers')"
                              :class="{ 'bg-white text-red-600 border-white': $page.props.day === 0 && $page.props.volunteers === 0, 'bg-white border-red-600 text-gray-600': ($page.props.day === 0 && $page.props.volunteers === 1) || ($page.props.day !== 0 && $page.props.volunteers === 0)}"
                              class="text-sm font-bold border-red-600 border-2 rounded p-2 ml-2 sm:mx-2">ALL</Link>
                        <Link :href="route('dashboard_subscribers', {day: 1})"
                              :class="{ 'bg-white text-red-600 border-white': $page.props.day === 1, 'bg-white border-red-600 text-gray-600': $page.props.day !== 1 }"
                              class="text-sm font-bold border-red-600 border-2 rounded p-2 ml-2 sm:mx-2"><span class="hidden md:inline-flex">Day&nbsp;</span>1</Link>
                        <Link :href="route('dashboard_subscribers', {day: 2})"
                              :class="{ 'bg-white text-red-600 border-white': $page.props.day === 2, 'bg-white border-red-600 text-gray-600': $page.props.day !== 2 }"
                              class="text-sm font-bold border-red-600 border-2 rounded p-2 ml-2 sm:mx-2"><span class="hidden md:inline-flex">Day&nbsp;</span>2</Link>
                        <Link :href="route('dashboard_subscribers', {day: 3})"
                              :class="{ 'bg-white text-red-600 border-white': $page.props.day === 3, 'bg-white border-red-600 text-gray-600': $page.props.day !== 3 }"
                              class="text-sm font-bold border-red-600 border-2 rounded p-2 ml-2 sm:mx-2"><span class="hidden md:inline-flex">Day&nbsp;</span>3</Link>
                    </div>
                    <div class="none">
                        <a :href="route('dashboard_subscribers_pdf', {day: $page.props.day || 0, volunteers: $page.props.volunteers})"
                           target="_blank"
                           class="text-sm text-red-600 font-bold bg-white rounded p-2 mr-2 sm:mr-0">Download</a>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="overflow-x-auto relative">
                        <table class="table flex table-auto w-full text-sm text-left text-gray-500">
                            <caption class="text-start leading-loose text-lg uppercase font-bold ml-4">
                                Subcribers: {{ $page.props.subscribers.length }}
                            </caption>
                            <thead class="text-xs text-gray-700 uppercase bg-gray-200">
                            <tr class="hidden md:table-row">
                                <th class="py-3 px-6">Subscribe Date</th>
                                <th class="py-3 px-6">Full name</th>
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
                                       :href="route('2024.confirmation', {id: subscriber.slug })">{{ subscriber.last_name }} {{ subscriber.first_name }}</a>
                                    <span class="block text-sm">Email: {{ subscriber.email }}</span>
                                    <span class="block text-sm">Phone: {{ subscriber.profile.phone }}</span>
                                    <span class="block text-md">Org: {{ subscriber.profile.organization }} ({{ subscriber.profile.country }})</span>
                                    <span class="block text-md">Job: {{ subscriber.profile.job }}</span>
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
