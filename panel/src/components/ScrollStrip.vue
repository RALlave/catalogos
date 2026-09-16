<!--
    Horizontal strip that slides with the finger when its content does not fit.
    A gradient on each side hints that there is more hidden in that direction,
    and disappears once that end is reached.
-->

<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'

const track = ref(null)
const fadeStart = ref(false)
const fadeEnd = ref(false)

/* One pixel of tolerance: zoom leaves scroll positions with decimals. */
function update() {
    const el = track.value

    if (! el) {
        return
    }

    fadeStart.value = el.scrollLeft > 1
    fadeEnd.value = el.scrollLeft + el.clientWidth < el.scrollWidth - 1
}

let observer = null

onMounted(() => {
    update()

    observer = new ResizeObserver(update)
    observer.observe(track.value)

    if (track.value.firstElementChild) {
        observer.observe(track.value.firstElementChild)
    }
})

onBeforeUnmount(() => {
    observer?.disconnect()
})
</script>

<template>
    <div class="scroll-strip" :class="{ 'is-fade-start': fadeStart, 'is-fade-end': fadeEnd }">
        <div ref="track" class="scroll-strip-track" @scroll.passive="update">
            <slot />
        </div>
    </div>
</template>
