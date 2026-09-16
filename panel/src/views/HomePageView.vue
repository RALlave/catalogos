<!--
    Página Home del catálogo: sus secciones, una por pestaña.

    La pestaña es estado de la vista y no toca la URL: se cambia sin navegar y
    al recargar se abre la primera.
-->

<script setup>
import { ref } from 'vue'

import HomeFeaturedPanel from '@/components/HomeFeaturedPanel.vue'
import HomeHeroPanel from '@/components/HomeHeroPanel.vue'

const TABS = [
    { key: 'hero', label: 'Hero (banner)' },
    { key: 'featured', label: 'Destacados' },
]

const tab = ref(TABS[0].key)

/* Cambiar de pestaña desmonta el panel: si tiene cambios, primero avisa. */
const featuredPanel = ref(null)

async function goTab(key) {
    if (featuredPanel.value && ! await featuredPanel.value.confirmLeave()) {
        return
    }

    tab.value = key
}
</script>

<template>
    <div class="page-header">
        <div class="page-title">
            <h1>Home</h1>
            <p>Las secciones de la portada de tu catálogo</p>
        </div>
    </div>

    <nav class="tabs">
        <button
            v-for="item in TABS"
            :key="item.key"
            class="tab"
            :class="{ 'is-active': tab === item.key }"
            type="button"
            @click="goTab(item.key)"
        >
            <span>{{ item.label }}</span>
        </button>
    </nav>

    <HomeHeroPanel v-if="tab === 'hero'" />
    <HomeFeaturedPanel v-else ref="featuredPanel" />
</template>
