---
name: prototipo-3-pie
description: prototipo-3 — pie de página de cuatro columnas, por qué el texto es blanco y no el tono claro de la paleta
metadata:
  type: project
---

Pie de `prototipo-3/`, maquetado el 2026-08-23. Vive en su propio archivo y no
dentro de [[prototipo-3-header-nav]] a propósito: ese archivo lo reescriben
otras sesiones y ya se llevó puesta esta nota una vez.

## Estructura

Cuatro columnas sobre fondo oscuro + barra inferior, según la referencia FinPRO
que trajo Rafael. Decidido con él: **cuatro** columnas (no las tres de las
instrucciones del proyecto) y fondo **oscuro** en las doce paletas.

- Col 1: marca + descripción. Col 2: `Contenido` (nav). Col 3: `Contacto`
  (`<address>` con WhatsApp, teléfono, mail, horario y dirección). Col 4:
  `Seguinos` con YouTube, Facebook, Instagram y TikTok.
- La cuarta columna **no** es un formulario de suscripción: Rafael la cambió
  por las redes, y por eso la columna 1 quedó sin el bloque "Follow us" de la
  referencia.
- Cada red es un enlace con **ícono + nombre**: el ícono solo no alcanza como
  señal.
- Íconos nuevos en el sprite: `i-mail`, `i-pin`, `i-youtube`, `i-facebook`,
  `i-instagram`, `i-tiktok`.

## El texto es BLANCO, y ese es el punto

Primero se pintó el cuerpo del pie con `--color-on-dark-muted`, que es lo que
usa la barra de navegación. **Estuvo mal.** Ese token es el tono claro de cada
paleta y en varias (alegre y arcoíris son amarillos, verano y oro cremas) el
pie entero quedaba amarillo y gritaba. Rafael lo marcó apenas lo vio.

Después se pintó con `--color-on-dark` y **también estuvo mal**: ese token
tampoco es blanco. En diez de las doce paletas es un tono de la paleta (en
halloween, el naranja `#F4A100`), así que el pie volvió a salir monocromo
naranja. Rafael lo marcó de nuevo el 2026-08-23 con una captura.

El blanco puro ahora es un token propio, `--color-footer-text: #FFFFFF`,
declarado en los doce bloques de `paletas.css` al lado de `--color-brand-1`
(mismo hex y mismos ratios ya medidos contra los dos extremos del degradado).
El pie declara dos variables y todo lo demás las lee:

```
--footer-text:  var(--color-footer-text);      /* blanco puro, por token */
--footer-accent: var(--color-accent-invert);
```

El color de la paleta queda **sólo para lo que sí es acento**: íconos, el
filete bajo los títulos, la segunda palabra de la marca y el hover. Cambiar de
idea es cambiar esas dos líneas.

**Única excepción blanca:** los `<h2>` de columna. `.footer-title` fija
`color: var(--color-on-dark)` a mano para que el bloque tenga jerarquía de
color y no sea un muro blanco parejo.

**Regla general que sale de acá:** el tono claro de una paleta sirve como
acento sobre fondo oscuro, no como color de cuerpo. Cuando el bloque tiene
mucho texto, blanco.

## Color: por qué el pie es seguro en las doce paletas

El cuerpo lleva `linear-gradient(--color-primary → --color-primary-hover)` y la
barra inferior `--color-primary-hover`. **En las doce paletas `primary-hover`
es más oscuro que `primary`**, así que el extremo más claro del gradiente es
`primary` y todos los ratios se miden contra ÉL, que es el peor caso. Mínimos
medidos sobre `primary` en las doce: blanco 8.91:1, `accent-invert` 4.80:1.

El gradiente se arma con `var()`, no con hexadecimales: `components.css` sigue
sin un solo hex.

## Tokens nuevos en base.css

`--footer-columns` (1 col → 2 → `1.4fr 1.15fr 1.25fr 1fr` → `1.5fr 1.1fr 1.2fr 1fr`),
`--footer-gap`, y `--footer-list-rows` / `--footer-list-flow` para que la lista de
`Contenido` se llene **por columnas** desde 768px (se lee de arriba abajo, no en
zigzag). El pie **no** usa `--grid-columns`: esa es la grilla de productos y
no tiene por qué tener la misma cantidad de columnas.

`base.css` también suma `.footer :focus-visible` al lado de `.nav :focus-visible`,
para que el anillo de foco use `--color-focus-invert` sobre fondo oscuro.

## Ojo con esto

- Los `<h2>` del pie son títulos de columna; el `<h1>` sigue siendo el del
  banner.
- El enlace de la página actual lleva `aria-current="page"` + peso + subrayado.
  Desde el pedido de Rafael **también es blanco**, así que el color ya no lo
  distingue y las otras dos señales son las únicas: no se pueden sacar. El
  hover de las redes se marca **sólo con el subrayado**: el texto ya es blanco
  y no puede "aclararse" más.
- **El ícono de cada red va suelto** (1.375rem, sin `padding` y sin fondo).
  Antes tenía una caja de 2.25rem con relleno que al pasar el mouse se
  invertía —fondo acento, ícono del color del pie—; Rafael pidió sacar ese
  recuadro y el relleno (2026-08-23). El subrayado del texto sigue siendo la
  señal de hover, así que no quedó marcada sólo por color.
- El nombre de la tienda se parte en dos colores con
  `<span class="brand-name-alt">`, no con `<strong>`: es color, no énfasis
  semántico. Está en las seis marcas de las tres páginas (cabecera + pie).
- Con `data-background="image"` el pie deja el gradiente y pasa a vidrio
  (`--color-glass-dark` + `backdrop-filter`), y la barra inferior se vuelve
  transparente para no apilar dos velos.
- Los datos de contacto son los mismos placeholders del header
  (`hola@tiendanube.com`, teléfonos inventados). Cuando lleguen los reales hay
  que cambiarlos en los dos lugares.

## Sesiones en paralelo — el problema real del día

Mientras se maquetaba el pie **otra sesión estaba editando los mismos
archivos**, y pasó tres veces:

1. `device_commit_files` rechazó por mtime y hubo que remergear (apareció y
   después desapareció un logotipo real, `assets/img/logo-6.png`, marca "Avni
   Outfit Hub", con los tokens `--logo-height` / `--logo-height-lg`).
2. La otra sesión reescribió `components.css` desde una copia vieja y **borró
   las ~200 líneas del bloque `PIE DE PÁGINA`**. El HTML y los tokens seguían,
   así que el pie se veía sin estilos. Hubo que reinsertarlo.
3. Lo mismo con esta nota dentro de `prototipo-3-header-nav.md`.

**Antes de escribir cualquier archivo de `prototipo-3/`, releerlo del disco.**
`base.css` quedó con `--logo-height*` declarados y sin usar: si el logotipo real
vuelve, ya están; si no, se borran.

## Verificado en navegador

340px, 991px y 1440px sin scroll horizontal, con bordes cuadrados y redondos, y
en las paletas café, verano, noche, alegre, arcoíris y oro.
