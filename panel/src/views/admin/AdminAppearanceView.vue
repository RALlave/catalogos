<script setup>
import { onMounted, ref } from 'vue'

import { api } from '@/services/api'

const palettes = ref([])
const options = ref([])
const loading = ref(true)

onMounted(async () => {
    try {
        const payload = await api.get('/themes')

        palettes.value = payload.palettes
        options.value = payload.options
    } finally {
        loading.value = false
    }
})
</script>

<template>
    <div class="page-header">
        <div class="page-title">
            <h1>Apariencia</h1>
            <p>Lo que puede elegir cada tienda para su catálogo. Se edita en api/config/themes.php</p>
        </div>
    </div>

    <section class="card">
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

    <section v-for="option in options" :key="option.key" class="card">
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
