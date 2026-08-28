/* ==========================================================================
   APP — monta los datos en el DOM y enciende las interacciones.
   ========================================================================== */

/* --------------------------------------------------------------------------
   Montaje de las secciones que vienen de data.js
   -------------------------------------------------------------------------- */

function montar(selector, items, plantilla) {
    const destino = document.querySelector(selector)

    if (!destino || !items) {
        return
    }

    destino.innerHTML = items.map(plantilla).join("")
}

function montarContenido() {
    montar("[data-stats]", LANDING.stats, tplStat)
    montar("[data-problemas]", LANDING.problemas, tplProblema)
    montar("[data-beneficios]", LANDING.beneficios, tplBeneficio)
    montar("[data-audiencias]", LANDING.audiencias, tplAudiencia)
    montar("[data-features]", LANDING.features, tplFeature)
    montar("[data-planes]", LANDING.planes, tplPlan)
    montar("[data-testimonios]", LANDING.testimonios, tplTestimonio)
    montar("[data-faq]", LANDING.faq, tplFaq)

    const schema = document.querySelector("[data-faq-schema]")

    if (schema) {
        schema.textContent = tplFaqSchema(LANDING.faq)
    }
}

/* --------------------------------------------------------------------------
   Header: sombra al hacer scroll y menú mobile
   -------------------------------------------------------------------------- */

function iniciarHeader() {
    const header = document.querySelector("[data-header]")
    const nav = document.querySelector("[data-nav]")
    const toggle = document.querySelector("[data-nav-toggle]")

    if (!header || !nav || !toggle) {
        return
    }

    const marcarScroll = () => {
        header.classList.toggle("is-stuck", window.scrollY > 8)
    }

    const cerrarMenu = () => {
        nav.classList.remove("is-open")
        toggle.setAttribute("aria-expanded", "false")
        toggle.setAttribute("aria-label", "Abrir menú")
    }

    marcarScroll()
    window.addEventListener("scroll", marcarScroll, { passive: true })

    toggle.addEventListener("click", () => {
        const abierto = nav.classList.toggle("is-open")

        toggle.setAttribute("aria-expanded", String(abierto))
        toggle.setAttribute("aria-label", abierto ? "Cerrar menú" : "Abrir menú")
    })

    nav.addEventListener("click", (evento) => {
        if (evento.target.closest("a")) {
            cerrarMenu()
        }
    })

    document.addEventListener("keydown", (evento) => {
        if (evento.key === "Escape") {
            cerrarMenu()
        }
    })

    window.addEventListener("resize", () => {
        if (window.innerWidth > 991) {
            cerrarMenu()
        }
    })
}

/* --------------------------------------------------------------------------
   Demo: pestañas entre las pantallas del producto
   -------------------------------------------------------------------------- */

function iniciarDemo() {
    const tabs = Array.from(document.querySelectorAll("[data-demo-tab]"))
    const paneles = Array.from(document.querySelectorAll("[data-demo-panel]"))

    if (!tabs.length || !paneles.length) {
        return
    }

    const activar = (clave) => {
        tabs.forEach((tab) => {
            const activo = tab.dataset.demoTab === clave

            tab.setAttribute("aria-selected", String(activo))
            tab.setAttribute("tabindex", activo ? "0" : "-1")
        })

        paneles.forEach((panel) => {
            panel.hidden = panel.dataset.demoPanel !== clave
        })
    }

    tabs.forEach((tab, indice) => {
        tab.addEventListener("click", () => activar(tab.dataset.demoTab))

        tab.addEventListener("keydown", (evento) => {
            if (evento.key !== "ArrowRight" && evento.key !== "ArrowLeft") {
                return
            }

            evento.preventDefault()

            const paso = evento.key === "ArrowRight" ? 1 : -1
            const siguiente = tabs[(indice + paso + tabs.length) % tabs.length]

            activar(siguiente.dataset.demoTab)
            siguiente.focus()
        })
    })

    activar(tabs[0].dataset.demoTab)
}

/* --------------------------------------------------------------------------
   Scroll reveal discreto
   -------------------------------------------------------------------------- */

function iniciarReveal() {
    const elementos = Array.from(document.querySelectorAll(".reveal"))

    if (!elementos.length) {
        return
    }

    const sinMovimiento = window.matchMedia("(prefers-reduced-motion: reduce)").matches

    if (sinMovimiento || !("IntersectionObserver" in window)) {
        elementos.forEach((elemento) => elemento.classList.add("is-visible"))
        return
    }

    const observador = new IntersectionObserver((entradas) => {
        entradas.forEach((entrada) => {
            if (!entrada.isIntersecting) {
                return
            }

            entrada.target.classList.add("is-visible")
            observador.unobserve(entrada.target)
        })
    }, { rootMargin: "0px 0px -10% 0px", threshold: 0.1 })

    elementos.forEach((elemento, indice) => {
        elemento.style.transitionDelay = `${Math.min(indice % 6, 5) * 60}ms`
        observador.observe(elemento)
    })
}

/* --------------------------------------------------------------------------
   Detalles menores
   -------------------------------------------------------------------------- */

function iniciarAnio() {
    const anio = document.querySelector("[data-anio]")

    if (anio) {
        anio.textContent = String(new Date().getFullYear())
    }
}

/* Al hacer clic en un input o textarea se selecciona todo su contenido. */
function iniciarSeleccionDeTexto() {
    document.addEventListener("click", (evento) => {
        const campo = evento.target.closest("input, textarea")

        if (campo && !["radio", "checkbox"].includes(campo.type)) {
            campo.select()
        }
    })
}

/* --------------------------------------------------------------------------
   Arranque
   -------------------------------------------------------------------------- */

document.addEventListener("DOMContentLoaded", () => {
    montarContenido()
    iniciarHeader()
    iniciarDemo()
    iniciarReveal()
    iniciarAnio()
    iniciarSeleccionDeTexto()
})
