<script setup>
import AppIcon from '@/components/AppIcon.vue'
import { THEMES, useUiStore } from '@/stores/ui'

const ui = useUiStore()
</script>

<template>
    <main class="auth">
        <div class="auth-brand">
            <span class="brand-mark">
                <AppIcon name="bag" />
            </span>
            <strong>Catálogos</strong>
        </div>

        <section class="auth-card">
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
