<script setup>
import { computed, ref } from 'vue'

import AppIcon from '@/components/AppIcon.vue'
import { useAuthStore } from '@/stores/auth'
import { ADMIN_THEMES, THEMES, useUiStore } from '@/stores/ui'

/*
 * What the topbar hides on mobile (up to 720px): the catalog link and the
 * panel theme. It lives below the menu, split by a line, because it is not
 * navigation. CSS hides it on wider screens.
 */
const props = defineProps({
    admin: { type: Boolean, default: false },
})

const auth = useAuthStore()
const ui = useUiStore()

const themes = computed(() => (props.admin ? ADMIN_THEMES : THEMES))

const themeOpen = ref(false)
</script>

<template>
    <div class="sidebar-extras">
        <div class="nav-list">
            <ul>
                <li v-if="! admin && auth.store">
                    <div class="nav-item">
                        <a :href="auth.store.public_url" @click="ui.closeMobile()">
                            <span class="nav-icon">
                                <AppIcon name="external" />
                            </span>
                            <span class="nav-text">Ver mi catálogo</span>
                        </a>
                    </div>
                </li>

                <li>
                    <div class="nav-branch" :class="{ 'is-open': themeOpen }">
                        <button
                            class="nav-toggle"
                            type="button"
                            :aria-expanded="themeOpen"
                            @click="themeOpen = ! themeOpen"
                        >
                            <span class="nav-icon">
                                <AppIcon name="theme" />
                            </span>
                            <span class="nav-text">Tema del panel</span>
                            <span class="nav-arrow">
                                <AppIcon name="chevronRight" stroke-width="2.5" />
                            </span>
                        </button>

                        <div class="nav-sub">
                            <ul>
                                <li v-for="item in themes" :key="item.key">
                                    <button
                                        class="nav-toggle sidebar-theme"
                                        :class="{ 'is-current': ui.currentTheme() === item.key }"
                                        type="button"
                                        :aria-pressed="ui.currentTheme() === item.key"
                                        @click="ui.applyTheme(item.key)"
                                    >
                                        <span class="theme-swatch" :class="`theme-${item.key}`">
                                            <span />
                                            <span />
                                        </span>
                                        <span class="nav-text">{{ item.name }}</span>
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>
