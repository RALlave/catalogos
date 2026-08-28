---
name: prototipo-3-separadores-invisibles
description: prototipo-3 — --color-on-dark-border es invisible en 4 de las 12 paletas: todos los filetes sobre fondo oscuro (menú, panel, pie) desaparecen ahí
metadata:
  type: project
---

Detectado el 2026-08-23, cuando Rafael no veía el separador entre los ítems del
menú y el bloque de horario/dirección del panel. **La regla estaba puesta: el
color es el que no se ve.**

`--color-on-dark-border` medido contra `--color-primary` (el fondo de la barra,
del panel y del pie) en las doce paletas:

| paleta | ratio |
|---|---|
| verano, primavera, alegre | **1.00:1** — el token es idéntico al fondo |
| noche | 1.81 |
| frío | 1.92 |
| invierno | 2.30 |
| piel | 2.45 |
| arcoíris | 2.49 |
| halloween | 2.57 |
| oro | 2.88 |
| tech | 3.09 |
| café | 3.17 |

En esas tres paletas el generador le asignó al borde el mismo hex que a
`--color-primary`, así que **todo filete sobre fondo oscuro desaparece**: los
`.menu-item + .menu-item`, el `border-top` de `.menu`, y los del pie
(`components.css` líneas ~305, ~309, ~1237, ~1361).

`--color-nav-brand` **no sirve de reemplazo**: en verano, primavera, alegre y
arcoíris también vale 1.00:1 contra el fondo.

Lo único con contraste garantizado sobre la barra en las doce es el color del
texto: `--color-on-dark` / `--color-on-dark-muted` (blanco 8.91:1,
`accent-invert` 4.80:1 según la medición del pie).

## Qué se hizo y qué falta

- **Hecho:** el separador de `.nav-info-list` pasó a `border-top: … var(--nav-text)`.
  Sólo ese.
- **Pendiente:** los demás filetes sobre oscuro siguen con `--nav-border` /
  `--color-on-dark-border` y siguen invisibles en esas paletas. Arreglarlo de
  raíz es tocar el generador (`tools/estricto.py` + `tools/generar2.py`) para
  que ese rol exija un mínimo —1.5:1 alcanza para un separador decorativo— y
  regenerar `paletas.css`. No se hizo porque estaba fuera de lo pedido y toca
  los 12 bloques.
