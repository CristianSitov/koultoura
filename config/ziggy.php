<?php

return [
    /*
     * Ziggy ships the whole route table to the browser inside app.js, which
     * every page loads — including the public announcement page. That would
     * publish the landing page's unlisted address to anyone who reads the
     * bundle, so its routes are withheld. Nothing needs them: the page builds
     * its own URLs from the `base` prop the controller passes.
     *
     * Drop this when the page goes live and its address stops being a secret.
     */
    'except' => ['2026.*'],
];
