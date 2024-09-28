<template>
    <div id="speakers" class="bg-white text-gray-700 max-w-7xl mx-auto my-24 px-4 sm:px-6 lg:px-8 pt-4 sm:pt-12 md:pt-16 rounded-lg snap-start scroll-m-0 snap-both">
        <div class="py-8 lg:px-4 mx-auto">
            <div class="sm:text-lg">
                <h1 class="text-6xl font-bold uppercase wcm" v-html="$tChoice('Speakers', 2)"></h1>
                <p class="mb-16">{{ $t('Speakers soon') }}</p>
                <div v-for="speaker in $page.props.speakers"
                     :id="speaker.slug"
                     class="md:grid md:grid-cols-12 items-center pt-14">
                    <img class="md:col-span-2 mx-auto align-top mb-4 w-75 sm:w-full md:w-36 md:h-36 rounded-md md:rounded-full sm:ml-0 sm:mr-6"
                         :src="route('2024.home') + speaker.avatar"
                         :alt="speaker.full_name">
                    <div class="flex flex-col md:col-span-10 text-left md:ml-24">
                        <h3 class="text-3xl font-bold tracking-tight text-center sm:text-left">{{ speaker.full_name }}</h3>
                        <span class="text-xl italic text-center sm:text-left py-3">{{ speaker.role }}</span>
                        <span class="text-lg text-center sm:text-left">{{ speaker.institution }}</span>
                        <p class="mt-3 mb-4 text-sm text-justify sm:text-left" v-html="speaker.description"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
