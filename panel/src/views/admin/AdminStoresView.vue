<script setup>
import { onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'

import AppIcon from '@/components/AppIcon.vue'
import RowActions from '@/components/RowActions.vue'
import ScrollStrip from '@/components/ScrollStrip.vue'
import { api } from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import { useConfirmStore } from '@/stores/confirm'
import { useUiStore } from '@/stores/ui'

const router = useRouter()
const auth = useAuthStore()
const confirm = useConfirmStore()
const ui = useUiStore()

const stores = ref([])
const meta = ref(null)
const counts = ref({ all: 0, published: 0, hidden: 0, trash: 0 })
const loading = ref(true)

const filters = ref({ search: '', status: '' })
const page = ref(1)

async function load() {
    loading.value = true

    try {
        const payload = await api.get('/admin/stores', {
            search: filters.value.search,
            status: filters.value.status,
            page: page.value,
        })

        stores.value = payload.data
        meta.value = payload.meta
        counts.value = payload.counts
    } finally {
        loading.value = false
    }
}

function formatDate(value) {
    return new Date(value).toLocaleDateString('es', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

/** La tienda sale de la lista actual: se recarga para mover también los contadores. */
async function moveToTrash(store) {
    const confirmed = await confirm.ask({
        title: `¿Mover "${store.name}" a la papelera?`,
        text: 'El catálogo deja de verse. El dueño sigue entrando a su panel y la podés restaurar.',
        action: 'Mover a papelera',
    })

    if (! confirmed) {
        return
    }

    await api.patch(`/admin/stores/${store.id}/trash`)

    ui.toast('Tienda en la papelera', store.name)

    load()
}

async function restore(store) {
    await api.patch(`/admin/stores/${store.id}/restore`)

    ui.toast('Tienda restaurada', store.name)

    load()
}

async function destroy(store) {
    const confirmed = await confirm.ask({
        title: `¿Eliminar "${store.name}" definitivamente?`,
        text: 'Se borran la tienda, la cuenta de su dueño, sus categorías, productos, pedidos, '
            + 'estadísticas y todas sus imágenes.\nNo se puede deshacer.',
        action: 'Eliminar definitivamente',
        danger: true,
    })

    if (! confirmed) {
        return
    }

    try {
        await api.delete(`/admin/stores/${store.id}`)

        ui.toast('Tienda eliminada', store.name)

        load()
    } catch (error) {
        ui.toast('No pudimos eliminar la tienda', error.message, 'danger')
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

watch(() => filters.value.status, () => {
    page.value = 1
    load()
})

watch(page, load)

async function toggleActive(store) {
    const response = await api.patch(`/admin/stores/${store.id}/active`, { active: ! store.active })

    Object.assign(store, response.store)

    ui.toast(store.active ? 'Tienda publicada' : 'Tienda oculta', store.name)
}

/**
 * Entrar al panel de la tienda como su dueño. El panel de cada tienda vive en
 * su propio subdominio, así que esto se lleva el navegador: si vuelve de acá
 * es porque algo falló.
 */
async function enterPanel(store) {
    try {
        await auth.impersonate(store.id)
    } catch {
        ui.toast('No pudimos entrar al panel', store.name, 'danger')
    }
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
                <span class="btn-label">Nueva tienda</span>
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

            <ScrollStrip>
                <div class="toolbar-filters">
                    <select v-model="filters.status" class="select" aria-label="Filtrar por estado">
                        <option value="">Todos los estados ({{ counts.all }})</option>
                        <option value="published">Publicadas ({{ counts.published }})</option>
                        <option value="hidden">Ocultas ({{ counts.hidden }})</option>
                        <option value="trash">En papelera ({{ counts.trash }})</option>
                    </select>
                </div>
            </ScrollStrip>

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
                            <th class="is-hide-mobile">Dueño</th>
                            <th class="is-hide-mobile">Categorías</th>
                            <th class="is-hide-mobile">Productos</th>
                            <th class="is-hide-mobile">Estado</th>
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

                            <td class="is-hide-mobile">
                                <span class="table-cell-text">
                                    <strong>{{ store.owner?.name }}</strong>
                                    <span>{{ store.owner?.email }}</span>
                                </span>
                            </td>

                            <td class="is-hide-mobile">{{ store.categories_count }}</td>
                            <td class="is-hide-mobile">{{ store.products_count }}</td>

                            <td class="is-hide-mobile">
                                <span
                                    v-if="store.trashed_at"
                                    class="badge badge-dot badge-danger"
                                    :title="`En la papelera desde el ${formatDate(store.trashed_at)}`"
                                >
                                    En papelera
                                </span>
                                <span
                                    v-else
                                    class="badge badge-dot"
                                    :class="store.active ? 'badge-success' : 'badge-warning'"
                                >
                                    {{ store.active ? 'Publicada' : 'Oculta' }}
                                </span>
                            </td>

                            <td>
                                <RowActions
                                    v-if="store.trashed_at"
                                    :actions="[
                                        { label: 'Restaurar', icon: 'restore', onClick: () => restore(store) },
                                        { label: 'Eliminar definitivamente', icon: 'trash', danger: true, onClick: () => destroy(store) },
                                    ]"
                                />

                                <RowActions
                                    v-else
                                    :actions="[
                                        { label: 'Ver catálogo', icon: 'external', href: store.public_url },
                                        { label: 'Entrar al panel de la tienda', icon: 'enter', onClick: () => enterPanel(store) },
                                        { label: 'Editar', icon: 'pencil', to: { name: 'admin-store-edit', params: { id: store.id } } },
                                        { label: store.active ? 'Ocultar' : 'Publicar', icon: store.active ? 'check' : 'ban', onClick: () => toggleActive(store) },
                                        { label: 'Mover a papelera', icon: 'trash', danger: true, onClick: () => moveToTrash(store) },
                                    ]"
                                />
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
                    <AppIcon name="chevronLeft" />
                    Anterior
                </button>
                <button
                    class="page-link"
                    :class="{ 'is-disabled': page >= meta.last_page }"
                    type="button"
                    @click="page < meta.last_page && page++"
                >
                    Siguiente
                    <AppIcon name="chevronRight" />
                </button>
            </nav>
        </div>
    </section>
</template>
