import { defineStore } from 'pinia'
import { ref } from 'vue'

import { api } from '@/services/api'

/**
 * Las categorías de la tienda, guardadas para toda la sesión.
 *
 * La lista es chica y no está paginada, así que entra entera. Quien la pide se
 * dibuja con lo que ya hay —sin esperar— mientras la petición confirma por
 * detrás: así el listado abre al instante y el formulario de edición no
 * arranca vacío.
 */
export const useCategoriesStore = defineStore('categories', () => {
    const items = ref([])
    const loading = ref(false)

    /* Si nunca se trajo la lista no hay nada que mostrar: va el esqueleto. */
    const loaded = ref(false)

    async function fetch() {
        loading.value = true

        try {
            const payload = await api.get('/categories')

            items.value = payload.data
            loaded.value = true
        } finally {
            loading.value = false
        }
    }

    /** La categoría que ya se conoce, para pintar el formulario sin esperar. */
    function find(id) {
        return items.value.find(item => String(item.id) === String(id)) ?? null
    }

    /** Deja la categoría como la última versión conocida: la agrega o la pisa. */
    function upsert(category) {
        const index = items.value.findIndex(item => item.id === category.id)

        if (index === -1) {
            items.value = [...items.value, category]

            return
        }

        items.value = items.value.map(item => (item.id === category.id ? category : item))
    }

    function drop(id) {
        items.value = items.value.filter(item => item.id !== id)
    }

    /** Otra tienda, otras categorías: al cerrar sesión no queda nada. */
    function reset() {
        items.value = []
        loaded.value = false
    }

    return {
        items,
        loading,
        loaded,
        fetch,
        find,
        upsert,
        drop,
        reset,
    }
})
