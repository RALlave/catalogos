<!--
    Botón del carrito en la franja superior. La burbuja con la cantidad
    sólo aparece cuando hay algo: un cero permanente es ruido.
-->

<script setup lang="ts">
import type { Store } from '~/types/catalog'

const props = defineProps<{ store: Store }>()

const open = useCartPanel()
const cart = useCart(toRef(props, 'store'))
</script>

<template>
    <button class="cart-toggle" type="button" :aria-expanded="open" @click="open = ! open">
        <AppIcon name="cart" class="cart-toggle-icon" />
        <span v-if="cart.count.value" class="cart-count">{{ cart.count.value }}</span>
        <span class="visually-hidden">
            Tu pedido{{ cart.count.value ? `: ${cart.count.value} productos` : ' está vacío' }}
        </span>
    </button>
</template>
