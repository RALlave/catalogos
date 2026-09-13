<!--
    Las confirmaciones del panel. Es uno solo para toda la aplicación: lo abre
    el store `confirm` y quien preguntó espera la respuesta.
-->

<script setup>
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'

import AppIcon from '@/components/AppIcon.vue'
import { useConfirmStore } from '@/stores/confirm'

const confirm = useConfirmStore()

const cancelButton = ref(null)

/* El foco arranca en Cancelar: la tecla Enter no borra nada por accidente. */
watch(() => confirm.open, async isOpen => {
    if (! isOpen) {
        return
    }

    await nextTick()

    cancelButton.value?.focus()
})

function onKeydown(event) {
    if (event.key === 'Escape' && confirm.open) {
        confirm.cancel()
    }
}

onMounted(() => document.addEventListener('keydown', onKeydown))
onBeforeUnmount(() => document.removeEventListener('keydown', onKeydown))
</script>

<template>
    <Teleport to="body">
        <div
            v-if="confirm.open"
            class="modal modal-confirm"
            role="dialog"
            aria-modal="true"
            :aria-label="confirm.title"
        >
            <div class="modal-backdrop" @click="confirm.cancel()" />

            <div class="modal-dialog modal-sm">
                <div class="modal-header">
                    <span class="modal-icon" :class="{ 'is-danger': confirm.danger }">
                        <AppIcon name="alert" />
                    </span>

                    <div class="modal-title">
                        <h2>{{ confirm.title }}</h2>
                        <p v-if="confirm.text">{{ confirm.text }}</p>
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="modal-actions">
                        <button
                            ref="cancelButton"
                            class="btn btn-outline"
                            type="button"
                            @click="confirm.cancel()"
                        >
                            Cancelar
                        </button>

                        <button
                            class="btn"
                            :class="confirm.danger ? 'btn-danger' : 'btn-primary'"
                            type="button"
                            @click="confirm.accept()"
                        >
                            {{ confirm.action }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
