<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'

const open = ref(false)
const root = ref(null)

function toggle() {
    open.value = ! open.value
}

function close() {
    open.value = false
}

function onDocumentClick(event) {
    if (root.value && ! root.value.contains(event.target)) {
        close()
    }
}

function onKeydown(event) {
    if (event.key === 'Escape') {
        close()
    }
}

onMounted(() => {
    document.addEventListener('click', onDocumentClick)
    document.addEventListener('keydown', onKeydown)
})

onBeforeUnmount(() => {
    document.removeEventListener('click', onDocumentClick)
    document.removeEventListener('keydown', onKeydown)
})

defineExpose({ close })
</script>

<template>
    <div ref="root" class="dropdown" :class="{ 'is-open': open }">
        <slot name="trigger" :toggle="toggle" :open="open" />

        <div class="dropdown-menu" @click="close">
            <slot />
        </div>
    </div>
</template>
