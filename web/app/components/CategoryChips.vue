<!--
    Filtro por categoría. Cada chip es un enlace a la misma página con
    otro ?cat=, así que el filtro funciona sin JS y cada categoría tiene
    su URL para compartir. Al filtrar se vuelve a la página 1.
-->

<script setup lang="ts">
import type { Category } from '~/types/catalog'

const props = defineProps<{ categories: Category[], active: string, loading?: boolean }>()

const route = useRoute()

function linkTo(slug: string) {
    return { path: route.path, query: { ...route.query, cat: slug || undefined, page: undefined } }
}

const chips = computed(() => [
    { name: 'Todas', slug: '' },
    ...props.categories,
])

/* Mobile shows a dropdown instead of the chips. It is a native <details>, so
   it opens without JS; with JS the SPA navigation keeps it mounted, so it is
   closed by hand once the category changes. */
const menu = ref<HTMLDetailsElement | null>(null)

const current = computed(() => chips.value.find(chip => chip.slug === props.active)?.name ?? 'Todas')

watch(() => route.query.cat, () => {
    if (menu.value) {
        menu.value.open = false
    }
})
</script>

<template>
    <details ref="menu" class="category-menu">
        <summary class="category-menu-toggle">
            <span v-if="loading" class="chip-spinner" aria-hidden="true"></span>
            <span class="category-menu-current">{{ current }}</span>
            <AppIcon name="chevron" class="category-menu-icon" />
        </summary>

        <nav class="category-menu-list" aria-label="Filtrar por categoría">
            <ul>
                <li v-for="chip in chips" :key="chip.slug">
                    <NuxtLink :to="linkTo(chip.slug)" :aria-current="chip.slug === active ? 'true' : undefined">
                        {{ chip.name }}
                    </NuxtLink>
                </li>
            </ul>
        </nav>
    </details>

    <div class="filters" role="group" aria-label="Filtrar por categoría" :aria-busy="loading || undefined">
        <NuxtLink
            v-for="chip in chips"
            :key="chip.slug"
            class="chip"
            :to="linkTo(chip.slug)"
            :aria-current="chip.slug === active ? 'true' : undefined"
        >
            <span v-if="loading && chip.slug === active" class="chip-spinner" aria-hidden="true"></span>
            <AppIcon v-else name="check" class="chip-icon" />
            {{ chip.name }}
        </NuxtLink>
    </div>
</template>
