import { fileURLToPath, URL } from 'node:url'

import vue from '@vitejs/plugin-vue'
import { defineConfig, loadEnv } from 'vite'

/*
 * El manifest de la app instalable.
 *
 * Se genera acá y no como archivo suelto en public/ porque necesita la URL de
 * la API, que sale del entorno. Los íconos apuntan a una ruta fija de la API:
 * el archivo real lo sube el superadmin y cambia de nombre cada vez.
 *
 * `start_url` es /login porque es la única ruta que existe en los dos orígenes
 * donde se sirve el panel: en el dominio principal la raíz es la landing y en
 * el subdominio de una tienda es su catálogo. Con la sesión abierta, el router
 * lleva al panel que corresponda.
 */
function manifest(apiBase) {
    const icon = size => ({
        src: `${apiBase}/platform/icon/${size}`,
        sizes: `${size}x${size}`,
        type: 'image/webp',
        purpose: 'any',
    })

    return {
        name: 'Catálogos',
        short_name: 'Catálogos',
        description: 'Panel de administración del catálogo',
        start_url: '/login',
        scope: '/',
        display: 'standalone',
        background_color: '#ffffff',
        theme_color: '#ffffff',
        icons: [icon(192), icon(512)],
    }
}

/**
 * Escribe el manifest junto al resto del build y lo sirve en desarrollo.
 */
function manifestPlugin(apiBase) {
    const path = 'manifest.webmanifest'
    const body = () => JSON.stringify(manifest(apiBase), null, 4)

    return {
        name: 'panel-manifest',

        generateBundle() {
            this.emitFile({ type: 'asset', fileName: path, source: body() })
        },

        configureServer(server) {
            server.middlewares.use(`/${path}`, (request, response) => {
                response.setHeader('Content-Type', 'application/manifest+json')
                response.end(body())
            })
        },
    }
}

/*
 * En local el panel vive en la raíz (/). En producción cuelga de /panel/,
 * y eso se define con VITE_BASE en el .env de producción para no tener que
 * tocar este archivo ni el entorno local.
 */
export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '')

    return {
        base: env.VITE_BASE ?? '/',

        plugins: [
            vue(),
            manifestPlugin(env.VITE_API_BASE ?? 'http://127.0.0.1:8000/api'),
        ],

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
               desarrollo (rex.lvh.me:5173). Vite bloquea los hosts que no
               conoce para protegerse del DNS rebinding, así que hay que
               nombrarlos. */
            allowedHosts: ['.lvh.me'],
        },
    }
})
