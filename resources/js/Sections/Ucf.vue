<template>
    <div>
        <button class="w-full h-12
            fixed left-0 bottom-0
            flex justify-center items-center
            bg-yellow-300 text-blue-600
            uppercase font-bold text-sm"
            @click="open = true">
            {{ $t('Help Ukrainian culture now!') }}
        </button>
    </div>
    <TransitionRoot as="template" :show="open">
        <Dialog as="div" class="relative z-10" @close="open = false">
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
            </TransitionChild>

            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200" leave-from="opacity-100 translate-y-0 sm:scale-100" leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg lg:max-w-4xl">
                            <section class="bg-white">
                                <div class="grid max-w-screen-xl p-4 mx-auto lg:gap-8 xl:gap-0 lg:grid-cols-12">
                                    <div class="mr-auto place-self-center lg:col-span-7 order-2 lg:order-1">
                                        <h1 class="max-w-2xl mb-4 text-4xl text-blue-700 font-extrabold tracking-tight leading-none md:text-5xl xl:text-6xl mr-3" v-html="$t('ucf title')"></h1>
                                        <p class="max-w-2xl mb-6 font-light text-gray-500 lg:mb-8 md:text-lg text-sm lg:text-md mr-3">{{ $t('ucf description') }}</p>
                                        <div class="bg-gray-50 sm:flex">
                                            <a href="https://lnk.charityhero.live/ucf-eu-web" target="_blank"
                                               class="inline-flex items-center justify-center p-2 mr-2 text-base font-bold text-center text-blue-600 hover:text-yellow-300 rounded-lg bg-yellow-300 hover:bg-blue-600 border-2 border-yellow-300">
                                                {{ $t('ucf cta') }}
                                            </a>
                                            <button type="button"
                                                    class="inline-flex items-center justify-center p-2 mr-2 text-base font-bold text-center text-red-600 hover:text-white rounded-lg bg-white hover:bg-red-600 border-2 border-red-600" @click="open = false" ref="cancelButtonRef">{{ $t('close') }}</button>
                                        </div>

                                    </div>
                                    <div class="mb-5 lg:mt-0 lg:col-span-5 order-1 lg:order-2">
                                        <img src="assets/images/ucf_1080_1080.png" alt="Help to save Ukrainian culture now!" class="rounded-xl shadow-lg">
                                    </div>
                                </div>
                            </section>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>
import { ref } from 'vue'
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue'

const open = ref(false)
</script>
