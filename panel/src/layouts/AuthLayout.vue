<script setup>
import { onMounted } from 'vue'

import { usePlatformStore } from '@/stores/platform'
import { THEMES, useUiStore } from '@/stores/ui'

/* Wide card for forms laid out in two columns (register). */
defineProps({
    wide: {
        type: Boolean,
        default: false,
    },
})

const ui = useUiStore()
const platform = usePlatformStore()

/* El logo lo sube el superadmin y es el mismo en todos los accesos, sea el
   dominio principal o el subdominio de una tienda. Si no hay ninguno cargado
   la marca no se dibuja: no hay texto de reserva. */
onMounted(() => platform.load())
</script>

<template>
    <main class="auth">
        <div v-if="platform.logos.auth" class="auth-brand">
            <img
                :src="platform.logos.auth.src"
                :srcset="platform.logos.auth.srcset"
                sizes="260px"
                alt="Logo"
            >
        </div>

        <section class="auth-card" :class="{ 'is-wide': wide }">
            <slot />
        </section>

        <footer class="auth-footer">
            <a href="#">Términos</a>
            <a href="#">Privacidad</a>
            <a href="#">Ayuda</a>

            <div class="theme-picker">
                <button
                    v-for="item in THEMES"
                    :key="item.key"
                    type="button"
                    :aria-label="`Tema ${item.name}`"
                    @click="ui.applyTheme(item.key)"
                >
                    <span class="theme-swatch" :class="`theme-${item.key}`">
                        <span />
                        <span />
                    </span>
                </button>
            </div>
        </footer>
    </main>
</template>
