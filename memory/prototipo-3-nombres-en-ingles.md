---
name: prototipo-3-nombres-en-ingles
description: prototipo-3 — el 2026-08-24 se renombraron al inglés los 522 identificadores del sitio; qué quedó en español y por qué
metadata:
  type: project
---

2026-08-24. Rafael fijó la convención en el `CLAUDE.md` de la cuenta principal
—**identificadores en inglés, contenido y comentarios en español**— y se aplicó
a todo `prototipo-3/`.

Se renombraron **522 identificadores**: 241 clases, 212 custom properties,
28 `data-*` y 71 ids, en los 3 HTML, los 3 CSS y los 2 JS. También se
renombraron las variables y funciones internas del JS (`abrirMenu` → `openMenu`,
`pintar` → `render`, `window.tema` → `window.theme`).

## Qué NO se tradujo, a propósito

Es contenido, no código:

- **Nombres de archivo**: `producto.html`, `contacto.html`. Traducirlos era
  cambiar URLs.
- **Slugs de categoría**: `#ropa-hombre`, `data-category="gafas"`,
  `data-filter="todas"`. Son datos de la tienda.
- **Nombres propios de las paletas**: `cafe`, `verano`, `oro`… El atributo sí
  cambió (`data-paleta` → `data-palette`), los valores no.
- El texto visible y los comentarios.

## Traducciones que no son obvias

`boton`→`btn`, `pie`→`footer`, `grilla`→`grid`, `tarjeta`→`card`,
`ficha`→`spec`, `migas`→`breadcrumbs`, `dato`→`info` / `datos`→`info-list`,
`tono`→`swatch` (color de producto) contra `muestra`→`sample` (la muestra de
paleta del panel), `red`→`social` / `redes`→`social-list`, `velo`→`veil`,
`talle` y `tamano`→`size`, `radios`→`radius`, `vacio`→`empty`,
`ventaja`→`benefit`, `visor`→`viewer`.

## La trampa del renombrado automático (si hay que repetirlo)

El script renombró primero las variables de JS y después las clases, y
**las variables se comieron los nombres dentro de los strings**: `'.nav-buscador'`
quedó como `'.nav-searchForm'` y `'[data-icono-abrir]'` como
`'[data-icono-openIcon]'`, porque `\bbuscador\b` y `\babrir\b` también casan
dentro de un selector. Se detectaron cruzando cada selector del JS contra las
clases del HTML y del CSS, y se corrigieron a mano (8 strings).

**El orden correcto es al revés: primero las clases, ids y `data-*` en todo el
archivo, y recién después las variables.**

Los comentarios se protegen aparte: se sacan antes de renombrar variables y se
vuelven a poner después, aplicándoles sólo el mapa de clases y tokens. Si no,
la prosa en español se llena de palabras en inglés.

## Verificación que se corrió

- `node --check` en los dos JS.
- Ningún `var(--token)` sin definir; llaves balanceadas.
- Cada selector, id y `data-*` que busca el JS existe en el HTML o el CSS.
- Etiquetas balanceadas, sin ids duplicados, sin `<label for>` huérfano.
- Las 16 clases del HTML sin regla CSS son **las mismas de antes** (se comparó
  contra el respaldo): son ganchos semánticos, no un error del renombrado.
- Se realinearon las columnas de las declaraciones CSS, que quedaron desparejas
  al cambiar el largo de los nombres.

**No se tocaron** `_grilla/` ni los archivos `- copia`: son respaldos y siguen
en español.

**Pendiente:** `tools/estricto.py` y `tools/generar2.py` generan `paletas.css`
con los nombres viejos. Regenerar ahora rompe todo el CSS, no sólo los tokens
nuevos. Ver [[prototipo-3-degradado-barra]].
