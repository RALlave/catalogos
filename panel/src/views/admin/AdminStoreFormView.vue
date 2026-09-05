<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import AppIcon from '@/components/AppIcon.vue'
import FormField from '@/components/FormField.vue'
import PasswordInput from '@/components/PasswordInput.vue'
import { ApiError, api } from '@/services/api'
import { REQUIRED_TOAST, checkRequired, hasErrors } from '@/services/validation'
import { useAuthStore } from '@/stores/auth'
import { useUiStore } from '@/stores/ui'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const ui = useUiStore()

const id = computed(() => route.params.id)
const isEdit = computed(() => Boolean(id.value))

/* En el alta se crean juntos el usuario dueño y su tienda. */
const owner = ref({ owner_name: '', owner_username: '', owner_email: '', owner_password: '' })

const form = ref({
    name: '',
    slug: '',
    industry: '',
    description: '',
    whatsapp: '',
    phone: '',
    email: '',
    city: '',
    country: '',
    currency: '',
    active: true,
})

const errors = ref({})
const message = ref('')
const loading = ref(false)

function payload() {
    const data = { ...form.value }

    if (! data.slug) {
        delete data.slug
    }

    /* La moneda se guarda tal como la escribió el usuario: "Gs." no es "GS.". */
    data.currency = data.currency ? data.currency.trim() : null

    return isEdit.value ? data : { ...owner.value, ...data }
}

async function submit() {
    /* En el alta también son obligatorios los datos del dueño. */
    errors.value = isEdit.value
        ? checkRequired(form.value, ['name'])
        : {
            ...checkRequired(form.value, ['name']),
            ...checkRequired(owner.value, ['owner_name', 'owner_username', 'owner_email', 'owner_password']),
        }

    message.value = ''

    if (hasErrors(errors.value)) {
        ui.toast(REQUIRED_TOAST, '', 'danger')

        return
    }

    loading.value = true

    try {
        if (isEdit.value) {
            await api.put(`/admin/stores/${id.value}`, payload())
        } else {
            await api.post('/admin/stores', payload())
        }

        ui.toast(isEdit.value ? 'Tienda actualizada' : 'Tienda creada', form.value.name)

        await router.push({ name: 'admin-stores' })
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

/** Entrar al panel de esta tienda como su dueño. */
async function enterPanel() {
    try {
        await auth.impersonate(id.value)

        ui.toast('Entraste al panel', form.value.name)

        await router.push({ name: 'dashboard' })
    } catch {
        ui.toast('No pudimos entrar al panel', form.value.name, 'danger')
    }
}

onMounted(async () => {
    if (! isEdit.value) {
        return
    }

    const payloadStore = await api.get(`/admin/stores/${id.value}`)
    const store = payloadStore.store

    form.value = {
        name: store.name,
        slug: store.slug,
        industry: store.industry ?? '',
        description: store.description ?? '',
        whatsapp: store.whatsapp ?? '',
        phone: store.phone ?? '',
        email: store.email ?? '',
        city: store.city ?? '',
        country: store.country ?? '',
        currency: store.currency ?? '',
        active: store.active,
    }
})
</script>

<template>
    <div class="page-header">
        <div class="page-title">
            <h1>{{ isEdit ? 'Editar tienda' : 'Nueva tienda' }}</h1>
            <p>{{ isEdit ? 'Datos de la tienda' : 'Se crea la cuenta del dueño junto con su tienda' }}</p>
        </div>

        <div class="page-actions">
            <button v-if="isEdit" class="btn btn-outline" type="button" @click="enterPanel">
                <AppIcon name="enter" />
                Entrar al panel
            </button>

            <RouterLink class="btn btn-outline" :to="{ name: 'admin-stores' }">Volver</RouterLink>
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
        <section v-if="! isEdit" class="card">
            <header class="card-header">
                <div class="card-title">
                    <h2>Dueño de la tienda</h2>
                    <p>Con estos datos entra al panel</p>
                </div>
            </header>

            <div class="card-body">
                <div class="form">
                    <div class="form-row">
                        <FormField label="Nombre" field-id="owner-name" :error="errors.owner_name?.[0]">
                            <input
                                id="owner-name"
                                maxlength="255"
                                v-model="owner.owner_name"
                                class="input"
                                :class="{ 'has-error': errors.owner_name }"
                                type="text"
                                placeholder="Ana Pérez"
                            >
                        </FormField>

                        <FormField label="Correo electrónico" field-id="owner-email" :error="errors.owner_email?.[0]">
                            <input
                                id="owner-email"
                                maxlength="255"
                                v-model="owner.owner_email"
                                class="input"
                                :class="{ 'has-error': errors.owner_email }"
                                type="email"
                                placeholder="ana@tienda.com"
                            >
                        </FormField>
                    </div>

                    <FormField
                        label="Usuario"
                        hint="Entre 4 y 15 letras o números. Puede entrar con esto o con el email."
                        field-id="owner-username"
                        :error="errors.owner_username?.[0]"
                    >
                        <input
                            id="owner-username"
                            maxlength="15"
                            v-model="owner.owner_username"
                            class="input"
                            :class="{ 'has-error': errors.owner_username }"
                            type="text"
                            placeholder="anaperez"
                        >
                    </FormField>

                    <FormField
                        label="Contraseña"
                        field-id="owner-password"
                        hint="Se la vas a tener que pasar al dueño."
                        :error="errors.owner_password?.[0]"
                    >
                        <PasswordInput
                            id="owner-password"
                            v-model="owner.owner_password"
                            autocomplete="new-password"
                        />
                    </FormField>
                </div>
            </div>
        </section>

        <section class="card">
            <header class="card-header">
                <div class="card-title">
                    <h2>Datos de la tienda</h2>
                </div>
            </header>

            <div class="card-body">
                <div class="form">
                    <div class="form-row">
                        <FormField label="Nombre" field-id="store-name" :error="errors.name?.[0]">
                            <input
                                id="store-name"
                                maxlength="255"
                                v-model="form.name"
                                class="input"
                                :class="{ 'has-error': errors.name }"
                                type="text"
                                placeholder="Tienda de Ana"
                            >
                        </FormField>

                        <FormField
                            label="Slug"
                            field-id="store-slug"
                            hint="Si lo dejás vacío se genera con el nombre."
                            :error="errors.slug?.[0]"
                        >
                            <input
                                id="store-slug"
                                maxlength="255"
                                v-model="form.slug"
                                class="input"
                                :class="{ 'has-error': errors.slug }"
                                type="text"
                            >
                        </FormField>
                    </div>

                    <div class="form-row">
                        <FormField label="Rubro" field-id="store-industry" :error="errors.industry?.[0]">
                            <input id="store-industry" maxlength="255" v-model="form.industry" class="input" type="text">
                        </FormField>

                        <FormField
                            label="Moneda"
                            field-id="store-currency"
                            hint="Dos o tres letras, con punto opcional: Gs., PYG, USD…"
                            :error="errors.currency?.[0]"
                        >
                            <input
                                id="store-currency"
                                v-model="form.currency"
                                class="input"
                                :class="{ 'has-error': errors.currency }"
                                type="text"
                                minlength="2"
                                maxlength="4"
                                placeholder="Gs."
                            >
                        </FormField>
                    </div>

                    <FormField
                        label="Descripción"
                        field-id="store-description"
                        :counter="form.description"
                        :max="2000"
                        :error="errors.description?.[0]"
                    >
                        <textarea id="store-description" v-model="form.description" class="textarea" maxlength="2000" />
                    </FormField>

                    <div class="form-row">
                        <FormField label="WhatsApp" field-id="store-whatsapp" :error="errors.whatsapp?.[0]">
                            <input id="store-whatsapp" maxlength="30" v-model="form.whatsapp" class="input" type="text">
                        </FormField>

                        <FormField label="Teléfono" field-id="store-phone" :error="errors.phone?.[0]">
                            <input id="store-phone" maxlength="30" v-model="form.phone" class="input" type="text">
                        </FormField>

                        <FormField label="Email" field-id="store-email" :error="errors.email?.[0]">
                            <input
                                id="store-email"
                                maxlength="255"
                                v-model="form.email"
                                class="input"
                                :class="{ 'has-error': errors.email }"
                                type="email"
                            >
                        </FormField>
                    </div>

                    <div class="form-row">
                        <FormField label="Ciudad" field-id="store-city" :error="errors.city?.[0]">
                            <input id="store-city" maxlength="255" v-model="form.city" class="input" type="text">
                        </FormField>

                        <FormField label="País" field-id="store-country" :error="errors.country?.[0]">
                            <input id="store-country" maxlength="255" v-model="form.country" class="input" type="text">
                        </FormField>
                    </div>

                    <label class="check">
                        <input v-model="form.active" type="checkbox">
                        <span>Catálogo publicado</span>
                    </label>
                </div>
            </div>

            <footer class="card-footer">
                <RouterLink class="btn btn-outline" :to="{ name: 'admin-stores' }">Cancelar</RouterLink>

                <button class="btn btn-primary" type="submit" :disabled="loading">
                    <span v-if="loading" class="btn-loader" />
                    <span>{{ loading ? 'Guardando…' : 'Guardar tienda' }}</span>
                </button>
            </footer>
        </section>
    </form>
</template>
