// node resources/js/agendaText.check.mjs
import assert from 'node:assert/strict';
import { isMap, linkify, place } from './agendaText.js';

// Maps, and not maps.
for (const url of [
    'https://maps.app.goo.gl/AbC123',
    'https://www.google.com/maps/place/Muzeul+Banatului/@45.75,21.23,17z',
    'https://google.ro/maps?q=Piata+Unirii',
    'https://maps.google.com/?q=45.75,21.22',
    'https://goo.gl/maps/xyz',
    'https://maps.apple.com/?q=Sabres',
    'https://www.openstreetmap.org/#map=18/45.75/21.22',
    'https://waze.com/ul?ll=45.75,21.22',
]) {
    assert.ok(isMap(url), url);
}
for (const url of ['https://www.google.com/search?q=maps', 'https://sabres.ro', 'https://goo.gl/abc', 'notaurl']) {
    assert.ok(! isMap(url), url);
}

// Links in text: the sentence's full stop is not part of the address.
assert.deepEqual(linkify('Menu: https://sabres.ro/menu. Book at www.sabres.ro!'), [
    { text: 'Menu: ' },
    { text: 'https://sabres.ro/menu', href: 'https://sabres.ro/menu', map: false },
    { text: '. Book at ' },
    { text: 'www.sabres.ro', href: 'https://www.sabres.ro', map: false },
    { text: '!' },
]);
assert.equal(linkify('(see https://en.wikipedia.org/wiki/Fabric_(Timișoara))')[1].text, 'https://en.wikipedia.org/wiki/Fabric_(Timișoara)');
assert.equal(linkify('see (https://sabres.ro)')[1].text, 'https://sabres.ro');
assert.deepEqual(linkify(''), []);
assert.deepEqual(linkify(null), []);
// Only web addresses become links — nothing else typed can.
assert.deepEqual(linkify('javascript:alert(1)'), [{ text: 'javascript:alert(1)' }]);

// A map in the description becomes the place; its line goes with it.
assert.deepEqual(
    place('Restaurant Sabres', 'Dinner for speakers.\nMap: https://maps.app.goo.gl/AbC123\nDress code: none.'),
    { name: 'Restaurant Sabres', map: 'https://maps.app.goo.gl/AbC123', description: 'Dinner for speakers.\nDress code: none.' },
);
// Other words on the line stay.
assert.equal(
    place('', 'Meet at the hotel entrance, https://maps.app.goo.gl/AbC123 then walk over.').description,
    'Meet at the hotel entrance, then walk over.',
);
// A map pasted as the location: no name of its own, just the pin.
assert.deepEqual(
    place('https://maps.app.goo.gl/AbC123', 'Brunch.'),
    { name: '', map: 'https://maps.app.goo.gl/AbC123', description: 'Brunch.' },
);
// The location's map wins over one in the description, which then stays a link.
const both = place('Muzeul Banatului https://maps.app.goo.gl/one', 'Also: https://maps.app.goo.gl/two');
assert.equal(both.map, 'https://maps.app.goo.gl/one');
assert.equal(both.name, 'Muzeul Banatului');
assert.equal(both.description, 'Also: https://maps.app.goo.gl/two');
// No map: nothing moves.
assert.deepEqual(place('Hotel lobby', 'See https://sabres.ro'), { name: 'Hotel lobby', map: null, description: 'See https://sabres.ro' });

// A Romanian text without the map takes the English one's.
assert.deepEqual(
    place('', 'Oferită de Prin Banat.', { location: 'Sabres', description: 'Map: https://maps.app.goo.gl/AbC123' }),
    { name: '', map: 'https://maps.app.goo.gl/AbC123', description: 'Oferită de Prin Banat.' },
);
assert.equal(place('Sala mare', '', { location: 'Main hall', description: 'No map here.' }).map, null);

console.log('agendaText: ok');
