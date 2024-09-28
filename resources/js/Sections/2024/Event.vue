<template>
    <div id="event" class="bg-white max-w-7xl mx-auto rounded-lg text-gray-800 snap-start scroll-m-0 snap-both">
        <section class="px-4 sm:px-6 lg:px-8 pt-4">
            <div class="flex flex-col md:flex-row md:space-x-12">
                <h1 class="md:basis-1/3 text-4xl sm:text-5xl lg:text-7xl py-5 drop-shadow-lg font-bold uppercase sm:mb-10 wcm">why culture matters</h1>
                <p class="md:basis-2/3 font-normal sm:text-xl sm:leading-9 lg:text-2xl lg:leading-10 p-5 pb-10 md:pb-20 lg:p-20 about"
                    v-html="$t('Event')"></p>
            </div>
        </section>
    </div>
</template>
