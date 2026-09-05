# Handoff — proyecto CATÁLOGOS

SaaS gratuito de catálogo para emprendedores: crean su catálogo y lo comparten
por enlace o WhatsApp. No es e-commerce (sin carrito ni pagos en el MVP).

Documento maestro: `CLAUDE.md`. Despliegue: `DEPLOY.md`. Notas por tema:
`memory/` con su índice en `memory/MEMORY.md`.

---

## Monorepo

```
proyecto CATALOGOS/
├── api/          Laravel — toda la lógica de negocio
├── web/          Nuxt 4 SSR — catálogo público
├── panel/        Vue 3 SPA — panel dueño + superadmin
├── landing/      HTML/CSS/JS vanilla — landing comercial del SaaS
├── prototipo-3/  maqueta estática de referencia (CONGELADA, no se toca)
├── deploy/       nginx.conf, ecosystem PM2, .env de producción de ejemplo
└── memory/       notas del proyecto
```

## Versiones

| Capa | Tecnología |
|---|---|
| Backend | PHP 8.3 · Laravel 13.8 · Sanctum 4 (tokens Bearer) |
| Permisos | spatie/laravel-permission 8.3 — solo roles: `superadmin`, `store_owner` |
| Imágenes | intervention/image 4.3 sobre GD → WebP |
| BD | MySQL 8 (`base_catalogos`; tests en `base_catalogos_testing`) |
| Público | Nuxt 4.5 · Vue 3.5 · vue-router 4 — SSR, sin Pinia (`useFetch` alcanza) |
| Panel | Vue 3.5 · Vite 7 · Pinia 3 · vue-router 4 · Chart.js 4 · vuedraggable |
| Runtime | Node 20 · PM2 (solo Nuxt necesita proceso) · nginx |

## Arquitectura

Un solo dominio, tres aplicaciones:

```
https://dominio.com/{tienda}   catálogo   → Nuxt SSR en 127.0.0.1:3000 (PM2)
https://dominio.com/panel      panel      → dist/ estático
https://dominio.com/api        API        → PHP-FPM
```

Toda la lógica vive en Laravel. Nuxt y la SPA consumen la misma API REST.
Capas: Controller → Form Request → Policy → Service → Resource.

El panel es **una sola aplicación para los dos perfiles**: un único login
(`POST /api/login`) y el router decide por el array `roles` — `store_owner` va
a `/`, `superadmin` va a `/admin`.

## Modelo de datos

`users` → `stores` (una por usuario) → `categories`, `products`, `media`,
`heroes`, `store_stats`, `orders`, `waitlist_entries`. Todo lleva `store_id`.

- `media` es la biblioteca central: `product_images` es un pivot y
  `stores.logo_media_id` / `cover_media_id` la referencian
- `products.specs` se llama así y no `attributes` (reservado por Eloquent);
  `is_new` y no `new` (palabra reservada de PHP)
- `store_stats` es el acumulado por día, no un evento por visita

---

## Estado

Repositorio: `https://github.com/RALlave/catalogos` (privado, rama `main`).

Sin desplegar todavía. El VPS es de Hostinger y el dominio está por comprarse.

## Deploy

El procedimiento completo está en `DEPLOY.md`, escrito para `diseprog.com`.

Sin dominio se puede instalar todo con la IP del VPS; después hay que cambiar
cinco variables y **recompilar panel y web** (esas URLs se hornean en el build,
no se leen en runtime), más `server_name` en el nginx y certbot:

- `APP_URL` y `FRONTEND_URL` en `api/.env`
- `VITE_API_BASE` en `panel/.env`
- `NUXT_PUBLIC_API_BASE` y `NUXT_PUBLIC_SITE_URL` en `web/.env`

### Trampas que ya costaron

- `VITE_BASE=/panel/` tiene que estar **antes** del build o el panel queda en blanco
- Sin `php artisan storage:link` no se ve ninguna imagen
- `APP_URL` termina en `/api`
- Los slugs `api`, `panel`, `admin`, `storage`, `login`… están reservados en
  `api/config/catalog.php`: ninguna tienda puede llamarse así, porque las
  tiendas viven en la raíz del dominio
- Nuxt reenvía `X-Forwarded-For` y `User-Agent` del visitante a la API; sin eso
  todas las visitas se cuentan como una sola

## Deuda técnica abierta

- La **caché de los endpoints públicos está apagada** por un bug: rompe heroes y
  categories y borra el banner. Revisarlo antes de encenderla en producción
- Tests: 41 de scoping multi-tenant sobre la API; el resto de los frentes sin cobertura
- Sin verificación de email, sin rate limit configurado, sin auditoría de impersonación
- `orders` y `waitlist_entries` están implementados pero **no documentados** en `CLAUDE.md`
- Planes y moderación del superadmin son maqueta sin backend

## Reglas duras

Están en `CLAUDE.md` y en `prototipo-3/CLAUDE.md`:

- Ningún hexadecimal dentro de una regla de componente: los colores salen de
  `palette.css` / `:root`
- Custom properties CSS solo para color, y siempre en `:root`
- Todo el código en inglés; el chat en español
- `maxlength` del input = `max:` de la validación = tamaño de la columna
- Nunca fallbacks de contenido: si el dato está vacío, se renderiza vacío
