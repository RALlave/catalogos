---
name: prototipo-3-ficha-producto
description: prototipo-3 — ficha de producto: galería con desvanecido y respaldo :target, pestañas con ARIA, layout por tokens desde 768px
metadata:
  type: project
---

`prototipo-3/producto.html`, maquetada el 2026-08-23. Cabecera, nav, pie y panel
de diseño se copiaron tal cual de `index.html`: son el mismo markup, no una
variante. Cuando `index.html` cambia uno de esos bloques, hay que volver a
copiarlo acá.

## Galería con desvanecido (fade)

Las cuatro fotos están en el HTML apiladas en `.gallery-viewer`; sólo
`.is-active` se ve (`opacity` + `visibility`, con `--transition-slow`). La
primera ya viene marcada desde el HTML.

Por qué: sin JS igual se ve una foto, y las miniaturas siguen siendo enlaces
`#foto-N`.

El respaldo sin JS es `:target`, con las reglas colgadas de
`.gallery:not([data-ready])`. El script pone `data-ready` al arrancar y desde
ahí manda él, así el hash de la URL no pelea con el clic en la miniatura. Las
flechas nacen con `hidden` y las muestra el script — sin él no harían nada.

No hay autoplay: la WCAG 2.2.2 pediría un botón de pausa. Si se agrega, va con
pausa.

## "Más sobre este producto" son pestañas

Empezó como acordeón `<details>` y se cambió a pestañas el 2026-08-23 a pedido.
Es el único lugar del prototipo donde hace falta ARIA: no existe elemento nativo
para pestañas.

Mismo patrón de mejora progresiva que la galería — sin JS la lista es un índice
de enlaces `#panel-*` y los tres paneles se ven enteros con su `<h3>`; el script
pone `data-ready` en `.tabs`, agrega `role=tablist/tab/tabpanel`, oculta los
`<h3>` (los dice la pestaña) y deja sólo el panel elegido.

Teclado: flechas izquierda/derecha con vuelta, Home/End, y tabulador roving
(`tabindex` 0/-1). Los paneles llevan `tabindex="0"` para poder desplazarlos con
el teclado. Si la URL entra con `#panel-talle`, abre esa pestaña.

La pestaña activa suma subrayado grueso + peso + color, y el borde inferior ya
existe transparente para que no salte un pixel al seleccionar.

## Layout por tokens

`--detail-columns`, `--detail-gap`, `--detail-title`, `--gallery-ratio`,
`--thumb-size` y `--related-columns` viven en el `:root` de `base.css` y
se redefinen en los tres breakpoints. En `components.css` no hay ni un ancho por
breakpoint.

La ficha se parte en dos **desde 768px**, no desde 992: en una sola columna la
foto cuadrada ocupaba toda la pantalla de una tablet.

## Relacionados

Cuatro `<li>` copiados de `index.html` sin los `data-category`/`data-name`
del filtro y **sin `data-grid`**: el módulo de catálogo de `app.js` corta si no
encuentra ese gancho, así que acá no se activan filtro ni paginación. Van a 4
columnas desde 992px (`--related-columns`), no a las 3 de
`--grid-columns`.

## Íconos y colores

Cinco `<symbol>` nuevos que el sprite de `index.html` no tiene: `#i-etiqueta`,
`#i-compartir`, `#i-camion`, `#i-cambio`, `#i-escudo`. Si alguno se necesita en
el catálogo, hay que copiarlo.

Ningún hexadecimal nuevo: el descuento reusa el par del badge "Nuevo"
(`--color-badge-nuevo-*`) y el detalle de las ventajas usa `--color-text-muted`
sobre `--color-surface-alt`, ≥ 4.62:1 en las 12 paletas.

Verificado en navegador a 340 / 991 / 1440: sin desborde horizontal y sin
errores de consola. Ver también [[prototipo-3-grilla]].
