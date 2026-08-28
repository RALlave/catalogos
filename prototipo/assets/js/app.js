/* ==========================================================================
   APP — carga el JSON de la tienda, aplica tema (layout + paleta)
   y pinta la página actual según [data-page].
   ========================================================================== */

(function () {
    "use strict";

    /* Configuración de composición por layout: qué variantes usa cada uno.
       Agregar un layout nuevo = agregar una entrada acá + su archivo CSS. */
    const LAYOUTS = {
        "01": {
            grid: ["grid-3"],
            card: [],
            media: ["card-media-framed"],
            tab: true,
            body: ["precio", "specs"],
            paleta_demo: "tierra"
        },
        "02": {
            grid: ["grid-3"],
            card: [],
            media: ["card-media-cover"],
            tab: false,
            body: ["titulo", "specs", "precio"],
            paleta_demo: "madera"
        },
        "03": {
            grid: ["grid-3"],
            card: ["card-shadow"],
            media: [],
            tab: false,
            body: ["precio", "titulo", "beneficios"],
            paleta_demo: "promocional"
        },
        "04": {
            grid: ["grid-3"],
            card: ["card-flat"],
            media: [],
            tab: false,
            body: ["titulo", "beneficios"],
            precio_esquina: true,
            cta: "Ver oferta",
            paleta_demo: "oferta"
        }
    };

    const DATA_URL = "data/tienda.json";

    function $(selector) {
        return document.querySelector(selector);
    }

    function param(nombre) {
        return new URLSearchParams(window.location.search).get(nombre);
    }

    function aplicarPaleta(paleta) {
        if (!paleta) {
            return;
        }
        const reglas = Object.keys(paleta)
            .map(function (clave) {
                return "    " + clave + ": " + paleta[clave] + ";";
            })
            .join("\n");

        const estilo = document.createElement("style");
        estilo.textContent = ":root {\n" + reglas + "\n}";
        document.head.appendChild(estilo);
    }

    function aplicarLayout(id) {
        const enlace = document.createElement("link");
        enlace.rel = "stylesheet";
        enlace.href = "assets/css/layouts/layout-" + id + ".css";
        document.head.appendChild(enlace);
        document.body.setAttribute("data-layout", id);
    }

    /* La barra fija arranca transparente, toma fondo al scrollear,
       se esconde al bajar y vuelve a aparecer al subir. */
    function activarTopbar() {
        const barra = document.querySelector(".page-topbar");
        if (!barra) {
            return;
        }

        let anterior = window.scrollY;

        function actualizar() {
            const actual = window.scrollY;
            const bajando = actual > anterior;
            const pasoLaBarra = actual > barra.offsetHeight;

            barra.classList.toggle("page-topbar-solid", actual > 8);
            barra.classList.toggle("page-topbar-hidden", bajando && pasoLaBarra);

            anterior = actual;
        }

        window.addEventListener("scroll", actualizar, { passive: true });
        actualizar();
    }

    function pintarComunes(data) {
        const marca = $("[data-brand]");
        if (marca) {
            marca.innerHTML = Render.brand(data.tienda);
        }

        const pie = $("[data-footer]");
        if (pie) {
            pie.innerHTML = Render.footer(data.tienda);
        }

        const barra = $("[data-actionbar]");
        if (barra) {
            barra.innerHTML = Render.actionbar(data.contacto, data.tienda);
        }

        const titulo = $("[data-store-name]");
        if (titulo) {
            titulo.textContent = data.tienda.nombre;
        }

        document.title = document.title.replace("{tienda}", data.tienda.nombre);
    }

    /* ------------------------------------------------------------- catálogo */

    function pintarCatalogo(data, config) {
        const contenedor = $("[data-catalogo]");
        if (!contenedor) {
            return;
        }

        let categoriaActiva = param("cat") || "";

        function productosVisibles() {
            if (!categoriaActiva) {
                return data.productos;
            }
            return data.productos.filter(function (producto) {
                return producto.categoria === categoriaActiva;
            });
        }

        function pintar() {
            const visibles = productosVisibles();

            contenedor.className = ["grid"].concat(config.grid).join(" ");
            contenedor.innerHTML = Render.cards(visibles, config);

            const contador = $("[data-total]");
            if (contador) {
                contador.textContent = visibles.length + " productos";
            }

            pintarFiltros();
            propagarPreview();
        }

        function pintarFiltros() {
            const caja = $("[data-filtros]");
            if (!caja) {
                return;
            }

            caja.innerHTML = Render.filters(data.categorias, categoriaActiva);

            const botones = caja.querySelectorAll("[data-filtro]");

            Array.prototype.forEach.call(botones, function (boton) {
                boton.addEventListener("click", function () {
                    categoriaActiva = boton.getAttribute("data-filtro");
                    actualizarUrl();
                    pintar();
                });
            });
        }

        function actualizarUrl() {
            const url = new URL(window.location.href);
            if (categoriaActiva) {
                url.searchParams.set("cat", categoriaActiva);
            } else {
                url.searchParams.delete("cat");
            }
            window.history.replaceState(null, "", url.pathname.split("/").pop() + url.search);
        }

        pintar();
    }

    /* -------------------------------------------------------------- producto */

    function productoActual(productos) {
        const id = new URLSearchParams(window.location.search).get("id");
        if (!id) {
            return null;
        }
        return productos.filter(function (item) {
            return item.id === id;
        })[0] || null;
    }

    function pintarProducto(data, config) {
        const contenedor = $("[data-producto]");
        if (!contenedor) {
            return;
        }

        const producto = productoActual(data.productos);

        if (!producto) {
            contenedor.innerHTML = '<div class="empty"><p>No encontramos ese producto.</p></div>';
            return;
        }

        document.title = producto.nombre + " — " + data.tienda.nombre;
        contenedor.innerHTML = Render.detail(producto, data.tienda, data.contacto);

        activarGaleria(contenedor);
        activarCompartir(contenedor, producto);
        pintarRelacionados(data, config, producto);
    }

    function pintarRelacionados(data, config, producto) {
        const contenedor = $("[data-relacionados]");
        if (!contenedor) {
            return;
        }

        /* Primero los de la misma categoría; si no alcanzan, completa con el resto. */
        const otros = data.productos.filter(function (item) {
            return item.id !== producto.id && item.categoria === producto.categoria;
        }).slice(0, 3);

        if (otros.length === 0) {
            return;
        }

        const seccion = $("[data-relacionados-seccion]");
        if (seccion) {
            seccion.hidden = false;
        }

        contenedor.className = ["grid", "grid-3"].join(" ");
        contenedor.innerHTML = Render.cards(otros, config);
    }

    function activarGaleria(contenedor) {
        const principal = contenedor.querySelector("[data-gallery-main]");
        const miniaturas = contenedor.querySelectorAll(".gallery-thumb");

        Array.prototype.forEach.call(miniaturas, function (boton) {
            boton.addEventListener("click", function () {
                principal.src = boton.getAttribute("data-src");
                Array.prototype.forEach.call(miniaturas, function (otro) {
                    otro.removeAttribute("aria-current");
                });
                boton.setAttribute("aria-current", "true");
            });
        });
    }

    function activarCompartir(contenedor, producto) {
        const boton = contenedor.querySelector("[data-share]");
        if (!boton) {
            return;
        }

        boton.addEventListener("click", function () {
            const url = window.location.href;

            if (navigator.share) {
                navigator.share({ title: producto.nombre, url: url });
                return;
            }

            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(function () {
                    boton.textContent = "Link copiado";
                });
            }
        });
    }

    /* -------------------------------------------------------------- contacto */

    function pintarContacto(data) {
        const datos = $("[data-contacto]");
        if (datos) {
            datos.innerHTML = Render.contactList(data.contacto);
        }

        const horarios = $("[data-horarios]");
        if (horarios) {
            horarios.innerHTML = Render.horarios(data.contacto.horarios);
        }

        const botones = $("[data-contacto-botones]");
        if (botones) {
            botones.innerHTML = Render.contactButtons(data.contacto, data.tienda);
        }

        const descripcion = $("[data-tienda-descripcion]");
        if (descripcion && Render.has(data.tienda.descripcion)) {
            descripcion.innerHTML = "<p>" + Render.esc(data.tienda.descripcion) + "</p>";
        }
    }

    /* ------------------------------------------------------------------ init */

    /* Previsualización: ?layout=03 y ?paleta=oferta pisan lo que trae el JSON
       y se propagan a los enlaces internos para poder navegar el demo. */
    function propagarPreview() {
        const layout = param("layout");
        const paleta = param("paleta");

        if (!layout && !paleta) {
            return;
        }

        const enlaces = document.querySelectorAll('a[href^="index.html"], a[href^="producto.html"], a[href^="contacto.html"]');

        Array.prototype.forEach.call(enlaces, function (enlace) {
            const url = new URL(enlace.getAttribute("href"), window.location.href);
            if (layout) {
                url.searchParams.set("layout", layout);
            }
            if (paleta) {
                url.searchParams.set("paleta", paleta);
            }
            enlace.setAttribute("href", url.pathname.split("/").pop() + url.search);
        });
    }

    function resolverPaleta(tema, config) {
        const solicitada = param("paleta");
        const paletas = tema.paletas || {};

        if (solicitada && paletas[solicitada]) {
            return paletas[solicitada];
        }

        if (param("layout") && config.paleta_demo && paletas[config.paleta_demo]) {
            return paletas[config.paleta_demo];
        }

        return tema.paleta;
    }

    function init(data) {
        const tema = data.tema || {};
        const layoutId = param("layout") || tema.layout || "01";
        const config = LAYOUTS[layoutId] || LAYOUTS["01"];

        aplicarPaleta(resolverPaleta(tema, config));
        aplicarLayout(layoutId);
        activarTopbar();
        pintarComunes(data);

        const pagina = document.body.getAttribute("data-page");

        if (pagina === "catalogo") {
            pintarCatalogo(data, config);
        } else if (pagina === "producto") {
            pintarProducto(data, config);
        } else if (pagina === "contacto") {
            pintarContacto(data);
        }

        propagarPreview();
    }

    fetch(DATA_URL)
        .then(function (respuesta) {
            return respuesta.json();
        })
        .then(init)
        .catch(function () {
            const main = $("[data-main]");
            if (main) {
                main.innerHTML = '<div class="container"><div class="empty"><p>No se pudieron cargar los datos de la tienda.</p></div></div>';
            }
        });
})();
