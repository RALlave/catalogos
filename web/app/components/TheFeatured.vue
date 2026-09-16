<!--
    Vitrina de destacados del home: un producto grande manda y los otros
    cuatro se leen como lista al costado.

    Los productos salen del endpoint `featured`, que devuelve los marcados
    como destacados primero y completa con el resto del catálogo: la vitrina
    nunca queda coja. Las tarjetas chicas no llevan ficha técnica ni badges:
    a ese ancho no se leen.
-->

<script setup lang="ts">
import type { Product, Store } from '~/types/catalog'

const props = defineProps<{ products: Product[], store: Store }>()

/* El grande ocupa 1.35fr del contenedor desde 768px y todo el ancho abajo. */
const LEAD_SIZES = '(min-width: 90rem) 660px, (min-width: 48rem) 55vw, 100vw'

/* Las chicas tienen la foto a un ancho fijo: 7.5rem, y 8rem desde 992px. */
const MINI_SIZES = '128px'

const lead = computed(() => props.products[0])
const rest = computed(() => props.products.slice(1, 5))

function path(product: Product): string {
    return `/producto/${product.slug}`
}

function price(product: Product): string | null {
    return product.sale_price ?? product.price
}
</script>

<template>
    <section id="featured" class="section section-featured" aria-labelledby="featured-title">
        <div class="container">

            <div class="section-header section-header-center">
                <h2 id="featured-title">
                    <span v-if="store.featured_title" class="h2 headline-lead">{{ store.featured_title }}</span>
                    <span v-if="store.featured_subtitle" class="h3 headline-main">{{ store.featured_subtitle }}</span>
                </h2>
            </div>

            <div class="showcase">
                <article class="product product-lead">
                    <div class="product-media">
                        <img
                            v-if="lead.images.length"
                            class="product-photo"
                            :src="lead.images[0].src"
                            :srcset="lead.images[0].srcset"
                            :sizes="LEAD_SIZES"
                            :alt="lead.name"
                            width="600"
                            height="600"
                            loading="lazy"
                            decoding="async"
                        >
                    </div>

                    <div class="product-body">
                        <p v-if="lead.category" class="product-eyebrow">{{ lead.category.name }}</p>

                        <h3 class="product-name">
                            <NuxtLink class="product-link" :to="path(lead)">{{ lead.name }}</NuxtLink>
                        </h3>

                        <p v-if="lead.description_text" class="product-excerpt">{{ lead.description_text }}</p>

                        <p v-if="price(lead)" class="price">
                            <span v-if="store.currency" class="price-currency">{{ store.currency }}</span>
                            <strong class="price-value">{{ formatAmount(price(lead)) }}</strong>
                            <s v-if="lead.sale_price && lead.price" class="price-before">
                                <span class="visually-hidden">Precio anterior:</span>
                                {{ store.currency }} {{ formatAmount(lead.price) }}
                            </s>
                        </p>
                    </div>
                </article>

                <ul v-if="rest.length" class="showcase-list">
                    <li v-for="product in rest" :key="product.slug">
                        <article class="product product-mini">
                            <div class="product-media">
                                <img
                                    v-if="product.images.length"
                                    class="product-photo"
                                    :src="product.images[0].thumb"
                                    :srcset="product.images[0].srcset"
                                    :sizes="MINI_SIZES"
                                    :alt="product.name"
                                    width="600"
                                    height="600"
                                    loading="lazy"
                                    decoding="async"
                                >
                            </div>

                            <div class="product-body">
                                <h3 class="product-name">
                                    <NuxtLink class="product-link" :to="path(product)">{{ product.name }}</NuxtLink>
                                </h3>

                                <p v-if="product.description_text" class="product-excerpt">{{ product.description_text }}</p>

                                <p v-if="price(product)" class="price">
                                    <span v-if="store.currency" class="price-currency">{{ store.currency }}</span>
                                    <strong class="price-value">{{ formatAmount(price(product)) }}</strong>
                                </p>
                            </div>
                        </article>
                    </li>
                </ul>
            </div>

        </div>
    </section>
</template>
