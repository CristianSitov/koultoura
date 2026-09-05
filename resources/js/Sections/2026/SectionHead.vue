<script setup>
defineProps({
    n: { type: String, required: true },
    title: { type: String, required: true },
});
</script>

<template>
    <div class="wcm26-head">
        <span class="wcm26-head-n">{{ n }}</span>
        <h2 class="wcm26-head-t">{{ title }}</h2>
    </div>
</template>
