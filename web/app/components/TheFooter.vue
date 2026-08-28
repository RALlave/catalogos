<!--
    PIE DE PÁGINA — cuatro columnas sobre fondo oscuro + barra inferior.
    Los títulos son <h2>: el <h1> de la página vive en el banner.

    Cada columna se arma con lo que la tienda cargó. Lo que no vino de
    la API no se muestra: no se inventan datos de contacto.
-->

<script setup lang="ts">
import type { Store } from '~/types/catalog'

const props = defineProps<{ store: Store }>()

const storePath = computed(() => `/${props.store.slug}`)

const brandWords = computed(() => props.store.name.trim().split(/\s+/))
const brandName = computed(() => brandWords.value.slice(0, -1).join(' '))
const brandAlt = computed(() => brandWords.value.length > 1 ? brandWords.value.at(-1) : props.store.name)

const socials = computed(() => [
    { name: 'Facebook', icon: 'facebook', url: props.store.facebook },
    { name: 'Instagram', icon: 'instagram', url: props.store.instagram },
    { name: 'TikTok', icon: 'tiktok', url: props.store.tiktok },
].filter((social) => hasValue(social.url)))

const year = new Date().getFullYear()
</script>

<template>
    <footer class="footer">

        <div class="container footer-inner">

            <div class="footer-col footer-col-brand">
                <NuxtLink class="brand brand-footer" :to="storePath">
                    <span class="brand-name">
                        {{ brandName }}
                        <span class="brand-name-alt">{{ brandAlt }}</span>
                    </span>
                </NuxtLink>
                <p v-if="store.description" class="footer-description">{{ store.description }}</p>
            </div>

            <nav class="footer-col" aria-labelledby="footer-content-title">
                <h2 class="footer-title" id="footer-content-title">Contenido</h2>
                <ul class="footer-list">
                    <li><NuxtLink class="footer-link" :to="storePath">Inicio</NuxtLink></li>
                    <li><NuxtLink class="footer-link" :to="`${storePath}#products`">Productos</NuxtLink></li>
                    <li><NuxtLink class="footer-link" :to="`${storePath}/contacto`">Contacto</NuxtLink></li>
                </ul>
            </nav>

            <section id="contact" class="footer-col" aria-labelledby="footer-contact-title">
                <h2 class="footer-title" id="footer-contact-title">Contacto</h2>
                <address class="footer-info-list">
                    <ul class="footer-list">
                        <li v-if="store.whatsapp" class="footer-info">
                            <AppIcon name="whatsapp" class="footer-info-icon" />
                            <a
                                class="footer-link footer-link-strong"
                                :href="`https://wa.me/${store.whatsapp}`"
                                target="_blank"
                                rel="noopener"
                            >
                                WhatsApp {{ store.whatsapp }}
                            </a>
                        </li>
                        <li v-if="store.phone" class="footer-info">
                            <AppIcon name="phone" class="footer-info-icon" />
                            <a class="footer-link" :href="`tel:${store.phone}`">{{ store.phone }}</a>
                        </li>
                        <li v-if="store.email" class="footer-info">
                            <AppIcon name="mail" class="footer-info-icon" />
                            <a class="footer-link" :href="`mailto:${store.email}`">{{ store.email }}</a>
                        </li>
                        <li v-for="schedule in store.schedules ?? []" :key="schedule.days" class="footer-info">
                            <AppIcon name="clock" class="footer-info-icon" />
                            <span>{{ schedule.days }}: {{ schedule.hours }}</span>
                        </li>
                        <li v-if="store.address || store.city" class="footer-info">
                            <AppIcon name="pin" class="footer-info-icon" />
                            <span>
                                {{ store.address }}
                                <br v-if="store.address && store.city">
                                {{ store.city }}
                            </span>
                        </li>
                    </ul>
                </address>
            </section>

            <section v-if="socials.length" class="footer-col" aria-labelledby="footer-social-list-title">
                <h2 class="footer-title" id="footer-social-list-title">Seguinos</h2>
                <ul class="footer-list footer-social-list">
                    <li v-for="social in socials" :key="social.name">
                        <a class="footer-social" :href="social.url!" target="_blank" rel="noopener">
                            <AppIcon :name="social.icon" class="footer-social-icon" />
                            {{ social.name }}
                        </a>
                    </li>
                </ul>
            </section>

        </div>

        <div class="footer-bottom">
            <div class="container footer-bottom-inner">
                <p class="footer-copy">© {{ year }} {{ store.name }} · Diseñado por <a class="footer-link" href="https://diseprog.com" target="_blank" rel="noopener">DiseProg</a></p>
            </div>
        </div>

    </footer>
</template>
