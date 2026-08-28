<!--
    PESTAÑAS — "Más sobre este producto".

    No hay elemento nativo para esto: acá ARIA es la única salida. Sin
    JS la lista es un índice de enlaces y los paneles se ven enteros con
    su <h3>, así que nunca queda contenido escondido y sin forma de
    abrirlo. Con una sola pestaña tampoco se activa el modo tablist.
-->

<script setup lang="ts">
interface Tab {
    id: string
    label: string
}

const props = defineProps<{ tabs: Tab[] }>()

const current = ref(0)
const ready = ref(false)

onMounted(() => {
    ready.value = props.tabs.length > 1
})

const isTabs = computed(() => ready.value && props.tabs.length > 1)

function select(index: number, move: boolean) {
    current.value = (index + props.tabs.length) % props.tabs.length

    /* Tabulador roving: al grupo se entra y se sale de una sola vez;
       entre las pestañas se navega con las flechas. */
    if (move) {
        nextTick(() => document.getElementById(`tab-${props.tabs[current.value]!.id}`)?.focus())
    }
}

function onKeydown(event: KeyboardEvent) {
    const step = { ArrowLeft: -1, ArrowRight: 1 }[event.key]

    if (step) {
        event.preventDefault()
        select(current.value + step, true)
    } else if (event.key === 'Home') {
        event.preventDefault()
        select(0, true)
    } else if (event.key === 'End') {
        event.preventDefault()
        select(props.tabs.length - 1, true)
    }
}
</script>

<template>
    <div class="tabs" :data-ready="isTabs ? '' : undefined">

        <ul
            class="tabs-list"
            :role="isTabs ? 'tablist' : undefined"
            aria-labelledby="more-title"
            @keydown="onKeydown"
        >
            <li
                v-for="(tab, index) in tabs"
                :key="tab.id"
                class="tabs-item"
                :role="isTabs ? 'presentation' : undefined"
            >
                <a
                    :id="`tab-${tab.id}`"
                    class="tab"
                    :href="`#panel-${tab.id}`"
                    :role="isTabs ? 'tab' : undefined"
                    :aria-controls="isTabs ? `panel-${tab.id}` : undefined"
                    :aria-selected="isTabs ? String(index === current) : undefined"
                    :tabindex="isTabs && index !== current ? -1 : undefined"
                    @click.prevent="select(index, false)"
                >
                    {{ tab.label }}
                </a>
            </li>
        </ul>

        <div
            v-for="(tab, index) in tabs"
            :id="`panel-${tab.id}`"
            :key="tab.id"
            class="tabs-panel"
            :role="isTabs ? 'tabpanel' : undefined"
            :aria-labelledby="isTabs ? `tab-${tab.id}` : undefined"
            :tabindex="isTabs ? 0 : undefined"
            :hidden="isTabs && index !== current"
        >
            <h3 class="tabs-panel-title">{{ tab.label }}</h3>
            <slot :name="tab.id" />
        </div>

    </div>
</template>
