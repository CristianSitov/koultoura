<script setup>
import {Link} from "@inertiajs/inertia-vue3";
import {Popover, PopoverButton, PopoverPanel} from '@headlessui/vue'
import {MenuIcon, XIcon} from '@heroicons/vue/outline'
</script>

<script>
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
            if (window.pageYOffset > 0) {
                if (this.view.atTopOfPage) this.view.atTopOfPage = false
            } else {
                if (!this.view.atTopOfPage) this.view.atTopOfPage = true
            }
        }
    }
}
</script>

<template>
    <Popover class="fixed top-0 w-full animated z-200"
             :class="{ 'scrolled bg-black': !view.atTopOfPage }">
        <div class="max-w-7xl relative mx-auto py-3 px-3 sm:px-4 lg:px-8 h-1/5">
            <nav class="relative flex items-center justify-between sm:h-10"
                 :class="{ '': !view.atTopOfPage }"
                 :data-request="$page.props.detected_ip"
                 aria-label="Global">
                <div class="flex items-center flex-grow flex-shrink-0 lg:flex-grow-0">
                    <div class="flex items-center justify-between w-full md:w-auto">
                        <Link :href="route('home')">
                            <span class="sr-only">why culture matters?</span>
                            <a :href="route('home')" class="text-red-700 uppercase font-bold">why culture matters?</a>
                        </Link>
                        <div class="-mr-2 flex items-center md:hidden">
                            <PopoverButton
                                class="rounded-md p-2 inline-flex items-center justify-center text-white hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                                <span class="sr-only">{{ $t('Open main menu') }}</span>
                                <MenuIcon class="h-6 w-6" aria-hidden="true"/>
                            </PopoverButton>
                        </div>
                    </div>
                </div>
                <div class="hidden flex items-center md:block md:ml-10 md:pr-4 md:space-x-5">
                    <a v-for="item in $page.props.navigation" :key="item.name" :href="item.href"
                       class="font-bold uppercase text-white hover:underline">{{ $t(item.name) }}</a>
                    <Link :href="route('register')"
                          class="font-bold uppercase bg-white text-red-600 p-2 rounded hover:underline">{{ $t('Register') }}</Link>
                    <a :href="route('locale', {'locale': 'en'})"
                       type="button"
                       v-if="$page.props.translation !== 'ro'"
                       class="font-bold uppercase text-white p-2 inline-flex justify-center items-center text-gray-500 rounded cursor-pointer hover:text-gray-900 hover:bg-gray-100 dark:hover:bg-gray-700 dark:hover:text-white"
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
                    <a :href="route('locale', {'locale': 'ro'})"
                       type="button"
                       v-if="$page.props.translation !== 'en'"
                       class="font-bold uppercase text-white p-2 inline-flex justify-center items-center text-gray-500 rounded cursor-pointer hover:text-gray-900 hover:bg-gray-100"
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
            </nav>
        </div>

        <transition enter-active-class="duration-150 ease-out" enter-from-class="opacity-0 scale-95"
                    enter-to-class="opacity-100 scale-100" leave-active-class="duration-100 ease-in"
                    leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <PopoverPanel focus
                          class="absolute z-20 top-0 inset-x-0 transition transform origin-top-right md:hidden">
                <div class="shadow-md bg-white ring-1 ring-black ring-opacity-5 overflow-hidden">
                    <div class="pt-6 px-5 flex items-center justify-between">
                        <div>
                            <Link :href="route('home')" class="text-red-700 uppercase font-bold">why culture matters?</Link>
                        </div>
                        <div class="-mr-2">
                            <PopoverButton
                                class="bg-white rounded-md p-2 inline-flex items-center justify-center text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                                <span class="sr-only">{{ $t('Close main menu') }}</span>
                                <XIcon class="h-6 w-6" aria-hidden="true"/>
                            </PopoverButton>
                        </div>
                    </div>
                    <div class="px-2 pt-2 pb-3 space-y-1">
                        <a v-for="item in $page.props.navigation" :key="item.name" :href="item.href"
                           class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">{{ $t(item.name) }}</a>
                        <Link class="block px-3 py-2 font-bold uppercase bg-red-600 text-white rounded" :href="route('register')">{{ $t('Register') }}</Link>
                        <a :href="route('locale', {'locale': 'en'})"
                           type="button"
                           v-if="$page.props.translation !== 'ro'"
                           class="font-bold uppercase px-3 py-2 inline-flex justify-center items-center text-gray-500 rounded cursor-pointer hover:text-gray-900 hover:bg-gray-100 dark:hover:bg-gray-700 dark:hover:text-white"
                           role="menuitem"><div class="inline-flex items-center">ENGLISH
                            <svg aria-hidden="true" class="h-5 w-5 rounded-full mx-2"
                                 xmlns="http://www.w3.org/2000/svg" id="flag-icons-gb" viewBox="0 0 512 512">
                                <path fill="#012169" d="M0 0h512v512H0z"/>
                                <path fill="#FFF" d="M512 0v64L322 256l190 187v69h-67L254 324 68 512H0v-68l186-187L0 74V0h62l192 188L440 0z"/>
                                <path fill="#C8102E" d="m184 324 11 34L42 512H0v-3l184-185zm124-12 54 8 150 147v45L308 312zM512 0 320 196l-4-44L466 0h46zM0 1l193 189-59-8L0 49V1z"/>
                                <path fill="#FFF" d="M176 0v512h160V0H176zM0 176v160h512V176H0z"/>
                                <path fill="#C8102E" d="M0 208v96h512v-96H0zM208 0v512h96V0h-96z"/>
                            </svg></div></a>
                        <a :href="route('locale', {'locale': 'ro'})"
                           type="button"
                           v-if="$page.props.translation !== 'en'"
                           class="font-bold uppercase px-3 py-2 inline-flex justify-center items-center text-gray-500 rounded cursor-pointer hover:text-gray-900 hover:bg-gray-100"
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
            </PopoverPanel>
        </transition>
    </Popover>
</template>
