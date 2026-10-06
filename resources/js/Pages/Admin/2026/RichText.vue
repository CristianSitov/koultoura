<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';

/*
 * A minimal biography editor: paragraphs, bold / italic / underline, and links.
 * The server keeps only web and mail addresses (http, https, mailto).
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
const inLink = ref(false); // the caret is inside a link

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
    inLink.value = !! linkAtCaret();
    // A link is drawn underlined, which the browser reports as underline;
    // inside one, only a real <u> counts.
    if (inLink.value) {
        const node = window.getSelection()?.anchorNode;
        const element = node?.nodeType === Node.TEXT_NODE ? node.parentElement : node;
        active.value.underline = !! element?.closest?.('u');
    }
}

// The link the caret or selection is in, if any.
function linkAtCaret() {
    const node = window.getSelection()?.anchorNode;
    const element = node?.nodeType === Node.TEXT_NODE ? node.parentElement : node;
    const link = element?.closest?.('a');

    return link && area.value.contains(link) ? link : null;
}

/*
 * The link button. On selected words it asks for the address and links them;
 * with nothing selected it puts the address in as its own link; inside a link
 * it offers to change the address — emptied, the link is removed and the
 * words stay. A bare "www." address is given https.
 */
function link() {
    const selection = window.getSelection();
    const range = selection.rangeCount ? selection.getRangeAt(0).cloneRange() : null;
    const existing = linkAtCaret();

    const answer = window.prompt(
        existing ? 'Link to (leave empty to remove the link):' : 'Link to — a web address (https://…) or mailto:…',
        existing ? existing.getAttribute('href') : 'https://',
    );

    if (answer === null) {
        return;
    }

    // The prompt took the focus; put the selection back where it was.
    area.value.focus();
    if (range) {
        selection.removeAllRanges();
        selection.addRange(range);
    }

    let href = answer.trim();
    if (/^www\./i.test(href)) {
        href = `https://${href}`;
    }

    if (existing) {
        if (! href || href === 'https://') {
            const words = document.createRange();
            words.selectNodeContents(existing);
            selection.removeAllRanges();
            selection.addRange(words);
            document.execCommand('unlink', false);
        } else {
            existing.setAttribute('href', href);
        }
    } else if (href && href !== 'https://') {
        if (selection.isCollapsed) {
            const a = document.createElement('a');
            a.href = href;
            a.textContent = href.replace(/^mailto:/i, '');
            document.execCommand('insertHTML', false, a.outerHTML);
        } else {
            document.execCommand('createLink', false, href);
        }
    }

    sync();
    refreshState();
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
            <button
                type="button"
                aria-label="Link"
                :aria-pressed="inLink"
                :title="inLink ? 'Change or remove this link' : 'Link the selected words (or add an address)'"
                :class="[
                    'flex h-7 w-7 items-center justify-center rounded',
                    inLink ? 'bg-gray-800 text-white' : 'text-gray-600 hover:bg-gray-100',
                ]"
                @mousedown.prevent
                @click="link"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" /><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" /></svg>
            </button>
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
.wcm-richtext :deep(a) {
    color: #b91c1c;
    text-decoration: underline;
}
</style>
