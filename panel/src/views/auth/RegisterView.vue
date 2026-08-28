<script setup>
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'

import AppIcon from '@/components/AppIcon.vue'
import FormField from '@/components/FormField.vue'
import PasswordInput from '@/components/PasswordInput.vue'
import PasswordStrength from '@/components/PasswordStrength.vue'
import AuthLayout from '@/layouts/AuthLayout.vue'
import { ApiError, api } from '@/services/api'
import { REQUIRED_TOAST, checkRequired, hasErrors } from '@/services/validation'
import { useAuthStore } from '@/stores/auth'
import { useUiStore } from '@/stores/ui'

const auth = useAuthStore()
const ui = useUiStore()
const router = useRouter()

const form = ref({
    name: '',
    username: '',
    store: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
})

const errors = ref({})
const message = ref('')
const loading = ref(false)

/* Vista previa del enlace que va a quedar publicado. */
const slugPreview = computed(() => form.value.store
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-|-$/g, ''))

async function submit() {
    errors.value = checkRequired(form.value, ['name', 'username', 'email', 'password', 'password_confirmation'])
    message.value = ''

    if (hasErrors(errors.value)) {
        ui.toast(REQUIRED_TOAST, '', 'danger')

        return
    }

    loading.value = true

    try {
        await auth.register({
            name: form.value.name,
            username: form.value.username,
            email: form.value.email,
            password: form.value.password,
            password_confirmation: form.value.password_confirmation,
        })

        /* La tienda es un segundo paso de la API: se crea con el nombre del formulario. */
        if (form.value.store) {
            await api.post('/store', { name: form.value.store })
            await auth.loadStore()
        }

        await router.push({ name: 'dashboard' })
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
</script>

<template>
    <AuthLayout>
        <header class="auth-head">
            <h1>Creá tu cuenta</h1>
            <p>Publicá tu catálogo en minutos, sin costo</p>
        </header>

        <div v-if="message" class="alert alert-danger">
            <AppIcon name="alert" />
            <div class="alert-body">
                <strong>No pudimos crear la cuenta</strong>
                <span>{{ message }}</span>
            </div>
        </div>

        <form class="form" novalidate @submit.prevent="submit">
            <FormField label="Nombre y apellido" field-id="register-name" :error="errors.name?.[0]">
                <input
                    id="register-name"
                    maxlength="255"
                    v-model="form.name"
                    class="input"
                    :class="{ 'has-error': errors.name }"
                    type="text"
                    placeholder="Rafael Albino"
                    autocomplete="name"
                >
            </FormField>

            <FormField
                label="Usuario"
                hint="Entre 4 y 15 letras o números, sin espacios"
                field-id="register-username"
                :error="errors.username?.[0]"
            >
                <input
                    id="register-username"
                    maxlength="15"
                    v-model="form.username"
                    class="input"
                    :class="{ 'has-error': errors.username }"
                    type="text"
                    placeholder="rafael99"
                    autocomplete="username"
                >
            </FormField>

            <FormField
                label="Nombre de tu tienda"
                field-id="register-store"
                :hint="slugPreview ? `Tu catálogo quedará en /${slugPreview}` : 'Tu catálogo quedará en /nombre-de-tu-tienda'"
            >
                <input
                    id="register-store"
                    maxlength="255"
                    v-model="form.store"
                    class="input"
                    type="text"
                    placeholder="Mi Tienda"
                >
            </FormField>

            <FormField label="Correo electrónico" field-id="register-email" :error="errors.email?.[0]">
                <input
                    id="register-email"
                    maxlength="255"
                    v-model="form.email"
                    class="input"
                    :class="{ 'has-error': errors.email }"
                    type="email"
                    placeholder="tunombre@correo.com"
                    autocomplete="email"
                >
            </FormField>

            <FormField label="Contraseña" field-id="register-password" :error="errors.password?.[0]">
                <PasswordInput
                    id="register-password"
                    v-model="form.password"
                    autocomplete="new-password"
                />
                <PasswordStrength :value="form.password" />
            </FormField>

            <FormField label="Repetir contraseña" field-id="register-password-confirm">
                <PasswordInput
                    id="register-password-confirm"
                    v-model="form.password_confirmation"
                    autocomplete="new-password"
                />
            </FormField>

            <label class="check">
                <input v-model="form.terms" type="checkbox">
                <span>Acepto los términos y la política de privacidad</span>
            </label>

            <button
                class="btn btn-primary btn-lg btn-block"
                type="submit"
                :disabled="loading || ! form.terms"
            >
                <span v-if="loading" class="btn-loader" />
                <span>{{ loading ? 'Creando…' : 'Crear cuenta gratis' }}</span>
            </button>
        </form>

        <div class="auth-divider">o</div>

        <div class="auth-alt">
            <p>
                ¿Ya tenés cuenta?
                <span class="auth-link">
                    <RouterLink :to="{ name: 'login' }">Iniciá sesión</RouterLink>
                </span>
            </p>
        </div>
    </AuthLayout>
</template>
