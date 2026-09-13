import { computed, onBeforeUnmount, onMounted, ref, toValue } from 'vue'
import { onBeforeRouteLeave } from 'vue-router'

import { useUnsavedStore } from '@/stores/unsaved'

/**
 * Avisa antes de perder lo que se escribió en un formulario.
 *
 * Compara el estado actual contra el que había cuando se cargó la pantalla, así
 * que cada vista tiene que llamar a markSaved() cuando termina de llenar el
 * formulario y cada vez que guarda: ese es el nuevo punto de partida.
 *
 * Cubre las tres formas de irse:
 *
 * - navegar a otra pantalla del panel → el modal propio, con los tres botones
 * - cerrar la pestaña o recargar → el aviso del navegador, que no se puede
 *   personalizar
 * - cerrar un modal que contiene el formulario → confirmLeave(), que la vista
 *   llama a mano antes de cerrarlo
 *
 * @param {object}        options
 * @param {Function}      options.state  Devuelve el estado a vigilar.
 * @param {Function|null} options.save   Guarda y devuelve si salió bien.
 * @param {Function|null} options.active Si el formulario está en pantalla.
 */
export function useUnsavedChanges({ state, save = null, active = null }) {
    const unsaved = useUnsavedStore()

    function snapshot() {
        return JSON.stringify(toValue(state))
    }

    const baseline = ref(snapshot())

    const dirty = computed(() => {
        if (active && ! toValue(active)) {
            return false
        }

        return snapshot() !== baseline.value
    })

    /** Lo que hay ahora pasa a ser lo guardado: deja de haber cambios. */
    function markSaved() {
        baseline.value = snapshot()
    }

    /**
     * Pregunta y responde si se puede salir. Salir descartando también limpia
     * el estado: si no, volver a la misma pantalla preguntaría de nuevo.
     */
    async function confirmLeave() {
        if (! dirty.value) {
            return true
        }

        const leave = await unsaved.ask(save)

        if (leave) {
            markSaved()
        }

        return leave
    }

    function onBeforeUnload(event) {
        if (! dirty.value) {
            return
        }

        event.preventDefault()
        event.returnValue = ''
    }

    onMounted(() => window.addEventListener('beforeunload', onBeforeUnload))
    onBeforeUnmount(() => window.removeEventListener('beforeunload', onBeforeUnload))

    onBeforeRouteLeave(() => confirmLeave())

    return { dirty, markSaved, confirmLeave }
}
