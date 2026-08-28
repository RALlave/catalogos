<script setup lang="ts">
import type { Store } from '~/types/catalog'

const route = useRoute()
const { siteUrl } = useRuntimeConfig().public

const { data, error } = await useCurrentStore()

if (error.value || ! data.value) {
    throw createError({
        statusCode: 404,
        statusMessage: 'No encontramos esta tienda.',
        fatal: true,
    })
}

const store = computed(() => data.value as Store)

/* La paleta va como estilo del <html>, no como un <style> con :root.
   Un bloque `:root` empata en especificidad con el de palette.css y
   pierde según el orden de carga —que en dev no controlamos, porque
   Vite inyecta las hojas después—; el estilo inline gana siempre. */
const paletteStyle = computed(() => Object.entries(store.value.theme.colors)
    .map(([name, value]) => `${name}:${value}`)
    .join(';'))

const canonical = computed(() => `${siteUrl}${route.path}`)

/* Las tres opciones de forma viajan como atributos del <html>, igual
   que en la maqueta: el CSS decide con ellos bordes, barra y velo. */
useHead({
    htmlAttrs: {
        'data-radius': () => store.value.theme.radius,
        'data-nav': () => store.value.theme.nav,
        'data-banner': () => store.value.theme.banner,
        style: paletteStyle,
    },
    link: [{ rel: 'canonical', href: canonical }],
})

useSeoMeta({ ogUrl: canonical })
</script>

<!--
    Sin contenedor: los tres bloques son hijos directos del <body>,
    que es el flex en columna que empuja el pie hacia abajo.
-->

<template>
    <TheHeader :store="store" />

    <slot />

    <TheFooter :store="store" />

    <!-- El carrito vive en el navegador: en el servidor estaría siempre
         vacío y la hidratación no coincidiría. -->
    <ClientOnly>
        <TheCart v-if="store.cart_enabled" :store="store" />
    </ClientOnly>
</template>
