<script setup>
import { computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'

import AdminSidebar from '@/components/AdminSidebar.vue'
import AppSidebar from '@/components/AppSidebar.vue'
import AppTopbar from '@/components/AppTopbar.vue'
import { useUiStore } from '@/stores/ui'

const route = useRoute()
const ui = useUiStore()

/* El mismo layout sirve para los dos paneles: cambia el menú y el tema. */
const isAdmin = computed(() => route.path.startsWith('/admin'))

const title = computed(() => route.meta.title ?? '')

function syncScope() {
    ui.useScope(isAdmin.value ? 'admin' : 'panel')
}

watch(isAdmin, syncScope)

onMounted(syncScope)
</script>

<template>
    <div class="shell" :class="{ 'is-collapsed': ui.collapsed, 'is-mobile-open': ui.mobileOpen }">
        <AdminSidebar v-if="isAdmin" />
        <AppSidebar v-else />

        <div class="sidebar-overlay" @click="ui.closeMobile()" />

        <div class="main">
            <AppTopbar :admin="isAdmin" />

            <nav class="breadcrumb" aria-label="Ruta de navegación">
                <ol>
                    <li>
                        <RouterLink :to="isAdmin ? { name: 'admin-dashboard' } : { name: 'dashboard' }">
                            Inicio
                        </RouterLink>
                    </li>
                    <li aria-current="page">{{ title }}</li>
                </ol>
            </nav>

            <main class="content">
                <RouterView />
            </main>
        </div>
    </div>
</template>
