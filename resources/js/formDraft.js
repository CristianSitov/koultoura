import { ref, watch } from 'vue';

/*
 * Unsaved work in a backoffice edit window, kept in this browser until it is
 * saved. Closing the window — Escape, a click beside it, Cancel, a reload, a
 * crash — no longer throws away what was typed: opening the same thing again
 * brings it back, with a notice and a way to discard it.
 *
 * Only this browser keeps it (localStorage), and only for a fortnight. Files
 * picked for upload cannot be kept and are left out.
 *
 *     const draft = useDraft(form, 'agenda-event');
 *     // after the form is filled for what is being edited:
 *     draft.start(event?.id ?? 'new');
 *     // once the server has it:
 *     onSuccess: () => draft.finish()
 */
const PREFIX = 'wcm-draft:';
const KEEP_MS = 14 * 24 * 60 * 60 * 1000;

const plain = (data) => JSON.stringify(data, (key, value) => (value instanceof Blob ? undefined : value));

// Storage can be missing or refuse (private windows, a full disk): then there
// is simply no draft, as before.
function read(key) {
    try {
        const saved = JSON.parse(localStorage.getItem(key));

        return saved && Date.now() - saved.at < KEEP_MS ? saved : null;
    } catch {
        return null;
    }
}

function write(key, value) {
    try {
        localStorage.setItem(key, JSON.stringify(value));
    } catch {
        // Nowhere to keep it; the form still works.
    }
}

function remove(key) {
    try {
        localStorage.removeItem(key);
    } catch {
        // As above.
    }
}

// Put kept values back. A nested group (a new moderator) is merged, so a
// file still picked in it is not wiped by its absence from the draft.
function fill(form, json) {
    for (const [field, value] of Object.entries(JSON.parse(json))) {
        if (! (field in form)) {
            continue;
        }
        const group = value && typeof value === 'object' && ! Array.isArray(value)
            && form[field] && typeof form[field] === 'object';

        form[field] = group ? { ...form[field], ...value } : value;
    }
}

export function useDraft(form, name) {
    const restored = ref(null); // when the draft brought back was written
    let key = null;
    let baseline = null;
    let stop = null;

    function start(id) {
        stop?.();
        key = `${PREFIX}${name}:${id}`;
        baseline = plain(form.data());
        restored.value = null;

        const saved = read(key);

        if (saved) {
            fill(form, saved.data);
            restored.value = new Date(saved.at);
        }

        // Back to how it was opened means nothing is unsaved: no draft.
        stop = watch(() => plain(form.data()), (now) => (now === baseline
            ? remove(key)
            : write(key, { at: Date.now(), data: now })));
    }

    function discard() {
        fill(form, baseline);
        remove(key);
        restored.value = null;
    }

    function finish() {
        stop?.();
        stop = null;
        remove(key);
        restored.value = null;
    }

    return { restored, start, discard, finish };
}
