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
 * Tienda del slug de la ruta.
 *
 * Se usa useFetch con la URL como función: la clave se deriva de la URL, así
 * que el layout y la página comparten una sola petición y se rehace sola
 * cuando cambia el slug.
 *
 * La base de la API se lee una vez acá dentro: useRuntimeConfig() necesita la
 * instancia de Nuxt y la función de URL se vuelve a evaluar fuera del setup.
 */
export function useCurrentStore() {
    const route = useRoute()
    const { apiBase } = useRuntimeConfig().public

    return useFetch(() => `${apiBase}/stores/${route.params.tienda}`, {
        transform: (response: StoreResponse) => response.store,
    })
}

export function useStoreProducts(filters: Ref<ProductFilters>) {
    const route = useRoute()
    const { apiBase } = useRuntimeConfig().public

    return useFetch<Paginated<Product>>(
        () => `${apiBase}/stores/${route.params.tienda}/products`,
        { query: filters },
    )
}

export function useStoreProduct(productSlug: Ref<string>) {
    const route = useRoute()
    const { apiBase } = useRuntimeConfig().public

    return useFetch(
        () => `${apiBase}/stores/${route.params.tienda}/products/${productSlug.value}`,
        { transform: (response: ProductResponse) => response.product },
    )
}

/**
 * Productos de la misma categoría para el bloque "Otros productos".
 * Sin categoría no hay nada que traer, por eso el fetch queda en pausa.
 */
export function useRelatedProducts(product: Ref<Product | null>) {
    const route = useRoute()
    const { apiBase } = useRuntimeConfig().public

    const category = computed(() => product.value?.category?.slug)

    return useFetch<Paginated<Product>>(
        () => `${apiBase}/stores/${route.params.tienda}/products`,
        {
            query: { category },
            immediate: Boolean(category.value),
        },
    )
}
