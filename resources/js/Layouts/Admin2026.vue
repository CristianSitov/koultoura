<script setup>
import { Head, Link, usePage } from '@inertiajs/inertia-vue3';
import { computed } from 'vue';

/*
 * The 2026 backoffice chrome. Deliberately plain: this is a working tool used
 * by two or three people, not a page anyone is meant to admire.
 */
defineProps({
    title: { type: String, required: true },
    publicBase: { type: String, default: '' },
});

const page = usePage();
const flash = computed(() => page.props.value.flash);
const user = computed(() => page.props.value.auth?.user);
const current = computed(() => page.url.value);

const nav = [
    { label: 'Overview', href: '/dashboard' },
    { label: 'Speakers', href: '/dashboard/speakers' },
    { label: 'Programme', href: '/dashboard/programme' },
    { label: 'Registrations', href: '/dashboard/registrations' },
    { label: 'Bookings', href: '/dashboard/bookings' },
];

// The overview matches only itself; the rest match their whole subtree.
const isCurrent = (href) => (href === '/dashboard' ? current.value === href : current.value.startsWith(href));
</script>

<template>
    <Head :title="`${title} · WCM 2026`" />

    <div class="min-h-screen bg-gray-100 text-gray-900">
        <header class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-14">
                    <div class="flex items-center gap-8">
                        <Link href="/dashboard" class="font-bold text-red-600 uppercase tracking-wide text-sm">
                            WCM 2026
                        </Link>
                        <nav class="hidden md:flex gap-6 text-sm">
                            <Link
                                v-for="item in nav"
                                :key="item.href"
                                :href="item.href"
                                :class="[
                                    'py-4 border-b-2 -mb-px transition',
                                    isCurrent(item.href)
                                        ? 'border-red-600 text-gray-900 font-semibold'
                                        : 'border-transparent text-gray-500 hover:text-gray-900',
                                ]"
                            >{{ item.label }}</Link>
                        </nav>
                    </div>

                    <div class="flex items-center gap-4 text-sm text-gray-500">
                        <a v-if="publicBase" :href="publicBase" target="_blank" class="hover:text-gray-900">View site ↗</a>
                        <Link href="/dashboard/2024" class="hover:text-gray-900">2024</Link>
                        <Link
                            v-if="user"
                            href="/dashboard/account"
                            :class="isCurrent('/dashboard/account') ? 'text-gray-900 font-semibold' : 'hover:text-gray-900'"
                        >{{ user.email }}</Link>
                        <Link href="/logout" method="post" as="button" type="button" class="hover:text-gray-900">Log out</Link>
                    </div>
                </div>

                <nav class="md:hidden flex gap-4 pb-3 text-sm overflow-x-auto">
                    <Link
                        v-for="item in nav"
                        :key="item.href"
                        :href="item.href"
                        :class="isCurrent(item.href) ? 'text-gray-900 font-semibold' : 'text-gray-500'"
                    >{{ item.label }}</Link>
                </nav>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div v-if="flash" class="mb-6 rounded border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ flash }}
            </div>

            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">{{ title }}</h1>
                <slot name="actions" />
            </div>

            <slot />
        </main>
    </div>
</template>
