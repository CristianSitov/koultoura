<?php

return [
    /*
     * The 2026 edition's two switches. Both live here so going live is a change
     * of environment rather than a change of code — see docs/2026-go-live.md.
     */

    /*
     * The segment the 2026 site is served under. Unlisted while the page is a
     * preview: an unguessable word means a crawler walking the obvious years
     * finds nothing. Set it to '2026' to go live.
     *
     * Every address on the site is built from this — routes, the locale switch,
     * the guest pages, the registration links and the ones already posted
     * inside confirmation emails.
     */
    'path' => env('WCM_2026_PATH', '2026-mulberry'),

    /*
     * Whether the edition is public. Off: the landing page tells search engines
     * not to index it, its routes are withheld from the Ziggy table shipped in
     * app.js, and `/` shows the announcement page. On: all three reverse.
     */
    'public' => (bool) env('WCM_2026_PUBLIC', false),

    /*
     * Addresses the site used to answer on. Every prefix listed here redirects
     * to the current one, so links already posted or emailed keep working after
     * the segment changes.
     */
    'old_paths' => array_filter(array_map('trim', explode(',', (string) env('WCM_2026_OLD_PATHS', '')))),
];
