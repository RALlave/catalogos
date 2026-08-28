<!--
    Paginación del catálogo. Cada página es un enlace con su ?page=,
    así que anda sin JS y cada página del listado es indexable.

    Cuando no hay a dónde ir, la flecha se pinta como <span> con
    aria-disabled: se ve igual y no entra en el recorrido de teclado.
-->

<script setup lang="ts">
const props = defineProps<{ current: number, last: number }>()

const route = useRoute()

function linkTo(page: number) {
    return { path: route.path, query: { ...route.query, page: page > 1 ? page : undefined } }
}

const pages = computed(() => Array.from({ length: props.last }, (_, index) => index + 1))
</script>

<template>
    <nav v-if="last > 1" class="pagination" aria-label="Paginación de productos">
        <NuxtLink v-if="current > 1" class="page page-arrow page-prev" :to="linkTo(current - 1)" rel="prev">
            <AppIcon name="chevron" class="page-icon" />
            <span class="page-text">Anterior</span>
        </NuxtLink>
        <span v-else class="page page-arrow page-prev" aria-disabled="true">
            <AppIcon name="chevron" class="page-icon" />
            <span class="page-text">Anterior</span>
        </span>

        <ol class="pagination-list">
            <li v-for="page in pages" :key="page">
                <NuxtLink
                    class="page"
                    :to="linkTo(page)"
                    :aria-current="page === current ? 'page' : undefined"
                >
                    {{ page }}
                </NuxtLink>
            </li>
        </ol>

        <NuxtLink v-if="current < last" class="page page-arrow page-next" :to="linkTo(current + 1)" rel="next">
            <span class="page-text">Siguiente</span>
            <AppIcon name="chevron" class="page-icon" />
        </NuxtLink>
        <span v-else class="page page-arrow page-next" aria-disabled="true">
            <span class="page-text">Siguiente</span>
            <AppIcon name="chevron" class="page-icon" />
        </span>
    </nav>
</template>
