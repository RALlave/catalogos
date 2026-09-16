<script setup>
import { onMounted, ref } from 'vue'

import AppIcon from '@/components/AppIcon.vue'
import FormField from '@/components/FormField.vue'
import { useUnsavedChanges } from '@/composables/useUnsavedChanges'
import { ApiError, api } from '@/services/api'
import { useUiStore } from '@/stores/ui'

const ui = useUiStore()

const form = ref({ store_trash_days: null })
const errors = ref({})
const message = ref('')
const loading = ref(false)
const ready = ref(false)

function fill(settings) {
    form.value = { store_trash_days: settings.store_trash_days }

    markSaved()
}

const { markSaved } = useUnsavedChanges({ state: () => form.value, save: submit })

/** @returns {Promise<boolean>} Si salió bien: lo mira el aviso de cambios sin guardar. */
async function submit() {
    errors.value = {}
    message.value = ''
    loading.value = true

    try {
        const response = await api.put('/admin/settings', { ...form.value })

        fill(response.settings)

        ui.toast('Ajustes guardados')

        return true
    } catch (error) {
        if (error instanceof ApiError) {
            errors.value = error.errors
            message.value = error.isValidation ? '' : error.message
        } else {
            message.value = 'No pudimos conectar con el servidor.'
        }

        return false
    } finally {
        loading.value = false
    }
}

onMounted(async () => {
    const response = await api.get('/admin/settings')

    fill(response.settings)

    ready.value = true
})
</script>

<template>
    <div class="page-header">
        <div class="page-title">
            <h1>Ajustes</h1>
            <p>Ajustes generales de la plataforma</p>
        </div>
    </div>

    <div v-if="message" class="alert alert-danger">
        <AppIcon name="alert" />
        <div class="alert-body">
            <strong>No pudimos guardar</strong>
            <span>{{ message }}</span>
        </div>
    </div>

    <form v-if="ready" novalidate @submit.prevent="submit">
        <section class="card">
            <header class="card-header">
                <div class="card-title">
                    <h2>Papelera de tiendas</h2>
                    <p>Cuánto espera una tienda en la papelera antes de borrarse sola, sin rastro</p>
                </div>
            </header>

            <div class="card-body">
                <div class="form">
                    <FormField
                        label="Días en la papelera"
                        field-id="store-trash-days"
                        hint="Al cumplirse, se borran la tienda, su dueño, sus datos y sus imágenes. Entre 1 y 3650."
                        :error="errors.store_trash_days?.[0]"
                    >
                        <input
                            id="store-trash-days"
                            v-model.number="form.store_trash_days"
                            class="input"
                            :class="{ 'has-error': errors.store_trash_days }"
                            type="number"
                            min="1"
                            max="3650"
                            step="1"
                        >
                    </FormField>
                </div>
            </div>

            <footer class="card-footer">
                <button class="btn btn-primary" type="submit" :disabled="loading">
                    <span v-if="loading" class="btn-loader" />
                    <span>{{ loading ? 'Guardando…' : 'Guardar ajustes' }}</span>
                </button>
            </footer>
        </section>
    </form>
</template>
