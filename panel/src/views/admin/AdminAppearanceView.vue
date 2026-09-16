<!--
    Apariencia del superadmin, en dos grupos de pestañas: arriba los logos
    —la marca del SaaS y la galería que se le ofrece a las tiendas— y abajo lo
    que cada tienda elige para su catálogo.

    Las dos pestañas son estado de la vista y no tocan la URL: se cambian sin
    navegar y al recargar se abre la primera de cada grupo.
-->

<script setup>
import { computed, onMounted, ref } from 'vue'

import AppIcon from '@/components/AppIcon.vue'
import FormField from '@/components/FormField.vue'
import { ApiError, api } from '@/services/api'
import { useConfirmStore } from '@/stores/confirm'
import { usePlatformStore } from '@/stores/platform'
import { useUiStore } from '@/stores/ui'

const ui = useUiStore()
const confirm = useConfirmStore()
const platform = usePlatformStore()

const palettes = ref([])
const options = ref([])
const loading = ref(true)

const LOGO_TABS = [
    { key: 'plataforma', label: 'Logos de la plataforma' },
    { key: 'tiendas', label: 'Logos para tiendas' },
]

/* Las opciones de forma salen de la API, así que las pestañas también: sumar
   una a config/themes.php no se toca acá. */
const themeTabs = computed(() => [
    { key: 'paletas', label: 'Paletas disponibles' },
    ...options.value.map(option => ({ key: option.key, label: option.name })),
])

const logoTab = ref(LOGO_TABS[0].key)

/* Arranca en la primera, que es fija: el resto de las pestañas recién existe
   cuando responde la API. */
const themeTab = ref('paletas')

/* La galería que se le ofrece a las tiendas: es aparte de la marca del SaaS. */
const storeLogos = ref([])
const uploadingLogo = ref(false)

onMounted(async () => {
    try {
        const [themes, logos] = await Promise.all([
            api.get('/themes'),
            api.get('/admin/platform/store-logos'),
            platform.load(),
        ])

        palettes.value = themes.palettes
        options.value = themes.options
        storeLogos.value = logos.data
    } finally {
        loading.value = false
    }
})

/* `variant` es cuál de los tres logos: "auth" el de las pantallas de acceso,
   "panel" el de la barra lateral e "icon" el cuadrado de la app. */
async function uploadLogo(event, variant) {
    const file = event.target.files?.[0]

    if (! file) {
        return
    }

    const data = new FormData()

    data.append('image', file)

    try {
        platform.apply(await api.upload(`/admin/platform/logo/${variant}`, data))

        ui.toast('Logo actualizado')
    } catch (error) {
        ui.toast(
            'No pudimos subir el logo',
            error instanceof ApiError ? (error.first('image') ?? error.message) : '',
            'danger',
        )
    } finally {
        /* Sin esto, volver a elegir el mismo archivo no dispara el change. */
        event.target.value = ''
    }
}

async function removeLogo(variant) {
    platform.apply(await api.delete(`/admin/platform/logo/${variant}`))

    ui.toast('Logo quitado')
}

/* Se suben de a uno: cada archivo es un logo con su nombre. */
async function uploadStoreLogos(event) {
    const files = Array.from(event.target.files ?? [])

    if (! files.length) {
        return
    }

    uploadingLogo.value = true

    try {
        for (const file of files) {
            const data = new FormData()

            data.append('image', file)

            const response = await api.upload('/admin/platform/store-logos', data)

            storeLogos.value = [response.logo, ...storeLogos.value]
        }

        ui.toast(files.length > 1 ? 'Logos subidos' : 'Logo subido')
    } catch (error) {
        ui.toast(
            'No pudimos subir el logo',
            error instanceof ApiError ? (error.first('image') ?? error.message) : '',
            'danger',
        )
    } finally {
        uploadingLogo.value = false
        event.target.value = ''
    }
}

async function renameStoreLogo(logo, name) {
    if (name === logo.name) {
        return
    }

    try {
        const response = await api.put(`/admin/platform/store-logos/${logo.id}`, { name })

        Object.assign(logo, response.logo)

        ui.toast('Nombre guardado', response.logo.name)
    } catch (error) {
        ui.toast(
            'No pudimos guardar el nombre',
            error instanceof ApiError ? (error.first('name') ?? error.message) : '',
            'danger',
        )
    }
}

/**
 * El logo con el que arranca una tienda nueva. Marcar uno desmarca al anterior,
 * así que la respuesta trae la lista entera.
 */
async function markDefaultLogo(logo) {
    const response = await api.patch(`/admin/platform/store-logos/${logo.id}/default`)

    storeLogos.value = response.data

    ui.toast('Logo por defecto', logo.name)
}

/**
 * Borrarlo no toca a las tiendas que ya lo eligieron: cada una se quedó con su
 * propia copia en la biblioteca.
 */
async function removeStoreLogo(logo) {
    const confirmed = await confirm.ask({
        title: `¿Quitar "${logo.name}" de los logos disponibles?`,
        text: 'Las tiendas que ya lo eligieron conservan el suyo.',
        action: 'Quitar',
        danger: true,
    })

    if (! confirmed) {
        return
    }

    await api.delete(`/admin/platform/store-logos/${logo.id}`)

    storeLogos.value = storeLogos.value.filter(item => item.id !== logo.id)

    ui.toast('Logo quitado')
}
</script>

<template>
    <div class="page-header">
        <div class="page-title">
            <h1>Apariencia</h1>
            <p>La marca de la plataforma y lo que puede elegir cada tienda para su catálogo</p>
        </div>
    </div>

    <nav class="tabs">
        <button
            v-for="item in LOGO_TABS"
            :key="item.key"
            class="tab"
            :class="{ 'is-active': logoTab === item.key }"
            type="button"
            @click="logoTab = item.key"
        >
            <span>{{ item.label }}</span>
        </button>
    </nav>

    <section v-show="logoTab === 'plataforma'" class="card">
        <header class="card-header">
            <div class="card-title">
                <h2>Logos de la plataforma</h2>
                <p>La marca del SaaS: la misma en el dominio principal y en el subdominio de cada tienda</p>
            </div>
        </header>

        <div class="card-body">
            <div class="form">
                <div class="form-row">
                    <FormField
                        label="Accesos"
                        field-id="platform-logo-auth"
                        hint="Ingreso, registro y recuperación de contraseña"
                    >
                        <img
                            v-if="platform.logos.auth"
                            class="preview-logo"
                            :src="platform.logos.auth.src"
                            alt="Logo de los accesos"
                        >
                        <input
                            id="platform-logo-auth"
                            class="input"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            @change="uploadLogo($event, 'auth')"
                        >
                        <div class="table-actions">
                            <button
                                v-if="platform.logos.auth"
                                class="btn btn-ghost btn-sm"
                                type="button"
                                @click="removeLogo('auth')"
                            >
                                <AppIcon name="trash" />
                                Quitar logo
                            </button>
                        </div>
                    </FormField>

                    <FormField
                        label="Panel"
                        field-id="platform-logo-panel"
                        hint="Barra lateral, tanto la del superadmin como la del dueño"
                    >
                        <img
                            v-if="platform.logos.panel"
                            class="preview-logo"
                            :src="platform.logos.panel.src"
                            alt="Logo del panel"
                        >
                        <input
                            id="platform-logo-panel"
                            class="input"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            @change="uploadLogo($event, 'panel')"
                        >
                        <div class="table-actions">
                            <button
                                v-if="platform.logos.panel"
                                class="btn btn-ghost btn-sm"
                                type="button"
                                @click="removeLogo('panel')"
                            >
                                <AppIcon name="trash" />
                                Quitar logo
                            </button>
                        </div>
                    </FormField>

                    <FormField
                        label="Ícono de la app"
                        field-id="platform-logo-icon"
                        hint="Favicon e ícono de la app instalada. Para que el navegador ofrezca instalarla, cuadrado y de 512 px o más"
                    >
                        <img
                            v-if="platform.logos.icon"
                            class="preview-logo preview-icon"
                            :src="platform.logos.icon.thumb"
                            alt="Ícono de la plataforma"
                        >
                        <input
                            id="platform-logo-icon"
                            class="input"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            @change="uploadLogo($event, 'icon')"
                        >
                        <div class="table-actions">
                            <button
                                v-if="platform.logos.icon"
                                class="btn btn-ghost btn-sm"
                                type="button"
                                @click="removeLogo('icon')"
                            >
                                <AppIcon name="trash" />
                                Quitar ícono
                            </button>
                        </div>
                    </FormField>
                </div>
            </div>
        </div>
    </section>

    <section v-show="logoTab === 'tiendas'" class="card">
        <header class="card-header">
            <div class="card-title">
                <h2>Logos para tiendas</h2>
                <p>Los que puede elegir cada tienda como logo propio. El marcado por defecto es con el que arranca una tienda nueva</p>
            </div>

            <label class="btn btn-outline btn-sm" :class="{ 'is-loading': uploadingLogo }">
                <AppIcon name="plus" />
                <span class="btn-label">Subir logos</span>
                <input
                    class="visually-hidden"
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    multiple
                    @change="uploadStoreLogos"
                >
            </label>
        </header>

        <div class="card-body">
            <div v-if="! storeLogos.length" class="empty">
                <span class="empty-icon">
                    <AppIcon name="image" />
                </span>
                <p>Todavía no subiste logos para ofrecer.</p>
            </div>

            <div v-else class="media-grid">
                <div v-for="logo in storeLogos" :key="logo.id" class="media-card">
                    <span class="media-card-image logo-card-image">
                        <img :src="logo.thumb_url" :alt="logo.name">
                    </span>

                    <span v-if="logo.is_default" class="logo-card-flag">
                        <AppIcon name="star" />
                        Por defecto
                    </span>

                    <div class="media-card-body">
                        <input
                            class="input"
                            type="text"
                            :value="logo.name"
                            maxlength="100"
                            aria-label="Nombre del logo"
                            @change="renameStoreLogo(logo, $event.target.value)"
                        >

                        <div class="logo-card-meta">
                            <span>{{ logo.width }}×{{ logo.height }}</span>

                            <div class="table-actions">
                                <button
                                    v-if="! logo.is_default"
                                    class="btn btn-ghost btn-icon btn-sm"
                                    type="button"
                                    title="Usar por defecto en las tiendas nuevas"
                                    aria-label="Usar por defecto en las tiendas nuevas"
                                    @click="markDefaultLogo(logo)"
                                >
                                    <AppIcon name="star" />
                                </button>

                                <button
                                    class="btn btn-ghost btn-icon btn-sm"
                                    type="button"
                                    title="Borrar logo"
                                    aria-label="Borrar logo"
                                    @click="removeStoreLogo(logo)"
                                >
                                    <AppIcon name="trash" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="page-header">
        <div class="page-title">
            <h2>Opciones para las tiendas</h2>
            <p>Lo que cada tienda elige para su catálogo. Se edita en api/config/themes.php</p>
        </div>
    </div>

    <nav class="tabs">
        <button
            v-for="item in themeTabs"
            :key="item.key"
            class="tab"
            :class="{ 'is-active': themeTab === item.key }"
            type="button"
            @click="themeTab = item.key"
        >
            <span>{{ item.label }}</span>
        </button>
    </nav>

    <section v-show="themeTab === 'paletas'" class="card">
        <header class="card-header">
            <div class="card-title">
                <h2>Paletas disponibles</h2>
                <p>{{ palettes.length }} en total</p>
            </div>
        </header>

        <div class="card-body">
            <div v-if="loading" class="empty">
                <p>Cargando…</p>
            </div>

            <div v-else class="option-grid">
                <div v-for="palette in palettes" :key="palette.key" class="option">
                    <span class="option-body">
                        <strong>{{ palette.name }}</strong>
                        <span>{{ palette.key }}</span>
                        <span class="palette-swatches">
                            <span
                                v-for="color in palette.swatches"
                                :key="color"
                                class="palette-dot"
                                :style="{ backgroundColor: color }"
                                :title="color"
                            />
                        </span>
                    </span>
                </div>
            </div>
        </div>
    </section>

    <section
        v-for="option in options"
        v-show="themeTab === option.key"
        :key="option.key"
        class="card"
    >
        <header class="card-header">
            <div class="card-title">
                <h2>{{ option.name }}</h2>
                <p>{{ option.key }}</p>
            </div>
        </header>

        <div class="card-body">
            <div class="option-grid">
                <div v-for="value in option.values" :key="value.key" class="option">
                    <span class="option-body">
                        <strong>{{ value.name }}</strong>
                        <span>{{ value.key }}</span>
                    </span>
                </div>
            </div>
        </div>
    </section>
</template>
