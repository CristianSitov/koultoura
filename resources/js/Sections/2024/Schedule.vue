<script setup>
import {LibraryIcon, LocationMarkerIcon, MicrophoneIcon} from '@heroicons/vue/outline'
import {TabGroup, TabList, Tab, TabPanels, TabPanel} from "@headlessui/vue";
</script>
<script>
import {ref} from "vue";
import emitter from "../../emitter.js";

const selectedTab = ref(1) // TODO: adjust this using date

export default {
    methods: {
        changeTab(index) {
            emitter.emit('flipToTab', { arg: index })
        }
    }
}

emitter.on('flipToTab', e => selectedTab.value = e.arg )

</script>

<template>
    <div id="schedule" class="bg-gray-900 text-white max-w-7xl mx-auto my-24 px-4 sm:px-6 lg:px-8 pt-4 sm:pt-12 md:pt-16 rounded-lg snap-start scroll-m-0 snap-both">
        <div id="ateliere"></div><div id="workshops"></div>
        <h1 class="text-6xl font-bold uppercase wcm" v-html="$t('Schedule')"></h1>

        <TabGroup :selectedIndex="selectedTab" @change="changeTab">
            <div class="mt-14 mb-4 border-b border-white">
                <TabList class="flex -mb-px text-sm font-medium text-center" data-tabs-toggle="#myTabContent" role="tablist">
                    <Tab v-for="(day, index) in $page.props.days"
                         class="grow"
                         as="template"
                         :key="index"
                         v-slot="{ selected }">
                        <button class="inline-block p-2 md:p-4 md:px-8 rounded-t-lg border-b-2 ui-not-selected:text-white hover:text-orange-600"
                                id="profile-tab"
                                type="button"
                                :class="{ 'bg-white text-black': selected, 'border-transparent': !selected }"
                                role="tab" aria-controls="profile" aria-selected="true">
                            <span class="text-xl md:text-4xl font-bold block uppercase">{{ $t('Day :day', { day: index }) }}</span>

                            <strong class="block md:text-xl">{{ day.name }}</strong></button>
                    </Tab>
                </TabList>
            </div>
            <TabPanels id="myTabContent">
                <TabPanel v-for="(day, index) in $page.props.presentations"
                          :id="'tab' + index"
                          role="tabpanel" aria-labelledby="profile-tab">
                    <section class="text-white body-font">
                        <div class="md:w-3/4 mx-auto">
                            <h3 class="text-2xl font-bold uppercase pt-6 md:pt-12">{{ $page.props.days[index].title }}</h3>
                            <p class="row-span-2 py-3" v-html="$page.props.days[index].description"></p>
                            <p class="flex flex-row mb-4"
                               v-if="$page.props.venues[index].length === 1"
                               v-for="(venue) in $page.props.venues[index]"><span class="leading-6 lg:leading-10 mb-2"><LocationMarkerIcon
                                class="h-5 md:h-9 sm:h-7 w-7 md:w-9 sm:w-7 mr-2" aria-hidden="true"/></span>
                                <span class="inline-flex md:py-2">{{ venue.location }}</span>
                            </p>
                            <p class="flex flex-row mb-4"><span class="leading-6 lg:leading-10 mb-2"><LibraryIcon
                                class="h-5 md:h-9 sm:h-7 w-7 md:w-9 sm:w-7 mr-2" aria-hidden="true"/></span>
                                <span class="inline-flex md:py-2">{{ $t('Host Moderator') }}:&nbsp;<span class="font-bold">{{ $page.props.days[index].host?.full_name || 'TBA'}}</span></span></p>
                            <p class="flex flex-row mb-4"><span class="leading-6 lg:leading-10 mb-2"
                                v-if="$page.props.days[index].moderators.length"><MicrophoneIcon
                                class="h-5 md:h-9 sm:h-7 w-7 md:w-9 sm:w-7 mr-2" aria-hidden="true"/></span>
                                <span class="inline-flex md:py-2"
                                      v-if="$page.props.days[index].moderators.length">{{ $tChoice('Moderators', $page.props.days[index].moderators.length) }}:&nbsp;<span class="font-bold">{{ $page.props.days[index].moderators.map((m) => m.full_name).join(", ") || 'TBA'}}</span></span></p>
                        </div>
                        <div class="container md:px-5 py-10 md:py-24 mx-auto flex flex-wrap">
                            <div v-for="venue in day">
                                <h2 class="flex flex-row w-1/2 self-center text-4xl font-bold pt-12"
                                    v-if="day.length > 1"><span class="leading-6 lg:leading-10 mb-2"><LocationMarkerIcon
                                    class="h-5 md:h-9 sm:h-7 w-7 md:w-9 sm:w-7 mr-2" aria-hidden="true"/></span>
                                    <span class="inline-flex md:py-2">{{ venue[0].venue.location }}</span></h2>
                                <div v-for="presentation in venue"
                                     :key="presentation.id"
                                     class="flex relative pt-5 mt-3 md:pt-10 md:pb-20 sm:items-center mx-auto w-full">
                                    <div class="flex-shrink-0 inline-flex relative font-bold text-lg md:text-5xl pr-4">
                                        {{ presentation.starts_at }}
                                    </div>
                                    <div class="flex sm:mx-5 md:pl-8 sm:pl-6 sm:items-center items-start flex-col sm:flex-row">
                                        <div class="flex-1 md:pl-6 md:mt-0">
                                            <h4 class="mb-1 text-lg" v-if="presentation.supratitle !== ''">{{ presentation.supratitle }}</h4>
                                            <h2 class="font-bold title-font mb-3 md:mb-5"
                                                :class="{ 'text-xl md:text-2xl italic text-gray-400': presentation.flag === 'service', 'text-xl md:text-2xl': presentation.flag === 'main' }"
                                                v-html="presentation.title"></h2>
                                            <p class="leading-relaxed" v-html="presentation.description"></p>
                                            <h3 class="font-medium title-font mb-3 text-2xl">{{ presentation.subtitle }}</h3>
                                            <h3 class="font-light title-font mt-1 text-sm md:text-lg"
                                                v-if="presentation.moderators.length > 0">{{ $tChoice('Moderators', presentation.moderators.length) }}: <span
                                                v-for="(moderator, index) in presentation.moderators"
                                                class="font-bold">{{ moderator.full_name }}<span v-if="index+1 < presentation.moderators.length">, </span></span></h3>
                                            <div class="flex flex-row flex-wrap items-center sm:items-left mt-2 mb-5"
                                                 v-if="presentation.moderators.length > 0">
                                                <div v-for="moderator in presentation.moderators" class="flex-none -ml-2">
                                                    <a :href="'#'+moderator.slug">
                                                        <img class="w-14 h-14 rounded-full" :src="moderator.avatar" :alt="moderator.full_name">
                                                    </a>
                                                </div>
                                            </div>
                                            <h3 class="font-light title-font mt-1 text-sm md:text-lg"
                                                v-if="presentation.chairpersons.length > 0">{{ $tChoice('Chairpersons', presentation.chairpersons.length) }}: <span
                                                v-for="(chairperson, index) in presentation.chairpersons"
                                                class="font-bold">{{ chairperson.full_name }}<span v-if="index+1 < presentation.chairpersons.length">, </span></span></h3>
                                            <div class="flex flex-row flex-wrap items-center sm:items-left mt-2 mb-5"
                                                 v-if="presentation.chairpersons.length > 0">
                                                <div v-for="chairperson in presentation.chairpersons" class="flex-none -ml-2">
                                                    <a :href="'#'+chairperson.slug">
                                                        <img class="w-14 h-14 rounded-full" :src="chairperson.avatar" :alt="chairperson.full_name">
                                                    </a>
                                                </div>
                                            </div>
                                            <h3 class="font-light title-font mt-1 text-sm md:text-lg"
                                                v-if="presentation.rapporteurs.length > 0">{{ $tChoice('Rapporteurs', presentation.rapporteurs.length) }}: <span
                                                v-for="(rapporteur, index) in presentation.rapporteurs"
                                                class="font-bold">{{ rapporteur.full_name }}<span v-if="index+1 < presentation.rapporteurs.length">, </span></span></h3>
                                            <div class="flex flex-row flex-wrap items-center sm:items-left mt-2 mb-5"
                                                 v-if="presentation.rapporteurs.length > 0">
                                                <div v-for="rapporteur in presentation.rapporteurs" class="flex-none -ml-2">
                                                    <a :href="'#'+rapporteur.slug">
                                                        <img class="w-14 h-14 rounded-full" :src="rapporteur.avatar" :alt="rapporteur.full_name">
                                                    </a>
                                                </div>
                                            </div>
                                            <h3 class="font-light title-font mt-1 text-sm md:text-lg"
                                                v-if="presentation.facilitators.length > 0">{{ $tChoice('Facilitators', presentation.facilitators.length) }}: <span
                                                v-for="(facilitator, index) in presentation.facilitators"
                                                class="font-bold">{{ facilitator.full_name }}<span v-if="index+1 < presentation.facilitators.length">, </span></span></h3>
                                            <div class="flex flex-row flex-wrap items-center sm:items-left mt-2 mb-5"
                                                 v-if="presentation.facilitators.length > 0">
                                                <div v-for="facilitator in presentation.facilitators" class="flex-none -ml-2">
                                                    <a :href="'#'+facilitator.slug">
                                                        <img class="w-14 h-14 rounded-full" :src="facilitator.avatar" :alt="facilitator.full_name">
                                                    </a>
                                                </div>
                                            </div>
                                            <h3 class="font-light title-font mt-1 text-sm md:text-lg"
                                                v-if="presentation.speakers.length > 0">{{ $tChoice('Speakers', presentation.speakers.length) }}: <span
                                                v-for="(speaker, index) in presentation.speakers"
                                                class="font-bold">{{ speaker.full_name }}<span v-if="index+1 < presentation.speakers.length">, </span></span></h3>
                                            <div class="flex flex-row flex-wrap items-center sm:items-left mt-2 mb-5"
                                                 v-if="presentation.speakers.length > 0">
                                                <div v-for="presenter in presentation.speakers" class="flex-none -ml-2">
                                                    <a :href="'#'+presenter.slug">
                                                        <img class="w-16 h-16 rounded-full" :src="presenter.avatar" :alt="presenter.full_name">
                                                    </a>
                                                </div>
                                            </div>
                                            <h3 class="font-light title-font mt-1 text-sm md:text-lg"
                                                v-if="presentation.urban_guides.length > 0">{{ $tChoice('Urban Guides', presentation.urban_guides.length) }}: <span
                                                v-for="(urban_guide, index) in presentation.urban_guides"
                                                class="font-bold">{{ urban_guide.full_name }}<span v-if="index+1 < presentation.urban_guides.length">, </span></span></h3>
                                            <div class="flex flex-row flex-wrap items-center sm:items-left mt-2 mb-5"
                                                 v-if="presentation.urban_guides.length > 0">
                                                <div v-for="urban_guide in presentation.urban_guides" class="flex-none -ml-2">
                                                    <a :href="'#'+urban_guide.slug">
                                                        <img class="w-16 h-16 rounded-full" :src="urban_guide.avatar" :alt="urban_guide.full_name">
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </TabPanel>
            </TabPanels>
        </TabGroup>
    </div>
</template>
