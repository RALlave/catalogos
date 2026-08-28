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
    },
}))
