<script setup>
import { ref, watch } from 'vue'

import AppIcon from '@/components/AppIcon.vue'
import { api } from '@/services/api'
import { useUiStore } from '@/stores/ui'

const props = defineProps({
    open: { type: Boolean, default: false },
    /* Simple para el logo o la portada, múltiple para la galería del producto. */
    multiple: { type: Boolean, default: false },
    title: { type: String, default: 'Biblioteca' },
})

const emit = defineEmits(['close', 'select'])

const ui = useUiStore()

const items = ref([])
const meta = ref(null)
const page = ref(1)
const search = ref('')
const loading = ref(false)
const uploading = ref(false)
const selected = ref([])

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

/** Lo recién subido queda elegido: es lo que el usuario vino a buscar. */
async function upload(event) {
    const files = Array.from(event.target.files ?? [])

    if (! files.length) {
        return
    }

    uploading.value = true

    try {
        const data = new FormData()

        files.forEach(file => data.append('images[]', file))

        const payload = await api.upload('/media', data)

        page.value = 1
        search.value = ''

        await load()

        payload.media.forEach(media => pick(media))
    } catch {
        ui.toast('No pudimos subir las imágenes', '', 'danger')
    } finally {
        uploading.value = false
        event.target.value = ''
    }
}

function isPicked(media) {
    return selected.value.some(item => item.id === media.id)
}

function pick(media) {
    if (! props.multiple) {
        selected.value = [media]

        return
    }

    selected.value = isPicked(media)
        ? selected.value.filter(item => item.id !== media.id)
        : [...selected.value, media]
}

function confirm() {
    if (! selected.value.length) {
        return
    }

    emit('select', props.multiple ? selected.value : selected.value[0])
    emit('close')
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

watch(() => props.open, (open) => {
    if (! open) {
        return
    }

    selected.value = []
    search.value = ''
    page.value = 1

    load()
})
</script>

<template>
    <Teleport to="body">
        <div v-if="open" class="modal" role="dialog" aria-modal="true" aria-label="Biblioteca de imágenes">
            <div class="modal-backdrop" @click="emit('close')" />

            <div class="modal-dialog modal-lg">
                <div class="modal-header">
                    <div class="modal-title">
                        <h2>{{ title }}</h2>
                        <p>{{ multiple ? 'Elegí una o varias imágenes' : 'Elegí una imagen' }}</p>
                    </div>

                    <button
                        class="btn btn-ghost btn-icon"
                        type="button"
                        title="Cerrar"
                        aria-label="Cerrar"
                        @click="emit('close')"
                    >
                        <AppIcon name="close" />
                    </button>
                </div>

                <div class="modal-toolbar">
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

                    <label class="btn btn-outline btn-sm" :class="{ 'is-loading': uploading }">
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

                <div class="modal-body">
                    <div v-if="loading" class="empty">
                        <p>Cargando…</p>
                    </div>

                    <div v-else-if="! items.length" class="empty">
                        <span class="empty-icon">
                            <AppIcon name="image" />
                        </span>
                        <p>No hay imágenes en la biblioteca.</p>
                    </div>

                    <div v-else class="media-grid">
                        <button
                            v-for="media in items"
                            :key="media.id"
                            class="media-card"
                            :class="{ 'is-picked': isPicked(media) }"
                            type="button"
                            @click="pick(media)"
                        >
                            <span class="media-card-image">
                                <img :src="media.url" :alt="media.alt ?? ''">
                            </span>

                            <span class="media-card-body">
                                <strong>{{ media.name }}</strong>
                                <span>{{ media.width }}×{{ media.height }}</span>
                            </span>

                            <span v-if="isPicked(media)" class="media-card-check">
                                <AppIcon name="check" />
                            </span>
                        </button>
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="page-info">
                        <span v-if="selected.length">{{ selected.length }} seleccionadas</span>
                        <span v-else-if="meta">{{ meta.total }} imágenes</span>
                    </div>

                    <nav v-if="meta && meta.last_page > 1" class="pagination" aria-label="Paginación">
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

                    <div class="modal-actions">
                        <button class="btn btn-outline" type="button" @click="emit('close')">Cancelar</button>
                        <button
                            class="btn btn-primary"
                            type="button"
                            :disabled="! selected.length"
                            @click="confirm"
                        >
                            Usar {{ selected.length > 1 ? `${selected.length} imágenes` : 'imagen' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
