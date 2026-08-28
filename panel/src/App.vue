<script setup>
import { onBeforeUnmount, onMounted } from 'vue'

import ToastStack from '@/components/ToastStack.vue'

const TEXT_INPUTS = ['text', 'email', 'password', 'search', 'tel', 'url', 'number', 'date']

function isTextField(el) {
    if (! el) {
        return false
    }

    if (el.tagName === 'TEXTAREA') {
        return true
    }

    return el.tagName === 'INPUT' && TEXT_INPUTS.includes(el.type)
}

/* Regla del proyecto: al entrar en un input se selecciona todo el texto. */
function onFocusIn(event) {
    const el = event.target

    if (! isTextField(el) || el.dataset.selected === '1') {
        return
    }

    el.dataset.selected = '1'
    window.setTimeout(() => el.select(), 0)
}

function onFocusOut(event) {
    if (isTextField(event.target)) {
        delete event.target.dataset.selected
    }
}

onMounted(() => {
    document.addEventListener('focusin', onFocusIn)
    document.addEventListener('focusout', onFocusOut)
})

onBeforeUnmount(() => {
    document.removeEventListener('focusin', onFocusIn)
    document.removeEventListener('focusout', onFocusOut)
})
</script>

<template>
    <RouterView />
    <ToastStack />
</template>
