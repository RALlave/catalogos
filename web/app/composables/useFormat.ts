/**
 * Formato de números y armado de enlaces de contacto.
 * Regla del proyecto: si un dato no viene, no se muestra. Nunca se inventa.
 */

export function formatAmount(value: string | number | null): string {
    if (value === null || value === '') {
        return ''
    }

    return new Intl.NumberFormat('es-PY', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(Number(value))
}

/**
 * Cuánto baja el precio, en porcentaje entero. Se calcula y no se carga,
 * así no puede quedar contradiciendo a los dos precios. Devuelve null si
 * no hay oferta: sin `sale_price`, sin `price`, o si no es más barato.
 */
export function discountPercent(price: string | number | null, salePrice: string | number | null): number | null {
    const before = Number(price)
    const now = Number(salePrice)

    if (! salePrice || ! before || now >= before) {
        return null
    }

    return Math.round((1 - now / before) * 100)
}

export function whatsappUrl(phone: string, message: string): string {
    return `https://wa.me/${encodeURIComponent(phone)}?text=${encodeURIComponent(message)}`
}

export function hasValue(value: unknown): boolean {
    if (value === null || value === undefined) {
        return false
    }

    if (Array.isArray(value)) {
        return value.length > 0
    }

    return String(value).trim() !== ''
}
