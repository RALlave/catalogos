# Respaldo de la grilla de productos

Copia de seguridad de la grilla de `index.html`. Existe porque durante el
armado estos archivos se pisaron cuatro veces desde otra sesión y hubo que
rehacer el merge cada vez. Si la grilla vuelve a desaparecer, restaurar desde
acá lleva un minuto en vez de rehacerla.

## Qué va dónde

| Archivo | Destino |
|---|---|
| `seccion-productos.html` | `index.html` — los tres `<symbol>` van dentro del `<svg class="sprite">`, después de `#i-flecha`; la `<section>` reemplaza a `<section class="seccion" id="productos">` dentro de `<main>`. |
| `grilla.css` | `assets/css/components.css` — reemplaza al bloque `SECCIÓN`. Las tres reglas comentadas al final van dentro del `@media (min-width: 62rem)`. |
| `badges-y-tonos.css` | `assets/css/paletas.css` — se pega al final del archivo. |
| `catalogo.js` | `assets/js/app.js` — se pega al final del archivo. |

## Tokens que además tiene que tener `assets/css/base.css`

Dentro del `:root`:

```css
    --producto-ratio:  1 / 1;
    --producto-pad:    var(--space-4);
    --tono-tamano:     1rem;
    --pagina-tamano:   var(--touch-min);
```

Y `--grilla-columnas: 2;` dentro del `@media (min-width: 48rem)`.

## Cosas que conviene no romper al retocar

- Los `--color-badge-*` son alias de pares que cada paleta ya declara
  (`accent/on-accent`, `surface/text`, `primary/on-primary`). No poner
  hexadecimales ahí: una paleta nueva hereda badges correctos sola.
- El estado nunca viaja sólo en el color: cada badge lleva ícono + texto, y
  "Agotado" además apaga la foto con `grayscale`.
- El filtro y la paginación son mejora progresiva: los 24 productos están en
  el HTML y sin JS se ven todos, con la paginación en `hidden`.
