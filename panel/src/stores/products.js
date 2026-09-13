import { defineStore } from 'pinia'
import { ref } from 'vue'

import { api } from '@/services/api'

const EMPTY_FILTERS = { search: '', category_id: '', visible: '', mark: '' }

/**
 * Los productos de la tienda: la última vista del listado y las fichas que ya
 * se abrieron.
 *
 * A diferencia de las categorías, el listado está paginado y filtrado, así que
 * lo guardado es **una** vista —qué filtros, qué página y sus productos—. Al
 * volver de editar se dibuja al instante y se refresca por detrás; al cambiar
 * de filtro o de página lo que se ve ya no corresponde y va el esqueleto.
 */
export const useProductsStore = defineStore('products', () => {
    const items = ref([])
    const meta = ref(null)
    const loading = ref(false)
    const loaded = ref(false)

    const filters = ref({ ...EMPTY_FILTERS })
    const page = ref(1)

    /* Cada producto que pasó por acá, para que la ficha no arranque vacía. */
    const known = ref({})

    /* Qué vista corresponde a lo que hay en `items` ahora mismo. */
    let shown = ''

    function viewKey() {
        return JSON.stringify({ ...filters.value, page: page.value })
    }

    function remember(product) {
        known.value = { ...known.value, [product.id]: product }
    }

    async function fetch() {
        const key = viewKey()

        /* Otra página u otro filtro: lo que se está mostrando ya no sirve. */
        if (key !== shown) {
            items.value = []
            meta.value = null
            loaded.value = false
        }

        loading.value = true

        try {
            const payload = await api.get('/products', {
                search: filters.value.search,
                category_id: filters.value.category_id,
                visible: filters.value.visible,
                /* La marca elegida viaja como el filtro que le toca; los otros
                   dos quedan vacíos y la API no los recibe. */
                featured: filters.value.mark === 'featured' ? '1' : '',
                is_new: filters.value.mark === 'is_new' ? '1' : '',
                on_sale: filters.value.mark === 'on_sale' ? '1' : '',
                page: page.value,
            })

            items.value = payload.data
            meta.value = payload.meta
            loaded.value = true
            shown = key

            payload.data.forEach(remember)
        } finally {
            loading.value = false
        }
    }

    /** El producto que ya se conoce, para pintar la ficha sin esperar. */
    function find(id) {
        return known.value[id] ?? null
    }

    /**
     * Deja el producto como la última versión conocida. En el listado se
     * mezcla en vez de reemplazar: la ficha y la fila no traen los mismos
     * campos.
     */
    function upsert(product) {
        remember(product)

        items.value = items.value.map(item => (item.id === product.id
            ? { ...item, ...product }
            : item))
    }

    function drop(id) {
        items.value = items.value.filter(item => item.id !== id)

        const rest = { ...known.value }

        delete rest[id]

        known.value = rest
    }

    /** Otra tienda, otros productos: al cerrar sesión no queda nada. */
    function reset() {
        items.value = []
        meta.value = null
        known.value = {}
        loaded.value = false
        filters.value = { ...EMPTY_FILTERS }
        page.value = 1
        shown = ''
    }

    return {
        items,
        meta,
        loading,
        loaded,
        filters,
        page,
        fetch,
        find,
        remember,
        upsert,
        drop,
        reset,
    }
})
