<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'

import AppIcon from '@/components/AppIcon.vue'
import FormField from '@/components/FormField.vue'
import PasswordInput from '@/components/PasswordInput.vue'
import PasswordStrength from '@/components/PasswordStrength.vue'
import { ApiError, api } from '@/services/api'
import { REQUIRED_TOAST, checkRequired, hasErrors } from '@/services/validation'
import { useAuthStore } from '@/stores/auth'
import { useUiStore } from '@/stores/ui'

const auth = useAuthStore()
const ui = useUiStore()
const router = useRouter()

const profile = ref({ name: '', email: '' })
const profileErrors = ref({})
const profileLoading = ref(false)

const password = ref({ current_password: '', password: '', password_confirmation: '' })
const passwordErrors = ref({})
const passwordLoading = ref(false)
const passwordMessage = ref('')

async function saveProfile() {
    profileErrors.value = checkRequired(profile.value, ['name', 'email'])

    if (hasErrors(profileErrors.value)) {
        ui.toast(REQUIRED_TOAST, '', 'danger')

        return
    }

    profileLoading.value = true

    try {
        const response = await api.put('/profile', profile.value)

        auth.user = response.user

        ui.toast('Perfil actualizado')
    } catch (error) {
        if (error instanceof ApiError) {
            profileErrors.value = error.errors
        }
    } finally {
        profileLoading.value = false
    }
}

async function savePassword() {
    passwordErrors.value = checkRequired(password.value, ['current_password', 'password', 'password_confirmation'])
    passwordMessage.value = ''

    if (hasErrors(passwordErrors.value)) {
        ui.toast(REQUIRED_TOAST, '', 'danger')

        return
    }

    passwordLoading.value = true

    try {
        await api.put('/password', password.value)

        password.value = { current_password: '', password: '', password_confirmation: '' }

        ui.toast('Contraseña actualizada', 'Se cerraron las otras sesiones.')
    } catch (error) {
        if (error instanceof ApiError) {
            passwordErrors.value = error.errors
            passwordMessage.value = error.isValidation ? '' : error.message
        }
    } finally {
        passwordLoading.value = false
    }
}

async function logout() {
    await auth.logout()
    await router.push({ name: 'login' })
}

onMounted(() => {
    profile.value = {
        name: auth.user?.name ?? '',
        email: auth.user?.email ?? '',
    }
})
</script>

<template>
    <div class="page-header">
        <div class="page-title">
            <h1>Mi cuenta</h1>
            <p>Tus datos de acceso al panel</p>
        </div>
    </div>

    <form id="perfil" class="card" novalidate @submit.prevent="saveProfile">
        <header class="card-header">
            <div class="card-title">
                <h2>Perfil</h2>
            </div>
        </header>

        <div class="card-body">
            <div class="form">
                <FormField label="Nombre" field-id="profile-name" :error="profileErrors.name?.[0]">
                    <input
                        id="profile-name"
                        maxlength="255"
                        v-model="profile.name"
                        class="input"
                        :class="{ 'has-error': profileErrors.name }"
                        type="text"
                        autocomplete="name"
                    >
                </FormField>

                <FormField label="Correo electrónico" field-id="profile-email" :error="profileErrors.email?.[0]">
                    <input
                        id="profile-email"
                        maxlength="255"
                        v-model="profile.email"
                        class="input"
                        :class="{ 'has-error': profileErrors.email }"
                        type="email"
                        autocomplete="email"
                    >
                </FormField>
            </div>
        </div>

        <footer class="card-footer">
            <button class="btn btn-primary" type="submit" :disabled="profileLoading">
                <span v-if="profileLoading" class="btn-loader" />
                <span>{{ profileLoading ? 'Guardando…' : 'Guardar perfil' }}</span>
            </button>
        </footer>
    </form>

    <form id="seguridad" class="card" novalidate @submit.prevent="savePassword">
        <header class="card-header">
            <div class="card-title">
                <h2>Seguridad</h2>
                <p>Al cambiar la contraseña se cierran las otras sesiones</p>
            </div>
        </header>

        <div class="card-body">
            <div v-if="passwordMessage" class="alert alert-danger">
                <AppIcon name="alert" />
                <div class="alert-body">
                    <strong>No pudimos cambiarla</strong>
                    <span>{{ passwordMessage }}</span>
                </div>
            </div>

            <div class="form">
                <FormField
                    label="Contraseña actual"
                    field-id="current-password"
                    :error="passwordErrors.current_password?.[0]"
                >
                    <PasswordInput id="current-password" v-model="password.current_password" />
                </FormField>

                <FormField label="Nueva contraseña" field-id="new-password" :error="passwordErrors.password?.[0]">
                    <PasswordInput id="new-password" v-model="password.password" autocomplete="new-password" />
                    <PasswordStrength :value="password.password" />
                </FormField>

                <FormField label="Repetir contraseña" field-id="new-password-confirm">
                    <PasswordInput
                        id="new-password-confirm"
                        v-model="password.password_confirmation"
                        autocomplete="new-password"
                    />
                </FormField>
            </div>
        </div>

        <footer class="card-footer">
            <button class="btn btn-primary" type="submit" :disabled="passwordLoading">
                <span v-if="passwordLoading" class="btn-loader" />
                <span>{{ passwordLoading ? 'Guardando…' : 'Cambiar contraseña' }}</span>
            </button>
        </footer>
    </form>

    <section class="card is-danger">
        <header class="card-header">
            <div class="card-title">
                <h2>Cerrar sesión</h2>
                <p>Salís de este dispositivo</p>
            </div>
        </header>

        <div class="card-footer">
            <button class="btn btn-outline" type="button" @click="logout">
                <AppIcon name="logout" />
                Cerrar sesión
            </button>
        </div>
    </section>
</template>
