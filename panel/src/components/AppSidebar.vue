<script setup>
import { ref } from 'vue'

import AppIcon from '@/components/AppIcon.vue'
import NavItem from '@/components/NavItem.vue'
import { useUiStore } from '@/stores/ui'

const ui = useUiStore()

/* Ramas desplegables del menú: arranca abierta la del catálogo. */
const open = ref({ catalog: true, store: false, account: false })

function toggle(key) {
    open.value[key] = ! open.value[key]
}
</script>

<template>
    <aside class="sidebar">
        <div class="sidebar-brand">
            <span class="brand-mark">
                <AppIcon name="bag" />
            </span>
            <span class="brand-text">
                <strong>Catálogos</strong>
                <span>Panel de administración</span>
            </span>
        </div>

        <nav class="sidebar-nav" aria-label="Menú principal">
            <div class="nav-group">
                <div class="nav-group-label">Menú</div>

                <div class="nav-list">
                    <ul>
                        <li>
                            <NavItem :to="{ name: 'dashboard' }" exact>
                                <span class="nav-icon">
                                    <AppIcon name="grid" />
                                </span>
                                <span class="nav-text">Dashboard</span>
                            </NavItem>
                        </li>

                        <li>
                            <div class="nav-branch" :class="{ 'is-open': open.catalog }">
                                <button
                                    class="nav-toggle"
                                    type="button"
                                    :aria-expanded="open.catalog"
                                    @click="toggle('catalog')"
                                >
                                    <span class="nav-icon">
                                        <AppIcon name="box" />
                                    </span>
                                    <span class="nav-text">Catálogo</span>
                                    <span class="nav-arrow">
                                        <AppIcon name="chevronRight" stroke-width="2.5" />
                                    </span>
                                </button>

                                <div class="nav-sub">
                                    <ul>
                                        <li>
                                            <NavItem :to="{ name: 'products' }">
                                                <span class="nav-text">Productos</span>
                                            </NavItem>
                                        </li>
                                        <li>
                                            <NavItem :to="{ name: 'categories' }">
                                                <span class="nav-text">Categorías</span>
                                            </NavItem>
                                        </li>
                                        <li>
                                            <NavItem :to="{ name: 'media' }">
                                                <span class="nav-text">Multimedia</span>
                                            </NavItem>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </li>

                        <li>
                            <div class="nav-branch" :class="{ 'is-open': open.store }">
                                <button
                                    class="nav-toggle"
                                    type="button"
                                    :aria-expanded="open.store"
                                    @click="toggle('store')"
                                >
                                    <span class="nav-icon">
                                        <AppIcon name="store" />
                                    </span>
                                    <span class="nav-text">Mi tienda</span>
                                    <span class="nav-arrow">
                                        <AppIcon name="chevronRight" stroke-width="2.5" />
                                    </span>
                                </button>

                                <div class="nav-sub">
                                    <ul>
                                        <li>
                                            <NavItem :to="{ name: 'store' }">
                                                <span class="nav-text">Información</span>
                                            </NavItem>
                                        </li>
                                        <li>
                                            <NavItem :to="{ name: 'heroes' }">
                                                <span class="nav-text">Hero (banner)</span>
                                            </NavItem>
                                        </li>
                                        <li>
                                            <NavItem :to="{ name: 'settings' }">
                                                <span class="nav-text">Diseño</span>
                                            </NavItem>
                                        </li>
                                        <li>
                                            <NavItem :to="{ name: 'seo' }">
                                                <span class="nav-text">SEO</span>
                                            </NavItem>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </li>

                        <li>
                            <div class="nav-branch" :class="{ 'is-open': open.account }">
                                <button
                                    class="nav-toggle"
                                    type="button"
                                    :aria-expanded="open.account"
                                    @click="toggle('account')"
                                >
                                    <span class="nav-icon">
                                        <AppIcon name="user" />
                                    </span>
                                    <span class="nav-text">Cuenta</span>
                                    <span class="nav-arrow">
                                        <AppIcon name="chevronRight" stroke-width="2.5" />
                                    </span>
                                </button>

                                <div class="nav-sub">
                                    <ul>
                                        <li>
                                            <NavItem :to="{ name: 'account', hash: '#perfil' }">
                                                <span class="nav-text">Perfil</span>
                                            </NavItem>
                                        </li>
                                        <li>
                                            <NavItem :to="{ name: 'account', hash: '#seguridad' }">
                                                <span class="nav-text">Seguridad</span>
                                            </NavItem>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </li>

                        <li>
                            <NavItem :to="{ name: 'settings' }">
                                <span class="nav-icon">
                                    <AppIcon name="settings" />
                                </span>
                                <span class="nav-text">Configuración</span>
                            </NavItem>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="sidebar-footer">
            <div class="plan-card">
                <div class="plan-top">
                    <strong>Plan Gratis</strong>
                    <span class="badge badge-accent">MVP</span>
                </div>
                <div class="progress">
                    <div class="progress-bar" style="width: 48%" />
                </div>
                <p>24 de 50 productos usados</p>
            </div>

            <button class="sidebar-collapse" type="button" @click="ui.toggleSidebar()">
                <AppIcon name="chevronsLeft" />
                <span>Contraer menú</span>
            </button>
        </div>
    </aside>
</template>
