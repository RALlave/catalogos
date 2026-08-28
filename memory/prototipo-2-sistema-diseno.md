---
name: prototipo-2-sistema-diseno
description: Sistema de diseño del prototipo-2 (catálogo de una sola página) — tokens, paleta con ratios WCAG y reglas de badges
metadata:
  type: project
---

`prototipo-2/` es una maqueta estática (HTML/CSS/JS vanilla, sin frameworks ni
preprocesadores) de **una sola página**: header + buscador, filtro por categoría,
grilla de productos y footer. Datos hardcodeados en `assets/js/data.js` con la
misma forma que devolvería la API (tienda + categorías + productos).

La tienda de ejemplo es **Tienda Nube** (celulares, moda y accesorios), 18
productos en 6 categorías, con fotos reales que ya estaban en
`assets/img/<categoria>/`: `celulares`, `zapatillas`, `ropa-hombre`, `perfumes`,
`gafas`, `unas`. El campo `imagen` del producto guarda la ruta relativa. Las
tarjetas usan recorte cuadrado (`--producto-ratio: 1 / 1` + `object-fit: cover`)
porque las fotos vienen con proporciones muy distintas.

Es un segundo prototipo, hermano de `prototipo/`. No reemplaza al primero: el
primero define los 4 layouts multipágina; este explora el sistema de diseño
tokenizado.

## Decisiones de diseño (fecha: 2026-08-23)

Dirección elegida con la skill `ui-ux-pro-max` (`--design-system`): estilo **Flat
Design** (sin gradientes ni sombras, transiciones 150–200 ms) y paleta neutra
piedra para que la foto del producto sea el color de la página.

Tipografía: **Poppins** (Google Fonts) para títulos y cuerpo. La skill había
sugerido Rubik + Nunito Sans; Rafael pidió Poppins.

- **Primario** `#1C1917` con **texto encima `#FFFFFF`** (17.49:1).
- **Acento** `#B45309` con **texto encima `#FFFFFF`** (5.02:1). El color de
  texto sobre primario y sobre acento está declarado explícitamente, no asumido.
- **WhatsApp** `#075E54` con blanco (7.67:1). Se descartó `#128C7E` porque con
  blanco da 4.14:1 y no llega al mínimo de 4.5:1.
- Cada hex de `:root` lleva su ratio de contraste anotado al lado, medido contra
  el fondo real donde se usa.

## Reglas propias de esta maqueta

- **Todos** los colores viven en `:root` de `base.css`. Ningún hex dentro de
  reglas de componentes ni en el JS (las miniaturas SVG leen los tokens con
  `getComputedStyle`).
- Tipografía, espaciado, radios, ritmo vertical y columnas de grilla también son
  custom properties. Los breakpoints **sólo redefinen tokens**
  (`--grilla-columnas`, `--contenedor-pad`, …); los componentes no repiten reglas.
- **Ningún estado se comunica sólo por color**: los badges (nuevo / destacado /
  agotado) llevan ícono SVG + texto, y "agotado" además cambia de forma
  (esquinas rectas en vez de píldora).
- El filtro de categoría son **radios nativos** ocultos con la técnica
  `clip-path: inset(50%)` (nunca `opacity:0` ni `pointer-events:none`, que los
  sacan del árbol de accesibilidad). Así se navega con flechas gratis y el
  anillo de foco se pinta sobre el `<label>`.
- Íconos SVG inline, nunca emojis.
- Se respeta la convención del proyecto: `p`, `h1`–`h6`, `ul`, `li`, `a`, `s`
  sin `class`; el estilo se aplica desde el wrap (`.pie-wa a`, `.producto-accion a`).

## Verificado en navegador

340px (sin scroll horizontal), 991px (2 columnas) y 1440px (4 columnas);
búsqueda insensible a tildes, contadores por chip, estado vacío con botón de
reset que devuelve el foco al buscador, y foco visible por teclado.

`prototipo-2/.claude/launch.json` levanta un `python -m http.server` en el
puerto 5599 para previsualizar.
