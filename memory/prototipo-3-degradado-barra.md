---
name: prototipo-3-degradado-barra
description: prototipo-3 — barra y pie llevan el mismo degradado horizontal; el extremo derecho es --color-grad-2, un token por paleta con su ratio medido
metadata:
  type: project
---

Desde 2026-08-23 **la barra oscura y el pie** no son un color plano: los dos
van con `linear-gradient(to right, var(--color-primary), var(--color-grad-2))`
(en la barra el primer tono se lee como `--nav-bg`, que ahí es lo mismo).

El pie **antes caía en vertical** (`180deg`) hacia `--color-primary-hover`, que
en seis paletas vale lo mismo que `primary`: quedaba plano. Ahora comparte
tono y dirección con la barra. Su texto blanco da como mínimo 5.42:1
(invierno) contra el extremo derecho, y los títulos en `accent-invert` 4.57:1
(noche). `.footer-bottom` sigue en `--color-primary-hover`, sin tocar.

La idea salió de café: **sw-5 a la izquierda y sw-4 a la derecha**
(`#3E2C23` → `#6F4E37`), o sea los dos tonos más oscuros de la paleta. Rafael
pidió extenderlo a las doce.

## Por qué hay un token nuevo y no se reusan roles

Probando con `--color-primary` → `--color-accent` (que en café da justo sw-5 →
sw-4) el resultado no se sostiene fuera de café:

- El texto del menú **no llega a 4.5:1** contra el extremo derecho en tech
  (3.70:1), halloween (3.31:1) y frío (3.12:1).
- En verano, primavera, oro, arcoíris, alegre y piel `accent` vale lo mismo
  que `primary`: barra plana, sin degradado.

Por eso cada bloque de `paletas.css` declara `--color-grad-2` con su ratio
anotado al lado. Cómo se eligió, en ese orden:

1. El segundo tono **entregado** más claro que el primario, si el texto le
   aguanta encima → café, invierno, halloween, noche.
2. `--color-primary-hover`, que ya es un tono del bloque → verano, primavera,
   oro, arcoíris, alegre, piel.
3. Derivado (`primary × 0.62`) sólo donde `primary-hover` vale lo mismo que
   `primary` y ningún entregado sirve → **tech** y **frío**, los dos únicos
   hexadecimales nuevos.

Verificado en las doce: peor texto encima del extremo derecho **4.57:1**
(noche) y ningún elemento de la barra queda con menos contraste del que ya
tenía contra el fondo plano. `--color-nav-brand` se controló aparte: donde hoy
ya falla contra el fondo (vale igual que `primary` en cuatro paletas) sólo se
exigió no empeorar.

El **panel lateral de móvil** (`.nav-panel`) también lo lleva, pero **en
vertical** (`to bottom`): es angosto y alto, así que el degradado corre por su
lado largo. Usa los mismos dos tonos, y como es un contenedor con scroll el
fondo no se mueve con el contenido (`background-attachment: scroll`, que es el
valor por defecto).

## Los botones oscuros (2026-08-23)

`--color-grad-2` también pinta los tres botones oscuros del catálogo:
`.btn-primary`, el chip de filtro encendido (`[aria-pressed="true"]`) y la
página actual de la paginación. **Al pasar el mouse el degradado se da
vuelta** (`to right` → `to left`), que fue lo que pidió Rafael.

- El borde de los tres pasó a `transparent`. No es un olvido: el fondo se
  pinta también bajo el borde, así que el degradado llega hasta el filo. Con
  el borde en `--color-primary`, el lado derecho no lo acompañaba. El tamaño
  no cambia porque el borde sigue ahí.
- **`background-image` no se puede animar**: el vuelco del rollover es
  instantáneo aunque `.btn` y `.page` tengan su `transition`. Para que
  fuera gradual habría que apilar dos capas y cruzar opacidades.
- La paginación **perdió su hover al acento** (`--color-accent` /
  `--color-on-accent`), que estaba puesto a propósito. Se reemplazó para que
  los tres botones respondan igual.
- Texto de botón (`--color-on-primary`) sobre el extremo derecho, medido en
  las doce: mínimo **4.85:1** (invierno). Ninguna baja del mínimo.

## Dos degradados que NO usan `--color-grad-2`

`--color-grad-2` se derivó del tono oscuro, así que sólo sirve para superficies
oscuras. Para estos dos alcanzó con pares que la paleta ya declara — **cero
tokens nuevos**:

- **Badge de descuento (`.price-off`)**: `--color-accent` → `--color-accent-hover`.
  Son distintos en las doce, y `on-accent` encima del extremo derecho no baja
  de 6.81:1 (oro).
- **Caja de ventajas (`.benefits`)**: `--color-surface-alt` → `--color-grad-alt-2`.
  El primer intento terminaba en `--color-surface` (blanco puro) y Rafael lo
  rechazó: en verano desteñía la caja entera. Pidió ir de un tono de la paleta
  a otro (sw-3 → sw-4 o sw-2).

  Se pudo **en cuatro paletas nada más** — verano (sw-2), primavera (sw-2),
  arcoíris (sw-3) y alegre (sw-3). En las otras ocho **ningún tono entregado
  aguanta el texto encima**: la caja es clara y el cuerpo va en
  `--color-text-muted`, que sobre `surface-alt` ya está apenas arriba de 4.5:1,
  así que cualquier tono más oscuro lo hunde. Ahí el token es `surface-alt`
  **aclarado un 35 %** hacia el blanco: con texto oscuro aclarar sólo sube el
  contraste, y queda un degradado suave en vez del lavado a blanco.

  Peor caso medido a la derecha: 5.16:1 el título (piel), 5.04:1 el detalle
  (invierno), 3.65:1 el ícono (frío, mínimo 3:1).

Ninguno de los dos lleva rollover invertido: no son botones.

## Detalles de implementación

- La regla vive en el `@media` de 62rem de `components.css`: **la barra sólo
  existe en escritorio**, en móvil y tablet no se dibuja.
- El selector es `html:not([data-nav="color"]) .nav`. El `html` **no es
  decorativo**: suelto, el `:not()` lo cumpliría cualquier ancestro (body,
  header) y el degradado se colaría también en la variante de barra de color,
  que tiene su propio fondo.
- `background-color` sigue puesto: queda de respaldo debajo del degradado.
- **Pendiente:** `tools/estricto.py` y `tools/generar2.py` no conocen este
  token, así que regenerar `paletas.css` lo borra — el mismo problema que el
  blanco del banner. Ver [[prototipo-3-header-nav]].
