<script setup>
import { ref } from 'vue'

import AppIcon from '@/components/AppIcon.vue'

defineProps({
    id: { type: String, required: true },
    autocomplete: { type: String, default: 'current-password' },
    placeholder: { type: String, default: '••••••••' },
})

const model = defineModel({ type: String, default: '' })

const visible = ref(false)
</script>

<template>
    <div class="input-group">
        <input
            :id="id"
            v-model="model"
            class="input"
            :type="visible ? 'text' : 'password'"
            :placeholder="placeholder"
            :autocomplete="autocomplete"
        >
        <button
            class="input-group-action"
            type="button"
            :aria-label="visible ? 'Ocultar contraseña' : 'Mostrar contraseña'"
            @click="visible = ! visible"
        >
            <AppIcon :name="visible ? 'eyeOff' : 'eye'" />
        </button>
    </div>
</template>
