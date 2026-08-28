/**
 * Marca en <html> si la última interacción fue con puntero o con teclado.
 *
 * Lo usa el CSS para no pintar el anillo de foco del buscador cuando se abre
 * con el mouse — quien hace clic ya sabe dónde está parado. Con teclado el
 * anillo se pinta siempre: sin él no hay forma de saber qué está activo.
 */
export function useInputMode() {
    onMounted(() => {
        const root = document.documentElement

        const mark = (mode: string) => root.setAttribute('data-input', mode)

        /* Sólo las teclas de navegación cuentan: escribir en el campo no
           tiene que hacer aparecer el anillo */
        const onKeydown = (event: KeyboardEvent) => {
            const key = event.key

            if (key === 'Tab' || key === 'Enter' || key === ' ' || key === 'Escape' || key.startsWith('Arrow')) {
                mark('keyboard')
            }
        }

        const onPointer = () => mark('mouse')

        mark('mouse')

        document.addEventListener('pointerdown', onPointer, true)
        document.addEventListener('keydown', onKeydown, true)

        onBeforeUnmount(() => {
            document.removeEventListener('pointerdown', onPointer, true)
            document.removeEventListener('keydown', onKeydown, true)
        })
    })
}
