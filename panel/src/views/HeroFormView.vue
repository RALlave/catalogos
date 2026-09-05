<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import AppIcon from '@/components/AppIcon.vue'
import FormField from '@/components/FormField.vue'
import MediaPicker from '@/components/MediaPicker.vue'
import { ApiError, api } from '@/services/api'
import { REQUIRED_TOAST, checkRequired, hasErrors } from '@/services/validation'
import { useUiStore } from '@/stores/ui'

const route = useRoute()
const router = useRouter()
const ui = useUiStore()

const id = computed(() => route.params.id)
const isEdit = computed(() => Boolean(id.value))

const form = ref({ media_id: null, eyebrow: '', title: '', text: '', active: true })
const imageUrl = ref(null)
const picking = ref(false)
const errors = ref({})
const message = ref('')
const loading = ref(false)

function pickImage(media) {
    form.value.media_id = media.id
    imageUrl.value = media.url
}

function removeImage() {
    form.value.media_id = null
    imageUrl.value = null
}

async function submit() {
    errors.value = checkRequired(form.value, ['title'])
    message.value = ''

    if (hasErrors(errors.value)) {
        ui.toast(REQUIRED_TOAST, '', 'danger')

        return
    }

    loading.value = true

    try {
        if (isEdit.value) {
            await api.put(`/heroes/${id.value}`, form.value)
        } else {
            await api.post('/heroes', form.value)
        }

        ui.toast(isEdit.value ? 'Hero actualizado' : 'Hero creado', form.value.title)

        await router.push({ name: 'heroes' })
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

onMounted(async () => {
    if (! isEdit.value) {
        return
    }

    const payload = await api.get(`/heroes/${id.value}`)

    form.value = {
        media_id: payload.hero.media_id,
        eyebrow: payload.hero.eyebrow ?? '',
        title: payload.hero.title,
        text: payload.hero.text ?? '',
        active: payload.hero.active,
    }

    imageUrl.value = payload.hero.image_url
})
</script>

<template>
    <div class="page-header">
        <div class="page-title">
            <h1>{{ isEdit ? 'Editar hero' : 'Nuevo hero' }}</h1>
            <p>La imagen y los textos con los que abre tu catálogo</p>
        </div>

        <div class="page-actions">
            <RouterLink class="btn btn-outline" :to="{ name: 'heroes' }">Volver</RouterLink>
        </div>
    </div>

    <div v-if="message" class="alert alert-danger">
        <AppIcon name="alert" />
        <div class="alert-body">
            <strong>No pudimos guardar</strong>
            <span>{{ message }}</span>
        </div>
    </div>

    <form novalidate @submit.prevent="submit">
        <section class="card">
            <header class="card-header">
                <div class="card-title">
                    <h2>Imagen de fondo</h2>
                    <p>Se ve a lo ancho, así que conviene una foto apaisada</p>
                </div>
            </header>

            <div class="card-body">
                <FormField label="Imagen" field-id="hero-image" :error="errors.media_id?.[0]">
                    <img v-if="imageUrl" class="preview-wide" :src="imageUrl" alt="">

                    <div class="table-actions">
                        <button class="btn btn-outline btn-sm" type="button" @click="picking = true">
                            <AppIcon name="image" />
                            Elegir de la biblioteca
                        </button>

                        <button v-if="imageUrl" class="btn btn-ghost btn-sm" type="button" @click="removeImage">
                            Quitar imagen
                        </button>
                    </div>
                </FormField>
            </div>
        </section>

        <section class="card">
            <header class="card-header">
                <div class="card-title">
                    <h2>Textos</h2>
                </div>
            </header>

            <div class="card-body">
                <div class="form">
                    <FormField
                        label="Eyebrow"
                        field-id="hero-eyebrow"
                        hint="El texto chico que va arriba del título."
                        :counter="form.eyebrow"
                        :max="120"
                        :error="errors.eyebrow?.[0]"
                    >
                        <input
                            id="hero-eyebrow"
                            maxlength="120"
                            v-model="form.eyebrow"
                            class="input"
                            type="text"
                            placeholder="Catálogo online · Consultá por WhatsApp"
                        >
                    </FormField>

                    <FormField
                        label="Título"
                        field-id="hero-title"
                        :counter="form.title"
                        :max="120"
                        :error="errors.title?.[0]"
                    >
                        <input
                            id="hero-title"
                            maxlength="120"
                            v-model="form.title"
                            class="input"
                            :class="{ 'has-error': errors.title }"
                            type="text"
                            placeholder="Elegí lo que te gusta y pedilo por WhatsApp"
                        >
                    </FormField>

                    <FormField
                        label="Texto"
                        field-id="hero-text"
                        :counter="form.text"
                        :max="255"
                        :error="errors.text?.[0]"
                    >
                        <textarea
                            id="hero-text"
                            maxlength="255"
                            v-model="form.text"
                            class="textarea"
                            placeholder="Todo el catálogo con precio a la vista."
                        />
                    </FormField>

                    <label class="check">
                        <input v-model="form.active" type="checkbox">
                        <span>Mostrar este hero en el catálogo</span>
                    </label>
                </div>
            </div>

            <footer class="card-footer">
                <RouterLink class="btn btn-outline" :to="{ name: 'heroes' }">Cancelar</RouterLink>

                <button class="btn btn-primary" type="submit" :disabled="loading">
                    <span v-if="loading" class="btn-loader" />
                    <span>{{ loading ? 'Guardando…' : 'Guardar hero' }}</span>
                </button>
            </footer>
        </section>
    </form>

    <MediaPicker
        :open="picking"
        title="Elegir la imagen del hero"
        @close="picking = false"
        @select="pickImage"
    />
</template>
