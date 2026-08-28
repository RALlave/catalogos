# -*- coding: utf-8 -*-
"""Genera los bloques de paletas.css midiendo contraste WCAG.
Los tonos derivados conservan tono y saturación del color original:
sólo se mueve la luminosidad hasta alcanzar el ratio pedido."""
import colorsys

def lin(c):
    c = c / 255
    return c / 12.92 if c <= 0.03928 else ((c + 0.055) / 1.055) ** 2.4

def hex2rgb(h):
    h = h.lstrip('#')
    return tuple(int(h[i:i+2], 16) for i in (0, 2, 4))

def rgb2hex(t):
    return '#%02X%02X%02X' % tuple(max(0, min(255, round(v))) for v in t)

def lum(c):
    r, g, b = c if isinstance(c, tuple) else hex2rgb(c)
    return 0.2126*lin(r) + 0.7152*lin(g) + 0.0722*lin(b)

def cr(a, b):
    la, lb = lum(a), lum(b)
    hi, lo = max(la, lb), min(la, lb)
    return (hi + 0.05) / (lo + 0.05)

def r2(x):
    return round(x, 2)

def set_light(h, l_new):
    r, g, b = [v/255 for v in hex2rgb(h)]
    hh, ll, ss = colorsys.rgb_to_hls(r, g, b)
    r2_, g2, b2 = colorsys.hls_to_rgb(hh, l_new, ss)
    return rgb2hex((r2_*255, g2*255, b2*255))

def get_light(h):
    r, g, b = [v/255 for v in hex2rgb(h)]
    return colorsys.rgb_to_hls(r, g, b)[1]

def ajustar(base, ref, objetivo, modo='max', lmin=0.0, lmax=1.0):
    """Devuelve el hex que, conservando tono y saturación de `base`, alcanza
    `objetivo` de contraste contra `ref`. modo='max' toma el más claro que
    cumple; modo='min', el más oscuro."""
    cumplen = []
    for i in range(1001):
        l = lmin + (lmax - lmin) * i / 1000
        c = set_light(base, l)
        if cr(c, ref) >= objetivo:
            cumplen.append(c)
    if not cumplen:
        return None
    return cumplen[-1] if modo == 'max' else cumplen[0]

def sat(h):
    r, g, b = [v/255 for v in hex2rgb(h)]
    return colorsys.rgb_to_hls(r, g, b)[2]

W = '#FFFFFF'

def construir(nombre, colores, ink_base=None, acento_base=None, inv_base=None):
    orden = sorted(colores, key=lum)
    mas_oscuro, mas_claro = orden[0], orden[-1]
    # el color con más carácter de la paleta: saturado y ni muy claro ni muy oscuro
    base_acento = acento_base or max(colores, key=lambda c: sat(c) * (1 - abs(get_light(c) - 0.5)))
    base_ink = ink_base or mas_oscuro
    base_inv = inv_base or base_acento

    # Si la paleta ya trae un tono realmente oscuro, se respeta. Si no, la tinta
    # se deriva del color con más carácter, para que no se sienta ajena.
    ink = base_ink if cr(base_ink, W) >= 8 else ajustar(base_ink, W, 10.5, 'max')
    ink_hover = ajustar(ink, W, min(cr(ink, W) * 1.25, 18), 'max')

    acento = base_acento if cr(base_acento, W) >= 4.6 else ajustar(base_acento, W, 4.8, 'max')
    if acento == ink:
        acento = ajustar(base_acento, W, 5.5, 'max')
    acento_hover = ajustar(acento, W, cr(acento, W) * 1.18, 'max')
    acento_soft = base_acento if cr(base_acento, W) >= 3.2 else ajustar(base_acento, W, 3.3, 'max')
    acento_inv = base_inv if cr(base_inv, ink) >= 4.8 else ajustar(base_inv, ink, 5.0, 'min')

    subtle = ajustar(ink, W, 4.9, 'max')

    # surface-alt tiene que ser un tono suave: si el color más claro de la
    # paleta es demasiado saturado u oscuro para una superficie, se aclara.
    if cr(W, mas_claro) > 1.9:
        surf_alt = ajustar(mas_claro, W, 1.7, 'max')
    else:
        surf_alt = mas_claro
    if cr(ink, surf_alt) < 7.0:
        surf_alt = ajustar(surf_alt, ink, 7.4, 'min')

    # El texto secundario tiene que cumplir sobre blanco Y sobre surface-alt
    muted = ajustar(ink, W, 7.2, 'max')
    if cr(muted, surf_alt) < 4.6:
        objetivo = 7.2
        while objetivo < 16:
            objetivo += 0.3
            cand = ajustar(ink, W, objetivo, 'max')
            if cr(cand, surf_alt) >= 4.6:
                muted = cand
                break
    on_dark_muted = surf_alt
    borde = ajustar(base_acento, W, 1.45, 'max')
    # separador dentro de la barra: un tono más claro que el primario
    on_dark_borde = ajustar(ink, ink, 1.8, 'min', lmin=get_light(ink))

    def alfa_velo(velo, texto, fondo_peor, objetivo=4.6):
        v = hex2rgb(velo)
        for i in range(50, 96):
            a = i / 100
            eff = tuple(a * v[k] + (1 - a) * fondo_peor[k] for k in range(3))
            if cr(texto, eff) >= objetivo:
                return a, r2(cr(texto, eff))
        return 0.95, 0

    a_osc, r_osc = alfa_velo(ink, W, (255, 255, 255))
    a_cla, r_cla = alfa_velo(surf_alt, ink, (0, 0, 0))

    return dict(nombre=nombre, orig=colores, ink=ink, ink_hover=ink_hover,
                acento=acento, acento_hover=acento_hover, acento_soft=acento_soft,
                acento_inv=acento_inv, muted=muted, subtle=subtle, surf_alt=surf_alt,
                on_dark_muted=on_dark_muted, borde=borde, on_dark_borde=on_dark_borde,
                a_osc=a_osc, r_osc=r_osc, a_cla=a_cla, r_cla=r_cla)
