<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import AppIcon from '@/components/AppIcon.vue'
import FormField from '@/components/FormField.vue'
import ImageCropper from '@/components/ImageCropper.vue'
import MediaPicker from '@/components/MediaPicker.vue'
import RichTextEditor from '@/components/RichTextEditor.vue'
import { useUnsavedChanges } from '@/composables/useUnsavedChanges'
import { richTextCounter } from '@/lib/richText'
import { ApiError, api } from '@/services/api'
import { REQUIRED_TOAST, checkRequired, hasErrors } from '@/services/validation'
import { useCategoriesStore } from '@/stores/categories'
import { useUiStore } from '@/stores/ui'

const route = useRoute()
const router = useRouter()
const ui = useUiStore()
const categories = useCategoriesStore()

const id = computed(() => route.params.id)
const isEdit = computed(() => Boolean(id.value))

const form = ref({
    media_id: null,
    category_id: null,
    eyebrow: '',
    title: '',
    text: '',
    button_text: 'Ver catálogo',
    link: 'products',
    align: 'center',
    active: true,
})

/*
 * The destination select holds a single value: a link key, or
 * `category:{id}` for a category. It is split back into `link` and
 * `category_id`, which is what the API stores.
 */
const destination = computed({
    get: () => form.value.link === 'category' && form.value.category_id
        ? `category:${form.value.category_id}`
        : form.value.link,
    set: (value) => {
        if (value.startsWith('category:')) {
            form.value.link = 'category'
            form.value.category_id = Number(value.slice(9))
        } else {
            form.value.link = value
            form.value.category_id = null
        }
    },
})
const imageUrl = ref(null)
const picking = ref(false)
const errors = ref({})
const message = ref('')
const loading = ref(false)

function pickImage(media) {
    form.value.media_id = media.id
    imageUrl.value = media.url
}

const cropping = ref(false)

/* The crop is saved on the media right away: nothing changes in the hero form. */
function onCropped(media) {
    imageUrl.value = media.url
}

function removeImage() {
    form.value.media_id = null
    imageUrl.value = null
}

const { markSaved } = useUnsavedChanges({ state: () => form.value, save: persist })

/** Guarda y devuelve si salió bien. No navega: de eso se encarga submit(). */
async function persist() {
    errors.value = checkRequired(form.value, ['title'])
    message.value = ''

    if (hasErrors(errors.value)) {
        ui.toast(REQUIRED_TOAST, '', 'danger')

        return false
    }

    loading.value = true

    try {
        if (isEdit.value) {
            await api.put(`/heroes/${id.value}`, form.value)
        } else {
            await api.post('/heroes', form.value)
        }

        markSaved()

        ui.toast(isEdit.value ? 'Hero actualizado' : 'Hero creado', form.value.title)

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

async function submit() {
    if (await persist()) {
        await router.push({ name: 'page-home', query: { seccion: 'hero' } })
    }
}

onMounted(async () => {
    /* The button target select lists the store categories. */
    categories.fetch()

    if (! isEdit.value) {
        return
    }

    const payload = await api.get(`/heroes/${id.value}`)

    form.value = {
        media_id: payload.hero.media_id,
        category_id: payload.hero.category_id,
        eyebrow: payload.hero.eyebrow ?? '',
        title: payload.hero.title,
        text: payload.hero.text ?? '',
        button_text: payload.hero.button_text ?? '',
        link: payload.hero.link,
        align: payload.hero.align,
        active: payload.hero.active,
    }

    imageUrl.value = payload.hero.image_url

    markSaved()
})
</script>

<template>
    <div class="page-header">
        <div class="page-title">
            <h1>{{ isEdit ? 'Editar hero' : 'Nuevo hero' }}</h1>
            <p>La imagen y los textos con los que abre tu catálogo</p>
        </div>

        <div class="page-actions">
            <RouterLink class="btn btn-outline" :to="{ name: 'page-home', query: { seccion: 'hero' } }">Volver</RouterLink>
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

                        <button v-if="imageUrl" class="btn btn-outline btn-sm" type="button" @click="cropping = true">
                            <AppIcon name="pencil" />
                            Recortar
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
                    <div class="form-cols form-cols-2">
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
                    </div>

                    <FormField
                        label="Texto"
                        field-id="hero-text"
                        :counter="richTextCounter(form.text)"
                        :max="255"
                        :error="errors.text?.[0]"
                    >
                        <RichTextEditor
                            id="hero-text"
                            v-model="form.text"
                            :max="255"
                            :has-error="Boolean(errors.text)"
                            placeholder="Todo el catálogo con precio a la vista."
                        />
                    </FormField>

                    <div class="form-cols">
                        <FormField
                            label="Texto del botón"
                            field-id="hero-button-text"
                            hint="Si lo dejás vacío, el botón no se muestra."
                            :counter="form.button_text"
                            :max="30"
                            :error="errors.button_text?.[0]"
                        >
                            <input
                                id="hero-button-text"
                                maxlength="30"
                                v-model="form.button_text"
                                class="input"
                                type="text"
                                placeholder="Ver catálogo"
                            >
                        </FormField>

                        <FormField
                            label="El botón lleva a"
                            field-id="hero-link"
                            hint="Si la categoría está oculta o la sección Destacados está apagada, lleva a Productos."
                            :error="errors.link?.[0] || errors.category_id?.[0]"
                        >
                            <select id="hero-link" v-model="destination" class="select">
                                <optgroup label="Anclas">
                                    <option value="products">Productos</option>
                                    <option value="featured">Destacados</option>
                                </optgroup>
                                <optgroup label="Páginas">
                                    <option value="home">Inicio</option>
                                    <option value="contact">Contacto</option>
                                </optgroup>
                                <optgroup v-if="categories.items.length" label="Categorías">
                                    <option v-for="category in categories.items" :key="category.id" :value="`category:${category.id}`">
                                        {{ category.name }}{{ category.active ? '' : ' (oculta)' }}
                                    </option>
                                </optgroup>
                            </select>
                        </FormField>

                        <FormField
                            label="Alineación del contenido"
                            field-id="hero-align"
                            :error="errors.align?.[0]"
                        >
                            <select id="hero-align" v-model="form.align" class="select">
                                <option value="center">Centro</option>
                                <option value="left">Izquierda</option>
                            </select>
                        </FormField>
                    </div>

                    <label class="check">
                        <input v-model="form.active" type="checkbox">
                        <span>Mostrar este hero en el catálogo</span>
                    </label>
                </div>
            </div>

            <footer class="card-footer">
                <RouterLink class="btn btn-outline" :to="{ name: 'page-home', query: { seccion: 'hero' } }">Cancelar</RouterLink>

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

    <ImageCropper
        :open="cropping"
        :media="form.media_id ? { id: form.media_id, url: imageUrl, name: form.title } : null"
        @close="cropping = false"
        @cropped="onCropped"
    />
</template>
