<!--
    Esqueleto de un listado: se dibuja mientras llega la primera carga, en el
    lugar exacto que va a ocupar la tabla.
-->

<script setup>
defineProps({
    rows: { type: Number, default: 6 },
    columns: { type: Number, default: 4 },
})
</script>

<template>
    <div class="skeleton-table" role="status" aria-label="Cargando">
        <div v-for="row in rows" :key="row" class="skeleton-row">
            <span v-for="column in columns" :key="column" class="skeleton" />
        </div>
    </div>
</template>
