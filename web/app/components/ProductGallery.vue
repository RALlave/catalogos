<!--
    GALERÍA — cambio de foto por desvanecido.

    Progresiva: sin JS se ve la primera foto y las miniaturas siguen
    siendo enlaces a cada una (el CSS las muestra con :target). Cuando
    el componente monta pone `data-ready`, que apaga ese respaldo, y de
    ahí en más el estado lo lleva el script.
-->

<script setup lang="ts">
const props = defineProps<{ images: string[], name: string, featured: boolean, soldOut: boolean }>()

const current = ref(0)
const ready = ref(false)

/* Las flechas no hacen nada sin script, así que recién se muestran
   cuando el componente está montado en el navegador. */
onMounted(() => {
    ready.value = true
})

function show(index: number) {
    current.value = (index + props.images.length) % props.images.length
}

function onKeydown(event: KeyboardEvent) {
    if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') {
        return
    }

    event.preventDefault()
    show(current.value + (event.key === 'ArrowLeft' ? -1 : 1))
}
</script>

<template>
    <div class="gallery" :data-ready="ready ? '' : undefined" @keydown="onKeydown">

        <div class="gallery-viewer">
            <ul class="gallery-slides">
                <li
                    v-for="(image, index) in images"
                    :id="`photo-${index + 1}`"
                    :key="image"
                    class="gallery-slide"
                    :class="{ 'is-active': index === current }"
                >
                    <img
                        class="gallery-photo"
                        :src="image"
                        :alt="`${name} — foto ${index + 1}`"
                        width="900"
                        height="900"
                        :fetchpriority="index === 0 ? 'high' : undefined"
                        :loading="index === 0 ? undefined : 'lazy'"
                        decoding="async"
                    >
                </li>
            </ul>

            <p v-if="soldOut" class="badge badge-sold-out">
                <AppIcon name="sold-out" class="badge-icon" />
                Agotado
            </p>
            <p v-else-if="featured" class="badge badge-featured">
                <AppIcon name="star" class="badge-icon" />
                Destacado
            </p>

            <template v-if="ready && images.length > 1">
                <button class="gallery-arrow gallery-prev" type="button" @click="show(current - 1)">
                    <AppIcon name="chevron" class="gallery-arrow-icon" />
                    <span class="visually-hidden">Foto anterior</span>
                </button>
                <button class="gallery-arrow gallery-next" type="button" @click="show(current + 1)">
                    <AppIcon name="chevron" class="gallery-arrow-icon" />
                    <span class="visually-hidden">Foto siguiente</span>
                </button>
            </template>
        </div>

        <!-- Qué foto se está viendo, para quien no la ve -->
        <p class="visually-hidden" role="status">Foto {{ current + 1 }} de {{ images.length }}</p>

        <ul v-if="images.length > 1" class="gallery-thumbs">
            <li v-for="(image, index) in images" :key="image">
                <a
                    class="gallery-thumb"
                    :class="{ 'is-active': index === current }"
                    :href="`#photo-${index + 1}`"
                    :aria-current="index === current ? 'true' : undefined"
                    @click.prevent="show(index)"
                >
                    <img :src="image" :alt="`Ver foto ${index + 1}`" width="160" height="160" loading="lazy" decoding="async">
                </a>
            </li>
        </ul>

    </div>
</template>
