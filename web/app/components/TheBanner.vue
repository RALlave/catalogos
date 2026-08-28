<!--
    BANNER — carrusel de heros: foto de fondo + velo + llamado a la acción.

    Los heros se cargan desde el panel. El primero se renderiza en el
    servidor, así que el banner se ve completo sin JavaScript; las flechas
    y los puntos son <ClientOnly> porque sin JS no harían nada.

    Los dos botones son fijos: van siempre al catálogo y al WhatsApp de la
    tienda, no se editan por hero.
-->

<script setup lang="ts">
import type { Store } from '~/types/catalog'

const props = defineProps<{ store: Store }>()

/* Cada cuánto pasa solo al hero siguiente. */
const AUTOPLAY_MS = 6000

const heroes = computed(() => props.store.heroes ?? [])
const many = computed(() => heroes.value.length > 1)

const index = ref(0)
const playing = ref(true)

let timer: ReturnType<typeof setInterval> | null = null

function stop() {
    if (timer) {
        clearInterval(timer)
        timer = null
    }
}

function play() {
    stop()

    if (! many.value || ! playing.value) {
        return
    }

    timer = setInterval(() => {
        index.value = (index.value + 1) % heroes.value.length
    }, AUTOPLAY_MS)
}

/* Tocar las flechas o los puntos apaga el automático: manda el visitante. */
function go(to: number) {
    index.value = (to + heroes.value.length) % heroes.value.length
    playing.value = false

    stop()
}

onMounted(() => {
    /* Quien pidió menos movimiento no recibe un carrusel que se mueve solo. */
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        playing.value = false

        return
    }

    play()
})

onBeforeUnmount(stop)

const whatsapp = computed(() => props.store.whatsapp
    ? whatsappUrl(props.store.whatsapp, `Hola ${props.store.name}, quiero hacer un pedido.`)
    : null)
</script>

<template>
    <section
        v-if="heroes.length"
        class="banner"
        :data-effect="store.hero_effect"
        @mouseenter="stop"
        @mouseleave="play"
    >
        <article
            v-for="(hero, position) in heroes"
            :key="position"
            class="banner-slide"
            :class="{ 'is-active': position === index, 'is-before': position < index }"
            :aria-hidden="position !== index || undefined"
            :inert="position !== index || undefined"
        >
            <img
                v-if="hero.image_url"
                class="banner-photo"
                :src="hero.image_url"
                alt=""
                width="1024"
                height="411"
                :loading="position === 0 ? 'eager' : 'lazy'"
                :fetchpriority="position === 0 ? 'high' : 'auto'"
            >

            <div class="container banner-inner">
                <p v-if="hero.eyebrow" class="banner-eyebrow">{{ hero.eyebrow }}</p>

                <h1 v-if="position === 0" class="banner-title">{{ hero.title }}</h1>
                <p v-else class="banner-title">{{ hero.title }}</p>

                <p v-if="hero.text" class="banner-text">{{ hero.text }}</p>

                <ul class="banner-actions">
                    <li>
                        <a class="btn btn-cta" href="#products">
                            Ver catálogo
                            <AppIcon name="arrow" class="btn-icon" />
                        </a>
                    </li>
                    <li v-if="whatsapp">
                        <a class="btn btn-cta-border" :href="whatsapp" target="_blank" rel="noopener">
                            <AppIcon name="whatsapp" class="btn-icon" />
                            Pedir por WhatsApp
                        </a>
                    </li>
                </ul>
            </div>
        </article>

        <ClientOnly>
            <div v-if="many" class="banner-controls">
                <button
                    class="banner-arrow banner-arrow-prev"
                    type="button"
                    aria-label="Hero anterior"
                    @click="go(index - 1)"
                >
                    <AppIcon name="arrow" class="btn-icon" />
                </button>

                <button
                    class="banner-arrow banner-arrow-next"
                    type="button"
                    aria-label="Hero siguiente"
                    @click="go(index + 1)"
                >
                    <AppIcon name="arrow" class="btn-icon" />
                </button>

                <ul class="banner-dots">
                    <li v-for="(hero, position) in heroes" :key="position">
                        <button
                            class="banner-dot"
                            :class="{ 'is-active': position === index }"
                            type="button"
                            :aria-label="`Ver el hero ${position + 1}`"
                            :aria-current="position === index || undefined"
                            @click="go(position)"
                        />
                    </li>
                </ul>
            </div>
        </ClientOnly>
    </section>
</template>
