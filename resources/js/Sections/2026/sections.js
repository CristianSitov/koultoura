/*
 * The landing-page sections and their address in each language. The `id` is the
 * DOM anchor (English, the same in both languages, so scrollIntoView and the
 * menu keep working); `en`/`ro` are the slugs that appear in the URL. The
 * server keeps the matching map — see Front2026Controller::SECTIONS.
 */
export const SECTIONS = [
    { id: 'about', en: 'about', ro: 'despre' },
    { id: 'format', en: 'format', ro: 'format' },
    { id: 'themes', en: 'themes', ro: 'teme' },
    { id: 'speakers', en: 'speakers', ro: 'invitati' },
    { id: 'programme', en: 'programme', ro: 'program' },
    { id: 'location', en: 'location', ro: 'locatie' },
    { id: 'partners', en: 'partners', ro: 'parteneri' },
];

/** The URL slug for a section id in a language, falling back to the id. */
export function sectionSlug(id, locale) {
    const s = SECTIONS.find((x) => x.id === id);

    return s ? (s[locale] ?? s.en) : id;
}
