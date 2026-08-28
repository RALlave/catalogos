# -*- coding: utf-8 -*-
import sys
sys.path.insert(0, '/home/claude/tools')
from estricto import *

PALETAS = [
 ('cafe','Café',           ['#EDE0D4','#C9A27E','#A47148','#6F4E37','#3E2C23'], None, None, '#6F4E37'),
 ('verano','Verano',       ['#00C2CB','#7FE0D4','#FFE38A','#FF9A76','#FF5D8F'], None, None, '#00C2CB'),
 ('primavera','Primavera', ['#FFF3C9','#C9E4A6','#F7B7C4','#A3D9C9','#7FB4E0'], None, None, '#7FB4E0'),
 ('oro','Oro',             ['#FBF3D0','#EBD08C','#D4AF37','#B8860B','#7C5E10'], None, None, '#D4AF37'),
 ('arcoiris','Arcoíris',   ['#FF5A5F','#FFB400','#3DDC84','#00A6ED','#8B5CF6'], None, None, '#8B5CF6'),
 ('tech','Tech',           ['#E7DFF7','#C6B4EE','#A48AE0','#7B5FD1','#4E2FB0'], None, None, '#7B5FD1'),
 ('alegre','Alegre',       ['#FF6B6B','#FFD93D','#6BCB77','#4D96FF','#FF6FB5'], None, None, '#4D96FF'),
 ('invierno','Invierno',   ['#E3F6F5','#A7E0DB','#5FB0C9','#3E6D9C','#2A2F63'], None, None, '#3E6D9C'),
 ('piel','Piel',           ['#F8D9C0','#E9B98F','#D89B72','#B87A56','#8A5A3C'], None, None, '#8A5A3C'),
 ('halloween','Halloween', ['#F4A100','#E8730C','#7A3E9D','#3E1F47','#1A1423'], None, None, '#7A3E9D'),
 ('frio','Frío',           ['#C1D993','#A7D078','#B560E1','#7C63BB','#6941B0'], None, None, '#6941B0'),
 ('noche','Noche',         ['#1F4973','#3124A5','#392184','#17224D','#271538'], None, None, '#1F4973'),
]

def rgba(h, a):
    r, g, b = hex2rgb(h)
    return 'rgba(%d, %d, %d, %s)' % (r, g, b, a)

def marca(hex_, o):
    return 'de la paleta' if hex_ in o else 'DERIVADO'

def bloque(slug, titulo, o, d, n):
    L=[]; A=L.append
    usados = sorted({v for k, v in d.items() if isinstance(v, str) and v in o}, key=o.index)
    sin_uso = [c for c in o if c not in usados]
    A('/* ============================================================')
    A('   PALETA %02d — "%s"' % (n, titulo))
    A('   Entregada: ' + ' · '.join(o))
    A('   Usa tal cual: ' + ', '.join(usados))
    if sin_uso:
        A('   Todavía sin rol: ' + ', '.join(sin_uso) + '  (entran con los badges)')
    if d['derivados']:
        A('')
        A('   DERIVADOS — la paleta no puede cubrir estos roles sin romper el')
        A('   mínimo WCAG, así que se derivó el tono conservando el matiz:')
        for nombre_tok, hexd, motivo in d['derivados']:
            A('     · %s → %s' % (nombre_tok, hexd))
            A('       %s' % motivo)
    else:
        A('   Sin derivados: los cinco roles se cubren con la paleta.')
    A('   ============================================================ */')
    A('')
    A(':root,\n[data-paleta="cafe"] {' if slug == 'cafe' else '[data-paleta="%s"] {' % slug)
    A('')
    A('    /* Superficies — el fondo de página es blanco por decisión del brief */')
    A('    --color-bg:            #FFFFFF;')
    A('    --color-surface:       #FFFFFF;')
    A('    --color-surface-alt:   %s;  /* %s · %s:1 con text */' % (d['surf_alt'], marca(d['surf_alt'], o), r2(cr(d['ink'], d['surf_alt']))))
    A('    --color-border:        %s;  /* %s · %s:1 sobre surface — separador decorativo */' % (d['borde'], marca(d['borde'], o), r2(cr(d['borde'], BLANCO))))
    A('    --color-border-strong: %s;  /* %s · %s:1 sobre surface — borde de control (mín. 3:1) */' % (d['border_strong'], marca(d['border_strong'], o), r2(cr(d['border_strong'], BLANCO))))
    A('')
    A('    /* Texto sobre fondo claro */')
    A('    --color-text:        %s;  /* %s · %s:1 sobre surface · %s:1 sobre surface-alt */' % (d['ink'], marca(d['ink'], o), r2(cr(d['ink'], BLANCO)), r2(cr(d['ink'], d['surf_alt']))))
    A('    --color-text-muted:  %s;  /* %s · %s:1 sobre surface · %s:1 sobre surface-alt */' % (d['muted'], marca(d['muted'], o), r2(cr(d['muted'], BLANCO)), r2(cr(d['muted'], d['surf_alt']))))
    A('    --color-text-subtle: %s;  /* %s · %s:1 sobre surface */' % (d['subtle'], marca(d['subtle'], o), r2(cr(d['subtle'], BLANCO))))
    A('')
    A('    /* Primario y acento */')
    A('    --color-primary:       %s;  /* %s */' % (d['ink'], marca(d['ink'], o)))
    A('    --color-primary-hover: %s;  /* %s */' % (d['acento_hover'], marca(d['acento_hover'], o)))
    A('    --color-on-primary:    %s;  /* %s · %s:1 sobre primary */' % (d['on_primary'], marca(d['on_primary'], o), r2(cr(d['on_primary'], d['ink']))))
    A('')
    A('    --color-accent:        %s;  /* %s · %s:1 sobre surface */' % (d['acento'], marca(d['acento'], o), r2(cr(d['acento'], BLANCO))))
    A('    --color-accent-hover:  %s;  /* %s */' % (d['acento_hover'], marca(d['acento_hover'], o)))
    A('    --color-on-accent:     %s;  /* %s · %s:1 sobre accent */' % (d['on_accent'], marca(d['on_accent'], o), r2(cr(d['on_accent'], d['acento']))))
    A('')
    A('    --color-accent-soft:   %s;  /* %s · %s:1 sobre surface — íconos y bordes, NUNCA texto */' % (d['acento_soft'], marca(d['acento_soft'], o), r2(cr(d['acento_soft'], BLANCO))))
    A('    --color-accent-invert: %s;  /* %s · %s:1 sobre primary — sólo sobre la barra */' % (d['acento_inv'], marca(d['acento_inv'], o), r2(cr(d['acento_inv'], d['ink']))))
    A('')
    A('    /* Texto sobre la barra oscura */')
    A('    --color-on-dark:        %s;  /* %s:1 sobre primary */' % (d['on_primary'], r2(cr(d['on_primary'], d['ink']))))
    A('    --color-on-dark-muted:  %s;  /* %s:1 sobre primary */' % (d['on_dark_muted'], r2(cr(d['on_dark_muted'], d['ink']))))
    A('    --color-on-dark-border: %s;  /* %s:1 sobre primary — separador decorativo */' % (d['on_dark_borde'], r2(cr(d['on_dark_borde'], d['ink']))))
    A('')
    A('    /* Barra de navegación de color (data-nav="color") */')
    A('    --color-nav-bg:     %s;  /* %s */' % (d['nav_bg'], marca(d['nav_bg'], o)))
    A('    --color-on-nav:     %s;  /* %s · %s:1 sobre nav-bg */' % (d['nav_texto'], marca(d['nav_texto'], o), r2(cr(d['nav_texto'], d['nav_bg']))))
    A('    --color-nav-activo: %s;  /* %s · %s:1 sobre nav-bg */' % (d['nav_activo'], marca(d['nav_activo'], o), r2(cr(d['nav_activo'], d['nav_bg']))))
    A('    --color-nav-marca:  %s;  /* %s · %s:1 — barra del activo y foco (mín. 3:1) */' % (d['nav_marca'], marca(d['nav_marca'], o), r2(cr(d['nav_marca'], d['nav_bg']))))
    A('    --color-nav-borde:  %s;  /* %s · separador decorativo */' % (d['nav_borde'], marca(d['nav_borde'], o)))
    A('')
    A('    /* Foco (mín. 3:1 contra el fondo donde se pinta) */')
    A('    --color-focus:        %s;  /* %s:1 sobre surface */' % (d['acento'], r2(cr(d['acento'], BLANCO))))
    A('    --color-focus-invert: %s;  /* %s:1 sobre primary */' % (d['acento_inv'], r2(cr(d['acento_inv'], d['ink']))))
    A('')
    A('    /* Vidrio (fondo con imagen + blur) */')
    A('    --color-glass:        rgba(255, 255, 255, 0.82);')
    A('    --color-glass-dark:   %s;' % rgba(d['ink'], '0.88'))
    A('    --color-glass-border: %s;' % rgba(d['surf_alt'], '0.55'))
    A('    --bg-imagen-fallback: %s;' % d['surf_alt'])
    A('')
    A('    /* Velos del banner */')
    A('    --color-banner-velo:       %s;' % rgba(d['ink'], d['a_osc']))
    A('    --color-on-banner:         %s;  /* %s:1 en el peor caso */' % (d['on_primary'], d['r_osc']))
    A('    --color-banner-velo-claro: %s;' % rgba(d['surf_alt'], d['a_cla']))
    A('    --color-on-banner-claro:   %s;  /* %s:1 en el peor caso */' % (d['ink'], d['r_cla']))
    A('}')
    A('')
    return '\n'.join(L)

CABECERA = """/* ============================================================
   PALETAS — el único archivo con hexadecimales
   prototipo-3

   Un bloque por paleta. Se cambia con `data-paleta` en <html>:
       <html data-paleta="verano">
   Sin atributo manda la paleta Café (el bloque :root).
   Doce paletas: cafe · verano · primavera · oro · arcoiris · tech ·
   alegre · invierno · piel · halloween · frio · noche.

   REGLA: se usan ESTRICTAMENTE los colores entregados.
   Un tono sólo se deriva cuando ningún color de la paleta puede
   cumplir el mínimo WCAG de ese rol — texto normal 4.5:1, íconos y
   bordes 3:1 — y en ese caso el bloque lo dice arriba, con el número
   que lo justifica. El blanco #FFFFFF del fondo de página no cuenta
   como color de paleta: es la superficie que pidió el brief.

   El color de texto que va sobre el primario, sobre el acento y
   sobre la barra está declarado, nunca se asume blanco.
   ============================================================ */

"""

bloques, swatches, reglas, resumen = [], [], [], []
for i, (slug, titulo, colores, ip, ap, np_) in enumerate(PALETAS, start=1):
    d = construir_estricto(slug, colores, ip, ap, np_)
    bloques.append(bloque(slug, titulo, colores, d, i))
    swatches.append('\n'.join('    --sw-%s-%d: %s;' % (slug, k + 1, c) for k, c in enumerate(colores)) + '\n')
    for k in range(1, 6):
        reglas.append('.muestra-%s .sw-%d { background-color: var(--sw-%s-%d); }' % (slug, k, slug, k))
    reglas.append('')
    resumen.append((titulo, len(d['derivados']), [x[0] for x in d['derivados']]))

COLA = """
/* ============================================================
   MUESTRAS DEL SELECTOR
   Los cuadraditos del panel muestran TODAS las paletas, también la
   que no está activa. Por eso los cinco tonos de cada una se repiten
   acá: son decorativos, no se usan en la interfaz.
   ============================================================ */

:root {
%s}

%s"""

open('/home/claude/prototipo-3/assets/css/paletas.css', 'w', encoding='utf-8').write(
    CABECERA + '\n'.join(bloques) + COLA % ('\n'.join(swatches), '\n'.join(reglas)))

print('RESUMEN DE DERIVADOS')
for t, n, ks in resumen:
    print(' ', t.ljust(11), n, ks if n else '')
