<script setup>
import { Inertia } from '@inertiajs/inertia';
import { Head } from '@inertiajs/inertia-vue3';
import MainMenu from '@/Sections/MainMenu.vue';
import Bottom from '@/Sections/Bottom.vue';

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
            this.gTracker.event(this.$inertia.page.component)
        }
    },
    watch: {
        '$page.url': function (newUrl, oldUrl) {
            console.log(newUrl)
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
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 h-30 pt-10">
                <slot name="header" />
            </div>
        </header>

        <main>
            <slot />
        </main>

        <Bottom />
    </div>
</template>
