import { defineStore } from 'pinia'
import { ref } from 'vue'

/**
 * Las confirmaciones del panel. Reemplazan a `window.confirm`, que dibuja el
 * navegador: no se puede estilar, cambia de forma en cada sistema y frena el
 * hilo.
 *
 * Se usa esperando la respuesta:
 *
 *     if (! await confirm.ask({ title: '¿Eliminar?', action: 'Eliminar' })) {
 *         return
 *     }
 */
export const useConfirmStore = defineStore('confirm', () => {
    const open = ref(false)
    const title = ref('')
    const text = ref('')
    const action = ref('Aceptar')
    const danger = ref(false)

    let resolver = null

    function answer(accepted) {
        const resolve = resolver

        open.value = false
        resolver = null

        resolve?.(accepted)
    }

    /**
     * @param {object}  options
     * @param {string}  options.title  La pregunta.
     * @param {string}  options.text   El detalle; los saltos de línea se respetan.
     * @param {string}  options.action Qué dice el botón que confirma.
     * @param {boolean} options.danger Si la acción destruye algo.
     * @returns {Promise<boolean>}
     */
    function ask({ title: askTitle, text: askText = '', action: askAction = 'Aceptar', danger: askDanger = false }) {
        /* Ya se está preguntando: la segunda pregunta no pisa a la primera. */
        if (open.value) {
            return Promise.resolve(false)
        }

        title.value = askTitle
        text.value = askText
        action.value = askAction
        danger.value = askDanger
        open.value = true

        return new Promise(resolve => {
            resolver = resolve
        })
    }

    function cancel() {
        answer(false)
    }

    function accept() {
        answer(true)
    }

    return {
        open,
        title,
        text,
        action,
        danger,
        ask,
        cancel,
        accept,
    }
})
