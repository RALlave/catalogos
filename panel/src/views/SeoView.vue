<script setup>
import { computed, onMounted, ref } from 'vue'

import AppIcon from '@/components/AppIcon.vue'
import FormField from '@/components/FormField.vue'
import MediaPicker from '@/components/MediaPicker.vue'
import { ApiError, api } from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import { useUiStore } from '@/stores/ui'

const auth = useAuthStore()
const ui = useUiStore()

const exists = computed(() => Boolean(auth.store))

const form = ref({ meta_title: '', meta_description: '' })
const picking = ref(false)
const errors = ref({})
const message = ref('')
const loading = ref(false)

function fill(store) {
    form.value = {
        meta_title: store.meta_title ?? '',
        meta_description: store.meta_description ?? '',
    }
}

async function submit() {
    errors.value = {}
    message.value = ''
    loading.value = true

    try {
        const response = await api.put('/store', { ...form.value })

        auth.store = response.store
        fill(response.store)

        ui.toast('SEO guardado')
    } catch (error) {
        if (error instanceof ApiError) {
            errors.value = error.errors
            message.value = error.isValidation ? '' : error.message
        } else {
            message.value = 'No pudimos conectar con el servidor.'
        }
    } finally {
        loading.value = false
    }
}

async function uploadImage(event) {
    const file = event.target.files?.[0]

    if (! file) {
        return
    }

    const data = new FormData()

    data.append('image', file)

    const response = await api.upload('/store/cover', data)

    auth.store = response.store

    ui.toast('Imagen guardada', 'Es la que se ve al compartir tu catálogo')
}

async function pickImage(media) {
    const response = await api.put('/store/cover', { media_id: media.id })

    auth.store = response.store

    ui.toast('Imagen guardada', 'Es la que se ve al compartir tu catálogo')
}

async function removeImage() {
    const response = await api.delete('/store/cover')

    auth.store = response.store

    ui.toast('Imagen quitada')
}

onMounted(() => {
    if (auth.store) {
        fill(auth.store)
    }
})
</script>

<template>
    <div class="page-header">
        <div class="page-title">
            <h1>SEO</h1>
            <p>Cómo se ve tu catálogo en Google y al compartirlo</p>
        </div>

        <div class="page-actions">
            <a
                v-if="exists"
                class="btn btn-outline"
                :href="auth.store.public_url"
                target="_blank"
                rel="noopener"
            >
                <AppIcon name="external" />
                Ver catálogo
            </a>
        </div>
    </div>

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
            <span>El SEO se guarda sobre la tienda.</span>
        </div>
    </div>

    <form v-else novalidate @submit.prevent="submit">
        <section class="card">
            <header class="card-header">
                <div class="card-title">
                    <h2>Buscadores</h2>
                    <p>Si los dejás vacíos se usa el nombre y la descripción de tu tienda</p>
                </div>
            </header>

            <div class="card-body">
                <div class="form">
                    <FormField
                        label="Título"
                        field-id="seo-title"
                        hint="Lo que se lee como título del resultado en Google."
                        :counter="form.meta_title"
                        :max="60"
                        :error="errors.meta_title?.[0]"
                    >
                        <input
                            id="seo-title"
                            maxlength="60"
                            v-model="form.meta_title"
                            class="input"
                            :class="{ 'has-error': errors.meta_title }"
                            type="text"
                            placeholder="Perfumería Ana — Catálogo online"
                        >
                    </FormField>

                    <FormField
                        label="Descripción"
                        field-id="seo-description"
                        hint="El párrafo corto que aparece debajo del título."
                        :counter="form.meta_description"
                        :max="160"
                        :error="errors.meta_description?.[0]"
                    >
                        <textarea
                            id="seo-description"
                            maxlength="160"
                            v-model="form.meta_description"
                            class="textarea"
                            placeholder="Perfumes importados con envío a todo el país. Consultá por WhatsApp."
                        />
                    </FormField>
                </div>
            </div>

            <footer class="card-footer">
                <button class="btn btn-primary" type="submit" :disabled="loading">
                    <span v-if="loading" class="btn-loader" />
                    <span>{{ loading ? 'Guardando…' : 'Guardar SEO' }}</span>
                </button>
            </footer>
        </section>

        <section class="card">
            <header class="card-header">
                <div class="card-title">
                    <h2>Imagen para compartir</h2>
                    <p>La que se ve cuando pegás el enlace en WhatsApp, Facebook o Instagram</p>
                </div>
            </header>

            <div class="card-body">
                <FormField label="Imagen" field-id="seo-image">
                    <img v-if="auth.store.cover_url" class="preview-wide" :src="auth.store.cover_url" alt="">

                    <input
                        id="seo-image"
                        class="input"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        @change="uploadImage"
                    >

                    <div class="table-actions">
                        <button class="btn btn-outline btn-sm" type="button" @click="picking = true">
                            <AppIcon name="image" />
                            Elegir de la biblioteca
                        </button>

                        <button
                            v-if="auth.store.cover_url"
                            class="btn btn-ghost btn-sm"
                            type="button"
                            @click="removeImage"
                        >
                            Quitar imagen
                        </button>
                    </div>
                </FormField>
            </div>
        </section>
    </form>

    <MediaPicker
        :open="picking"
        title="Elegir la imagen para compartir"
        @close="picking = false"
        @select="pickImage"
    />
</template>
