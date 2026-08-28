<!--
    LISTA DE ESPERA — sólo aparece en un producto agotado.

    Los maxlength coinciden con el tamaño de las columnas de
    `waitlist_entries` y con las reglas del form request: si cambia uno,
    cambian los tres.
-->

<script setup lang="ts">
import type { Product, Store } from '~/types/catalog'

const props = defineProps<{ product: Product, store: Store }>()

const { apiBase } = useRuntimeConfig().public

const name = ref('')
const phone = ref('')
const sending = ref(false)
const done = ref(false)
const error = ref('')

async function submit() {
    sending.value = true
    error.value = ''

    try {
        await $fetch(`${apiBase}/stores/${props.store.slug}/waitlist`, {
            method: 'POST',
            body: {
                product_slug: props.product.slug,
                name: name.value,
                phone: phone.value,
            },
        })

        done.value = true
    } catch {
        error.value = 'No pudimos anotarte. Probá de nuevo en un momento.'
    } finally {
        sending.value = false
    }
}
</script>

<template>
    <section class="waitlist" aria-labelledby="waitlist-title">
        <div class="waitlist-header">
            <AppIcon name="bell" class="waitlist-icon" />
            <div>
                <h2 id="waitlist-title" class="waitlist-title">Avisame cuando vuelva</h2>
                <p class="waitlist-text">Dejanos tu WhatsApp y te escribimos apenas esté disponible.</p>
            </div>
        </div>

        <p v-if="done" class="waitlist-message">
            <AppIcon name="check" class="waitlist-message-icon" />
            Listo, te avisamos por WhatsApp cuando vuelva a estar disponible.
        </p>

        <form v-else class="waitlist-fields" @submit.prevent="submit">
            <div class="waitlist-field">
                <label class="waitlist-label" for="waitlist-name">Nombre</label>
                <input
                    id="waitlist-name"
                    v-model="name"
                    class="waitlist-input"
                    type="text"
                    name="name"
                    maxlength="120"
                    autocomplete="name"
                    required
                >
            </div>

            <div class="waitlist-field">
                <label class="waitlist-label" for="waitlist-phone">WhatsApp</label>
                <input
                    id="waitlist-phone"
                    v-model="phone"
                    class="waitlist-input"
                    type="tel"
                    name="phone"
                    maxlength="30"
                    autocomplete="tel"
                    required
                >
            </div>

            <p v-if="error" class="waitlist-message">{{ error }}</p>

            <button class="btn btn-border" type="submit" :disabled="sending">
                <AppIcon name="bell" class="btn-icon" />
                {{ sending ? 'Anotando…' : 'Avisame' }}
            </button>
        </form>
    </section>
</template>
