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

    /* Hides while scrolling down and comes back on the way up. Moves under
       SCROLL_TOLERANCE are ignored so trackpad bounce does not flicker it, and
       it never hides above its own height or with the mobile menu open. */
    const SCROLL_TOLERANCE = 8
    let lastScroll = window.scrollY

    const marcarScroll = () => {
        const y = window.scrollY
        const delta = y - lastScroll

        header.classList.toggle("is-stuck", y > 8)
        /* Half height anywhere but the top of the page. */
        header.classList.toggle("is-compact", y > 8)

        if (Math.abs(delta) < SCROLL_TOLERANCE) {
            return
        }

        const menuOpen = nav.classList.contains("is-open")

        header.classList.toggle("is-hidden", delta > 0 && y > header.offsetHeight && !menuOpen)
        lastScroll = y
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

/* Every screenshot of every tab forms one sequence: each one shows for
   DEMO_DELAY and, when a tab runs out of screenshots, the next tab opens.
   Clicking a tab or a dot jumps there and the sequence goes on from it. */
const DEMO_DELAY = 4000

function iniciarDemo() {
    const demo = document.querySelector(".demo-body")
    const tabs = Array.from(document.querySelectorAll("[data-demo-tab]"))

    if (!demo || !tabs.length) {
        return
    }

    /* One dots bar below the card for every tab: each tab adds its own dots and
       only the active tab's are shown. A single screenshot gets no dots. */
    const dotsWrap = document.querySelector("[data-demo-dots]")

    const panels = tabs.map((tab) => {
        const panel = document.querySelector(`[data-demo-panel="${tab.dataset.demoTab}"]`)
        const slides = Array.from(panel.querySelectorAll("[data-demo-slide]"))
        const dots = []

        if (dotsWrap && slides.length > 1) {
            slides.forEach((slide, index) => {
                const dot = document.createElement("button")

                dot.type = "button"
                dot.setAttribute("aria-label", `Ver imagen ${index + 1} de ${slides.length}`)
                dot.addEventListener("click", () => {
                    show(tabs.indexOf(tab), index)
                    restart()
                })

                dotsWrap.appendChild(dot)
                dots.push(dot)
            })
        }

        return { tab, panel, slides, dots }
    })

    /* Flat list of [tab, slide] pairs: the order the sequence walks. */
    const steps = panels.flatMap((item, tabIndex) => item.slides.map((slide, slideIndex) => [tabIndex, slideIndex]))

    const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches

    let current = 0
    let timer = null
    let inView = false

    function show(tabIndex, slideIndex) {
        current = steps.findIndex(([t, s]) => t === tabIndex && s === slideIndex)

        panels.forEach((item, index) => {
            const active = index === tabIndex

            item.tab.setAttribute("aria-selected", String(active))
            item.tab.setAttribute("tabindex", active ? "0" : "-1")
            item.panel.hidden = !active
            item.dots.forEach((dot) => {
                dot.hidden = !active
            })

            if (!active) {
                return
            }

            item.slides.forEach((slide, i) => slide.classList.toggle("is-active", i === slideIndex))
            item.dots.forEach((dot, i) => dot.setAttribute("aria-current", String(i === slideIndex)))
        })
    }

    /* Walks the whole sequence, crossing tabs: +1 forward, -1 back. */
    function step(direction = 1) {
        const [tabIndex, slideIndex] = steps[(current + direction + steps.length) % steps.length]

        show(tabIndex, slideIndex)
    }

    function stop() {
        window.clearInterval(timer)
        timer = null
    }

    function restart() {
        stop()

        if (!reducedMotion && inView) {
            timer = window.setInterval(() => step(1), DEMO_DELAY)
        }
    }

    tabs.forEach((tab, index) => {
        tab.addEventListener("click", () => {
            show(index, 0)
            restart()
        })

        tab.addEventListener("keydown", (evento) => {
            if (!["ArrowRight", "ArrowLeft", "ArrowDown", "ArrowUp"].includes(evento.key)) {
                return
            }

            evento.preventDefault()

            const paso = ["ArrowRight", "ArrowDown"].includes(evento.key) ? 1 : -1
            const siguiente = (index + paso + tabs.length) % tabs.length

            show(siguiente, 0)
            tabs[siguiente].focus()
            restart()
        })
    })

    const arrows = [
        [document.querySelector("[data-demo-prev]"), -1],
        [document.querySelector("[data-demo-next]"), 1],
    ]

    arrows.forEach(([button, direction]) => {
        button?.addEventListener("click", () => {
            step(direction)
            restart()
        })
    })

    /* Starts when the section scrolls into view, so it opens on Dashboard. */
    if ("IntersectionObserver" in window) {
        new IntersectionObserver((entries) => {
            inView = entries[0].isIntersecting
            restart()
        }, { threshold: 0.3 }).observe(demo)
    } else {
        inView = true
    }

    show(0, 0)
    restart()
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
   Back to top: shows after scrolling and goes back to the top
   -------------------------------------------------------------------------- */

function initBackToTop() {
    const button = document.querySelector("[data-back-to-top]")

    if (!button) {
        return
    }

    const threshold = 400

    const update = () => button.classList.toggle("is-visible", window.scrollY > threshold)

    update()
    window.addEventListener("scroll", update, { passive: true })

    button.addEventListener("click", () => {
        const reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches

        window.scrollTo({ top: 0, behavior: reduced ? "auto" : "smooth" })
    })
}

/* --------------------------------------------------------------------------
   Platform brand: logo and favicon uploaded by the superadmin
   -------------------------------------------------------------------------- */

const LOCAL_HOSTS = ["lvh.me", "localhost", "127.0.0.1"]

/* The landing has no build step, so the API address comes from the domain:
   api.{domain} in production and the local API port in development. */
function apiBase() {
    const { protocol, hostname } = window.location

    if (LOCAL_HOSTS.includes(hostname)) {
        return "http://127.0.0.1:8001/api"
    }

    return `${protocol}//api.${hostname.replace(/^www\./, "")}/api`
}

/* Panel links are written as /login and /registro, which nginx serves on the
   same domain in production. Locally the panel runs on its own port, so they
   are pointed there. Must run after montarContenido(): the plans add some. */
function initPanelLinks() {
    if (!LOCAL_HOSTS.includes(window.location.hostname)) {
        return
    }

    document.querySelectorAll("[data-panel-link]").forEach((link) => {
        link.href = `http://lvh.me:5173${link.getAttribute("href")}`
    })
}

async function initPlatform() {
    let logos

    try {
        const response = await fetch(`${apiBase()}/platform`, { headers: { Accept: "application/json" } })

        if (!response.ok) {
            return
        }

        logos = (await response.json()).logos
    } catch {
        return
    }

    /* Header and footer draw the same logo. */
    document.querySelectorAll("[data-platform-logo]").forEach((logo) => {
        if (!logos.auth) {
            return
        }

        logo.src = logos.auth.src
        logo.srcset = logos.auth.srcset

        if (logos.auth.width && logos.auth.height) {
            logo.width = logos.auth.width
            logo.height = logos.auth.height
        }

        logo.hidden = false
    })

    const favicon = document.querySelector("[data-favicon]")

    if (favicon && logos.icon) {
        favicon.href = logos.icon.thumb
    }
}

/* --------------------------------------------------------------------------
   Arranque
   -------------------------------------------------------------------------- */

document.addEventListener("DOMContentLoaded", () => {
    initPlatform()
    montarContenido()
    initPanelLinks()
    iniciarHeader()
    iniciarDemo()
    iniciarReveal()
    iniciarAnio()
    iniciarSeleccionDeTexto()
    initBackToTop()
})
