<!--
    ACCESO AL PANEL — sólo para el dueño que tiene la sesión abierta.

    El panel deja la cookie `dash_session` mientras hay sesión (ver
    `panel/src/services/api.js`). Es cookie y no localStorage porque no
    distingue puertos: en desarrollo el catálogo (:3000) y el panel
    (:5173) son orígenes distintos. Basta con que exista: si el token
    venció, /admin manda al login.

    Como el servidor también ve la cookie, el botón llega en el HTML.
-->

<script setup lang="ts">
const session = useCookie('dash_session')

/* En producción el panel vive en el mismo origen y alcanza con /admin.
   En desarrollo corre en otro puerto (`NUXT_PUBLIC_PANEL_PORT`). */
const { panelPort } = useRuntimeConfig().public
const url = useRequestURL()

const href = computed(() => panelPort
    ? `${url.protocol}//${url.hostname}:${panelPort}/admin`
    : '/admin')
</script>

<template>
    <div v-if="session" class="panel-link">
        <a :href="href">Mi panel</a>
    </div>
</template>
