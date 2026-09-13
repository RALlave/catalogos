import type { Paginated, Product, Store } from '~/types/catalog'

interface StoreResponse {
    store: Store
}

interface ProductResponse {
    product: Product
}

export interface ProductFilters {
    category?: string
    search?: string
    page?: number
}

/**
 * Datos del visitante que la API necesita para contar la visita.
 *
 * En el render del servidor la petición sale del servidor de Nuxt: sin esto la
 * API vería siempre la misma IP y ningún navegador, y no contaría a nadie. En
 * el navegador no hace falta, los manda él.
 */
function visitorHeaders(): Record<string, string> {
    if (import.meta.client) {
        return {}
    }

    const headers = useRequestHeaders(['x-forwarded-for', 'user-agent'])
    const ip = headers['x-forwarded-for'] ?? useRequestEvent()?.node.req.socket.remoteAddress

    return {
        ...(ip ? { 'x-forwarded-for': ip } : {}),
        ...(headers['user-agent'] ? { 'user-agent': headers['user-agent'] } : {}),
    }
}

/**
 * Tienda del subdominio.
 *
 * Se usa useFetch con la URL como función: la clave se deriva de la URL, así
 * que el layout y la página comparten una sola petición.
 *
 * El slug ya no cambia sin recargar —está en el host, no en la ruta—, pero la
 * base de la API se sigue leyendo acá dentro: useRuntimeConfig() necesita la
 * instancia de Nuxt y la función de URL se vuelve a evaluar fuera del setup.
 */
export function useCurrentStore() {
    const slug = useRequiredStoreSlug()
    const { apiBase } = useRuntimeConfig().public

    /* Es la petición que la API usa para contar la visita al catálogo. */
    return useFetch(() => `${apiBase}/stores/${slug}`, {
        headers: visitorHeaders(),
        transform: (response: StoreResponse) => response.store,
    })
}

/**
 * Listado de productos de la tienda.
 *
 * `immediate` en false salta la primera petición y deja el resto igual: la
 * página de búsqueda la usa para no pedir nada mientras no haya término, y el
 * fetch sale solo en cuanto los filtros cambian.
 */
export function useStoreProducts(filters: Ref<ProductFilters>, options: { immediate?: boolean } = {}) {
    const slug = useRequiredStoreSlug()
    const { apiBase } = useRuntimeConfig().public

    return useFetch<Paginated<Product>>(
        () => `${apiBase}/stores/${slug}/products`,
        { query: filters, ...options },
    )
}

/**
 * La vitrina de destacados del home: hasta cinco productos.
 *
 * Mientras no haga falta —la sección apagada, o un filtro activo— la petición
 * no sale: el dato del interruptor ya llegó con la tienda. La URL es siempre la
 * misma, así que sin `watch` volver al home sin filtro no la traería nunca.
 */
export function useFeaturedProducts(enabled: Ref<boolean>) {
    const slug = useRequiredStoreSlug()
    const { apiBase } = useRuntimeConfig().public

    return useFetch<{ data: Product[] }>(
        () => `${apiBase}/stores/${slug}/featured`,
        { immediate: enabled.value, watch: [enabled] },
    )
}

export function useStoreProduct(productSlug: Ref<string>) {
    const slug = useRequiredStoreSlug()
    const { apiBase } = useRuntimeConfig().public

    return useFetch(
        () => `${apiBase}/stores/${slug}/products/${productSlug.value}`,
        {
            headers: visitorHeaders(),
            transform: (response: ProductResponse) => response.product,
        },
    )
}

/**
 * Avisa que alguien tocó el botón de compartir.
 *
 * Es lo único que la API no puede ver por su cuenta. Va sin await y se traga el
 * error: que falle una estadística no puede frenar el compartido.
 */
export function trackShare(storeSlug: string, productSlug: string): void {
    const { apiBase } = useRuntimeConfig().public

    $fetch(`${apiBase}/stores/${storeSlug}/track`, {
        method: 'POST',
        body: { type: 'share', product_slug: productSlug },
    }).catch(() => {})
}

/**
 * Productos de la misma categoría para el bloque "Otros productos".
 * Sin categoría no hay nada que traer, por eso el fetch queda en pausa.
 */
export function useRelatedProducts(product: Ref<Product | null>) {
    const slug = useRequiredStoreSlug()
    const { apiBase } = useRuntimeConfig().public

    const category = computed(() => product.value?.category?.slug)

    return useFetch<Paginated<Product>>(
        () => `${apiBase}/stores/${slug}/products`,
        {
            query: { category },
            immediate: Boolean(category.value),
        },
    )
}
