export default defineNuxtConfig({
    compatibilityDate: '2026-08-12',

    devtools: { enabled: true },

    /* El orden importa: base define forma y tipografía, palette los
       colores y components los consume con var(). */
    css: [
        '~/assets/css/base.css',
        '~/assets/css/palette.css',
        '~/assets/css/components.css',
    ],

    runtimeConfig: {
        public: {
            apiBase: 'http://127.0.0.1:8000/api',
            siteUrl: 'http://localhost:3000',
        },
    },

    app: {
        head: {
            htmlAttrs: { lang: 'es' },
            meta: [
                { charset: 'utf-8' },
                { name: 'viewport', content: 'width=device-width, initial-scale=1' },
            ],
            link: [
                { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
                { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
                { rel: 'stylesheet', href: 'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap' },
            ],
        },
    },
})
