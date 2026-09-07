import { defineStore } from 'pinia'
import { computed, ref } from 'vue'

import { apexUrl, storeUrl } from '@/lib/host'
import {
    api,
    getImpersonatedStore,
    getToken,
    setImpersonatedStore,
    setToken,
} from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null)
    const store = ref(null)
    const ready = ref(false)

    /* Nombre de la tienda a la que entró el superadmin, o null si es él mismo. */
    const impersonating = ref(getImpersonatedStore())

    const isLogged = computed(() => Boolean(user.value))
    const roles = computed(() => user.value?.roles ?? [])
    const isSuperadmin = computed(() => roles.value.includes('superadmin'))
    const isStoreOwner = computed(() => roles.value.includes('store_owner'))

    /** Iniciales para el avatar. */
    const initials = computed(() => (user.value?.name ?? '')
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map(part => part[0].toUpperCase())
        .join(''))

    function session(payload) {
        user.value = payload.user
        setToken(payload.token)
    }

    async function login(credentials) {
        session(await api.post('/login', { ...credentials, device_name: 'panel' }))

        await loadStore()
    }

    async function register(data) {
        session(await api.post('/register', data))

        await loadStore()
    }

    /**
     * El superadmin no tiene tienda propia: el 404 del endpoint es la
     * respuesta esperada y no debe romper la sesión.
     */
    async function loadStore() {
        if (isSuperadmin.value) {
            store.value = null

            return
        }

        try {
            const payload = await api.get('/store')

            store.value = payload.store
        } catch {
            store.value = null
        }
    }

    async function restore() {
        if (! getToken()) {
            ready.value = true

            return
        }

        try {
            const payload = await api.get('/me')

            user.value = payload.user

            await loadStore()
        } catch {
            reset()
        }

        ready.value = true
    }

    /**
     * Código de un solo uso para llevar esta sesión a otro subdominio.
     *
     * Los dos orígenes no comparten localStorage, así que el token no se puede
     * copiar: se pide un código de vida corta, viaja en la URL y del otro lado
     * se canjea por un token propio de ese origen.
     */
    async function handoff() {
        return (await api.post('/auth/handoff')).code
    }

    /**
     * Canjea el código con el que llegó el navegador. Sirve para los dos
     * caminos: el dueño que se logueó en el apex y el superadmin que entra a
     * administrar una tienda.
     */
    async function redeemHandoff(code) {
        const payload = await api.post('/auth/handoff/redeem', { code, device_name: 'panel' })

        session(payload)

        impersonating.value = payload.impersonated ? payload.store?.name ?? null : null
        setImpersonatedStore(impersonating.value)

        await loadStore()

        ready.value = true
    }

    /**
     * Entrar al panel de una tienda como su dueño: se salta al subdominio de
     * la tienda con un código de un solo uso. El token del superadmin no se
     * mueve de su origen, así que no hay nada que guardar para volver.
     */
    async function impersonate(storeId) {
        const payload = await api.post(`/admin/stores/${storeId}/impersonate`)

        window.location.href = storeUrl(payload.store.slug, `/admin?handoff=${payload.code}`)
    }

    /**
     * Volver a la sesión de superadmin. El token del dueño se revoca acá —no
     * se puede dejar vivo en la base— y el del superadmin sigue esperando
     * intacto en el apex, que es a donde vuelve el navegador.
     */
    async function stopImpersonating() {
        try {
            await api.post('/logout')
        } catch {
            /* Si el token ya no existe, volver igual. */
        }

        reset()

        window.location.href = apexUrl('/superadmin/tiendas')
    }

    async function logout() {
        try {
            await api.post('/logout')
        } finally {
            reset()
        }
    }

    function reset() {
        user.value = null
        store.value = null
        impersonating.value = null

        setToken(null)
        setImpersonatedStore(null)
    }

    return {
        user,
        store,
        ready,
        isLogged,
        roles,
        isSuperadmin,
        isStoreOwner,
        impersonating,
        initials,
        login,
        register,
        loadStore,
        restore,
        handoff,
        redeemHandoff,
        impersonate,
        stopImpersonating,
        logout,
    }
})
