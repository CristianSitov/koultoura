import './bootstrap';
import '../css/app.css';
import '/node_modules/vue-cookieconsent/vendor/cookieconsent.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/inertia-vue3';
import { InertiaProgress } from '@inertiajs/progress';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { i18nVue } from 'laravel-vue-i18n'
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';
import { Ziggy } from './ziggy';
import CookieConsent from 'vue-cookieconsent';
import { consentOptions } from './consent';
import VueGtag, { optIn, optOut } from 'vue-gtag';
import emitter from './emitter';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Why Culture Matters?';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, app, props, plugin }) {
        return createApp({ render: () => h(app, props) })
            .use(plugin)
            .use(i18nVue, {
                resolve: async lang => {
                    const langs = import.meta.glob('../../lang/*.json');
                    return await langs[`../../lang/${lang}.json`]();
                }
            })
            .use(ZiggyVue, Ziggy)
            .mixin({methods: { route }})
            .use(VueGtag, {
                enabled: false,
                config: {
                    id: "G-WYGPJKWNT1",
                    send_page_view: true,
                    debug_mode: true
                }
            })
            .use(CookieConsent, consentOptions)
            .mount(el);
    },
});

InertiaProgress.init({ color: '#4B5563' });

/*
 * Analytics is off until it is consented to — `enabled: false` above is the
 * default, and this turns it on.
 *
 * Only when the analytics category itself is accepted: the banner's "Reject"
 * button still fires onAccept, with necessary alone, and the pages that used to
 * do this each called optIn() on any accept at all — so declining analytics
 * switched analytics on. Reading the level fixes that for every year's pages at
 * once, which is also why this lives here rather than in three layouts.
 */
emitter.on('consentAccepted', ({ consent }) => {
    const levels = Array.isArray(consent) ? consent : [consent];

    levels.includes('analytics') ? optIn() : optOut();
});
