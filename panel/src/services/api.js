const BASE = import.meta.env.VITE_API_BASE ?? 'http://127.0.0.1:8000/api'

const STORAGE_TOKEN = 'dash.token'

export function getToken() {
    return window.localStorage.getItem(STORAGE_TOKEN)
}

export function setToken(token) {
    if (token) {
        window.localStorage.setItem(STORAGE_TOKEN, token)
    } else {
        window.localStorage.removeItem(STORAGE_TOKEN)
    }
}

/**
 * Error de la API. Guarda el estado y, en un 422, los errores por campo
 * para que el formulario los muestre debajo de cada input.
 */
export class ApiError extends Error {
    constructor(status, payload) {
        super(payload?.message ?? 'No pudimos completar la operación.')

        this.status = status
        this.errors = payload?.errors ?? {}
    }

    get isValidation() {
        return this.status === 422
    }

    /** Primer mensaje de un campo, si lo hay. */
    first(field) {
        return this.errors[field]?.[0] ?? null
    }
}

async function request(method, path, { body, query, raw } = {}) {
    const url = new URL(`${BASE}${path}`)

    Object.entries(query ?? {}).forEach(([key, value]) => {
        if (value !== null && value !== undefined && value !== '') {
            url.searchParams.set(key, value)
        }
    })

    const headers = { Accept: 'application/json' }
    const token = getToken()

    if (token) {
        headers.Authorization = `Bearer ${token}`
    }

    /* FormData viaja tal cual: el navegador arma el boundary. */
    if (body && ! raw) {
        headers['Content-Type'] = 'application/json'
    }

    const response = await fetch(url, {
        method,
        headers,
        body: raw ? body : (body ? JSON.stringify(body) : undefined),
    })

    if (response.status === 204) {
        return null
    }

    const payload = await response.json().catch(() => null)

    if (! response.ok) {
        throw new ApiError(response.status, payload)
    }

    return payload
}

export const api = {
    get: (path, query) => request('GET', path, { query }),
    post: (path, body) => request('POST', path, { body }),
    put: (path, body) => request('PUT', path, { body }),
    patch: (path, body) => request('PATCH', path, { body }),
    delete: path => request('DELETE', path),
    upload: (path, formData) => request('POST', path, { body: formData, raw: true }),
}
