<script setup>
import { onMounted, ref, watch } from 'vue'

import AppIcon from '@/components/AppIcon.vue'
import { api } from '@/services/api'
import { useUiStore } from '@/stores/ui'

const ui = useUiStore()

const stores = ref([])
const meta = ref(null)
const loading = ref(true)

const filters = ref({ search: '', active: '' })
const page = ref(1)

async function load() {
    loading.value = true

    try {
        const payload = await api.get('/admin/stores', {
            search: filters.value.search,
            active: filters.value.active,
            page: page.value,
        })

        stores.value = payload.data
        meta.value = payload.meta
    } finally {
        loading.value = false
    }
}

let searchTimer

watch(() => filters.value.search, () => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(() => {
        page.value = 1
        load()
    }, 350)
})

watch(() => filters.value.active, () => {
    page.value = 1
    load()
})

watch(page, load)

async function toggleActive(store) {
    const response = await api.patch(`/admin/stores/${store.id}/active`, { active: ! store.active })

    Object.assign(store, response.store)

    ui.toast(store.active ? 'Tienda publicada' : 'Tienda oculta', store.name)
}

onMounted(load)
</script>

<template>
    <div class="page-header">
        <div class="page-title">
            <h1>Tiendas</h1>
            <p>Todas las tiendas de la plataforma</p>
        </div>

        <div class="page-actions">
            <RouterLink class="btn btn-primary" :to="{ name: 'admin-store-create' }">
                <AppIcon name="plus" />
                Nueva tienda
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
                    placeholder="Buscar por nombre, slug o email del dueño…"
                    aria-label="Buscar tienda"
                >
            </div>

            <div class="toolbar-filters">
                <select v-model="filters.active" class="select" aria-label="Filtrar por estado">
                    <option value="">Todos los estados</option>
                    <option value="1">Publicadas</option>
                    <option value="0">Ocultas</option>
                </select>
            </div>

            <div class="toolbar-count">{{ meta?.total ?? 0 }} tiendas</div>
        </div>

        <div class="card-body is-flush">
            <div v-if="loading" class="empty">
                <p>Cargando…</p>
            </div>

            <div v-else-if="! stores.length" class="empty">
                <p>No hay tiendas con esos filtros.</p>
            </div>

            <div v-else class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tienda</th>
                            <th>Dueño</th>
                            <th>Categorías</th>
                            <th>Productos</th>
                            <th>Estado</th>
                            <th><span class="visually-hidden">Acciones</span></th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="store in stores" :key="store.id">
                            <td>
                                <div class="table-cell">
                                    <img v-if="store.logo_url" class="thumb" :src="store.logo_url" :alt="store.name">
                                    <span v-else class="thumb">
                                        <AppIcon name="store" />
                                    </span>

                                    <span class="table-cell-text">
                                        <strong>{{ store.name }}</strong>
                                        <span>{{ store.slug }}</span>
                                    </span>
                                </div>
                            </td>

                            <td>
                                <span class="table-cell-text">
                                    <strong>{{ store.owner?.name }}</strong>
                                    <span>{{ store.owner?.email }}</span>
                                </span>
                            </td>

                            <td>{{ store.categories_count }}</td>
                            <td>{{ store.products_count }}</td>

                            <td>
                                <span
                                    class="badge badge-dot"
                                    :class="store.active ? 'badge-success' : 'badge-warning'"
                                >
                                    {{ store.active ? 'Publicada' : 'Oculta' }}
                                </span>
                            </td>

                            <td>
                                <div class="table-actions">
                                    <a
                                        class="btn btn-ghost btn-icon"
                                        :href="store.public_url"
                                        target="_blank"
                                        rel="noopener"
                                        aria-label="Ver catálogo"
                                    >
                                        <AppIcon name="external" />
                                    </a>

                                    <RouterLink
                                        class="btn btn-ghost btn-icon"
                                        :to="{ name: 'admin-store-edit', params: { id: store.id } }"
                                        title="Editar"
                                        aria-label="Editar"
                                    >
                                        <AppIcon name="pencil" />
                                    </RouterLink>

                                    <button
                                        class="btn btn-ghost btn-icon"
                                        type="button"
                                        :aria-label="store.active ? 'Ocultar' : 'Publicar'"
                                        @click="toggleActive(store)"
                                    >
                                        <AppIcon :name="store.active ? 'ban' : 'check'" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
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
