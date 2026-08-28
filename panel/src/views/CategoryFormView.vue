<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import AppIcon from '@/components/AppIcon.vue'
import FormField from '@/components/FormField.vue'
import { ApiError, api } from '@/services/api'
import { REQUIRED_TOAST, checkRequired, hasErrors } from '@/services/validation'
import { useUiStore } from '@/stores/ui'

const route = useRoute()
const router = useRouter()
const ui = useUiStore()

const id = computed(() => route.params.id)
const isEdit = computed(() => Boolean(id.value))

const form = ref({ name: '', slug: '', description: '', active: true })
const errors = ref({})
const message = ref('')
const loading = ref(false)

async function submit() {
    errors.value = checkRequired(form.value, ['name'])
    message.value = ''

    if (hasErrors(errors.value)) {
        ui.toast(REQUIRED_TOAST, '', 'danger')

        return
    }

    loading.value = true

    const payload = { ...form.value }

    /* Sin slug propio, la API lo genera a partir del nombre. */
    if (! payload.slug) {
        delete payload.slug
    }

    try {
        if (isEdit.value) {
            await api.put(`/categories/${id.value}`, payload)
        } else {
            await api.post('/categories', payload)
        }

        ui.toast(isEdit.value ? 'Categoría actualizada' : 'Categoría creada', form.value.name)

        await router.push({ name: 'categories' })
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

    const payload = await api.get(`/categories/${id.value}`)

    form.value = {
        name: payload.category.name,
        slug: payload.category.slug,
        description: payload.category.description ?? '',
        active: payload.category.active,
    }
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

    <form class="card" novalidate @submit.prevent="submit">
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
