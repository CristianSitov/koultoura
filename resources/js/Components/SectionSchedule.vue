<script setup>
import {UserIcon, LocationMarkerIcon} from '@heroicons/vue/outline'

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
                <h1 class="text-6xl font-bold uppercase mb-10 wcm">{{ $t('Schedule') }}</h1>

                <div class="mb-4 border-b border-white">
                    <ul class="flex -mb-px text-sm font-medium text-center" id="myTab" data-tabs-toggle="#myTabContent" role="tablist">
                        <li v-for="(day, index) in $page.props.days" class="grow" role="presentation">
                            <button class="inline-block p-4 px-8 rounded-t-lg border-b-2 text-white hover:text-orange-600"
                                    id="profile-tab" :data-tabs-target="'#tab' + index" type="button"
                                    v-on:click="activeTab = index"
                                    :class="{ 'border-orange-600 text-orange-600': activeTab === index, 'border-transparent': activeTab !== index }"
                                    role="tab" aria-controls="profile" aria-selected="true"><span class="text-4xl font-bold block uppercase">{{ $t('Day :day', { day: index }) }}</span><strong class="block text-xl">{{ day.name }}</strong></button>
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
                            <div class="w-3/4 mx-auto">
                                <h3 class="text-2xl font-bold pt-12">{{ $page.props.days[index].title }}</h3>
                                <p class="row-span-2 py-3" v-html="$page.props.days[index].description"></p>
                                <p><span class="inline-flex leading-10"><LocationMarkerIcon
                                    class="h-7 md:h-9 sm:h-7 w-7 md:w-9 sm:w-7 mr-4" aria-hidden="true"/>{{ $page.props.days[index].location }}</span></p>
                                <p><span class="inline-flex leading-10"><UserIcon
                                    class="h-7 md:h-9 sm:h-7 w-7 md:w-9 sm:w-7 mr-4" aria-hidden="true"/>{{ $page.props.days[index].host.full_name }}</span></p>
                            </div>
                            <div class="container px-5 py-24 mx-auto flex flex-wrap">
                                <div v-for="presentation in day" :key="presentation.id" class="flex relative pt-10 pb-20 sm:items-center mx-auto w-full">
                                    <div class="flex-shrink-0 inline-flex relative text-5xl px-4">
                                        {{ presentation.starts_at }}
                                    </div>
                                    <div class="flex mx-5 md:pl-8 pl-6 sm:items-center items-start flex-col sm:flex-row">
                                        <div class="flex items-center justify-center -space-x-4 w-32">
                                            <div v-for="presenter in presentation.speakers" class="flex-none">
                                                <img class="w-14 h-14 rounded-full" :src="presenter.avatar" :alt="presenter.full_name">
                                            </div>
                                        </div>
                                        <div class="flex-1 sm:pl-6 mt-6 sm:mt-0">
                                            <h4 class="font-medium title-font mb-1 text-lg">{{ presentation.supratitle }}</h4>
                                            <h2 class="font-bold title-font mb-5"
                                                :class="{ 'text-2xl italic text-gray-400': presentation.flag === 'service', 'text-4xl': presentation.flag === 'main' }">{{ presentation.title }}</h2>
                                            <p class="leading-relaxed" v-html="presentation.description"></p>
                                            <h3 class="font-medium title-font mb-1 text-2xl">{{ presentation.subtitle }}</h3>
                                            <h3 class="font-light title-font my-2 text-lg"
                                                v-if="presentation.moderators.length > 0">{{ $t('Moderated by') }}: <span
                                                v-for="(moderator, index) in presentation.moderators"
                                                class="font-bold">{{ moderator.full_name }}<span v-if="index+1 < presentation.moderators.length">, </span></span></h3>
                                            <h3 class="font-light title-font mb-1 text-xl"
                                                v-if="presentation.speakers.length > 0">{{ $t('Speakers') }}: <span
                                                v-for="speaker in presentation.speakers"
                                                class="font-bold">{{ speaker.full_name }}</span></h3>
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
