<script setup>
import { computed, onMounted, ref } from 'vue'

import AppIcon from '@/components/AppIcon.vue'
import DashChart from '@/components/DashChart.vue'
import { api } from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import { useUiStore } from '@/stores/ui'

const auth = useAuthStore()
const ui = useUiStore()

const metrics = ref(null)
const latest = ref([])
const loading = ref(true)

const firstName = computed(() => auth.user?.name?.split(' ')[0] ?? '')

/* Checklist calculado con los datos reales de la tienda. */
const checklist = computed(() => {
    const store = auth.store

    return [
        { label: 'Logo y portada cargados', done: Boolean(store?.logo_url && store?.cover_url) },
        { label: 'WhatsApp configurado', done: Boolean(store?.whatsapp) },
        { label: 'Descripción de la tienda', done: Boolean(store?.description) },
        { label: 'Al menos una categoría', done: (metrics.value?.categories.total ?? 0) > 0 },
    ]
})

const storeInitials = computed(() => (auth.store?.name ?? '')
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map(part => part[0].toUpperCase())
    .join(''))

function copyLink() {
    if (! auth.store) {
        return
    }

    navigator.clipboard?.writeText(auth.store.public_url)
    ui.toast('Enlace copiado', auth.store.public_url)
}

const shareUrl = computed(() => {
    const text = `Mirá mi catálogo: ${auth.store?.public_url ?? ''}`

    return `https://wa.me/?text=${encodeURIComponent(text)}`
})

/* ---------------------------------------------------------------------
   Los gráficos y las visitas todavía no tienen backend: los datos son
   de ejemplo hasta que exista el módulo de estadísticas.
   --------------------------------------------------------------------- */

function tooltip(colors) {
    return {
        backgroundColor: colors.primary,
        padding: 10,
        cornerRadius: 8,
        displayColors: false,
        titleFont: { weight: '600' },
    }
}

const visitsChart = colors => ({
    type: 'line',
    data: {
        labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
        datasets: [{
            label: 'Visitas',
            data: [128, 165, 142, 210, 264, 318, 287],
            borderColor: colors.primary,
            backgroundColor: colors.accentSoft,
            borderWidth: 2,
            fill: true,
            tension: 0.35,
            pointRadius: 3,
            pointBackgroundColor: colors.surface,
            pointBorderColor: colors.primary,
            pointBorderWidth: 2,
            pointHoverRadius: 5,
        }],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: tooltip(colors) },
        scales: {
            x: { grid: { display: false }, border: { color: colors.grid } },
            y: {
                beginAtZero: true,
                grid: { color: colors.grid },
                border: { display: false },
                ticks: { maxTicksLimit: 5 },
            },
        },
    },
})

const viewedChart = colors => ({
    type: 'bar',
    data: {
        labels: ['Perfume Ámbar', 'Gafas Retro', 'Zapatilla Urbana', 'Camisa Lino', 'Kit de Uñas'],
        datasets: [{
            label: 'Vistas',
            data: [412, 358, 291, 244, 187],
            backgroundColor: colors.accent,
            hoverBackgroundColor: colors.primary,
            borderRadius: 6,
            barThickness: 18,
        }],
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: tooltip(colors) },
        scales: {
            x: {
                beginAtZero: true,
                grid: { color: colors.grid },
                border: { display: false },
                ticks: { maxTicksLimit: 5 },
            },
            y: { grid: { display: false }, border: { display: false } },
        },
    },
})

const sharedChart = colors => ({
    type: 'doughnut',
    data: {
        labels: ['WhatsApp', 'Facebook', 'Enlace directo', 'Instagram'],
        datasets: [{
            data: [148, 62, 45, 31],
            backgroundColor: [colors.green, colors.primary, colors.accent, colors.rose],
            borderColor: colors.surface,
            borderWidth: 3,
            hoverOffset: 6,
        }],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '62%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: { usePointStyle: true, pointStyle: 'circle', boxWidth: 8, padding: 14 },
            },
            tooltip: tooltip(colors),
        },
    },
})

onMounted(async () => {
    try {
        const payload = await api.get('/dashboard')

        metrics.value = payload.metrics
        latest.value = payload.latest_products
    } catch {
        metrics.value = null
    } finally {
        loading.value = false
    }
})
</script>

<template>
    <div class="page-header">
        <div class="page-title">
            <h1>Hola, {{ firstName }}</h1>
            <p>Así viene tu catálogo esta semana</p>
        </div>

        <div class="page-actions">
            <button v-if="auth.store" class="btn btn-outline" type="button" @click="copyLink">
                <AppIcon name="copy" />
                Copiar enlace
            </button>

            <RouterLink class="btn btn-primary" :to="{ name: 'product-create' }">
                <AppIcon name="plus" />
                Nuevo producto
            </RouterLink>
        </div>
    </div>

    <div v-if="! auth.store" class="alert alert-warning">
        <AppIcon name="info" />
        <div class="alert-body">
            <strong>Todavía no creaste tu tienda</strong>
            <span>Cargá los datos de tu tienda para publicar tu catálogo.</span>
        </div>
    </div>

    <section class="stat-grid">
        <article class="stat">
            <div class="stat-top">
                <div class="stat-label">Total productos</div>
                <span class="stat-icon is-accent">
                    <AppIcon name="box" />
                </span>
            </div>
            <div class="stat-bottom">
                <div class="stat-value">{{ metrics?.products.total ?? '—' }}</div>
                <span class="stat-delta">{{ metrics?.products.visible ?? 0 }} visibles</span>
            </div>
        </article>

        <article class="stat">
            <div class="stat-top">
                <div class="stat-label">Total categorías</div>
                <span class="stat-icon">
                    <AppIcon name="folder" />
                </span>
            </div>
            <div class="stat-bottom">
                <div class="stat-value">{{ metrics?.categories.total ?? '—' }}</div>
                <span class="stat-delta">{{ metrics?.categories.active ?? 0 }} activas</span>
            </div>
        </article>

        <article class="stat">
            <div class="stat-top">
                <div class="stat-label">Visitas al catálogo</div>
                <span class="stat-icon is-info">
                    <AppIcon name="eye" />
                </span>
            </div>
            <div class="stat-bottom">
                <div class="stat-value">1.514</div>
                <span class="stat-delta is-up">+18%</span>
            </div>
        </article>

        <article class="stat">
            <div class="stat-top">
                <div class="stat-label">Productos destacados</div>
                <span class="stat-icon is-success">
                    <AppIcon name="star" />
                </span>
            </div>
            <div class="stat-bottom">
                <div class="stat-value">{{ metrics?.products.featured ?? '—' }}</div>
                <span class="stat-delta">de {{ metrics?.products.total ?? 0 }} productos</span>
            </div>
        </article>
    </section>

    <section class="panel-grid">
        <article class="card">
            <header class="card-header">
                <div class="card-title">
                    <h2>Visitas por día</h2>
                    <p>Últimos 7 días</p>
                </div>
                <span class="badge badge-success badge-dot">+18%</span>
            </header>
            <div class="card-body">
                <DashChart :factory="visitsChart" />
            </div>
        </article>

        <article class="card">
            <header class="card-header">
                <div class="card-title">
                    <h2>Estado de la tienda</h2>
                </div>
                <span v-if="auth.store" class="badge badge-dot" :class="auth.store.active ? 'badge-success' : 'badge-warning'">
                    {{ auth.store.active ? 'Publicada' : 'Oculta' }}
                </span>
            </header>

            <div class="card-body">
                <div v-if="auth.store" class="store-status">
                    <div class="store-head">
                        <span class="avatar avatar-lg">{{ storeInitials }}</span>
                        <span class="store-head-text">
                            <strong>{{ auth.store.name }}</strong>
                            <span>{{ auth.store.industry }}</span>
                        </span>
                    </div>

                    <div class="store-link">
                        <span class="store-link-url">{{ auth.store.public_url }}</span>
                        <button class="btn btn-ghost btn-sm" type="button" @click="copyLink">Copiar</button>
                    </div>

                    <div class="store-actions">
                        <a
                            class="btn btn-outline btn-sm"
                            :href="auth.store.public_url"
                            target="_blank"
                            rel="noopener"
                        >Abrir tienda</a>
                        <a class="btn btn-whatsapp btn-sm" :href="shareUrl" target="_blank" rel="noopener">Compartir</a>
                    </div>

                    <hr>

                    <div class="checklist">
                        <ul>
                            <li v-for="item in checklist" :key="item.label">
                                <span class="check-mark" :class="{ 'is-done': item.done }">
                                    <AppIcon name="check" stroke-width="3" />
                                </span>
                                <span>{{ item.label }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div v-else class="empty">
                    <p>Creá tu tienda para ver su estado acá.</p>
                    <RouterLink class="btn btn-primary btn-sm" :to="{ name: 'store' }">Crear mi tienda</RouterLink>
                </div>
            </div>
        </article>
    </section>

    <section class="panel-grid is-even">
        <article class="card">
            <header class="card-header">
                <div class="card-title">
                    <h2>Productos más vistos</h2>
                    <p>Top 5 del mes</p>
                </div>
            </header>
            <div class="card-body">
                <DashChart :factory="viewedChart" short />
            </div>
        </article>

        <article class="card">
            <header class="card-header">
                <div class="card-title">
                    <h2>Productos compartidos</h2>
                    <p>Por canal, últimos 30 días</p>
                </div>
            </header>
            <div class="card-body">
                <DashChart :factory="sharedChart" short />
            </div>
        </article>
    </section>

    <section class="card">
        <header class="card-header">
            <div class="card-title">
                <h2>Productos recientes</h2>
                <p>Los últimos que cargaste</p>
            </div>
            <RouterLink class="btn btn-outline btn-sm" :to="{ name: 'products' }">Ver todos</RouterLink>
        </header>

        <div class="card-body">
            <div v-if="loading" class="empty">
                <p>Cargando…</p>
            </div>

            <div v-else-if="! latest.length" class="empty">
                <p>Todavía no cargaste productos.</p>
                <RouterLink class="btn btn-primary btn-sm" :to="{ name: 'product-create' }">
                    Cargar el primero
                </RouterLink>
            </div>

            <div v-else class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="product in latest" :key="product.id">
                            <td>
                                <div class="table-cell">
                                    <span class="thumb">
                                        <img v-if="product.main_image_url" :src="product.main_image_url" :alt="product.name">
                                        <AppIcon v-else name="image" />
                                    </span>
                                    <span class="table-cell-text">
                                        <strong>{{ product.name }}</strong>
                                        <span>{{ product.sku }}</span>
                                    </span>
                                </div>
                            </td>
                            <td>{{ product.category?.name }}</td>
                            <td>{{ product.sale_price ?? product.price }}</td>
                            <td>
                                <span class="badge" :class="product.visible ? 'badge-success' : 'badge-warning'">
                                    {{ product.visible ? 'Visible' : 'Oculto' }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</template>
