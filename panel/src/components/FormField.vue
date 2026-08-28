<script setup>
import { computed } from 'vue'

const props = defineProps({
    label: { type: String, required: true },
    fieldId: { type: String, required: true },
    hint: { type: String, default: '' },
    error: { type: String, default: null },
    /* Texto del campo: con `max` muestra el contador al lado del label. */
    counter: { type: String, default: null },
    max: { type: [Number, String], default: null },
})

const used = computed(() => (props.counter ?? '').length)

const isFull = computed(() => props.max && used.value >= Number(props.max))
</script>

<template>
    <div class="field">
        <div class="field-label">
            <label :for="fieldId">{{ label }}</label>

            <span v-if="max" class="field-counter" :class="{ 'is-full': isFull }">
                {{ used }} / {{ max }}
            </span>

            <slot name="action" />
        </div>

        <slot />

        <div v-if="hint && ! error" class="field-hint">
            <p>{{ hint }}</p>
        </div>

        <div v-if="error" class="field-error">
            <p>{{ error }}</p>
        </div>
    </div>
</template>
