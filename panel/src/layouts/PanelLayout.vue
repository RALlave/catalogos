<script setup>
import { computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import AdminSidebar from '@/components/AdminSidebar.vue'
import AppIcon from '@/components/AppIcon.vue'
import AppSidebar from '@/components/AppSidebar.vue'
import AppTopbar from '@/components/AppTopbar.vue'
import { useAuthStore } from '@/stores/auth'
import { useUiStore } from '@/stores/ui'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const ui = useUiStore()

/** Volver a la sesión de superadmin y a su listado de tiendas. */
async function backToAdmin() {
    await auth.stopImpersonating()

    ui.toast('Volviste a tu sesión')

    await router.push({ name: 'admin-stores' })
}

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
                <div v-if="auth.impersonating" class="alert alert-warning">
                    <AppIcon name="shield" />
                    <div class="alert-body">
                        <strong>Estás en el panel de {{ auth.impersonating }}</strong>
                        <span>Lo que edites se guarda a nombre del dueño de la tienda.</span>
                    </div>

                    <button class="btn btn-outline btn-sm" type="button" @click="backToAdmin">
                        Volver a superadmin
                    </button>
                </div>

                <RouterView />
            </main>
        </div>
    </div>
</template>
