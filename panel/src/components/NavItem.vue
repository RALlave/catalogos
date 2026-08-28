<script setup>
import { useUiStore } from '@/stores/ui'

/**
 * El CSS marca el estado activo en .nav-item, no en el <a>, así que se usa
 * RouterLink en modo custom para poder poner la clase en el envoltorio.
 */
defineProps({
    to: { type: [String, Object], required: true },
    exact: { type: Boolean, default: false },
})

const ui = useUiStore()
</script>

<template>
    <RouterLink v-slot="{ href, isActive, isExactActive, navigate }" :to="to" custom>
        <div class="nav-item" :class="{ 'is-active': exact ? isExactActive : isActive }">
            <!-- navigate necesita el evento: sin él no cancela el salto del <a>
                 y el navegador recarga la página encima de la navegación SPA. -->
            <a :href="href" @click="navigate($event); ui.closeMobile()">
                <slot />
            </a>
        </div>
    </RouterLink>
</template>
