<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'

import AppDropdown from '@/components/AppDropdown.vue'
import AppIcon from '@/components/AppIcon.vue'
import { useAuthStore } from '@/stores/auth'
import { ADMIN_THEMES, THEMES, useUiStore } from '@/stores/ui'

const props = defineProps({
    admin: { type: Boolean, default: false },
})

const auth = useAuthStore()
const ui = useUiStore()
const router = useRouter()

const themes = computed(() => (props.admin ? ADMIN_THEMES : THEMES))

async function logout() {
    await auth.logout()
    await router.push({ name: 'login' })
}
</script>

<template>
    <header class="topbar">
        <div class="topbar-left">
            <button
                class="sidebar-toggle"
                type="button"
                aria-label="Abrir menú"
                @click="ui.toggleSidebar()"
            >
                <AppIcon name="menu" />
            </button>

            <div class="search topbar-search">
                <AppIcon name="search" />
                <input class="input" type="search" placeholder="Buscar productos, categorías…" aria-label="Buscar">
            </div>
        </div>

        <div class="topbar-right">
            <a
                v-if="auth.store"
                class="btn btn-outline btn-sm"
                :href="auth.store.public_url"
                target="_blank"
                rel="noopener"
            >
                <AppIcon name="external" />
                Ver mi catálogo
            </a>

            <AppDropdown>
                <template #trigger="{ toggle, open }">
                    <button
                        class="topbar-action"
                        type="button"
                        :aria-expanded="open"
                        aria-label="Cambiar tema"
                        @click="toggle"
                    >
                        <AppIcon name="theme" />
                    </button>
                </template>

                <div class="dropdown-head">
                    <strong>Tema del panel</strong>
                    <span>Se guarda en este navegador</span>
                </div>

                <div
                    v-for="item in themes"
                    :key="item.key"
                    class="dropdown-item"
                    :class="{ 'is-current': ui.currentTheme() === item.key }"
                >
                    <button type="button" @click="ui.applyTheme(item.key)">
                        <span class="theme-swatch" :class="`theme-${item.key}`">
                            <span />
                            <span />
                        </span>
                        {{ item.name }}
                        <span class="theme-check">
                            <AppIcon name="check" stroke-width="3" />
                        </span>
                    </button>
                </div>
            </AppDropdown>

            <AppDropdown>
                <template #trigger="{ toggle, open }">
                    <button
                        class="topbar-action"
                        type="button"
                        :aria-expanded="open"
                        aria-label="Notificaciones"
                        @click="toggle"
                    >
                        <AppIcon name="bell" />
                    </button>
                </template>

                <div class="dropdown-head">
                    <strong>Notificaciones</strong>
                    <span>Sin novedades por ahora</span>
                </div>
            </AppDropdown>

            <div class="topbar-divider" />

            <AppDropdown>
                <template #trigger="{ toggle, open }">
                    <button class="user-button" type="button" :aria-expanded="open" @click="toggle">
                        <span class="avatar">{{ auth.initials }}</span>
                        <span class="user-meta">
                            <strong>{{ auth.user?.name }}</strong>
                            <span>{{ admin ? 'Plataforma' : auth.store?.name }}</span>
                        </span>
                        <AppIcon name="chevronDown" />
                    </button>
                </template>

                <div class="dropdown-head">
                    <strong>{{ auth.user?.name }}</strong>
                    <span>{{ auth.user?.email }}</span>
                </div>

                <hr>

                <template v-if="! admin">
                    <div class="dropdown-item">
                        <RouterLink :to="{ name: 'account', hash: '#perfil' }">
                            <AppIcon name="user" />
                            Mi perfil
                        </RouterLink>
                    </div>

                    <div class="dropdown-item">
                        <RouterLink :to="{ name: 'account', hash: '#seguridad' }">
                            <AppIcon name="shield" />
                            Seguridad
                        </RouterLink>
                    </div>

                    <div class="dropdown-item">
                        <RouterLink :to="{ name: 'settings' }">
                            <AppIcon name="settings" />
                            Configuración
                        </RouterLink>
                    </div>

                    <hr>
                </template>

                <div class="dropdown-item is-danger">
                    <button type="button" @click="logout">
                        <AppIcon name="logout" />
                        Cerrar sesión
                    </button>
                </div>
            </AppDropdown>
        </div>
    </header>
</template>
