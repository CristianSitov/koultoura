<script setup>
import {Link, usePage} from "@inertiajs/inertia-vue3";
import {Menu, MenuButton, MenuItem, MenuItems, Popover, PopoverButton, PopoverPanel} from '@headlessui/vue'
import {MenuIcon, XIcon} from '@heroicons/vue/outline'
</script>

<script>
import emitter from "../../emitter.js";

export default {
    data () {
        return {
            view: {
                atTopOfPage: true
            }
        }
    },

    beforeMount () {
        window.addEventListener('scroll', this.handleScroll);
    },

    methods: {
        handleScroll() {
            if (window.scrollY > 0) {
                if (this.view.atTopOfPage) this.view.atTopOfPage = false
            } else {
                if (!this.view.atTopOfPage) this.view.atTopOfPage = true
            }
        },
        handleMenuClick(item) {
            if (typeof item.method !== 'undefined') {
                emitter.emit(item.method, { arg: item.argument })
            }
        }
    }
}
</script>

<template>
    <div  class="fixed w-full z-20"
          :class="{ 'scrolled bg-black': !view.atTopOfPage }">
        <div class="max-w-7xl relative mx-auto h-1/5">
            <nav class="relative flex items-center justify-between px-3 sm:px-4 lg:px-8"
                 :class="{ '': !view.atTopOfPage }"
                 aria-label="Global">
                <div class="flex items-center flex-grow flex-shrink-0 lg:flex-grow-0 py-3">
                    <div class="flex items-center justify-between w-full lg:w-auto">
                        <Link :href="route('2024.home')">
                            <span class="sr-only">why culture matters</span>
                            <span class="text-white uppercase font-bold">why culture matters</span>
                        </Link>
                    </div>
                </div>
                <div class="hidden flex items-center lg:block md:ml-10 md:pr-4 md:space-x-5">
                    <Link v-for="item in $page.props.navigation"
                          :href="item.href" @click="handleMenuClick(item)"
                          :key="item.name"
                          class="font-bold uppercase text-white hover:underline">{{ $t(item.name) }}</Link>
                    <Link :href="route('register')"
                          v-if="!$page.props.auth.user"
                          class="font-bold uppercase text-white hover:underline">{{ $t('Register') }}</Link>
                    <Link
                        :href="route('logout')"
                        v-if="$page.props.auth.user"
                        method="post"
                        as="button"
                        class="px-4 py-2 rounded-md font-semibold text-xs text-red-600 uppercase tracking-widest bg-white hover:bg-red-800 hover:text-white ml-2"
                    >Logout</Link>
                    <a :href="route('2024.locale', {'locale': 'en'})"
                       type="button"
                       v-if="$page.props.translation !== 'ro'"
                       class="font-bold uppercase text-white p-2 inline-flex justify-center items-center rounded cursor-pointer hover:text-gray-900 hover:bg-gray-100 dark:hover:bg-gray-700 dark:hover:text-white"
                       role="menuitem">
                        <div class="inline-flex items-center">
                            <svg aria-hidden="true" class="h-3.5 w-3.5 rounded-full mr-2"
                                 xmlns="http://www.w3.org/2000/svg" id="flag-icons-gb" viewBox="0 0 512 512">
                                <path fill="#012169" d="M0 0h512v512H0z"/>
                                <path fill="#FFF" d="M512 0v64L322 256l190 187v69h-67L254 324 68 512H0v-68l186-187L0 74V0h62l192 188L440 0z"/>
                                <path fill="#C8102E" d="m184 324 11 34L42 512H0v-3l184-185zm124-12 54 8 150 147v45L308 312zM512 0 320 196l-4-44L466 0h46zM0 1l193 189-59-8L0 49V1z"/>
                                <path fill="#FFF" d="M176 0v512h160V0H176zM0 176v160h512V176H0z"/>
                                <path fill="#C8102E" d="M0 208v96h512v-96H0zM208 0v512h96V0h-96z"/>
                            </svg>EN
                        </div>
                    </a>
                    <a :href="route('2024.locale', {'locale': 'ro'})"
                       type="button"
                       v-if="$page.props.translation !== 'en'"
                       class="font-bold uppercase text-white p-2 inline-flex justify-center items-center rounded cursor-pointer hover:text-gray-900 hover:bg-gray-100"
                       role="menuitem">
                        <div class="inline-flex items-center">
                            <svg aria-hidden="true" class="h-3.5 w-3.5 rounded-full mr-2"
                                 xmlns="http://www.w3.org/2000/svg" id="flag-icons-ro" viewBox="0 0 512 512">
                                <g fill-rule="evenodd" stroke-width="1pt">
                                    <path fill="#00319c" d="M0 0h170.7v512H0z"/>
                                    <path fill="#ffde00" d="M170.7 0h170.6v512H170.7z"/>
                                    <path fill="#de2110" d="M341.3 0H512v512H341.3z"/>
                                </g>
                            </svg>RO
                        </div>
                    </a>
                </div>
                <Menu as="div" class="lg:hidden py-3">
                    <div class="flex items-center">
                        <MenuButton
                            class="rounded-md p-2 inline-flex items-center text-white hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                            <span class="sr-only">{{ $t('Open main menu') }}</span>
                            <MenuIcon class="h-6 w-6" aria-hidden="true"/>
                        </MenuButton>
                    </div>

                    <transition enter-active-class="transition duration-150 ease-out"
                                enter-from-class="transform opacity-0 slide-95"
                                enter-to-class="transform opacity-100 slide-100"
                                leave-active-class="transition duration-100 ease-in"
                                leave-from-class="transform opacity-100 slide-100"
                                leave-to-class="transform opacity-0 slide-95">
                        <MenuItems class="absolute z-20 top-0 inset-x-0 transition transform origin-top-right lg:hidden shadow-lg ring-1 ring-black/5 focus:outline-none">
                            <div class="shadow-md bg-white ring-1 ring-black ring-opacity-5 overflow-hidden">
                                <div class="pl-4 pt-0 flex items-center justify-between">
                                    <div class="mt-3">
                                        <Link :href="route('2024.home')" class="uppercase font-bold">why culture matters</Link>
                                    </div>
                                    <div class="pt-3 px-3">
                                        <MenuButton
                                            class="bg-white rounded-md p-2 mr-0 sm:mr-1 inline-flex items-center justify-center text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                                            <span class="sr-only">{{ $t('Close main menu') }}</span>
                                            <XIcon class="h-6 w-6" aria-hidden="true"/>
                                        </MenuButton>
                                    </div>
                                </div>
                                <div class="px-2 pt-2 pb-3 space-y-1">
                                    <MenuItem v-for="item in $page.props.navigation" :key="item.name">
                                        <Link :href="item.href" @click.prevent="handleMenuClick(item)"
                                              class="block uppercase px-3 py-2 text-xs font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">{{ $t(item.name) }}</Link>
                                    </MenuItem>
                                    <Link class="block uppercase px-3 py-2 text-xs font-medium text-white bg-red-600 rounded" :href="route('register')">{{ $t('Register') }}</Link>
                                    <a :href="route('2024.locale', {'locale': 'en'})"
                                       type="button"
                                       v-if="$page.props.translation !== 'ro'"
                                       class="inline-flex uppercase px-3 py-2 text-xs font-medium justify-center items-center text-gray-700 rounded cursor-pointer hover:text-gray-900 hover:bg-gray-100 dark:hover:bg-gray-700 dark:hover:text-white"
                                       role="menuitem"><div class="inline-flex items-center">ENGLISH
                                        <svg aria-hidden="true" class="h-5 w-5 rounded-full mx-2"
                                             xmlns="http://www.w3.org/2000/svg" id="flag-icons-gb" viewBox="0 0 512 512">
                                            <path fill="#012169" d="M0 0h512v512H0z"/>
                                            <path fill="#FFF" d="M512 0v64L322 256l190 187v69h-67L254 324 68 512H0v-68l186-187L0 74V0h62l192 188L440 0z"/>
                                            <path fill="#C8102E" d="m184 324 11 34L42 512H0v-3l184-185zm124-12 54 8 150 147v45L308 312zM512 0 320 196l-4-44L466 0h46zM0 1l193 189-59-8L0 49V1z"/>
                                            <path fill="#FFF" d="M176 0v512h160V0H176zM0 176v160h512V176H0z"/>
                                            <path fill="#C8102E" d="M0 208v96h512v-96H0zM208 0v512h96V0h-96z"/>
                                        </svg></div></a>
                                    <a :href="route('2024.locale', {'locale': 'ro'})"
                                       type="button"
                                       v-if="$page.props.translation !== 'en'"
                                       class="inline-flex uppercase px-3 py-2 text-xs font-medium justify-center items-center text-gray-700 rounded cursor-pointer hover:text-gray-900 hover:bg-gray-100"
                                       role="menuitem"><div class="inline-flex items-center">ROMÂNĂ
                                        <svg aria-hidden="true" class="h-5 w-5 rounded-full mx-2"
                                             xmlns="http://www.w3.org/2000/svg" id="flag-icons-ro" viewBox="0 0 512 512">
                                            <g fill-rule="evenodd" stroke-width="1pt">
                                                <path fill="#00319c" d="M0 0h170.7v512H0z"/>
                                                <path fill="#ffde00" d="M170.7 0h170.6v512H170.7z"/>
                                                <path fill="#de2110" d="M341.3 0H512v512H341.3z"/>
                                            </g>
                                        </svg></div></a>
                                </div>
                            </div>
                        </MenuItems>
                    </transition>
                </Menu>
            </nav>
        </div>
    </div>


</template>
