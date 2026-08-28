<!--
    CARRITO — panel lateral que entra desde la derecha.

    El estado vive en el navegador (`useCart`), así que el cliente vuelve
    días después y su pedido sigue ahí. Al enviar se registra el pedido en
    la tienda y recién entonces se abre WhatsApp con la lista escrita.

    Todo el componente se pinta dentro de <ClientOnly> desde el layout:
    en el servidor no hay `localStorage` y el carrito estaría siempre
    vacío, lo que rompería la hidratación.
-->

<script setup lang="ts">
import type { Store } from '~/types/catalog'

const props = defineProps<{ store: Store }>()

const open = useCartPanel()
const cart = useCart(toRef(props, 'store'))

const { apiBase, siteUrl } = useRuntimeConfig().public

const sending = ref(false)
const panel = ref<HTMLElement | null>(null)
const closeButton = ref<HTMLElement | null>(null)

/* Mientras el panel está abierto la página de atrás no se desplaza,
   igual que con el menú lateral. */
watch(open, (value) => {
    document.documentElement.classList.toggle('is-cart-open', value)

    if (value) {
        nextTick(() => closeButton.value?.focus())
    }
})

onBeforeUnmount(() => document.documentElement.classList.remove('is-cart-open'))

function onKeydown(event: KeyboardEvent) {
    if (event.key === 'Escape') {
        open.value = false

        return
    }

    /* Trampa de foco: con el velo puesto, Tab no puede irse a la página
       de atrás. Se recalcula en cada Tab porque la lista cambia. */
    if (event.key !== 'Tab' || ! panel.value) {
        return
    }

    const focusables = Array.from(
        panel.value.querySelectorAll<HTMLElement>('a[href], button, input, [tabindex]:not([tabindex="-1"])'),
    ).filter((element) => element.offsetParent !== null)

    if (! focusables.length) {
        return
    }

    const first = focusables[0]!
    const last = focusables.at(-1)!

    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault()
        last.focus()
    } else if (! event.shiftKey && document.activeElement === last) {
        event.preventDefault()
        first.focus()
    }
}

onMounted(() => {
    document.addEventListener('keydown', onKeydown)

    onBeforeUnmount(() => document.removeEventListener('keydown', onKeydown))
})

/* El mensaje lleva el enlace de cada producto: el dueño recibe la lista
   y puede abrir la ficha de cada uno sin buscarla. */
const message = computed(() => {
    const lines = cart.lines.value.map((line) => {
        const price = line.price ? ` — ${props.store.currency ?? ''} ${formatAmount(line.price)}`.trimEnd() : ''

        return `• ${line.quantity} × ${line.name}${price}\n  ${siteUrl}/${props.store.slug}/producto/${line.slug}`
    })

    const total = cart.total.value === null
        ? ''
        : `\n\nTotal: ${props.store.currency ?? ''} ${formatAmount(cart.total.value)}`.trimEnd()

    return `Hola ${props.store.name}, quiero pedir:\n\n${lines.join('\n')}${total}`
})

async function send() {
    if (! props.store.whatsapp || ! cart.lines.value.length) {
        return
    }

    sending.value = true

    /* La ventana se abre antes del await: si se abriera después, el
       navegador la trataría como emergente y la bloquearía. */
    const target = window.open('', '_blank')

    try {
        /* El pedido queda registrado para el dueño. Si la petición falla
           igual se manda el WhatsApp: perder la venta sería peor que
           perder el registro. */
        await $fetch(`${apiBase}/stores/${props.store.slug}/orders`, {
            method: 'POST',
            body: {
                items: cart.lines.value.map((line) => ({ slug: line.slug, quantity: line.quantity })),
            },
        })
    } catch {
        /* Sin conexión o con el carrito recién apagado: se sigue igual. */
    } finally {
        const url = whatsappUrl(props.store.whatsapp, message.value)

        if (target) {
            target.location.href = url
        } else {
            window.location.href = url
        }

        sending.value = false
        open.value = false
    }
}
</script>

<template>
    <div>
        <div class="cart-veil" @click="open = false" />

        <aside
            ref="panel"
            class="cart-panel"
            :aria-hidden="! open"
            aria-labelledby="cart-title"
        >
            <header class="cart-header">
                <h2 id="cart-title" class="cart-title">Tu pedido</h2>

                <button ref="closeButton" class="cart-close" type="button" @click="open = false">
                    <AppIcon name="close" class="cart-close-icon" />
                    <span class="visually-hidden">Cerrar el pedido</span>
                </button>
            </header>

            <div v-if="cart.lines.value.length" class="cart-list">
                <article v-for="line in cart.lines.value" :key="line.slug" class="cart-line">
                    <img v-if="line.image" class="cart-thumb" :src="line.image" :alt="line.name" width="56" height="56" loading="lazy">

                    <div class="cart-line-body">
                        <h3 class="cart-line-name">
                            <NuxtLink :to="`/${store.slug}/producto/${line.slug}`" @click="open = false">
                                {{ line.name }}
                            </NuxtLink>
                        </h3>

                        <div class="cart-line-foot">
                            <div class="cart-step">
                                <button
                                    class="cart-step-btn"
                                    type="button"
                                    @click="cart.setQuantity(line.slug, line.quantity - 1)"
                                >
                                    <AppIcon name="minus" class="cart-step-icon" />
                                    <span class="visually-hidden">Quitar uno de {{ line.name }}</span>
                                </button>

                                <span class="cart-step-value">{{ line.quantity }}</span>

                                <button
                                    class="cart-step-btn"
                                    type="button"
                                    @click="cart.setQuantity(line.slug, line.quantity + 1)"
                                >
                                    <AppIcon name="plus" class="cart-step-icon" />
                                    <span class="visually-hidden">Agregar uno de {{ line.name }}</span>
                                </button>
                            </div>

                            <span v-if="line.price" class="cart-line-price">
                                {{ store.currency }} {{ formatAmount(line.price) }}
                            </span>

                            <button class="cart-remove" type="button" @click="cart.remove(line.slug)">
                                <AppIcon name="trash" class="cart-remove-icon" />
                                <span class="visually-hidden">Sacar {{ line.name }} del pedido</span>
                            </button>
                        </div>
                    </div>
                </article>
            </div>

            <p v-else class="cart-empty">
                <AppIcon name="cart" class="cart-empty-icon" />
                Todavía no agregaste nada. Elegí un producto del catálogo y volvé acá.
            </p>

            <footer v-if="cart.lines.value.length" class="cart-footer">
                <p v-if="cart.total.value !== null" class="cart-total">
                    <span class="cart-total-key">Total</span>
                    <span class="cart-total-value">{{ store.currency }} {{ formatAmount(cart.total.value) }}</span>
                </p>

                <button
                    v-if="store.whatsapp"
                    class="btn btn-primary"
                    type="button"
                    :disabled="sending"
                    @click="send"
                >
                    <AppIcon name="whatsapp" class="btn-icon" />
                    {{ sending ? 'Abriendo WhatsApp…' : 'Pedir por WhatsApp' }}
                </button>

                <p class="cart-note">Se abre WhatsApp con tu pedido escrito. No se cobra nada acá.</p>

                <button class="cart-clear" type="button" @click="cart.clear()">Vaciar el pedido</button>
            </footer>
        </aside>
    </div>
</template>
