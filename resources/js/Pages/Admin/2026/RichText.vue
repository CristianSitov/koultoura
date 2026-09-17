<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';

/*
 * A minimal biography editor: paragraphs, and bold / italic / underline.
 *
 * It is a contenteditable driven by document.execCommand — deprecated, but
 * every browser still runs it, and it is a few lines against a formatting
 * library for three buttons. The server keeps the last word: whatever this
 * emits is re-cleaned by App\Support\HtmlBio before it is stored or shown, so
 * the editor only has to be convenient, not trusted.
 *
 * ponytail: execCommand is the deprecated-but-universal path; reach for a
 * ProseMirror/Tiptap editor only if this grows past a handful of marks.
 */
const props = defineProps({
    modelValue: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue']);

const area = ref(null);
const active = ref({ bold: false, italic: false, underline: false });

const marks = [
    { cmd: 'bold', label: 'Bold', glyph: 'B', style: 'font-weight:700' },
    { cmd: 'italic', label: 'Italic', glyph: 'I', style: 'font-style:italic' },
    { cmd: 'underline', label: 'Underline', glyph: 'U', style: 'text-decoration:underline' },
];

function sync() {
    emit('update:modelValue', area.value.innerHTML);
}

function refreshState() {
    if (! area.value) {
        return;
    }
    // Only while the caret is in this editor, or every editor on the page lights
    // its buttons from the same global selection.
    if (! area.value.contains(document.activeElement)) {
        return;
    }
    for (const { cmd } of marks) {
        active.value[cmd] = document.queryCommandState(cmd);
    }
}

function run(cmd) {
    area.value.focus();
    document.execCommand(cmd, false);
    sync();
    refreshState();
}

// Paste as plain text: styled HTML off the clipboard would arrive full of spans
// and inline colour the server only strips again — and the editor would show
// the junk until then.
function onPaste(event) {
    event.preventDefault();
    const text = (event.clipboardData || window.clipboardData).getData('text/plain');
    document.execCommand('insertText', false, text);
}

onMounted(() => {
    area.value.innerHTML = props.modelValue || '';
    // Enter makes a new paragraph, not a <div>.
    try {
        document.execCommand('defaultParagraphSeparator', false, 'p');
    } catch (e) {
        // Not every engine exposes it; the default block is still fine.
    }
    document.addEventListener('selectionchange', refreshState);
});

onBeforeUnmount(() => document.removeEventListener('selectionchange', refreshState));
</script>

<template>
    <div class="rounded border border-gray-300 focus-within:border-red-400 focus-within:ring-1 focus-within:ring-red-400">
        <div class="flex gap-1 border-b border-gray-200 px-2 py-1.5">
            <button
                v-for="mark in marks"
                :key="mark.cmd"
                type="button"
                :aria-label="mark.label"
                :aria-pressed="active[mark.cmd]"
                :class="[
                    'h-7 w-7 rounded text-sm leading-none',
                    active[mark.cmd] ? 'bg-gray-800 text-white' : 'text-gray-600 hover:bg-gray-100',
                ]"
                :style="mark.style"
                @mousedown.prevent
                @click="run(mark.cmd)"
            >{{ mark.glyph }}</button>
        </div>

        <div
            ref="area"
            contenteditable="true"
            class="wcm-richtext min-h-[7rem] px-3 py-2 text-sm leading-relaxed focus:outline-none"
            @input="sync"
            @paste="onPaste"
            @blur="sync"
        ></div>
    </div>
</template>

<style scoped>
.wcm-richtext :deep(p) {
    margin: 0 0 0.75em;
}
.wcm-richtext :deep(p:last-child) {
    margin-bottom: 0;
}
</style>
