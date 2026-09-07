/**
 * La tienda ya no viaja en la ruta sino en el subdominio: el catálogo de
 * `rex` vive en `rex.miotienda.com/`, no en `miotienda.com/rex`.
 *
 * Todo lo que antes leía `route.params.tienda` pasa por acá. El host se lee
 * del pedido en el servidor y de `location` en el navegador: los dos tienen
 * que dar lo mismo o la hidratación se rompe.
 */

/** Host del pedido, con puerto y sin protocolo (`rex.lvh.me:3000`). */
function currentHost(): string {
    if (import.meta.client) {
        return window.location.host
    }

    return useRequestHeaders(['host']).host ?? ''
}

/**
 * Slug de la tienda del subdominio.
 *
 * Devuelve null cuando el host no es el de una tienda: el dominio pelado, un
 * `www` o cualquier cosa que no termine en el dominio base. Quien lo use
 * decide qué hacer con eso; acá no se inventa una tienda por defecto.
 */
export function useStoreSlug(): string | null {
    const { baseDomain } = useRuntimeConfig().public

    /* El puerto no distingue tiendas: en dev el dominio base viene como
       `lvh.me:3000` y el host como `rex.lvh.me:3000`. */
    const host = currentHost().split(':')[0]?.toLowerCase() ?? ''
    const base = String(baseDomain).split(':')[0]?.toLowerCase() ?? ''

    if (! host.endsWith(`.${base}`)) {
        return null
    }

    const slug = host.slice(0, -(base.length + 1))

    /* Un sub-subdominio (`a.b.miotienda.com`) no es una tienda, y el
       certificado wildcard tampoco lo cubre. */
    if (! slug || slug.includes('.') || slug === 'www') {
        return null
    }

    return slug
}

/**
 * Igual que el anterior pero para las páginas del catálogo: sin tienda no hay
 * nada que renderizar, así que corta con un 404 en vez de devolver null.
 */
export function useRequiredStoreSlug(): string {
    const slug = useStoreSlug()

    if (! slug) {
        throw createError({ statusCode: 404, statusMessage: 'No encontramos esta tienda.' })
    }

    return slug
}

/**
 * Origen absoluto de la tienda actual (`https://rex.miotienda.com`), para las
 * URLs canónicas, el Open Graph y los enlaces que se comparten por WhatsApp.
 *
 * Sale del host real y no de una variable de entorno: con una tienda por
 * subdominio, una URL fija en el `.env` sería la de otra tienda.
 */
export function useSiteUrl(): string {
    if (import.meta.client) {
        return window.location.origin
    }

    const headers = useRequestHeaders(['host', 'x-forwarded-proto'])

    return `${headers['x-forwarded-proto'] ?? 'http'}://${headers.host ?? ''}`
}
