/*
 * El panel vive en dos lugares a la vez: en el apex (miotienda.com) para el
 * superadmin y para quien todavía no tiene tienda, y en el subdominio de cada
 * tienda (rex.miotienda.com) para su dueño.
 *
 * Los dos son orígenes distintos y NO comparten localStorage: cada salto de
 * uno a otro es una recarga completa, no una navegación del router.
 */

/* Con el puerto incluido: así se escribe cuando hay que mostrarlo. */
export const BASE_DOMAIN = import.meta.env.VITE_BASE_DOMAIN ?? 'lvh.me:5173'

/** Dominio base sin puerto: en desarrollo viene como `lvh.me:5173`. */
function baseHost() {
    return BASE_DOMAIN.split(':')[0].toLowerCase()
}

/** Puerto del dominio base, con los dos puntos, o cadena vacía. */
function basePort() {
    const port = BASE_DOMAIN.split(':')[1]

    return port ? `:${port}` : ''
}

/**
 * Slug de la tienda en cuyo subdominio estamos, o null si es el apex.
 *
 * `www` y los sub-subdominios no son tiendas: el certificado wildcard tampoco
 * cubre un segundo nivel.
 */
export function currentStoreSlug() {
    const host = window.location.hostname.toLowerCase()
    const base = baseHost()

    if (! host.endsWith(`.${base}`)) {
        return null
    }

    const slug = host.slice(0, -(base.length + 1))

    return (! slug || slug.includes('.') || slug === 'www') ? null : slug
}

/** URL absoluta de una ruta del panel en el subdominio de una tienda. */
export function storeUrl(slug, path = '/admin') {
    return `${window.location.protocol}//${slug}.${baseHost()}${basePort()}${path}`
}

/** URL absoluta de una ruta del apex. */
export function apexUrl(path = '/login') {
    return `${window.location.protocol}//${baseHost()}${basePort()}${path}`
}
