<script setup>
import { computed, onMounted, ref } from 'vue'

import AppIcon from '@/components/AppIcon.vue'
import FormField from '@/components/FormField.vue'
import PasswordInput from '@/components/PasswordInput.vue'
import PasswordStrength from '@/components/PasswordStrength.vue'
import { useUnsavedChanges } from '@/composables/useUnsavedChanges'
import { ApiError, api } from '@/services/api'
import { REQUIRED_TOAST, checkRequired, hasErrors } from '@/services/validation'
import { useAuthStore } from '@/stores/auth'
import { useUiStore } from '@/stores/ui'

const auth = useAuthStore()
const ui = useUiStore()

const EMPTY_PASSWORD = { password: '', password_confirmation: '' }

const profile = ref({ name: '', username: '', email: '' })
const password = ref({ ...EMPTY_PASSWORD })

const errors = ref({})
const message = ref('')
const loading = ref(false)

/* La contraseña es opcional: con los dos campos vacíos se guarda sólo el
   perfil. Basta con que haya uno escrito para que se pidan los dos. */
const changingPassword = computed(() => Object.values(password.value).some(value => value !== ''))

/* Un solo botón guarda las dos cosas, así que el aviso de cambios sin guardar
   mira los dos formularios juntos. */
const changes = useUnsavedChanges({
    state: () => ({ ...profile.value, ...password.value }),
    save,
})

/** @returns {Promise<boolean>} Si salió bien: lo mira el aviso de cambios sin guardar. */
async function save() {
    message.value = ''
    errors.value = checkRequired(profile.value, ['name', 'username', 'email'])

    if (changingPassword.value) {
        errors.value = {
            ...errors.value,
            ...checkRequired(password.value, ['password', 'password_confirmation']),
        }
    }

    if (hasErrors(errors.value)) {
        ui.toast(REQUIRED_TOAST, '', 'danger')

        return false
    }

    loading.value = true

    try {
        auth.user = (await api.put('/profile', profile.value)).user

        if (changingPassword.value) {
            await api.put('/password', password.value)

            password.value = { ...EMPTY_PASSWORD }

            ui.toast('Cambios guardados', 'Se cerraron las otras sesiones.')
        } else {
            ui.toast('Cambios guardados')
        }

        changes.markSaved()

        return true
    } catch (error) {
        if (error instanceof ApiError) {
            errors.value = error.errors
            message.value = error.isValidation ? '' : error.message
        }

        return false
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    profile.value = {
        name: auth.user?.name ?? '',
        username: auth.user?.username ?? '',
        email: auth.user?.email ?? '',
    }

    changes.markSaved()
})
</script>

<template>
    <div class="page-header">
        <div class="page-title">
            <h1>Mi cuenta</h1>
            <p>Tus datos de acceso al panel</p>
        </div>
    </div>

    <form novalidate @submit.prevent="save">
        <section id="perfil" class="card">
            <header class="card-header">
                <div class="card-title">
                    <h2>Perfil</h2>
                </div>
            </header>

            <div class="card-body">
                <div v-if="message" class="alert alert-danger">
                    <AppIcon name="alert" />
                    <div class="alert-body">
                        <strong>No pudimos guardar</strong>
                        <span>{{ message }}</span>
                    </div>
                </div>

                <div class="form">
                    <div class="form-row">
                        <FormField label="Nombre" field-id="profile-name" :error="errors.name?.[0]">
                            <input
                                id="profile-name"
                                maxlength="255"
                                v-model="profile.name"
                                class="input"
                                :class="{ 'has-error': errors.name }"
                                type="text"
                                autocomplete="name"
                            >
                        </FormField>

                        <FormField
                            label="Usuario"
                            hint="Con esto entrás al panel. Entre 4 y 15 letras o números, sin espacios"
                            field-id="profile-username"
                            :error="errors.username?.[0]"
                        >
                            <input
                                id="profile-username"
                                maxlength="15"
                                v-model="profile.username"
                                class="input"
                                :class="{ 'has-error': errors.username }"
                                type="text"
                                autocomplete="username"
                            >
                        </FormField>

                        <FormField label="Correo electrónico" field-id="profile-email" :error="errors.email?.[0]">
                            <input
                                id="profile-email"
                                maxlength="255"
                                v-model="profile.email"
                                class="input"
                                :class="{ 'has-error': errors.email }"
                                type="email"
                                autocomplete="email"
                            >
                        </FormField>
                    </div>
                </div>
            </div>
        </section>

        <section id="seguridad" class="card">
            <header class="card-header">
                <div class="card-title">
                    <h2>Seguridad</h2>
                    <p>Completala sólo si querés cambiarla; al hacerlo se cierran las otras sesiones</p>
                </div>
            </header>

            <div class="card-body">
                <div class="form">
                    <div class="form-row">
                        <FormField label="Nueva contraseña" field-id="new-password" :error="errors.password?.[0]">
                            <PasswordInput id="new-password" v-model="password.password" autocomplete="new-password" />
                            <PasswordStrength :value="password.password" />
                        </FormField>

                        <FormField
                            label="Repetir contraseña"
                            field-id="new-password-confirm"
                            :error="errors.password_confirmation?.[0]"
                        >
                            <PasswordInput
                                id="new-password-confirm"
                                v-model="password.password_confirmation"
                                autocomplete="new-password"
                            />
                        </FormField>
                    </div>
                </div>
            </div>

            <footer class="card-footer">
                <button class="btn btn-primary" type="submit" :disabled="loading">
                    <span v-if="loading" class="btn-loader" />
                    <span>{{ loading ? 'Guardando…' : 'Guardar cambios' }}</span>
                </button>
            </footer>
        </section>
    </form>
</template>
