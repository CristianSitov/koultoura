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
 * Support us is not one of the sections: it is a page of its own, so its
 * address hangs off the edition's base rather than the landing page's, and it
 * carries the arrow the Support page gives its own heading rather than a
 * number it has no place in.
 *
 * It is in the menu because the bar drops it below 1100px — on a phone the
 * menu is the only place it exists.
 */
export const menuSupport = { n: '→', label: 'Support us', href: '/support' };
