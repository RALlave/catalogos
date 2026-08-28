import { defineStore } from 'pinia'
import { ref } from 'vue'

const STORAGE_SIDEBAR = 'dash.sidebar.collapsed'
const STORAGE_THEME = 'dash.theme'
const STORAGE_ADMIN_THEME = 'dash.admin.theme'

/** Temas del panel de tienda. "cacao" es el de base y no lleva atributo. */
export const THEMES = [
    { key: 'cacao', name: 'Cacao' },
    { key: 'oceano', name: 'Océano' },
    { key: 'grafito', name: 'Grafito' },
]

/** El panel de plataforma arranca en el tema oscuro, pero se puede aclarar. */
export const ADMIN_THEMES = [
    { key: 'admin', name: 'Admin oscuro' },
    ...THEMES,
]

let toastId = 0

export const useUiStore = defineStore('ui', () => {
    const theme = ref(window.localStorage.getItem(STORAGE_THEME) || 'cacao')
    const adminTheme = ref(window.localStorage.getItem(STORAGE_ADMIN_THEME) || 'admin')
    const scope = ref('panel')

    const collapsed = ref(window.localStorage.getItem(STORAGE_SIDEBAR) === '1')
    const mobileOpen = ref(false)
    const toasts = ref([])

    function paint(name) {
        if (name && name !== 'cacao') {
            document.documentElement.setAttribute('data-theme', name)
        } else {
            document.documentElement.removeAttribute('data-theme')
        }
    }

    function applyTheme(name) {
        if (scope.value === 'admin') {
            adminTheme.value = name
            window.localStorage.setItem(STORAGE_ADMIN_THEME, name)
        } else {
            theme.value = name
            window.localStorage.setItem(STORAGE_THEME, name)
        }

        paint(name)
    }

    /** Cada panel recuerda su propio tema: se repinta al entrar en uno u otro. */
    function useScope(name) {
        scope.value = name

        paint(name === 'admin' ? adminTheme.value : theme.value)
    }

    function currentTheme() {
        return scope.value === 'admin' ? adminTheme.value : theme.value
    }

    function toggleSidebar() {
        if (window.matchMedia('(max-width: 900px)').matches) {
            mobileOpen.value = ! mobileOpen.value

            return
        }

        collapsed.value = ! collapsed.value
        window.localStorage.setItem(STORAGE_SIDEBAR, collapsed.value ? '1' : '0')
    }

    function closeMobile() {
        mobileOpen.value = false
    }

    /** El tipo "danger" pinta el toast de error: mismo stack, otro icono. */
    function toast(title, message = '', type = 'success') {
        const id = ++toastId

        toasts.value.push({ id, title, message, type })

        window.setTimeout(() => {
            toasts.value = toasts.value.filter(item => item.id !== id)
        }, 3200)
    }

    paint(theme.value)

    return {
        theme,
        adminTheme,
        scope,
        collapsed,
        mobileOpen,
        toasts,
        applyTheme,
        useScope,
        currentTheme,
        toggleSidebar,
        closeMobile,
        toast,
    }
})
