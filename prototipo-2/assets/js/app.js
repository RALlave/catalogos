/* ============================================================
   APP — filtro por categoría, búsqueda y render de la grilla.
   JS vanilla, sin dependencias.
   ============================================================ */

(function () {
    'use strict';

    /* --------------------------------------------------------
       Referencias del DOM
       -------------------------------------------------------- */
    const $chips      = document.getElementById('chips-categoria');
    const $grilla     = document.getElementById('grilla-productos');
    const $vacio      = document.getElementById('estado-vacio');
    const $conteo     = document.getElementById('resultado-conteo');
    const $form       = document.getElementById('form-busqueda');
    const $input      = document.getElementById('q');
    const $limpiar    = document.getElementById('btn-limpiar');
    const $reset      = document.getElementById('btn-reset');

    /* --------------------------------------------------------
       Estado
       -------------------------------------------------------- */
    const estado = {
        categoria: 'todos',
        texto: ''
    };

    let debounceId = null;

    /* --------------------------------------------------------
       Utilidades
       -------------------------------------------------------- */

    // Ignora tildes en ambas direcciones: "cafe" encuentra "café" y viceversa.
    function normalizar(txt) {
        return String(txt)
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
    }

    function escapar(txt) {
        return String(txt)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function precio(valor) {
        return TIENDA.moneda + ' ' + valor.toLocaleString('es-BO');
    }

    /* --------------------------------------------------------
       Íconos SVG (nunca emojis)
       -------------------------------------------------------- */
    const ICONOS = {
        nuevo: '<svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" aria-hidden="true"><path d="M12 2.5 13.9 8l5.6 1.9-5.6 1.9L12 17.4l-1.9-5.6L4.5 9.9 10.1 8Z"/><path d="M18.5 14.5l.9 2.4 2.4.9-2.4.9-.9 2.4-.9-2.4-2.4-.9 2.4-.9Z"/></svg>',
        destacado: '<svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" aria-hidden="true"><path d="m12 3 2.7 5.6 6.1.9-4.4 4.3 1 6.1-5.4-2.9-5.4 2.9 1-6.1L3.2 9.5l6.1-.9Z"/></svg>',
        agotado: '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="8.5"/><line x1="6.5" y1="17.5" x2="17.5" y2="6.5"/></svg>',
        check: '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="4.5 12.5 9.5 17.5 19.5 6.5"/></svg>',
        whatsapp: '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true"><path d="M12.04 2c-5.46 0-9.9 4.44-9.9 9.9 0 1.75.46 3.45 1.32 4.95L2 22l5.3-1.38a9.86 9.86 0 0 0 4.74 1.21c5.46 0 9.9-4.44 9.9-9.9 0-2.64-1.03-5.13-2.9-7A9.82 9.82 0 0 0 12.04 2Zm4.52 11.86c-.25-.13-1.47-.72-1.69-.8-.23-.09-.39-.13-.56.12-.16.25-.64.8-.78.97-.15.16-.29.18-.53.06-.25-.13-1.05-.39-1.99-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.01-.38.11-.5.11-.11.25-.29.37-.44.13-.15.17-.25.25-.42.09-.16.04-.31-.02-.44-.06-.12-.56-1.34-.76-1.84-.2-.48-.4-.42-.56-.43h-.47c-.16 0-.43.06-.65.31-.23.25-.86.84-.86 2.05s.88 2.38 1 2.54c.13.17 1.74 2.65 4.21 3.72.59.25 1.05.4 1.4.52.59.19 1.13.16 1.55.1.47-.07 1.47-.6 1.67-1.18.21-.58.21-1.07.15-1.18-.06-.11-.23-.17-.48-.29Z"/></svg>'
    };

    const BADGES = {
        nuevo:     { clase: 'badge-nuevo',     texto: 'Nuevo' },
        destacado: { clase: 'badge-destacado', texto: 'Destacado' },
        agotado:   { clase: 'badge-agotado',   texto: 'Agotado' }
    };

    /* --------------------------------------------------------
       Plantillas
       -------------------------------------------------------- */

    function plantillaChip(categoria, cantidad, activo) {
        const id = 'cat-' + categoria.slug;
        return '' +
            '<input type="radio" name="categoria" id="' + id + '" value="' + categoria.slug + '"' + (activo ? ' checked' : '') + '>' +
            '<label for="' + id + '">' +
                '<span class="chip-check" aria-hidden="true">' + ICONOS.check + '</span>' +
                escapar(categoria.nombre) +
                '<span class="chip-conteo">' + cantidad + '</span>' +
            '</label>';
    }

    function plantillaProducto(producto) {
        const badge = BADGES[producto.estado];
        const agotado = producto.estado === 'agotado';
        const categoria = CATEGORIAS.find(function (c) { return c.slug === producto.categoria; });

        const mensaje = encodeURIComponent(
            'Hola, me interesa "' + producto.nombre + '" (' + precio(producto.precio) + ') del catálogo.'
        );
        const enlaceWa = 'https://wa.me/' + TIENDA.whatsapp + '?text=' + mensaje;

        const badgeHtml = badge
            ? '<div class="producto-badges">' +
                  '<span class="badge ' + badge.clase + '">' + ICONOS[producto.estado] + badge.texto + '</span>' +
              '</div>'
            : '';

        const antesHtml = producto.precio_antes
            ? '<span class="precio-antes">Antes <s>' + precio(producto.precio_antes) + '</s></span>'
            : '';

        return '' +
            '<article class="producto' + (agotado ? ' producto-agotado' : '') + '">' +
                '<div class="producto-media">' +
                    '<img src="' + escapar(producto.imagen) + '" alt="Foto de ' + escapar(producto.nombre) + '" width="800" height="800" loading="lazy" decoding="async">' +
                    badgeHtml +
                '</div>' +
                '<div class="producto-cuerpo">' +
                    '<div class="producto-categoria">' + escapar(categoria ? categoria.nombre : '') + '</div>' +
                    '<h3>' + escapar(producto.nombre) + '</h3>' +
                    '<p class="producto-desc">' + escapar(producto.descripcion) + '</p>' +
                    '<div class="producto-pie">' +
                        '<div class="producto-precio">' +
                            '<span class="precio-actual">' + precio(producto.precio) + '</span>' +
                            antesHtml +
                        '</div>' +
                        '<div class="producto-accion">' +
                            '<a href="' + enlaceWa + '" target="_blank" rel="noopener">' +
                                '<span class="btn-icono">' + ICONOS.whatsapp + '</span>' +
                                (agotado ? 'Consultar' : 'Pedir') +
                                '<span class="visually-hidden"> ' + escapar(producto.nombre) + ' por WhatsApp</span>' +
                            '</a>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</article>';
    }

    /* --------------------------------------------------------
       Filtrado
       -------------------------------------------------------- */

    function porCategoria(lista, slug) {
        if (slug === 'todos') return lista;
        return lista.filter(function (p) { return p.categoria === slug; });
    }

    function porTexto(lista, texto) {
        if (!texto) return lista;
        const q = normalizar(texto);
        return lista.filter(function (p) {
            return normalizar(p.nombre).indexOf(q) !== -1 ||
                   normalizar(p.descripcion).indexOf(q) !== -1;
        });
    }

    function filtrados() {
        return porTexto(porCategoria(PRODUCTOS, estado.categoria), estado.texto);
    }

    /* --------------------------------------------------------
       Render
       -------------------------------------------------------- */

    function pintarChips() {
        $chips.innerHTML = CATEGORIAS.map(function (c) {
            const cantidad = porTexto(porCategoria(PRODUCTOS, c.slug), estado.texto).length;
            return plantillaChip(c, cantidad, c.slug === estado.categoria);
        }).join('');
    }

    function pintarGrilla() {
        const lista = filtrados();

        $grilla.innerHTML = lista.map(plantillaProducto).join('');
        $grilla.hidden = lista.length === 0;
        $vacio.hidden = lista.length > 0;

        const total = PRODUCTOS.length;
        $conteo.textContent = lista.length === 1
            ? '1 producto de ' + total
            : lista.length + ' productos de ' + total;
    }

    function actualizarChips() {
        // Sólo se refrescan los contadores; no se recrea el DOM para no
        // perder el foco del teclado sobre el radio activo.
        CATEGORIAS.forEach(function (c) {
            const $label = $chips.querySelector('label[for="cat-' + c.slug + '"] .chip-conteo');
            if ($label) {
                $label.textContent = porTexto(porCategoria(PRODUCTOS, c.slug), estado.texto).length;
            }
        });
    }

    function render() {
        actualizarChips();
        pintarGrilla();
    }

    /* --------------------------------------------------------
       Eventos
       -------------------------------------------------------- */

    $chips.addEventListener('change', function (e) {
        if (e.target.name !== 'categoria') return;
        estado.categoria = e.target.value;
        render();
    });

    // Se lee del DOM (no v-model / no variable intermedia) para que el
    // teclado predictivo de móvil no retrase la búsqueda.
    $input.addEventListener('input', function (e) {
        const valor = e.target.value;
        $limpiar.hidden = valor.length === 0;

        window.clearTimeout(debounceId);
        debounceId = window.setTimeout(function () {
            estado.texto = valor.trim();
            render();
        }, 200);
    });

    $form.addEventListener('submit', function (e) {
        e.preventDefault();
        window.clearTimeout(debounceId);
        estado.texto = $input.value.trim();
        render();
    });

    $limpiar.addEventListener('click', function () {
        $input.value = '';
        $limpiar.hidden = true;
        estado.texto = '';
        render();
        $input.focus();
    });

    $reset.addEventListener('click', function () {
        $input.value = '';
        $limpiar.hidden = true;
        estado.texto = '';
        estado.categoria = 'todos';
        pintarChips();
        pintarGrilla();
        $input.focus();
    });

    /* --------------------------------------------------------
       Arranque
       -------------------------------------------------------- */
    pintarChips();
    pintarGrilla();

})();
