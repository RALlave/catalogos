/* ============================================================
   CATÁLOGO — filtro por categoría, buscador y paginación
   Los productos están en el HTML, no acá: el script sólo decide
   cuáles se muestran. Sin JS se ven los 24 y la paginación queda
   oculta, así que la página nunca queda a medias.
   ============================================================ */

(function () {
    'use strict';

    var grilla = document.querySelector('[data-grilla]');
    if (!grilla) return;

    var POR_PAGINA = 8;

    var items = Array.prototype.slice.call(grilla.querySelectorAll('.grilla-item'));
    var chips = Array.prototype.slice.call(document.querySelectorAll('.chip[data-filtro]'));
    var resultado = document.querySelector('[data-resultado]');
    var vacio = document.querySelector('[data-vacio]');
    var paginacion = document.querySelector('[data-paginacion]');
    var listaPaginas = document.querySelector('[data-paginas]');
    var botonAnterior = document.querySelector('[data-anterior]');
    var botonSiguiente = document.querySelector('[data-siguiente]');
    var formulario = document.getElementById('form-buscador');
    var campo = document.getElementById('q');

    var categoria = 'todas';
    var busqueda = '';
    var pagina = 1;

    /* --------------------------------------------------------
       Qué productos pasan el filtro
       -------------------------------------------------------- */

    function normalizar(texto) {
        return texto
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');   /* saca tildes: "indigo" encuentra "índigo" */
    }

    function coinciden() {
        var termino = normalizar(busqueda).trim();

        return items.filter(function (item) {
            var esCategoria = categoria === 'todas' || item.dataset.categoria === categoria;
            if (!esCategoria) return false;
            if (!termino) return true;
            return normalizar(item.dataset.nombre).indexOf(termino) !== -1;
        });
    }

    /* --------------------------------------------------------
       Pintado
       -------------------------------------------------------- */

    function textoResultado(total) {
        if (total === 0) return 'Ningún producto coincide con el filtro';
        if (total === 1) return '1 producto';
        return total + ' productos';
    }

    function pintarPaginacion(paginas) {
        listaPaginas.textContent = '';

        if (paginas < 2) {
            paginacion.hidden = true;
            return;
        }

        paginacion.hidden = false;

        for (var numero = 1; numero <= paginas; numero++) {
            var li = document.createElement('li');
            var boton = document.createElement('button');

            boton.type = 'button';
            boton.className = 'pagina';
            boton.dataset.pagina = String(numero);
            boton.textContent = String(numero);

            if (numero === pagina) {
                boton.setAttribute('aria-current', 'page');
            } else {
                /* El número solo no dice a dónde lleva fuera de contexto */
                boton.setAttribute('aria-label', 'Ir a la página ' + numero);
            }

            li.appendChild(boton);
            listaPaginas.appendChild(li);
        }

        botonAnterior.disabled = pagina === 1;
        botonSiguiente.disabled = pagina === paginas;
    }

    function pintar() {
        var visibles = coinciden();
        var total = visibles.length;
        var paginas = Math.max(1, Math.ceil(total / POR_PAGINA));

        if (pagina > paginas) pagina = paginas;

        var desde = (pagina - 1) * POR_PAGINA;
        var hasta = desde + POR_PAGINA;

        items.forEach(function (item) {
            var indice = visibles.indexOf(item);
            item.hidden = indice === -1 || indice < desde || indice >= hasta;
        });

        if (resultado) resultado.textContent = textoResultado(total);
        if (vacio) vacio.hidden = total !== 0;

        pintarPaginacion(total === 0 ? 0 : paginas);
    }

    /* --------------------------------------------------------
       Eventos
       -------------------------------------------------------- */

    chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            categoria = chip.dataset.filtro;
            pagina = 1;

            chips.forEach(function (otro) {
                otro.setAttribute('aria-pressed', String(otro === chip));
            });

            pintar();
        });
    });

    if (formulario) {
        formulario.addEventListener('submit', function (evento) {
            evento.preventDefault();
            busqueda = campo ? campo.value : '';
            pagina = 1;
            pintar();
            document.getElementById('productos').scrollIntoView({ block: 'start' });
        });
    }

    if (campo) {
        campo.addEventListener('input', function () {
            busqueda = campo.value;
            pagina = 1;
            pintar();
        });
    }

    function irA(numero) {
        pagina = numero;
        pintar();
        document.getElementById('productos').scrollIntoView({ block: 'start' });
    }

    listaPaginas.addEventListener('click', function (evento) {
        var boton = evento.target.closest('[data-pagina]');
        if (boton) irA(Number(boton.dataset.pagina));
    });

    botonAnterior.addEventListener('click', function () {
        if (pagina > 1) irA(pagina - 1);
    });

    botonSiguiente.addEventListener('click', function () {
        irA(pagina + 1);
    });

    pintar();
})();
