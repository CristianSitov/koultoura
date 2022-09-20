<script setup>
import { Link } from '@inertiajs/inertia-vue3';
defineProps({
    title: String,
});
</script>

<template>
    <div class="text-center my-8">
        <Link :href="'/'">
            <span class="text-3xl text-red-600 font-sans font-bold uppercase">why culture matters?</span>
        </Link>
        <span class="text-lg block font-sans font-bold uppercase">{{ $t(title) }}</span>
    </div>
</template>
