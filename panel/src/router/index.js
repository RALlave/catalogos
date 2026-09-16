import { createRouter, createWebHistory } from 'vue-router'

import { currentStoreSlug, storeUrl } from '@/lib/host'
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
        path: '/admin',
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
                path: 'paginas/home',
                name: 'page-home',
                component: () => import('@/views/HomePageView.vue'),
                meta: { title: 'Home' },
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
        path: '/superadmin',
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
            {
                path: 'import-export',
                name: 'admin-backup',
                component: () => import('@/views/admin/AdminBackupView.vue'),
                meta: { title: 'Import & Export' },
            },
            {
                path: 'ajustes',
                name: 'admin-settings',
                component: () => import('@/views/admin/AdminSettingsView.vue'),
                meta: { title: 'Ajustes' },
            },
            {
                path: 'cuenta',
                name: 'admin-account',
                component: () => import('@/views/AccountView.vue'),
                meta: { title: 'Cuenta' },
            },
        ],
    },

    { path: '/:pathMatch(.*)*', redirect: '/login' },
]

export const router = createRouter({
    /* El panel no cuelga de un prefijo: sus rutas conviven con las del
       catálogo en el mismo origen y nginx decide cuál sirve cada una. Los
       assets sí van bajo /panel/, pero eso es cosa de Vite, no del router. */
    history: createWebHistory('/'),
    routes,
    /* Cambiar de pantalla arranca arriba, pero quedarse en la misma y sólo
       mover la query —las pestañas viajan ahí— no mueve el scroll: el usuario
       está mirando esa parte de la página. */
    scrollBehavior: (to, from) => (to.path === from.path ? false : { top: 0 }),
})

/** A dónde va cada rol cuando entra por la puerta equivocada. */
function homeFor(auth) {
    return auth.isSuperadmin ? { name: 'admin-dashboard' } : { name: 'dashboard' }
}

/**
 * El dueño administra desde el subdominio de su tienda. Si llegó por otro host
 * —el apex, o el subdominio de una tienda ajena— hay que **saltar de origen**,
 * no navegar: se pide un código de un solo uso, se cambia de dirección y la
 * sesión se rearma del otro lado, porque el token no cruza entre orígenes.
 *
 * Quien todavía no creó su tienda no tiene subdominio: se queda donde está.
 */
async function movedToOwnSubdomain(auth) {
    const slug = auth.store?.slug

    if (! slug || currentStoreSlug() === slug) {
        return false
    }

    window.location.href = storeUrl(slug, `/admin?handoff=${await auth.handoff()}`)

    return true
}

router.beforeEach(async (to) => {
    const auth = useAuthStore()

    /* Llega de otro origen —una impersonación o el propio dueño que se logueó
       en el apex— con un código de un solo uso. Se canjea por un token de este
       origen y se saca de la URL, que queda en el historial. */
    if (to.query.handoff) {
        await auth.redeemHandoff(String(to.query.handoff))

        return { path: to.path, query: { ...to.query, handoff: undefined } }
    }

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

    /* Última parada: el dueño con el panel abierto en el host de otro. Va
       acá y no en homeFor() para que valga también cuando la ruta es la
       correcta y sólo está mal el subdominio. */
    if (to.meta.role === 'store_owner' && auth.isLogged && await movedToOwnSubdomain(auth)) {
        return false
    }

    return true
})

router.afterEach((to) => {
    document.title = to.meta.title ? `${to.meta.title} — Catálogos` : 'Panel — Catálogos'
})
