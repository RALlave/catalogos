import { defineStore } from 'pinia'
import { ref } from 'vue'

/**
 * El aviso de cambios sin guardar. Hay uno solo para todo el panel: la pantalla
 * que tiene cambios pregunta y espera la respuesta, así que el modal vive
 * montado en App.vue y no en cada formulario.
 */
export const useUnsavedStore = defineStore('unsaved', () => {
    const open = ref(false)
    const saving = ref(false)
    const canSave = ref(false)

    let resolver = null
    let saver = null

    function answer(leave) {
        const resolve = resolver

        open.value = false
        saving.value = false
        resolver = null
        saver = null

        resolve?.(leave)
    }

    /**
     * Pregunta qué hacer con los cambios pendientes.
     *
     * @param {Function|null} save Guardado de la pantalla; devuelve si guardó.
     * @returns {Promise<boolean>} Si el usuario decidió salir.
     */
    function ask(save = null) {
        /* Ya se está preguntando: la segunda salida no interrumpe a la
           primera. Pasa con Escape, que cierra el modal y el formulario. */
        if (open.value) {
            return Promise.resolve(false)
        }

        saver = save
        canSave.value = Boolean(save)
        open.value = true

        return new Promise(resolve => {
            resolver = resolve
        })
    }

    function stay() {
        if (saving.value) {
            return
        }

        answer(false)
    }

    function discard() {
        if (saving.value) {
            return
        }

        answer(true)
    }

    /**
     * Guarda con la función que dejó la pantalla y recién ahí sale. Si el
     * guardado falla el modal se cierra igual: los errores se leen en el
     * formulario, que es donde el usuario se queda.
     */
    async function saveAndLeave() {
        if (! saver || saving.value) {
            return
        }

        saving.value = true

        let saved = false

        try {
            saved = await saver() === true
        } catch {
            saved = false
        }

        answer(saved)
    }

    return {
        open,
        saving,
        canSave,
        ask,
        stay,
        discard,
        saveAndLeave,
    }
})
