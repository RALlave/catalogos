<script setup>
import { onMounted, ref } from 'vue'
import draggable from 'vuedraggable'

import AppIcon from '@/components/AppIcon.vue'
import { api } from '@/services/api'
import { useUiStore } from '@/stores/ui'

const ui = useUiStore()

const categories = ref([])
const loading = ref(true)

async function load() {
    loading.value = true

    try {
        const payload = await api.get('/categories')

        categories.value = payload.data
    } finally {
        loading.value = false
    }
}

async function remove(category) {
    if (! window.confirm(`¿Eliminar "${category.name}"? Los productos quedan sin categoría.`)) {
        return
    }

    await api.delete(`/categories/${category.id}`)

    ui.toast('Categoría eliminada', category.name)

    await load()
}

/* Orden previo al arrastre: si la API falla, la lista vuelve a como estaba. */
let orderBackup = []

function startDrag() {
    orderBackup = categories.value.map(category => category.id)
}

/* El orden se manda completo: la API lo recibe en lote. */
async function saveOrder() {
    const ids = categories.value.map(category => category.id)

    if (ids.join() === orderBackup.join()) {
        return
    }

    try {
        await api.post('/categories/reorder', { ids })

        ui.toast('Orden actualizado')

        await load()
    } catch {
        categories.value = orderBackup.map(id => categories.value.find(category => category.id === id))

        ui.toast('No pudimos guardar el orden', '', 'danger')
    }
}

onMounted(load)
</script>

<template>
    <div class="page-header">
        <div class="page-title">
            <h1>Categorías</h1>
            <p>Ordená cómo se agrupan tus productos</p>
        </div>

        <div class="page-actions">
            <RouterLink class="btn btn-primary" :to="{ name: 'category-create' }">
                <AppIcon name="plus" />
                Nueva categoría
            </RouterLink>
        </div>
    </div>

    <section class="card">
        <div class="card-body is-flush">
            <div v-if="loading" class="empty">
                <p>Cargando…</p>
            </div>

            <div v-else-if="! categories.length" class="empty">
                <p>Todavía no creaste categorías.</p>
                <RouterLink class="btn btn-primary btn-sm" :to="{ name: 'category-create' }">
                    Crear la primera
                </RouterLink>
            </div>

            <div v-else class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="table-handle"><span class="visually-hidden">Ordenar</span></th>
                            <th>Categoría</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th>Orden</th>
                            <th><span class="visually-hidden">Acciones</span></th>
                        </tr>
                    </thead>

                    <draggable
                        v-model="categories"
                        tag="tbody"
                        item-key="id"
                        handle=".drag-handle"
                        ghost-class="is-drag-ghost"
                        chosen-class="is-dragging"
                        :animation="150"
                        @start="startDrag"
                        @end="saveOrder"
                    >
                        <template #item="{ element: category }">
                            <tr>
                                <td class="table-handle">
                                    <span
                                        class="drag-handle"
                                        title="Arrastrar para ordenar"
                                        :aria-label="`Ordenar ${category.name}`"
                                    >
                                        <AppIcon name="grip" />
                                    </span>
                                </td>

                                <td>
                                    <span class="table-cell-text">
                                        <strong>{{ category.name }}</strong>
                                        <span>{{ category.slug }}</span>
                                    </span>
                                </td>

                                <td>{{ category.description }}</td>

                                <td>
                                    <span
                                        class="badge badge-dot"
                                        :class="category.active ? 'badge-success' : 'badge-warning'"
                                    >
                                        {{ category.active ? 'Activa' : 'Oculta' }}
                                    </span>
                                </td>

                                <td>{{ category.order }}</td>

                                <td>
                                    <div class="table-actions">
                                        <RouterLink
                                            class="btn btn-ghost btn-icon"
                                            :to="{ name: 'category-edit', params: { id: category.id } }"
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
                                            @click="remove(category)"
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
    </section>
</template>
