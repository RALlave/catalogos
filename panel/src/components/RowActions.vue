<!--
    Actions column of a table row. Each action is declared once and rendered
    twice: loose buttons on desktop and a "⋮" menu on mobile.

    Action: { label, icon?, to?, href?, onClick?, danger?, disabled?, loading? }
    Without an icon, the desktop button shows its label instead.
-->

<script setup>
import { RouterLink } from 'vue-router'

import AppDropdown from '@/components/AppDropdown.vue'
import AppIcon from '@/components/AppIcon.vue'

defineProps({
    actions: {
        type: Array,
        required: true,
    },
})

function tagOf(action) {
    if (action.to) {
        return RouterLink
    }

    return action.href ? 'a' : 'button'
}

function attrsOf(action) {
    if (action.to) {
        return { to: action.to }
    }

    if (action.href) {
        return { href: action.href, target: '_blank', rel: 'noopener' }
    }

    return { type: 'button', disabled: action.disabled || action.loading }
}

function run(action) {
    if (action.onClick && ! action.disabled && ! action.loading) {
        action.onClick()
    }
}
</script>

<template>
    <div class="row-actions">
        <div class="table-actions row-actions-inline">
            <component
                :is="tagOf(action)"
                v-for="action in actions"
                :key="action.label"
                v-bind="attrsOf(action)"
                class="btn"
                :class="action.icon
                    ? 'btn-ghost btn-icon'
                    : ['btn-sm', action.danger ? 'btn-danger' : 'btn-ghost']"
                :title="action.icon ? action.label : null"
                :aria-label="action.icon ? action.label : null"
                @click="run(action)"
            >
                <span v-if="action.loading" class="btn-loader" />
                <AppIcon v-else-if="action.icon" :name="action.icon" />
                <template v-else>{{ action.label }}</template>
            </component>
        </div>

        <AppDropdown class="row-actions-menu">
            <template #trigger="{ toggle, open }">
                <button
                    class="btn btn-ghost btn-icon"
                    type="button"
                    title="Acciones"
                    aria-label="Acciones"
                    aria-haspopup="menu"
                    :aria-expanded="open"
                    @click="toggle"
                >
                    <AppIcon name="dotsVertical" />
                </button>
            </template>

            <div
                v-for="action in actions"
                :key="action.label"
                class="dropdown-item"
                :class="{ 'is-danger': action.danger }"
            >
                <component
                    :is="tagOf(action)"
                    v-bind="attrsOf(action)"
                    @click="run(action)"
                >
                    <span v-if="action.loading" class="btn-loader" />
                    <AppIcon v-else-if="action.icon" :name="action.icon" />
                    <span>{{ action.label }}</span>
                </component>
            </div>
        </AppDropdown>
    </div>
</template>
