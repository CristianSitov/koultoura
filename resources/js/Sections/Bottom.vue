<script setup>
import {Link} from '@inertiajs/inertia-vue3'
</script>
<template>
    <div>
        <footer class="mb-10 p-4 bg-white md:p-8 lg:p-10">
            <div class="mx-auto max-w-screen-xl text-center">
                <div class="mt-4 sm:mt-0 sm:flex sm:items-center sm:justify-between">
                    <div class="text-sm text-gray-500 text-center md:text-left">
                        © 2022 <strong><a href="//prinbanat.ngo" class="hover:underline">Prin Banat NGO</a></strong>. {{ $t('All Rights Reserved.') }}<br />
                        <Link :href="route('terms')">{{ $t('Terms') }}</Link> | <Link :href="route('cookies')">{{ $t('Cookies') }}</Link></div>
                    <div class="text-sm text-gray-500 text-center md:text-right" v-html="$t('Made with ♡')"></div>
                </div>
            </div>
        </footer>
    </div>
</template>
