---
name: prototipo-3-contacto
description: prototipo-3 — contacto.html: banner corto, datos, horarios, formulario que arma el mensaje de WhatsApp y mapa de Google embebido
metadata:
  type: project
---

`prototipo-3/contacto.html` (2026-08-23). Era el pendiente "falta contacto.html"
que anotaba el `CLAUDE.md` de la carpeta. Estructura final:
**banner + datos + horarios + mapa**.

**El formulario se sacó el mismo día, a pedido de Rafael.** Se había hecho uno
que armaba el mensaje y abría WhatsApp; ocupaba la segunda columna y ahí ahora
va la tarjeta de horarios, con las dos columnas del mismo peso
(`repeat(2, minmax(0, 1fr))`). Se borró todo lo suyo: el `<form>`, el bloque
`.form`/`.campo-*` de `components.css`, el wrapper `.contacto-col`, el módulo
`[data-form-wa]` de `app.js` y el `textarea` que se le había sumado al reset de
`base.css`. Si alguna vez vuelve, el contacto del catálogo pasa **sólo** por los
enlaces de WhatsApp / teléfono / mail de la tarjeta de datos.

## Decisiones

- **Banner corto.** Es el MISMO componente `.banner` (misma foto, mismo velo,
  los dos toggles lo siguen afectando); el modificador `.banner-short` sólo
  reescribe un token: `--banner-height: var(--banner-height-short)`. Ese token
  existe aparte y no es un `calc()` sobre `--banner-height` porque una custom
  property no puede referirse a sí misma. Vale la mitad en los tres cortes:
  11 / 14 / 15rem contra 22 / 28 / 30rem.
- **Mapa: iframe con `?q=<dirección>&output=embed`.** Esa forma **no pide clave
  de API**, a diferencia de la Maps Embed API oficial. Se cambia de local
  cambiando la dirección. **Ojo: está en cuatro lugares** — el `q=` del iframe,
  el `title` del iframe, el `q=` del botón "Cómo llegar" y el texto del
  `figcaption`.
- Los enlaces `Contact` del nav y del pie de `index.html` y `producto.html`
  ahora apuntan a `contacto.html` (2 enlaces por archivo). La columna del pie
  conserva su `id="contacto"`, que ya no lo apunta nadie.

## Cómo se armó (repetir así)

`contacto.html` NO se escribió a mano: un script leyó `index.html` **del disco**
y extrajo sprite, cabecera, pie y panel de diseño, les aplicó los parches de
enlaces (`#x` → `index.html#x`, `aria-current` a Contact) y pegó el `<main>` en
el medio. Es el procedimiento que el `CLAUDE.md` de la carpeta describe para
`producto.html`, y evita que la página nazca desfasada de la otra sesión que
edita los mismos archivos.

No se agregó ni un `<symbol>`: la página usa sólo íconos que ya están en los dos
sprites, así que la divergencia conocida entre `index.html` y `producto.html` no
creció.

## Estado

Verificado **estáticamente**: etiquetas balanceadas, sin ids duplicados, ningún
`<label for>` huérfano y ningún `var(--token)` sin definir en los tres CSS.
**Falta la pasada por navegador a 340 / 991 / 1440** que pide el `CLAUDE.md`:
en esta máquina no hay Playwright instalado.

Inconsistencia heredada, no inventada acá: la cabecera dice "Av. Ballivián 1234,
Calacoto, La Paz" y el pie "Av. Ballivián 267, Santa Cruz de la Sierra". La
página de contacto y el mapa usan la del pie.
