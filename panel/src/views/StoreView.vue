<script setup>
import { computed, onMounted, ref } from 'vue'

import AppIcon from '@/components/AppIcon.vue'
import FormField from '@/components/FormField.vue'
import MediaPicker from '@/components/MediaPicker.vue'
import { ApiError, api } from '@/services/api'
import { REQUIRED_TOAST, checkRequired, hasErrors } from '@/services/validation'
import { useAuthStore } from '@/stores/auth'
import { useUiStore } from '@/stores/ui'

const auth = useAuthStore()
const ui = useUiStore()

const exists = computed(() => Boolean(auth.store))

const form = ref({
    name: '',
    slug: '',
    industry: '',
    description: '',
    whatsapp: '',
    phone: '',
    email: '',
    facebook: '',
    instagram: '',
    tiktok: '',
    website: '',
    address: '',
    map_url: '',
    city: '',
    country: '',
    currency: '',
    schedules: [],
    active: true,
})

const errors = ref({})
const message = ref('')
const loading = ref(false)

function addSchedule() {
    form.value.schedules.push({ days: '', hours: '' })
}

function fill(store) {
    form.value = {
        name: store.name,
        slug: store.slug,
        industry: store.industry ?? '',
        description: store.description ?? '',
        whatsapp: store.whatsapp ?? '',
        phone: store.phone ?? '',
        email: store.email ?? '',
        facebook: store.facebook ?? '',
        instagram: store.instagram ?? '',
        tiktok: store.tiktok ?? '',
        website: store.website ?? '',
        address: store.address ?? '',
        map_url: store.map_url ?? '',
        city: store.city ?? '',
        country: store.country ?? '',
        currency: store.currency ?? '',
        schedules: store.schedules ?? [],
        active: store.active,
    }
}

function payload() {
    const data = { ...form.value }

    data.schedules = form.value.schedules.filter(item => item.days && item.hours)

    if (! data.schedules.length) {
        data.schedules = null
    }

    /* La moneda se guarda tal como la escribió el usuario: "Gs." no es "GS.". */
    data.currency = data.currency ? data.currency.trim() : null

    return data
}

async function submit() {
    errors.value = checkRequired(form.value, ['name'])
    message.value = ''

    if (hasErrors(errors.value)) {
        ui.toast(REQUIRED_TOAST, '', 'danger')

        return
    }

    loading.value = true

    try {
        const response = exists.value
            ? await api.put('/store', payload())
            : await api.post('/store', payload())

        auth.store = response.store
        fill(response.store)

        ui.toast('Tienda guardada', response.store.name)
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

/* La portada se administra en SEO: acá solo queda el logo. */
const picking = ref(false)

async function uploadImage(event, field) {
    const file = event.target.files?.[0]

    if (! file) {
        return
    }

    const data = new FormData()

    data.append('image', file)

    const response = await api.upload(`/store/${field}`, data)

    auth.store = response.store

    ui.toast('Logo actualizado')
}

/** Elegir el logo de lo que ya está en la biblioteca. */
async function pickImage(media) {
    const response = await api.put('/store/logo', { media_id: media.id })

    auth.store = response.store

    ui.toast('Logo actualizado')
}

async function removeImage(field) {
    const response = await api.delete(`/store/${field}`)

    auth.store = response.store
}

onMounted(() => {
    if (auth.store) {
        fill(auth.store)
    }
})
</script>

<template>
    <div class="page-header">
        <div class="page-title">
            <h1>Mi tienda</h1>
            <p>Los datos que ve tu cliente en el catálogo</p>
        </div>

        <div class="page-actions">
            <a
                v-if="exists"
                class="btn btn-outline"
                :href="auth.store.public_url"
                target="_blank"
                rel="noopener"
            >
                <AppIcon name="external" />
                Ver catálogo
            </a>
        </div>
    </div>

    <div v-if="message" class="alert alert-danger">
        <AppIcon name="alert" />
        <div class="alert-body">
            <strong>No pudimos guardar</strong>
            <span>{{ message }}</span>
        </div>
    </div>

    <div v-if="! exists" class="alert alert-info">
        <AppIcon name="info" />
        <div class="alert-body">
            <strong>Creá tu tienda</strong>
            <span>Con el nombre alcanza para empezar; el resto lo completás cuando quieras.</span>
        </div>
    </div>

    <form novalidate @submit.prevent="submit">
        <section id="informacion" class="card">
            <header class="card-header">
                <div class="card-title">
                    <h2>Información</h2>
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
                                placeholder="Mi Tienda"
                            >
                        </FormField>

                        <FormField
                            label="Slug"
                            field-id="store-slug"
                            hint="Es la dirección de tu catálogo."
                            :error="errors.slug?.[0]"
                        >
                            <input
                                id="store-slug"
                                maxlength="255"
                                v-model="form.slug"
                                class="input"
                                :class="{ 'has-error': errors.slug }"
                                type="text"
                                placeholder="mi-tienda"
                            >
                        </FormField>
                    </div>

                    <div class="form-row">
                        <FormField label="Rubro" field-id="store-industry" :error="errors.industry?.[0]">
                            <input id="store-industry" maxlength="255" v-model="form.industry" class="input" type="text" placeholder="Perfumería">
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
                        <textarea
                            id="store-description"
                            maxlength="2000"
                            v-model="form.description"
                            class="textarea"
                            placeholder="Contá qué vendés"
                        />
                    </FormField>

                    <label class="check">
                        <input v-model="form.active" type="checkbox">
                        <span>Catálogo publicado</span>
                    </label>
                </div>
            </div>
        </section>

        <section v-if="exists" class="card">
            <header class="card-header">
                <div class="card-title">
                    <h2>Logo</h2>
                </div>
            </header>

            <div class="card-body">
                <div class="form">
                    <div class="form-row">
                        <FormField label="Logo" field-id="store-logo">
                            <img v-if="auth.store.logo_url" class="thumb" :src="auth.store.logo_url" alt="Logo">
                            <input
                                id="store-logo"
                                class="input"
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                @change="uploadImage($event, 'logo')"
                            >
                            <div class="table-actions">
                                <button class="btn btn-outline btn-sm" type="button" @click="picking = true">
                                    <AppIcon name="image" />
                                    Elegir de la biblioteca
                                </button>

                                <button
                                    v-if="auth.store.logo_url"
                                    class="btn btn-ghost btn-sm"
                                    type="button"
                                    @click="removeImage('logo')"
                                >
                                    Quitar logo
                                </button>
                            </div>
                        </FormField>

                    </div>
                </div>
            </div>
        </section>

        <section class="card">
            <header class="card-header">
                <div class="card-title">
                    <h2>Contacto</h2>
                </div>
            </header>

            <div class="card-body">
                <div class="form">
                    <div class="form-row">
                        <FormField
                            label="WhatsApp"
                            field-id="store-whatsapp"
                            hint="Con código de país, sin signos."
                            :error="errors.whatsapp?.[0]"
                        >
                            <input id="store-whatsapp" maxlength="30" v-model="form.whatsapp" class="input" type="text" placeholder="595981234567">
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
                        <FormField label="Facebook" field-id="store-facebook" :error="errors.facebook?.[0]">
                            <input id="store-facebook" maxlength="255" v-model="form.facebook" class="input" type="text">
                        </FormField>

                        <FormField label="Instagram" field-id="store-instagram" :error="errors.instagram?.[0]">
                            <input id="store-instagram" maxlength="255" v-model="form.instagram" class="input" type="text">
                        </FormField>

                        <FormField label="TikTok" field-id="store-tiktok" :error="errors.tiktok?.[0]">
                            <input id="store-tiktok" maxlength="255" v-model="form.tiktok" class="input" type="text">
                        </FormField>
                    </div>

                    <div class="form-row">
                        <FormField label="Sitio web" field-id="store-website" :error="errors.website?.[0]">
                            <input id="store-website" maxlength="255" v-model="form.website" class="input" type="text">
                        </FormField>

                        <FormField label="Dirección" field-id="store-address" :error="errors.address?.[0]">
                            <input id="store-address" maxlength="255" v-model="form.address" class="input" type="text">
                        </FormField>
                    </div>

                    <div class="form-row">
                        <FormField label="Enlace del mapa" field-id="store-map" :error="errors.map_url?.[0]">
                            <input id="store-map" maxlength="500" v-model="form.map_url" class="input" type="text">
                        </FormField>

                        <FormField label="Ciudad" field-id="store-city" :error="errors.city?.[0]">
                            <input id="store-city" maxlength="255" v-model="form.city" class="input" type="text">
                        </FormField>

                        <FormField label="País" field-id="store-country" :error="errors.country?.[0]">
                            <input id="store-country" maxlength="255" v-model="form.country" class="input" type="text">
                        </FormField>
                    </div>
                </div>
            </div>
        </section>

        <section class="card">
            <header class="card-header">
                <div class="card-title">
                    <h2>Horarios de atención</h2>
                </div>
                <button class="btn btn-outline btn-sm" type="button" @click="addSchedule">Agregar horario</button>
            </header>

            <div class="card-body">
                <div v-if="! form.schedules.length" class="empty">
                    <p>Sin horarios cargados.</p>
                </div>

                <div v-else class="form">
                    <div v-for="(schedule, index) in form.schedules" :key="index" class="form-row">
                        <FormField label="Días" :field-id="`schedule-days-${index}`">
                            <input
                                :id="`schedule-days-${index}`"
                                maxlength="100"
                                v-model="schedule.days"
                                class="input"
                                type="text"
                                placeholder="Lunes a viernes"
                            >
                        </FormField>

                        <FormField label="Horas" :field-id="`schedule-hours-${index}`">
                            <input
                                :id="`schedule-hours-${index}`"
                                maxlength="100"
                                v-model="schedule.hours"
                                class="input"
                                type="text"
                                placeholder="08:00 a 18:00"
                            >
                        </FormField>

                        <button
                            class="btn btn-ghost btn-icon"
                            type="button"
                            aria-label="Quitar"
                            @click="form.schedules.splice(index, 1)"
                        >
                            <AppIcon name="trash" />
                        </button>
                    </div>
                </div>
            </div>

            <footer class="card-footer">
                <button class="btn btn-primary" type="submit" :disabled="loading">
                    <span v-if="loading" class="btn-loader" />
                    <span>{{ loading ? 'Guardando…' : 'Guardar tienda' }}</span>
                </button>
            </footer>
        </section>
    </form>

    <MediaPicker
        :open="picking"
        title="Elegir el logo"
        @close="picking = false"
        @select="pickImage"
    />
</template>
