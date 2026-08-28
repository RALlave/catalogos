<script setup>
import AppIcon from '@/components/AppIcon.vue'
import { useUiStore } from '@/stores/ui'

const ui = useUiStore()
</script>

<template>
    <div class="toast-stack">
        <div
            v-for="item in ui.toasts"
            :key="item.id"
            class="toast"
            :class="{ 'is-danger': item.type === 'danger' }"
        >
            <AppIcon :name="item.type === 'danger' ? 'alert' : 'checkCircle'" />
            <div class="toast-text">
                <strong>{{ item.title }}</strong>
                <span v-if="item.message">{{ item.message }}</span>
            </div>
        </div>
    </div>
</template>
