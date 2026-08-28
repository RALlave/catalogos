<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import AppIcon from '@/components/AppIcon.vue'
import FormField from '@/components/FormField.vue'
import MediaPicker from '@/components/MediaPicker.vue'
import { ApiError, api } from '@/services/api'
import { REQUIRED_TOAST, checkRequired, hasErrors } from '@/services/validation'
import { useUiStore } from '@/stores/ui'

const route = useRoute()
const router = useRouter()
const ui = useUiStore()

const id = ref(route.params.id ?? null)
const isEdit = computed(() => Boolean(id.value))

const categories = ref([])
const images = ref([])
const pending = ref([])
const pendingMedia = ref([])
const pickerOpen = ref(false)

/* Cada tab conoce los campos que muestra: así sabe cuántos errores tiene. */
const TABS = [
    { key: 'info', label: 'Información', fields: ['name', 'sku', 'slug', 'description', 'category_id', 'price', 'sale_price'] },
    { key: 'images', label: 'Imágenes', fields: ['images'] },
    { key: 'specs', label: 'Ficha técnica', fields: ['specs'] },
    { key: 'extras', label: 'Beneficios y etiquetas', fields: ['benefits', 'badges'] },
]

const REQUIRED = ['name']

const tab = ref('info')

const form = ref({
    name: '',
    sku: '',
    slug: '',
    description: '',
    category_id: '',
    price: '',
    sale_price: '',
    specs: [],
    benefits: [],
    badges: [],
    featured: false,
    visible: true,
    sold_out: false,
})

const errors = ref({})
const message = ref('')
const loading = ref(false)

const errorCount = computed(() => Object.fromEntries(TABS.map(item => [
    item.key,
    Object.keys(errors.value).filter(key => item.fields.some(field => key === field || key.startsWith(`${field}.`))).length,
])))

/** Evita recorrer tab por tab buscando qué falta al fallar el guardado. */
function goToFirstError() {
    const target = TABS.find(item => errorCount.value[item.key] > 0)

    if (target) {
        tab.value = target.key
    }
}

function addSpec() {
    form.value.specs.push({ label: '', value: '', type: '' })
}

function addColorSpec() {
    form.value.specs.push({ label: 'Colores', type: 'colors', values: ['#000000'] })
}

/** El input de color solo entiende hexadecimales de 6 dígitos. */
function asHex(color) {
    return /^#[0-9a-f]{6}$/i.test(color) ? color : '#000000'
}

function addBenefit() {
    form.value.benefits.push('')
}

function addBadge() {
    form.value.badges.push({ type: 'strong', text: '', detail: '' })
}

function onFiles(event) {
    pending.value = Array.from(event.target.files ?? [])
}

async function uploadPending(productId) {
    if (! pending.value.length) {
        return
    }

    const data = new FormData()

    pending.value.forEach(file => data.append('images[]', file))

    const payload = await api.upload(`/products/${productId}/images`, data)

    images.value = payload.product.images
    pending.value = []
}

/**
 * Elegidas en la biblioteca: si el producto ya existe se suman en el momento, y
 * si todavía no se creó esperan al guardado.
 */
async function onPick(list) {
    if (! id.value) {
        const nuevas = list.filter(media => ! pendingMedia.value.some(item => item.id === media.id))

        pendingMedia.value = [...pendingMedia.value, ...nuevas]

        return
    }

    await attachMedia(id.value, list.map(media => media.id))
}

async function attachMedia(productId, mediaIds) {
    const payload = await api.post(`/products/${productId}/images/attach`, { media_ids: mediaIds })

    images.value = payload.product.images
}

async function attachPending(productId) {
    if (! pendingMedia.value.length) {
        return
    }

    await attachMedia(productId, pendingMedia.value.map(media => media.id))

    pendingMedia.value = []
}

async function removeImage(image) {
    const payload = await api.delete(`/products/${id.value}/images/${image.id}`)

    images.value = payload.product.images
}

/**
 * Los campos vacíos no se mandan: la API guarda null y el catálogo
 * simplemente no los renderiza.
 */
function payload() {
    const data = { ...form.value }

    if (! data.slug) {
        delete data.slug
    }

    data.price = data.price === '' ? null : Number(data.price)
    data.sale_price = data.sale_price === '' ? null : Number(data.sale_price)
    data.category_id = data.category_id === '' ? null : Number(data.category_id)

    data.specs = form.value.specs
        .filter(spec => spec.label)
        .map(spec => (spec.type === 'colors'
            ? { label: spec.label, type: 'colors', values: spec.values.filter(Boolean) }
            : { label: spec.label, value: spec.value }))

    data.benefits = form.value.benefits.filter(Boolean)
    data.badges = form.value.badges.filter(badge => badge.text)

    if (! data.specs.length) {
        data.specs = null
    }

    if (! data.benefits.length) {
        data.benefits = null
    }

    if (! data.badges.length) {
        data.badges = null
    }

    return data
}

/**
 * @param {boolean} stay Guardar quedándose en la ficha en vez de volver al listado.
 */
async function submit(stay = false) {
    errors.value = checkRequired(form.value, REQUIRED)
    message.value = ''

    if (hasErrors(errors.value)) {
        ui.toast(REQUIRED_TOAST, '', 'danger')
        goToFirstError()

        return
    }

    loading.value = true

    try {
        const created = isEdit.value
            ? null
            : await api.post('/products', payload())

        if (created) {
            id.value = created.product.id
        } else {
            await api.put(`/products/${id.value}`, payload())
        }

        await uploadPending(id.value)
        await attachPending(id.value)

        ui.toast(created ? 'Producto creado' : 'Producto actualizado', form.value.name)

        if (! stay) {
            await router.push({ name: 'products' })

            return
        }

        /* Recién creado: la URL pasa a ser la de edición para no duplicarlo. */
        if (created) {
            await router.replace({ name: 'product-edit', params: { id: id.value } })
        }
    } catch (error) {
        if (error instanceof ApiError) {
            errors.value = error.errors
            message.value = error.isValidation ? '' : error.message

            if (error.isValidation) {
                ui.toast('Revisá los datos cargados', '', 'danger')
                goToFirstError()
            }
        } else {
            message.value = 'No pudimos conectar con el servidor.'
        }
    } finally {
        loading.value = false
    }
}

onMounted(async () => {
    const list = await api.get('/categories')

    categories.value = list.data

    if (! isEdit.value) {
        return
    }

    const payloadProduct = await api.get(`/products/${id.value}`)
    const product = payloadProduct.product

    form.value = {
        name: product.name,
        sku: product.sku ?? '',
        slug: product.slug,
        description: product.description ?? '',
        category_id: product.category_id ?? '',
        price: product.price ?? '',
        sale_price: product.sale_price ?? '',
        specs: (product.specs ?? []).map(spec => ({ type: '', value: '', values: [], ...spec })),
        benefits: product.benefits ?? [],
        badges: product.badges ?? [],
        featured: product.featured,
        visible: product.visible,
        sold_out: product.sold_out,
    }

    images.value = product.images
})
</script>

<template>
    <div class="page-header">
        <div class="page-title">
            <h1>{{ isEdit ? 'Editar producto' : 'Nuevo producto' }}</h1>
            <p>Lo que cargues acá es lo que ve tu cliente</p>
        </div>

        <div class="page-actions">
            <RouterLink class="btn btn-outline" :to="{ name: 'products' }">Volver</RouterLink>
        </div>
    </div>

    <div v-if="message" class="alert alert-danger">
        <AppIcon name="alert" />
        <div class="alert-body">
            <strong>No pudimos guardar</strong>
            <span>{{ message }}</span>
        </div>
    </div>

    <form novalidate @submit.prevent="submit(true)">
        <section class="card">
            <nav class="tabs">
                <button
                    v-for="item in TABS"
                    :key="item.key"
                    class="tab"
                    :class="{ 'is-active': tab === item.key }"
                    type="button"
                    @click="tab = item.key"
                >
                    <span>{{ item.label }}</span>

                    <span
                        v-if="errorCount[item.key]"
                        class="badge badge-warning"
                        :title="`${errorCount[item.key]} campos con errores`"
                    >
                        {{ errorCount[item.key] }} con error
                    </span>
                </button>
            </nav>

            <div class="card-body">
                <div v-show="tab === 'info'" class="tab-panel">
                    <div class="form">
                        <div class="form-row">
                            <FormField label="Nombre" field-id="product-name" :error="errors.name?.[0]">
                                <input
                                    id="product-name"
                                    maxlength="255"
                                    v-model="form.name"
                                    class="input"
                                    :class="{ 'has-error': errors.name }"
                                    type="text"
                                    placeholder="Perfume Ámbar Nocturno"
                                >
                            </FormField>

                            <FormField label="Código / SKU" field-id="product-sku" :error="errors.sku?.[0]">
                                <input
                                    id="product-sku"
                                    maxlength="100"
                                    v-model="form.sku"
                                    class="input"
                                    type="text"
                                    placeholder="PERF-001"
                                >
                            </FormField>
                        </div>

                        <FormField
                            label="Slug"
                            field-id="product-slug"
                            hint="Si lo dejás vacío se genera con el nombre."
                            :error="errors.slug?.[0]"
                        >
                            <input
                                id="product-slug"
                                maxlength="255"
                                v-model="form.slug"
                                class="input"
                                :class="{ 'has-error': errors.slug }"
                                type="text"
                            >
                        </FormField>

                        <FormField
                            label="Descripción"
                            field-id="product-description"
                            :counter="form.description"
                            :max="5000"
                            :error="errors.description?.[0]"
                        >
                            <textarea
                                id="product-description"
                                maxlength="5000"
                                v-model="form.description"
                                class="textarea"
                                placeholder="Contale a tu cliente de qué se trata"
                            />
                        </FormField>

                        <div class="form-row">
                            <FormField label="Categoría" field-id="product-category" :error="errors.category_id?.[0]">
                                <select id="product-category" v-model="form.category_id" class="select">
                                    <option value="">Sin categoría</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                            </FormField>

                            <FormField label="Precio" field-id="product-price" :error="errors.price?.[0]">
                                <input
                                    id="product-price"
                                    v-model="form.price"
                                    class="input"
                                    :class="{ 'has-error': errors.price }"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="18900"
                                >
                            </FormField>

                            <FormField
                                label="Precio oferta"
                                field-id="product-sale"
                                hint="Tiene que ser menor al precio."
                                :error="errors.sale_price?.[0]"
                            >
                                <input
                                    id="product-sale"
                                    v-model="form.sale_price"
                                    class="input"
                                    :class="{ 'has-error': errors.sale_price }"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                >
                            </FormField>
                        </div>
                    </div>
                </div>

                <div v-show="tab === 'images'" class="tab-panel">
                    <div class="form">
                        <div class="table-actions">
                            <button class="btn btn-outline btn-sm" type="button" @click="pickerOpen = true">
                                <AppIcon name="image" />
                                Elegir de la biblioteca
                            </button>
                        </div>

                        <div v-if="images.length" class="gallery">
                            <div
                                v-for="(image, index) in images"
                                :key="image.id"
                                class="gallery-item"
                                :class="{ 'is-main': index === 0 }"
                            >
                                <img :src="image.url" alt="">

                                <span v-if="index === 0" class="gallery-tag">Principal</span>

                                <button
                                    class="gallery-remove"
                                    type="button"
                                    title="Quitar imagen"
                                    aria-label="Quitar imagen"
                                    @click="removeImage(image)"
                                >
                                    <AppIcon name="trash" />
                                </button>
                            </div>
                        </div>

                        <FormField
                            label="Agregar imágenes"
                            field-id="product-images"
                            hint="JPG, PNG o WEBP, hasta 4 MB cada una. Hasta 10 por producto."
                            :error="errors['images.0']?.[0]"
                        >
                            <input
                                id="product-images"
                                class="input"
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                multiple
                                @change="onFiles"
                            >
                        </FormField>

                        <div v-if="pendingMedia.length" class="gallery">
                            <div
                                v-for="media in pendingMedia"
                                :key="media.id"
                                class="gallery-item"
                            >
                                <img :src="media.url" :alt="media.alt ?? ''">

                                <span class="gallery-tag">Al guardar</span>

                                <button
                                    class="gallery-remove"
                                    type="button"
                                    title="Quitar imagen"
                                    aria-label="Quitar imagen"
                                    @click="pendingMedia = pendingMedia.filter(item => item.id !== media.id)"
                                >
                                    <AppIcon name="trash" />
                                </button>
                            </div>
                        </div>

                        <div v-if="pending.length" class="field-hint">
                            <p>{{ pending.length }} imágenes se suben al guardar.</p>
                        </div>
                    </div>
                </div>

                <div v-show="tab === 'specs'" class="tab-panel">
                    <div class="table-actions">
                        <button class="btn btn-outline btn-sm" type="button" @click="addSpec">Agregar dato</button>
                        <button class="btn btn-outline btn-sm" type="button" @click="addColorSpec">Agregar colores</button>
                    </div>

                    <div v-if="! form.specs.length" class="empty">
                        <p>Sin ficha técnica.</p>
                    </div>

                    <div v-else class="form">
                        <div v-for="(spec, index) in form.specs" :key="index" class="form-row">
                            <FormField label="Etiqueta" :field-id="`spec-label-${index}`">
                                <input :id="`spec-label-${index}`" v-model="spec.label" class="input" type="text" maxlength="100">
                            </FormField>

                            <FormField
                                v-if="spec.type === 'colors'"
                                label="Colores"
                                :field-id="`spec-colors-${index}`"
                                hint="Elegí cada color con el selector."
                            >
                                <input :id="`spec-colors-${index}`" type="hidden" :value="spec.values.join(', ')">

                                <div class="swatches">
                                    <span v-for="(color, position) in spec.values" :key="position" class="swatch">
                                        <input
                                            type="color"
                                            :value="asHex(color)"
                                            :title="color"
                                            :aria-label="`Color ${position + 1}`"
                                            @input="spec.values[position] = $event.target.value"
                                        >

                                        <button
                                            class="swatch-remove"
                                            type="button"
                                            title="Quitar color"
                                            aria-label="Quitar color"
                                            @click="spec.values.splice(position, 1)"
                                        >
                                            ×
                                        </button>
                                    </span>

                                    <button
                                        class="btn btn-outline btn-sm"
                                        type="button"
                                        @click="spec.values.push('#000000')"
                                    >
                                        Agregar color
                                    </button>
                                </div>
                            </FormField>

                            <FormField v-else label="Valor" :field-id="`spec-value-${index}`">
                                <input :id="`spec-value-${index}`" v-model="spec.value" class="input" type="text" maxlength="255">
                            </FormField>

                            <button
                                class="btn btn-ghost btn-icon"
                                type="button"
                                title="Quitar dato"
                                aria-label="Quitar dato"
                                @click="form.specs.splice(index, 1)"
                            >
                                <AppIcon name="trash" />
                            </button>
                        </div>
                    </div>
                </div>

                <div v-show="tab === 'extras'" class="tab-panel">
                    <div class="table-actions">
                        <button class="btn btn-outline btn-sm" type="button" @click="addBenefit">Agregar beneficio</button>
                        <button class="btn btn-outline btn-sm" type="button" @click="addBadge">Agregar etiqueta</button>
                    </div>

                    <div v-if="! form.benefits.length && ! form.badges.length" class="empty">
                        <p>Sin beneficios ni etiquetas.</p>
                    </div>

                    <div v-else class="form">
                        <div v-for="(benefit, index) in form.benefits" :key="`benefit-${index}`" class="form-row">
                            <FormField label="Beneficio" :field-id="`benefit-${index}`">
                                <input
                                    :id="`benefit-${index}`"
                                    maxlength="150"
                                    v-model="form.benefits[index]"
                                    class="input"
                                    type="text"
                                    placeholder="Envío gratis"
                                >
                            </FormField>

                            <button
                                class="btn btn-ghost btn-icon"
                                type="button"
                                title="Quitar beneficio"
                                aria-label="Quitar beneficio"
                                @click="form.benefits.splice(index, 1)"
                            >
                                <AppIcon name="trash" />
                            </button>
                        </div>

                        <div v-for="(badge, index) in form.badges" :key="`badge-${index}`" class="form-row">
                            <FormField label="Tipo" :field-id="`badge-type-${index}`">
                                <select :id="`badge-type-${index}`" v-model="badge.type" class="select">
                                    <option value="strong">Etiqueta</option>
                                    <option value="discount">Descuento</option>
                                </select>
                            </FormField>

                            <FormField label="Texto" :field-id="`badge-text-${index}`">
                                <input :id="`badge-text-${index}`" v-model="badge.text" class="input" type="text" maxlength="40" placeholder="-20%">
                            </FormField>

                            <FormField label="Detalle" :field-id="`badge-detail-${index}`">
                                <input :id="`badge-detail-${index}`" v-model="badge.detail" class="input" type="text" maxlength="40" placeholder="OFF">
                            </FormField>

                            <button
                                class="btn btn-ghost btn-icon"
                                type="button"
                                title="Quitar etiqueta"
                                aria-label="Quitar etiqueta"
                                @click="form.badges.splice(index, 1)"
                            >
                                <AppIcon name="trash" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="card">
            <header class="card-header">
                <div class="card-title">
                    <h2>Publicación</h2>
                </div>
            </header>

            <div class="card-body">
                <div class="form">
                    <label class="check">
                        <input v-model="form.visible" type="checkbox">
                        <span>Visible en el catálogo</span>
                    </label>

                    <label class="check">
                        <input v-model="form.featured" type="checkbox">
                        <span>Producto destacado</span>
                    </label>

                    <label class="check">
                        <input v-model="form.sold_out" type="checkbox">
                        <span>Agotado</span>
                    </label>
                </div>
            </div>

            <footer class="card-footer">
                <RouterLink class="btn btn-outline" :to="{ name: 'products' }">Cancelar</RouterLink>

                <div class="table-actions">
                    <button class="btn btn-outline" type="button" :disabled="loading" @click="submit(false)">
                        Guardar y volver
                    </button>

                    <button class="btn btn-primary" type="submit" :disabled="loading">
                        <span v-if="loading" class="btn-loader" />
                        <span>{{ loading ? 'Guardando…' : 'Guardar y seguir editando' }}</span>
                    </button>
                </div>
            </footer>
        </section>
    </form>

    <MediaPicker
        :open="pickerOpen"
        multiple
        title="Elegir imágenes del producto"
        @close="pickerOpen = false"
        @select="onPick"
    />
</template>
