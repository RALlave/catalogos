<!--
    Sin div contenedor a propósito: el <body> es `display: flex` en
    columna y la cabecera, el contenido y el pie tienen que ser hijos
    directos suyos para que el pie quede abajo.
-->

<script setup lang="ts">
useInputMode()

const { apiBase } = useRuntimeConfig().public

/* El favicon es el ícono de la plataforma, el mismo del panel: lo sube el
   superadmin y vale para todas las tiendas. Va contra una ruta fija porque el
   archivo cambia de nombre con cada subida; sin ícono cargado responde 404 y
   el navegador no dibuja ninguno. */
useHead({
    link: [{ rel: 'icon', type: 'image/webp', href: `${apiBase}/platform/icon/192` }],
})
</script>

<template>
    <IconSprite />

    <p class="skip-link"><a href="#content">Saltar al contenido</a></p>

    <NuxtLayout>
        <NuxtPage />
    </NuxtLayout>
</template>
