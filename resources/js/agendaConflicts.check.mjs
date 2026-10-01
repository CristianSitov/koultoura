/*
 * The check for agendaConflicts.js. No framework — run it:
 *
 *     node resources/js/agendaConflicts.check.mjs
 *
 * It exits non-zero on the first thing that is wrong.
 */
import assert from 'node:assert/strict';
import { conflictsFor } from './agendaConflicts.js';

// A morning the way the programme records it: starts only, each session
// running until the next (the backoffice works those ends out).
const day = {
    busy: [
        { title: 'Welcome', start: '10:00', end: '10:20', break: false, draft: false },
        { title: 'Case study', start: '10:20', end: '11:15', break: false, draft: false },
        { title: 'Coffee Break', start: '11:15', end: '11:35', break: true, draft: false },
        { title: 'Talk', start: '11:35', end: '12:30', break: false, draft: true },
        { title: 'Lunch Break', start: '12:30', end: '13:30', break: true, draft: false },
        { title: 'Afternoon talk', start: '13:30', end: '14:00', break: false, draft: false },
    ],
    events: [
        { id: 1, title: 'Briefing', starts_at: '08:30', ends_at: '09:00' },
        { id: 2, title: 'Dinner', starts_at: '20:00', ends_at: '' },
    ],
};

const titles = (start, end, ignore) => conflictsFor(day, start, end, ignore).map((c) => c.title);

// Clear of everything.
assert.deepEqual(titles('09:00', '10:00'), [], 'ending as a session starts is not a clash');
assert.deepEqual(titles('14:00', '15:00'), [], 'starting as a session ends is not a clash');
assert.deepEqual(titles('16:00', '17:00'), []);

// On top of sessions.
assert.deepEqual(titles('10:10', '10:30'), ['Welcome', 'Case study'], 'spans two sessions');
assert.deepEqual(titles('10:25', '10:40'), ['Case study'], 'inside one session');
assert.deepEqual(titles('09:00', '18:00').length, 4, 'covers every session, but no break');

// Breaks are free time.
assert.deepEqual(titles('11:15', '11:35'), [], 'exactly the coffee break');
assert.deepEqual(titles('12:30', '13:30'), [], 'exactly the lunch break');
assert.deepEqual(titles('13:00', '14:00'), ['Afternoon talk'], 'a lunch that runs past the break is flagged for what follows');

// A draft session still counts, and says what it is.
assert.deepEqual(titles('11:40', '12:00'), ['Talk (draft)']);

// No end: checked at the moment it starts.
assert.deepEqual(titles('10:05', ''), ['Welcome'], 'starts mid-session');
assert.deepEqual(titles('09:50', ''), [], 'starts before a session, length unknown');
assert.deepEqual(titles('10:20', ''), ['Case study'], 'starts with a session');

// Other agenda events.
assert.deepEqual(titles('08:45', '09:15'), ['Briefing']);
assert.deepEqual(titles('08:45', '09:15', 1), [], 'an event is not held against itself');
assert.deepEqual(titles('19:30', '21:00'), ['Dinner'], 'over an event with no end');
assert.equal(conflictsFor(day, '08:45', '09:15')[0].kind, 'agenda');
assert.equal(conflictsFor(day, '10:25', '10:40')[0].kind, 'programme');

// Nothing to check, or nothing sensible to check.
assert.deepEqual(titles('', ''), [], 'no start yet');
assert.deepEqual(titles('15:00', '14:00'), [], 'end before start is the form\'s error, not a clash');
assert.deepEqual(conflictsFor(undefined, '10:00', '11:00'), [], 'no day picked');
assert.deepEqual(conflictsFor({ busy: [], events: [] }, '10:00', '11:00'), [], 'a day either side of the symposium');

console.log('agendaConflicts: all checks pass');
