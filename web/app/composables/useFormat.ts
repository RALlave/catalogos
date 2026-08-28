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
