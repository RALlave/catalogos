"""Repone el pie en index.html si otra sesion lo borro.
Uso:  python tools/reponer-pie.py      (desde prototipo-3/)
El bloque vive en tools/pie.html. El CSS del pie esta en components.css
(seccion PIE DE PAGINA) y los tokens en base.css (--pie-*).
"""
import os, sys

raiz = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
indice = os.path.join(raiz, 'index.html')
bloque = os.path.join(raiz, 'tools', 'pie.html')

s = open(indice, encoding='utf-8').read()
if 'class="pie-inner"' in s:
    print('OK: el pie ya esta en index.html'); sys.exit(0)

footer = open(bloque, encoding='utf-8').read()
anclas = [
    '    <!-- ========================================================\n         PANEL DE DISE\u00d1O',
    '    <div class="panel">',
    '</body>',
]
for a in anclas:
    if s.count(a) == 1:
        s = s.replace(a, footer + '\n' + a, 1)
        open(indice, 'w', encoding='utf-8').write(s)
        print('pie repuesto antes de:', a.strip()[:40]); sys.exit(0)
print('ERROR: no encontre donde insertarlo'); sys.exit(1)
