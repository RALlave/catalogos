<!--
    Pestaña "Hero (banner)" de la página Home: el efecto del carrusel y la
    lista de heros, que se arrastra para ordenar.

    Salió de HeroesView.vue, que dejó de ser una pantalla propia cuando el
    hero pasó a ser una sección más del home.
-->

<script setup>
import { onMounted, ref } from 'vue'
import draggable from 'vuedraggable'

import AppIcon from '@/components/AppIcon.vue'
import RowActions from '@/components/RowActions.vue'
import { richTextToPlain } from '@/lib/richText'
import { ApiError, api } from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import { useConfirmStore } from '@/stores/confirm'
import { useUiStore } from '@/stores/ui'

const auth = useAuthStore()
const ui = useUiStore()
const confirm = useConfirmStore()

/* Cómo pasa el carrusel de un hero al siguiente. */
const EFFECTS = [
    { key: 'slide', name: 'Deslizar', hint: 'Los heros se corren de costado' },
    { key: 'fade', name: 'Fundido', hint: 'Uno se desvanece y aparece el siguiente' },
]

const heroes = ref([])
const effect = ref('slide')
const loading = ref(true)

/* The carousel settings live in a modal opened from the gear button. */
const settingsOpen = ref(false)

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
    const confirmed = await confirm.ask({
        title: `¿Eliminar el hero "${hero.title}"?`,
        text: 'Deja de verse en el banner de tu catálogo.',
        action: 'Eliminar',
        danger: true,
    })

    if (! confirmed) {
        return
    }

    await api.delete(`/heroes/${hero.id}`)

    ui.toast('Hero eliminado', hero.title)

    await load()
}

/* Hero being duplicated, to disable its button while the request runs. */
const duplicating = ref(null)

/* The copy is born hidden and at the end: it is reviewed before it shows up. */
async function duplicate(hero) {
    duplicating.value = hero.id

    try {
        const payload = await api.post(`/heroes/${hero.id}/clone`)

        ui.toast('Hero duplicado', `${payload.hero.title} · se creó oculto`)

        await load()
    } catch (error) {
        ui.toast('No pudimos duplicar el hero', error instanceof ApiError ? error.message : '', 'danger')
    } finally {
        duplicating.value = null
    }
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
    <div class="tab-panel">
        <section class="card">
            <header class="card-header">
                <div class="card-title">
                    <h2>Heros del banner</h2>
                    <p>Arrastrá para cambiar en qué orden se muestran</p>
                </div>

                <div class="page-actions">
                    <button
                        class="btn btn-outline btn-sm btn-icon"
                        type="button"
                        title="Ajustes del carrusel"
                        aria-label="Ajustes del carrusel"
                        @click="settingsOpen = true"
                    >
                        <AppIcon name="settings" />
                    </button>

                    <RouterLink class="btn btn-primary btn-sm" :to="{ name: 'hero-create' }">
                        <AppIcon name="plus" />
                        <span class="btn-label">Nuevo hero</span>
                    </RouterLink>
                </div>
            </header>

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
                                <th class="is-hide-mobile">Texto</th>
                                <th class="is-hide-mobile">Estado</th>
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
                                        <img v-if="hero.image_thumb_url" class="thumb thumb-wide" :src="hero.image_thumb_url" alt="">
                                        <span v-else class="badge badge-warning">Sin imagen</span>
                                    </td>

                                    <td>
                                        <strong>{{ hero.title }}</strong>
                                    </td>

                                    <td class="is-hide-mobile">{{ richTextToPlain(hero.text) }}</td>

                                    <td class="is-hide-mobile">
                                        <span
                                            class="badge badge-dot"
                                            :class="hero.active ? 'badge-success' : 'badge-warning'"
                                        >
                                            {{ hero.active ? 'Visible' : 'Oculto' }}
                                        </span>
                                    </td>

                                    <td>
                                        <RowActions
                                            :actions="[
                                                { label: 'Editar', icon: 'pencil', to: { name: 'hero-edit', params: { id: hero.id } } },
                                                { label: 'Duplicar', icon: 'copy', disabled: duplicating === hero.id, onClick: () => duplicate(hero) },
                                                { label: 'Eliminar', icon: 'trash', danger: true, onClick: () => remove(hero) },
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

        <Teleport to="body">
            <div v-if="settingsOpen" class="modal" role="dialog" aria-modal="true" aria-label="Ajustes del carrusel">
                <div class="modal-backdrop" @click="settingsOpen = false" />

                <div class="modal-dialog">
                    <div class="modal-header">
                        <div class="modal-title">
                            <h2>Cómo se pasa de un hero al otro</h2>
                            <p>Solo se nota cuando hay más de uno cargado</p>
                        </div>

                        <button
                            class="btn btn-ghost btn-icon"
                            type="button"
                            title="Cerrar"
                            aria-label="Cerrar"
                            @click="settingsOpen = false"
                        >
                            <AppIcon name="close" />
                        </button>
                    </div>

                    <div class="modal-body">
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

                    <div class="modal-footer">
                        <div class="modal-actions">
                            <button class="btn btn-outline" type="button" @click="settingsOpen = false">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
