<!--
    Botón de agregar al carrito. Cuando el producto ya está en el pedido
    lo dice y abre el panel, en vez de sumar otro sin avisar.

    Un producto agotado no se agrega: para eso está la lista de espera.
-->

<script setup lang="ts">
import type { Product, Store } from '~/types/catalog'

const props = withDefaults(
    defineProps<{ product: Product, store: Store, variant?: string }>(),
    { variant: 'btn-primary' },
)

const open = useCartPanel()
const cart = useCart(toRef(props, 'store'))

const added = computed(() => cart.has(props.product.slug))

function click() {
    if (added.value) {
        open.value = true

        return
    }

    cart.add(props.product)
    open.value = true
}
</script>

<template>
    <button class="btn" :class="variant" type="button" @click="click">
        <AppIcon :name="added ? 'check' : 'cart'" class="btn-icon" />
        {{ added ? 'Ya está en tu pedido' : 'Agregar al pedido' }}
    </button>
</template>
