<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

import AppIcon from '@/components/AppIcon.vue'
import FormField from '@/components/FormField.vue'
import { ApiError, api } from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import { useConfirmStore } from '@/stores/confirm'
import { useUiStore } from '@/stores/ui'

const router = useRouter()
const ui = useUiStore()
const auth = useAuthStore()
const confirm = useConfirmStore()

/* Lo elegido en cada input, por clave: "database" es el .sql y "files" el .zip. */
const chosen = ref({ database: null, files: null })

const filesInput = ref(null)

const busy = ref('')

function pick(event, key) {
    chosen.value[key] = event.target.files?.[0] ?? null
}

function fail(error, fallback) {
    ui.toast(
        fallback,
        error instanceof ApiError ? (error.first('file') ?? error.message) : '',
        'danger',
    )
}

async function exportDatabase() {
    busy.value = 'export-database'

    try {
        await api.download('/admin/backup/database', 'backup.sql')
    } catch (error) {
        fail(error, 'No pudimos exportar la base de datos')
    } finally {
        busy.value = ''
    }
}

async function exportFiles() {
    busy.value = 'export-files'

    try {
        await api.download('/admin/backup/files', 'backup.zip')
    } catch (error) {
        fail(error, 'No pudimos exportar los archivos')
    } finally {
        busy.value = ''
    }
}

/**
 * Restaurar la base reemplaza también la tabla de tokens: el de esta sesión
 * pertenece al momento actual, no al del respaldo, así que lo más probable es
 * que deje de existir. Se cierra la sesión a propósito, en vez de esperar el
 * primer 401 con el panel a medio andar.
 */
async function importDatabase() {
    if (! chosen.value.database) {
        return
    }

    const confirmed = await confirm.ask({
        title: '¿Restaurar la base de datos?',
        text: 'Se van a borrar TODOS los datos actuales y se van a reemplazar por los del archivo. '
            + 'No se puede deshacer y vas a tener que iniciar sesión de nuevo.',
        action: 'Restaurar',
        danger: true,
    })

    if (! confirmed) {
        return
    }

    busy.value = 'import-database'

    const body = new FormData()

    body.append('file', chosen.value.database)

    try {
        await api.upload('/admin/backup/database', body)

        try {
            await auth.logout()
        } catch {
            /* El token ya no existe en la base restaurada: no hay nada que revocar. */
        }

        router.push({ name: 'login' })
    } catch (error) {
        fail(error, 'No pudimos importar la base de datos')
    } finally {
        busy.value = ''
    }
}

async function importFiles() {
    if (! chosen.value.files) {
        return
    }

    const confirmed = await confirm.ask({
        title: '¿Restaurar los archivos?',
        text: 'Se van a borrar TODAS las imágenes actuales y se van a reemplazar por las del archivo. '
            + 'No se puede deshacer.',
        action: 'Restaurar',
        danger: true,
    })

    if (! confirmed) {
        return
    }

    busy.value = 'import-files'

    const body = new FormData()

    body.append('file', chosen.value.files)

    try {
        await api.upload('/admin/backup/files', body)

        chosen.value.files = null

        /* Sin esto, el input sigue mostrando el nombre del .zip ya restaurado. */
        if (filesInput.value) {
            filesInput.value.value = ''
        }

        ui.toast('Archivos restaurados')
    } catch (error) {
        fail(error, 'No pudimos importar los archivos')
    } finally {
        busy.value = ''
    }
}
</script>

<template>
    <div class="page-header">
        <div class="page-title">
            <h1>Import &amp; Export</h1>
            <p>Respaldo de la plataforma entera: la base de datos y las imágenes de todas las tiendas</p>
        </div>
    </div>

    <div class="alert alert-warning">
        <AppIcon name="alert" />
        <div class="alert-body">
            <strong>Los dos respaldos van juntos</strong>
            <p>
                Descargá siempre la base de datos y los archivos del mismo momento. Restaurar una base
                vieja sin sus imágenes deja productos apuntando a fotos que ya no existen.
            </p>
        </div>
    </div>

    <section class="card">
        <header class="card-header">
            <div class="card-title">
                <h2>Base de datos</h2>
                <p>Tiendas, productos, categorías, usuarios y estadísticas</p>
            </div>
        </header>

        <div class="card-body">
            <div class="form">
                <div class="form-row">
                    <FormField
                        label="Exportar"
                        field-id="export-database"
                        hint="Descarga un .sql con la estructura y los datos completos"
                    >
                        <div class="table-actions">
                            <button
                                id="export-database"
                                class="btn btn-primary"
                                type="button"
                                :disabled="busy !== ''"
                                @click="exportDatabase"
                            >
                                <AppIcon name="download" />
                                {{ busy === 'export-database' ? 'Exportando…' : 'Exportar base de datos' }}
                            </button>
                        </div>
                    </FormField>

                    <FormField
                        label="Importar"
                        field-id="import-database"
                        hint="Borra todas las tablas y las reemplaza por las del archivo"
                    >
                        <input
                            id="import-database"
                            class="input"
                            type="file"
                            accept=".sql"
                            :disabled="busy !== ''"
                            @change="pick($event, 'database')"
                        >

                        <div class="table-actions">
                            <button
                                class="btn btn-danger"
                                type="button"
                                :disabled="! chosen.database || busy !== ''"
                                @click="importDatabase"
                            >
                                <AppIcon name="upload" />
                                {{ busy === 'import-database' ? 'Importando…' : 'Importar y reemplazar' }}
                            </button>
                        </div>
                    </FormField>
                </div>
            </div>
        </div>
    </section>

    <section class="card">
        <header class="card-header">
            <div class="card-title">
                <h2>Archivos</h2>
                <p>Las imágenes de todas las tiendas y los logos de la plataforma</p>
            </div>
        </header>

        <div class="card-body">
            <div class="form">
                <div class="form-row">
                    <FormField
                        label="Exportar"
                        field-id="export-files"
                        hint="Descarga un .zip con las carpetas media y platform"
                    >
                        <div class="table-actions">
                            <button
                                id="export-files"
                                class="btn btn-primary"
                                type="button"
                                :disabled="busy !== ''"
                                @click="exportFiles"
                            >
                                <AppIcon name="download" />
                                {{ busy === 'export-files' ? 'Exportando…' : 'Exportar archivos' }}
                            </button>
                        </div>
                    </FormField>

                    <FormField
                        label="Importar"
                        field-id="import-files"
                        hint="Borra las imágenes actuales y las reemplaza por las del archivo"
                    >
                        <input
                            id="import-files"
                            ref="filesInput"
                            class="input"
                            type="file"
                            accept=".zip"
                            :disabled="busy !== ''"
                            @change="pick($event, 'files')"
                        >

                        <div class="table-actions">
                            <button
                                class="btn btn-danger"
                                type="button"
                                :disabled="! chosen.files || busy !== ''"
                                @click="importFiles"
                            >
                                <AppIcon name="upload" />
                                {{ busy === 'import-files' ? 'Importando…' : 'Importar y reemplazar' }}
                            </button>
                        </div>
                    </FormField>
                </div>
            </div>
        </div>
    </section>
</template>
