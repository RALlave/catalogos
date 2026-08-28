# prototipo-3 — maqueta del catálogo público

Maqueta estática en **HTML, CSS y JS vanilla**. Sin frameworks, sin
preprocesadores, sin build: se abre el `.html` en el navegador y anda.

Es la referencia visual del catálogo público del SaaS (Fase 5 del plan que está
en el `CLAUDE.md` de la raíz). El markup y los tokens están pensados para
portarse después a componentes Nuxt/Vue: los datos van desacoplados del markup y
toda la identidad visual vive en variables CSS.

Referencia de estilo: <https://demo.templatemonster.com/> — moderno y
minimalista, fondos blancos, gradientes con cuidado, tipografía Poppins.

---

## Mapa de archivos

```
prototipo-3/
├── index.html          catálogo: banner, filtro por categoría, grilla de 24 productos, paginación
├── producto.html       ficha: migas, galería fade, datos, pestañas, relacionados
├── assets/
│   ├── css/
│   │   ├── base.css        tokens de forma/tipografía/espacio/layout + reset + responsive
│   │   ├── paletas.css     LOS COLORES: 12 paletas, tonos de producto, alias de badges
│   │   └── components.css  todos los componentes
│   ├── js/
│   │   ├── tema.js         aplica la paleta guardada ANTES de pintar (va en el <head>)
│   │   └── app.js          nav, buscador, panel de diseño, catálogo, galería, pestañas
│   └── img/                fotos por categoría, banners, fondos, logos
├── tools/              scripts Python que generaron paletas y bloques repetitivos
└── _grilla/            respaldo de la grilla (ver "Peligros" abajo)
```

`index - copia*.html` y `producto - copia*.html` son respaldos manuales sueltos.
No son la fuente de verdad.

---

## Las tres capas del CSS (regla dura)

El orden de carga importa: `base.css` → `paletas.css` → `components.css`.

| Archivo | Qué va | Qué NO va |
|---|---|---|
| `base.css` | Forma, tipografía, escala de espacio, layout, reset, foco, `prefers-reduced-motion`, y **todo el responsive** | **Ni un solo hexadecimal** |
| `paletas.css` | Todos los colores, un bloque por paleta | Nada de layout |
| `components.css` | Los componentes | **Ni un solo hexadecimal**: sólo `var(--…)` |

**Ningún hex dentro de una regla de componente. Nunca.** Si hace falta un color
nuevo, se declara en `paletas.css` con su ratio WCAG anotado en un comentario al
lado, y el componente lo consume con `var()`.

### Cada hex lleva su ratio anotado

```css
--color-text-muted: #6F4E37;  /* 7.44:1 sobre surface · 5.75:1 sobre surface-alt — AAA / AA */
```

Mínimos: texto normal **4.5:1**, texto grande 3:1, bordes y componentes de UI
3:1. Si un par no llega, se corrige el hex — no se baja el umbral.

### El texto sobre primario y sobre acento se declara explícitamente

Nunca asumir blanco. Para eso están `--color-on-primary`, `--color-on-accent`,
`--color-on-dark`, `--color-on-banner`. Cada uno con su ratio anotado.

### Los badges son alias, no hexadecimales nuevos

`--color-badge-*` apunta a pares que la paleta ya declaró y ya midió
(`accent/on-accent`, `surface/text`, `primary/on-primary`). Así una paleta nueva
hereda badges correctos sin tocar el bloque de badges.

### Los tonos de producto no son de la paleta

`--swatch-black`, `--swatch-blue-medium`, … son **dato del producto**: no cambian al
cambiar de paleta. Viven en su propio bloque de `paletas.css`.

---

## Toggles de diseño — atributos en `<html>`

El panel flotante de la esquina derecha los cambia en vivo. Es **herramienta de
la maqueta, no del catálogo**: se saca borrando el bloque `<div class="panel">`
del HTML, la sección `PANEL DE DISEÑO` de `components.css` y el bloque del mismo
nombre en `app.js`.

| Atributo | Valores | Qué cambia |
|---|---|---|
| `data-palette` | `cafe` `verano` `primavera` `oro` `arcoiris` `tech` `alegre` `invierno` `piel` `halloween` `frio` `noche` | los 12 juegos de color |
| `data-radius` | `cuadrado` (por defecto) · `redondo` | `--radio-*` de todo el sistema |
| `data-banner` | `oscuro` · `claro` | el velo del banner y el par de texto de encima |
| `data-nav` | `oscuro` · `color` | la barra de navegación |
| `data-background` | `imagen` | fondo con foto + capas de vidrio esmerilado (`backdrop-filter`). **Está en el CSS pero todavía no en el panel** |

Al agregar una página nueva hay que copiarle los mismos atributos al `<html>` y
el mismo bloque de panel, o los toggles no la afectan.

---

## Responsive — sólo se redefinen tokens

Cortes de referencia del proyecto: **340px** (base, sin media query), **991px** y
**1440px**. Los `@media` están puestos en `48rem` (768), `62rem` (992, para
superar el corte de 991) y `90rem` (1440).

La regla: **el responsive vive en `base.css` y sólo cambia variables**.

```css
@media (min-width: 62rem) {
    :root {
        --grid-columns: 3;
        --detail-columns: 1.05fr 1fr;
    }
}
```

En `components.css` los `@media` son la excepción y van **todos juntos al final
del archivo, un bloque por breakpoint, ordenados de menor a mayor**. Si aparece
el mismo breakpoint dos veces, se unifican.

Tokens de layout que se redefinen por corte: `--grid-columns`,
`--related-columns`, `--detail-columns`, `--detail-gap`,
`--footer-columns`, `--container-pad`, `--container-max`, `--banner-height`,
`--thumb-size`.

---

## El menú en móvil y tablet es un panel lateral

Debajo de 992px el menú no se despliega dentro de la barra: entra desde el
borde izquierdo un panel a pantalla completa (`.nav-panel`) con un velo oscuro
detrás (`.nav-veil`).

- El panel es `position: fixed` y tapa también la cabecera del logo.
- Adentro van **el buscador y el menú**, en ese orden. En móvil el buscador es
  un campo fijo siempre visible y la lupa de la barra (`.search-toggle`)
  desaparece; el buscador que se desliza hacia la izquierda es sólo de
  escritorio y su CSS vive en el bloque de 62rem.
- Cerrado, el panel lleva `translateX(-100%)` **y** `visibility: hidden`: sin lo
  segundo los enlaces siguen en el orden de tabulación.
- En escritorio el wrap se disuelve con `display: contents` y sus dos hijos
  vuelven a ser ítems del flex de la barra, cada uno con su `order`. Ojo:
  `display: contents` **no genera caja pero sí hereda `visibility`**, así que en
  ese bloque hay que devolverle `visibility: visible` o el menú nace oculto.
- El velo usa `--color-veil`, un solo token global de `paletas.css` para las 12
  paletas: no se tiñe de primario porque su trabajo es apagar parejo lo que hay
  detrás.
- Con el panel abierto el script pone `is-menu-abierto` en `<html>`: bloquea el
  scroll de la página y sube la cabecera por encima del panel de diseño
  flotante (z-index 30) para que el velo lo tape también a él.
- Cierra por cuatro vías: el ✕ del panel, el velo, Escape y tocar cualquier
  enlace del menú (hace falta porque las anclas de la misma página no recargan).
  Mientras está abierto, Tab queda atrapado dentro del panel.

## Accesibilidad — no negociable

- **Elemento nativo antes que ARIA.** `<button>`, `<a>`, `<details>` traen rol,
  foco y teclado gratis. El único lugar donde se usó ARIA a propósito son las
  **pestañas** de `producto.html`: no existe elemento nativo para eso.
- **El color nunca es la única señal.** Los badges llevan ícono + texto además
  del color. La pestaña activa suma subrayado y peso. El chip de filtro activo
  suma relleno, peso y un ✓. "Agotado" además apaga la foto con `grayscale`.
- **Foco visible siempre.** `:focus-visible` con `--color-focus`, y
  `--color-focus-invert` sobre los fondos oscuros (nav y pie).
- **`prefers-reduced-motion`** anulado al final de `base.css`.
- **Íconos SVG del sprite inline, nunca emojis.** El sprite va al principio del
  `<body>`, y se usan con `<use href="#i-x">`.
- Objetivo táctil mínimo `--touch-min` (44px).
- `alt=""` en lo decorativo (foto del banner, logo junto al nombre); `alt`
  descriptivo en las fotos de producto.

---

## Mejora progresiva — el patrón que se repite

Todo lo interactivo funciona sin JS, y el script sólo mejora lo que ya anda:

| Componente | Sin JS | Con JS |
|---|---|---|
| Grilla del catálogo | se ven los 24 productos; la paginación queda con `hidden` | filtra por categoría y por búsqueda, pagina de a 8 |
| Galería de producto | se ve la primera foto; las miniaturas son enlaces `#foto-N` y `:target` muestra la que corresponda | cambio por desvanecido, flechas, teclado |
| Pestañas | la lista es un índice de enlaces y los tres paneles se ven enteros con su `<h3>` | `tablist` real, un panel a la vez |

El mecanismo: el script pone `data-ready` en el contenedor y el CSS apaga el
respaldo (`.gallery:not([data-ready])`, `.tabs[data-ready]`). Así el hash de la
URL no pelea con el script.

Los controles que sin JS no harían nada (flechas de la galería, paginación)
nacen con `hidden` en el HTML y los muestra el script.

Cada módulo de `app.js` es un IIFE que corta al principio si no encuentra su
gancho (`[data-grid]`, `[data-gallery]`, `[data-tabs]`). Por eso el mismo
`app.js` sirve para las dos páginas.

---

## Convenciones de nombres

Clases, ids, `data-*`, custom properties y variables de JS **en inglés**, en
minúsculas con guiones: `.product-media`, `.gallery-thumb`, `--detail-columns`.
Los estados van con `is-` (`.is-active`) y los ganchos de JS son `data-*`,
nunca clases.

**El contenido y los comentarios siguen en español.** Comentarios explicando
**el porqué** de la decisión — no lo que ya dice el código.

Se renombró todo el sitio del español al inglés el 2026-08-24 (522
identificadores). Quedaron en español a propósito, porque son contenido y no
código: los nombres de archivo (`producto.html`, `contacto.html`), los slugs de
categoría (`#ropa-hombre`, `data-category="gafas"`, `data-filter="todas"`) y
los nombres propios de las paletas (`cafe`, `verano`, `oro`…).

---

## Cómo verificar antes de dar algo por terminado

Abrir la página y revisar a 340 / 991 / 1440. Lo mínimo:

- Sin desborde horizontal (`document.documentElement.scrollWidth > clientWidth`)
- Sin errores en consola
- Recorrer con Tab: foco visible en cada paso y orden lógico
- Probar con JS desactivado
- Ningún `var(--x)` de `components.css` sin definir en `base.css` + `paletas.css`
- Etiquetas balanceadas en el HTML

Con Playwright instalado, un chequeo rápido de las tres anchuras:

```js
for (const w of [340, 991, 1440]) {
  const p = await browser.newPage({ viewport: { width: w, height: 900 } });
  p.on('pageerror', e => console.log('ERROR', e.message));
  await p.goto('file:///.../producto.html');
  console.log(w, await p.evaluate(() =>
    document.documentElement.scrollWidth > document.documentElement.clientWidth));
  await p.screenshot({ path: `shot-${w}.png`, fullPage: true });
}
```

---

## Peligros conocidos

**1. No hay control de versiones.** No hay `.git` en ninguna parte del proyecto.
Es lo primero que conviene arreglar al pasar a VSCode: `git init` y un commit
inicial. Hoy, el respaldo es `_grilla/` y los `index - copia*.html`.

**2. Estos archivos se pisaron cinco veces en un día.** El proyecto se trabajó
desde dos sesiones a la vez y cada una escribió el archivo entero desde una copia
vieja de su contexto. Los que se comparten sí o sí: `index.html`,
`components.css`, `base.css`, `app.js`.

Regla: **releer el archivo de disco en el mismo turno en que se lo va a escribir.**
Nunca escribir desde la copia que quedó en el contexto de un turno anterior.
Con git esto se vuelve un conflicto de merge normal en vez de una pérdida.

**Si algo se pierde, no rehacerlo — re-injertarlo.** Los aportes de cada sesión a
`components.css` y `app.js` son aditivos y contiguos: se parte del archivo que
está en disco (ese trae lo de la otra sesión) y se vuelven a pegar los bloques
propios usando como ancla el comentario de banner de la sección siguiente. Para
`producto.html`, que comparte cabecera / pie / panel con `index.html`, se rearma
al revés: se extraen esos tres bloques del `index.html` actual, se les aplican
los parches propios (íconos extra del sprite, sacar `aria-current`, anclas `#x`
→ `index.html#x`) y se pega el `<main>` propio en el medio.

**3. Los dos sprites divergieron.** `producto.html` tiene cinco `<symbol>` que
`index.html` no tiene: `#i-etiqueta`, `#i-compartir`, `#i-camion`, `#i-cambio`,
`#i-escudo`. Si alguno se necesita en el catálogo, hay que copiarlo.

**4. Todas las tarjetas enlazan a la misma `producto.html`.** Es una maqueta: no
hay una página por producto.

---

## Estado y pendientes

Hecho: cabecera con datos de atención, nav con submenú y buscador, banner,
filtro por categoría, grilla de 24 productos con badges y paginación, pie de
cuatro columnas, panel de diseño con 12 paletas, y la ficha de producto completa
(migas, galería con desvanecido, precio con descuento, ficha técnica, pestañas y
relacionados).

Pendientes conocidos:

- `#nosotros`, `#como-comprar` y `#envios` son anclas que todavía no existen
- El buscador manda a `index.html?q=…` pero `index.html` no lee el parámetro
- `data-background="image"` no está en el panel de diseño
- Falta `contacto.html` (el `prototipo/` original la tenía)
- La galería no tiene autoplay a propósito: la WCAG 2.2.2 pediría un botón de
  pausa. Si se agrega, va con pausa.
