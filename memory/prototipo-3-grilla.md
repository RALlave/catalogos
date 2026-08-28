---
name: prototipo-3-grilla
description: prototipo-3 — grilla de productos, badges por alias de tokens, tonos de producto, filtro y paginación como mejora progresiva
metadata:
  type: project
---

Grilla de productos de `prototipo-3/index.html`, maquetada el 2026-08-23.
Categorías con datos: `ropa-hombre` y `gafas`, 12 productos cada una (24 en
total), 8 por página.

## Badges de estado — un solo bloque para las 12 paletas

`--color-badge-*` NO repite hexadecimales: son alias de pares que cada paleta ya
declara y ya tiene su ratio anotado (`accent/on-accent`, `surface/text`,
`primary/on-primary`).

Por qué: una paleta nueva hereda badges correctos sin tocar nada. Con los
valores del 2026-08-23 el peor caso de los tres pares es 4.56:1
(`primary/on-primary` en Frío).

Al agregar una paleta alcanza con declarar esos seis tokens de siempre. Si algún
día un par baja de 4.5:1, se corrige en la paleta, no en el bloque de badges.

Ojo: el par de "destacado" era `surface-alt/text` y hubo que cambiarlo a
`surface/text` cuando las paletas cambiaron sus `--color-on-*` — en la paleta
Piel había caído a 3.88:1. Al ser el badge claro, lleva borde
`--color-border-strong` para no perderse sobre una foto de producto clara.

Los tres estados están en luminancias separadas (media / clara / oscura) para
que se distingan en escala de grises. Igual cada badge lleva ícono + texto
(`#i-nuevo`, `#i-estrella`, `#i-agotado`), así que el color nunca es la única
señal. "Agotado" además apaga la foto con `grayscale`.

## Tonos de producto son dato del producto, no de la paleta

`--tono-*` vive en su propio bloque de `paletas.css` y no cambia al cambiar de
paleta. Cada muestra lleva su nombre en `.visually-hidden` y borde
`--color-border-strong` para no desaparecer si el tono es casi blanco.

## Filtro y paginación son mejora progresiva

Los 24 productos están en el HTML; `app.js` sólo decide cuáles se ven. Sin JS se
ven los 24 y la paginación queda con `hidden`. El buscador del header filtra por
nombre (sin tildes) contra `data-name`.

La página actual va con fondo `--color-primary` (oscuro) y, por pedido de
Rafael (2026-08-23), al pasar el mouse **cambia a `--color-accent`**: la regla
`.page[aria-current="page"]:hover` va **después** de la del activo porque
empata en especificidad con `.page:hover:not(:disabled)` y gana la última.
Limitación conocida: en cuatro paletas (verano, primavera, arcoíris, alegre)
`--color-accent` es el mismo hex que `--color-primary`, así que ahí el hover no
se ve. Si molesta, esas cuatro necesitan un tono propio para el estado.

## Respaldo

`prototipo-3/_grilla/` tiene los cuatro pedazos por separado (markup, CSS de
grilla, badges y tonos, JS) con un `LEEME.md` que dice dónde va cada uno. Existe
porque la grilla se perdió cuatro veces por escrituras concurrentes — ver la
sección "Peligros conocidos" de `prototipo-3/CLAUDE.md`.

Las tarjetas enlazan a `producto.html` — ver [[prototipo-3-ficha-producto]].
Todas apuntan a la misma ficha: es una maqueta, no hay una página por producto.
