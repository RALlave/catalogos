<script setup>
import { computed } from 'vue'

const props = defineProps({
    value: { type: String, default: '' },
})

const LEVELS = [
    { className: 'is-weak', label: 'Contraseña débil' },
    { className: 'is-medium', label: 'Contraseña aceptable' },
    { className: 'is-strong', label: 'Contraseña segura' },
]

const EMPTY_LABEL = 'Usá al menos 8 caracteres, con mayúsculas y números'

const score = computed(() => {
    const value = props.value
    let points = 0

    if (value.length >= 8) {
        points++
    }

    if (/[A-Z]/.test(value) && /[a-z]/.test(value)) {
        points++
    }

    if (/\d/.test(value)) {
        points++
    }

    if (/[^A-Za-z0-9]/.test(value)) {
        points++
    }

    return points
})

const level = computed(() => (props.value
    ? LEVELS[Math.min(Math.max(score.value - 1, 0), 2)]
    : null))
</script>

<template>
    <div class="strength" :class="level?.className">
        <div class="strength-bars">
            <span />
            <span />
            <span />
        </div>
        <div class="strength-label">{{ level?.label ?? EMPTY_LABEL }}</div>
    </div>
</template>
