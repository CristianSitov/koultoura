<template>
    <div>
        <footer class="my-20 p-4 bg-white md:p-8 lg:p-10">
            <div class="mx-auto max-w-screen-xl text-center">
                <a href="/public" class="flex justify-center items-center text-5xl font-semibold text-red-600">why culture matters</a>
                <div class="sm:flex sm:items-center sm:justify-between">
                    <span class="text-sm text-gray-500 sm:text-center">© 2022 <strong><a href="//prinbanat.ngo" class="hover:underline">Prin Banat NGO</a></strong>. {{ $t('All Rights Reserved.') }}</span>
                    <div class="mt-4 text-sm sm:justify-center sm:mt-0 text-gray-500" v-html="$t('Made with ♡')"></div>
                </div>
            </div>
        </footer>
    </div>
</template>
