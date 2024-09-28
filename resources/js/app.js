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
import VueGtag from 'vue-gtag';

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
                    id: "G-C3S8YMPKF0",
                    send_page_view: true
                }
            })
            .use(CookieConsent, consentOptions)
            .mount(el);
    },
});

InertiaProgress.init({ color: '#4B5563' });
