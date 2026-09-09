import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import i18n from 'laravel-vue-i18n/vite';

export default defineConfig({
    build: {
        /*
         * Do not empty public/build.
         *
         * A deploy rebuilds in place, on the tree nginx is serving. Wiping the
         * directory first means that for the twenty-odd seconds the build
         * takes, a browser that loaded a page before the deploy asks for a
         * chunk that no longer exists — and gets nothing. That is not
         * hypothetical: it happened to somebody halfway through registering.
         *
         * Leaving the old files be, they keep answering until the manifest
         * points elsewhere. The Envoy task prunes what is no longer referenced.
         */
        emptyOutDir: false,
    },
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        i18n(),
    ],
    server: {
        https: false,
        hmr: {
            host: 'localhost',
            protocol: 'ws'
        }
    }
});
