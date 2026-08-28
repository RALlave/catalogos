<script setup>
import { onMounted, ref } from 'vue'
import draggable from 'vuedraggable'

import AppIcon from '@/components/AppIcon.vue'
import { api } from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import { useUiStore } from '@/stores/ui'

const auth = useAuthStore()
const ui = useUiStore()

/* Cómo pasa el carrusel de un hero al siguiente. */
const EFFECTS = [
    { key: 'slide', name: 'Deslizar', hint: 'Los heros se corren de costado' },
    { key: 'fade', name: 'Fundido', hint: 'Uno se desvanece y aparece el siguiente' },
]

const heroes = ref([])
const effect = ref('slide')
const loading = ref(true)

async function load() {
    loading.value = true

    try {
        const payload = await api.get('/heroes')

        heroes.value = payload.data
    } finally {
        loading.value = false
    }
}

async function saveEffect(value) {
    const previous = effect.value

    effect.value = value

    try {
        const response = await api.put('/store', { hero_effect: value })

        auth.store = response.store

        ui.toast('Efecto guardado')
    } catch {
        effect.value = previous

        ui.toast('No pudimos guardar el efecto', '', 'danger')
    }
}

async function remove(hero) {
    if (! window.confirm(`¿Eliminar el hero "${hero.title}"?`)) {
        return
    }

    await api.delete(`/heroes/${hero.id}`)

    ui.toast('Hero eliminado', hero.title)

    await load()
}

/* Orden previo al arrastre: si la API falla, la lista vuelve a como estaba. */
let orderBackup = []

function startDrag() {
    orderBackup = heroes.value.map(hero => hero.id)
}

/* El orden se manda completo: la API lo recibe en lote. */
async function saveOrder() {
    const ids = heroes.value.map(hero => hero.id)

    if (ids.join() === orderBackup.join()) {
        return
    }

    try {
        await api.post('/heroes/reorder', { ids })

        ui.toast('Orden actualizado')

        await load()
    } catch {
        heroes.value = orderBackup.map(id => heroes.value.find(hero => hero.id === id))

        ui.toast('No pudimos guardar el orden', '', 'danger')
    }
}

onMounted(async () => {
    effect.value = auth.store?.hero_effect ?? 'slide'

    await load()
})
</script>

<template>
    <div class="page-header">
        <div class="page-title">
            <h1>Hero (banner)</h1>
            <p>Lo primero que ve tu cliente al entrar al catálogo</p>
        </div>

        <div class="page-actions">
            <RouterLink class="btn btn-primary" :to="{ name: 'hero-create' }">
                <AppIcon name="plus" />
                Nuevo hero
            </RouterLink>
        </div>
    </div>

    <div v-if="! auth.store" class="alert alert-warning">
        <AppIcon name="info" />
        <div class="alert-body">
            <strong>Primero creá tu tienda</strong>
            <span>Los heros se guardan sobre la tienda.</span>
        </div>
    </div>

    <section class="card">
        <header class="card-header">
            <div class="card-title">
                <h2>Cómo se pasa de un hero al otro</h2>
                <p>Solo se nota cuando hay más de uno cargado</p>
            </div>
        </header>

        <div class="card-body">
            <div class="option-grid">
                <label
                    v-for="item in EFFECTS"
                    :key="item.key"
                    class="option"
                    :class="{ 'is-selected': effect === item.key }"
                >
                    <input
                        type="radio"
                        name="hero-effect"
                        :value="item.key"
                        :checked="effect === item.key"
                        :disabled="! auth.store"
                        @change="saveEffect(item.key)"
                    >
                    <span class="option-body">
                        <strong>{{ item.name }}</strong>
                        <span>{{ item.hint }}</span>
                    </span>
                </label>
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card-body is-flush">
            <div v-if="loading" class="empty">
                <p>Cargando…</p>
            </div>

            <div v-else-if="! heroes.length" class="empty">
                <p>Todavía no creaste heros. Sin ninguno, el catálogo abre directo en los productos.</p>
                <RouterLink class="btn btn-primary btn-sm" :to="{ name: 'hero-create' }">
                    Crear el primero
                </RouterLink>
            </div>

            <div v-else class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="table-handle"><span class="visually-hidden">Ordenar</span></th>
                            <th>Imagen</th>
                            <th>Hero</th>
                            <th>Texto</th>
                            <th>Estado</th>
                            <th>Orden</th>
                            <th><span class="visually-hidden">Acciones</span></th>
                        </tr>
                    </thead>

                    <draggable
                        v-model="heroes"
                        tag="tbody"
                        item-key="id"
                        handle=".drag-handle"
                        ghost-class="is-drag-ghost"
                        chosen-class="is-dragging"
                        :animation="150"
                        @start="startDrag"
                        @end="saveOrder"
                    >
                        <template #item="{ element: hero }">
                            <tr>
                                <td class="table-handle">
                                    <span
                                        class="drag-handle"
                                        title="Arrastrar para ordenar"
                                        :aria-label="`Ordenar ${hero.title}`"
                                    >
                                        <AppIcon name="grip" />
                                    </span>
                                </td>

                                <td>
                                    <img v-if="hero.image_url" class="thumb thumb-wide" :src="hero.image_url" alt="">
                                    <span v-else class="badge badge-warning">Sin imagen</span>
                                </td>

                                <td>
                                    <span class="table-cell-text">
                                        <strong>{{ hero.title }}</strong>
                                        <span>{{ hero.eyebrow }}</span>
                                    </span>
                                </td>

                                <td>{{ hero.text }}</td>

                                <td>
                                    <span
                                        class="badge badge-dot"
                                        :class="hero.active ? 'badge-success' : 'badge-warning'"
                                    >
                                        {{ hero.active ? 'Visible' : 'Oculto' }}
                                    </span>
                                </td>

                                <td>{{ hero.order }}</td>

                                <td>
                                    <div class="table-actions">
                                        <RouterLink
                                            class="btn btn-ghost btn-icon"
                                            :to="{ name: 'hero-edit', params: { id: hero.id } }"
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
                                            @click="remove(hero)"
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
