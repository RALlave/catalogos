<!--
    Esqueleto de un formulario: la etiqueta y el campo de cada fila, mientras
    llegan los datos que hay que editar.
-->

<script setup>
defineProps({
    fields: { type: Number, default: 4 },
})
</script>

<template>
    <div class="skeleton-form" role="status" aria-label="Cargando">
        <div v-for="field in fields" :key="field" class="skeleton-field">
            <span class="skeleton skeleton-label" />
            <span class="skeleton skeleton-input" />
        </div>
    </div>
</template>
