/*
 * Does an agenda event collide with what else is on that day?
 *
 * Only ever a warning. Some overlaps are meant — a round table for guests while
 * a workshop runs — so nothing here stops a save; it tells the office what the
 * event sits on top of and leaves the decision with them.
 *
 * Pure, so it can be run on its own: see agendaConflicts.check.mjs.
 */

const minutes = (time) => {
    const [hours, mins] = time.split(':').map(Number);

    return hours * 60 + mins;
};

/*
 * A stretch as [from, to) in minutes. With no end, an event is checked at the
 * moment it starts: how long it runs is not known, and a guess would flag half
 * the morning for a ten-minute briefing.
 */
const span = (start, end) => {
    const from = minutes(start);

    return [from, end ? minutes(end) : from + 1];
};

const clash = (a, b) => a[0] < b[1] && b[0] < a[1];

/**
 * What an event at these times would overlap on this day.
 *
 * @param day       one of the backoffice's days: `busy` (the programme's
 *                  sessions, each with a start and an end) and `events`
 * @param ignoreId  the event being edited, so it is not held against itself
 * @returns         [{ time, title, kind: 'programme' | 'agenda' }]
 */
export function conflictsFor(day, start, end, ignoreId = null) {
    if (! day || ! start) {
        return [];
    }

    const mine = span(start, end);

    // An end before the start is the form's error to report, not a clash.
    if (mine[1] <= mine[0]) {
        return [];
    }

    const sessions = (day.busy || [])
        // A break is free time: a speakers' lunch belongs in the lunch break.
        .filter((session) => ! session.break && clash(mine, span(session.start, session.end)))
        .map((session) => ({
            time: session.start,
            title: session.draft ? `${session.title} (draft)` : session.title,
            kind: 'programme',
        }));

    const events = (day.events || [])
        .filter((event) => event.id !== ignoreId && clash(mine, span(event.starts_at, event.ends_at)))
        .map((event) => ({ time: event.starts_at, title: event.title, kind: 'agenda' }));

    return [...sessions, ...events];
}
