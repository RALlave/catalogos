<!--
    PANTALLA DE ERROR — en la práctica se ve con la tienda caída o
    inexistente: las URLs que no existen dentro de una tienda redirigen
    al catálogo sin pasar por acá.
-->

<script setup lang="ts">
import type { NuxtError } from '#app'

defineProps<{ error: NuxtError }>()
</script>

<template>
    <main id="content" class="content">
        <section class="section">
            <div class="container">
                <h1>{{ error.statusCode }}</h1>

                <p class="empty">
                    <AppIcon name="search" class="empty-icon" />
                    {{ error.statusMessage }}
                </p>
            </div>
        </section>
    </main>
</template>
