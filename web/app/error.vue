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
