import { trans } from 'laravel-vue-i18n';

/*
 * Session kinds are free text, and the same one gets typed a few ways —
 * "Case study" and "Case Study", a stray space, spaces around the slash. The
 * database's collation hides those from a DISTINCT but $t() sees them, so a
 * variant that does not match its key byte-for-byte falls back to English.
 *
 * So the kind is normalised before it is translated: lower-cased, whitespace
 * collapsed, slashes tightened. Anything that normalises to one of the known
 * kinds is translated and shown in the known kind's own casing; anything else
 * is shown as it was typed.
 */
const KINDS = [
    'Opening',
    'Presentation / Case Study',
    'Workshop',
    'Conversation',
    'Closing Conversation',
    'Why Culture Matters+',
];

const normalise = (value) =>
    (value || '')
        .trim()
        .toLowerCase()
        .replace(/\s+/g, ' ')
        .replace(/\s*\/\s*/g, '/');

const byNormal = Object.fromEntries(KINDS.map((kind) => [normalise(kind), kind]));

export function translateKind(kind) {
    const canonical = byNormal[normalise(kind)];

    return canonical ? trans(canonical) : (kind || '');
}

export const knownKinds = KINDS;
