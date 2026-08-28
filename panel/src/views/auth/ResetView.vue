<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import AppIcon from '@/components/AppIcon.vue'
import FormField from '@/components/FormField.vue'
import PasswordInput from '@/components/PasswordInput.vue'
import PasswordStrength from '@/components/PasswordStrength.vue'
import AuthLayout from '@/layouts/AuthLayout.vue'
import { ApiError, api } from '@/services/api'
import { REQUIRED_TOAST, checkRequired, hasErrors } from '@/services/validation'
import { useUiStore } from '@/stores/ui'

const route = useRoute()
const router = useRouter()
const ui = useUiStore()

/* El enlace del correo trae el token y el email en la querystring. */
const form = ref({
    token: String(route.query.token ?? ''),
    email: String(route.query.email ?? ''),
    password: '',
    password_confirmation: '',
})

const errors = ref({})
const message = ref('')
const loading = ref(false)

async function submit() {
    errors.value = checkRequired(form.value, ['email', 'password', 'password_confirmation'])
    message.value = ''

    if (hasErrors(errors.value)) {
        ui.toast(REQUIRED_TOAST, '', 'danger')

        return
    }

    loading.value = true

    try {
        await api.post('/reset-password', form.value)

        ui.toast('Contraseña actualizada', 'Ya podés iniciar sesión.')

        await router.push({ name: 'login' })
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
            <h1>Nueva contraseña</h1>
            <p>Elegí una contraseña para tu cuenta</p>
        </header>

        <div v-if="message" class="alert alert-danger">
            <AppIcon name="alert" />
            <div class="alert-body">
                <strong>No pudimos cambiarla</strong>
                <span>{{ message }}</span>
            </div>
        </div>

        <div v-if="! form.token" class="alert alert-warning">
            <AppIcon name="info" />
            <div class="alert-body">
                <strong>Falta el enlace</strong>
                <span>Abrí esta página desde el correo que te enviamos.</span>
            </div>
        </div>

        <form class="form" novalidate @submit.prevent="submit">
            <FormField label="Correo electrónico" field-id="reset-email" :error="errors.email?.[0]">
                <input
                    id="reset-email"
                    maxlength="255"
                    v-model="form.email"
                    class="input"
                    :class="{ 'has-error': errors.email }"
                    type="email"
                    autocomplete="email"
                >
            </FormField>

            <FormField label="Nueva contraseña" field-id="reset-password" :error="errors.password?.[0]">
                <PasswordInput id="reset-password" v-model="form.password" autocomplete="new-password" />
                <PasswordStrength :value="form.password" />
            </FormField>

            <FormField label="Repetir contraseña" field-id="reset-password-confirm">
                <PasswordInput
                    id="reset-password-confirm"
                    v-model="form.password_confirmation"
                    autocomplete="new-password"
                />
            </FormField>

            <button class="btn btn-primary btn-lg btn-block" type="submit" :disabled="loading">
                <span v-if="loading" class="btn-loader" />
                <span>{{ loading ? 'Guardando…' : 'Guardar contraseña' }}</span>
            </button>
        </form>
    </AuthLayout>
</template>
