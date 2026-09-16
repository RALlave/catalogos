<!--
    BÚSQUEDA — resultados de la tienda.

    El término, la categoría y la página son la URL (?s= &cat= &page=), igual
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

const term = computed(() => String(route.query.s ?? '').trim())
const category = computed(() => String(route.query.cat ?? ''))
const page = computed(() => Number(route.query.page ?? 1))

const filters = computed<ProductFilters>(() => ({
    ...(term.value ? { search: term.value } : {}),
    ...(category.value ? { category: category.value } : {}),
    ...(page.value > 1 ? { page: page.value } : {}),
}))

/* Sin término no hay nada que buscar: la primera carga no pide productos y la
   petición sale recién cuando la URL trae un `s`. */
const { data: products } = await useStoreProducts(filters, { immediate: Boolean(term.value) })

const results = computed(() => (term.value ? products.value?.data ?? [] : []))
const total = computed(() => (term.value ? products.value?.meta.total ?? 0 : 0))
const lastPage = computed(() => (term.value ? products.value?.meta.last_page ?? 1 : 1))

/* Banner photo: a random hero with an image, like Contacto. The draw lives in
   useState so server and client pick the same one and it doesn't flicker. */
const photos = computed(() => (store.value.heroes ?? []).filter(hero => hero.image_url))

const draw = useState('search-hero-draw', () => Math.random())

const photo = computed(() => photos.value.length
    ? photos.value[Math.floor(draw.value * photos.value.length) % photos.value.length]
    : null)

const totalLabel = computed(() => `${total.value} ${total.value === 1 ? 'resultado' : 'resultados'} para «${term.value}»`)

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

        <!-- Short banner, same as Contacto: the searched text goes inside -->
        <section class="banner banner-short" aria-labelledby="search-title">
            <img
                v-if="photo"
                class="banner-photo"
                :src="photo.image_url ?? undefined"
                :srcset="photo.image_srcset || undefined"
                sizes="100vw"
                alt=""
                width="1024"
                height="411"
                fetchpriority="high"
            >

            <div class="container banner-inner">
                <p v-if="term" class="banner-eyebrow">Resultados de búsqueda</p>
                <h1 id="search-title" class="banner-title">
                    <template v-if="term">«{{ term }}»</template>
                    <template v-else>Buscar productos</template>
                </h1>
            </div>
        </section>

        <section class="section" aria-label="Resultados">
            <div class="container">

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
                    <NuxtLink to="/">mirá todo el catálogo</NuxtLink>.
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
