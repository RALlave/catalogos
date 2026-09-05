## Regla: anotar detalles importantes del proyecto

Todo detalle importante del proyecto debe quedar anotado para recordarlo siempre.

Se anota **siempre** que aparezca:

- Una decisión de arquitectura, stack o diseño (y el porqué)
- Un acuerdo o cambio de alcance (qué entra y qué queda fuera)
- Una convención propia del proyecto (naming, estructura, endpoints, respuestas)
- Un pendiente, deuda técnica o bloqueo conocido
- Algo no obvio que no se deduzca leyendo el código

Dónde se anota:

- **Este `CLAUDE.md`** → lo estructural y permanente: plan, fases, stack, convenciones y reglas.
- **`memory/` del proyecto** → una memoria por tema, con su puntero en `MEMORY.md`.

Antes de crear una nota nueva, revisar si ya existe una del mismo tema y actualizarla en vez de duplicar.

No anotar lo que ya se deduce del código, del historial de git o de este archivo.

## Rol del asistente de desarrollo

Eres un Arquitecto de Software Senior y Desarrollador Full Stack con más de 15 años de experiencia.

Dominas:

Laravel
PHP
Vue 3
Nuxt 4
Pinia
Vue Router
MySQL
REST APIs
Clean Architecture
SOLID
Clean Code
Domain Driven Design (cuando sea necesario)
Patrones de Diseño
Optimización de consultas SQL
Seguridad Web
SEO
SSR
UX/UI

Tu objetivo no es únicamente escribir código, sino construir un SaaS profesional, mantenible y escalable.

## Proyecto

El proyecto consiste en desarrollar un SaaS de catálogo para emprendedores.

# Plan de desarrollo — SaaS de Catálogo para Emprendedores

## Objetivo

Desarrollar un SaaS gratuito para que emprendedores puedan crear un catálogo comercial y compartirlo fácilmente mediante un enlace o WhatsApp.

El objetivo inicial NO es competir con Shopify o WooCommerce, sino ofrecer una solución simple y rápida. Posteriormente se utilizará como canal de captación para ofrecer migraciones hacia plataformas de e-commerce más completas.

## ANTECEDENTES

La idea es sólida y el nicho es real: mucha gente vende por WhatsApp/Facebook y su "catálogo" hoy son fotos sueltas en un estado. Un link único, ordenado y compartible es un salto grande para ellos con costo casi cero.

## Stack tecnológico

### Backend

- Laravel (última versión estable)
- API REST
- Laravel Sanctum
- MySQL

### Frontend público

- Nuxt 4 (se arrancó con la 4.x, que es la estable; la 3.x quedó en mantenimiento)
- Vue 3
- Pinia (todavía no hace falta: el catálogo público se resuelve con `useFetch`)

Objetivo:

- SEO
- SSR
- Excelente rendimiento

### Panel administrativo

- Vue 3 SPA
- Pinia
- Vue Router

Objetivo:

- Experiencia de usuario rápida
- Administración completa del catálogo

## Arquitectura

Separar completamente el proyecto en tres aplicaciones.

```
Laravel API
     ↓
Nuxt (catálogo público)
     ↓
Vue SPA (panel administrativo)
```

Toda la lógica de negocio debe residir únicamente en Laravel.

Nuxt y la SPA consumirán exactamente la misma API.

## Prototipo

En `prototipo-3/` está el template estático (HTML/CSS/JS vanilla) que define la
**estructura, el maquetado y el diseño** del catálogo público. Es la única
maqueta vigente: las dos iteraciones anteriores (`prototipo/` y `prototipo-2/`)
se eliminaron el 2026-08-29 y quedaron solo en el historial de git.

Tiene su propio `CLAUDE.md` con las reglas de su sistema de diseño (las tres
capas del CSS, tokens, las 12 paletas con sus ratios WCAG, breakpoints, mejora
progresiva). **Leerlo antes de tocar cualquier archivo de esa carpeta.**

Es la referencia visual del catálogo de la Fase 5, ya portada a `web/`: el
markup y los componentes CSS estaban pensados para eso, con los datos
desacoplados del markup y la identidad visual entera en variables CSS.

## Fase 1 — Base del proyecto

Crear:

- Autenticación
- Registro
- Login
- Recuperación de contraseña
- Laravel Sanctum

Crear estructura base de:

- Usuarios
- Tiendas
- Categorías
- Productos

## Fase 2 — Tiendas

Cada usuario podrá crear una tienda.

Datos:

- Nombre
- Slug único
- Logo
- Portada
- Descripción
- Rubro
- WhatsApp
- Teléfono
- Email
- Facebook
- Instagram
- TikTok
- Sitio web
- Dirección
- Enlace de mapa
- Ciudad
- País
- Moneda (setting global de la tienda, no por producto)
- Horarios de atención (lista de `{días, horas}`)

Cada tienda tendrá una URL pública.

Ejemplo:

```
catalogo.com/mitienda
```

## Fase 3 — Categorías

CRUD completo.

Campos:

- Nombre
- Slug
- Descripción
- Orden
- Estado

## Fase 4 — Productos

CRUD completo.

Campos:

- Nombre
- Slug
- SKU / código (opcional, lo escribe el usuario)
- Descripción
- Ficha técnica (`specs`): lista de `{label, value}`; con `type: "colors"` y `values` para muestras de color
- Beneficios: lista de textos
- Badges: lista de `{type, text, detail}` — `type` es `discount` o `strong`
- Precio
- Precio oferta
- Categoría
- Imágenes
- Producto destacado
- Visible
- Agotado (`sold_out`)
- Nuevo (`is_new`)
- Orden

`specs`, `benefits` y `badges` se guardan como columnas JSON. La columna se llama `specs` y no `attributes` porque ese nombre está reservado por Eloquent.

La moneda no vive en el producto: es un setting de la tienda.

No implementar inventario en esta etapa. `sold_out` es una marca manual, no un stock.

`is_new` también es una marca manual: nada la deriva de `created_at`, porque la
carga inicial de un catálogo dejaría todos los productos "nuevos" a la vez. Se
llama `is_new` y no `new` porque esa es palabra reservada de PHP.

### Qué se dibuja sobre la foto

Un solo distintivo por producto, y en este orden: **agotado**, **oferta**,
**nuevo**. "Destacado" **ya no se dibuja** (2026-08-29): el campo `featured`
sigue vivo y ordena el catálogo y cuenta en el dashboard, pero no muestra nada
al visitante.

"Oferta" y "nuevo" no son badges rectangulares sino **cintas diagonales en la
esquina superior izquierda**, hechas en CSS puro para que el color siga saliendo
de la paleta. La geometría vive en `.badge-ribbon` y el color en `.badge-sale` /
`.badge-new`. Van sin ícono: en la diagonal no entra.

La oferta **no tiene campo propio**: es tener `sale_price` menor que `price`, y
la cinta muestra el porcentaje calculado (`discountPercent()` en `useFormat.ts`,
compartido con el badge de la ficha). Sólo se dibuja en la tarjeta de la grilla:
en la ficha el descuento ya se lee en el precio anterior tachado más el badge
del porcentaje, así que ahí la cadena es agotado > nuevo.

La tarjeta de la grilla muestra el precio actual y, si hay oferta, el anterior
tachado. El badge del porcentaje sigue siendo sólo de la ficha.

## Fase 5 — Catálogo público

Construir en Nuxt.

Debe incluir:

- Página principal
- Categorías
- Listado de productos
- Detalle del producto
- Buscador
- Compartir producto
- Botón WhatsApp
- SEO automático
  - Meta Title
  - Meta Description
  - Open Graph
  - URLs amigables

Vive en `web/`, separado de `api/`. Estructura de URLs:

```
/                          landing del SaaS
/{tienda}                  catálogo        (?cat= &q= &page=)
/{tienda}/producto/{slug}  detalle
/{tienda}/buscar           resultados de búsqueda (?q= &cat= &page=)
/{tienda}/contacto         contacto
```

El catálogo es el diseño de `prototipo-3/`, portado el 2026-08-28. Ya no hay
cuatro layouts: es **un solo diseño configurable** y lo que cambia es la paleta
(12) más tres opciones de forma, que viajan como atributos del `<html>`:
`data-radius` (square/round), `data-nav` (dark/color) y `data-banner`
(dark/light). El layout de tienda inyecta los colores de la paleta como un
`<style>` con `:root`.

El CSS se copió de la maqueta y se carga en este orden: `base.css` (forma,
tipografía, responsive; ni un hexadecimal), `palette.css` (colores) y
`components.css` (componentes, sólo `var()`). Vale la misma regla dura que en
`prototipo-3/CLAUDE.md`: **ningún hexadecimal dentro de una regla de
componente**. La fuente de verdad de los colores sigue siendo
`prototipo-3/assets/css/paletas.css`; de ahí salen los de `config/themes.php`.

Filtro, búsqueda y paginación son la URL (`?cat=`, `?q=`, `?page=`), no estado
del cliente: andan sin JS, se comparten y los indexa el buscador.

El banner del home es un **carrusel de heros administrables** (ver más abajo).
Lo que sigue fijo en el código son los títulos de las pestañas de producto y los
dos botones del hero: "Ver catálogo" (va al listado) y "Pedir por WhatsApp" (se
arma con el número de la tienda).

## Hero (banner) del home

El banner dejó de ser un bloque fijo: la tienda carga **hasta 10 heros** en la
tabla `heroes` (`store_id`, `media_id`, `eyebrow`, `title`, `text`, `order`,
`active`) y el catálogo los rota. Cada hero tiene imagen y tres textos —
**eyebrow** (el texto chico de arriba; se llama así, no *kicker*), título y
texto—; los botones no se editan.

- El orden se arrastra en el panel, con el mismo `reorder` en lote que
  categorías y productos.
- Cómo pasa de uno a otro es un setting de la tienda: `hero_effect`, `slide` o
  `fade`. Los valores válidos están en `config/catalog.php` y **no** en
  `config/themes.php`, porque no es una opción de tema: se elige en la pantalla
  Hero (banner), no en Apariencia.
- La imagen sale de la biblioteca. Borrar una media no borra el hero: lo deja
  sin foto, y la biblioteca avisa antes nombrando los heros afectados.
- Sin heros cargados el banner no se renderiza: el catálogo abre en los
  productos. No hay textos de reserva.
- El banner corto de **Contacto** usa la foto de **un hero al azar** entre los
  que tienen imagen (ya no la portada). El sorteo va en un `useState`, para que
  el servidor y el cliente saquen el mismo número y la foto no parpadee al
  hidratar.
- En el catálogo, el primer hero se renderiza en el servidor (se ve sin JS) y
  las flechas y los puntos son `<ClientOnly>`. Pasa solo cada 6 segundos, se
  frena al pasar el mouse o al usar los controles, y no se mueve si el visitante
  pidió menos movimiento.

Endpoints: `GET|POST /api/heroes`, `GET|PUT|DELETE /api/heroes/{hero}` y
`POST /api/heroes/reorder`.

## SEO de la tienda

El SEO es independiente del hero: `stores.meta_title` (60) y
`stores.meta_description` (160), más la **portada** (`cover_media_id`), que ya no
es la foto del banner y quedó solo como imagen para compartir en redes. Si están
vacíos, el catálogo sigue armando el título y la descripción con el nombre y la
descripción de la tienda. Se editan en la pantalla SEO del panel.

## Fase 6 — Panel administrativo

Dashboard simple.

Menú:

- Dashboard
- Productos
- Categorías
- Configuración
- Perfil

### Dashboard

Mostrar:

- Cantidad de productos
- Cantidad de categorías
- Productos destacados
- Últimos productos

### Configuración

Editar:

- Logo (en Mi tienda → Información)
- Apariencia: paleta de colores y las tres opciones de forma
- Información de contacto
- Redes sociales
- Hero (banner): los heros del home y el efecto del carrusel
- SEO: meta title, meta description e imagen para compartir

La apariencia se elige de una lista, no se arma a mano: las paletas y las
opciones viven en `api/config/themes.php` y se consultan con `GET /api/themes`.
La tienda guarda solo las claves (`palette`, `radius`, `nav`, `banner`), así que
retocar una paleta ahí actualiza a todas las tiendas que la usan. Agregar una
paleta nueva es agregar un bloque a ese archivo.

### Dónde vive

SPA en `panel/` (Vite + Vue 3 + Pinia + Vue Router), hermana de `api/` y `web/`.
Es **una sola aplicación para los dos perfiles**: un único login (`POST /api/login`)
y el router decide por el array `roles` que devuelve la respuesta —
`store_owner` va a `/`, `superadmin` va a `/admin`.

El token de Sanctum se guarda en `localStorage` y viaja como Bearer.

### Entrar al panel de una tienda (impersonación)

Desde el listado de tiendas y desde Editar tienda, el superadmin puede entrar al
panel de una tienda **como su dueño**, para dar soporte. Es acceso completo: lo
que se edite queda a nombre del dueño.

`POST /api/admin/stores/{store}/impersonate` devuelve un token del dueño. El
panel guarda el token del superadmin en `dash.admin_token` y el nombre de la
tienda en `dash.impersonated_store`, así la sesión sobrevive a un F5; mientras
dura, el layout muestra una barra con "Volver a superadmin". Al volver, el token
del dueño se revoca (`POST /api/logout`) y se restaura el del superadmin.

No se puede impersonar a otro superadmin, y el token impersonado no entra a
`/api/admin` (el middleware de rol lo corta con 403). **No hay auditoría**: nada
registra quién impersonó a quién.

### Fuera de esta fase

`planes` y `moderación` del prototipo superadmin quedan sin conectar: no tienen
tablas ni endpoints, y suscripciones/facturación están fuera del MVP. La
verificación de email tampoco se implementa todavía.

## Estadísticas del catálogo

Los tres gráficos y el número de visitas del dashboard dejaron de ser datos de
ejemplo. Se miden tres cosas: **visita** al catálogo, **vista** de un producto y
**compartido** de un producto.

No se guarda un evento por visita: la tabla `store_stats` (`store_id`,
`product_id`, `type`, `date`, `count`) es el acumulado por día, con una fila por
tienda, tipo, producto y día. `product_id` en `null` es el dato de la tienda
entera. La tabla queda chica para siempre y no hay nada que limpiar.

Quién cuenta qué:

- **visita** y **vista de producto** las cuenta la API al servir
  `GET /stores/{slug}` y `GET /stores/{slug}/products/{producto}`. El conteo va
  **fuera** del `remember` de `CatalogCache`: adentro sólo correría la primera
  vez y las respuestas cacheadas no se contarían.
- **compartido** llega por `POST /api/stores/{slug}/track` (`type` + `product_slug`),
  porque es un clic del visitante que la API no ve. Ese endpoint **sólo acepta
  `share`**: si aceptara `visit`, cualquiera podría inflar el número de otra
  tienda. Responde 204 siempre, incluso si la tienda no existe.

El mismo visitante cuenta **una vez por día**, por tienda y por producto. Se
identifica con un hash de IP y user agent que vive en la caché hasta que termina
el día: no se guarda ninguna de las dos cosas. Los bots y las peticiones sin
user agent no cuentan.

Como el catálogo se renderiza en el servidor, la petición sale del servidor de
Nuxt: `useCatalog.ts` reenvía la IP y el user agent del visitante (`X-Forwarded-For`
y `User-Agent`). Sin eso todas las visitas serían la misma y no se contaría
ninguna. `StatService` lee el **primer** valor de `X-Forwarded-For`, que es el
visitante real cuando nginx va agregando la cadena.

El dashboard muestra los **últimos 30 días** (`StatService::PERIOD`) y la
variación contra los 30 anteriores; sin período anterior la variación es `null`
y no se dibuja el badge. El del superadmin usa el mismo período para "Tiendas más
visitadas". Sin datos, los gráficos no se dibujan: va un mensaje, porque un
gráfico en cero parece un error.

## Multimedia — biblioteca de imágenes

Todas las imágenes de una tienda viven en la tabla `media` (`store_id`, `path`,
`variants`, `name`, `alt`, `mime`, `size`, `width`, `height`). Nada guarda un
archivo por su cuenta:

- `product_images` es un pivot (`product_id` + `media_id` + `order`): la misma
  imagen puede estar en varios productos.
- `stores.logo_media_id` y `stores.cover_media_id` reemplazan a las viejas
  columnas `logo` y `cover`.

El JSON no cambió de forma: `StoreResource` sigue devolviendo `logo`, `logo_url`,
`cover` y `cover_url`, ahora derivados de la relación.

Consecuencias del modelo compartido:

- Sacar una imagen de un producto **no borra el archivo**: solo suelta la
  referencia. El archivo se borra desde la biblioteca.
- Clonar un producto **no duplica archivos**: el clon comparte las mismas media.
- Borrar una media borra el archivo y cae en cascada: desaparece de todos los
  productos y deja el logo o la portada en `null`. Por eso `MediaResource`
  devuelve `used_by` (los **nombres** de los productos que la usan) y los flags
  `used_as_logo` / `used_as_cover`: el panel avisa a quién afecta antes de
  borrar.

Endpoints: `GET|POST /api/media`, `GET|PUT|DELETE /api/media/{media}`,
`POST /api/products/{product}/images/attach` (`media_ids[]`) y
`PUT /api/store/logo` | `/api/store/cover` (`media_id`). Los `POST` de subida
directa siguen existiendo: suben el archivo y de paso lo dejan en la biblioteca.

En el panel: pantalla `Multimedia` (`/multimedia`) y el modal `MediaPicker`,
reutilizado desde el formulario de producto y desde la configuración de la
tienda.

### Optimización: WebP en varias medidas

Ninguna imagen se guarda como la subió el usuario. Al subir se convierte a
**WebP** (calidad 82) en varias medidas y **el original se descarta**. Los
tamaños y los perfiles están en `api/config/media.php`; el trabajo lo hace
`ImageOptimizer` con `intervention/image` sobre GD.

- `thumb` 400 · `card` 800 · `full` 1600, medidos sobre el lado mayor. Una foto
  más chica que la medida **no se agranda**, y si dos medidas dan el mismo ancho
  se escribe un solo archivo que las dos variantes comparten.
- Qué variantes se generan depende de **desde dónde se sube**: perfil `library`
  (biblioteca, hero, logo, portada) genera las tres; perfil `product` (galería
  del producto) genera solo `thumb` y `card`, porque la ficha nunca muestra la
  foto más ancha que la card.
- Como no queda el original, **una imagen subida desde un producto se queda en
  800px para siempre**: si después se elige para el banner, se estira la card.
  Es una consecuencia aceptada, no un error.

La columna `variants` es un JSON `{size: {path, width, height}}`. `path` sigue
apuntando a la variante más grande, así que todo lo que ya leía `url()` no
cambió. Un `variants` en `null` es una fila anterior a la conversión.

`Media::responsive()` arma `{src, srcset, thumb, width, height}` y el `srcset`
declara el **ancho real** de cada archivo, nunca el de la medida objetivo: una
foto de 500px anunciada como 1600w hace que el navegador elija mal. Por eso
`PublicProductResource` devuelve `images` como objetos y no como URLs sueltas.
`logo_url` y `main_image_url` apuntan al `thumb`, porque solo se dibujan chicos.

Todo vive en `media/{store_id}/`, sin excepción: borrar una tienda es borrar una
carpeta. El límite de subida es de **4 MB** por archivo.

Para rehacer lo ya subido está `php artisan media:optimize` (`--all` para
reconvertir todo, `--profile=` para elegir el juego de medidas, `--keep` para no
borrar los archivos de origen). Es la herramienta a usar si cambian los tamaños
de `config/media.php`.

## API

Toda la aplicación debe funcionar mediante API REST.

Seguir convenciones REST.

Ejemplo:

- GET
- POST
- PUT
- DELETE

## Seguridad

- Sanctum
- Validaciones
- Policies
- Form Requests
- Rate Limit

## Base de datos

Diseñar pensando en crecimiento.

Todas las tablas relacionadas con el negocio deberán incluir:

- `store_id`

Esto permitirá soportar múltiples tiendas.

## Objetivos del MVP

Al finalizar el MVP el usuario debe poder:

- Crear cuenta
- Crear su tienda
- Crear categorías
- Crear productos
- Compartir el enlace de su catálogo
- Compartir productos por WhatsApp
- Administrar todo desde un panel sencillo

## Fuera del MVP

No desarrollar todavía:

- Pedidos
- Carrito
- Pasarela de pago
- Inventario
- Cupones
- IA
- Facturación
- Usuarios múltiples
- Dominios personalizados
- Aplicación móvil
- Importación desde Excel
- Exportación
- Notificaciones
- Multi idioma
- Tema oscuro

## Principios de desarrollo

- Código limpio (Clean Code)
- Arquitectura por capas
- Componentes reutilizables
- API documentada
- Validaciones centralizadas
- Uso de Resources para respuestas JSON
- Uso de Service Layer cuando sea necesario
- Código preparado para futuras funcionalidades sin sobreingeniería

## Meta del proyecto

Lanzar un MVP funcional lo antes posible para validar el producto con usuarios reales y evolucionarlo en función de su uso, evitando desarrollar módulos innecesarios en etapas tempranas.
