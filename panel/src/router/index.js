import { createRouter, createWebHistory } from 'vue-router'

import { useAuthStore } from '@/stores/auth'

const routes = [
    {
        path: '/login',
        name: 'login',
        component: () => import('@/views/auth/LoginView.vue'),
        meta: { guest: true, title: 'Iniciar sesión' },
    },
    {
        path: '/registro',
        name: 'register',
        component: () => import('@/views/auth/RegisterView.vue'),
        meta: { guest: true, title: 'Crear cuenta' },
    },
    {
        path: '/recuperar',
        name: 'forgot',
        component: () => import('@/views/auth/ForgotView.vue'),
        meta: { guest: true, title: 'Recuperar contraseña' },
    },
    {
        path: '/restablecer',
        name: 'reset',
        component: () => import('@/views/auth/ResetView.vue'),
        meta: { guest: true, title: 'Nueva contraseña' },
    },

    {
        path: '/',
        component: () => import('@/layouts/PanelLayout.vue'),
        meta: { auth: true, role: 'store_owner' },
        children: [
            {
                path: '',
                name: 'dashboard',
                component: () => import('@/views/DashboardView.vue'),
                meta: { title: 'Dashboard' },
            },
            {
                path: 'productos',
                name: 'products',
                component: () => import('@/views/ProductsView.vue'),
                meta: { title: 'Productos' },
            },
            {
                path: 'productos/nuevo',
                name: 'product-create',
                component: () => import('@/views/ProductFormView.vue'),
                meta: { title: 'Nuevo producto' },
            },
            {
                path: 'productos/:id',
                name: 'product-edit',
                component: () => import('@/views/ProductFormView.vue'),
                meta: { title: 'Editar producto' },
            },
            {
                path: 'categorias',
                name: 'categories',
                component: () => import('@/views/CategoriesView.vue'),
                meta: { title: 'Categorías' },
            },
            {
                path: 'categorias/nueva',
                name: 'category-create',
                component: () => import('@/views/CategoryFormView.vue'),
                meta: { title: 'Nueva categoría' },
            },
            {
                path: 'categorias/:id',
                name: 'category-edit',
                component: () => import('@/views/CategoryFormView.vue'),
                meta: { title: 'Editar categoría' },
            },
            {
                path: 'hero',
                name: 'heroes',
                component: () => import('@/views/HeroesView.vue'),
                meta: { title: 'Hero (banner)' },
            },
            {
                path: 'hero/nuevo',
                name: 'hero-create',
                component: () => import('@/views/HeroFormView.vue'),
                meta: { title: 'Nuevo hero' },
            },
            {
                path: 'hero/:id',
                name: 'hero-edit',
                component: () => import('@/views/HeroFormView.vue'),
                meta: { title: 'Editar hero' },
            },
            {
                path: 'multimedia',
                name: 'media',
                component: () => import('@/views/MediaView.vue'),
                meta: { title: 'Multimedia' },
            },
            {
                path: 'tienda',
                name: 'store',
                component: () => import('@/views/StoreView.vue'),
                meta: { title: 'Mi tienda' },
            },
            {
                path: 'configuracion',
                name: 'settings',
                component: () => import('@/views/SettingsView.vue'),
                meta: { title: 'Configuración' },
            },
            {
                path: 'seo',
                name: 'seo',
                component: () => import('@/views/SeoView.vue'),
                meta: { title: 'SEO' },
            },
            {
                path: 'cuenta',
                name: 'account',
                component: () => import('@/views/AccountView.vue'),
                meta: { title: 'Cuenta' },
            },
        ],
    },

    {
        path: '/admin',
        component: () => import('@/layouts/PanelLayout.vue'),
        meta: { auth: true, role: 'superadmin' },
        children: [
            {
                path: '',
                name: 'admin-dashboard',
                component: () => import('@/views/admin/AdminDashboardView.vue'),
                meta: { title: 'Dashboard' },
            },
            {
                path: 'tiendas',
                name: 'admin-stores',
                component: () => import('@/views/admin/AdminStoresView.vue'),
                meta: { title: 'Tiendas' },
            },
            {
                path: 'tiendas/nueva',
                name: 'admin-store-create',
                component: () => import('@/views/admin/AdminStoreFormView.vue'),
                meta: { title: 'Nueva tienda' },
            },
            {
                path: 'tiendas/:id',
                name: 'admin-store-edit',
                component: () => import('@/views/admin/AdminStoreFormView.vue'),
                meta: { title: 'Editar tienda' },
            },
            {
                path: 'usuarios',
                name: 'admin-users',
                component: () => import('@/views/admin/AdminUsersView.vue'),
                meta: { title: 'Usuarios' },
            },
            {
                path: 'planes',
                name: 'admin-plans',
                component: () => import('@/views/admin/AdminPlansView.vue'),
                meta: { title: 'Planes' },
            },
            {
                path: 'moderacion',
                name: 'admin-moderation',
                component: () => import('@/views/admin/AdminModerationView.vue'),
                meta: { title: 'Moderación' },
            },
            {
                path: 'apariencia',
                name: 'admin-appearance',
                component: () => import('@/views/admin/AdminAppearanceView.vue'),
                meta: { title: 'Apariencia' },
            },
        ],
    },

    { path: '/:pathMatch(.*)*', redirect: '/' },
]

export const router = createRouter({
    /* BASE_URL lo pone Vite: "/" en local y "/panel/" en producción. */
    history: createWebHistory(import.meta.env.BASE_URL),
    routes,
    scrollBehavior: () => ({ top: 0 }),
})

/** A dónde va cada rol cuando entra por la puerta equivocada. */
function homeFor(auth) {
    return auth.isSuperadmin ? { name: 'admin-dashboard' } : { name: 'dashboard' }
}

router.beforeEach(async (to) => {
    const auth = useAuthStore()

    if (! auth.ready) {
        await auth.restore()
    }

    if (to.meta.auth && ! auth.isLogged) {
        return { name: 'login', query: { redirect: to.fullPath } }
    }

    if (to.meta.guest && auth.isLogged) {
        return homeFor(auth)
    }

    if (to.meta.role && auth.isLogged && ! auth.roles.includes(to.meta.role)) {
        return homeFor(auth)
    }

    return true
})

router.afterEach((to) => {
    document.title = to.meta.title ? `${to.meta.title} — Catálogos` : 'Panel — Catálogos'
})
