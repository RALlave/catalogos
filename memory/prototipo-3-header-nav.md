---
name: prototipo-3-header-nav
description: prototipo-3 — estructura base de header + nav, paleta "Café" con ratios WCAG y toggles de forma y fondo
metadata:
  type: project
---

`prototipo-3/` es un tercer prototipo estático (HTML/CSS/JS vanilla), hermano de
`prototipo/` y `prototipo-2/`. No los reemplaza: explora un **layout de cabecera
tipo FinPRO** (franja clara arriba + barra de navegación oscura) sobre el mismo
sistema de tokens.

Estado a 2026-08-23: **header + nav + banner**. Falta filtro por categoría,
grilla de productos y footer.

## Decisiones (2026-08-23)

- La skill `ui-ux-pro-max` **no está disponible** en la sesión de Cowork; se usó
  `maquetado-semantico` para las reglas de semántica, contraste y accesibilidad.
- Estructura de la cabecera, elegida con Rafael:
  - Franja superior clara: logo/marca a la izquierda y tres bloques a la
    derecha, cada uno con ícono SVG: **días de atención**, **dirección** (va en
    el medio, como en la referencia) y **teléfono + email**. La dirección usa
    `<address>`, no un `<div>`.
  - Barra oscura: `Inicio · Categorías · About us · Contact` + buscador que se
    abre desde un botón de lupa. `Categorías` es un desplegable nativo
    (`<button aria-expanded>` + `<ul hidden>`), no un `<a>`.

## Cabecera en móvil y tablet — sin barra (2026-08-23, pedido de Rafael)

Debajo de 992px la cabecera es **una sola franja clara: logo a la izquierda,
botón de menú al borde derecho**. Se van los tres bloques de datos (horario,
dirección, teléfono) y **la barra oscura no se dibuja**. Todo vuelve en
escritorio.

Cómo está hecho, que tiene dos trampas:

- El `<nav>` **sigue en el DOM y no se puede ocultar**: adentro viven el panel
  lateral y su velo. Lo que se sacó del `.nav` es sólo el `background-color`
  (ahora se declara recién en el `@media` de 62rem) y el `min-height` de
  `.nav-inner`. Los tokens `--nav-*` se siguen declarando en `.nav` porque el
  panel los usa para su propio fondo y su borde.
- **Horario y dirección bajan al panel.** No desaparecen del sitio: van al pie
  del menú lateral, después de los ítems, en un `<ul class="nav-info-list">` que
  reusa los `.info*` de la cabecera y sólo les da vuelta los tres colores
  (ícono con `--nav-brand`, que es el tono que las doce paletas garantizan
  ≥ 3:1 contra la barra). El teléfono NO se agregó: Rafael pidió sólo esos dos.
  En escritorio `.nav-info-list` se esconde junto al ✕ y al velo, porque ahí los
  datos vuelven a la franja superior.
- El `<button class="nav-toggle">` se **movió en el HTML** de `.nav-inner` a
  `.header-top-inner`, en las tres páginas. Dos consecuencias:
  - Su color ya no puede salir de `var(--nav-text-strong)`: esos tokens están
    scopeados en `.nav` y el botón quedó afuera. Usa `var(--color-text)`, que
    es lo correcto sobre la franja clara.
  - En `app.js` el botón se busca con `document.querySelector('.nav-toggle')`,
    no con `nav.querySelector(...)`. Si alguien vuelve a meterlo dentro del
    `<nav>`, esto sigue andando; al revés no.

## Banner (hero)

Foto a sangre + velo + texto centrado, con el filete decorativo bajo el título
que tenía la referencia. El `<h1>` de la página vive acá:
*"Elegí lo que te gusta y pedilo por WhatsApp"*, con dos acciones —
`Ver catálogo` (sólido crema, 10.20:1) y `Pedir por WhatsApp` (fantasma blanco).

- La foto es un `<img alt="">` con `object-fit: cover`, no un
  `background-image`: entra en la carga temprana con `fetchpriority="high"` y
  no hay que declarar la URL en el CSS.
- Foto en uso: `assets/img/banners/banner-1.jpg` (1024×411), la que puso
  Rafael. `assets/img/banner.jpg` (el recorte de la zapatilla que se había
  armado antes) quedó sin usar y se puede borrar.
- **El velo es lo que sostiene el contraste.** Esa foto de oficina tiene
  píxeles blancos puros Y negros puros dentro de la banda donde va el texto,
  así que los dos velos están calculados contra ESE peor caso, no contra el
  promedio. Si se cambia la foto, hay que recalcularlos.
- **2026-08-23 — el texto del banner es blanco fijo en las 12 paletas.** Lo
  pidió Rafael: `--color-on-banner: #FFFFFF` en todos los bloques de
  `paletas.css` (la variante `-claro` no se tocó, sigue con su tinta oscura).
  Los ratios anotados se recalcularon contra el peor caso real (velo sobre
  píxel blanco puro) y van de **2.15:1 (piel) a 3.42:1 (halloween)**: ninguno
  llega a 4.5:1, es una decisión estética asumida. **Pendiente:**
  `tools/generar2.py` y `tools/estricto.py` todavía calculan ese token con la
  lógica vieja, así que regenerar `paletas.css` pisa el blanco.
- Hay **dos variantes**, y se cambian con un atributo en `<html>`:
  - por defecto, velo `#3E2C23` al 70 % + texto blanco → 5.12:1 en el peor
    caso (AA), 7.94:1 sobre el promedio. Es la de la referencia FinPRO.
  - `data-banner="light"`, velo `#EDE0D4` al 78 % + texto `#3E2C23` → 6.11:1
    en el peor caso (AAA), 8.86:1 sobre el promedio. Deja la foto luminosa,
    que le sienta mejor a esta imagen de oficina.
  Las dos variantes se implementan con custom properties **en el componente**
  (`--banner-veil`, `--banner-text`, `--banner-cta-*`), que sólo apuntan a
  tokens de `:root`. Los botones (`.btn-cta`, `.btn-cta-border`) leen esas
  variables, así que la variante clara invierte el CTA a sólido café sin
  duplicar reglas.
- `--banner-height` y `--banner-title` son tokens: los breakpoints sólo los
  redefinen (22rem/28px → 28rem/36px → 30rem/44px). Se bajaron respecto del
  primer intento porque la foto es de 1024 px de ancho y a más altura se
  notaba el reescalado.

## Paletas — dónde viven y cómo se cambian

Desde 2026-08-23 los colores **no están en `base.css`**: viven en
`assets/css/paletas.css`, un bloque por paleta, y es el único archivo del
prototipo con hexadecimales (`base.css`, `components.css` y el HTML no tienen
ninguno). Se cambia de paleta con `data-palette` en `<html>`; sin atributo manda
Café, que es el bloque `:root`. Hay 12.

Cada bloque declara los mismos ~25 tokens con su ratio WCAG anotado contra el
fondo real. Agregar una paleta = copiar un bloque y reasignar, midiendo.

Hay un **panel de diseño** fijo en el borde derecho (`.panel`) para comparar en
vivo: paleta, bordes (cuadrados/redondos) y velo del banner (oscuro/claro). Son
radios nativos ocultos con `clip-path`, y cada grupo escribe un atributo en
`<html>` — el CSS hace todo lo demás, el JS no toca estilos. **Es una
herramienta de la maqueta, no del catálogo**: se saca borrando el bloque
"PANEL DE DISEÑO" de `index.html`, `components.css` y `app.js`, más
`assets/js/tema.js` y su `<script>` del `<head>`.

La elección **sí persiste al recargar**: `tema.js` la guarda en `localStorage`
con el prefijo `proto3:`. Ese script va en el `<head>` y **sin `defer` a
propósito** — se ejecuta antes de pintar, así no se ve el parpadeo de la paleta
por defecto antes de aplicar la guardada. Los valores guardados se validan
contra una lista blanca antes de escribirlos en el `<html>`: lo que viene del
navegador no se escribe a ciegas. Todo va en `try/catch` porque `localStorage`
tira excepción en ventana privada, con cookies bloqueadas, o si la página se
abre con `file://` en vez de por el servidor local — en ese caso el panel
muestra un aviso en vez de fingir que guardó. Hay un botón para volver a los
valores por defecto (los del HTML, no los guardados).

## Paleta 01 — "Café" (entregada por Rafael)

`#EDE0D4 · #C9A27E · #A47148 · #6F4E37 · #3E2C23`

Cómo se repartió, con el ratio medido contra su fondo real:

- `--color-text: #3E2C23` → 13.21:1 sobre blanco (AAA). También es
  `--color-primary` (barra oscura), con `--color-on-primary: #FFFFFF` (13.21:1).
- `--color-accent: #6F4E37` → 7.44:1 sobre blanco y **blanco encima da 7.44:1**,
  así que sirve para texto y para botón sólido.
- `#A47148` da **4.17:1 sobre blanco: no llega al 4.5:1 de texto normal**. Quedó
  como `--color-accent-soft`, sólo para íconos y bordes (mínimo 3:1).
- `#C9A27E` da 2.35:1 sobre blanco — inservible en claro. Es
  `--color-accent-invert`: sólo sobre la barra oscura, donde da 5.63:1 (AA), y
  ahí marca el enlace activo y el anillo de foco.
- Derivados fuera de la paleta base, marcados como tales en `:root`:
  `#2A1D17` (primary-hover), `#5A4034` (accent-hover), `#8A6A4F`
  (text-subtle, 4.94:1) y `#E2D3C3` (borde decorativo).

## Las 12 paletas — reparto ESTRICTO (2026-08-23)

Regla que pidió Rafael: **usar estrictamente los colores entregados**. Un tono
sólo se deriva cuando ningún color de la paleta puede cumplir el mínimo WCAG de
ese rol, y el bloque de `paletas.css` lo dice arriba con el número que lo
justifica. El blanco `#FFFFFF` del fondo de página no cuenta como color de
paleta: es la superficie que pide el brief.

`tools/estricto.py` + `tools/generar2.py` regeneran el archivo entero.
(`tools/paletas.py` + `generar.py` son la versión vieja, no estricta.)

### El límite físico: qué paleta puede y qué paleta no

Medido: contraste del **mejor color de cada paleta sobre blanco**.

- Llegan a 4.5:1 y por lo tanto pueden dar texto y barra sin derivar nada:
  **cafe** (13.21), **noche** (16.79), **halloween** (17.98), **invierno**
  (12.46), **tech** (8.91), **frio** (7.04), **oro** (6.06), **piel** (5.82).
- **No llegan**, y ahí la tinta es obligatoriamente derivada:
  **verano** (mejor 2.91), **primavera** (2.21), **alegre** (2.95) y
  **arcoiris** (4.23 — se queda a 0.27 del mínimo).

Ese era el motivo de que la barra se viera más oscura que la paleta: en esas
cuatro, `--color-primary` es un tono derivado, no uno entregado.

### Derivados que quedan (21 en total, sobre ~300 tokens)

- 4 paletas derivan la tinta (las de arriba).
- 5 derivan sólo `--color-accent-hover`, porque la paleta tiene un único tono
  legible sobre blanco y sin ese derivado el hover no cambiaría nada.
- 4 derivan `--color-on-accent` (el blanco encima del acento).
- **piel** (5) y **noche** (4) son las más forzadas: en piel el tono más claro
  se queda en 4.34:1 contra su propia tinta, y en noche los cinco tonos son
  oscuros, así que la superficie suave es un tinte derivado.

### Auditoría automática (hacerla siempre después de regenerar)

Tras regenerar hay que releer `paletas.css`, extraer los tokens de los 12
bloques y verificar cada par contra su mínimo. La primera corrida encontró
**11 fallos** que el generador había dejado pasar:

- `nav_pref` se respetaba sin comprobar si el color aguanta texto legible
  (oro con `#D4AF37` daba 2.88:1). Ahora la preferencia se valida y, si no
  sirve, se descarta: oro, arcoiris y alegre cayeron al color automático.
- `--color-surface-alt` derivada se generaba como "tinte claro" sin verificar
  la tinta encima (piel quedaba en 3.88:1). Ahora se deriva apuntando a 4.8:1
  contra la tinta.
- `--color-text-muted` no se verificaba sobre `surface-alt` (tech 3.70, frío
  3.12). Ahora se exige que cumpla sobre los dos fondos.

### CUIDADO: hay otra sesión trabajando en la misma carpeta

`prototipo-3/` lo está editando también otro asistente (es quien creó
`producto.html` y quien agregó a `paletas.css` los bloques de BADGES DE ESTADO
y TONOS DE PRODUCTO al final). Antes de escribir cualquier archivo de esa
carpeta hay que **volver a leerlo del disco**, no confiar en la copia local, y
commitear con `expectedMtimeMs` para que el puente rechace la escritura si el
archivo cambió. Al regenerar `paletas.css` hay que preservar esa cola.

### La barra tiene dos versiones

`data-nav` en `<html>`:

- `oscuro` (por defecto): la barra usa `--color-primary`, o sea la tinta.
- `color`: usa `--color-nav-bg`, **siempre un tono real de la paleta**, con el
  texto elegido entre blanco y la tinta según cuál llegue a 4.5:1. Cada paleta
  declara además `--color-nav-active`, `--color-nav-brand` (la barra del ítem
  activo y el anillo de foco, mínimo 3:1) y `--color-nav-border`.

En `components.css` la barra lee custom properties propias (`--nav-bg`,
`--nav-text`, `--nav-active`, `--nav-brand`, `--nav-border`) que apuntan a un
juego u otro. Ninguna regla del componente conoce un hex.

## Dos interruptores del sistema, pensados para las demás paletas

- **Forma:** `--radio-*` arranca en `0` (cuadrado, que fue lo elegido). Poner
  `data-radius="round"` en `<html>` redondea todo el sistema sin tocar
  componentes.
- **Fondo:** `data-background="image"` en `<html>` cambia el body a
  `--bg-image` (`assets/img/fondo.jpg`, todavía no existe: cae al color de
  respaldo) y pasa cabecera y barra a vidrio con `backdrop-filter`. Los velos
  son opacos (0.82 / 0.88) para que el texto siga midiéndose contra su color
  sólido.

## Reglas heredadas que se respetan

Ningún estado sólo por color (la página actual lleva `aria-current="page"` +
barra lateral en móvil / inferior en desktop, además del color), íconos SVG
inline en un sprite con `<symbol>` + `<use>`, foco visible siempre, un solo
`@media` por breakpoint y todos al final del archivo, y `prefers-reduced-motion`
respetado.

## Verificado en navegador

340px, 991px y 1440px sin scroll horizontal; menú móvil, desplegable de
categorías y buscador abren y cierran con clic, Escape y clic fuera; el anillo
de foco se ve sobre la barra oscura; el banner no desborda ni corta el título
en ninguno de los tres anchos.

`prototipo-3/.claude/launch.json` levanta `python -m http.server` en el
puerto **5600** (prototipo-2 usa el 5599).
