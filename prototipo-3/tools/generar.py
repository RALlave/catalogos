# -*- coding: utf-8 -*-
import sys
sys.path.insert(0, '/home/claude/tools')
from paletas import *

# nombre, colores originales, ink_base, acento_base, inv_base
PALETAS = [
 ('verano',   'Verano',    ['#00C2CB','#7FE0D4','#FFE38A','#FF9A76','#FF5D8F'], '#00C2CB','#FF5D8F','#7FE0D4'),
 ('primavera','Primavera', ['#FFF3C9','#C9E4A6','#F7B7C4','#A3D9C9','#7FB4E0'], '#7FB4E0','#7FB4E0','#7FB4E0'),
 ('oro',      'Oro',       ['#FBF3D0','#EBD08C','#D4AF37','#B8860B','#7C5E10'], None,'#B8860B','#D4AF37'),
 ('arcoiris', 'Arcoíris',  ['#FF5A5F','#FFB400','#3DDC84','#00A6ED','#8B5CF6'], '#8B5CF6','#FF5A5F','#FFB400'),
 ('tech',     'Tech',      ['#E7DFF7','#C6B4EE','#A48AE0','#7B5FD1','#4E2FB0'], None,None,'#C6B4EE'),
 ('alegre',   'Alegre',    ['#FF6B6B','#FFD93D','#6BCB77','#4D96FF','#FF6FB5'], '#4D96FF','#FF6B6B','#FFD93D'),
 ('invierno', 'Invierno',  ['#E3F6F5','#A7E0DB','#5FB0C9','#3E6D9C','#2A2F63'], None,'#5FB0C9','#A7E0DB'),
 ('piel',     'Piel',      ['#F8D9C0','#E9B98F','#D89B72','#B87A56','#8A5A3C'], None,'#D89B72','#E9B98F'),
 ('halloween','Halloween', ['#F4A100','#E8730C','#7A3E9D','#3E1F47','#1A1423'], None,'#7A3E9D','#F4A100'),
 ('frio',     'Frío',      ['#C1D993','#A7D078','#B560E1','#7C63BB','#6941B0'], None,'#B560E1','#A7D078'),
 ('noche',    'Noche',     ['#1F4973','#3124A5','#392184','#17224D','#271538'], None,'#1F4973','#1F4973'),
]

def rgba(h, a):
    r, g, b = hex2rgb(h)
    return 'rgba(%d, %d, %d, %s)' % (r, g, b, a)

def es_original(hex_, orig):
    return 'de la paleta' if hex_ in orig else 'derivado'

def bloque(slug, titulo, orig, d, n):
    o = orig
    usados = [c for c in o if c in d.values()]
    sin_uso = [c for c in o if c not in d.values()]
    L = []
    A = L.append
    A('/* ============================================================')
    A('   PALETA %02d — "%s"' % (n, titulo))
    A('   Entregada: ' + ' · '.join(o))
    A('   Se usan tal cual: ' + (', '.join(usados) if usados else 'ninguno — los cinco tonos'))
    if not usados:
        A('   son demasiado claros para los roles de texto y navegación.')
    if sin_uso:
        A('   Sin lugar todavía (entran con los badges): ' + ', '.join(sin_uso))
    A('   ============================================================ */')
    A('')
    A('[data-paleta="%s"] {' % slug)
    A('')
    A('    /* Superficies */')
    A('    --color-bg:            #FFFFFF;')
    A('    --color-surface:       #FFFFFF;')
    A('    --color-surface-alt:   %s;  /* %s · %s:1 con text — superficie suave */' % (d['surf_alt'], es_original(d['surf_alt'], o), r2(cr(d['ink'], d['surf_alt']))))
    A('    --color-border:        %s;  /* derivado · %s:1 sobre surface — separador decorativo */' % (d['borde'], r2(cr(d['borde'], W))))
    A('    --color-border-strong: %s;  /* %s · %s:1 sobre surface — borde de control (mín. 3:1) */' % (d['acento_soft'], es_original(d['acento_soft'], o), r2(cr(d['acento_soft'], W))))
    A('')
    A('    /* Texto sobre fondo claro */')
    A('    --color-text:        %s;  /* %s · %s:1 sobre surface · %s:1 sobre surface-alt */' % (d['ink'], es_original(d['ink'], o), r2(cr(d['ink'], W)), r2(cr(d['ink'], d['surf_alt']))))
    A('    --color-text-muted:  %s;  /* derivado · %s:1 sobre surface · %s:1 sobre surface-alt */' % (d['muted'], r2(cr(d['muted'], W)), r2(cr(d['muted'], d['surf_alt']))))
    A('    --color-text-subtle: %s;  /* derivado · %s:1 sobre surface — sólo sobre blanco */' % (d['subtle'], r2(cr(d['subtle'], W))))
    A('')
    A('    /* Primario y acento */')
    A('    --color-primary:       %s;' % d['ink'])
    A('    --color-primary-hover: %s;  /* derivado · %s:1 con on-primary */' % (d['ink_hover'], r2(cr(d['ink_hover'], '#FFFFFF'))))
    A('    --color-on-primary:    #FFFFFF;  /* %s:1 sobre primary */' % r2(cr('#FFFFFF', d['ink'])))
    A('')
    A('    --color-accent:        %s;  /* %s · %s:1 sobre surface — texto y fondo sólido */' % (d['acento'], es_original(d['acento'], o), r2(cr(d['acento'], W))))
    A('    --color-accent-hover:  %s;  /* derivado · %s:1 con on-accent */' % (d['acento_hover'], r2(cr(d['acento_hover'], '#FFFFFF'))))
    A('    --color-on-accent:     #FFFFFF;  /* %s:1 sobre accent */' % r2(cr('#FFFFFF', d['acento'])))
    A('')
    A('    --color-accent-soft:   %s;  /* %s:1 sobre surface — íconos y bordes, NUNCA texto */' % (d['acento_soft'], r2(cr(d['acento_soft'], W))))
    A('    --color-accent-invert: %s;  /* %s:1 sobre primary — sólo sobre la barra oscura */' % (d['acento_inv'], r2(cr(d['acento_inv'], d['ink']))))
    A('')
    A('    /* Texto sobre la barra oscura */')
    A('    --color-on-dark:        #FFFFFF;  /* %s:1 sobre primary */' % r2(cr('#FFFFFF', d['ink'])))
    A('    --color-on-dark-muted:  %s;  /* %s:1 sobre primary */' % (d['on_dark_muted'], r2(cr(d['on_dark_muted'], d['ink']))))
    A('    --color-on-dark-border: %s;  /* %s:1 sobre primary — separador decorativo */' % (d['on_dark_borde'], r2(cr(d['on_dark_borde'], d['ink']))))
    A('')
    A('    /* Foco (mín. 3:1 contra el fondo donde se pinta) */')
    A('    --color-focus:        %s;  /* %s:1 sobre surface */' % (d['acento'], r2(cr(d['acento'], W))))
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
    A('    --color-on-banner:         #FFFFFF;  /* %s:1 en el peor caso */' % d['r_osc'])
    A('    --color-banner-velo-claro: %s;' % rgba(d['surf_alt'], d['a_cla']))
    A('    --color-on-banner-claro:   %s;  /* %s:1 en el peor caso */' % (d['ink'], d['r_cla']))
    A('}')
    A('')
    return '\n'.join(L)

bloques = []
swatches = []
opciones = []
for i, (slug, titulo, colores, ib, ab, vb) in enumerate(PALETAS, start=2):
    d = construir(slug, colores, ib, ab, vb)
    bloques.append(bloque(slug, titulo, colores, d, i))
    swatches.append('\n'.join(
        '    --sw-%s-%d: %s;' % (slug, k + 1, c) for k, c in enumerate(colores)))
    swatches.append('')
    opciones.append((slug, titulo))
    for k in range(5):
        pass

reglas = []
for slug, titulo in [('cafe', 'Café')] + opciones:
    for k in range(1, 6):
        reglas.append('.muestra-%s .sw-%d { background-color: var(--sw-%s-%d); }' % (slug, k, slug, k))
    reglas.append('')

open('/home/claude/tools/_bloques.css', 'w', encoding='utf-8').write('\n'.join(bloques))
open('/home/claude/tools/_swatches.css', 'w', encoding='utf-8').write('\n'.join(swatches))
open('/home/claude/tools/_reglas.css', 'w', encoding='utf-8').write('\n'.join(reglas))
open('/home/claude/tools/_opciones.txt', 'w', encoding='utf-8').write('\n'.join('%s|%s' % o for o in opciones))
print('bloques', len(bloques))
