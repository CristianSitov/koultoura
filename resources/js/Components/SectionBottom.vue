<script setup>
</script>

<template>
    <div>
        <footer class="p-4 bg-white md:p-8 lg:p-10 dark:bg-gray-800">
            <div class="mx-auto max-w-screen-xl text-center">
                <a href="/" class="flex justify-center items-center text-2xl font-semibold text-gray-900">why culture matters</a>
                <p class="my-6 text-gray-500 dark:text-gray-400">{{ $t('bottom tagline') }}</p>
                <ul class="flex flex-wrap justify-center items-center mb-14 text-gray-900">
                    <li v-for="item in $page.props.navigation" :key="item.name">
                        <a :href="item.href" class="mr-4 hover:underline md:mr-6 ">{{ $t(item.name) }}</a>
                    </li>
                </ul>
                <div class="sm:flex sm:items-center sm:justify-between">
                    <span class="text-sm text-gray-500 sm:text-center">© 2022 <a href="https://prinbanat.ngo" class="hover:underline">Prin Banat NGO</a>. {{ $t('All Rights Reserved.') }}</span>
                    <div class="flex mt-4 text-sm space-x-6 sm:justify-center sm:mt-0 text-gray-500">{{ $t('Made with ♡') }}</div>
                </div>
            </div>
        </footer>
    </div>
</template>
