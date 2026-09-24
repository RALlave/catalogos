<!--
    CUALQUIER OTRA URL — lo que no es el home, un producto, la búsqueda o
    contacto manda al catálogo, sin pantalla de error de por medio.

    Temporal (302): la dirección no se mudó, nunca existió.
-->

<script setup lang="ts">
await navigateTo('/', { redirectCode: 302, replace: true })
</script>

<template>
    <div />
</template>
