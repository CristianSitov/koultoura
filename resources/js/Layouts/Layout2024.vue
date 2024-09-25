<script setup>
import { Head } from '@inertiajs/inertia-vue3';
import Bottom from "../Sections/2024/Bottom.vue";

defineProps({
    title: String,
});

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

        <!-- Page Heading -->
        <header v-if="$slots.header" class="bg-no-repeat bg-[url('@/../assets/images/wall_2024_1.jpg')] shadow">
            <div class="max-w-7xl mx-auto flex flex-row py-6 px-4 sm:px-6 lg:px-8 h-30 pt-5">
                <slot name="header" />
                <div class="grow"></div>
            </div>
        </header>

        <main class="bg-[url('@/../assets/images/wall_2024_1.jpg')] shadow min-h-screen">
            <slot />
        </main>

        <Bottom />
    </div>
</template>
