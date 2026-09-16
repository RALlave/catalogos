<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'

import AppIcon from '@/components/AppIcon.vue'
import FormField from '@/components/FormField.vue'
import PasswordInput from '@/components/PasswordInput.vue'
import RowActions from '@/components/RowActions.vue'
import ScrollStrip from '@/components/ScrollStrip.vue'
import { useUnsavedChanges } from '@/composables/useUnsavedChanges'
import { ApiError, api } from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import { useConfirmStore } from '@/stores/confirm'
import { useUiStore } from '@/stores/ui'

const auth = useAuthStore()
const ui = useUiStore()
const confirm = useConfirmStore()

const users = ref([])
const meta = ref(null)
const loading = ref(true)

const route = useRoute()

/* The topbar search lands here with ?search= already filled in. */
const filters = ref({ search: String(route.query.search ?? ''), role: '', suspended: '' })
const page = ref(1)

/* Edición en modal: se abre un solo usuario a la vez. */
const editing = ref(null)
const saving = ref(false)
const form = ref({ name: '', username: '', email: '', password: '' })
const errors = ref({})

async function load() {
    loading.value = true

    try {
        const payload = await api.get('/admin/users', {
            search: filters.value.search,
            role: filters.value.role,
            suspended: filters.value.suspended,
            page: page.value,
        })

        users.value = payload.data
        meta.value = payload.meta
    } finally {
        loading.value = false
    }
}

let searchTimer

watch(() => filters.value.search, () => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(() => {
        page.value = 1
        load()
    }, 350)
})

/* Same view, new ?search= from the topbar: the component is reused, so sync it. */
watch(() => route.query.search, (search) => {
    if (search !== undefined) {
        filters.value.search = String(search)
    }
})

watch(() => [filters.value.role, filters.value.suspended], () => {
    page.value = 1
    load()
})

watch(page, load)

/* La edición es un modal: los cambios sólo cuentan mientras está abierto. */
const { markSaved, confirmLeave } = useUnsavedChanges({
    state: () => form.value,
    save,
    active: () => Boolean(editing.value),
})

function edit(user) {
    editing.value = user
    errors.value = {}
    form.value = { name: user.name, username: user.username, email: user.email, password: '' }

    markSaved()
}

/** Cierra sin preguntar: la usa lo que ya resolvió qué hacer con los cambios. */
function dismiss() {
    editing.value = null
}

async function closeEdit() {
    if (await confirmLeave()) {
        dismiss()
    }
}

/** @returns {Promise<boolean>} Si salió bien: lo mira el aviso de cambios sin guardar. */
async function save() {
    const user = editing.value

    errors.value = {}
    saving.value = true

    try {
        const payload = {
            name: form.value.name,
            username: form.value.username,
            email: form.value.email,
        }

        if (form.value.password) {
            payload.password = form.value.password
        }

        const response = await api.put(`/admin/users/${user.id}`, payload)

        Object.assign(user, response.user)

        markSaved()
        dismiss()

        ui.toast('Usuario actualizado', user.name)

        return true
    } catch (error) {
        if (error instanceof ApiError) {
            errors.value = error.errors
        }

        return false
    } finally {
        saving.value = false
    }
}

function onKeydown(event) {
    if (event.key === 'Escape' && editing.value) {
        closeEdit()
    }
}

async function toggleSuspend(user) {
    const suspend = ! user.suspended

    if (suspend) {
        const confirmed = await confirm.ask({
            title: `¿Suspender a ${user.name}?`,
            text: 'No va a poder entrar al panel.',
            action: 'Suspender',
            danger: true,
        })

        if (! confirmed) {
            return
        }
    }

    try {
        const response = await api.patch(`/admin/users/${user.id}/suspend`, { suspended: suspend })

        Object.assign(user, response.user)

        ui.toast(suspend ? 'Usuario suspendido' : 'Usuario reactivado', user.name)
    } catch (error) {
        ui.toast('No se pudo cambiar', error instanceof ApiError ? error.message : '')
    }
}

onMounted(() => {
    load()
    window.addEventListener('keydown', onKeydown)
})

onBeforeUnmount(() => {
    window.removeEventListener('keydown', onKeydown)
})
</script>

<template>
    <div class="page-header">
        <div class="page-title">
            <h1>Usuarios</h1>
            <p>Cuentas registradas en la plataforma</p>
        </div>
    </div>

    <section class="card">
        <div class="toolbar">
            <div class="search toolbar-search">
                <AppIcon name="search" />
                <input
                    v-model="filters.search"
                    class="input"
                    type="search"
                    placeholder="Buscar por nombre o email…"
                    aria-label="Buscar usuario"
                >
            </div>

            <ScrollStrip>
                <div class="toolbar-filters">
                    <select v-model="filters.role" class="select" aria-label="Filtrar por rol">
                        <option value="">Todos los roles</option>
                        <option value="store_owner">Dueños de tienda</option>
                        <option value="superadmin">Superadmins</option>
                    </select>

                    <select v-model="filters.suspended" class="select" aria-label="Filtrar por estado">
                        <option value="">Todos los estados</option>
                        <option value="0">Activos</option>
                        <option value="1">Suspendidos</option>
                    </select>
                </div>
            </ScrollStrip>

            <div class="toolbar-count">{{ meta?.total ?? 0 }} usuarios</div>
        </div>

        <div class="card-body is-flush">
            <div v-if="loading" class="empty">
                <p>Cargando…</p>
            </div>

            <div v-else-if="! users.length" class="empty">
                <p>No hay usuarios con esos filtros.</p>
            </div>

            <div v-else class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th class="is-hide-mobile">Tienda</th>
                            <th class="is-hide-mobile">Rol</th>
                            <th class="is-hide-mobile">Estado</th>
                            <th><span class="visually-hidden">Acciones</span></th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="user in users" :key="user.id">
                            <td>
                                <div class="table-cell">
                                    <span class="avatar">{{ user.name.slice(0, 2).toUpperCase() }}</span>
                                    <span class="table-cell-text">
                                        <strong>{{ user.name }}</strong>
                                        <span>{{ user.username }} · {{ user.email }}</span>
                                    </span>
                                </div>
                            </td>

                            <td class="is-hide-mobile">{{ user.store?.name }}</td>

                            <td class="is-hide-mobile">
                                <span v-for="role in user.roles" :key="role" class="badge">
                                    {{ role === 'superadmin' ? 'Superadmin' : 'Dueño' }}
                                </span>
                            </td>

                            <td class="is-hide-mobile">
                                <span
                                    class="badge badge-dot"
                                    :class="user.suspended ? 'badge-danger' : 'badge-success'"
                                >
                                    {{ user.suspended ? 'Suspendido' : 'Activo' }}
                                </span>
                            </td>

                            <td>
                                <RowActions
                                    :actions="[
                                        { label: 'Editar', icon: 'pencil', onClick: () => edit(user) },
                                        ...(user.id !== auth.user?.id
                                            ? [{
                                                label: user.suspended ? 'Reactivar' : 'Suspender',
                                                icon: user.suspended ? 'check' : 'ban',
                                                onClick: () => toggleSuspend(user),
                                            }]
                                            : []),
                                    ]"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="meta && meta.last_page > 1" class="card-footer">
            <div class="page-info">Página {{ meta.current_page }} de {{ meta.last_page }}</div>

            <nav class="pagination" aria-label="Paginación">
                <button
                    class="page-link"
                    :class="{ 'is-disabled': page <= 1 }"
                    type="button"
                    @click="page > 1 && page--"
                >
                    <AppIcon name="chevronLeft" />
                    Anterior
                </button>
                <button
                    class="page-link"
                    :class="{ 'is-disabled': page >= meta.last_page }"
                    type="button"
                    @click="page < meta.last_page && page++"
                >
                    Siguiente
                    <AppIcon name="chevronRight" />
                </button>
            </nav>
        </div>
    </section>

    <Teleport to="body">
        <div v-if="editing" class="modal" role="dialog" aria-modal="true" aria-label="Editar usuario">
            <div class="modal-backdrop" @click="closeEdit" />

            <div class="modal-dialog">
                <div class="modal-header">
                    <div class="modal-title">
                        <h2>Editar usuario</h2>
                        <p>{{ editing.username }} · {{ editing.email }}</p>
                    </div>

                    <button
                        class="btn btn-ghost btn-icon"
                        type="button"
                        title="Cerrar"
                        aria-label="Cerrar"
                        @click="closeEdit"
                    >
                        <AppIcon name="close" />
                    </button>
                </div>

                <div class="modal-body">
                    <form class="form" @submit.prevent="save">
                        <FormField label="Nombre" field-id="user-name" :error="errors.name?.[0]">
                            <input
                                id="user-name"
                                v-model="form.name"
                                maxlength="255"
                                class="input"
                                :class="{ 'has-error': errors.name }"
                                type="text"
                            >
                        </FormField>

                        <FormField
                            label="Usuario"
                            hint="Entre 4 y 15 letras o números, sin espacios"
                            field-id="user-username"
                            :error="errors.username?.[0]"
                        >
                            <input
                                id="user-username"
                                v-model="form.username"
                                maxlength="15"
                                class="input"
                                :class="{ 'has-error': errors.username }"
                                type="text"
                            >
                        </FormField>

                        <FormField
                            label="Email"
                            hint="Puede entrar con el email o con el usuario"
                            field-id="user-email"
                            :error="errors.email?.[0]"
                        >
                            <input
                                id="user-email"
                                v-model="form.email"
                                maxlength="255"
                                class="input"
                                :class="{ 'has-error': errors.email }"
                                type="email"
                            >
                        </FormField>

                        <FormField
                            label="Nueva contraseña"
                            hint="Dejala vacía para no cambiarla"
                            field-id="user-password"
                            :error="errors.password?.[0]"
                        >
                            <PasswordInput
                                id="user-password"
                                v-model="form.password"
                                autocomplete="new-password"
                            />
                        </FormField>
                    </form>
                </div>

                <div class="modal-footer">
                    <div class="modal-actions">
                        <button class="btn btn-ghost" type="button" @click="closeEdit">
                            Cancelar
                        </button>

                        <button
                            class="btn btn-primary"
                            :class="{ 'is-loading': saving }"
                            type="button"
                            @click="save"
                        >
                            Guardar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
