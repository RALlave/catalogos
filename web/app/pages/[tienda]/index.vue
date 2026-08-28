<script setup lang="ts">
import type { ProductFilters } from '~/composables/useCatalog'
import type { Store } from '~/types/catalog'

definePageMeta({ layout: 'store' })

const route = useRoute()

const { data: storeData } = await useCurrentStore()
const store = computed(() => storeData.value as Store)

/* Los filtros son la URL, no un estado aparte: así el listado se puede
   compartir, funciona sin JS y cada combinación es indexable. */
const category = computed(() => String(route.query.cat ?? ''))
const search = computed(() => String(route.query.q ?? ''))
const page = computed(() => Number(route.query.page ?? 1))

const filters = computed<ProductFilters>(() => ({
    ...(category.value ? { category: category.value } : {}),
    ...(search.value ? { search: search.value } : {}),
    ...(page.value > 1 ? { page: page.value } : {}),
}))

const { data: products } = await useStoreProducts(filters)

const total = computed(() => products.value?.meta.total ?? 0)
const totalLabel = computed(() => `${total.value} ${total.value === 1 ? 'producto' : 'productos'}`)
const lastPage = computed(() => products.value?.meta.last_page ?? 1)

/* El SEO se edita en el panel; sin cargar, se arma con los datos de la tienda. */
const title = computed(() => store.value.meta_title || `Catálogo de productos — ${store.value.name}`)
const description = computed(() => store.value.meta_description || store.value.description || undefined)

useSeoMeta({
    title,
    description,
    ogType: 'website',
    ogTitle: title,
    ogDescription: description,
    ogImage: () => store.value.cover_url ?? store.value.logo_url ?? undefined,
})
</script>

<template>
    <main id="content" class="content">

        <TheBanner :store="store" />

        <section id="products" class="section" aria-labelledby="products-title">
            <div class="container">

                <div class="section-header">
                    <h2 id="products-title">Productos</h2>

                    <CategoryChips
                        v-if="store.categories.length"
                        :categories="store.categories"
                        :active="category"
                    />
                </div>

                <p class="result" role="status">{{ totalLabel }}</p>

                <ul v-if="products?.data.length" class="grid">
                    <ProductCard
                        v-for="product in products.data"
                        :key="product.slug"
                        :product="product"
                        :store="store"
                    />
                </ul>

                <p v-else class="empty">
                    <AppIcon name="search" class="empty-icon" />
                    No encontramos productos con ese filtro. Probá con otra categoría o borrá la búsqueda.
                </p>

                <ThePagination :current="page" :last="lastPage" />

            </div>
        </section>

    </main>
</template>
