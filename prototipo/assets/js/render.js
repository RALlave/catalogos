/* ==========================================================================
   RENDER — plantillas únicas de marca, tarjeta, ficha, detalle y contacto.
   Regla: si un dato no viene, NO se renderiza. Nunca se inventan valores.
   ========================================================================== */

const Render = (function () {
    "use strict";

    function esc(value) {
        return String(value == null ? "" : value)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;");
    }

    function has(value) {
        if (value == null) {
            return false;
        }
        if (Array.isArray(value)) {
            return value.length > 0;
        }
        return String(value).trim() !== "";
    }

    function numero(valor) {
        return Number(valor).toLocaleString("es-PY");
    }

    /* ---------------------------------------------------------------- marca */

    function brand(tienda, variantes) {
        if (!tienda) {
            return "";
        }
        const clases = ["brand"].concat(variantes || []).join(" ");
        const contenido = has(tienda.logo)
            ? '<span class="brand-logo"><img src="' + esc(tienda.logo) + '" alt="' + esc(tienda.nombre) + '"></span>'
            : '<span class="brand-name">' + esc(tienda.nombre) + "</span>";

        return '<div class="' + clases + '"><a href="index.html">' + contenido + "</a></div>";
    }

    /* --------------------------------------------------------------- precio */

    function precio(producto, variantes) {
        if (!has(producto.precio)) {
            return "";
        }
        const clases = ["price"].concat(variantes || []).join(" ");
        const moneda = has(producto.moneda)
            ? '<span class="price-currency">' + esc(producto.moneda) + "</span>"
            : "";

        return (
            '<div class="' + clases + '">' +
            moneda +
            '<span class="price-value">' + numero(producto.precio) + "</span>" +
            "</div>"
        );
    }

    /* --------------------------------------------------------------- badges */

    function badge(item, variantes) {
        const clases = ["badge"].concat(variantes || []);

        if (item.tipo === "descuento") {
            clases.push("badge-discount");
            return (
                '<span class="' + clases.join(" ") + '">' +
                '<span class="badge-value">' + esc(item.texto) + "</span>" +
                (has(item.detalle) ? '<span class="badge-label">' + esc(item.detalle) + "</span>" : "") +
                "</span>"
            );
        }

        clases.push("badge-strong");
        return '<span class="' + clases.join(" ") + '">' + esc(item.texto) + "</span>";
    }

    function badges(producto, opciones) {
        const config = opciones || {};
        const lista = Array.isArray(producto.badges) ? producto.badges.slice() : [];
        const partes = [];

        lista.forEach(function (item, indice) {
            const variantes = [];
            if (config.corner) {
                variantes.push("badge-corner");
                if (indice > 0) {
                    variantes.push("badge-corner-right");
                }
            }
            partes.push(badge(item, variantes));
        });

        if (producto.estado === "agotado") {
            const variantes = ["badge-soldout"];
            if (config.corner) {
                variantes.push("badge-corner", "badge-corner-right");
            }
            partes.push('<span class="badge ' + variantes.join(" ") + '">Agotado</span>');
        }

        if (partes.length === 0) {
            return "";
        }

        return config.corner ? partes.join("") : '<div class="badge-list">' + partes.join("") + "</div>";
    }

    /* ---------------------------------------------------------------- ficha */

    function specs(atributos, variantes) {
        if (!has(atributos)) {
            return "";
        }

        const filas = atributos.map(function (attr) {
            let valor = "";

            if (attr.tipo === "colores" && has(attr.valores)) {
                valor =
                    '<span class="swatches">' +
                    attr.valores
                        .map(function (color) {
                            return '<span class="swatch" style="background-color: ' + esc(color) + '"></span>';
                        })
                        .join("") +
                    "</span>";
            } else if (has(attr.valor)) {
                valor = esc(attr.valor);
            } else {
                return "";
            }

            return (
                '<div class="spec">' +
                "<dt>" + esc(attr.etiqueta) + "</dt>" +
                "<dd>" + valor + "</dd>" +
                "</div>"
            );
        });

        const clases = ["specs"].concat(variantes || []).join(" ");
        return '<div class="' + clases + '"><dl>' + filas.join("") + "</dl></div>";
    }

    /* ----------------------------------------------------------- beneficios */

    function benefits(lista, variantes) {
        if (!has(lista)) {
            return "";
        }
        const clases = ["benefits"].concat(variantes || []).join(" ");
        const items = lista
            .map(function (texto) {
                return "<li>" + esc(texto) + "</li>";
            })
            .join("");

        return '<div class="' + clases + '"><ul>' + items + "</ul></div>";
    }

    /* --------------------------------------------------------------- tarjeta */

    /* El cuerpo de la tarjeta se compone por layout: cada bloque es opcional
       y el orden lo define la configuración (config.body). */
    function cardBlock(nombre, producto, config) {
        if (nombre === "titulo") {
            return config.tab ? "" : '<div class="card-title"><h3>' + esc(producto.nombre) + "</h3></div>";
        }
        if (nombre === "precio") {
            return precio(producto, config.precio);
        }
        if (nombre === "specs") {
            return specs(producto.atributos, config.specs);
        }
        if (nombre === "beneficios") {
            return benefits(producto.beneficios);
        }
        return "";
    }

    function card(producto, opciones) {
        const config = opciones || {};
        const clasesCard = ["card"].concat(config.card || []).join(" ");
        const clasesMedia = ["card-media"].concat(config.media || []).join(" ");
        const clasesBody = ["card-body"].concat(config.body_class || []).join(" ");
        const bloques = config.body || ["titulo", "precio", "specs"];
        const url = "producto.html?id=" + encodeURIComponent(producto.id);

        const tab = config.tab
            ? '<div class="card-tab"><span>' + esc(producto.nombre) + "</span></div>"
            : "";

        const precioEsquina = config.precio_esquina ? precio(producto, ["price-corner"]) : "";

        const cta = has(config.cta)
            ? '<div class="btn btn-sm btn-accent card-cta"><a href="' + url + '">' + esc(config.cta) + "</a></div>"
            : "";

        const contenido = bloques
            .map(function (bloque) {
                return cardBlock(bloque, producto, config);
            })
            .join("");

        return (
            '<article class="' + clasesCard + '">' +
            tab +
            '<div class="' + clasesMedia + '">' +
            badges(producto, { corner: true }) +
            '<img src="' + esc(producto.imagen) + '" alt="' + esc(producto.nombre) + '" loading="lazy">' +
            cta +
            "</div>" +
            '<div class="' + clasesBody + '">' + contenido + precioEsquina + "</div>" +
            '<span class="card-link"><a href="' + url + '"><span class="visually-hidden">' +
            esc(producto.nombre) +
            "</span></a></span>" +
            "</article>"
        );
    }

    function cards(productos, opciones) {
        return productos
            .map(function (producto) {
                return card(producto, opciones);
            })
            .join("");
    }

    /* --------------------------------------------------------------- filtros */

    function filters(categorias, activa) {
        if (!has(categorias)) {
            return "";
        }

        const items = [{ id: "", nombre: "Todas" }].concat(categorias);

        const botones = items
            .map(function (categoria) {
                const clases = ["btn", "btn-sm"];
                if (categoria.id !== activa) {
                    clases.push("btn-outline");
                }
                return (
                    '<div class="' + clases.join(" ") + '">' +
                    '<button type="button" data-filtro="' + esc(categoria.id) + '">' +
                    esc(categoria.nombre) +
                    "</button></div>"
                );
            })
            .join("");

        return '<div class="filters">' + botones + "</div>";
    }

    /* ------------------------------------------------------------ whatsapp */

    function whatsappUrl(numero, mensaje) {
        return "https://wa.me/" + encodeURIComponent(numero) + "?text=" + encodeURIComponent(mensaje);
    }

    function whatsappProducto(contacto, tienda, producto) {
        if (!has(contacto.whatsapp)) {
            return "";
        }
        const mensaje =
            "Hola " + tienda.nombre + ", me interesa el producto: " + producto.nombre +
            " (" + window.location.href + ")";

        return (
            '<div class="btn btn-whatsapp btn-lg btn-block-mobile">' +
            '<a href="' + esc(whatsappUrl(contacto.whatsapp, mensaje)) + '" target="_blank" rel="noopener">' +
            "Consultar por WhatsApp</a></div>"
        );
    }

    /* --------------------------------------------------------------- galería */

    function gallery(producto) {
        const imagenes = has(producto.galeria) ? producto.galeria : [producto.imagen];

        const thumbs =
            imagenes.length > 1
                ? '<div class="gallery-thumbs">' +
                  imagenes
                      .map(function (src, indice) {
                          return (
                              '<button type="button" class="gallery-thumb" data-src="' + esc(src) + '"' +
                              (indice === 0 ? ' aria-current="true"' : "") + ">" +
                              '<img src="' + esc(src) + '" alt="">' +
                              "</button>"
                          );
                      })
                      .join("") +
                  "</div>"
                : "";

        return (
            '<div class="gallery">' +
            '<div class="gallery-main">' +
            '<img src="' + esc(imagenes[0]) + '" alt="' + esc(producto.nombre) + '" data-gallery-main>' +
            "</div>" +
            thumbs +
            "</div>"
        );
    }

    /* ---------------------------------------------------------------- detalle */

    function detail(producto, tienda, contacto) {
        const descripcion = has(producto.descripcion)
            ? '<div class="detail-text"><p>' + esc(producto.descripcion) + "</p></div>"
            : "";

        const facebook = has(contacto.facebook)
            ? '<div class="btn btn-facebook btn-lg btn-block-mobile">' +
              '<a href="' + esc(contacto.facebook) + '" target="_blank" rel="noopener">Escribir por Facebook</a></div>'
            : "";

        return (
            '<div class="detail">' +
            gallery(producto) +
            '<div class="detail-info">' +
            badges(producto) +
            '<div class="detail-title"><h1>' + esc(producto.nombre) + "</h1></div>" +
            precio(producto, ["price-lg"]) +
            descripcion +
            specs(producto.atributos, ["specs-boxed"]) +
            benefits(producto.beneficios) +
            '<div class="btn-group">' +
            whatsappProducto(contacto, tienda, producto) +
            facebook +
            '<div class="btn btn-outline btn-lg"><button type="button" data-share>Compartir</button></div>' +
            "</div>" +
            "</div>" +
            "</div>"
        );
    }

    /* --------------------------------------------------------------- contacto */

    function contactItem(etiqueta, valor, href) {
        if (!has(valor)) {
            return "";
        }
        const contenido = has(href)
            ? '<a href="' + esc(href) + '" target="_blank" rel="noopener">' + esc(valor) + "</a>"
            : esc(valor);

        return (
            '<div class="contact-item">' +
            '<span class="contact-label">' + esc(etiqueta) + "</span>" +
            '<span class="contact-value">' + contenido + "</span>" +
            "</div>"
        );
    }

    function contactList(contacto) {
        const items = [
            contactItem("WhatsApp", contacto.whatsapp_visible, has(contacto.whatsapp) ? whatsappUrl(contacto.whatsapp, "Hola, quiero hacer una consulta.") : ""),
            contactItem("Teléfono", contacto.telefono, has(contacto.telefono) ? "tel:" + contacto.telefono.replace(/\s/g, "") : ""),
            contactItem("Email", contacto.email, has(contacto.email) ? "mailto:" + contacto.email : ""),
            contactItem("Facebook", has(contacto.facebook) ? "Ver página" : "", contacto.facebook),
            contactItem("Instagram", has(contacto.instagram) ? "Ver perfil" : "", contacto.instagram),
            contactItem("Dirección", contacto.direccion, contacto.mapa),
            contactItem("Sitio web", contacto.web, "")
        ].join("");

        return '<div class="contact-list">' + items + "</div>";
    }

    function horarios(lista) {
        if (!has(lista)) {
            return "";
        }
        const items = lista
            .map(function (item) {
                return (
                    '<div class="contact-item">' +
                    '<span class="contact-label">' + esc(item.dias) + "</span>" +
                    '<span class="contact-value">' + esc(item.horas) + "</span>" +
                    "</div>"
                );
            })
            .join("");

        return '<div class="contact-list">' + items + "</div>";
    }

    function contactButtons(contacto, tienda) {
        const partes = [];

        if (has(contacto.whatsapp)) {
            const mensaje = "Hola " + tienda.nombre + ", quiero hacer una consulta.";
            partes.push(
                '<div class="btn btn-whatsapp btn-lg btn-block">' +
                '<a href="' + esc(whatsappUrl(contacto.whatsapp, mensaje)) + '" target="_blank" rel="noopener">Escribir por WhatsApp</a></div>'
            );
        }

        if (has(contacto.facebook)) {
            partes.push(
                '<div class="btn btn-facebook btn-lg btn-block">' +
                '<a href="' + esc(contacto.facebook) + '" target="_blank" rel="noopener">Escribir por Facebook</a></div>'
            );
        }

        return partes.join("");
    }

    /* -------------------------------------------------------------- actionbar */

    const ICONO_WHATSAPP =
        '<svg viewBox="0 0 448 512" role="img" aria-hidden="true"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 110.9L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg>';

    const ICONO_FACEBOOK =
        '<svg viewBox="0 0 320 512" role="img" aria-hidden="true"><path d="M80 299.3V512h116V299.3h86.5l18-97.8H196v-34.6c0-51.7 20.3-71.5 72.7-71.5 16.3 0 29.4.4 37 1.2V7.9C291.4 4 256.4 0 236.2 0 129.3 0 80 50.5 80 159.4v42.1H14v97.8h66z"/></svg>';

    function actionbar(contacto, tienda) {
        const partes = [];

        if (has(contacto.whatsapp)) {
            const mensaje = "Hola " + tienda.nombre + ", quiero hacer una consulta.";
            partes.push(
                '<div class="btn btn-whatsapp btn-icon">' +
                '<a href="' + esc(whatsappUrl(contacto.whatsapp, mensaje)) + '" target="_blank" rel="noopener" aria-label="Escribir por WhatsApp">' +
                ICONO_WHATSAPP + "</a></div>"
            );
        }

        if (has(contacto.facebook)) {
            partes.push(
                '<div class="btn btn-facebook btn-icon">' +
                '<a href="' + esc(contacto.facebook) + '" target="_blank" rel="noopener" aria-label="Escribir por Facebook">' +
                ICONO_FACEBOOK + "</a></div>"
            );
        }

        if (partes.length === 0) {
            return "";
        }

        return '<div class="container"><div class="btn-group">' + partes.join("") + "</div></div>";
    }

    /* ----------------------------------------------------------------- footer */

    function footer(tienda) {
        return '<div class="footer-inner">' + brand(tienda) + "</div>";
    }

    return {
        esc: esc,
        has: has,
        brand: brand,
        precio: precio,
        badges: badges,
        specs: specs,
        benefits: benefits,
        card: card,
        cards: cards,
        filters: filters,
        detail: detail,
        contactList: contactList,
        horarios: horarios,
        contactButtons: contactButtons,
        actionbar: actionbar,
        footer: footer,
        whatsappUrl: whatsappUrl
    };
})();
