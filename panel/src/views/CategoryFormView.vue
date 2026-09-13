<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import AppIcon from '@/components/AppIcon.vue'
import FormField from '@/components/FormField.vue'
import SkeletonForm from '@/components/SkeletonForm.vue'
import { useUnsavedChanges } from '@/composables/useUnsavedChanges'
import { ApiError, api } from '@/services/api'
import { REQUIRED_TOAST, checkRequired, hasErrors } from '@/services/validation'
import { useCategoriesStore } from '@/stores/categories'
import { useUiStore } from '@/stores/ui'

const route = useRoute()
const router = useRouter()
const ui = useUiStore()
const store = useCategoriesStore()

const id = computed(() => route.params.id)
const isEdit = computed(() => Boolean(id.value))

const form = ref({ name: '', slug: '', description: '', active: true })
const errors = ref({})
const message = ref('')
const loading = ref(false)

/* Hasta tener los datos va el esqueleto: un formulario vacío se llena solo
   unos milisegundos después y se lleva puesto lo que el usuario escribió. */
const ready = ref(false)

const { markSaved, dirty } = useUnsavedChanges({ state: () => form.value, save: persist })

function fill(category) {
    form.value = {
        name: category.name,
        slug: category.slug,
        description: category.description ?? '',
        active: category.active,
    }

    markSaved()
}

/** Guarda y devuelve si salió bien. No navega: de eso se encarga submit(). */
async function persist() {
    errors.value = checkRequired(form.value, ['name'])
    message.value = ''

    if (hasErrors(errors.value)) {
        ui.toast(REQUIRED_TOAST, '', 'danger')

        return false
    }

    loading.value = true

    const payload = { ...form.value }

    /* Sin slug propio, la API lo genera a partir del nombre. */
    if (! payload.slug) {
        delete payload.slug
    }

    try {
        const response = isEdit.value
            ? await api.put(`/categories/${id.value}`, payload)
            : await api.post('/categories', payload)

        /* El listado se entera sin volver a pedir la lista. */
        store.upsert(response.category)

        markSaved()

        ui.toast(isEdit.value ? 'Categoría actualizada' : 'Categoría creada', form.value.name)

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
        await router.push({ name: 'categories' })
    }
}

onMounted(async () => {
    if (! isEdit.value) {
        ready.value = true

        return
    }

    /* Lo que ya trajo el listado: el formulario abre lleno. */
    const known = store.find(id.value)

    if (known) {
        fill(known)
        ready.value = true
    }

    const payload = await api.get(`/categories/${id.value}`)

    /* Si el usuario ya escribió sobre lo precargado, no se le pisa. */
    if (! dirty.value) {
        fill(payload.category)
    }

    store.upsert(payload.category)

    ready.value = true
})
</script>

<template>
    <div class="page-header">
        <div class="page-title">
            <h1>{{ isEdit ? 'Editar categoría' : 'Nueva categoría' }}</h1>
            <p>Agrupá tus productos para que sea más fácil encontrarlos</p>
        </div>

        <div class="page-actions">
            <RouterLink class="btn btn-outline" :to="{ name: 'categories' }">Volver</RouterLink>
        </div>
    </div>

    <div v-if="message" class="alert alert-danger">
        <AppIcon name="alert" />
        <div class="alert-body">
            <strong>No pudimos guardar</strong>
            <span>{{ message }}</span>
        </div>
    </div>

    <section v-if="! ready" class="card">
        <header class="card-header">
            <div class="card-title">
                <h2>Datos de la categoría</h2>
            </div>
        </header>

        <div class="card-body">
            <SkeletonForm :fields="3" />
        </div>
    </section>

    <form v-else class="card" novalidate @submit.prevent="submit">
        <header class="card-header">
            <div class="card-title">
                <h2>Datos de la categoría</h2>
            </div>
        </header>

        <div class="card-body">
            <div class="form">
                <FormField label="Nombre" field-id="category-name" :error="errors.name?.[0]">
                    <input
                        id="category-name"
                        maxlength="255"
                        v-model="form.name"
                        class="input"
                        :class="{ 'has-error': errors.name }"
                        type="text"
                        placeholder="Perfumes"
                    >
                </FormField>

                <FormField
                    label="Slug"
                    field-id="category-slug"
                    hint="Si lo dejás vacío se genera con el nombre."
                    :error="errors.slug?.[0]"
                >
                    <input
                        id="category-slug"
                        maxlength="255"
                        v-model="form.slug"
                        class="input"
                        :class="{ 'has-error': errors.slug }"
                        type="text"
                        placeholder="perfumes"
                    >
                </FormField>

                <FormField
                    label="Descripción"
                    field-id="category-description"
                    :counter="form.description"
                    :max="2000"
                    :error="errors.description?.[0]"
                >
                    <textarea
                        id="category-description"
                        maxlength="2000"
                        v-model="form.description"
                        class="textarea"
                        placeholder="Qué incluye esta categoría"
                    />
                </FormField>

                <label class="check">
                    <input v-model="form.active" type="checkbox">
                    <span>Mostrar esta categoría en el catálogo</span>
                </label>
            </div>
        </div>

        <footer class="card-footer">
            <RouterLink class="btn btn-outline" :to="{ name: 'categories' }">Cancelar</RouterLink>

            <button class="btn btn-primary" type="submit" :disabled="loading">
                <span v-if="loading" class="btn-loader" />
                <span>{{ loading ? 'Guardando…' : 'Guardar categoría' }}</span>
            </button>
        </footer>
    </form>
</template>
