import { defineStore } from 'pinia'
import { ref } from 'vue'

import { applyFavicon } from '@/lib/pwa'
import { api } from '@/services/api'

/**
 * Marca de la plataforma: los tres logos que sube el superadmin.
 *
 * `auth` lo dibujan las pantallas de acceso, `panel` la barra lateral —tanto
 * la del superadmin como la del dueño de una tienda— e `icon` es el favicon y
 * el ícono de la app instalada. Se piden una sola vez por carga: la petición
 * es pública, así que también sirve antes del login.
 */
export const usePlatformStore = defineStore('platform', () => {
    const logos = ref({ auth: null, panel: null, icon: null })

    let request = null

    /** Una petición para todos los que la pidan a la vez. */
    function load() {
        request ??= api.get('/platform')
            .then(payload => {
                apply(payload)
            })
            .catch(() => {
                /* Sin marca no se dibuja nada: no es motivo para romper la
                   pantalla que la pidió. */
                request = null
            })

        return request
    }

    /** Lo que devuelve una subida o un borrado, para no volver a pedirlo. */
    function apply(payload) {
        logos.value = payload.logos

        applyFavicon(payload.logos.icon)
    }

    return { logos, load, apply }
})
