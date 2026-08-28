/* ============================================================
   APP — comportamiento del header y la navegación
   Sin dependencias. Progresivo: si el JS falla, los enlaces
   del menú y el formulario de búsqueda siguen siendo válidos.
   ============================================================ */

/* ============================================================
   MODALIDAD DE ENTRADA
   Marca en <html> si la última interacción fue con puntero o con
   teclado. Lo usa el CSS para no pintar el anillo de foco del
   buscador cuando se abre con el mouse — quien hace clic ya sabe
   dónde está parado. Con teclado el anillo se pinta siempre: sin él
   no hay forma de saber qué elemento está activo.
   ============================================================ */

(function () {
    'use strict';

    var root = document.documentElement;

    function mark(mode) {
        root.setAttribute('data-input', mode);
    }

    mark('mouse');

    document.addEventListener('pointerdown', function () {
        mark('mouse');
    }, true);

    /* Sólo las teclas de navegación cuentan: escribir en el campo no
       tiene que hacer aparecer el anillo */
    document.addEventListener('keydown', function (event) {
        var k = event.key;
        if (k === 'Tab' || k === 'Enter' || k === ' ' || k === 'Escape' || k.indexOf('Arrow') === 0) {
            mark('keyboard');
        }
    }, true);
})();


(function () {
    'use strict';

    var nav = document.querySelector('.nav');
    if (!nav) return;

    /* El botón vive en la franja superior, FUERA del <nav>: en móvil
       y tablet no hay barra donde meterlo */
    var navToggle = document.querySelector('.nav-toggle');
    var menu = document.getElementById('menu-main');
    var navPanel = nav.querySelector('.nav-panel');
    var panelClose = nav.querySelector('.nav-panel-close');
    var veil = nav.querySelector('.nav-veil');
    var searchToggle = nav.querySelector('.search-toggle');
    var searchForm = document.getElementById('form-search');
    var searchField = document.getElementById('q');
    var dropdowns = nav.querySelectorAll('.menu-btn');

    /* Coincide con el breakpoint de 62rem (992px) de la hoja de estilos */
    var desktop = window.matchMedia('(min-width: 62rem)');

    /* --------------------------------------------------------
       Utilidades
       -------------------------------------------------------- */

    function swapIcons(button, open) {
        var openIcon = button.querySelector('[data-icon-open]');
        var closeIcon = button.querySelector('[data-icon-close]');
        if (!openIcon || !closeIcon) return;
        openIcon.hidden = open;
        closeIcon.hidden = !open;
    }

    /* --------------------------------------------------------
       Menú principal (panel lateral de móvil y tablet)
       -------------------------------------------------------- */

    /* Lo que se puede tabular dentro del panel. Se calcula en cada
       apertura y no una sola vez: el submenú de categorías agrega y
       saca enlaces del recorrido. */
    function panelFocusables() {
        if (!navPanel) return [];
        var candidates = navPanel.querySelectorAll('a[href], button, input, [tabindex]:not([tabindex="-1"])');
        return Array.prototype.filter.call(candidates, function (element) {
            return !element.disabled && element.offsetParent !== null;
        });
    }

    function openMenu(open) {
        if (!navToggle || !menu) return;
        navToggle.setAttribute('aria-expanded', String(open));
        nav.classList.toggle('is-open', open);
        swapIcons(navToggle, open);
        /* El panel tapa la pantalla entera: la página de atrás no se
           desplaza y la cabecera sube por encima del panel de diseño.
           Las dos cosas cuelgan de esta clase en <html>. */
        document.documentElement.classList.toggle('is-menu-open', open);
    }

    if (navToggle) {
        navToggle.addEventListener('click', function () {
            var open = navToggle.getAttribute('aria-expanded') !== 'true';
            openMenu(open);
            /* El foco entra al panel, que es lo que quedó al frente. Va
               al ✕ y no al primer enlace: así el primer Tab recorre el
               menú de arriba abajo, y Enter cierra sin buscar nada. */
            if (open && panelClose) panelClose.focus();
        });
    }

    function closeMenuAndReturn() {
        openMenu(false);
        if (navToggle) navToggle.focus();
    }

    if (panelClose) {
        panelClose.addEventListener('click', closeMenuAndReturn);
    }

    /* El velo es el área de cierre más grande que hay: se toca a un
       costado del panel y el menú se va */
    if (veil) {
        veil.addEventListener('click', closeMenuAndReturn);
    }

    /* Trampa de foco: mientras el panel está abierto, Tab no puede
       salirse hacia la página de atrás, que está tapada por el velo */
    if (navPanel) {
        navPanel.addEventListener('keydown', function (event) {
            if (event.key !== 'Tab') return;
            if (!nav.classList.contains('is-open')) return;

            var focusables = panelFocusables();
            if (!focusables.length) return;

            var first = focusables[0];
            var last = focusables[focusables.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        });
    }

    /* Al tocar un enlace del menú el panel tiene que irse: si el
       destino es un ancla de la misma página, no hay recarga que lo
       cierre por su cuenta */
    if (menu) {
        menu.addEventListener('click', function (event) {
            if (!event.target.closest('a[href]')) return;
            openMenu(false);
        });
    }

    /* --------------------------------------------------------
       Submenú de categorías
       -------------------------------------------------------- */

    function closeDropdowns(except) {
        dropdowns.forEach(function (button) {
            if (button === except) return;
            button.setAttribute('aria-expanded', 'false');
            var panel = document.getElementById(button.getAttribute('aria-controls'));
            if (panel) panel.hidden = true;
        });
    }

    dropdowns.forEach(function (button) {
        var panel = document.getElementById(button.getAttribute('aria-controls'));
        if (!panel) return;

        button.addEventListener('click', function () {
            var open = button.getAttribute('aria-expanded') === 'true';
            closeDropdowns(button);
            button.setAttribute('aria-expanded', String(!open));
            panel.hidden = open;
        });
    });

    /* --------------------------------------------------------
       Buscador
       -------------------------------------------------------- */

    /* El formulario se despliega hacia la izquierda dentro de la barra.
       No se usa el atributo `hidden` porque `display: none` no se puede
       animar: el ancho lo controla la clase, y `visibility` en el CSS es
       lo que lo saca del orden de tabulación cuando está cerrado. */
    function isSearchOpen() {
        return nav.classList.contains('search-open');
    }

    function openSearch(open) {
        if (!searchToggle || !searchForm) return;
        searchToggle.setAttribute('aria-expanded', String(open));
        nav.classList.toggle('search-open', open);
        swapIcons(searchToggle, open);
        if (open && searchField) searchField.focus();
    }

    if (searchToggle) {
        searchToggle.addEventListener('click', function () {
            openSearch(!isSearchOpen());
        });
    }

    /* Al perder el foco vuelve a su posición inicial. `focusout` avisa a
       dónde se fue el foco: si sigue dentro del buscador (por ejemplo al
       pasar del campo al botón de enviar), no se cierra. */
    var searchBox = nav.querySelector('.nav-search');
    if (searchBox) {
        searchBox.addEventListener('focusout', function (event) {
            if (searchBox.contains(event.relatedTarget)) return;
            openSearch(false);
        });
    }

    /* --------------------------------------------------------
       Cierres globales: Escape y clic fuera
       -------------------------------------------------------- */

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;

        var active = document.activeElement;

        if (isSearchOpen()) {
            openSearch(false);
            if (searchToggle) searchToggle.focus();
            return;
        }

        var anyOpen = false;
        dropdowns.forEach(function (button) {
            if (button.getAttribute('aria-expanded') === 'true') {
                anyOpen = true;
                if (active && button.parentElement.contains(active)) button.focus();
            }
        });

        if (anyOpen) {
            closeDropdowns(null);
            return;
        }

        if (nav.classList.contains('is-open')) {
            openMenu(false);
            if (navToggle) navToggle.focus();
        }
    });

    document.addEventListener('click', function (event) {
        if (nav.contains(event.target)) return;
        closeDropdowns(null);
        openSearch(false);
    });

    /* --------------------------------------------------------
       Cambio de tamaño: el menú móvil no debe quedar "abierto"
       cuando la barra vuelve a ser horizontal
       -------------------------------------------------------- */

    function syncWithWidth(mq) {
        if (mq.matches) openMenu(false);
        closeDropdowns(null);
    }

    if (desktop.addEventListener) {
        desktop.addEventListener('change', syncWithWidth);
    } else if (desktop.addListener) {
        desktop.addListener(syncWithWidth);
    }
})();


/* ============================================================
   PANEL DE DISEÑO
   Herramienta de la maqueta, no del catálogo. Cada grupo de
   radios escribe un atributo en <html>; el CSS hace el resto.
   Para sacarlo: borrar este bloque, la sección del mismo nombre
   en components.css y el bloque del mismo nombre en index.html.
   ============================================================ */

(function () {
    'use strict';

    var panel = document.querySelector('.panel');
    if (!panel) return;

    var toggle = panel.querySelector('.panel-toggle');
    var panelBody = document.getElementById('panel-design');
    var root = document.documentElement;

    /* nombre del grupo de radios → atributo que escribe en <html> */
    var attributes = {
        palette: 'data-palette',
        radius:  'data-radius',
        banner:  'data-banner',
        nav:     'data-nav'
    };

    function openPanel(open) {
        toggle.setAttribute('aria-expanded', String(open));
        panelBody.hidden = !open;
        var openIcon = toggle.querySelector('[data-icon-open]');
        var closeIcon = toggle.querySelector('[data-icon-close]');
        if (openIcon && closeIcon) {
            openIcon.hidden = open;
            closeIcon.hidden = !open;
        }
    }

    toggle.addEventListener('click', function () {
        openPanel(toggle.getAttribute('aria-expanded') !== 'true');
    });

    /* Un solo listener: aprovecha que el evento burbujea desde los radios */
    panelBody.addEventListener('change', function (event) {
        var control = event.target;
        var attribute = attributes[control.name];
        if (!attribute) return;
        root.setAttribute(attribute, control.value);
        if (window.theme) window.theme.save(attribute, control.value);
    });

    /* Volver a los valores por defecto: los del HTML, no los guardados */
    var reset = panelBody.querySelector('.panel-reset');
    if (reset) {
        reset.addEventListener('click', function () {
            if (window.theme) window.theme.clear();
            Object.keys(attributes).forEach(function (name) {
                var first = panelBody.querySelector('input[name="' + name + '"]');
                if (!first) return;
                first.checked = true;
                root.setAttribute(attributes[name], first.value);
            });
        });
    }

    /* Si el navegador no deja guardar, se avisa en vez de fingir que guarda */
    var notice = panelBody.querySelector('.panel-notice');
    if (notice && window.theme && !window.theme.available) notice.hidden = false;

    /* El estado inicial lo manda el HTML, no el JS: se marcan los
       radios que coinciden con los atributos que ya tiene <html> */
    Object.keys(attributes).forEach(function (name) {
        var value = root.getAttribute(attributes[name]);
        if (!value) return;
        var radio = panelBody.querySelector('input[name="' + name + '"][value="' + value + '"]');
        if (radio) radio.checked = true;
    });

    /* Clic fuera: el panel es una herramienta flotante y no tiene por qué
       quedar tapando la página después de elegir. El clic en el botón no
       cuenta —está dentro de `.panel`— así que no pelea con el toggle.
       Si el foco había quedado adentro, vuelve al botón: al ocultar el
       cuerpo con `hidden` se perdería. */
    document.addEventListener('click', function (event) {
        if (panelBody.hidden) return;
        if (panel.contains(event.target)) return;
        var focus = document.activeElement;
        openPanel(false);
        if (focus && panelBody.contains(focus)) toggle.focus();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !panelBody.hidden) {
            openPanel(false);
            toggle.focus();
        }
    });
})();


/* ============================================================
   CATÁLOGO — filtro por categoría, buscador y paginación
   Los productos están en el HTML, no acá: el script sólo decide
   cuáles se muestran. Sin JS se ven los 24 y la paginación queda
   oculta, así que la página nunca queda a medias.
   ============================================================ */

(function () {
    'use strict';

    var grid = document.querySelector('[data-grid]');
    if (!grid) return;

    var PER_PAGE = 8;

    var items = Array.prototype.slice.call(grid.querySelectorAll('.grid-item'));
    var chips = Array.prototype.slice.call(document.querySelectorAll('.chip[data-filter]'));
    var result = document.querySelector('[data-result]');
    var empty = document.querySelector('[data-empty]');
    var pagination = document.querySelector('[data-pagination]');
    var pageList = document.querySelector('[data-pages]');
    var prevButton = document.querySelector('[data-prev]');
    var nextButton = document.querySelector('[data-next]');
    var form = document.getElementById('form-search');
    var field = document.getElementById('q');

    var category = 'todas';
    var query = '';
    var page = 1;

    /* --------------------------------------------------------
       Qué productos pasan el filtro
       -------------------------------------------------------- */

    function normalize(text) {
        return text
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');   /* saca tildes: "indigo" encuentra "índigo" */
    }

    function matching() {
        var term = normalize(query).trim();

        return items.filter(function (item) {
            var inCategory = category === 'todas' || item.dataset.category === category;
            if (!inCategory) return false;
            if (!term) return true;
            return normalize(item.dataset.name).indexOf(term) !== -1;
        });
    }

    /* --------------------------------------------------------
       Pintado
       -------------------------------------------------------- */

    function resultText(total) {
        if (total === 0) return 'Ningún producto coincide con el filtro';
        if (total === 1) return '1 producto';
        return total + ' productos';
    }

    function renderPagination(pages) {
        pageList.textContent = '';

        if (pages < 2) {
            pagination.hidden = true;
            return;
        }

        pagination.hidden = false;

        for (var number = 1; number <= pages; number++) {
            var li = document.createElement('li');
            var button = document.createElement('button');

            button.type = 'button';
            button.className = 'page';
            button.dataset.page = String(number);
            button.textContent = String(number);

            if (number === page) {
                button.setAttribute('aria-current', 'page');
            } else {
                /* El número solo no dice a dónde lleva fuera de contexto */
                button.setAttribute('aria-label', 'Ir a la página ' + number);
            }

            li.appendChild(button);
            pageList.appendChild(li);
        }

        prevButton.disabled = page === 1;
        nextButton.disabled = page === pages;
    }

    function render() {
        var visible = matching();
        var total = visible.length;
        var pages = Math.max(1, Math.ceil(total / PER_PAGE));

        if (page > pages) page = pages;

        var from = (page - 1) * PER_PAGE;
        var to = from + PER_PAGE;

        items.forEach(function (item) {
            var index = visible.indexOf(item);
            item.hidden = index === -1 || index < from || index >= to;
        });

        if (result) result.textContent = resultText(total);
        if (empty) empty.hidden = total !== 0;

        renderPagination(total === 0 ? 0 : pages);
    }

    /* --------------------------------------------------------
       Eventos
       -------------------------------------------------------- */

    chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            category = chip.dataset.filtro;
            page = 1;

            chips.forEach(function (other) {
                other.setAttribute('aria-pressed', String(other === chip));
            });

            render();
        });
    });

    if (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            query = field ? field.value : '';
            page = 1;
            render();
            document.getElementById('products').scrollIntoView({ block: 'start' });
        });
    }

    if (field) {
        field.addEventListener('input', function () {
            query = field.value;
            page = 1;
            render();
        });
    }

    function goTo(number) {
        page = number;
        render();
        document.getElementById('products').scrollIntoView({ block: 'start' });
    }

    pageList.addEventListener('click', function (event) {
        var button = event.target.closest('[data-page]');
        if (button) goTo(Number(button.dataset.page));
    });

    prevButton.addEventListener('click', function () {
        if (page > 1) goTo(page - 1);
    });

    nextButton.addEventListener('click', function () {
        goTo(page + 1);
    });

    render();
})();


/* ============================================================
   GALERÍA DE PRODUCTO — cambio de foto por desvanecido (fade)
   Las fotos ya están en el HTML y la primera viene marcada como
   activa. El script sólo cambia cuál lo es; si no corre, se ve esa
   foto y las miniaturas siguen siendo enlaces a cada una.
   ============================================================ */

(function () {
    'use strict';

    var gallery = document.querySelector('[data-gallery]');
    if (!gallery) return;

    var slides = Array.prototype.slice.call(gallery.querySelectorAll('.gallery-slide'));
    var thumbs = Array.prototype.slice.call(gallery.querySelectorAll('.gallery-thumb'));
    if (slides.length < 2) return;

    /* Apaga el respaldo de `:target` del CSS: de acá en más manda el script */
    gallery.setAttribute('data-ready', '');

    var state = gallery.querySelector('[data-gallery-state]');
    var prev = gallery.querySelector('[data-prev]');
    var next = gallery.querySelector('[data-next]');

    var current = 0;

    function show(index) {
        current = (index + slides.length) % slides.length;

        slides.forEach(function (slide, i) {
            slide.classList.toggle('is-active', i === current);
        });

        thumbs.forEach(function (thumb, i) {
            var active = i === current;
            thumb.classList.toggle('is-active', active);
            if (active) {
                thumb.setAttribute('aria-current', 'true');
            } else {
                thumb.removeAttribute('aria-current');
            }
        });

        /* Región viva: la foto que se ve también se anuncia */
        if (state) state.textContent = 'Foto ' + (current + 1) + ' de ' + slides.length;
    }

    thumbs.forEach(function (thumb, i) {
        thumb.addEventListener('click', function (event) {
            event.preventDefault();     /* con JS cambia el visor, no salta al ancla */
            show(i);
        });
    });

    /* Las flechas no hacen nada sin script, así que recién ahora se muestran */
    [prev, next].forEach(function (button) {
        if (button) button.hidden = false;
    });

    if (prev) {
        prev.addEventListener('click', function () {
            show(current - 1);
        });
    }

    if (next) {
        next.addEventListener('click', function () {
            show(current + 1);
        });
    }

    /* Flechas del teclado mientras el foco esté dentro de la galería */
    gallery.addEventListener('keydown', function (event) {
        if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;
        event.preventDefault();
        show(event.key === 'ArrowLeft' ? current - 1 : current + 1);
    });

    show(0);
})();


/* ============================================================
   PESTAÑAS — "Más sobre este producto"
   No hay elemento nativo para pestañas: acá ARIA es la única
   salida. Sin este bloque la lista sigue siendo un índice de
   enlaces y los tres paneles se ven enteros, así que la página
   nunca queda con contenido escondido y sin forma de abrirlo.
   ============================================================ */

(function () {
    'use strict';

    var tabs = document.querySelector('[data-tabs]');
    if (!tabs) return;

    var list = tabs.querySelector('[data-tabs-list]');
    var links = Array.prototype.slice.call(tabs.querySelectorAll('.tab'));
    if (!list || links.length < 2) return;

    /* Cada pestaña apunta a su panel por el `href`: si alguno falta,
       mejor dejar todo como está que romper el bloque */
    var panels = links.map(function (link) {
        return document.getElementById(link.getAttribute('href').slice(1));
    });
    if (panels.indexOf(null) !== -1) return;

    tabs.setAttribute('data-ready', '');

    list.setAttribute('role', 'tablist');
    Array.prototype.forEach.call(list.children, function (item) {
        item.setAttribute('role', 'presentation');   /* dentro de un tablist el <li> no aporta */
    });

    links.forEach(function (link, i) {
        link.setAttribute('role', 'tab');
        link.setAttribute('aria-controls', panels[i].id);
    });

    panels.forEach(function (panel, i) {
        panel.setAttribute('role', 'tabpanel');
        panel.setAttribute('aria-labelledby', links[i].id);
        panel.tabIndex = 0;                          /* el panel se puede desplazar con el teclado */
    });

    var current = 0;

    function select(index, move) {
        current = (index + links.length) % links.length;

        links.forEach(function (link, i) {
            var active = i === current;
            link.setAttribute('aria-selected', String(active));
            /* Tabulador roving: el grupo de pestañas se entra y se sale
               de una sola vez; entre ellas se navega con las flechas */
            link.tabIndex = active ? 0 : -1;
            panels[i].hidden = !active;
        });

        if (move) links[current].focus();
    }

    links.forEach(function (link, i) {
        link.addEventListener('click', function (event) {
            event.preventDefault();                 /* cambia el panel, no salta al ancla */
            select(i, false);
        });
    });

    list.addEventListener('keydown', function (event) {
        var step = { ArrowLeft: -1, ArrowRight: 1 }[event.key];

        if (step) {
            event.preventDefault();
            select(current + step, true);
        } else if (event.key === 'Home') {
            event.preventDefault();
            select(0, true);
        } else if (event.key === 'End') {
            event.preventDefault();
            select(links.length - 1, true);
        }
    });

    /* Si la URL apunta a un panel, esa es la pestaña que abre */
    var fromHash = panels.map(function (panel) {
        return '#' + panel.id;
    }).indexOf(window.location.hash);

    select(fromHash === -1 ? 0 : fromHash, false);
})();
