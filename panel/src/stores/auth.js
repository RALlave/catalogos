import { defineStore } from 'pinia'
import { computed, ref } from 'vue'

import { api, getToken, setToken } from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null)
    const store = ref(null)
    const ready = ref(false)

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
        setToken(null)
    }

    return {
        user,
        store,
        ready,
        isLogged,
        roles,
        isSuperadmin,
        isStoreOwner,
        initials,
        login,
        register,
        loadStore,
        restore,
        logout,
    }
})
