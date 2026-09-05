<!--
    Botón de agregar al carrito. Cuando el producto ya está en el pedido
    lo dice y abre el panel, en vez de sumar otro sin avisar.

    Un producto agotado no se agrega: para eso está la lista de espera.

    Con `icon-only` queda sólo el ícono (la tarjeta de la grilla): el
    texto no desaparece, pasa a `aria-label`.
-->

<script setup lang="ts">
import type { Product, Store } from '~/types/catalog'

const props = withDefaults(
    defineProps<{ product: Product, store: Store, variant?: string, iconOnly?: boolean }>(),
    { variant: 'btn-primary', iconOnly: false },
)

const open = useCartPanel()
const cart = useCart(toRef(props, 'store'))

const added = computed(() => cart.has(props.product.slug))

const label = computed(() => added.value ? 'En tu pedido' : 'Agregar al pedido')

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
    <button
        class="btn"
        :class="variant"
        type="button"
        :aria-label="iconOnly ? label : undefined"
        @click="click"
    >
        <AppIcon :name="added ? 'check' : 'cart'" class="btn-icon" />
        <template v-if="! iconOnly">{{ label }}</template>
    </button>
</template>
