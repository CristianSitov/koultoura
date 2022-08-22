<script setup>
const props = defineProps({
    activeTab: {
        type: String,
        default: '1',
    },
});

</script>

<template>
    <div class="snap-start scroll-m-0 snap-both">
        <div id="schedule" class="bg-white w-full">
            <section class="my-10 mx-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
                <h1 class="text-6xl font-bold uppercase text-black mb-10">Schedule</h1>

                <div class="mb-4 border-b border-orange-200">
                    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="myTab" data-tabs-toggle="#myTabContent" role="tablist">
                        <li v-for="(day, index) in $page.props.presentations" class="mr-2" role="presentation">
                            <button class="inline-block p-4 rounded-t-lg border-b-2 text-orange-500 hover:text-orange-600"
                                    id="profile-tab" :data-tabs-target="'#tab' + index" type="button"
                                    v-on:click="activeTab = index"
                                    :class="{ 'border-orange-600': activeTab === index, 'border-transparent': activeTab !== index }"
                                    role="tab" aria-controls="profile" aria-selected="true">Day {{ index }}</button>
                        </li>
                    </ul>
                </div>
                <div id="myTabContent">
                    <div v-for="(day, index) in $page.props.presentations"
                         :id="'tab' + index"
                         :class="{ hidden: activeTab !== index }"
                         class=""
                         role="tabpanel" aria-labelledby="profile-tab">
                        <section class="text-gray-600 body-font">
                            <div class="container px-5 py-24 mx-auto flex flex-wrap">
                                <div v-for="presentation in day" :key="presentation.id" class="flex relative pt-10 pb-20 sm:items-center mx-auto w-full">
                                    <div class="h-full w-12 absolute inset-0 flex items-center justify-center">
                                        <div class="h-full w-1 bg-orange-300 pointer-events-none"></div>
                                    </div>
                                    <div class="flex-shrink-0 w-12 h-12 rounded-full mt-10 sm:mt-0 inline-flex items-center justify-center bg-orange-500 text-white relative z-10 title-font font-medium text-sm">{{ presentation.hour }}</div>
                                    <div class="flex mx-5 md:pl-8 pl-6 sm:items-center items-start flex-col sm:flex-row">
                                        <div class="flex items-center justify-center -space-x-12 w-40">
                                            <div v-for="presenter in presentation.speakers" class="flex-none">
                                                <img class="w-24 h-24 rounded-full" :src="presenter.avatar" :alt="presenter.full_name">
                                            </div>
                                        </div>
                                        <div class="flex-1 sm:pl-6 mt-6 sm:mt-0">
                                            <h2 class="font-medium title-font text-gray-900 mb-1 text-xl">{{ presentation.title }}</h2>
                                            <p class="leading-relaxed">{{ presentation.description }}</p>
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
