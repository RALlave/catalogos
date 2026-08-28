# -*- coding: utf-8 -*-
"""Reparto ESTRICTO: sólo colores de la paleta.
Sólo se deriva un tono cuando NINGÚN color de la paleta puede cumplir el
mínimo WCAG de ese rol, y en ese caso queda marcado como derivado."""
from paletas import *

BLANCO = '#FFFFFF'   # el fondo de página, no es un color de paleta

def por_contraste(cs, ref, minimo):
    return sorted([c for c in cs if cr(c, ref) >= minimo], key=lambda c: -cr(c, ref))

def mas_saturado(lista, por_defecto=None):
    return max(lista, key=sat) if lista else por_defecto

def construir_estricto(nombre, colores, ink_pref=None, acento_pref=None, nav_pref=None):
    d = {'derivados': []}
    W_ = BLANCO
    texto_ok = por_contraste(colores, W_, 4.5)      # sirven como texto sobre blanco
    ui_ok    = por_contraste(colores, W_, 3.0)      # sirven como ícono o borde

    # --- tinta: texto y barra por defecto
    if texto_ok:
        ink = ink_pref if (ink_pref in texto_ok) else texto_ok[0]
    else:
        ink = ajustar(sorted(colores, key=lum)[0], W_, 10.5, 'max')
        d['derivados'].append(('--color-text / --color-primary', ink,
            'ningún color de la paleta llega a 4.5:1 sobre blanco (el mejor es %s con %s:1)'
            % (max(colores, key=lambda c: cr(c, W_)), r2(max(cr(c, W_) for c in colores)))))

    resto = [c for c in texto_ok if c != ink]
    muted  = resto[0] if resto else ink
    subtle = resto[1] if len(resto) > 1 else muted

    # --- acento
    if acento_pref and acento_pref in texto_ok and acento_pref != ink:
        acento = acento_pref
    else:
        acento = mas_saturado(resto, ink)
    # El hover necesita un cambio perceptible aunque la paleta no tenga otro
    # tono legible. Es el único derivado que se acepta por diseño, y sólo
    # aparece al pasar el puntero.
    if ink != acento:
        acento_hover = ink
    else:
        acento_hover = ajustar(acento, W_, cr(acento, W_) * 1.25, 'max')
        d['derivados'].append(('--color-accent-hover', acento_hover,
            'sólo para el estado hover: la paleta no tiene otro tono legible'))

    soft = mas_saturado([c for c in ui_ok if c != ink], acento)

    inv_ok = por_contraste(colores, ink, 4.5)
    if inv_ok:
        invert = mas_saturado(inv_ok)
    else:
        invert = ajustar(mas_saturado(colores), ink, 5.0, 'min', lmin=get_light(ink))
        d['derivados'].append(('--color-accent-invert', invert,
            'ningún color de la paleta llega a 4.5:1 sobre la barra'))

    # --- superficie suave: el color más claro que aguante la tinta encima
    alt_ok = por_contraste(colores, ink, 4.5)
    if alt_ok:
        surf_alt = max(alt_ok, key=lum)          # el más claro que aguanta la tinta encima
    else:
        surf_alt = ajustar(max(colores, key=lum), W_, 1.5, 'max')
        d['derivados'].append(('--color-surface-alt', surf_alt,
            'ningún color de la paleta aguanta la tinta encima (el mejor da %s:1); '
            'es un tinte del más claro' % r2(max(cr(c, ink) for c in colores))))

    # texto sobre primario y sobre acento: se prefiere un color de la paleta
    sobre_ink = por_contraste(colores, ink, 4.5)
    on_primary = max(sobre_ink, key=lambda c: cr(c, ink)) if sobre_ink else BLANCO
    sobre_acento = por_contraste(colores, acento, 4.5)
    on_accent = max(sobre_acento, key=lambda c: cr(c, acento)) if sobre_acento else BLANCO
    if on_primary == BLANCO:
        d['derivados'].append(('--color-on-primary', BLANCO, 'sobre la barra ningún color de la paleta llega a 4.5:1'))
    if on_accent == BLANCO:
        d['derivados'].append(('--color-on-accent', BLANCO, 'sobre el acento ningún color de la paleta llega a 4.5:1'))

    on_dark_muted = max(sobre_ink, key=lambda c: cr(c, ink)) if sobre_ink else on_primary
    decor = [c for c in colores if 1.2 <= cr(c, ink) <= 3.2]
    on_dark_borde = decor[0] if decor else (muted if muted != ink else acento)
    borde = max(colores, key=lum)

    # --- barra de color: un tono real de la paleta
    # La barra quiere un tono con presencia: saturado y de luminosidad media,
    # nunca el más claro de la paleta.
    mas_claro = max(colores, key=lum)
    legibles = [c for c in colores
                if max(cr(BLANCO, c), cr(ink, c)) >= 4.5 and c != mas_claro] \
               or [c for c in colores if max(cr(BLANCO, c), cr(ink, c)) >= 4.5]
    nav_bg = nav_pref or max(legibles, key=lambda c: sat(c) * (1 - abs(get_light(c) - 0.45)))
    nav_texto = BLANCO if cr(BLANCO, nav_bg) >= cr(ink, nav_bg) else ink
    otros = [c for c in colores + [BLANCO, ink] if c not in (nav_bg, nav_texto)]
    nav_activo = mas_saturado([c for c in otros if cr(c, nav_bg) >= 4.5], nav_texto)
    nav_marca  = mas_saturado([c for c in otros if cr(c, nav_bg) >= 3.0], nav_activo)
    nav_borde  = mas_saturado([c for c in colores if 1.15 <= cr(c, nav_bg) <= 3.0],
                              ajustar(nav_bg, nav_bg, 1.35, 'min', lmin=get_light(nav_bg)) or nav_bg)

    def alfa(velo, texto, peor, objetivo=4.6):
        v = hex2rgb(velo)
        for i in range(50, 96):
            a = i / 100
            eff = tuple(a * v[k] + (1 - a) * peor[k] for k in range(3))
            if cr(texto, eff) >= objetivo:
                return a, r2(cr(texto, eff))
        return 0.95, 0

    a_osc, r_osc = alfa(ink, on_primary, (255, 255, 255))
    a_cla, r_cla = alfa(surf_alt, ink, (0, 0, 0))

    d.update(nombre=nombre, orig=colores, ink=ink, ink_hover=acento_hover,
             muted=muted, subtle=subtle, acento=acento, acento_hover=acento_hover,
             acento_soft=soft, acento_inv=invert, surf_alt=surf_alt, borde=borde,
             border_strong=soft, on_primary=on_primary, on_accent=on_accent,
             on_dark_muted=on_dark_muted, on_dark_borde=on_dark_borde,
             nav_bg=nav_bg, nav_texto=nav_texto, nav_activo=nav_activo,
             nav_marca=nav_marca, nav_borde=nav_borde,
             a_osc=a_osc, r_osc=r_osc, a_cla=a_cla, r_cla=r_cla)
    return d
