/*
 * The agenda's typed text, made readable: web addresses become links, and a
 * map link is taken as where the event is — a restaurant, a museum — rather
 * than printed as a long address. Plain text in, plain parts out; the page
 * renders the parts itself, so nothing typed is ever treated as HTML.
 *
 * Checked by `node resources/js/agendaText.check.mjs`.
 */

const URL_RE = /\b(?:https?:\/\/|www\.)[^\s<>"]+/gi;

// "…see https://x.ro/a." — the full stop ends the sentence, not the address.
function trim(url) {
    let end = url;

    while (/[.,;:!?'’)\]]$/.test(end)) {
        // A closing bracket the address opened itself stays (Wikipedia does that).
        if (end.endsWith(')') && (end.match(/\(/g) || []).length >= (end.match(/\)/g) || []).length) {
            break;
        }
        end = end.slice(0, -1);
    }

    return end;
}

const href = (url) => (/^www\./i.test(url) ? `https://${url}` : url);

/** Whether an address is a map: Google, Apple, OpenStreetMap or Waze. */
export function isMap(url) {
    let u;

    try {
        u = new URL(href(url));
    } catch {
        return false;
    }

    const host = u.hostname.toLowerCase().replace(/^www\./, '');

    return host === 'maps.app.goo.gl'
        || /^maps\.google\.[a-z.]+$/.test(host)
        || (/^google\.[a-z.]+$/.test(host) && u.pathname.startsWith('/maps'))
        || (host === 'goo.gl' && u.pathname.startsWith('/maps'))
        || host === 'maps.apple.com'
        || host === 'openstreetmap.org' || host.endsWith('.openstreetmap.org')
        || host === 'waze.com' || host.endsWith('.waze.com');
}

/** Text as parts: `{ text }`, or `{ text, href, map }` for an address. */
export function linkify(text) {
    const parts = [];
    let last = 0;

    for (const match of (text || '').matchAll(URL_RE)) {
        const url = trim(match[0]);

        if (match.index > last) {
            parts.push({ text: text.slice(last, match.index) });
        }
        parts.push({ text: url, href: href(url), map: isMap(url) });
        last = match.index + url.length;
    }

    if (last < (text || '').length) {
        parts.push({ text: text.slice(last) });
    }

    return parts;
}

/*
 * An event's place: its name, the map it points at, and the description left
 * once that map is taken out of it. The first map link wins, from the location
 * first, then the description. In the description, a line that held only the
 * link — or a label for it, "Map:" — goes with it; other words on it stay.
 * `english` is the same event's English text, for a translation without a map.
 */
export function place(location, description, english = null) {
    const map = [...linkify(location), ...linkify(description)].find((part) => part.map) ?? null;

    if (! map) {
        // The Romanian left the map out: the English one still says where it is.
        const elsewhere = english ? place(english.location, english.description).map : null;

        return { name: (location || '').trim(), map: elsewhere, description: description || '' };
    }

    // In the location, what is left is the place's name, however short; in the
    // description a short remainder is the link's label, and goes with it.
    const without = (text, labels) => (text || '')
        .split('\n')
        .flatMap((line) => {
            if (! line.includes(map.text)) {
                return [line];
            }
            const rest = line.replace(map.text, '').replace(/[ \t]{2,}/g, ' ').trim();

            return labels && /^[\p{L}\s]{0,24}[:\-–—]?$/u.test(rest) ? [] : [rest];
        })
        .join('\n')
        .trim();

    return { name: without(location, false), map: map.href, description: without(description, true) };
}
