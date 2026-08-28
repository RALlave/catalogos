<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import AppIcon from '@/components/AppIcon.vue'
import FormField from '@/components/FormField.vue'
import PasswordInput from '@/components/PasswordInput.vue'
import AuthLayout from '@/layouts/AuthLayout.vue'
import { ApiError } from '@/services/api'
import { REQUIRED_TOAST, checkRequired, hasErrors } from '@/services/validation'
import { useAuthStore } from '@/stores/auth'
import { useUiStore } from '@/stores/ui'

const auth = useAuthStore()
const ui = useUiStore()
const route = useRoute()
const router = useRouter()

const form = ref({ login: '', password: '' })
const errors = ref({})
const message = ref('')
const loading = ref(false)

async function submit() {
    errors.value = checkRequired(form.value, ['login', 'password'])
    message.value = ''

    if (hasErrors(errors.value)) {
        ui.toast(REQUIRED_TOAST, '', 'danger')

        return
    }

    loading.value = true

    try {
        await auth.login(form.value)

        const redirect = route.query.redirect
        const home = auth.isSuperadmin ? { name: 'admin-dashboard' } : { name: 'dashboard' }

        await router.push(redirect ? String(redirect) : home)
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
            <h1>Iniciar sesión</h1>
            <p>Entrá para administrar tu catálogo</p>
        </header>

        <div v-if="message" class="alert alert-danger">
            <AppIcon name="alert" />
            <div class="alert-body">
                <strong>No pudimos entrar</strong>
                <span>{{ message }}</span>
            </div>
        </div>

        <form class="form" novalidate @submit.prevent="submit">
            <FormField label="Email o usuario" field-id="login-email" :error="errors.login?.[0]">
                <input
                    id="login-email"
                    maxlength="255"
                    v-model="form.login"
                    class="input"
                    :class="{ 'has-error': errors.login }"
                    type="text"
                    placeholder="tunombre@correo.com"
                    autocomplete="username"
                >
            </FormField>

            <FormField label="Contraseña" field-id="login-password" :error="errors.password?.[0]">
                <template #action>
                    <span class="auth-link">
                        <RouterLink :to="{ name: 'forgot' }">¿La olvidaste?</RouterLink>
                    </span>
                </template>

                <PasswordInput id="login-password" v-model="form.password" />
            </FormField>

            <button class="btn btn-primary btn-lg btn-block" type="submit" :disabled="loading">
                <span v-if="loading" class="btn-loader" />
                <span>{{ loading ? 'Entrando…' : 'Entrar' }}</span>
            </button>
        </form>

        <div class="auth-divider">o</div>

        <div class="auth-alt">
            <p>
                ¿Todavía no tenés cuenta?
                <span class="auth-link">
                    <RouterLink :to="{ name: 'register' }">Creá la tuya gratis</RouterLink>
                </span>
            </p>
        </div>
    </AuthLayout>
</template>
