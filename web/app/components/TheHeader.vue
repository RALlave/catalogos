<!--
    CABECERA — franja superior con los datos de atención y barra de
    navegación. Debajo de 992px el menú es un panel lateral que entra
    desde el borde izquierdo con un velo detrás.

    Todo lo interactivo es progresivo: sin JS los enlaces del menú y el
    formulario de búsqueda siguen sirviendo.
-->

<script setup lang="ts">
import type { Store } from '~/types/catalog'

const props = defineProps<{ store: Store }>()

const route = useRoute()

const storePath = computed(() => `/${props.store.slug}`)

/* El nombre se parte para pintar la última palabra con el color de
   acento. Con una sola palabra no hay segunda mitad y no se pinta. */
const brandWords = computed(() => props.store.name.trim().split(/\s+/))
const brandName = computed(() => brandWords.value.slice(0, -1).join(' '))
const brandAlt = computed(() => brandWords.value.length > 1 ? brandWords.value.at(-1) : props.store.name)

const schedule = computed(() => props.store.schedules?.[0] ?? null)
const scheduleRest = computed(() => props.store.schedules?.slice(1) ?? [])

const menuOpen = ref(false)
const searchOpen = ref(false)
const categoriesOpen = ref(false)

/* La cabecera se retira al bajar y vuelve al subir. Sólo en móvil: es
   donde ocupa una porción real de la pantalla. */
const headerHidden = ref(false)

/* Cuánto hay que mover el dedo para que cuente como un gesto: sin esto
   el temblor de un scroll suave la haría titilar. */
const SCROLL_STEP = 8

/* Arriba del todo nunca se esconde: los primeros píxeles son justamente
   donde el visitante espera ver la marca. */
const SCROLL_FLOOR = 120

const search = ref(String(route.query.q ?? ''))

const navPanel = ref<HTMLElement | null>(null)
const panelClose = ref<HTMLElement | null>(null)
const navToggle = ref<HTMLElement | null>(null)
const searchToggle = ref<HTMLElement | null>(null)
const searchField = ref<HTMLInputElement | null>(null)

/* El panel tapa la pantalla entera: la página de atrás no se desplaza
   mientras está abierto. La clase vive en <html>, como en la maqueta. */
watch(menuOpen, (open) => {
    document.documentElement.classList.toggle('is-menu-open', open)

    /* El panel arranca desde el borde de la cabecera: retirada, el ✕ y
       el buscador nacerían fuera de la pantalla. */
    if (open) {
        headerHidden.value = false
    }
})

onBeforeUnmount(() => document.documentElement.classList.remove('is-menu-open'))

/* Al cambiar de página el panel tiene que irse: si el destino es un
   ancla de la misma página no hay recarga que lo cierre por su cuenta. */
watch(() => route.fullPath, () => {
    menuOpen.value = false
    categoriesOpen.value = false
})

function toggleMenu() {
    menuOpen.value = ! menuOpen.value

    /* El foco entra al ✕ y no al primer enlace: así el primer Tab
       recorre el menú de arriba abajo, y Enter cierra sin buscar nada. */
    if (menuOpen.value) {
        nextTick(() => panelClose.value?.focus())
    }
}

function closeMenuAndReturn() {
    menuOpen.value = false
    navToggle.value?.focus()
}

/* Trampa de foco: mientras el panel está abierto, Tab no puede salirse
   hacia la página de atrás, que está tapada por el velo. Se calcula en
   cada Tab y no una sola vez: el submenú agrega y saca enlaces. */
function trapFocus(event: KeyboardEvent) {
    if (event.key !== 'Tab' || ! menuOpen.value || ! navPanel.value) {
        return
    }

    const focusables = Array.from(
        navPanel.value.querySelectorAll<HTMLElement>('a[href], button, input, [tabindex]:not([tabindex="-1"])'),
    ).filter((element) => element.offsetParent !== null)

    if (! focusables.length) {
        return
    }

    const first = focusables[0]!
    const last = focusables.at(-1)!

    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault()
        last.focus()
    } else if (! event.shiftKey && document.activeElement === last) {
        event.preventDefault()
        first.focus()
    }
}

function toggleSearch() {
    searchOpen.value = ! searchOpen.value

    if (searchOpen.value) {
        nextTick(() => searchField.value?.focus())
    }
}

/* Al perder el foco el buscador vuelve a su posición. `relatedTarget`
   dice a dónde se fue: si sigue adentro (del campo al botón), no cierra. */
function onSearchBlur(event: FocusEvent) {
    const box = event.currentTarget as HTMLElement

    if (! box.contains(event.relatedTarget as Node)) {
        searchOpen.value = false
    }
}

/* Buscar es una página aparte, no un filtro del catálogo: se sale con el
   término solo, sin arrastrar la categoría ni la página que hubiera. */
const searchPath = computed(() => `${storePath.value}/buscar`)

function submitSearch() {
    searchOpen.value = false
    menuOpen.value = false

    navigateTo({ path: searchPath.value, query: { q: search.value.trim() || undefined } })
}

function onEscape(event: KeyboardEvent) {
    if (event.key !== 'Escape') {
        return
    }

    if (searchOpen.value) {
        searchOpen.value = false
        searchToggle.value?.focus()
    } else if (categoriesOpen.value) {
        categoriesOpen.value = false
    } else if (menuOpen.value) {
        closeMenuAndReturn()
    }
}

/* El menú de móvil no puede quedar "abierto" cuando la barra vuelve a
   ser horizontal. 62rem es el mismo corte que usa la hoja de estilos. */
onMounted(() => {
    document.addEventListener('keydown', onEscape)

    const desktop = window.matchMedia('(min-width: 62rem)')
    const sync = (query: MediaQueryList | MediaQueryListEvent) => {
        if (query.matches) {
            menuOpen.value = false
        }

        categoriesOpen.value = false
    }

    desktop.addEventListener('change', sync)

    /* Quien pidió menos movimiento no tiene por qué ver la cabecera
       entrando y saliendo: para esa gente se queda donde está. */
    const still = window.matchMedia('(prefers-reduced-motion: reduce)')

    let last = window.scrollY
    let waiting = false

    const onScroll = () => {
        if (waiting) {
            return
        }

        waiting = true

        /* La decisión se toma en el cuadro siguiente: el evento llega
           muchas veces por gesto y leer scrollY fuerza un reflow. */
        requestAnimationFrame(() => {
            waiting = false

            const top = Math.max(0, window.scrollY)
            const step = top - last

            if (Math.abs(step) < SCROLL_STEP) {
                return
            }

            last = top

            if (still.matches || menuOpen.value || desktop.matches) {
                headerHidden.value = false

                return
            }

            headerHidden.value = step > 0 && top > SCROLL_FLOOR
        })
    }

    window.addEventListener('scroll', onScroll, { passive: true })

    onBeforeUnmount(() => {
        document.removeEventListener('keydown', onEscape)
        desktop.removeEventListener('change', sync)
        window.removeEventListener('scroll', onScroll)
    })
})
</script>

<template>
    <header class="header" :class="{ 'is-hidden': headerHidden }">

        <div class="header-top">
            <div class="container header-top-inner">

                <NuxtLink class="brand" :to="storePath">
                    <img
                        v-if="store.logo_url"
                        class="brand-logo"
                        :src="store.logo_url"
                        alt=""
                        width="405"
                        height="238"
                        decoding="async"
                    >
                    <span class="brand-name">
                        {{ brandName }}
                        <span class="brand-name-alt">{{ brandAlt }}</span>
                    </span>
                </NuxtLink>

                <ul class="info-list">
                    <li v-if="schedule" class="info">
                        <AppIcon name="clock" class="info-icon" />
                        <span class="info-body">
                            <strong class="info-title">{{ schedule.days }}: {{ schedule.hours }}</strong>
                            <span v-if="scheduleRest.length" class="info-detail">
                                {{ scheduleRest[0]?.days }}: {{ scheduleRest[0]?.hours }}
                            </span>
                        </span>
                    </li>
                    <li v-if="store.address || store.city" class="info">
                        <AppIcon name="location" class="info-icon" />
                        <address class="info-body">
                            <span v-if="store.address" class="info-title">{{ store.address }}</span>
                            <span v-if="store.city" class="info-detail">{{ store.city }}</span>
                        </address>
                    </li>
                    <li v-if="store.phone || store.email" class="info">
                        <AppIcon name="phone" class="info-icon" />
                        <span class="info-body">
                            <a v-if="store.phone" class="info-title" :href="`tel:${store.phone}`">{{ store.phone }}</a>
                            <a v-if="store.email" class="info-detail" :href="`mailto:${store.email}`">{{ store.email }}</a>
                        </span>
                    </li>
                </ul>

                <!-- Va después de la lista de datos: `.info-list` desaparece
                     en móvil, así que este bloque queda pegado a la derecha
                     en los dos tamaños sin tocar el orden. -->
                <div class="header-actions">
                    <ClientOnly>
                        <CartToggle v-if="store.cart_enabled" :store="store" />
                    </ClientOnly>

                    <button
                        ref="navToggle"
                        class="nav-toggle"
                        type="button"
                        :aria-expanded="menuOpen"
                        aria-controls="menu-main"
                        @click="toggleMenu"
                    >
                        <AppIcon :name="menuOpen ? 'close' : 'menu'" class="nav-toggle-icon" />
                        <span class="visually-hidden">Menú</span>
                    </button>
                </div>

            </div>
        </div>

        <nav class="nav" :class="{ 'is-open': menuOpen, 'search-open': searchOpen }" aria-label="Principal">
            <div class="container nav-inner">

                <!-- Panel lateral de móvil y tablet: adentro van el buscador
                     y el menú, en ese orden. En escritorio el div se disuelve
                     (display: contents) y los dos vuelven a la barra. -->
                <div ref="navPanel" class="nav-panel" @keydown="trapFocus">

                    <button ref="panelClose" class="nav-panel-close" type="button" @click="closeMenuAndReturn">
                        <AppIcon name="close" class="nav-panel-close-icon" />
                        <span class="visually-hidden">Cerrar menú</span>
                    </button>

                    <div class="nav-search" @focusout="onSearchBlur">
                        <button
                            ref="searchToggle"
                            class="search-toggle"
                            type="button"
                            :aria-expanded="searchOpen"
                            aria-controls="form-search"
                            @click="toggleSearch"
                        >
                            <AppIcon :name="searchOpen ? 'close' : 'search'" class="search-toggle-icon" />
                            <span class="visually-hidden">Buscar productos</span>
                        </button>

                        <!-- `action` y `method` son la versión sin JS: el
                             navegador arma solo `/tienda/buscar?q=…`. -->
                        <form
                            id="form-search"
                            class="search"
                            role="search"
                            method="get"
                            :action="searchPath"
                            @submit.prevent="submitSearch"
                        >
                            <label class="visually-hidden" for="q">Buscar productos</label>
                            <input
                                id="q"
                                ref="searchField"
                                v-model="search"
                                class="search-field"
                                type="search"
                                name="q"
                                placeholder="Buscar productos…"
                                autocomplete="off"
                                maxlength="120"
                                @click="($event.target as HTMLInputElement).select()"
                            >
                            <button class="search-submit" type="submit">
                                <AppIcon name="search" class="search-submit-icon" />
                                <span class="visually-hidden">Buscar</span>
                            </button>
                        </form>
                    </div>

                    <ul id="menu-main" class="menu">
                        <li class="menu-item">
                            <NuxtLink class="menu-link" :to="storePath">Inicio</NuxtLink>
                        </li>
                        <li v-if="store.categories.length" class="menu-item">
                            <button
                                class="menu-link menu-btn"
                                type="button"
                                :aria-expanded="categoriesOpen"
                                aria-controls="submenu-categories"
                                @click="categoriesOpen = ! categoriesOpen"
                            >
                                Categorías
                                <AppIcon name="chevron" class="menu-chevron" />
                            </button>
                            <ul id="submenu-categories" class="submenu" :hidden="! categoriesOpen">
                                <li v-for="category in store.categories" :key="category.slug">
                                    <NuxtLink :to="{ path: storePath, query: { cat: category.slug } }">
                                        {{ category.name }}
                                    </NuxtLink>
                                </li>
                            </ul>
                        </li>
                        <li class="menu-item">
                            <NuxtLink class="menu-link" :to="`${storePath}/contacto`">Contacto</NuxtLink>
                        </li>
                    </ul>

                    <!-- Horario y dirección: a este ancho no están en la franja
                         superior, y el panel es el único lugar donde se ven sin
                         bajar hasta el pie -->
                    <ul class="nav-info-list">
                        <li v-if="schedule" class="info">
                            <AppIcon name="clock" class="info-icon" />
                            <span class="info-body">
                                <strong class="info-title">{{ schedule.days }}: {{ schedule.hours }}</strong>
                                <span v-if="scheduleRest.length" class="info-detail">
                                    {{ scheduleRest[0]?.days }}: {{ scheduleRest[0]?.hours }}
                                </span>
                            </span>
                        </li>
                        <li v-if="store.address || store.city" class="info">
                            <AppIcon name="location" class="info-icon" />
                            <address class="info-body">
                                <span v-if="store.address" class="info-title">{{ store.address }}</span>
                                <span v-if="store.city" class="info-detail">{{ store.city }}</span>
                            </address>
                        </li>
                    </ul>

                </div>

                <!-- Va fuera del panel a propósito: adentro se lo llevaría el
                     menú lateral en móvil. Acá queda a la derecha de la lupa
                     en escritorio, que es el único tamaño donde se muestra;
                     el de la franja superior atiende al resto. -->
                <ClientOnly>
                    <CartToggle v-if="store.cart_enabled" class="cart-toggle-nav" :store="store" />
                </ClientOnly>

                <!-- Velo: oscurece la página detrás del panel y cierra al tocarlo -->
                <div class="nav-veil" @click="closeMenuAndReturn" />

            </div>
        </nav>

    </header>
</template>
