<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'

import AppIcon from '@/components/AppIcon.vue'
import { useAuthStore } from '@/stores/auth'

/*
 * Reminder that the superadmin is inside a store panel as its owner. It stays
 * out of the way as a floating round button; the card with the way back opens
 * on click.
 */
const auth = useAuthStore()

const root = ref(null)
const open = ref(false)

function toggle() {
    open.value = ! open.value
}

function close() {
    open.value = false
}

/** Back to the superadmin session, which is waiting on the main domain. */
async function backToAdmin() {
    /* An origin jump, not a navigation: it leaves this page. */
    await auth.stopImpersonating()
}

function onPointerDown(event) {
    if (open.value && root.value && ! root.value.contains(event.target)) {
        close()
    }
}

function onKeydown(event) {
    if (open.value && event.key === 'Escape') {
        close()
    }
}

onMounted(() => {
    document.addEventListener('pointerdown', onPointerDown)
    document.addEventListener('keydown', onKeydown)
})

onBeforeUnmount(() => {
    document.removeEventListener('pointerdown', onPointerDown)
    document.removeEventListener('keydown', onKeydown)
})
</script>

<template>
    <div ref="root" class="impersonation">
        <div
            v-if="open"
            id="impersonation-card"
            class="impersonation-card"
            role="dialog"
            aria-label="Sesión de soporte"
        >
            <AppIcon name="shield" />

            <div class="alert-body">
                <strong>Estás en el panel de {{ auth.impersonating }}</strong>
                <span>Lo que edites se guarda a nombre del dueño de la tienda.</span>

                <div class="impersonation-actions">
                    <button class="btn btn-outline btn-sm" type="button" @click="backToAdmin">
                        Volver a superadmin
                    </button>
                </div>
            </div>

            <button
                class="btn btn-ghost btn-icon impersonation-close"
                type="button"
                title="Cerrar"
                aria-label="Cerrar"
                @click="close"
            >
                <AppIcon name="close" />
            </button>
        </div>

        <button
            class="impersonation-toggle"
            type="button"
            :title="`Estás en el panel de ${auth.impersonating}`"
            :aria-label="`Estás en el panel de ${auth.impersonating}`"
            aria-controls="impersonation-card"
            :aria-expanded="open"
            @click="toggle"
        >
            <AppIcon name="shield" />
        </button>
    </div>
</template>
