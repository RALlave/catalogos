<script setup>
import { computed, onMounted } from 'vue'
import draggable from 'vuedraggable'

import AppIcon from '@/components/AppIcon.vue'
import RowActions from '@/components/RowActions.vue'
import SkeletonTable from '@/components/SkeletonTable.vue'
import { richTextToPlain } from '@/lib/richText'
import { api } from '@/services/api'
import { useCategoriesStore } from '@/stores/categories'
import { useConfirmStore } from '@/stores/confirm'
import { useUiStore } from '@/stores/ui'

const ui = useUiStore()
const confirm = useConfirmStore()
const store = useCategoriesStore()

/* El arrastre reordena la lista del store: por eso se escribe, no se copia. */
const categories = computed({
    get: () => store.items,
    set: value => {
        store.items = value
    },
})

async function remove(category) {
    const confirmed = await confirm.ask({
        title: `¿Eliminar "${category.name}"?`,
        text: 'Los productos quedan sin categoría.',
        action: 'Eliminar',
        danger: true,
    })

    if (! confirmed) {
        return
    }

    await api.delete(`/categories/${category.id}`)

    store.drop(category.id)

    ui.toast('Categoría eliminada', category.name)
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
    } catch {
        categories.value = orderBackup.map(id => categories.value.find(category => category.id === id))

        ui.toast('No pudimos guardar el orden', '', 'danger')
    }
}

/* Lo ya cargado se dibuja al instante y la petición confirma por detrás. */
onMounted(store.fetch)
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
                <span class="btn-label">Nueva categoría</span>
            </RouterLink>
        </div>
    </div>

    <section class="card">
        <div class="card-body is-flush">
            <SkeletonTable v-if="! store.loaded" :rows="5" :columns="4" />

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
                            <th class="is-hide-mobile">Descripción</th>
                            <th class="is-hide-mobile">Estado</th>
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

                                <td class="is-hide-mobile">{{ richTextToPlain(category.description) }}</td>

                                <td class="is-hide-mobile">
                                    <span
                                        class="badge badge-dot"
                                        :class="category.active ? 'badge-success' : 'badge-warning'"
                                    >
                                        {{ category.active ? 'Activa' : 'Oculta' }}
                                    </span>
                                </td>

                                <td>
                                    <RowActions
                                        :actions="[
                                            { label: 'Editar', icon: 'pencil', to: { name: 'category-edit', params: { id: category.id } } },
                                            { label: 'Eliminar', icon: 'trash', danger: true, onClick: () => remove(category) },
                                        ]"
                                    />
                                </td>
                            </tr>
                        </template>
                    </draggable>
                </table>
            </div>
        </div>
    </section>
</template>
