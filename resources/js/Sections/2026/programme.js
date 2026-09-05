/*
 * PLACEHOLDER PROGRAMME — every time, title and pairing below is invented, so
 * the section can be built and reviewed before the real schedule exists. The
 * guests are real; what they are down to speak about is not. Replace wholesale
 * once the programme is set, and drop the "Draft" marker in Programme.vue.
 *
 * A theme can run over more than one day — III covers days 3 and 4 — and the
 * Heritage School is an umbrella across days 2–4, marked per session with
 * `school: true` rather than belonging to any one day's theme.
 * Titles double as translation keys, as elsewhere on the page.
 */
export const days = [
    {
        key: 'wednesday',
        num: '07',
        day: 1,
        theme: { numeral: 'I', title: 'theme.1.title' },
        sessions: [
            { time: '10:00', kind: '', title: 'Opening', who: 'Asociația Prin Banat' },
            { time: '10:30', kind: 'Talk', title: 'Evacuating a collection under fire', who: 'Oleksandra Kovalchuk' },
            { time: '11:15', kind: 'Talk', title: 'After the factory: industrial heritage at risk', who: 'Raluca-Maria Trifa' },
            { time: '12:00', kind: 'Conversation', title: 'What we lose first', who: 'Gabriela Robeci' },
            { time: '16:00', kind: 'Talk', title: 'Sensing what is left', who: 'Anđela Petrović' },
        ],
    },
    {
        key: 'thursday',
        num: '08',
        day: 2,
        theme: { numeral: 'II', title: 'theme.2.title' },
        sessions: [
            { time: '10:00', kind: 'Talk', title: 'Budapest100: research as public invitation', who: 'Barbara Szij' },
            { time: '10:45', kind: 'Talk', title: 'Twenty years of museum education', who: 'Iulia Iordan' },
            { time: '11:30', kind: 'Conversation', title: 'Learning through place', who: 'Andreea Lazea' },
            { time: '14:00', kind: 'Workshop 1', title: 'Reading a facade', who: 'Children, 8–12', school: true },
            { time: '16:00', kind: 'Workshop 2', title: 'Mapping your street', who: 'Young people', school: true },
        ],
    },
    {
        key: 'friday',
        num: '09',
        day: 3,
        theme: { numeral: 'III', title: 'theme.3.title' },
        sessions: [
            { time: '10:00', kind: 'Talk', title: 'Funeral rites and the stories communities keep', who: 'Nicoleta Mușat' },
            { time: '10:45', kind: 'Talk', title: 'Late modern, badly loved', who: 'András Mudra' },
            { time: '11:30', kind: 'Conversation', title: 'Whose story is it', who: 'Gabriela Robeci' },
            { time: '14:00', kind: 'Workshop 3', title: "Collecting a neighbourhood's voices", who: 'Adults', school: true },
            { time: '16:00', kind: 'Workshop 4', title: 'Objects that carry a family', who: 'All ages', school: true },
        ],
    },
    {
        key: 'saturday',
        num: '10',
        day: 4,
        theme: { numeral: 'III', title: 'theme.3.title' },
        sessions: [
            { time: '10:00', kind: 'Workshop 5', title: 'Drawing the invisible city', who: 'Children, 8–12', school: true },
            { time: '11:30', kind: 'Workshop 6', title: 'A walk that asks questions', who: 'Young people', school: true },
            { time: '14:00', kind: 'Workshop 7', title: 'Teaching heritage without a textbook', who: 'Educators', school: true },
            { time: '16:00', kind: 'Workshop 8', title: 'What we pass on', who: 'All ages', school: true },
            { time: '17:30', kind: 'Closing', title: 'What we choose to protect', who: 'Andreea Lazea, Gabriela Robeci' },
        ],
    },
];
