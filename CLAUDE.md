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

## Regla: el disco no acumula imágenes basura

Un SaaS de catálogos vive de imágenes: si cada subida deja archivos que ya no
mira nadie, el disco se llena de basura y el costo lo paga la plataforma. Por
eso, en cualquier funcionalidad que suba o reemplace imágenes:

- **El borrado es físico.** Borrar una imagen borra la fila **y** todos sus
  archivos del disco, variantes incluidas. Nunca alcanza con soltar la
  referencia. Lo hace `MediaService::delete()` / `ImageOptimizer::forget()`.
- **Nada escribe archivos por su cuenta.** Todo lo que sube pasa por
  `ImageOptimizer`: se convierte a WebP, se genera sólo el juego de medidas del
  perfil que corresponde y **el original se descarta**.
- **No se generan variantes que ninguna pantalla muestra.** Si un tamaño no se
  dibuja, no se escribe: para eso están los perfiles de `config/media.php`.
- **Reemplazar es borrar lo anterior.** Cambiar el logo, la portada, la foto de
  un hero o un logo de la plataforma borra el archivo viejo en el mismo momento,
  salvo que otra cosa lo esté usando —una media de la biblioteca puede estar en
  varios productos—.
- **Lo que se copia se limpia solo.** Una copia que existe sólo mientras se usa
  —como el logo tomado de la galería de la plataforma— se marca al crearla y se
  borra en cuanto deja de usarse. Lo que subió el dueño no se toca nunca.
- **Antes de sumar una funcionalidad que suba imágenes hay que responder quién
  las borra y cuándo.** Si no hay respuesta, falta diseño, no código.

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

### Reparto por dominio

Cada tienda tiene su propio subdominio, y ahí conviven su catálogo y su panel:

```
dominio.com              landing + puerta de entrada al panel
{tienda}.dominio.com     catálogo (Nuxt) + panel de esa tienda (SPA)
api.dominio.com          Laravel
```

Consecuencias que atraviesan todo el proyecto:

- Todas las peticiones del navegador a la API son **cross-origin**. Se
  resuelven con CORS (`api/config/cors.php`), que permite el dominio y un nivel
  de subdominio. Va **sin credenciales**: Sanctum se usa con tokens Bearer, no
  con cookies de sesión.
- El dominio se compró en **Cloudflare**, que da el certificado wildcard sin
  renovación. A cambio, nginx necesita el bloque `real_ip` con
  `CF-Connecting-IP`, o la IP del visitante que llega a las estadísticas es
  falsificable. Está en `deploy/cloudflare-realip.conf`; el detalle, en
  `DEPLOY.md`.
- El VPS está administrado con **CloudPanel** y comparte servidor con otros
  cuatro sitios en producción. Los vhosts los genera el panel: lo que se edita a
  mano vive en `deploy/vhost-apex.conf` y `deploy/vhost-tiendas.conf`, y se
  pierde cuando CloudPanel los regenera. El catálogo se levanta con **systemd**
  (`deploy/catalogos-web.service`), no con PM2.
- En desarrollo se entra por `lvh.me`, que resuelve a 127.0.0.1 sin tocar el
  archivo `hosts`: `rex.lvh.me:3000` es el catálogo y `rex.lvh.me:5173` el
  panel.

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

Cada tienda tendrá una URL pública. **El slug es el subdominio**, no un
segmento de la ruta:

```
https://mitienda.catalogo.com
```

Por eso los slugs reservados de `api/config/catalog.php` son nombres de host
(`api`, `www`, `mail`, `panel`…) y no rutas de la plataforma.

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
{tienda}.dominio.com/                  catálogo   (?cat= &q= &page=)
{tienda}.dominio.com/producto/{slug}   detalle
{tienda}.dominio.com/search            resultados de búsqueda (?s= &cat= &page=)
{tienda}.dominio.com/contacto          contacto
```

La landing del SaaS **no la sirve Nuxt**: vive en `landing/` y la sirve nginx
en el dominio principal. Nuxt sólo atiende subdominios de tienda; un host sin
tienda —el dominio pelado, `www`, un slug que no existe— responde 404.

La tienda sale del header `Host`, no de la ruta: lo resuelve
`web/app/composables/useStoreHost.ts`, que también arma la URL absoluta de la
tienda (`useSiteUrl`) para el canonical, el Open Graph y lo que se comparte por
WhatsApp. **No existe una variable con la URL del sitio**: con una tienda por
subdominio, una URL fija sería la de otra tienda. La única variable es
`NUXT_PUBLIC_BASE_DOMAIN`, el dominio de la plataforma sin tienda.

El catálogo es el diseño de `prototipo-3/`, portado el 2026-08-28. Ya no hay
cuatro layouts: es **un solo diseño configurable** y lo que cambia es la paleta
(14) más tres opciones de forma, que viajan como atributos del `<html>`:
`data-radius` (square/round), `data-nav` (dark/color) y `data-banner`
(dark/light). El layout de tienda inyecta los colores de la paleta como un
`<style>` con `:root`.

El CSS se copió de la maqueta y se carga en este orden: `base.css` (forma,
tipografía, responsive; ni un hexadecimal), `palette.css` (colores) y
`components.css` (componentes, sólo `var()`). Vale la misma regla dura que en
`prototipo-3/CLAUDE.md`: **ningún hexadecimal dentro de una regla de
componente**. La fuente de verdad de los colores sigue siendo
`prototipo-3/assets/css/paletas.css`; de ahí salen los de `config/themes.php`.
Excepción (2026-09-14): las cuatro rosas (`rosa-pastel`, `rosa-fucsia`,
`rosa-dorado`, `rosa-nude`) viven **sólo** en `themes.php`, y Alegre y Arcoíris
se quitaron de ahí pero siguen en la maqueta congelada.

Filtro, búsqueda y paginación son la URL (`?cat=`, `?q=`, `?page=`), no estado
del cliente: andan sin JS, se comparten y los indexa el buscador.

El banner del home es un **carrusel de heros administrables** (ver más abajo).
Lo que sigue fijo en el código son los títulos de las pestañas de producto y el
botón "Pedir por WhatsApp" del hero (se arma con el número de la tienda). El
botón principal —texto y destino— se elige por hero (ver abajo).

## Hero (banner) del home

El banner dejó de ser un bloque fijo: la tienda carga **hasta 10 heros** en la
tabla `heroes` (`store_id`, `media_id`, `eyebrow`, `title`, `text`, `order`,
`active`) y el catálogo los rota. Cada hero tiene imagen y tres textos —
**eyebrow** (el texto chico de arriba; se llama así, no *kicker*), título y
texto—.

- **El botón principal** se personaliza por hero (2026-09-14):
  `heroes.button_text` (30, por defecto "Ver catálogo" en la columna; vacío
  = no hay botón) y `heroes.link`, que **se elige de una lista y nunca se
  escribe**, para que el dueño no pueda dejar un enlace roto. Los valores y
  sus URLs están en `config/catalog.php` (`hero_links`): anclas `products`
  (`#products`) y `featured` (`#featured`), páginas `home` (`/#products`, baja
  a la grilla) y `contact` (`/contacto`), y `category`, que usa
  `heroes.category_id` y abre `/?cat={slug}#products`.
- La URL la arma la API (`PublicHeroResource` → `button_href`), no el
  catálogo. Todo destino que no abriría nada va a `#products`: categoría
  oculta o borrada (`category_id` queda `null`) y Destacados con la vitrina
  apagada.
- "Pedir por WhatsApp" sigue fijo.
- **La alineación del contenido** también se elige por hero (2026-09-16):
  `heroes.align`, `center` (por defecto) o `left`, con los valores en
  `config/catalog.php` (`hero_aligns`). Viaja como `data-align` del
  `.banner-slide` y rige en todos los tamaños de pantalla; el banner corto de
  Contacto no se ve afectado.

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

- **Duplicar** (2026-09-16): la copia trae imagen (compartida, no se copia el
  archivo), textos, botón y alineación; nace **oculta**, **al final** del orden
  y con "(copia)" en el título. Respeta el tope de 10 heros.

Endpoints: `GET|POST /api/heroes`, `GET|PUT|DELETE /api/heroes/{hero}`,
`POST /api/heroes/reorder` y `POST /api/heroes/{hero}/clone`.

## Destacados del home — la vitrina

Entre el banner y la grilla va la **vitrina de destacados**: un producto grande
y hasta cuatro chicos al costado. Es la propuesta **C** de las tres que se
maquetaron el 2026-08-29 (`prototipo-3/index-featured-c.html`); las otras dos
quedaron sin usar, y `.rail` sigue reservado para la futura sección Ofertas.

- Los productos **no se eligen a mano**: salen de los marcados como `featured`
  en su ficha y, si son menos de cinco, **se completa con el resto del
  catálogo** por su orden. La vitrina no queda coja nunca.
- Los **agotados quedan afuera**: recomendar algo que no se puede comprar es
  peor que mostrar una tarjeta menos. Por eso las tarjetas de la vitrina son
  las únicas del catálogo **sin badge**.
- La sección se prende y se apaga por tienda (`featured_enabled`) y sus dos
  títulos se editan (`featured_title` 60 · `featured_subtitle` 80). Los dos
  traen un texto por defecto en la columna, así una tienda recién migrada ya
  muestra algo legible.
- Sólo se dibuja en el **home sin filtrar**: con `?cat=`, `?q=` o `?page=`
  activos el visitante ya sabe qué busca y la vitrina se le pondría delante.

Endpoint: `GET /api/stores/{slug}/featured`, cacheado con clave fija. Con la
sección apagada devuelve la lista vacía, no un 404.

## Ofertas debajo de Contacto

Al pie de `/contacto` va una sección **"Ofertas"**: título y la grilla de
siempre (`ProductCard`), sin chips de categoría.

- Entran los productos visibles con `sale_price` menor que `price`
  (`ProductService::onSale()`, el mismo criterio del filtro del panel). Los
  **agotados nunca**.
- Son **3 al azar**. La caché guarda todas las ofertas y el sorteo se hace
  **fuera** del `remember`, así cada visita ve otras; el resultado viaja en el
  payload del SSR y no parpadea al hidratar.
- Es fija: no se administra desde el panel y el título está en el código. Sin
  ofertas, la sección no se dibuja.

Endpoint: `GET /api/stores/{slug}/offers`.

## Compartir producto

El botón **Compartir** de la ficha abre un modal (`ShareModal.vue`, sobre el
`<dialog>` nativo) con tres opciones: **WhatsApp**, **Facebook** e
**Instagram**.

- El botón sigue siendo un `<a>` a `wa.me`: **sin JS comparte directo por
  WhatsApp**; con JS el clic se intercepta y abre el modal.
- Instagram **no tiene enlace para compartir desde la web**: en pantallas
  táctiles con `navigator.share` abre el menú del teléfono; en escritorio copia
  el enlace y el botón dice "Enlace copiado".
- El compartido se cuenta (`trackShare`) **al elegir una red**, no al abrir el
  modal.

## Texto enriquecido

Las descripciones de producto, categoría y tienda y el texto del hero se
escriben con un **mini editor** (`panel/src/components/RichTextEditor.vue`,
sobre Tiptap): negrita, cursiva, subrayado, viñetas y lista numerada. La meta
description del SEO queda como textarea: Google la muestra como texto plano.

- Se guarda **HTML**, y la API lo limpia antes de validar
  (`App\Support\RichText::sanitize()`, con `symfony/html-sanitizer`): sólo
  sobreviven `p`, `br`, `strong`, `em`, `u`, `ul`, `ol` y `li`. Un editor
  vacío se guarda `null`, no `<p></p>`.
- **Excepción a la regla de `maxlength`**: en estos campos el límite cuenta los
  **caracteres visibles**, no el HTML (regla `RichTextMax`, y el contador del
  panel usa `richTextCounter()`). Por eso la columna es más grande que el
  límite: `heroes.text` pasó a `text` (2026-09-16). Los límites siguen siendo
  hero 255 · categoría y tienda 2000 · producto 5000.
- En el catálogo **sólo lleva formato** la pestaña Descripción del producto, el
  texto del hero y la descripción de la tienda en el banner de Contacto, con
  `v-html` dentro de un wrap `.rich-text`. Los resúmenes de las tarjetas y la
  vitrina, el resumen de la ficha, el footer y las meta tags usan
  `description_text`, que la API devuelve ya sin etiquetas.
- Los listados del panel muestran el texto plano con `richTextToPlain()`.
- La migración `convert_texts_to_rich_text` pasó a HTML los textos que ya había.
  Lo que se cargue **sin pasar por la API** (seeders) queda como texto suelto:
  se ve, pero sin párrafos.

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
- Páginas → Home: las secciones de la portada, una por pestaña
- SEO: meta title, meta description e imagen para compartir

Las secciones del home viven todas en la misma pantalla (`/admin/paginas/home`),
repartidas en pestañas: **Hero (banner)** —los heros y el efecto del carrusel— y
**Destacados** —el interruptor de la vitrina y sus dos títulos—. La pestaña
viaja en `?seccion=`, así que volver de editar un hero cae donde corresponde.
Hero dejó de ser una entrada suelta de "Mi tienda".

La apariencia se elige de una lista, no se arma a mano: las paletas y las
opciones viven en `api/config/themes.php` y se consultan con `GET /api/themes`.
La tienda guarda solo las claves (`palette`, `radius`, `nav`, `banner`), así que
retocar una paleta ahí actualiza a todas las tiendas que la usan. Agregar una
paleta nueva es agregar un bloque a ese archivo.

### Regla: las confirmaciones son del panel, no del navegador

**Nada de `window.confirm`, `window.alert` ni `window.prompt`.** Los dibuja el
navegador: no se pueden estilar, cambian de forma en cada sistema y no se
parecen en nada al resto del panel.

Toda confirmación va en el modal propio: `stores/confirm.js` más
`ConfirmModal.vue`, montado una sola vez en `App.vue`. Se usa esperando la
respuesta, y lo destructivo se pinta en rojo:

```js
const confirmed = await confirm.ask({
    title: `¿Eliminar "${product.name}"?`,
    text: 'No se puede deshacer.',
    action: 'Eliminar',
    danger: true,
})
```

El foco arranca en **Cancelar**, Escape cancela y el detalle respeta los saltos
de línea, así que un aviso de varias líneas —el de borrar una imagen en uso—
entra tal cual.

### Nadie pierde un formulario a medio llenar

Ningún formulario del panel se abandona en silencio. Si tiene cambios sin
guardar y el usuario se va —a otra pantalla, cerrando la pestaña o cerrando el
modal que lo contiene—, primero aparece un aviso con tres salidas: **Seguir
editando**, **Salir sin guardar** y **Guardar y salir**.

Lo resuelve `useUnsavedChanges` (`panel/src/composables/`), que compara el
formulario contra cómo estaba al cargarse. Consecuencias para el código nuevo:

- La pantalla llama `markSaved()` al terminar de cargar los datos y cada vez
  que guarda: ese es el punto de partida contra el que se compara.
- La función de guardado devuelve `true`/`false` —es lo que mira "Guardar y
  salir"— y **no navega**. Por eso las vistas que volvían al listado quedaron
  partidas en `persist()` (guarda) y `submit()` (redirige).
- Un formulario dentro de un modal declara `active` y cierra con
  `confirmLeave()`; `dismiss()` es el cierre directo, para lo que ya resolvió
  qué hacer con los cambios.
- Lo que se guarda al instante no lleva aviso: el efecto y el orden de los
  heros, los logos de la plataforma, el logo y la portada de la tienda.

El aviso al cerrar la pestaña lo dibuja el navegador y **no se puede
personalizar**: ahí no hay tres botones, sólo su propio cartel.

### Regla: el panel se piensa primero en el celular

Es desde donde más lo van a usar los dueños. Por debajo de **720px** (el único
corte de mobile del panel), dos piezas de `components.css`:

- **`.btn-label`** envuelve el texto de los botones con ícono de los
  **encabezados** de pantallas y tarjetas ("Nuevo producto", "Ver catálogo"…):
  en mobile queda sólo el ícono. Guardar, cancelar y los botones de
  formularios y modales **mantienen el texto**.
- **`.is-hide-mobile`** va en el `<th>` y el `<td>` de cada columna secundaria.
  **Ninguna tabla hace scroll horizontal**: en mobile quedan arrastrar, el
  nombre (con su miniatura), las acciones y, en Productos, el checkbox del
  lote. El estado también se oculta.
- **Las acciones de una fila van en `<RowActions :actions="[…]">`**
  (`{ label, icon?, to?, href?, onClick?, danger?, disabled?, loading? }`).
  Se declaran una vez: en escritorio son los botones sueltos y en mobile, un
  menú **⋮**, aunque haya una sola acción. En mobile el `.table-wrap` deja de
  recortar, para que el menú de la última fila no quede cortado.
- **Las filas de campos (`.form-row`) van de a 2 en mobile**, y un campo impar
  queda a lo ancho debajo. Una fila con su propio botón (Horarios) no salta de
  línea. Con `.form-row.is-stacked` va **un campo por fila** (Logo y Contacto
  de Mi tienda).
- **El encabezado de pantalla no se apila**: título a la izquierda y botones a
  la derecha, también en mobile.
- **Los filtros de una lista van en `<ScrollStrip>`**: en tablet y mobile
  (≤900px) quedan en una línea que se desliza con el dedo, con un gradiente del
  lado donde hay opciones ocultas que desaparece al llegar a ese extremo.

Toda pantalla o tabla nueva se revisa a ~400px antes de darla por terminada.

### Dónde vive

SPA en `panel/` (Vite + Vue 3 + Pinia + Vue Router), hermana de `api/` y `web/`.
Es **una sola aplicación para los dos perfiles**: un único login
(`POST /api/login`) y el router decide por el array `roles` que devuelve la
respuesta — `store_owner` va a `/admin`, `superadmin` va a `/superadmin`.

La misma SPA se sirve desde **dos lugares**, porque el panel de cada tienda
vive en el subdominio de esa tienda:

```
dominio.com/login          puerta universal: entra cualquiera
dominio.com/registro       alta de cuenta (quien no tiene tienda no tiene subdominio)
dominio.com/restablecer    destino del correo de recuperación
dominio.com/superadmin     panel del superadmin
{tienda}.dominio.com/login panel de esa tienda: login…
{tienda}.dominio.com/admin …y administración
```

Sus **rutas** viven en la raíz, conviviendo con el catálogo en el mismo origen
(nginx decide cuál sirve cada una); sus **archivos** cuelgan de `/panel/`, que
es lo que declara `VITE_BASE`. Son dos cosas distintas: el router usa base `/`.

El token de Sanctum se guarda en `localStorage` y viaja como Bearer. Como cada
subdominio es un origen aparte, **el token no se comparte entre tiendas ni con
el dominio principal**: cada origen tiene su propia sesión.

### Saltar de un subdominio a otro

Un dueño que entra por `dominio.com/login` tiene que terminar en su propio
subdominio, ya logueado. Como el token no cruza entre orígenes, se usa un
**código de un solo uso** (`App\Services\SessionHandoff`): dura un minuto, vive
en la caché, viaja en la query y del otro lado se canjea por un token nuevo.

- `POST /api/auth/handoff` (autenticado) emite el código
- `POST /api/auth/handoff/redeem` lo canjea; va sin autenticar porque el código
  **es** la credencial

Por eso `CACHE_STORE` no puede ser `array` en ningún entorno donde se pruebe el
panel: el código se guarda en una petición y se canjea en otra.

### Entrar al panel de una tienda (impersonación)

Desde el listado de tiendas y desde Editar tienda, el superadmin puede entrar al
panel de una tienda **como su dueño**, para dar soporte. Es acceso completo: lo
que se edite queda a nombre del dueño.

`POST /api/admin/stores/{store}/impersonate` devuelve un **código de un solo
uso**, no un token: el panel de la tienda está en otro origen y desde el
dominio principal no se puede escribir su `localStorage`. El navegador salta a
`{tienda}.dominio.com/admin?handoff=<código>`, lo canjea ahí y limpia la URL.

El token del superadmin **no se mueve**: sigue esperando intacto en el dominio
principal, así que no hay nada que guardar para volver (se fue el
`dash.admin_token`). En el subdominio queda `dash.impersonated_store` con el
nombre de la tienda, para que la sesión sobreviva a un F5 y el layout muestre
el aviso. Ya no es una barra arriba del contenido: es un **botón redondo
flotante abajo a la izquierda** (escudo, con un pulso suave) que abre una
tarjeta con "Estás en el panel de…" y **Volver a superadmin**
(`ImpersonationBadge.vue`). La tarjeta se cierra con la X, clic afuera, Escape
o el mismo botón. Al volver, el token del dueño se revoca
(`POST /api/logout`) y el navegador vuelve al dominio principal.

No se puede impersonar a otro superadmin, y el token impersonado no entra a
`/api/admin` (el middleware de rol lo corta con 403). **No hay auditoría**: nada
registra quién impersonó a quién.

### Marca de la plataforma

Son **tres logos**, los tres los sube el superadmin en Apariencia:

- **`auth`** — las cuatro pantallas de acceso: ingreso, registro, recuperar y
  restablecer
- **`panel`** — la barra lateral, la misma para el superadmin y para el dueño
  de una tienda
- **`icon`** — el cuadrado: el favicon del panel y el ícono de la aplicación
  instalada en el escritorio

Son de la plataforma, no de la tienda: el mismo logo en el dominio principal y
en todos los subdominios. Reemplazan el bloque entero que estaba escrito en el
código (el ícono más "Catálogos"), así que **sin logo cargado no se dibuja
nada**, ni en el login ni arriba del menú.

Viven en la tabla `settings` (`key`, `value`), la única del negocio **sin
`store_id`** porque el dato es del SaaS entero. Es clave-valor para que sumar
después el nombre del SaaS no sea otra migración; el mapeo de cada logo a su
clave está en `Setting::LOGOS`. Los archivos no entran a la biblioteca —`media`
exige una tienda— y se guardan en `platform/`, convertidos a WebP como todo lo
demás. **SVG no se acepta**: la conversión es con GD, que no lee vectores.

El ícono usa su propio perfil de medidas (`icon` 192 · `icon_large` 512 en
`config/media.php`) en vez de las del catálogo: son las que pide el manifest.
**No se valida la forma** —se acepta cualquier imagen—, pero el navegador sólo
ofrece instalar la aplicación si el archivo es cuadrado y de 512 px o más, y
como el original no se guarda, una imagen chica no se puede agrandar después.

Endpoints: `GET /api/platform` (público y sin auth, porque lo piden las
pantallas donde todavía no hay sesión) y
`POST|DELETE /api/admin/platform/logo/{auth|panel|icon}`. En el panel los tiene
el store `platform`, que los pide una sola vez por carga y de paso pone el
favicon.

### Logos para las tiendas

Aparte de esos tres, el superadmin sube una **galería de logos** que cualquier
tienda puede tomar como propio, para la que no tiene uno hecho. Se administra en
la misma pantalla de Apariencia, en su propia pestaña, y el dueño la abre desde
Mi tienda → Información, al lado de "Elegir de la biblioteca".

La pantalla de Apariencia del superadmin son **dos grupos de pestañas**: arriba
los logos (los tres de la plataforma · los de las tiendas) y abajo, bajo el
título "Opciones para las tiendas", lo que cada tienda elige (paletas · bordes ·
barra · banner). Las de abajo salen de `config/themes.php`, así que agregar una
opción ahí agrega su pestaña sola. Las dos viajan en la URL (`?logos=` y
`?seccion=`).

Viven en la tabla **`platform_logos`** y no en `settings`: son muchos, se
listan, se renombran y se borran de a uno, y eso pide columnas y no un
clave-valor. Tampoco entran en `media`, que exige una tienda. Los archivos van a
la misma carpeta `platform/`, convertidos a WebP con el perfil `library`.

Elegir uno **copia** la imagen a la biblioteca de la tienda y la deja como
`logo_media_id`. Las consecuencias de que sea copia y no referencia:

- El logo elegido es una imagen más de la tienda: se ve en Multimedia, se puede
  borrar y se puede reutilizar. Pero **no se acumula**: la copia queda marcada
  (`media.from_platform`) y se borra sola en cuanto deja de ser el logo, sea
  porque el dueño eligió otro de la galería, subió el suyo, tomó otro de la
  biblioteca o quitó el logo. Lo que subió el dueño no se toca nunca. La
  limpieza vive en `StoreService::setImage()`, el único camino por el que cambia
  el logo.
- El superadmin puede **sacar un logo de la galería sin dejar a nadie sin
  logo**: quien lo eligió ya tiene el suyo. Por eso, al revés que en Multimedia,
  el borrado no avisa a quién afecta — no afecta a nadie.
- La misma imagen queda duplicada por tienda. Es el precio de que nadie dependa
  de la galería.

Lo único que se edita de un logo es el **nombre** (100): es lo que identifica al
logo en las dos pantallas.

Uno de ellos puede quedar marcado **por defecto** (`is_default`): es el logo con
el que **arranca una tienda recién creada**, por las dos vías —el dueño desde Mi
tienda y el superadmin desde su listado—, porque las dos pasan por
`StoreService::create()`. Se copia igual que cuando el dueño elige uno, así que
la tienda puede cambiarlo o borrarlo desde el primer día. Detalles:

- Hay **uno o ninguno**: marcar uno desmarca al anterior en la misma
  transacción, y no se puede desmarcar sin elegir otro.
- Es una marca en la fila y no una clave en `settings`: borrar el logo se lleva
  la marca sola. Eso sí, **borrar el marcado deja a la plataforma sin logo por
  defecto** hasta que se marque otro.
- Las tiendas que ya existen no se tocan.

Endpoints: `GET|POST /api/admin/platform/store-logos`,
`PUT|DELETE /api/admin/platform/store-logos/{logo}` y
`PATCH /api/admin/platform/store-logos/{logo}/default` para el superadmin;
`GET /api/store-logos` (sólo lectura) y `POST /api/store/logo/platform`
(`platform_logo_id`) para el dueño.

### Instalar el panel en el escritorio

El panel se instala como aplicación (PWA): queda con su ícono en el escritorio
y abre en su propia ventana, sin barra de direcciones. Es para el dueño y para
el superadmin; el catálogo público no se instala.

- El dueño lo tiene en el **menú de su usuario**, arriba a la derecha.
- El superadmin, al final de la **barra lateral**.

El botón no instala nada por su cuenta: el navegador decide si se puede y avisa
con `beforeinstallprompt`; el botón sólo dispara ese aviso guardado
(`panel/src/lib/pwa.js`). Consecuencias:

- **Sin el ícono cargado no hay instalación.** El navegador exige las dos
  medidas del manifest, y ninguna otra parte del panel las tiene. Tampoco la
  ofrece si el ícono subido no es cuadrado o no llega a 512 px: el archivo real
  no coincidiría con lo que declara el manifest.
- **Sólo Chrome y Edge.** Firefox y Safari no emiten el evento: ahí el botón no
  aparece, y tampoco hay a quién avisarle.
- **Se instala una app por origen.** El dueño instala la de su subdominio y el
  superadmin la del dominio principal: para el sistema operativo son dos
  aplicaciones distintas.
- **En desarrollo no se puede probar**: `rex.lvh.me:5173` va por HTTP y una PWA
  necesita HTTPS.

El manifest lo genera el build del panel (`vite.config.js`), no es un archivo
suelto: necesita la URL de la API. Sus íconos apuntan a
`GET /api/platform/icon/{192|512}`, una ruta fija que redirige al archivo del
momento, porque el archivo real cambia de nombre con cada subida. `start_url` es
**`/login`**, la única ruta que existe en los dos orígenes donde vive el panel
(en el dominio principal la raíz es la landing y en el subdominio, el catálogo).

El `sw.js` **no cachea nada**: está sólo porque el navegador todavía lo pide
para ofrecer la instalación. Cachear el panel sería servir la versión anterior
después de cada despliegue.

Los dos archivos se sirven desde la **raíz** del dominio, aunque el build los
deje bajo `/panel/`: un service worker no alcanza a rutas fuera de su carpeta, y
`/login`, `/admin` y `/superadmin` están en la raíz. Eso son dos `location` a
mano en cada vhost (`deploy/vhost-apex.conf` y `deploy/vhost-tiendas.conf`), con
la misma advertencia de siempre: CloudPanel se los lleva puestos al regenerar el
vhost.

### Import & Export

El superadmin se lleva la plataforma entera en dos archivos, desde
`/superadmin/import-export`. Son **dos respaldos separados y bajo demanda**:
nada queda guardado en el servidor, no hay historial ni respaldos programados.

- **Base de datos** — `mysqldump` completo. Al importar se dropean **todas** las
  tablas y vistas antes de cargar el `.sql`, y no sólo las que el archivo
  recrea: si no, una tabla creada después del respaldo sobreviviría con filas
  viejas.
- **Archivos** — un `.zip` con `media/` y `platform/`. Al importar también
  reemplaza: borra esas carpetas y deja exactamente lo que trae el zip. Lo que
  en el zip esté fuera de ellas se ignora, que es lo que además corta el zip
  slip.

Dos consecuencias que hay que tener presentes:

- **Restaurar la base cierra la sesión.** El dump trae los tokens del momento
  del respaldo, así que el actual deja de existir; el panel hace logout y manda
  al login en vez de esperar el primer 401.
- **Los dos respaldos van juntos.** Una base restaurada sin sus imágenes deja
  productos apuntando a fotos que ya no están en el disco.

Endpoints: `GET|POST /api/admin/backup/database` y
`GET|POST /api/admin/backup/files` — el GET descarga y el POST del mismo camino
restaura. Los binarios de MySQL y el límite de subida están en
`api/config/backup.php`. Necesita **ext-zip**, y el límite real de subida lo
ponen PHP y nginx, no esa config.

### Papelera de tiendas

El superadmin no borra una tienda de un clic: primero la **mueve a la
papelera** y recién desde ahí la **elimina definitivamente**.

- En la papelera (`stores.trashed_at`) el catálogo responde **404**, pero el
  dueño **sigue entrando a su panel**. Por eso es una columna propia y no el
  `SoftDeletes` de Laravel, que escondería la tienda también en
  `$user->store`. Todo lo público filtra con el scope `Store::public()`
  (publicada y fuera de la papelera): **una consulta pública nueva tiene que
  usarlo**, no `where('active', true)`.
- Restaurar deja la tienda como estaba: `active` no se toca al moverla.
- **Eliminar definitivamente no deja rastro**: se borra el **usuario dueño** (un
  dueño = una tienda) y la cascada de la base se lleva tienda, categorías,
  productos, galerías, media, heros, pedidos, lista de espera y estadísticas. A
  mano se borran tokens, token de recuperación y sesiones, y en disco la carpeta
  entera `media/{store_id}`. Si el dueño fuera superadmin, se va la tienda y la
  cuenta queda. Lo hace `StoreTrashService`.
- Se **vacía sola**: `stores:purge-trash` corre todos los días y borra las que
  llevan más días que `settings.store_trash_days` (90, cargado por migración).
  Se edita en **Ajustes** del superadmin. Necesita el cron de `schedule:run` en
  el servidor (ver `DEPLOY.md`).
- En el listado es una opción más del select de estados, con contador; "Todos"
  no cuenta las de la papelera.

Endpoints: `PATCH /api/admin/stores/{store}/trash`, `PATCH …/restore`,
`DELETE /api/admin/stores/{store}` (sólo si ya está en la papelera, si no 422) y
`GET|PUT /api/admin/settings`.

**Pendiente:** notificaciones por email a los admins (por ejemplo, antes de que
la papelera borre una tienda). No hay nada implementado.

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

En la grilla de Multimedia, la imagen que está en uso lleva el distintivo **"En
uso"**, contando todo —productos, heros, logo y portada— y no sólo los
productos: una imagen que es sólo el logo se veía libre hasta el momento de
confirmar el borrado. La copia de un logo de la plataforma **no se puede
borrar** desde ahí: no la subió el dueño y se limpia sola. El endpoint también
la rechaza, no sólo la pantalla.

Endpoints: `GET|POST /api/media`, `GET|PUT|DELETE /api/media/{media}`,
`POST /api/products/{product}/images/attach` (`media_ids[]`) y
`PUT /api/store/logo` | `/api/store/cover` (`media_id`). Los `POST` de subida
directa siguen existiendo: suben el archivo y de paso lo dejan en la biblioteca.

En el panel: pantalla `Multimedia` (`/multimedia`) y el modal `MediaPicker`,
reutilizado desde el formulario de producto y desde la configuración de la
tienda.

### Recortar una imagen

Desde el formulario del hero y desde el detalle de Multimedia hay un botón
**Recortar** (`ImageCropper.vue`, sobre `cropperjs` 1.x) con recuadro libre.

- **Pisa la imagen**: no crea una copia. El recorte se ve en todo lo que la usa
  —productos, heros, logo, portada— y **no se puede deshacer**, porque el
  original no se guarda. Por eso pide confirmación.
- El panel manda sólo el rectángulo, en píxeles de la variante más grande. La
  API recorta ese archivo (`MediaService::crop()` → `ImageOptimizer::crop()`),
  regenera **las mismas variantes que ya tenía** con nombres nuevos —así ni el
  navegador ni Cloudflare sirven la vieja— y borra los archivos anteriores.
- Recortar **baja la resolución**: la mitad de una foto de 1600 px queda en
  800 px, y ahí se queda.

Endpoint: `POST /api/media/{media}/crop` (`x`, `y`, `width`, `height`).

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
