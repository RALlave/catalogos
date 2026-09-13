const BASE = import.meta.env.VITE_API_BASE ?? 'http://127.0.0.1:8000/api'

const STORAGE_TOKEN = 'dash.token'

/* Mientras el superadmin administra una tienda, el nombre de esa tienda queda
   guardado para la barra de aviso. Va en localStorage y no en memoria para que
   un F5 no deje la sesión a medias.

   Su propio token no se guarda acá: vive en el apex, que es otro origen y
   tiene su propio localStorage. */
const STORAGE_IMPERSONATED = 'dash.impersonated_store'

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

export function getImpersonatedStore() {
    return window.localStorage.getItem(STORAGE_IMPERSONATED)
}

export function setImpersonatedStore(name) {
    if (name) {
        window.localStorage.setItem(STORAGE_IMPERSONATED, name)
    } else {
        window.localStorage.removeItem(STORAGE_IMPERSONATED)
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

/**
 * Nombre con el que el servidor manda el archivo. La API lo arma con la fecha,
 * así que el cliente no lo inventa: lo lee de la cabecera.
 */
function filename(response, fallback) {
    const header = response.headers.get('Content-Disposition') ?? ''

    const encoded = header.match(/filename\*=UTF-8''([^;]+)/i)

    if (encoded) {
        return decodeURIComponent(encoded[1])
    }

    return header.match(/filename="?([^";]+)"?/i)?.[1] ?? fallback
}

/** Empuja el blob al disco del visitante con un enlace de un solo uso. */
function save(blob, name) {
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')

    link.href = url
    link.download = name

    document.body.appendChild(link)
    link.click()
    link.remove()

    window.URL.revokeObjectURL(url)
}

/**
 * Descarga un archivo de la API. No pasa por request(): la respuesta es
 * binaria y no se puede leer como JSON, salvo cuando falla.
 */
async function downloadFile(path, fallback) {
    const headers = { Accept: '*/*' }
    const token = getToken()

    if (token) {
        headers.Authorization = `Bearer ${token}`
    }

    const response = await fetch(`${BASE}${path}`, { headers })

    if (! response.ok) {
        throw new ApiError(response.status, await response.json().catch(() => null))
    }

    save(await response.blob(), filename(response, fallback))
}

export const api = {
    get: (path, query) => request('GET', path, { query }),
    post: (path, body) => request('POST', path, { body }),
    put: (path, body) => request('PUT', path, { body }),
    patch: (path, body) => request('PATCH', path, { body }),
    delete: path => request('DELETE', path),
    upload: (path, formData) => request('POST', path, { body: formData, raw: true }),
    download: (path, fallback) => downloadFile(path, fallback),
}
