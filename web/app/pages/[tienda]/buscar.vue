<!--
    BÚSQUEDA — resultados de la tienda.

    El término, la categoría y la página son la URL (?q= &cat= &page=), igual
    que en el catálogo: la búsqueda se puede compartir y anda sin JS. Es la
    única página que no se indexa: cada término sería una URL distinta con el
    mismo contenido que el listado.
-->

<script setup lang="ts">
import type { ProductFilters } from '~/composables/useCatalog'
import type { Store } from '~/types/catalog'

definePageMeta({ layout: 'store' })

const route = useRoute()

const { data: storeData } = await useCurrentStore()
const store = computed(() => storeData.value as Store)

const storePath = computed(() => `/${store.value.slug}`)

const term = computed(() => String(route.query.q ?? '').trim())
const category = computed(() => String(route.query.cat ?? ''))
const page = computed(() => Number(route.query.page ?? 1))

const filters = computed<ProductFilters>(() => ({
    ...(term.value ? { search: term.value } : {}),
    ...(category.value ? { category: category.value } : {}),
    ...(page.value > 1 ? { page: page.value } : {}),
}))

/* Sin término no hay nada que buscar: la primera carga no pide productos y la
   petición sale recién cuando la URL trae un `q`. */
const { data: products } = await useStoreProducts(filters, { immediate: Boolean(term.value) })

const results = computed(() => (term.value ? products.value?.data ?? [] : []))
const total = computed(() => (term.value ? products.value?.meta.total ?? 0 : 0))
const lastPage = computed(() => (term.value ? products.value?.meta.last_page ?? 1 : 1))

const totalLabel = computed(() => `${total.value} ${total.value === 1 ? 'resultado' : 'resultados'} para «${term.value}»`)

/* El campo es un borrador: mientras se escribe no toca la URL. La búsqueda
   pasa a la URL al enviar, y de la URL vuelve al campo si se llega por un
   enlace o con el botón de atrás. */
const query = ref(term.value)

watch(term, (value) => {
    query.value = value
})

function submit() {
    navigateTo({ path: route.path, query: { ...route.query, q: query.value.trim() || undefined, page: undefined } })
}

const title = computed(() => (term.value
    ? `Búsqueda de «${term.value}» — ${store.value.name}`
    : `Buscar productos — ${store.value.name}`))

useSeoMeta({
    title,
    /* `follow` para que los productos encontrados sigan recorriéndose desde
       acá; `noindex` porque la página en sí no aporta nada al buscador. */
    robots: 'noindex, follow',
})
</script>

<template>
    <main id="content" class="content">

        <nav class="breadcrumbs" aria-label="Migas de pan">
            <div class="container">
                <ol class="breadcrumbs-list">
                    <li class="breadcrumbs-item">
                        <NuxtLink class="breadcrumbs-link" :to="storePath">Inicio</NuxtLink>
                        <AppIcon name="chevron" class="breadcrumbs-icon" />
                    </li>
                    <li class="breadcrumbs-item">
                        <span class="breadcrumbs-current" aria-current="page">Buscar</span>
                    </li>
                </ol>
            </div>
        </nav>

        <section class="section" aria-labelledby="search-title">
            <div class="container">

                <div class="section-header">
                    <h1 id="search-title">
                        <template v-if="term">Resultados para «{{ term }}»</template>
                        <template v-else>Buscar productos</template>
                    </h1>

                    <!-- Sin JS el navegador arma la URL solo: el término va en
                         `q` y la categoría viaja en un campo oculto para que
                         el filtro no se pierda al buscar de nuevo. -->
                    <form class="search search-page" role="search" method="get" @submit.prevent="submit">
                        <label class="visually-hidden" for="search-q">Buscar productos</label>
                        <input
                            id="search-q"
                            v-model="query"
                            class="search-field"
                            type="search"
                            name="q"
                            placeholder="Buscar productos…"
                            autocomplete="off"
                            maxlength="120"
                            @click="($event.target as HTMLInputElement).select()"
                        >
                        <input v-if="category" type="hidden" name="cat" :value="category">
                        <button class="search-submit" type="submit">
                            <AppIcon name="search" class="search-submit-icon" />
                            <span class="visually-hidden">Buscar</span>
                        </button>
                    </form>

                    <CategoryChips
                        v-if="term && store.categories.length"
                        :categories="store.categories"
                        :active="category"
                    />
                </div>

                <p v-if="term" class="result" role="status">{{ totalLabel }}</p>

                <ul v-if="results.length" class="grid">
                    <ProductCard
                        v-for="product in results"
                        :key="product.slug"
                        :product="product"
                        :store="store"
                    />
                </ul>

                <p v-else-if="term" class="empty">
                    <AppIcon name="search" class="empty-icon" />
                    No encontramos productos para «{{ term }}». Probá con otra palabra o
                    <NuxtLink :to="storePath">mirá todo el catálogo</NuxtLink>.
                </p>

                <p v-else class="empty">
                    <AppIcon name="search" class="empty-icon" />
                    Escribí el nombre de un producto para buscarlo en el catálogo.
                </p>

                <ThePagination :current="page" :last="lastPage" />

            </div>
        </section>

    </main>
</template>
