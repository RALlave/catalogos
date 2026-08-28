<script setup>
import Chart from 'chart.js/auto'
import { onBeforeUnmount, onMounted, ref, watch } from 'vue'

import { useUiStore } from '@/stores/ui'

const props = defineProps({
    /** Recibe los colores del tema y devuelve la configuración de Chart.js. */
    factory: { type: Function, required: true },
    short: { type: Boolean, default: false },
})

const ui = useUiStore()
const canvas = ref(null)

let chart = null

function readColors() {
    const styles = getComputedStyle(document.documentElement)
    const read = name => styles.getPropertyValue(name).trim()

    return {
        primary: read('--color-chart-1'),
        accent: read('--color-chart-2'),
        green: read('--color-chart-3'),
        rose: read('--color-chart-4'),
        grid: read('--color-chart-grid'),
        textSoft: read('--color-text-soft'),
        text: read('--color-text'),
        surface: read('--color-surface'),
        surfaceHover: read('--color-surface-hover'),
        accentSoft: read('--color-accent-soft'),
    }
}

function render() {
    if (! canvas.value) {
        return
    }

    chart?.destroy()

    const colors = readColors()

    Chart.defaults.font.family = '"Segoe UI", system-ui, -apple-system, Arial, sans-serif'
    Chart.defaults.font.size = 12
    Chart.defaults.color = colors.textSoft

    chart = new Chart(canvas.value, props.factory(colors))
}

/* Al cambiar de tema hay que releer las variables de color. */
watch(() => ui.theme, () => render())

onMounted(render)

onBeforeUnmount(() => chart?.destroy())
</script>

<template>
    <div class="chart-box" :class="{ 'is-short': short }">
        <canvas ref="canvas" />
    </div>
</template>
