<script setup>
import { onMounted } from 'vue'

import AppIcon from '@/components/AppIcon.vue'
import NavItem from '@/components/NavItem.vue'
import SidebarMobileExtras from '@/components/SidebarMobileExtras.vue'
import { usePwaInstall } from '@/lib/pwa'
import { usePlatformStore } from '@/stores/platform'
import { useUiStore } from '@/stores/ui'

const ui = useUiStore()
const platform = usePlatformStore()

/* El dueño de una tienda la tiene en el menú de su usuario, no acá. */
const { canInstall, install } = usePwaInstall()

onMounted(() => platform.load())
</script>

<template>
    <aside class="sidebar">
        <div v-if="platform.logos.panel" class="sidebar-brand">
            <RouterLink :to="{ name: 'admin-dashboard' }" @click="ui.closeMobile()">
                <img
                    :src="platform.logos.panel.src"
                    :srcset="platform.logos.panel.srcset"
                    sizes="218px"
                    alt="Ir al dashboard"
                >
            </RouterLink>
        </div>

        <nav class="sidebar-nav" aria-label="Menú principal">
            <div class="nav-group">
                <div class="nav-group-label">Plataforma</div>

                <div class="nav-list">
                    <ul>
                        <li>
                            <NavItem :to="{ name: 'admin-dashboard' }" exact>
                                <span class="nav-icon">
                                    <AppIcon name="grid" />
                                </span>
                                <span class="nav-text">Dashboard</span>
                            </NavItem>
                        </li>

                        <li>
                            <NavItem :to="{ name: 'admin-stores' }">
                                <span class="nav-icon">
                                    <AppIcon name="store" />
                                </span>
                                <span class="nav-text">Tiendas</span>
                            </NavItem>
                        </li>

                        <li>
                            <NavItem :to="{ name: 'admin-users' }">
                                <span class="nav-icon">
                                    <AppIcon name="users" />
                                </span>
                                <span class="nav-text">Usuarios</span>
                            </NavItem>
                        </li>

                        <li>
                            <NavItem :to="{ name: 'admin-plans' }">
                                <span class="nav-icon">
                                    <AppIcon name="card" />
                                </span>
                                <span class="nav-text">Planes</span>
                            </NavItem>
                        </li>

                        <li>
                            <NavItem :to="{ name: 'admin-moderation' }">
                                <span class="nav-icon">
                                    <AppIcon name="flag" />
                                </span>
                                <span class="nav-text">Moderación</span>
                            </NavItem>
                        </li>

                    </ul>
                </div>
            </div>

            <div class="nav-group">
                <div class="nav-group-label">Configuración</div>

                <div class="nav-list">
                    <ul>
                        <li>
                            <NavItem :to="{ name: 'admin-appearance' }">
                                <span class="nav-icon">
                                    <AppIcon name="theme" />
                                </span>
                                <span class="nav-text">Apariencia</span>
                            </NavItem>
                        </li>

                        <li>
                            <NavItem :to="{ name: 'admin-backup' }">
                                <span class="nav-icon">
                                    <AppIcon name="database" />
                                </span>
                                <span class="nav-text">Import &amp; Export</span>
                            </NavItem>
                        </li>

                        <li>
                            <div class="nav-item">
                                <a href="#">
                                    <span class="nav-icon">
                                        <AppIcon name="mail" />
                                    </span>
                                    <span class="nav-text">Correos</span>
                                </a>
                            </div>
                        </li>

                        <li>
                            <NavItem :to="{ name: 'admin-settings' }">
                                <span class="nav-icon">
                                    <AppIcon name="settings" />
                                </span>
                                <span class="nav-text">Ajustes</span>
                            </NavItem>
                        </li>
                    </ul>
                </div>
            </div>

            <SidebarMobileExtras admin />
        </nav>

        <div class="sidebar-footer">
            <button
                v-if="canInstall"
                class="sidebar-action"
                type="button"
                @click="install"
            >
                <AppIcon name="monitor" />
                <span>Instalar en el escritorio</span>
            </button>

            <button class="sidebar-action sidebar-collapse" type="button" @click="ui.toggleSidebar()">
                <AppIcon name="chevronsLeft" />
                <span>Contraer menú</span>
            </button>
        </div>
    </aside>
</template>
