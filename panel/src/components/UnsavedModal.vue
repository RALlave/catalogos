<!--
    Aviso de cambios sin guardar. Es único para todo el panel: lo abre el
    composable useUnsavedChanges cuando el usuario intenta irse de un
    formulario con cambios.
-->

<script setup>
import { onBeforeUnmount, onMounted } from 'vue'

import AppIcon from '@/components/AppIcon.vue'
import { useUnsavedStore } from '@/stores/unsaved'

const unsaved = useUnsavedStore()

/* Escape es "seguir editando": la salida siempre se elige a propósito. */
function onKeydown(event) {
    if (event.key === 'Escape' && unsaved.open) {
        unsaved.stay()
    }
}

onMounted(() => document.addEventListener('keydown', onKeydown))
onBeforeUnmount(() => document.removeEventListener('keydown', onKeydown))
</script>

<template>
    <Teleport to="body">
        <div
            v-if="unsaved.open"
            class="modal modal-confirm"
            role="dialog"
            aria-modal="true"
            aria-label="Cambios sin guardar"
        >
            <div class="modal-backdrop" @click="unsaved.stay()" />

            <div class="modal-dialog modal-sm">
                <div class="modal-header">
                    <span class="modal-icon">
                        <AppIcon name="alert" />
                    </span>

                    <div class="modal-title">
                        <h2>Tenés cambios sin guardar</h2>
                        <p>Si salís ahora se pierde lo que cargaste.</p>
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="modal-actions">
                        <button
                            class="btn btn-outline"
                            type="button"
                            :disabled="unsaved.saving"
                            @click="unsaved.stay()"
                        >
                            Seguir editando
                        </button>

                        <button
                            class="btn btn-danger"
                            type="button"
                            :disabled="unsaved.saving"
                            @click="unsaved.discard()"
                        >
                            Salir sin guardar
                        </button>

                        <button
                            v-if="unsaved.canSave"
                            class="btn btn-primary"
                            type="button"
                            :disabled="unsaved.saving"
                            @click="unsaved.saveAndLeave()"
                        >
                            <span v-if="unsaved.saving" class="btn-loader" />
                            <span>{{ unsaved.saving ? 'Guardando…' : 'Guardar y salir' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
