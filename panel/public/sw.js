/*
 * Service worker del panel.
 *
 * No cachea nada a propósito: está sólo porque el navegador todavía lo pide
 * para ofrecer la instalación en el escritorio. El panel es una SPA que se
 * despliega seguido, y una caché acá significaría usuarios con una versión
 * vieja hasta que limpien el navegador.
 *
 * El handler de fetch no responde: deja que el pedido siga su camino normal.
 */
self.addEventListener('install', () => self.skipWaiting())

self.addEventListener('activate', event => event.waitUntil(self.clients.claim()))

self.addEventListener('fetch', () => {})
