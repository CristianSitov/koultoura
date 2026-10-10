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
import VueGtag, { event, optIn, optOut, pageview } from 'vue-gtag';
import { Inertia } from '@inertiajs/inertia';
import emitter from './emitter';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Why Culture Matters?';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, app, props, plugin }) {
        const vm = createApp({ render: () => h(app, props) })
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
                    // The page a visit lands on. vue-gtag reads this from
                    // `params` only — set beside `id`, as it was until 10
                    // October 2026, it was ignored and no landing page was
                    // ever sent. Later pages are Inertia visits, which GA's
                    // own "browser history" page views count.
                    params: { send_page_view: true },
                }
            })
            .use(CookieConsent, consentOptions)
            .mount(el);

        /*
         * The banner has to be started. Only the 2022 and 2024 pages ever did
         * it, so on the 2026 pages nobody was ever asked — and analytics, which
         * waits for that answer, could never be turned on. Started here so
         * every page gets it, once, rather than in each layout.
         */
        vm.$cc.run(consentOptions);

        return vm;
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
const allowsAnalytics = (consent) => (Array.isArray(consent) ? consent : [consent]).includes('analytics');

emitter.on('consentAccepted', ({ consent }) => (allowsAnalytics(consent) ? optIn() : optOut()));

/*
 * A first visit: its landing page was sent while analytics was still off, so
 * it never counted. Once the visitor says yes, the page they are on is sent.
 * (A returning visitor's landing page counts by itself: their answer is known
 * before Google's script runs.)
 */
emitter.on('consentFirstAnswer', ({ consent }) => {
    if (allowsAnalytics(consent)) {
        optIn();
        pageview({ page_path: window.location.pathname, page_location: window.location.href, page_title: document.title });
    }
});

/*
 * What happened, for Analytics: a registration (sign_up) or a workshop place
 * (book_workshop). The server flashes it once, with the page that follows the
 * form, so a reload does not count it twice. Like every hit, it goes nowhere
 * unless analytics was consented to.
 */
Inertia.on('navigate', ({ detail }) => {
    const happened = detail.page.props.ga_event;

    if (happened?.name) {
        event(happened.name, happened.params || {});
    }
});
