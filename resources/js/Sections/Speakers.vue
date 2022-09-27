<template>
    <div class="snap-start scroll-m-0 snap-both">
        <div id="speakers" class="bg-black w-full">
            <section class="max-w-7xl mx-auto px-4 sm:pt-12 sm:px-6 md:pt-16 lg:pt-20 lg:px-8 xl:pt-28">
                <div class="py-8 px-4 mx-auto">
                    <div class="text-white sm:text-lg dark:text-gray-400">
                        <h1 class="text-6xl font-bold uppercase text-white wcm" v-html="$t('Speakers')"></h1>
                        <span>{{ $t('schedule updating') }}</span>
                        <div v-for="speaker in $page.props.speakers" class="md:grid md:grid-cols-12 items-center py-8">
                            <img class="md:col-span-2 mx-auto align-top mb-4 max-w-sm sm:w-full md:w-36 md:h-36 rounded-md md:rounded-full sm:ml-0 sm:mr-6"
                                 :src="route('home') + speaker.avatar"
                                 :alt="speaker.full_name">
                            <div class="md:col-span-10 text-left md:ml-24">
                                <h3 class="text-3xl font-bold tracking-tight text-center sm:text-left">{{ speaker.full_name }}</h3>
                                <span class="text-xl italic text-center sm:text-left py-3">{{ speaker.role }}</span>
                                <span class="text-lg text-center sm:text-left">{{ speaker.institution }}</span>
                                <p class="mt-3 mb-4 text-sm text-justify sm:text-left" v-html="speaker.description"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
