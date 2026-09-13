<!--
    Pestaña "Destacados" de la página Home: el interruptor de la vitrina y
    sus dos títulos.

    Qué productos entran no se elige acá: salen de los que están marcados
    como destacados en su ficha, y la API completa hasta cinco con el resto
    del catálogo para que la vitrina no quede coja.
-->

<script setup>
import { computed, onMounted, ref } from 'vue'

import AppIcon from '@/components/AppIcon.vue'
import FormField from '@/components/FormField.vue'
import { useUnsavedChanges } from '@/composables/useUnsavedChanges'
import { ApiError, api } from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import { useUiStore } from '@/stores/ui'

const auth = useAuthStore()
const ui = useUiStore()

const exists = computed(() => Boolean(auth.store))

const form = ref({ featured_enabled: true, featured_title: '', featured_subtitle: '' })
const errors = ref({})
const message = ref('')
const loading = ref(false)

function fill(store) {
    form.value = {
        featured_enabled: Boolean(store.featured_enabled),
        featured_title: store.featured_title ?? '',
        featured_subtitle: store.featured_subtitle ?? '',
    }

    markSaved()
}

/* La pestaña se cierra sin cambiar de ruta: HomePageView llama a confirmLeave. */
const { markSaved, confirmLeave } = useUnsavedChanges({ state: () => form.value, save: submit })

defineExpose({ confirmLeave })

/** @returns {Promise<boolean>} Si salió bien: lo mira el aviso de cambios sin guardar. */
async function submit() {
    errors.value = {}
    message.value = ''
    loading.value = true

    try {
        const response = await api.put('/store', { ...form.value })

        auth.store = response.store
        fill(response.store)

        ui.toast('Destacados guardado')

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

onMounted(() => {
    if (auth.store) {
        fill(auth.store)
    }
})
</script>

<template>
    <div class="tab-panel">
        <div v-if="message" class="alert alert-danger">
            <AppIcon name="alert" />
            <div class="alert-body">
                <strong>No pudimos guardar</strong>
                <span>{{ message }}</span>
            </div>
        </div>

        <div v-if="! exists" class="alert alert-warning">
            <AppIcon name="info" />
            <div class="alert-body">
                <strong>Primero creá tu tienda</strong>
                <span>Los destacados se guardan sobre la tienda.</span>
            </div>
        </div>

        <form v-else novalidate @submit.prevent="submit">
            <section class="card">
                <header class="card-header">
                    <div class="card-title">
                        <h2>Vitrina de destacados</h2>
                        <p>Va entre el banner y la grilla de productos</p>
                    </div>
                </header>

                <div class="card-body">
                    <label class="switch">
                        <input v-model="form.featured_enabled" type="checkbox">
                        <span>Mostrar la vitrina en el home</span>
                    </label>

                    <div class="field-hint">
                        <p>
                            Entran cinco productos: primero los que marcaste como destacados en su
                            ficha y, si son menos de cinco, se completa con el resto del catálogo.
                            Los agotados quedan afuera.
                        </p>
                    </div>

                    <hr>

                    <div class="form">
                        <div class="form-row">
                            <FormField
                                label="Título"
                                field-id="featured-title"
                                :counter="form.featured_title"
                                max="60"
                                hint="La primera línea, en color de acento"
                                :error="errors.featured_title?.[0]"
                            >
                                <input
                                    id="featured-title"
                                    v-model="form.featured_title"
                                    class="input"
                                    :class="{ 'has-error': errors.featured_title }"
                                    type="text"
                                    maxlength="60"
                                >
                            </FormField>
                        </div>

                        <div class="form-row">
                            <FormField
                                label="Subtítulo"
                                field-id="featured-subtitle"
                                :counter="form.featured_subtitle"
                                max="80"
                                hint="La segunda línea, más chica. Vacío no se muestra"
                                :error="errors.featured_subtitle?.[0]"
                            >
                                <input
                                    id="featured-subtitle"
                                    v-model="form.featured_subtitle"
                                    class="input"
                                    :class="{ 'has-error': errors.featured_subtitle }"
                                    type="text"
                                    maxlength="80"
                                >
                            </FormField>
                        </div>
                    </div>
                </div>

                <footer class="card-footer">
                    <button class="btn btn-primary" type="submit" :disabled="loading">
                        <span v-if="loading" class="btn-loader" />
                        <span>{{ loading ? 'Guardando…' : 'Guardar destacados' }}</span>
                    </button>
                </footer>
            </section>
        </form>
    </div>
</template>
