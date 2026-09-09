/* The seven page sections, in order — shared by the menu overlay. The label is
   a translation key; the anchors are the same in every language. */
export const menuItems = [
    { n: '01', label: 'About', href: '#about' },
    { n: '02', label: 'Format', href: '#format' },
    { n: '03', label: 'Themes', href: '#themes' },
    { n: '04', label: 'Guests', href: '#speakers' },
    { n: '05', label: 'Programme', href: '#programme' },
    { n: '06', label: 'Location', href: '#location' },
    { n: '07', label: 'Partners', href: '#partners' },
];

/*
 * The two pages of their own. Not sections, so their addresses hang off the
 * edition's base rather than the landing page's, and they carry an arrow
 * instead of a number they have no place in.
 *
 * They are rows of the menu rather than buttons beside it: a menu is a list of
 * places to go, and these are two more places.
 */
export const menuPages = [
    { n: '→', label: 'register.submit', href: '/register' },
    { n: '→', label: 'Support us', href: '/support', support: true },
];
