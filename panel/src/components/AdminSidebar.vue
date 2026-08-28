<script setup>
import { ref } from 'vue'

import AppIcon from '@/components/AppIcon.vue'
import NavItem from '@/components/NavItem.vue'
import { useUiStore } from '@/stores/ui'

const ui = useUiStore()

const open = ref({ platform: false })

function toggle(key) {
    open.value[key] = ! open.value[key]
}
</script>

<template>
    <aside class="sidebar">
        <div class="sidebar-brand">
            <span class="brand-mark">
                <AppIcon name="shieldCheck" />
            </span>
            <span class="brand-text">
                <strong>Catálogos</strong>
                <span>Plataforma</span>
            </span>
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

                        <li>
                            <div class="nav-branch" :class="{ 'is-open': open.platform }">
                                <button
                                    class="nav-toggle"
                                    type="button"
                                    :aria-expanded="open.platform"
                                    @click="toggle('platform')"
                                >
                                    <span class="nav-icon">
                                        <AppIcon name="settings" />
                                    </span>
                                    <span class="nav-text">Plataforma</span>
                                    <span class="nav-arrow">
                                        <AppIcon name="chevronRight" stroke-width="2.5" />
                                    </span>
                                </button>

                                <div class="nav-sub">
                                    <ul>
                                        <li>
                                            <NavItem :to="{ name: 'admin-appearance' }">
                                                <span class="nav-text">Apariencia</span>
                                            </NavItem>
                                        </li>
                                        <li>
                                            <div class="nav-item">
                                                <a href="#">
                                                    <span class="nav-text">Correos</span>
                                                </a>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="nav-item">
                                                <a href="#">
                                                    <span class="nav-text">Ajustes</span>
                                                </a>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="sidebar-footer">
            <button class="sidebar-collapse" type="button" @click="ui.toggleSidebar()">
                <AppIcon name="chevronsLeft" />
                <span>Contraer menú</span>
            </button>
        </div>
    </aside>
</template>
