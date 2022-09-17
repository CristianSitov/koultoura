<script setup>
import {CalendarIcon, LocationMarkerIcon} from '@heroicons/vue/outline'

const props = defineProps({
    activeTab: {
        type: String,
        default: '1',
    },
});

</script>

<template>
    <div class="snap-start scroll-m-0 snap-both">
        <div id="schedule" class="bg-gray-900 w-full text-white">
            <section class="max-w-7xl mx-auto px-4 sm:pt-12 sm:px-6 md:pt-16 lg:pt-20 lg:px-8 xl:pt-28">
                <h1 class="text-6xl font-bold uppercase mb-10">Schedule</h1>

                <div class="mb-4 border-b border-white">
                    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="myTab" data-tabs-toggle="#myTabContent" role="tablist">
                        <li v-for="(day, index) in $page.props.days" class="mr-2" role="presentation">
                            <button class="inline-block p-4 rounded-t-lg border-b-2 text-white hover:text-orange-600"
                                    id="profile-tab" :data-tabs-target="'#tab' + index" type="button"
                                    v-on:click="activeTab = index"
                                    :class="{ 'border-orange-600': activeTab === index, 'border-transparent': activeTab !== index }"
                                    role="tab" aria-controls="profile" aria-selected="true">{{ $t('Day :day // :name', {day: day.id, name: day.name }) }}</button>
                        </li>
                    </ul>
                </div>
                <div id="myTabContent">
                    <div v-for="(day, index) in $page.props.presentations"
                         :id="'tab' + index"
                         :class="{ hidden: activeTab !== index }"
                         class=""
                         role="tabpanel" aria-labelledby="profile-tab">
                        <section class="text-white body-font">
                            <p><span class="inline-flex leading-10"><CalendarIcon
                                class="h-7 md:h-9 sm:h-7 w-7 md:w-9 sm:w-7 mr-4" aria-hidden="true"/>{{ $page.props.days[index].title }}</span></p>
                            <p><span class="inline-flex leading-10"><LocationMarkerIcon
                                class="h-7 md:h-9 sm:h-7 w-7 md:w-9 sm:w-7 mr-4" aria-hidden="true"/>{{ $page.props.days[index].location }}</span></p>
                            <p>{{ $page.props.days[index].description }}</p>
                            <div class="container px-5 py-24 mx-auto flex flex-wrap">
                                <div v-for="presentation in day" :key="presentation.id" class="flex relative pt-10 pb-20 sm:items-center mx-auto w-full">
<!--                                    <div class="h-full w-12 absolute inset-0 flex items-center justify-center">-->
<!--                                        <div class="h-full w-1 bg-red-500 pointer-events-none"></div>-->
<!--                                    </div>-->
<!--                                    <div class="flex-shrink-0 w-12 h-12 rounded-full mt-10 sm:mt-0 inline-flex items-center justify-center bg-red-700 text-white relative z-10 title-font font-medium text-sm"></div>-->
                                    <div class="flex-shrink-0 inline-flex relative text-5xl">
                                        {{ presentation.hour }}
                                    </div>
                                    <div class="flex mx-5 md:pl-8 pl-6 sm:items-center items-start flex-col sm:flex-row">
                                        <div class="flex items-center justify-center -space-x-4 w-32">
                                            <div v-for="presenter in presentation.speakers" class="flex-none">
                                                <img class="w-14 h-14 rounded-full" :src="presenter.avatar" :alt="presenter.full_name">
                                            </div>
                                        </div>
                                        <div class="flex-1 sm:pl-6 mt-6 sm:mt-0">
                                            <h4 class="font-bold title-font mb-1 text-lg">{{ presentation.supratitle }}</h4>
                                            <h2 class="font-medium title-font mb-1"
                                                :class="{ 'text-2xl italic text-gray-400': presentation.flag === 'service', 'text-4xl': presentation.flag === 'main' }">{{ presentation.title }}</h2>
                                            <p class="leading-relaxed">{{ presentation.description }}</p>
                                            <h3 class="font-medium title-font mb-1 text-2xl">{{ presentation.subtitle }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
