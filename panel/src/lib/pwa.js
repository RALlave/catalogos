import { computed, ref } from 'vue'

/**
 * Instalación del panel como aplicación de escritorio.
 *
 * El navegador decide si se puede instalar y avisa con `beforeinstallprompt`,
 * una sola vez y antes de que monte ningún componente. Por eso el listener se
 * registra al importar este módulo y el evento queda guardado: el botón lo
 * dispara después, cuando el usuario hace clic.
 *
 * Sólo Chrome y Edge lo emiten. En Firefox y Safari nunca hay nada guardado y
 * el botón no se dibuja.
 */
const prompt = ref(null)

if (typeof window !== 'undefined') {
    window.addEventListener('beforeinstallprompt', event => {
        /* Sin esto el navegador muestra su propio cartel. */
        event.preventDefault()

        prompt.value = event
    })

    /* Ya instalada: no hay nada más que ofrecer en esta pestaña. */
    window.addEventListener('appinstalled', () => {
        prompt.value = null
    })
}

export function usePwaInstall() {
    async function install() {
        const event = prompt.value

        if (! event) {
            return
        }

        /* El evento sirve una sola vez, se acepte o no. Si el usuario cancela,
           el navegador vuelve a emitirlo más adelante por su cuenta. */
        prompt.value = null

        await event.prompt()
    }

    return {
        canInstall: computed(() => prompt.value !== null),
        install,
    }
}

/**
 * Registra el service worker.
 *
 * No cachea nada: existe porque el navegador todavía lo pide para ofrecer la
 * instalación. Cachear el panel sería servir una versión vieja después de cada
 * despliegue.
 *
 * Vive en la raíz del dominio y no en /panel/ como el resto de los archivos:
 * un service worker sólo alcanza a las rutas que cuelgan de su propia carpeta,
 * y las del panel (/login, /admin, /superadmin) están en la raíz.
 */
export function registerServiceWorker() {
    if (! ('serviceWorker' in navigator)) {
        return
    }

    navigator.serviceWorker.register('/sw.js').catch(() => {
        /* Sin service worker el panel funciona igual; lo único que se pierde
           es la oferta de instalarlo. */
    })
}

/**
 * Pone el ícono de la plataforma como favicon.
 *
 * No puede escribirse en el HTML: el archivo cambia de nombre cada vez que el
 * superadmin sube uno nuevo. Sin ícono cargado no se dibuja ninguno.
 */
export function applyFavicon(icon) {
    if (! icon) {
        return
    }

    let link = document.querySelector('link[rel="icon"]')

    if (! link) {
        link = document.createElement('link')
        link.rel = 'icon'
        document.head.appendChild(link)
    }

    link.type = 'image/webp'
    link.href = icon.thumb
}
