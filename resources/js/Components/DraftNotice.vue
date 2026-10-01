<script setup>
/* Shown in an edit window that came back with unsaved changes — see formDraft.js. */
defineProps({ draft: { type: Object, required: true } });

const when = (date) => date.toLocaleString('en-GB', { weekday: 'short', day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
</script>

<template>
    <p v-if="draft.restored.value" class="flex flex-wrap items-center justify-between gap-2 rounded border border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-900">
        <span>Your unsaved changes from {{ when(draft.restored.value) }} are back — not saved yet.</span>
        <button type="button" class="text-amber-800 underline hover:text-amber-950" @click="draft.discard()">Discard them</button>
    </p>
</template>
