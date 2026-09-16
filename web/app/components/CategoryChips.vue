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
</script>

<template>
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
