import { defineStore } from 'pinia'
import { computed, ref } from 'vue'

import {
    api,
    getAdminToken,
    getImpersonatedStore,
    getToken,
    setAdminToken,
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
     * Entrar al panel de una tienda como su dueño. El token del superadmin
     * queda guardado para poder volver.
     */
    async function impersonate(storeId) {
        const payload = await api.post(`/admin/stores/${storeId}/impersonate`)

        setAdminToken(getToken())
        setImpersonatedStore(payload.store.name)

        impersonating.value = payload.store.name
        user.value = payload.user
        setToken(payload.token)

        await loadStore()
    }

    /**
     * Volver a la sesión de superadmin: el token del dueño se revoca antes de
     * restaurar el propio, para no dejarlo vivo en la base.
     */
    async function stopImpersonating() {
        const adminToken = getAdminToken()

        try {
            await api.post('/logout')
        } catch {
            /* Si el token ya no existe, volver igual: lo que importa es el del superadmin. */
        }

        setToken(adminToken)
        setAdminToken(null)
        setImpersonatedStore(null)

        impersonating.value = null

        await restore()
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
        setAdminToken(null)
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
        impersonate,
        stopImpersonating,
        logout,
    }
})
