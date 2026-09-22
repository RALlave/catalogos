export default defineNuxtConfig({
    compatibilityDate: '2026-08-12',

    devtools: { enabled: false },

    /* El orden importa: base define forma y tipografía, palette los
       colores y components los consume con var(). */
    css: [
        '~/assets/css/base.css',
        '~/assets/css/palette.css',
        '~/assets/css/components.css',
    ],

    /* `baseDomain` es el dominio de la plataforma, sin tienda: el catálogo
       saca el slug del subdominio que sobra en el host. En desarrollo se usa
       lvh.me, que resuelve a 127.0.0.1 sin tocar el archivo hosts.

       No hay `siteUrl`: con una tienda por subdominio, una URL fija sería la
       de otra tienda. La absoluta sale del host (`useSiteUrl`).

       `panelPort` sólo se llena en desarrollo, donde el panel corre en otro
       puerto; en producción comparte origen con el catálogo. */
    runtimeConfig: {
        public: {
            apiBase: 'http://127.0.0.1:8000/api',
            baseDomain: 'lvh.me:3000',
            panelPort: '',
        },
    },

    /* Cada tienda se abre en su subdominio, también en desarrollo
       (rex.lvh.me:3000). Vite bloquea los hosts que no conoce para protegerse
       del DNS rebinding, así que hay que nombrarlos. */
    vite: {
        server: {
            allowedHosts: ['.lvh.me'],
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
