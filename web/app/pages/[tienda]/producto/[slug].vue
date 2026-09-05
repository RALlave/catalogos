<script setup lang="ts">
import type { Product, Store } from '~/types/catalog'

definePageMeta({ layout: 'store' })

const route = useRoute()

const productSlug = computed(() => String(route.params.slug ?? ''))

const { data: storeData } = await useCurrentStore()
const { data: productData, error } = await useStoreProduct(productSlug)

if (error.value || ! productData.value) {
    throw createError({
        statusCode: 404,
        statusMessage: 'No encontramos ese producto.',
        fatal: true,
    })
}

const store = computed(() => storeData.value as Store)
const product = computed(() => productData.value as Product)

const storePath = computed(() => `/${store.value.slug}`)

const { data: catalogPage } = await useRelatedProducts(productData)

const related = computed(() => (catalogPage.value?.data ?? [])
    .filter((item) => item.slug !== product.value.slug)
    .slice(0, 3))

const { siteUrl } = useRuntimeConfig().public

const price = computed(() => product.value.sale_price ?? product.value.price)

const discount = computed(() => discountPercent(product.value.price, product.value.sale_price))

const productUrl = computed(() => `${siteUrl}${route.path}`)

const whatsapp = computed(() => store.value.whatsapp
    ? whatsappUrl(
        store.value.whatsapp,
        `Hola ${store.value.name}, me interesa el producto: ${product.value.name}`
        + (product.value.sku ? ` (${product.value.sku})` : '')
        + ` ${productUrl.value}`,
    )
    : null)

const shareUrl = computed(() => `https://wa.me/?text=${encodeURIComponent(`${product.value.name} — ${productUrl.value}`)}`)

/* El enlace se abre igual: el aviso a la API sale en paralelo. */
function share(): void {
    trackShare(store.value.slug, product.value.slug)
}

/* Las pestañas se arman con lo que el producto tenga cargado. Con una
   sola no se activa el modo tablist: se ve el panel abierto. */
const tabs = computed(() => [
    ...(product.value.description ? [{ id: 'description', label: 'Descripción' }] : []),
    ...(product.value.specs?.length ? [{ id: 'specs', label: 'Ficha técnica' }] : []),
])

const title = computed(() => `${product.value.name} — ${store.value.name}`)

useSeoMeta({
    title,
    description: () => product.value.description ?? undefined,
    ogType: 'product',
    ogTitle: () => product.value.name,
    ogDescription: () => product.value.description ?? undefined,
    /* Para compartir va la más grande: las redes la recortan a su gusto. */
    ogImage: () => product.value.images[0]?.src ?? store.value.logo_url ?? undefined,
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
                    <li v-if="product.category" class="breadcrumbs-item">
                        <NuxtLink
                            class="breadcrumbs-link"
                            :to="{ path: storePath, query: { cat: product.category.slug } }"
                        >
                            {{ product.category.name }}
                        </NuxtLink>
                        <AppIcon name="chevron" class="breadcrumbs-icon" />
                    </li>
                    <li class="breadcrumbs-item">
                        <span class="breadcrumbs-current" aria-current="page">{{ product.name }}</span>
                    </li>
                </ol>
            </div>
        </nav>

        <section class="section detail" aria-labelledby="product-title">
            <div class="container detail-inner">

                <ProductGallery
                    v-if="product.images.length"
                    :images="product.images"
                    :name="product.name"
                    :is-new="product.is_new"
                    :sold-out="product.sold_out"
                />

                <div class="detail-info">

                    <h1 id="product-title" class="detail-title">{{ product.name }}</h1>

                    <ul class="detail-meta">
                        <li v-if="product.sku" class="detail-meta-item">
                            Código <strong>{{ product.sku }}</strong>
                        </li>
                        <li v-if="product.category" class="detail-meta-item">
                            <NuxtLink
                                class="detail-meta-link"
                                :to="{ path: storePath, query: { cat: product.category.slug } }"
                            >
                                {{ product.category.name }}
                            </NuxtLink>
                        </li>
                        <li class="detail-meta-item detail-stock">
                            <AppIcon :name="product.sold_out ? 'sold-out' : 'check'" class="detail-meta-icon" />
                            {{ product.sold_out ? 'Agotado' : 'Disponible' }}
                        </li>
                    </ul>

                    <p v-if="price" class="price price-detail">
                        <span v-if="store.currency" class="price-currency">{{ store.currency }}</span>
                        <strong class="price-value">{{ formatAmount(price) }}</strong>
                        <s v-if="discount" class="price-before">
                            <span class="visually-hidden">Precio anterior:</span>
                            {{ store.currency }} {{ formatAmount(product.price) }}
                        </s>
                        <span v-if="discount" class="price-off">
                            <AppIcon name="tag" class="price-off-icon" />
                            {{ discount }}&nbsp;% menos
                        </span>
                    </p>

                    <p v-if="product.description" class="detail-summary">{{ product.description }}</p>

                    <dl v-if="product.specs?.length" class="spec spec-detail">
                        <div v-for="spec in product.specs" :key="spec.label" class="spec-row">
                            <dt class="spec-key">{{ spec.label }}</dt>
                            <dd class="spec-value">
                                <ul v-if="spec.type === 'colors'" class="swatches swatches-detail">
                                    <li
                                        v-for="color in spec.values ?? []"
                                        :key="color"
                                        class="swatch"
                                        :style="{ backgroundColor: color }"
                                    >
                                        <span class="visually-hidden">{{ color }}</span>
                                    </li>
                                </ul>
                                <template v-else>{{ spec.value }}</template>
                            </dd>
                        </div>
                    </dl>

                    <!-- Con el carrito encendido la consulta directa desaparece:
                         el pedido se arma en el carrito y se manda de una vez.
                         Un producto agotado tampoco se consulta: para eso está
                         la lista de espera de abajo. -->
                    <ul class="detail-actions">
                        <li v-if="store.cart_enabled && ! product.sold_out">
                            <ClientOnly>
                                <AddToCartButton :product="product" :store="store" />
                            </ClientOnly>
                        </li>
                        <li v-else-if="! store.cart_enabled && whatsapp">
                            <a class="btn btn-primary" :href="whatsapp" target="_blank" rel="noopener">
                                <AppIcon name="whatsapp" class="btn-icon" />
                                Consultar por WhatsApp
                            </a>
                        </li>
                        <li>
                            <a
                                class="btn btn-border"
                                :href="shareUrl"
                                target="_blank"
                                rel="noopener"
                                @click="share"
                            >
                                <AppIcon name="share" class="btn-icon" />
                                Compartir
                            </a>
                        </li>
                    </ul>

                    <WaitlistForm
                        v-if="store.waitlist_enabled && product.sold_out"
                        :product="product"
                        :store="store"
                    />

                    <ul v-if="product.benefits?.length" class="benefits">
                        <li v-for="benefit in product.benefits" :key="benefit" class="benefit">
                            <AppIcon name="check" class="benefit-icon" />
                            <span class="benefit-body">
                                <strong class="benefit-title">{{ benefit }}</strong>
                            </span>
                        </li>
                    </ul>

                </div>

            </div>
        </section>

        <section v-if="tabs.length" class="section section-details" aria-labelledby="more-title">
            <div class="container">

                <h2 id="more-title">Más sobre este producto</h2>

                <ProductTabs :tabs="tabs">
                    <template #description>
                        <p>{{ product.description }}</p>
                    </template>

                    <template #specs>
                        <dl class="spec">
                            <div v-for="spec in product.specs ?? []" :key="spec.label" class="spec-row">
                                <dt class="spec-key">{{ spec.label }}</dt>
                                <dd class="spec-value">
                                    <ul v-if="spec.type === 'colors'" class="swatches">
                                        <li
                                            v-for="color in spec.values ?? []"
                                            :key="color"
                                            class="swatch"
                                            :style="{ backgroundColor: color }"
                                        >
                                            <span class="visually-hidden">{{ color }}</span>
                                        </li>
                                    </ul>
                                    <template v-else>{{ spec.value }}</template>
                                </dd>
                            </div>
                        </dl>
                    </template>
                </ProductTabs>

            </div>
        </section>

        <section v-if="related.length" class="section section-related" aria-labelledby="related-title">
            <div class="container">

                <div class="section-header section-header-row">
                    <h2 id="related-title">Productos relacionados</h2>
                    <NuxtLink class="section-link" :to="`${storePath}#products`">
                        Ver todo el catálogo
                        <AppIcon name="arrow" class="section-link-icon" />
                    </NuxtLink>
                </div>

                <ul class="grid grid-related">
                    <ProductCard
                        v-for="item in related"
                        :key="item.slug"
                        :product="item"
                        :store="store"
                    />
                </ul>

            </div>
        </section>

    </main>
</template>
