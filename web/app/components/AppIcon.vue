<!--
    Un ícono del sprite. La clase la pone quien lo usa, porque cada
    componente tiene la suya (.info-icon, .btn-icon, .badge-icon…).
-->

<script setup lang="ts">
defineProps<{ name: string }>()
</script>

<template>
    <svg aria-hidden="true" focusable="false">
        <use :href="`#i-${name}`" />
    </svg>
</template>
