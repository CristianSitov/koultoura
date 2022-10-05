<script setup>
import { Inertia } from '@inertiajs/inertia';
import { Head, Link, usePage } from '@inertiajs/inertia-vue3';
import MainMenu from '@/Sections/MainMenu.vue';
import Bottom from '@/Sections/Bottom.vue';
import Ucf from '@/Sections/Ucf.vue';

defineProps({
    title: String,
});

const logout = () => {
    Inertia.post(route('logout'));
};
</script>
<script>
import { consentOptions } from "../consent";
import emitter from "../emitter";

export default {
    beforeCreate() {
        this.$cc.on('consent-changed', () => {
            console.log('cookie consent changed, new user preferences:',
                this.$cc.getUserPreferences())
        })
    },
    created() {
        this.$cc.run(consentOptions);
        emitter.on("consentAccepted", () => this.$gtag.optIn());
    },
    mounted() {
        this.track()
    },
    data() {
        return {
            gTracker: this.$gtag
        }
    },
    methods: {
        track() {
            this.gTracker.pageview(this.$inertia.page.url)
            this.gTracker.event(this.$inertia.page.component, {value: this.title})
        }
    }
}
</script>

<template>
    <div>
        <Head>
            <title>{{ title }}</title>
        </Head>

        <MainMenu />

        <!-- Page Heading -->
        <header v-if="$slots.header" class="bg-cover bg-no-repeat bg-[url('@/../assets/images/wall_1.jpg')] shadow">
            <div class="max-w-7xl mx-auto flex flex-row pt-20 py-6 px-4 sm:px-6 lg:px-8 h-30 pt-10">
                <slot name="header" />
                <div class="grow"></div>
                <div class="flex-none"
                     v-if="usePage().props.value.user?.email_verified_at">
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="px-4 py-2 rounded-md font-semibold text-xs text-red-600 uppercase tracking-widest bg-white hover:bg-red-800 hover:text-white ml-2"
                    >Logout</Link>
                </div>
            </div>
        </header>

        <main>
            <slot />
        </main>

        <Bottom />

        <Ucf />
    </div>
</template>
