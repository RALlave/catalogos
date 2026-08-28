<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import draggable from 'vuedraggable'

import AppIcon from '@/components/AppIcon.vue'
import { api } from '@/services/api'
import { useUiStore } from '@/stores/ui'

const ui = useUiStore()

const products = ref([])
const categories = ref([])
const meta = ref(null)
const loading = ref(true)
const selected = ref([])
const featuring = ref(null)
const cloning = ref(null)

const filters = ref({ search: '', category_id: '', visible: '' })
const page = ref(1)

const allChecked = computed(() => products.value.length > 0
    && selected.value.length === products.value.length)

async function load() {
    loading.value = true

    try {
        const payload = await api.get('/products', {
            search: filters.value.search,
            category_id: filters.value.category_id,
            visible: filters.value.visible,
            page: page.value,
        })

        products.value = payload.data
        meta.value = payload.meta
        selected.value = []
    } finally {
        loading.value = false
    }
}

async function loadCategories() {
    const payload = await api.get('/categories')

    categories.value = payload.data
}

let searchTimer

watch(() => filters.value.search, () => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(() => {
        page.value = 1
        load()
    }, 350)
})

watch(() => [filters.value.category_id, filters.value.visible], () => {
    page.value = 1
    load()
})

watch(page, load)

function toggleAll(event) {
    selected.value = event.target.checked ? products.value.map(item => item.id) : []
}

function toggleOne(id, event) {
    selected.value = event.target.checked
        ? [...selected.value, id]
        : selected.value.filter(item => item !== id)
}

/* Orden previo al arrastre: si la API falla, la lista vuelve a como estaba. */
let orderBackup = []

function startDrag() {
    orderBackup = products.value.map(product => product.id)
}

async function saveOrder() {
    const ids = products.value.map(product => product.id)

    if (ids.join() === orderBackup.join()) {
        return
    }

    try {
        await api.post('/products/reorder', { ids })

        ui.toast('Orden actualizado')

        /* Se recarga para que la columna "Orden" muestre los valores guardados. */
        await load()
    } catch {
        products.value = orderBackup.map(id => products.value.find(product => product.id === id))

        ui.toast('No pudimos guardar el orden', '', 'danger')
    }
}

async function toggleFeatured(product) {
    featuring.value = product.id

    try {
        await api.put(`/products/${product.id}`, { featured: ! product.featured })

        product.featured = ! product.featured

        ui.toast(product.featured ? 'Producto destacado' : 'Quitado de destacados', product.name)
    } catch {
        ui.toast('No pudimos cambiar el destacado', product.name, 'danger')
    } finally {
        featuring.value = null
    }
}

async function duplicate(product) {
    cloning.value = product.id

    try {
        const payload = await api.post(`/products/${product.id}/clone`)

        ui.toast('Producto clonado', payload.product.name)

        await load()
    } catch {
        ui.toast('No pudimos clonar el producto', product.name, 'danger')
    } finally {
        cloning.value = null
    }
}

async function remove(product) {
    if (! window.confirm(`¿Eliminar "${product.name}"? No se puede deshacer.`)) {
        return
    }

    await api.delete(`/products/${product.id}`)

    ui.toast('Producto eliminado', product.name)

    await load()
}

/* La API no tiene endpoints en lote: se resuelve con una llamada por producto. */
async function bulkVisibility(visible) {
    await Promise.all(selected.value.map(id => api.put(`/products/${id}`, { visible })))

    ui.toast(visible ? 'Productos publicados' : 'Productos ocultados')

    await load()
}

async function bulkRemove() {
    if (! window.confirm(`¿Eliminar ${selected.value.length} productos? No se puede deshacer.`)) {
        return
    }

    await Promise.all(selected.value.map(id => api.delete(`/products/${id}`)))

    ui.toast('Productos eliminados')

    await load()
}

function money(product) {
    const amount = product.sale_price ?? product.price

    if (! amount) {
        return ''
    }

    return new Intl.NumberFormat('es-PY', { maximumFractionDigits: 2 }).format(Number(amount))
}

onMounted(async () => {
    await Promise.all([load(), loadCategories()])
})
</script>

<template>
    <div class="page-header">
        <div class="page-title">
            <h1>Productos</h1>
            <p>Administrá lo que se ve en tu catálogo</p>
        </div>

        <div class="page-actions">
            <RouterLink class="btn btn-primary" :to="{ name: 'product-create' }">
                <AppIcon name="plus" />
                Nuevo producto
            </RouterLink>
        </div>
    </div>

    <section class="card">
        <div class="toolbar">
            <div class="search toolbar-search">
                <AppIcon name="search" />
                <input
                    v-model="filters.search"
                    class="input"
                    type="search"
                    placeholder="Buscar por nombre o código…"
                    aria-label="Buscar producto"
                >
            </div>

            <div class="toolbar-filters">
                <select v-model="filters.category_id" class="select" aria-label="Filtrar por categoría">
                    <option value="">Todas las categorías</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">
                        {{ category.name }}
                    </option>
                </select>

                <select v-model="filters.visible" class="select" aria-label="Filtrar por estado">
                    <option value="">Todos los estados</option>
                    <option value="1">Visible</option>
                    <option value="0">Oculto</option>
                </select>
            </div>

            <div class="toolbar-count">{{ meta?.total ?? 0 }} resultados</div>
        </div>

        <div v-if="selected.length" class="bulkbar">
            <div class="bulkbar-text">{{ selected.length }} productos seleccionados</div>

            <button class="btn btn-outline btn-sm" type="button" @click="bulkVisibility(true)">Mostrar</button>
            <button class="btn btn-outline btn-sm" type="button" @click="bulkVisibility(false)">Ocultar</button>
            <button class="btn btn-ghost btn-sm" type="button" @click="bulkRemove">Eliminar</button>
        </div>

        <div class="card-body is-flush">
            <div v-if="loading" class="empty">
                <p>Cargando…</p>
            </div>

            <div v-else-if="! products.length" class="empty">
                <p>No hay productos con esos filtros.</p>
                <RouterLink class="btn btn-primary btn-sm" :to="{ name: 'product-create' }">
                    Cargar un producto
                </RouterLink>
            </div>

            <div v-else class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="table-handle"><span class="visually-hidden">Ordenar</span></th>
                            <th class="table-check">
                                <input
                                    type="checkbox"
                                    :checked="allChecked"
                                    aria-label="Seleccionar todos"
                                    @change="toggleAll"
                                >
                            </th>
                            <th>Producto</th>
                            <th>Código</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Destacado</th>
                            <th>Estado</th>
                            <th>Orden</th>
                            <th><span class="visually-hidden">Acciones</span></th>
                        </tr>
                    </thead>

                    <draggable
                        v-model="products"
                        tag="tbody"
                        item-key="id"
                        handle=".drag-handle"
                        ghost-class="is-drag-ghost"
                        chosen-class="is-dragging"
                        :animation="150"
                        @start="startDrag"
                        @end="saveOrder"
                    >
                        <template #item="{ element: product }">
                            <tr>
                                <td class="table-handle">
                                    <span
                                        class="drag-handle"
                                        title="Arrastrar para ordenar"
                                        :aria-label="`Ordenar ${product.name}`"
                                    >
                                        <AppIcon name="grip" />
                                    </span>
                                </td>

                                <td class="table-check">
                                    <input
                                        type="checkbox"
                                        :checked="selected.includes(product.id)"
                                        :aria-label="`Seleccionar ${product.name}`"
                                        @change="toggleOne(product.id, $event)"
                                    >
                                </td>

                                <td>
                                    <div class="table-cell">
                                        <img
                                            v-if="product.main_image_url"
                                            class="thumb"
                                            :src="product.main_image_url"
                                            :alt="product.name"
                                        >
                                        <span v-else class="thumb">
                                            <AppIcon name="image" />
                                        </span>

                                        <span class="table-cell-text">
                                            <strong>{{ product.name }}</strong>
                                            <span>{{ product.slug }}</span>
                                        </span>
                                    </div>
                                </td>

                                <td>{{ product.sku }}</td>
                                <td>{{ product.category?.name }}</td>

                                <td>
                                    <div class="table-price">{{ money(product) }}</div>
                                </td>

                                <td>
                                    <button
                                        class="star"
                                        :class="{ 'is-on': product.featured, 'is-loading': featuring === product.id }"
                                        type="button"
                                        :disabled="featuring === product.id"
                                        :title="product.featured ? 'Quitar de destacados' : 'Destacar'"
                                        :aria-label="product.featured ? 'Quitar de destacados' : 'Destacar'"
                                        @click="toggleFeatured(product)"
                                    >
                                        <span v-if="featuring === product.id" class="star-loader" />
                                        <AppIcon v-else name="star" />
                                    </button>
                                </td>

                                <td>
                                    <span
                                        class="badge badge-dot"
                                        :class="product.visible ? 'badge-success' : 'badge-warning'"
                                    >
                                        {{ product.visible ? 'Visible' : 'Oculto' }}
                                    </span>
                                </td>

                                <td>{{ product.order }}</td>

                                <td>
                                    <div class="table-actions">
                                        <button
                                            class="btn btn-ghost btn-icon"
                                            type="button"
                                            :disabled="cloning === product.id"
                                            title="Clonar"
                                            aria-label="Clonar"
                                            @click="duplicate(product)"
                                        >
                                            <span v-if="cloning === product.id" class="btn-loader" />
                                            <AppIcon v-else name="copy" />
                                        </button>

                                        <RouterLink
                                            class="btn btn-ghost btn-icon"
                                            :to="{ name: 'product-edit', params: { id: product.id } }"
                                            title="Editar"
                                            aria-label="Editar"
                                        >
                                            <AppIcon name="pencil" />
                                        </RouterLink>

                                        <button
                                            class="btn btn-ghost btn-icon"
                                            type="button"
                                            title="Eliminar"
                                            aria-label="Eliminar"
                                            @click="remove(product)"
                                        >
                                            <AppIcon name="trash" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </draggable>
                </table>
            </div>
        </div>

        <div v-if="meta && meta.last_page > 1" class="card-footer">
            <div class="page-info">Página {{ meta.current_page }} de {{ meta.last_page }}</div>

            <nav class="pagination" aria-label="Paginación">
                <button
                    class="page-link"
                    :class="{ 'is-disabled': page <= 1 }"
                    type="button"
                    @click="page > 1 && page--"
                >
                    Anterior
                </button>
                <button
                    class="page-link"
                    :class="{ 'is-disabled': page >= meta.last_page }"
                    type="button"
                    @click="page < meta.last_page && page++"
                >
                    Siguiente
                </button>
            </nav>
        </div>
    </section>
</template>
