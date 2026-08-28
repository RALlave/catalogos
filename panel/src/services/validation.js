export const REQUIRED_MESSAGE = 'Completá este campo.'

export const REQUIRED_TOAST = 'Llene información en los campos obligatorios'

/**
 * Chequeo previo al submit para no gastar un viaje a la API: devuelve los campos
 * vacíos con el mismo formato que usa Laravel, `{ campo: ['mensaje'] }`.
 *
 * @param {object} form
 * @param {string[]} fields Campos obligatorios según el FormRequest de la API.
 */
export function checkRequired(form, fields) {
    const errors = {}

    fields.forEach(field => {
        const value = form[field]

        if (value === null || value === undefined || String(value).trim() === '') {
            errors[field] = [REQUIRED_MESSAGE]
        }
    })

    return errors
}

export function hasErrors(errors) {
    return Object.keys(errors).length > 0
}
