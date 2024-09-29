<script setup>
import { Head } from '@inertiajs/inertia-vue3';

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
            this.gTracker.pageview(this.$route)
            this.gTracker.event(this.$inertia.page.component, {value: this.title})
        }
    }
}
</script>

<template>
    <!-- Page Title -->
    <Head>
        <title>{{ title }}</title>
    </Head>

    <!-- Page Content -->
    <main class="bg-[url('@/../assets/images/wall_2024_1.jpg')] shadow min-h-screen px-2.5 xl:px-0 pb-10 xl:pb-0">
        <slot />
    </main>
</template>
