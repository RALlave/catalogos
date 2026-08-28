---
name: prototipo-3-fondo-con-foto
description: prototipo-3 — data-background="image" activo sólo en index.html con bgs/bg-4.jpg; velos al 70% y sin desenfoque, con el contraste que eso deja
metadata:
  type: project
---

> **ESTADO: apagado.** Fue una prueba. El 2026-08-23 Rafael sacó el
> `data-background="image"` del `<html>` de `index.html` — «veremos si más adelante
> aplicamos o no». **Ninguna página lo tiene hoy**, así que toda la variante
> está inactiva. Volver a encenderla es agregar ese atributo, nada más; el CSS
> quedó entero. Lo de abajo es el detalle de la prueba, para no rehacerla.

2026-08-23. Rafael pidió probar el fondo con foto **sólo en el home**.

- `--bg-image` en `base.css` apunta ahora a `../img/bgs/bg-4.jpg`. Antes
  apuntaba a `assets/img/fondo.jpg`, que **nunca existió**: por eso la variante
  se veía sin foto.
- El interruptor es `data-background="image"` en el `<html>` de `index.html`
  **nada más**. `producto.html` y `contacto.html` no lo tienen, y por eso el
  cambio no las toca aunque el CSS sea compartido.
- `tema.js` no conoce `data-background`, así que no lo pisa ni lo guarda. Tampoco
  está en el panel de diseño: se prende y se apaga a mano.
- El degradado de la barra es opaco y tapaba el velo, así que bajo esta
  variante se apaga con `background-image: none`. El pie conserva el suyo: la
  variante nunca lo cubrió.

## La foto va NÍTIDA (se probó desenfocarla y se descartó)

Se probó en 10px, 5px y 2px, y Rafael terminó sacando el desenfoque: la foto
va como `background-image` del `body` con `background-attachment: fixed`, sin
`filter` ni pseudo-elemento, y el token `--blur-fondo` se borró.

**Si alguna vez vuelve, el desenfoque NO va en el `body`**: `filter` afecta a
todo el subárbol y desenfocaría también el texto y las tarjetas. La forma
correcta, que es la que estuvo puesta, es un `body::before` con
`position: fixed`, `z-index: -1`, la foto de fondo y el `filter` encima, con un
`inset` negativo mayor que el radio del desenfoque — sin ese margen se ve el
borde deshilachado de la foto contra los cantos de la ventana. **La foto no está en el `body` sino en
`body::before`**: `filter` afecta a todo el subárbol, así que puesto en el
`body` habría desenfocado también el texto y las tarjetas. El pseudo-elemento
es `position: fixed` (reemplaza al viejo `background-attachment: fixed`) y
lleva `inset: -1.5rem`, más grande que el radio del desenfoque: sin ese margen
se ve el borde deshilachado de la foto contra los cuatro cantos de la ventana.

Efecto lateral bueno: el desenfoque promedia los extremos locales de la foto,
así que el peor caso de contraste de la tabla de abajo —calculado sobre la foto
nítida— mejora. **Cuánto, no está medido**: en esta máquina no hay Pillow para
leer los píxeles de la imagen.

## Velos al 70 % y SIN desenfoque — decisión de Rafael

Se sacó `backdrop-filter: blur()` de las tres reglas y los doce
`--color-glass` / `--color-glass-dark` pasaron de 0.82 / 0.88 a **0.7**.
`--color-glass-border` quedó en 0.55.

**Esto baja el contraste por debajo del mínimo en 11 de las 12 paletas**, medido
como se midió el banner: el velo compuesto contra el peor píxel posible de la
foto. Peor elemento sobre cada velo:

| paleta | velo claro | velo oscuro | alfa que haría falta (claro / oscuro) |
|---|---|---|---|
| noche | 6.68 | 6.29 | 0.70 / 0.70 — la única que cumple |
| halloween | 6.68 | 3.16 | 0.70 / 0.80 |
| primavera | 5.03 | 4.05 | 0.70 / 0.74 |
| verano · arcoíris · alegre | 4.99 | 4.24 / 2.95 / 3.40 | 0.70 / 0.73–0.87 |
| tech | 4.22 | 3.26 | 0.73 / 0.83 |
| café | 3.53 | 3.95 | 0.79 / 0.75 |
| frío | 3.34 | 2.30 | 0.82 / 1.00 (ni opaco) |
| piel | 2.76 | 3.10 | 0.89 / 0.89 |
| oro | 2.87 | 2.86 | 0.88 / 0.92 |
| invierno | 2.57 | 4.44 | 0.92 / 0.71 |

El elemento que rompe casi siempre es `--color-text-muted` sobre el velo claro
y `--color-on-dark-muted` sobre el oscuro — el cuerpo, no los títulos.

Queda así **porque Rafael lo pidió explícitamente**, sabiendo el número. Si más
adelante se busca cumplir AA sin volver al desenfoque, las salidas son: subir
el alfa a ~0.9 (0.92 cubre a las doce en el velo claro), o cambiar `bg-4.jpg`
por una foto sin zonas quemadas ni sombras profundas —el rango de la foto es lo
que fija el peor caso—. Con el desenfoque puesto el peor caso mejoraba porque
el `blur` promedia los extremos locales.
