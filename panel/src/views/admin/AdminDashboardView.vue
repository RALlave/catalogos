<script setup>
import { onMounted, ref } from 'vue'

import AppIcon from '@/components/AppIcon.vue'
import DashChart from '@/components/DashChart.vue'
import { api } from '@/services/api'

const metrics = ref(null)
const latest = ref([])
const loading = ref(true)

/* ---------------------------------------------------------------------
   Altas por mes, planes, tiendas más visitadas e ingresos todavía no
   tienen backend: los datos son de ejemplo.
   --------------------------------------------------------------------- */

function tooltip(colors) {
    return {
        backgroundColor: colors.surfaceHover,
        titleColor: colors.text,
        bodyColor: colors.text,
        padding: 10,
        cornerRadius: 8,
        displayColors: false,
        titleFont: { weight: '600' },
    }
}

const signupsChart = colors => ({
    type: 'line',
    data: {
        labels: ['Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago'],
        datasets: [
            {
                label: 'Tiendas nuevas',
                data: [42, 68, 91, 124, 158, 203, 247],
                borderColor: colors.primary,
                backgroundColor: colors.accentSoft,
                borderWidth: 2,
                fill: true,
                tension: 0.35,
                pointRadius: 3,
                pointBackgroundColor: colors.surface,
                pointBorderColor: colors.primary,
                pointBorderWidth: 2,
            },
            {
                label: 'Tiendas publicadas',
                data: [28, 45, 62, 88, 112, 147, 181],
                borderColor: colors.accent,
                borderWidth: 2,
                borderDash: [5, 4],
                fill: false,
                tension: 0.35,
                pointRadius: 0,
            },
        ],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { usePointStyle: true, pointStyle: 'circle', boxWidth: 8, padding: 14 },
            },
            tooltip: tooltip(colors),
        },
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

const plansChart = colors => ({
    type: 'doughnut',
    data: {
        labels: ['Gratis', 'Emprendedor', 'Negocio'],
        datasets: [{
            data: [1042, 168, 37],
            backgroundColor: [colors.primary, colors.accent, colors.green],
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

const topStoresChart = colors => ({
    type: 'bar',
    data: {
        labels: ['Aroma Sur', 'Kaya Deco', 'Mi Tienda', 'Luz Beauty', 'Nómade'],
        datasets: [{
            label: 'Visitas',
            data: [8420, 6180, 4930, 3710, 2880],
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

const revenueChart = colors => ({
    type: 'bar',
    data: {
        labels: ['Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago'],
        datasets: [{
            label: 'Ingresos',
            data: [186000, 254000, 341000, 468000, 612000, 798000, 961000],
            backgroundColor: colors.primary,
            hoverBackgroundColor: colors.accent,
            borderRadius: 6,
            barThickness: 22,
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

onMounted(async () => {
    try {
        const payload = await api.get('/admin/metrics')

        metrics.value = payload.metrics
        latest.value = payload.latest_stores
    } finally {
        loading.value = false
    }
})
</script>

<template>
    <div class="page-header">
        <div class="page-title">
            <h1>Plataforma</h1>
            <p>Cómo viene el SaaS en general</p>
        </div>

        <div class="page-actions">
            <RouterLink class="btn btn-primary" :to="{ name: 'admin-store-create' }">
                <AppIcon name="plus" />
                Nueva tienda
            </RouterLink>
        </div>
    </div>

    <section class="stat-grid">
        <article class="stat">
            <div class="stat-top">
                <div class="stat-label">Tiendas</div>
                <span class="stat-icon is-accent">
                    <AppIcon name="store" />
                </span>
            </div>
            <div class="stat-bottom">
                <div class="stat-value">{{ metrics?.stores.total ?? '—' }}</div>
                <span class="stat-delta">{{ metrics?.stores.active ?? 0 }} publicadas</span>
            </div>
        </article>

        <article class="stat">
            <div class="stat-top">
                <div class="stat-label">Dueños de tienda</div>
                <span class="stat-icon">
                    <AppIcon name="users" />
                </span>
            </div>
            <div class="stat-bottom">
                <div class="stat-value">{{ metrics?.users.total ?? '—' }}</div>
                <span class="stat-delta">{{ metrics?.users.suspended ?? 0 }} suspendidos</span>
            </div>
        </article>

        <article class="stat">
            <div class="stat-top">
                <div class="stat-label">Productos</div>
                <span class="stat-icon is-info">
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
                <div class="stat-label">Categorías</div>
                <span class="stat-icon is-success">
                    <AppIcon name="folder" />
                </span>
            </div>
            <div class="stat-bottom">
                <div class="stat-value">{{ metrics?.categories ?? '—' }}</div>
                <span class="stat-delta">en toda la plataforma</span>
            </div>
        </article>
    </section>

    <section class="panel-grid">
        <article class="card">
            <header class="card-header">
                <div class="card-title">
                    <h2>Altas de tiendas por mes</h2>
                    <p>Últimos 7 meses</p>
                </div>
            </header>
            <div class="card-body">
                <DashChart :factory="signupsChart" />
            </div>
        </article>

        <article class="card">
            <header class="card-header">
                <div class="card-title">
                    <h2>Distribución por plan</h2>
                </div>
            </header>
            <div class="card-body">
                <DashChart :factory="plansChart" />
            </div>
        </article>
    </section>

    <section class="panel-grid is-even">
        <article class="card">
            <header class="card-header">
                <div class="card-title">
                    <h2>Tiendas más visitadas</h2>
                    <p>Top 5 del mes</p>
                </div>
            </header>
            <div class="card-body">
                <DashChart :factory="topStoresChart" short />
            </div>
        </article>

        <article class="card">
            <header class="card-header">
                <div class="card-title">
                    <h2>Ingresos mensuales</h2>
                    <p>Últimos 7 meses</p>
                </div>
            </header>
            <div class="card-body">
                <DashChart :factory="revenueChart" short />
            </div>
        </article>
    </section>

    <section class="card">
        <header class="card-header">
            <div class="card-title">
                <h2>Últimas tiendas</h2>
                <p>Las que se dieron de alta hace poco</p>
            </div>
            <RouterLink class="btn btn-outline btn-sm" :to="{ name: 'admin-stores' }">Ver todas</RouterLink>
        </header>

        <div class="card-body is-flush">
            <div v-if="loading" class="empty">
                <p>Cargando…</p>
            </div>

            <div v-else-if="! latest.length" class="empty">
                <p>Todavía no hay tiendas.</p>
            </div>

            <div v-else class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tienda</th>
                            <th>Dueño</th>
                            <th>Productos</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="store in latest" :key="store.id">
                            <td>
                                <span class="table-cell-text">
                                    <strong>{{ store.name }}</strong>
                                    <span>{{ store.slug }}</span>
                                </span>
                            </td>
                            <td>
                                <span class="table-cell-text">
                                    <strong>{{ store.owner?.name }}</strong>
                                    <span>{{ store.owner?.email }}</span>
                                </span>
                            </td>
                            <td>{{ store.products_count }}</td>
                            <td>
                                <span
                                    class="badge badge-dot"
                                    :class="store.active ? 'badge-success' : 'badge-warning'"
                                >
                                    {{ store.active ? 'Publicada' : 'Oculta' }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</template>
