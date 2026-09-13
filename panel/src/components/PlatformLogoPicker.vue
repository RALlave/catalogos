<script setup>
import { ref, watch } from 'vue'

import AppIcon from '@/components/AppIcon.vue'
import { api } from '@/services/api'

/**
 * Los logos que dejó el superadmin. El dueño sólo elige: al confirmar, la
 * imagen se copia a su biblioteca y queda como un logo propio.
 */
const props = defineProps({
    open: { type: Boolean, default: false },
})

const emit = defineEmits(['close', 'select'])

const items = ref([])
const loading = ref(false)
const selected = ref(null)

async function load() {
    loading.value = true

    try {
        items.value = (await api.get('/store-logos')).data
    } finally {
        loading.value = false
    }
}

function confirm() {
    if (! selected.value) {
        return
    }

    emit('select', selected.value)
    emit('close')
}

watch(() => props.open, (open) => {
    if (! open) {
        return
    }

    selected.value = null

    load()
})
</script>

<template>
    <Teleport to="body">
        <div v-if="open" class="modal" role="dialog" aria-modal="true" aria-label="Logos disponibles">
            <div class="modal-backdrop" @click="emit('close')" />

            <div class="modal-dialog modal-lg">
                <div class="modal-header">
                    <div class="modal-title">
                        <h2>Logos disponibles</h2>
                        <p>Elegí uno y queda como logo de tu tienda</p>
                    </div>

                    <button
                        class="btn btn-ghost btn-icon"
                        type="button"
                        title="Cerrar"
                        aria-label="Cerrar"
                        @click="emit('close')"
                    >
                        <AppIcon name="close" />
                    </button>
                </div>

                <div class="modal-body">
                    <div v-if="loading" class="empty">
                        <p>Cargando…</p>
                    </div>

                    <div v-else-if="! items.length" class="empty">
                        <span class="empty-icon">
                            <AppIcon name="image" />
                        </span>
                        <p>Todavía no hay logos para elegir.</p>
                    </div>

                    <div v-else class="media-grid">
                        <button
                            v-for="logo in items"
                            :key="logo.id"
                            class="media-card"
                            :class="{ 'is-picked': selected?.id === logo.id }"
                            type="button"
                            @click="selected = logo"
                        >
                            <span class="media-card-image">
                                <img :src="logo.thumb_url" :alt="logo.name">
                            </span>

                            <span class="media-card-body">
                                <strong>{{ logo.name }}</strong>
                                <span>{{ logo.width }}×{{ logo.height }}</span>
                            </span>

                            <span v-if="selected?.id === logo.id" class="media-card-check">
                                <AppIcon name="check" />
                            </span>
                        </button>
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="page-info">
                        <span v-if="items.length">{{ items.length }} logos</span>
                    </div>

                    <div class="modal-actions">
                        <button class="btn btn-outline" type="button" @click="emit('close')">Cancelar</button>
                        <button
                            class="btn btn-primary"
                            type="button"
                            :disabled="! selected"
                            @click="confirm"
                        >
                            Usar logo
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
