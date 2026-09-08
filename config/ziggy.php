<?php

return [
    /*
     * Ziggy ships the whole route table to the browser inside app.js, which
     * every page loads — including the public announcement page. While the
     * landing page's address is unlisted that would publish it to anyone who
     * reads the bundle, so its routes are withheld. Nothing needs them: the
     * page builds its own URLs from the `base` prop the controller passes.
     *
     * Once the edition is public the address is not a secret and the exception
     * only gets in the way. Read from env rather than config() because config
     * files are loaded before the container can resolve one another.
     */
    'except' => env('WCM_2026_PUBLIC', false) ? [] : ['2026.*'],
];
