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
├── deploy/       service de systemd, bloques de vhost, .env de producción de ejemplo
└── memory/       notas del proyecto
```

## Versiones

| Capa | Tecnología |
|---|---|
| Backend | PHP 8.3 · Laravel 13.8 · Sanctum 4 (tokens Bearer) |
| Permisos | spatie/laravel-permission 8.3 — solo roles: `superadmin`, `store_owner` |
| Imágenes | intervention/image 4.3 sobre GD → WebP |
| BD | MySQL 8 (local `base_catalogos`, producción `catalogos`, tests `base_catalogos_testing`) |
| Público | Nuxt 4.5 · Vue 3.5 · vue-router 4 — SSR, sin Pinia (`useFetch` alcanza) |
| Panel | Vue 3.5 · Vite 7 · Pinia 3 · vue-router 4 · Chart.js 4 · vuedraggable |
| Runtime | Node 20 · systemd (solo Nuxt necesita proceso) · nginx vía CloudPanel |

## Arquitectura

Una tienda por subdominio:

```
https://dominio.com                 landing + login    → landing/ y panel/dist
https://dominio.com/superadmin      panel superadmin   → panel/dist
https://{tienda}.dominio.com        catálogo           → Nuxt SSR en 127.0.0.1:3000 (systemd)
https://{tienda}.dominio.com/admin  panel de la tienda → panel/dist
https://api.dominio.com             API                → PHP-FPM
```

El slug de la tienda **es su subdominio**: Nuxt lo saca del header `Host`
(`web/app/composables/useStoreHost.ts`), no de la ruta.

Toda la lógica vive en Laravel. Nuxt y la SPA consumen la misma API REST.
Capas: Controller → Form Request → Policy → Service → Resource.

El panel es **una sola aplicación para los dos perfiles**: un único login
(`POST /api/login`) y el router decide por el array `roles` — `store_owner` va
a `/admin`, `superadmin` a `/superadmin`. Sus rutas viven en la raíz junto a
las del catálogo; sus archivos cuelgan de `/panel/` (`VITE_BASE`).

Cada subdominio es un origen aparte y **no comparte `localStorage`**: para
llevar una sesión de uno a otro —el dueño que entró por el dominio principal, o
el superadmin que entra a una tienda— se usa un código de un solo uso de 60s
(`App\Services\SessionHandoff`). Por eso `CACHE_STORE` no puede ser `array`.

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

Sin desplegar todavía. El VPS es de Hostinger y el dominio se compró en
Cloudflare.

El VPS **no está vacío**: comparte servidor con otros cuatro sitios en
producción y lo administra **CloudPanel**, así que el código va en
`/home/{site-user}/htdocs/{dominio}` y los vhosts los genera el panel. Los tres
sitios, el DNS y el SSL ya están creados.

## Deploy

El procedimiento completo está en `DEPLOY.md`, escrito con `miotienda.com` de
ejemplo. La arquitectura de subdominios **necesita un dominio**: no se puede
instalar con la IP pelada, porque no hay subdominios que resolver.

Al cambiar de dominio hay que tocar seis variables y **recompilar panel y web**
(esas URLs se hornean en el build, no se leen en runtime), más los tres
`server_name` del nginx:

- `APP_URL` y `FRONTEND_URL` en `api/.env`
- `VITE_API_BASE` y `VITE_BASE_DOMAIN` en `panel/.env`
- `NUXT_PUBLIC_API_BASE` y `NUXT_PUBLIC_BASE_DOMAIN` en `web/.env`

`FRONTEND_URL` es el dominio pelado y hace tres cosas a la vez: arma la URL del
catálogo de cada tienda, es el destino del correo de recuperación y de ahí sale
el patrón de CORS.

### Trampas que ya costaron

- `VITE_BASE=/panel/` tiene que estar **antes** del build o el panel queda en
  blanco. Es de dónde cuelgan los archivos, no las rutas
- `CACHE_STORE=array` rompe el salto entre subdominios: el código de un solo
  uso se pierde entre una petición y la siguiente
- Sin `php artisan storage:link` no se ve ninguna imagen
- `APP_URL` es el subdominio pelado, **sin `/api`**: el disco público arma las
  URLs de las fotos como `APP_URL + /storage`
- Los subdominios `api`, `www`, `mail`, `panel`… están reservados en
  `api/config/catalog.php`: ninguna tienda puede llamarse así
- Nuxt reenvía `X-Forwarded-For` y `User-Agent` del visitante a la API; sin eso
  todas las visitas se cuentan como una sola
- Detrás de Cloudflare hace falta el bloque `real_ip` con `CF-Connecting-IP`, o
  la IP que llega a las estadísticas la escribe el visitante
- Cloudflare va en **Full (strict)** y con la nube naranja, o no hay
  certificado wildcard
- En el VPS, `php` es 8.4 y el sitio de la API corre con 8.3: los comandos de
  Laravel van con `php8.3` explícito
- Clonar el repo como el site user y no como root, o PHP-FPM no puede escribir
  en `storage/`

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
