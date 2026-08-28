<script setup lang="ts">
import type { Store } from '~/types/catalog'

definePageMeta({ layout: 'store' })

const { data: storeData } = await useCurrentStore()
const store = computed(() => storeData.value as Store)

const consultMessage = computed(() => `Hola ${store.value.name}, quiero hacer una consulta.`)

const websiteHref = computed(() => {
    const website = store.value.website

    if (! website) {
        return null
    }

    return website.startsWith('http') ? website : `https://${website}`
})

const location = computed(() => [store.value.city, store.value.country].filter(Boolean).join(', '))

const schedules = computed(() => store.value.schedules ?? [])

/* Un enlace común de Maps no se puede meter en un iframe: el sitio lo
   rechaza con X-Frame-Options. Sólo las URL de tipo "embed" se dibujan;
   con cualquier otra queda el botón, que siempre funciona. */
const embeddable = computed(() => store.value.map_url?.includes('/maps/embed') ?? false)

/* Ícono + clave + valor. El enlace es opcional: la ciudad no lleva. */
const items = computed(() => [
    {
        key: 'WhatsApp',
        value: store.value.whatsapp,
        icon: 'whatsapp',
        href: store.value.whatsapp ? whatsappUrl(store.value.whatsapp, consultMessage.value) : null,
    },
    {
        key: 'Teléfono',
        value: store.value.phone,
        icon: 'phone',
        href: store.value.phone ? `tel:${store.value.phone.replace(/\s/g, '')}` : null,
    },
    { key: 'Email', value: store.value.email, icon: 'mail', href: store.value.email ? `mailto:${store.value.email}` : null },
    { key: 'Dirección', value: store.value.address, icon: 'pin', href: store.value.map_url },
    { key: 'Ciudad', value: location.value, icon: 'location', href: null },
    { key: 'Sitio web', value: store.value.website, icon: 'arrow', href: websiteHref.value },
    { key: 'Facebook', value: store.value.facebook ? 'Ver página' : null, icon: 'facebook', href: store.value.facebook },
    { key: 'Instagram', value: store.value.instagram ? 'Ver perfil' : null, icon: 'instagram', href: store.value.instagram },
    { key: 'TikTok', value: store.value.tiktok ? 'Ver perfil' : null, icon: 'tiktok', href: store.value.tiktok },
].filter((item) => hasValue(item.value)))

const title = computed(() => `Contacto — ${store.value.name}`)

useSeoMeta({
    title,
    description: () => store.value.description ?? undefined,
    ogType: 'website',
    ogTitle: title,
    ogDescription: () => store.value.description ?? undefined,
    ogImage: () => store.value.cover_url ?? store.value.logo_url ?? undefined,
})
</script>

<template>
    <main id="content" class="content">

        <!-- Mismo banner del catálogo, más bajo: el modificador sólo
             cambia el token del alto, no los colores ni los toggles -->
        <section class="banner banner-short">
            <img
                v-if="store.cover_url"
                class="banner-photo"
                :src="store.cover_url"
                alt=""
                width="1024"
                height="411"
                fetchpriority="high"
            >

            <div class="container banner-inner">
                <h1 class="banner-title">Contacto</h1>
                <p v-if="store.description" class="banner-text">{{ store.description }}</p>
            </div>
        </section>

        <section class="section" aria-labelledby="contact-title">
            <div class="container">

                <h2 id="contact-title">Cómo comunicarte</h2>

                <div class="contact">

                    <section class="card" aria-labelledby="contact-data-title">
                        <h3 class="card-title" id="contact-data-title">Datos</h3>

                        <address class="contact-list">
                            <div v-for="item in items" :key="item.key" class="contact-info">
                                <AppIcon :name="item.icon" class="contact-info-icon" />
                                <span class="contact-info-body">
                                    <span class="contact-info-key">{{ item.key }}</span>
                                    <a
                                        v-if="item.href"
                                        class="contact-info-value contact-info-link"
                                        :href="item.href"
                                        :target="item.href.startsWith('http') ? '_blank' : undefined"
                                        :rel="item.href.startsWith('http') ? 'noopener' : undefined"
                                    >
                                        {{ item.value }}
                                    </a>
                                    <span v-else class="contact-info-value">{{ item.value }}</span>
                                </span>
                            </div>
                        </address>

                        <p v-if="store.whatsapp">
                            <a
                                class="btn btn-primary"
                                :href="whatsappUrl(store.whatsapp, consultMessage)"
                                target="_blank"
                                rel="noopener"
                            >
                                <AppIcon name="whatsapp" class="btn-icon" />
                                Escribir por WhatsApp
                            </a>
                        </p>
                    </section>

                    <section v-if="schedules.length" class="card" aria-labelledby="contact-hours-title">
                        <h3 class="card-title" id="contact-hours-title">Horarios de atención</h3>

                        <dl class="hours">
                            <div v-for="schedule in schedules" :key="schedule.days" class="hours-row">
                                <dt class="hours-day">{{ schedule.days }}</dt>
                                <dd class="hours-time">{{ schedule.hours }}</dd>
                            </div>
                        </dl>
                    </section>

                </div>

                <div v-if="store.map_url" class="map">
                    <iframe
                        v-if="embeddable"
                        class="map-frame"
                        :src="store.map_url"
                        title="Mapa de la tienda"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                    />

                    <p class="map-footer">
                        <span class="map-address">{{ store.address }}{{ location ? ` · ${location}` : '' }}</span>
                        <a class="btn btn-border" :href="store.map_url" target="_blank" rel="noopener">
                            <AppIcon name="pin" class="btn-icon" />
                            Ver en el mapa
                        </a>
                    </p>
                </div>

            </div>
        </section>

    </main>
</template>
