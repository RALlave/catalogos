import type { Product, Store } from '~/types/catalog'

export interface CartLine {
    slug: string
    name: string
    price: string | null
    image: string | null
    quantity: number
}

const MAX_QUANTITY = 999

/**
 * Si el panel del carrito está abierto. Vive aparte del carrito en sí
 * porque lo comparten dos componentes: el botón de la cabecera lo abre y
 * el panel lo cierra.
 */
export function useCartPanel() {
    return useState<boolean>('cart-open', () => false)
}

/**
 * Carrito del cliente, guardado en el navegador.
 *
 * La clave lleva el slug de la tienda: alguien puede tener un carrito abierto
 * en dos catálogos distintos sin que se pisen. Como vive en `localStorage`,
 * sigue ahí cuando la persona vuelve días después.
 *
 * En el servidor el carrito siempre está vacío —no hay `localStorage`—, así
 * que todo lo que dependa de él se pinta dentro de <ClientOnly> para no
 * romper la hidratación.
 */
export function useCart(store: Ref<Store>) {
    const key = computed(() => `cart:${store.value.slug}`)

    const lines = useState<CartLine[]>(() => [])
    const ready = useState<boolean>(() => false)

    function read(): CartLine[] {
        try {
            const raw = window.localStorage.getItem(key.value)

            return raw ? JSON.parse(raw) as CartLine[] : []
        } catch {
            /* Ventana privada, cookies bloqueadas o JSON roto: el catálogo
               tiene que seguir andando, sólo que sin carrito guardado. */
            return []
        }
    }

    function persist() {
        try {
            window.localStorage.setItem(key.value, JSON.stringify(lines.value))
        } catch {
            /* Sin lugar para guardar, el carrito dura lo que la pestaña. */
        }
    }

    onMounted(() => {
        lines.value = read()
        ready.value = true
    })

    const count = computed(() => lines.value.reduce((total, line) => total + line.quantity, 0))

    /* Null cuando ningún producto del carrito tiene precio: es distinto de
       cero y así se ve que la tienda no publica precios. */
    const total = computed(() => {
        const priced = lines.value.filter((line) => line.price !== null)

        if (! priced.length) {
            return null
        }

        return priced.reduce((sum, line) => sum + Number(line.price) * line.quantity, 0)
    })

    function add(product: Product, quantity = 1) {
        const existing = lines.value.find((line) => line.slug === product.slug)

        if (existing) {
            existing.quantity = Math.min(existing.quantity + quantity, MAX_QUANTITY)
        } else {
            lines.value.push({
                slug: product.slug,
                name: product.name,
                price: product.sale_price ?? product.price,
                image: product.images[0] ?? null,
                quantity,
            })
        }

        persist()
    }

    function setQuantity(slug: string, quantity: number) {
        const line = lines.value.find((item) => item.slug === slug)

        if (! line) {
            return
        }

        if (quantity < 1) {
            remove(slug)

            return
        }

        line.quantity = Math.min(quantity, MAX_QUANTITY)
        persist()
    }

    function remove(slug: string) {
        lines.value = lines.value.filter((line) => line.slug !== slug)
        persist()
    }

    function clear() {
        lines.value = []
        persist()
    }

    function has(slug: string) {
        return lines.value.some((line) => line.slug === slug)
    }

    return { lines, ready, count, total, add, setQuantity, remove, clear, has }
}
