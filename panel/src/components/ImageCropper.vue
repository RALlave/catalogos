<script setup>
import 'cropperjs/dist/cropper.css'

import Cropper from 'cropperjs'
import { nextTick, onBeforeUnmount, ref, watch } from 'vue'

import AppIcon from '@/components/AppIcon.vue'
import { ApiError, api } from '@/services/api'
import { useConfirmStore } from '@/stores/confirm'
import { useUiStore } from '@/stores/ui'

/*
 * Crops a library image in place. Only the rectangle travels: the API cuts the
 * file, rewrites its variants and returns the updated media.
 */
const props = defineProps({
    open: { type: Boolean, default: false },
    /* Needs `id`, `url` and `name`. */
    media: { type: Object, default: null },
})

const emit = defineEmits(['close', 'cropped'])

const ui = useUiStore()
const confirm = useConfirmStore()

const image = ref(null)
const saving = ref(false)

let cropper = null

function destroy() {
    cropper?.destroy()
    cropper = null
}

async function start() {
    await nextTick()

    destroy()

    if (! image.value) {
        return
    }

    cropper = new Cropper(image.value, {
        viewMode: 1,
        autoCropArea: 1,
        background: false,
        zoomable: false,
        /* Only the rectangle is read, never the pixels: no CORS needed. */
        checkCrossOrigin: false,
        checkOrientation: false,
    })
}

watch(() => props.open, (open) => {
    if (open) {
        start()
    } else {
        destroy()
    }
}, { immediate: true })

onBeforeUnmount(destroy)

async function apply() {
    if (! cropper) {
        return
    }

    const confirmed = await confirm.ask({
        title: '¿Recortar la imagen?',
        text: 'Se reemplaza en todos los lugares donde se usa y no se puede deshacer.',
        action: 'Recortar',
        danger: true,
    })

    if (! confirmed) {
        return
    }

    const { x, y, width, height } = cropper.getData(true)

    saving.value = true

    try {
        const payload = await api.post(`/media/${props.media.id}/crop`, { x, y, width, height })

        ui.toast('Imagen recortada', payload.media.name)

        emit('cropped', payload.media)
        emit('close')
    } catch (error) {
        ui.toast('No pudimos recortar la imagen', error instanceof ApiError ? error.message : '', 'danger')
    } finally {
        saving.value = false
    }
}
</script>

<template>
    <Teleport to="body">
        <div v-if="open && media" class="modal modal-cropper" role="dialog" aria-modal="true" aria-label="Recortar imagen">
            <div class="modal-backdrop" @click="emit('close')" />

            <div class="modal-dialog modal-lg">
                <div class="modal-header">
                    <div class="modal-title">
                        <h2>Recortar imagen</h2>
                        <p>Arrastrá el recuadro y sus bordes para elegir qué parte queda</p>
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

                <div class="modal-body">
                    <div class="cropper-area">
                        <img ref="image" :src="media.url" :alt="media.name">
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="modal-actions">
                        <button class="btn btn-outline" type="button" @click="emit('close')">Cancelar</button>
                        <button
                            class="btn btn-primary"
                            type="button"
                            :class="{ 'is-loading': saving }"
                            :disabled="saving"
                            @click="apply"
                        >
                            Recortar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
