<script setup>
import {LibraryIcon, LocationMarkerIcon, MicrophoneIcon} from '@heroicons/vue/outline'

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
            <section class="max-w-7xl mx-auto px-4 pt-8 sm:pt-12 sm:px-6 md:pt-16 lg:pt-20 lg:px-8 xl:pt-28">
                <h1 class="text-6xl font-bold uppercase wcm" v-html="$t('Schedule')"></h1>
                <span>{{ $t('schedule updating') }}</span>

                <div class="mt-10 mb-4 border-b border-white">
                    <ul class="flex -mb-px text-sm font-medium text-center" id="myTab" data-tabs-toggle="#myTabContent" role="tablist">
                        <li v-for="(day, index) in $page.props.days" class="grow" role="presentation">
                            <button class="inline-block p-2 md:p-4 md:px-8 rounded-t-lg border-b-2 text-white hover:text-orange-600"
                                    id="profile-tab" :data-tabs-target="'#tab' + index" type="button"
                                    v-on:click="activeTab = index"
                                    :class="{ 'border-orange-600 text-orange-600': activeTab === index, 'border-transparent': activeTab !== index }"
                                    role="tab" aria-controls="profile" aria-selected="true">
                                <span class="text-xl md:text-4xl font-bold block uppercase">{{ $t('Day :day', { day: index }) }}</span>
                                <strong class="block md:text-xl">{{ day.name }}</strong></button>
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
                            <div class="md:w-3/4 mx-auto">
                                <h3 class="text-2xl font-bold uppercase pt-6 md:pt-12">{{ $page.props.days[index].title }}</h3>
                                <p class="row-span-2 py-3" v-html="$page.props.days[index].description"></p>
                                <p class="flex flex-row mb-3"><span class="leading-6 lg:leading-10 mb-2"><LocationMarkerIcon
                                    class="h-6 md:h-9 sm:h-7 w-7 md:w-9 sm:w-7 mr-2" aria-hidden="true"/></span>
                                    <span class="inline-flex font-bold">{{ $page.props.days[index].location }}</span></p>
                                <p class="flex flex-row mb-3"><span class="leading-6 lg:leading-10 mb-2"><LibraryIcon
                                    class="h-5 md:h-9 sm:h-7 w-7 md:w-9 sm:w-7 mr-2" aria-hidden="true"/></span>
                                    <span class="inline-flex">{{ $t('Host Moderator') }}:&nbsp;<span class="font-bold">{{ $page.props.days[index].host?.full_name || 'TBA'}}</span></span></p>
                                <p class="flex flex-row mb-3"><span class="leading-6 lg:leading-10 mb-2"><MicrophoneIcon
                                    class="h-5 md:h-9 sm:h-7 w-7 md:w-9 sm:w-7 mr-2" aria-hidden="true"/></span>
                                    <span class="inline-flex">{{ $t('Moderator(s)') }}:&nbsp;<span class="font-bold">{{ $page.props.days[index].moderators.map((m) => m.full_name).join(", ") || 'TBA'}}</span></span></p>
                            </div>
                            <div class="container md:px-5 py-10 md:py-24 mx-auto flex flex-wrap">
                                <div v-for="presentation in day" :key="presentation.id" class="flex relative pt-5 mt-3 md:pt-10 md:pb-20 sm:items-center mx-auto w-full">
                                    <div class="flex-shrink-0 inline-flex relative font-bold text-lg md:text-5xl px-4">
                                        {{ presentation.starts_at }}
                                    </div>
                                    <div class="flex sm:mx-5 md:pl-8 sm:pl-6 sm:items-center items-start flex-col sm:flex-row">
                                        <div class="flex-1 md:pl-6 md:mt-6 md:mt-0">
                                            <h4 class="mb-1 text-lg" v-if="presentation.supratitle !== ''">{{ presentation.supratitle }}</h4>
                                            <h2 class="font-bold title-font mb-3 md:mb-5"
                                                :class="{ 'text-xl md:text-2xl italic text-gray-400': presentation.flag === 'service', 'text-xl md:text-2xl': presentation.flag === 'main' }">{{ presentation.title }}</h2>
                                            <p class="leading-relaxed" v-html="presentation.description"></p>
                                            <h3 class="font-medium title-font mb-3 text-2xl">{{ presentation.subtitle }}</h3>
                                            <h3 class="font-light title-font my-2 text-sm md:text-lg"
                                                v-if="presentation.moderators.length > 0">{{ $t('Moderated by') }}: <span
                                                v-for="(moderator, index) in presentation.moderators"
                                                class="font-bold">{{ moderator.full_name }}<span v-if="index+1 < presentation.moderators.length">, </span></span></h3>
                                            <h3 class="font-light title-font mb-1 text-sm md:text-lg"
                                                v-if="presentation.speakers.length > 0">{{ $t('Speakers') }}: <span
                                                v-for="(speaker, index) in presentation.speakers"
                                                class="font-bold">{{ speaker.full_name }}<span v-if="index+1 < presentation.speakers.length">, </span></span></h3>
                                            <div class="flex flex-row flex-wrap items-center sm:items-left mt-5">
                                                <div v-for="presenter in presentation.speakers" class="flex-none -ml-2">
                                                    <img class="w-14 h-14 rounded-full" :src="route('home') + presenter.avatar" :alt="presenter.full_name">
                                                </div>
                                            </div>
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
