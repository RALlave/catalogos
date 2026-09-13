<script setup>
import { computed, onMounted, ref } from 'vue'

import AppIcon from '@/components/AppIcon.vue'
import { useUnsavedChanges } from '@/composables/useUnsavedChanges'
import { ApiError, api } from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import { THEMES, useUiStore } from '@/stores/ui'

const auth = useAuthStore()
const ui = useUiStore()

const palettes = ref([])
const options = ref([])

const selected = ref({ palette: '', radius: '', nav: '', banner: '' })
const message = ref('')
const loading = ref(false)

/* Las opciones de forma salen de la API, así que las pestañas también: sumar
   una a config/themes.php no se toca acá. */
const tabs = computed(() => [
    { key: 'paleta', label: 'Paleta de colores' },
    ...options.value.map(option => ({ key: option.key, label: option.name })),
    { key: 'panel', label: 'Tema del panel' },
])

/* Arranca en la primera, que es fija: el resto de las pestañas recién existe
   cuando responde la API. */
const tab = ref('paleta')

/* Vista previa del catálogo tal como está guardado hoy. */
const previewUrl = computed(() => auth.store?.public_url ?? null)

const { markSaved } = useUnsavedChanges({ state: () => selected.value, save })

/** @returns {Promise<boolean>} Si salió bien: lo mira el aviso de cambios sin guardar. */
async function save() {
    loading.value = true
    message.value = ''

    try {
        const response = await api.put('/store', { ...selected.value })

        auth.store = response.store

        markSaved()

        ui.toast('Apariencia guardada', 'Tu catálogo ya se ve con los colores nuevos.')

        return true
    } catch (error) {
        message.value = error instanceof ApiError
            ? error.message
            : 'No pudimos conectar con el servidor.'

        return false
    } finally {
        loading.value = false
    }
}

onMounted(async () => {
    const payload = await api.get('/themes')

    palettes.value = payload.palettes
    options.value = payload.options

    selected.value = {
        palette: auth.store?.palette ?? payload.default.palette,
        radius: auth.store?.radius ?? payload.default.radius,
        nav: auth.store?.nav ?? payload.default.nav,
        banner: auth.store?.banner ?? payload.default.banner,
    }

    markSaved()
})
</script>

<template>
    <div class="page-header">
        <div class="page-title">
            <h1>Configuración</h1>
            <p>Elegí cómo se ve tu catálogo</p>
        </div>

        <div class="page-actions">
            <a v-if="previewUrl" class="btn btn-outline" :href="previewUrl" target="_blank" rel="noopener">
                <AppIcon name="external" />
                Ver mi catálogo
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

    <div v-if="! auth.store" class="alert alert-warning">
        <AppIcon name="info" />
        <div class="alert-body">
            <strong>Primero creá tu tienda</strong>
            <span>La apariencia se guarda sobre la tienda.</span>
        </div>
    </div>

    <nav class="tabs">
        <button
            v-for="item in tabs"
            :key="item.key"
            class="tab"
            :class="{ 'is-active': tab === item.key }"
            type="button"
            @click="tab = item.key"
        >
            <span>{{ item.label }}</span>
        </button>
    </nav>

    <section v-show="tab === 'paleta'" class="card">
        <header class="card-header">
            <div class="card-title">
                <h2>Paleta de colores</h2>
                <p>Los colores con los que se pinta tu catálogo</p>
            </div>
        </header>

        <div class="card-body">
            <div class="option-grid">
                <label
                    v-for="palette in palettes"
                    :key="palette.key"
                    class="option"
                    :class="{ 'is-selected': selected.palette === palette.key }"
                >
                    <input v-model="selected.palette" type="radio" :value="palette.key">
                    <span class="option-body">
                        <strong>{{ palette.name }}</strong>
                        <span class="palette-swatches">
                            <span
                                v-for="color in palette.swatches"
                                :key="color"
                                class="palette-dot"
                                :style="{ backgroundColor: color }"
                            />
                        </span>
                    </span>
                </label>
            </div>
        </div>
    </section>

    <section
        v-for="option in options"
        v-show="tab === option.key"
        :key="option.key"
        class="card"
    >
        <header class="card-header">
            <div class="card-title">
                <h2>{{ option.name }}</h2>
            </div>
        </header>

        <div class="card-body">
            <div class="option-grid">
                <label
                    v-for="value in option.values"
                    :key="value.key"
                    class="option"
                    :class="{ 'is-selected': selected[option.key] === value.key }"
                >
                    <input v-model="selected[option.key]" type="radio" :value="value.key">
                    <span class="option-body">
                        <strong>{{ value.name }}</strong>
                    </span>
                </label>
            </div>
        </div>
    </section>

    <section v-show="tab === 'panel'" class="card">
        <header class="card-header">
            <div class="card-title">
                <h2>Tema del panel</h2>
                <p>Solo cambia cómo ves vos este panel</p>
            </div>
        </header>

        <div class="card-body">
            <div class="option-grid">
                <label
                    v-for="item in THEMES"
                    :key="item.key"
                    class="option"
                    :class="{ 'is-selected': ui.theme === item.key }"
                >
                    <input type="radio" :value="item.key" :checked="ui.theme === item.key" @change="ui.applyTheme(item.key)">
                    <span class="option-body">
                        <strong>{{ item.name }}</strong>
                        <span class="theme-swatch" :class="`theme-${item.key}`">
                            <span />
                            <span />
                        </span>
                    </span>
                </label>
            </div>
        </div>
    </section>

    <div class="page-actions">
        <button class="btn btn-primary" type="button" :disabled="loading || ! auth.store" @click="save">
            <span v-if="loading" class="btn-loader" />
            <span>{{ loading ? 'Guardando…' : 'Guardar apariencia' }}</span>
        </button>
    </div>
</template>
