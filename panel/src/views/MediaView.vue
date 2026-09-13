<script setup>
import { computed, onMounted, ref, watch } from 'vue'

import AppIcon from '@/components/AppIcon.vue'
import FormField from '@/components/FormField.vue'
import { useUnsavedChanges } from '@/composables/useUnsavedChanges'
import { ApiError, api } from '@/services/api'
import { useConfirmStore } from '@/stores/confirm'
import { useUiStore } from '@/stores/ui'

const ui = useUiStore()
const confirm = useConfirmStore()

const items = ref([])
const meta = ref(null)
const page = ref(1)
const search = ref('')
const loading = ref(true)
const uploading = ref(false)

const current = ref(null)
const form = ref({ name: '', alt: '' })
const errors = ref({})
const saving = ref(false)

const usedBy = computed(() => current.value?.used_by ?? [])
const usedInHeroes = computed(() => current.value?.used_in_heroes ?? [])

async function load() {
    loading.value = true

    try {
        const payload = await api.get('/media', { search: search.value, page: page.value })

        items.value = payload.data
        meta.value = payload.meta
    } finally {
        loading.value = false
    }
}

async function upload(event) {
    const files = Array.from(event.target.files ?? [])

    if (! files.length) {
        return
    }

    uploading.value = true

    try {
        const data = new FormData()

        files.forEach(file => data.append('images[]', file))

        await api.upload('/media', data)

        ui.toast(files.length > 1 ? `${files.length} imágenes subidas` : 'Imagen subida')

        page.value = 1

        await load()
    } catch (error) {
        ui.toast('No pudimos subir las imágenes', error instanceof ApiError ? error.first('images.0') ?? '' : '', 'danger')
    } finally {
        uploading.value = false
        event.target.value = ''
    }
}

/* El detalle es un modal: los cambios sólo cuentan mientras está abierto. */
const { markSaved, confirmLeave } = useUnsavedChanges({
    state: () => form.value,
    save,
    active: () => Boolean(current.value),
})

function open(media) {
    current.value = media
    form.value = { name: media.name, alt: media.alt ?? '' }
    errors.value = {}

    markSaved()
}

/** Cierra sin preguntar: la usa lo que ya resolvió qué hacer con los cambios. */
function dismiss() {
    current.value = null
}

async function close() {
    if (await confirmLeave()) {
        dismiss()
    }
}

/** @returns {Promise<boolean>} Si salió bien: lo mira el aviso de cambios sin guardar. */
async function save() {
    saving.value = true
    errors.value = {}

    try {
        const payload = await api.put(`/media/${current.value.id}`, form.value)

        current.value = payload.media
        items.value = items.value.map(item => (item.id === payload.media.id ? payload.media : item))

        markSaved()

        ui.toast('Imagen actualizada', payload.media.name)

        return true
    } catch (error) {
        if (error instanceof ApiError) {
            errors.value = error.errors
        }

        return false
    } finally {
        saving.value = false
    }
}

/**
 * Cuántas cosas se romperían si se borra. Se cuenta todo, no sólo los
 * productos: una imagen que es el logo también está en uso, y hasta ahora eso
 * recién se veía al confirmar el borrado.
 */
function uses(media) {
    return (media.used_by?.length ?? 0)
        + (media.used_in_heroes?.length ?? 0)
        + (media.used_as_logo ? 1 : 0)
        + (media.used_as_cover ? 1 : 0)
}

/** Qué la está usando, para el title de la tarjeta. */
function usesTitle(media) {
    const parts = []

    if (media.used_by?.length) {
        parts.push(`${media.used_by.length} ${media.used_by.length === 1 ? 'producto' : 'productos'}`)
    }

    if (media.used_in_heroes?.length) {
        parts.push(`${media.used_in_heroes.length} ${media.used_in_heroes.length === 1 ? 'hero' : 'heros'}`)
    }

    if (media.used_as_logo) {
        parts.push('el logo')
    }

    if (media.used_as_cover) {
        parts.push('la imagen para compartir')
    }

    return `Se usa en ${parts.join(', ')}`
}

/* Sólo llega acá una imagen que no usa nadie: el aviso de a quién afectaba el
   borrado se fue con la regla de no borrar lo que está en uso. */
async function remove(media) {
    const confirmed = await confirm.ask({
        title: `¿Eliminar "${media.name}"?`,
        text: 'Se borra el archivo y no se puede deshacer.',
        action: 'Eliminar',
        danger: true,
    })

    if (! confirmed) {
        return
    }

    await api.delete(`/media/${media.id}`)

    ui.toast('Imagen eliminada', media.name)

    dismiss()

    await load()
}

/** Peso legible: los KB alcanzan hasta el mega, después van los MB. */
function weight(size) {
    return size < 1024 * 1024
        ? `${Math.round(size / 1024)} KB`
        : `${(size / 1024 / 1024).toFixed(1)} MB`
}

let searchTimer

watch(search, () => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(() => {
        page.value = 1
        load()
    }, 350)
})

watch(page, load)

onMounted(load)
</script>

<template>
    <div class="page-header">
        <div class="page-title">
            <h1>Multimedia</h1>
            <p>Las imágenes de tu tienda, en un solo lugar</p>
        </div>

        <div class="page-actions">
            <label class="btn btn-primary" :class="{ 'is-loading': uploading }">
                <AppIcon name="plus" />
                Subir imágenes
                <input
                    class="visually-hidden"
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    multiple
                    @change="upload"
                >
            </label>
        </div>
    </div>

    <section class="card">
        <div class="toolbar">
            <div class="search toolbar-search">
                <AppIcon name="search" />
                <input
                    v-model="search"
                    class="input"
                    type="search"
                    placeholder="Buscar por nombre…"
                    aria-label="Buscar imagen"
                    maxlength="255"
                >
            </div>

            <div class="toolbar-count">{{ meta?.total ?? 0 }} imágenes</div>
        </div>

        <div class="card-body">
            <div v-if="loading" class="empty">
                <p>Cargando…</p>
            </div>

            <div v-else-if="! items.length" class="empty">
                <span class="empty-icon">
                    <AppIcon name="image" />
                </span>
                <p>Todavía no subiste imágenes.</p>
            </div>

            <div v-else class="media-grid">
                <button
                    v-for="media in items"
                    :key="media.id"
                    class="media-card"
                    type="button"
                    @click="open(media)"
                >
                    <span class="media-card-image">
                        <img :src="media.thumb_url" :alt="media.alt ?? ''">
                    </span>

                    <span class="media-card-body">
                        <strong>{{ media.name }}</strong>
                        <span>{{ media.width }}×{{ media.height }} · {{ weight(media.size) }}</span>
                    </span>

                    <span v-if="uses(media)" class="media-card-uses" :title="usesTitle(media)">En uso</span>
                </button>
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

    <Teleport to="body">
        <div v-if="current" class="modal" role="dialog" aria-modal="true" aria-label="Detalle de la imagen">
            <div class="modal-backdrop" @click="close" />

            <div class="modal-dialog">
                <div class="modal-header">
                    <div class="modal-title">
                        <h2>{{ current.name }}</h2>
                        <p>{{ current.width }}×{{ current.height }} · {{ weight(current.size) }} · {{ current.mime }}</p>
                    </div>

                    <button
                        class="btn btn-ghost btn-icon"
                        type="button"
                        title="Cerrar"
                        aria-label="Cerrar"
                        @click="close"
                    >
                        <AppIcon name="close" />
                    </button>
                </div>

                <div class="modal-body">
                    <div class="media-detail">
                        <div class="media-detail-image">
                            <img :src="current.url" :alt="current.alt ?? ''">
                        </div>

                        <div class="media-detail-side">
                            <div class="form">
                                <FormField
                                    label="Nombre"
                                    field-id="media-name"
                                    :error="errors.name?.[0]"
                                >
                                    <input
                                        id="media-name"
                                        v-model="form.name"
                                        class="input"
                                        type="text"
                                        maxlength="255"
                                    >
                                </FormField>

                                <FormField
                                    label="Texto alternativo"
                                    field-id="media-alt"
                                    hint="Lo que lee un buscador o alguien que no ve la imagen."
                                    :error="errors.alt?.[0]"
                                >
                                    <input
                                        id="media-alt"
                                        v-model="form.alt"
                                        class="input"
                                        type="text"
                                        maxlength="255"
                                    >
                                </FormField>
                            </div>

                            <div class="media-uses">
                                <strong>Dónde se usa</strong>

                                <ul v-if="usedBy.length || usedInHeroes.length || current.used_as_logo || current.used_as_cover">
                                    <li v-for="product in usedBy" :key="product.id">
                                        <RouterLink :to="{ name: 'product-edit', params: { id: product.id } }">
                                            {{ product.name }}
                                        </RouterLink>
                                    </li>
                                    <li v-for="hero in usedInHeroes" :key="`hero-${hero.id}`">
                                        <RouterLink :to="{ name: 'hero-edit', params: { id: hero.id } }">
                                            {{ hero.title }}
                                        </RouterLink>
                                    </li>
                                    <li v-if="current.used_as_logo">
                                        <RouterLink :to="{ name: 'store' }">Logo de la tienda</RouterLink>
                                    </li>
                                    <li v-if="current.used_as_cover">
                                        <RouterLink :to="{ name: 'seo' }">Imagen para compartir</RouterLink>
                                    </li>
                                </ul>

                                <p v-else>No la usa nadie todavía.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <!-- La copia de un logo de la plataforma no la borra el
                         dueño: entra y sale sola al elegir el logo. -->
                    <div v-if="current.from_platform" class="media-locked">
                        <AppIcon name="info" />
                        <span>La administra la plataforma</span>
                    </div>

                    <!-- En uso: borrarla dejaría sin foto a lo que la muestra.
                         Primero hay que sacarla de ahí. -->
                    <div v-else-if="uses(current)" class="media-locked">
                        <AppIcon name="info" />
                        <span>{{ usesTitle(current) }}: sacala de ahí para poder borrarla</span>
                    </div>

                    <button v-else class="btn btn-danger" type="button" @click="remove(current)">
                        <AppIcon name="trash" />
                        Eliminar
                    </button>

                    <div class="modal-actions">
                        <button class="btn btn-outline" type="button" @click="close">Cerrar</button>
                        <button
                            class="btn btn-primary"
                            type="button"
                            :class="{ 'is-loading': saving }"
                            @click="save"
                        >
                            Guardar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
