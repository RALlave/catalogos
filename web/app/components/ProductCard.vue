<!--
    Tarjeta de producto de la grilla: foto con badge, nombre, un
    resumen de la descripción y, al pie, el precio con el botón de
    agregar. La ficha técnica no entra acá: vive en el detalle.

    Un solo distintivo por tarjeta y en este orden: agotado, oferta,
    nuevo. "Destacado" ya no se dibuja —el campo sigue vivo y ordena
    el catálogo, pero no se muestra. El color nunca es la única señal:
    "agotado" lleva ícono y texto y además apaga la foto desde el CSS,
    y las cintas de la esquina llevan su texto.
-->

<script setup lang="ts">
import type { Product, Store } from '~/types/catalog'

const props = defineProps<{ product: Product, store: Store }>()

/* Cuánto ocupa la tarjeta en cada corte, para que el navegador baje la
   variante justa. Sigue a `--grid-columns` de base.css: 2, 3 y 4
   columnas, y arriba de 1440px el contenedor deja de crecer. */
const CARD_SIZES = '(min-width: 90rem) 320px, (min-width: 62rem) 33vw, 50vw'

const productPath = computed(() => `/${props.store.slug}/producto/${props.product.slug}`)

const price = computed(() => props.product.sale_price ?? props.product.price)

/* El anterior sólo si hay oferta. La API ya garantiza que sale_price es
   menor que price, pero sin price no hay nada que tachar. */
const priceBefore = computed(() => props.product.sale_price ? props.product.price : null)

const discount = computed(() => discountPercent(props.product.price, props.product.sale_price))
</script>

<template>
    <li class="grid-item">
        <article class="product" :data-sold-out="product.sold_out ? '' : undefined">
            <div class="product-media">
                <img
                    v-if="product.images.length"
                    class="product-photo"
                    :src="product.images[0].src"
                    :srcset="product.images[0].srcset"
                    :sizes="CARD_SIZES"
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
                <!-- Las cintas no llevan ícono: en la diagonal no entra.
                     La señal son el texto y la forma, no sólo el color -->
                <p v-else-if="discount" class="badge badge-ribbon badge-sale">
                    <span class="visually-hidden">Oferta:</span>
                    -{{ discount }}&nbsp;%
                </p>
                <p v-else-if="product.is_new" class="badge badge-ribbon badge-new">
                    Nuevo
                </p>
            </div>

            <div class="product-body">
                <h3 class="product-name">
                    <NuxtLink class="product-link" :to="productPath">{{ product.name }}</NuxtLink>
                </h3>

                <p v-if="product.description" class="product-excerpt">{{ product.description }}</p>

                <div class="product-foot">
                    <!-- El precio actual y, si es oferta, el anterior tachado.
                         El badge del porcentaje no: ese es de la ficha, donde
                         hay lugar para leerlo -->
                    <p v-if="price" class="price">
                        <span v-if="store.currency" class="price-currency">{{ store.currency }}</span>
                        <strong class="price-value">{{ formatAmount(price) }}</strong>
                        <s v-if="priceBefore" class="price-before">
                            <span class="visually-hidden">Precio anterior:</span>
                            {{ store.currency }} {{ formatAmount(priceBefore) }}
                        </s>
                    </p>

                    <!-- Con carrito, el botón de agregar; sin carrito (o
                         agotado) el mismo lugar lo ocupa el acceso a la
                         ficha, así la fila no queda coja -->
                    <ClientOnly v-if="store.cart_enabled && ! product.sold_out">
                        <AddToCartButton
                            :product="product"
                            :store="store"
                            variant="btn-border btn-icon-only"
                            icon-only
                        />
                    </ClientOnly>

                    <NuxtLink
                        v-else
                        class="btn btn-border btn-icon-only"
                        :to="productPath"
                        :aria-label="`Ver ${product.name}`"
                    >
                        <AppIcon name="arrow" class="btn-icon" />
                    </NuxtLink>
                </div>
            </div>
        </article>
    </li>
</template>
