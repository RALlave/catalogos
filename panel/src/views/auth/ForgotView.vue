<script setup>
import { ref } from 'vue'

import AppIcon from '@/components/AppIcon.vue'
import FormField from '@/components/FormField.vue'
import AuthLayout from '@/layouts/AuthLayout.vue'
import { ApiError, api } from '@/services/api'
import { REQUIRED_TOAST, checkRequired, hasErrors } from '@/services/validation'
import { useUiStore } from '@/stores/ui'

const ui = useUiStore()

const email = ref('')
const errors = ref({})
const message = ref('')
const sent = ref(false)
const loading = ref(false)

async function submit() {
    errors.value = checkRequired({ email: email.value }, ['email'])
    message.value = ''

    if (hasErrors(errors.value)) {
        ui.toast(REQUIRED_TOAST, '', 'danger')

        return
    }

    loading.value = true

    try {
        await api.post('/forgot-password', { email: email.value })

        sent.value = true
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
            <h1>Recuperar contraseña</h1>
            <p>Te mandamos un enlace para crear una nueva</p>
        </header>

        <div v-if="sent" class="alert alert-success">
            <AppIcon name="checkCircle" />
            <div class="alert-body">
                <strong>Revisá tu correo</strong>
                <span>Si el email existe, te llega el enlace en unos minutos.</span>
            </div>
        </div>

        <div v-if="message" class="alert alert-danger">
            <AppIcon name="alert" />
            <div class="alert-body">
                <strong>Algo salió mal</strong>
                <span>{{ message }}</span>
            </div>
        </div>

        <form class="form" novalidate @submit.prevent="submit">
            <FormField label="Correo electrónico" field-id="forgot-email" :error="errors.email?.[0]">
                <input
                    id="forgot-email"
                    maxlength="255"
                    v-model="email"
                    class="input"
                    :class="{ 'has-error': errors.email }"
                    type="email"
                    placeholder="tunombre@correo.com"
                    autocomplete="email"
                >
            </FormField>

            <button class="btn btn-primary btn-lg btn-block" type="submit" :disabled="loading">
                <span v-if="loading" class="btn-loader" />
                <span>{{ loading ? 'Enviando…' : 'Enviar enlace' }}</span>
            </button>
        </form>

        <div class="auth-alt">
            <p>
                <span class="auth-link">
                    <RouterLink :to="{ name: 'login' }">Volver a iniciar sesión</RouterLink>
                </span>
            </p>
        </div>
    </AuthLayout>
</template>
