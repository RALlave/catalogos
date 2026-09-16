<script setup lang="ts">
/* Scroll distance, in px, after which the button shows up. */
const THRESHOLD = 400

const visible = ref(false)

function onScroll() {
    visible.value = window.scrollY > THRESHOLD
}

function scrollToTop() {
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches

    window.scrollTo({ top: 0, behavior: reduced ? 'auto' : 'smooth' })
}

onMounted(() => {
    onScroll()
    window.addEventListener('scroll', onScroll, { passive: true })
})

onBeforeUnmount(() => {
    window.removeEventListener('scroll', onScroll)
})
</script>

<template>
    <button
        class="back-to-top"
        :class="{ 'is-visible': visible }"
        type="button"
        title="Volver arriba"
        aria-label="Volver arriba"
        @click="scrollToTop"
    >
        <AppIcon name="chevron" class="back-to-top-icon" />
    </button>
</template>
