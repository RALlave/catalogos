<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'

import AppIcon from '@/components/AppIcon.vue'
import { api } from '@/services/api'
import { useCategoriesStore } from '@/stores/categories'

const props = defineProps({
    /* Superadmin searches stores and users; a store owner, products and categories. */
    admin: { type: Boolean, default: false },
})

const MIN_LENGTH = 2
const LIMIT = 5

const router = useRouter()
const categories = useCategoriesStore()

const root = ref(null)
const query = ref('')
const open = ref(false)
const loading = ref(false)
const groups = ref([])
const active = ref(-1)

/* Only the latest request may paint results: a slow earlier one would overwrite them. */
let requestId = 0
let timer

const placeholder = computed(() => (props.admin ? 'Buscar tiendas, usuarios…' : 'Buscar productos, categorías…'))

const flat = computed(() => groups.value.flatMap(group => group.items))

const hasQuery = computed(() => query.value.trim().length >= MIN_LENGTH)

function normalize(text) {
    return String(text ?? '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase()
}

async function searchOwner(term) {
    const [products] = await Promise.all([
        api.get('/products', { search: term }),
        categories.loaded ? null : categories.fetch(),
    ])

    const needle = normalize(term)

    return [
        {
            key: 'products',
            title: 'Productos',
            items: products.data.slice(0, LIMIT).map(product => ({
                key: `product-${product.id}`,
                label: product.name,
                detail: product.category?.name ?? '',
                icon: 'box',
                to: { name: 'product-edit', params: { id: product.id } },
            })),
        },
        {
            key: 'categories',
            title: 'Categorías',
            items: categories.items
                .filter(category => normalize(category.name).includes(needle))
                .slice(0, LIMIT)
                .map(category => ({
                    key: `category-${category.id}`,
                    label: category.name,
                    detail: `${category.products_count ?? 0} productos`,
                    icon: 'folder',
                    to: { name: 'category-edit', params: { id: category.id } },
                })),
        },
    ]
}

async function searchAdmin(term) {
    const [stores, users] = await Promise.all([
        api.get('/admin/stores', { search: term, per_page: LIMIT }),
        api.get('/admin/users', { search: term, per_page: LIMIT }),
    ])

    return [
        {
            key: 'stores',
            title: 'Tiendas',
            items: stores.data.map(store => ({
                key: `store-${store.id}`,
                label: store.name,
                detail: store.slug,
                icon: 'store',
                to: { name: 'admin-store-edit', params: { id: store.id } },
            })),
        },
        {
            key: 'users',
            title: 'Usuarios',
            items: users.data.map(user => ({
                key: `user-${user.id}`,
                label: user.name,
                detail: user.email,
                icon: 'user',
                to: { name: 'admin-users', query: { search: user.email } },
            })),
        },
    ]
}

async function run() {
    const term = query.value.trim()
    const id = ++requestId

    loading.value = true

    try {
        const result = props.admin ? await searchAdmin(term) : await searchOwner(term)

        if (id !== requestId) {
            return
        }

        groups.value = result.filter(group => group.items.length)
        active.value = -1
    } catch {
        if (id === requestId) {
            groups.value = []
        }
    } finally {
        if (id === requestId) {
            loading.value = false
        }
    }
}

watch(query, () => {
    clearTimeout(timer)

    if (! hasQuery.value) {
        requestId++
        groups.value = []
        loading.value = false

        return
    }

    open.value = true
    timer = setTimeout(run, 300)
})

function go(item) {
    open.value = false
    query.value = ''
    router.push(item.to)
}

function onKeydown(event) {
    if (event.key === 'Escape') {
        open.value = false

        return
    }

    if (! flat.value.length) {
        return
    }

    if (event.key === 'ArrowDown') {
        event.preventDefault()
        open.value = true
        active.value = (active.value + 1) % flat.value.length
    } else if (event.key === 'ArrowUp') {
        event.preventDefault()
        active.value = active.value <= 0 ? flat.value.length - 1 : active.value - 1
    } else if (event.key === 'Enter') {
        event.preventDefault()
        go(flat.value[Math.max(active.value, 0)])
    }
}

function onDocumentClick(event) {
    if (root.value && ! root.value.contains(event.target)) {
        open.value = false
    }
}

onMounted(() => document.addEventListener('click', onDocumentClick))

onBeforeUnmount(() => {
    clearTimeout(timer)
    document.removeEventListener('click', onDocumentClick)
})
</script>

<template>
    <div ref="root" class="global-search">
        <div class="search topbar-search">
            <AppIcon name="search" />
            <input
                v-model="query"
                class="input"
                type="search"
                :placeholder="placeholder"
                aria-label="Buscar"
                maxlength="255"
                autocomplete="off"
                @focus="open = hasQuery"
                @keydown="onKeydown"
            >
        </div>

        <div v-if="open && hasQuery" class="global-search-panel" role="listbox">
            <div v-if="loading && ! groups.length" class="global-search-state">Buscando…</div>

            <div v-else-if="! groups.length" class="global-search-state">Sin resultados para "{{ query.trim() }}"</div>

            <template v-else>
                <div v-for="group in groups" :key="group.key" class="global-search-group">
                    <span class="global-search-title">{{ group.title }}</span>

                    <button
                        v-for="item in group.items"
                        :key="item.key"
                        class="global-search-item"
                        :class="{ 'is-active': flat[active]?.key === item.key }"
                        type="button"
                        role="option"
                        @click="go(item)"
                        @mouseenter="active = flat.indexOf(item)"
                    >
                        <AppIcon :name="item.icon" />
                        <span class="global-search-text">
                            <span class="global-search-label">{{ item.label }}</span>
                            <span v-if="item.detail" class="global-search-detail">{{ item.detail }}</span>
                        </span>
                    </button>
                </div>
            </template>
        </div>
    </div>
</template>
