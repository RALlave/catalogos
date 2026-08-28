<!--
    Sin div contenedor a propósito: el <body> es `display: flex` en
    columna y la cabecera, el contenido y el pie tienen que ser hijos
    directos suyos para que el pie quede abajo.
-->

<script setup lang="ts">
useInputMode()
</script>

<template>
    <IconSprite />

    <p class="skip-link"><a href="#content">Saltar al contenido</a></p>

    <NuxtLayout>
        <NuxtPage />
    </NuxtLayout>
</template>
