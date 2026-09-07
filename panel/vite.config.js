import { fileURLToPath, URL } from 'node:url'

import vue from '@vitejs/plugin-vue'
import { defineConfig, loadEnv } from 'vite'

/*
 * En local el panel vive en la raíz (/). En producción cuelga de /panel/,
 * y eso se define con VITE_BASE en el .env de producción para no tener que
 * tocar este archivo ni el entorno local.
 */
export default defineConfig(({ mode }) => ({
    base: loadEnv(mode, process.cwd(), '').VITE_BASE ?? '/',

    plugins: [vue()],

    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./src', import.meta.url)),
        },
    },

    server: {
        port: 5173,

        /* Por defecto Vite escucha en `localhost`, que en Windows es ::1;
           lvh.me resuelve a 127.0.0.1 y no llegaría nada. */
        host: '127.0.0.1',

        /* El panel de cada tienda se abre en su subdominio, también en
           desarrollo (rex.lvh.me:5173). Vite bloquea los hosts que no conoce
           para protegerse del DNS rebinding, así que hay que nombrarlos. */
        allowedHosts: ['.lvh.me'],
    },
}))
