<script setup>
import {Popover, PopoverButton, PopoverPanel} from '@headlessui/vue'
import {MenuIcon, XIcon} from '@heroicons/vue/outline'

const navigation = [
    {name: 'schedule', href: '#schedule'},
    {name: 'speakers', href: '#speakers'},
    {name: 'venues', href: '#venues'},
    {name: 'sponsors', href: '#sponsors'},
]
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
    <Popover class="fixed top-0 w-full animated z-100"
             :class="{ 'scrolled bg-black': !view.atTopOfPage }">
        <div class="relative mx-auto max-w-7xl py-6 px-5 sm:px-6 lg:px-8 h-1/5">
            <nav class="relative flex items-center justify-between sm:h-10 lg:justify-start"
                 :class="{ '': !view.atTopOfPage }"
                 aria-label="Global">
                <div class="flex items-center flex-grow flex-shrink-0 lg:flex-grow-0">
                    <div class="flex items-center justify-between w-full md:w-auto">
                        <a href="#">
                            <span class="sr-only">Workflow</span>
                            <a href="/public" class="text-red-700 font-bold uppercase">wcm</a>
                        </a>
                        <div class="-mr-2 flex items-center md:hidden">
                            <PopoverButton
                                class="rounded-md p-2 inline-flex items-center justify-center text-white hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                                <span class="sr-only">Open main menu</span>
                                <MenuIcon class="h-6 w-6" aria-hidden="true"/>
                            </PopoverButton>
                        </div>
                    </div>
                </div>
                <div class="hidden md:block md:ml-10 md:pr-4 md:space-x-8">
                    <a v-for="item in navigation" :key="item.name" :href="item.href"
                       class="font-bold uppercase text-white hover:underline">{{ item.name }}</a>
                    <!--                            <a href="#" class="font-medium hover:text-white">login</a>-->
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
                            <a href="/public" class="text-red-700 uppercase font-bold">wcm</a>
                        </div>
                        <div class="-mr-2">
                            <PopoverButton
                                class="bg-white rounded-md p-2 inline-flex items-center justify-center text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                                <span class="sr-only">Close main menu</span>
                                <XIcon class="h-6 w-6" aria-hidden="true"/>
                            </PopoverButton>
                        </div>
                    </div>
                    <div class="px-2 pt-2 pb-3 space-y-1">
                        <a v-for="item in navigation" :key="item.name" :href="item.href"
                           class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">{{
                                item.name
                            }}</a>
                    </div>
                    <!--                            <a href="#" class="block w-full px-5 py-3 text-center font-medium text-indigo-600 bg-gray-50 hover:bg-gray-100"> login </a>-->
                </div>
            </PopoverPanel>
        </transition>
    </Popover>
</template>
