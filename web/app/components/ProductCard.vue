<!--
    Tarjeta de producto de la grilla: foto con badge, nombre, ficha
    técnica y precio pegado al pie.

    Un solo badge por tarjeta y en este orden: agotado gana sobre
    destacado. El color nunca es la única señal — los tres llevan
    ícono y texto, y "agotado" además apaga la foto desde el CSS.
-->

<script setup lang="ts">
import type { Product, Store } from '~/types/catalog'

const props = defineProps<{ product: Product, store: Store }>()

const productPath = computed(() => `/${props.store.slug}/producto/${props.product.slug}`)

const price = computed(() => props.product.sale_price ?? props.product.price)
</script>

<template>
    <li class="grid-item">
        <article class="product" :data-sold-out="product.sold_out ? '' : undefined">
            <div class="product-media">
                <img
                    v-if="product.images.length"
                    class="product-photo"
                    :src="product.images[0]"
                    :alt="product.name"
                    width="600"
                    height="600"
                    loading="lazy"
                    decoding="async"
                >

                <p v-if="product.sold_out" class="badge badge-sold-out">
                    <AppIcon name="sold-out" class="badge-icon" />
                    Agotado
                </p>
                <p v-else-if="product.featured" class="badge badge-featured">
                    <AppIcon name="star" class="badge-icon" />
                    Destacado
                </p>
            </div>

            <div class="product-body">
                <h3 class="product-name">
                    <NuxtLink class="product-link" :to="productPath">{{ product.name }}</NuxtLink>
                </h3>

                <dl v-if="product.specs?.length" class="spec">
                    <div v-for="spec in product.specs" :key="spec.label" class="spec-row">
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

                <!-- Un solo precio en la tarjeta: el anterior tachado y el
                     porcentaje son de la ficha, donde hay lugar para leerlos -->
                <p v-if="price" class="price">
                    <span v-if="store.currency" class="price-currency">{{ store.currency }}</span>
                    <strong class="price-value">{{ formatAmount(price) }}</strong>
                </p>

                <ClientOnly>
                    <AddToCartButton
                        v-if="store.cart_enabled && ! product.sold_out"
                        :product="product"
                        :store="store"
                        variant="btn-border btn-sm"
                    />
                </ClientOnly>
            </div>
        </article>
    </li>
</template>
