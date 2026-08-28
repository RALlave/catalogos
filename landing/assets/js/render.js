/* ==========================================================================
   RENDER — plantillas HTML. Reciben datos, devuelven markup.
   No leen el DOM ni conocen de dónde salen los datos.
   ========================================================================== */

/* Set de iconos (trazo de 1.75, hereda el color con currentColor). */
const ICONS = {
    file: '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>',
    images: '<rect x="3" y="3" width="14" height="14" rx="2"/><path d="M7 21h12a2 2 0 0 0 2-2V8"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="17 13 13 9 4 17"/>',
    refresh: '<polyline points="21 3 21 9 15 9"/><path d="M21 9a9 9 0 1 0-2.6 6.4"/>',
    store: '<path d="M3 9l1.5-5h15L21 9"/><path d="M4 9v11h16V9"/><path d="M3 9a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0"/><path d="M9 20v-6h6v6"/>',
    grid: '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
    box: '<path d="M21 8l-9-5-9 5v8l9 5 9-5z"/><polyline points="3 8 12 13 21 8"/><line x1="12" y1="13" x2="12" y2="21"/>',
    share: '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.6" y1="10.5" x2="15.4" y2="6.5"/><line x1="8.6" y1="13.5" x2="15.4" y2="17.5"/>',
    sparkles: '<path d="M12 3l1.9 4.6L18.5 9.5 13.9 11.4 12 16l-1.9-4.6L5.5 9.5l4.6-1.9z"/><path d="M18 15l.9 2.1L21 18l-2.1.9L18 21l-.9-2.1L15 18l2.1-.9z"/>',
    bolt: '<polygon points="13 2 4 14 11 14 10 22 20 10 13 10"/>',
    phone: '<rect x="6" y="2" width="12" height="20" rx="2.5"/><line x1="10.5" y1="18.5" x2="13.5" y2="18.5"/>',
    rocket: '<path d="M5 15c-1.5 1.5-2 6-2 6s4.5-.5 6-2c.9-.9.9-2.3 0-3.2a2.2 2.2 0 0 0-4 0z"/><path d="M15.5 12.5L18 15c2-2 3-5 3-9-4 0-7 1-9 3l2.5 2.5z"/><path d="M9 11l-3-1 3-3 3 .5"/><path d="M13 15l1 3 3-3-.5-3"/>',
    whatsapp: '<path d="M3 21l1.6-4.4A8.4 8.4 0 1 1 7.8 20z"/><path d="M8.8 9.2c.3 2.4 2.4 4.5 4.9 4.9l1-1.4 1.9.8c-.2 1-1.1 1.7-2.2 1.6-3-.3-5.5-2.8-5.8-5.8-.1-1.1.6-2 1.6-2.2l.8 1.9z"/>',
    layers: '<polygon points="12 2 2 7.5 12 13 22 7.5 12 2"/><polyline points="2 12.5 12 18 22 12.5"/><polyline points="2 17 12 22.5 22 17"/>',
    tag: '<path d="M20.5 13.5l-7 7a2 2 0 0 1-2.8 0l-7.2-7.2A2 2 0 0 1 3 12V4a1 1 0 0 1 1-1h8a2 2 0 0 1 1.4.6l7.1 7.1a2 2 0 0 1 0 2.8z"/><circle cx="7.5" cy="7.5" r="1.3"/>',
    price: '<line x1="12" y1="2" x2="12" y2="22"/><path d="M17 6.5H9.8a3.3 3.3 0 0 0 0 6.5h4.4a3.3 3.3 0 0 1 0 6.5H6"/>',
    link: '<path d="M10 13a5 5 0 0 0 7.5.5l3-3A5 5 0 0 0 13.5 3.5l-1.7 1.7"/><path d="M14 11a5 5 0 0 0-7.5-.5l-3 3A5 5 0 0 0 10.5 20.5l1.7-1.7"/>',
    search: '<circle cx="11" cy="11" r="7"/><line x1="16.5" y1="16.5" x2="21" y2="21"/>',
    dashboard: '<rect x="3" y="3" width="18" height="18" rx="2.5"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/>',
    chart: '<line x1="3" y1="21" x2="21" y2="21"/><rect x="5" y="12" width="4" height="6" rx="1"/><rect x="11" y="7" width="4" height="11" rx="1"/><rect x="17" y="3" width="4" height="15" rx="1"/>',
    check: '<polyline points="20 6 9 17 4 12"/>',
    plus: '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
    image: '<rect x="3" y="3" width="18" height="18" rx="2.5"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>',
    send: '<line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9"/>',
    facebook: '<path d="M14 8.5V7c0-.9.4-1.5 1.5-1.5H17V2.5h-2.5C11.8 2.5 10.5 4 10.5 6.7v1.8H8v3.2h2.5v9.8H14v-9.8h2.6l.4-3.2z"/>',
    instagram: '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><line x1="17.5" y1="6.5" x2="17.5" y2="6.5"/>',
    tiktok: '<path d="M15 3v10.5a3.5 3.5 0 1 1-3-3.46"/><path d="M15 6.2A5.2 5.2 0 0 0 20 8.5"/>',
}

/* Devuelve un <svg> con el icono pedido. */
function icon(nombre) {
    const path = ICONS[nombre]

    if (!path) {
        return ""
    }

    return `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">${path}</svg>`
}

/* Escapa texto que viene de los datos. */
function esc(texto) {
    return String(texto)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
}

/* Iniciales para el avatar de los testimonios. */
function iniciales(nombre) {
    return nombre
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map((parte) => parte.charAt(0).toUpperCase())
        .join("")
}

/* --------------------------------------------------------------------------
   Plantillas
   -------------------------------------------------------------------------- */

function tplStat(stat) {
    return `
        <div class="stat">
            <span class="stat-value">${esc(stat.value)}</span>
            <span class="stat-label">${esc(stat.label)}</span>
        </div>
    `
}

function tplProblema(item) {
    return `
        <article class="card card-hover card-problem reveal">
            <span class="icon-box icon-box-danger">${icon(item.icono)}</span>
            <div class="card-text">
                <h3>${esc(item.titulo)}</h3>
                <p>${esc(item.texto)}</p>
            </div>
        </article>
    `
}

function tplBeneficio(item) {
    return `
        <article class="card card-hover benefit-card reveal">
            <span class="icon-box">${icon(item.icono)}</span>
            <div class="card-text">
                <h3>${esc(item.titulo)}</h3>
                <p>${esc(item.texto)}</p>
            </div>
        </article>
    `
}

function tplAudiencia(item) {
    return `
        <article class="card card-hover audience-card reveal">
            <span class="icon-box">${icon(item.icono)}</span>
            <div class="card-text">
                <h3>${esc(item.titulo)}</h3>
                <p>${esc(item.texto)}</p>
            </div>
        </article>
    `
}

function tplFeature(item) {
    const pronto = item.estado === "pronto"

    return `
        <article class="feature${pronto ? " feature-soon" : ""}">
            <span class="feature-icon">${icon(item.icono)}</span>
            <div class="feature-text">
                <div class="feature-head">
                    <h3>${esc(item.titulo)}</h3>
                    ${pronto ? '<span class="badge badge-accent">Próximamente</span>' : ""}
                </div>
                <p>${esc(item.texto)}</p>
            </div>
        </article>
    `
}

function tplPlan(plan) {
    const incluye = plan.incluye.map((linea) => `<li>${esc(linea)}</li>`).join("")

    return `
        <article class="plan${plan.destacado ? " plan-featured" : ""} reveal">
            <div class="plan-head">
                <div class="plan-name">
                    <h3>${esc(plan.nombre)}</h3>
                    ${plan.destacado ? '<span class="badge badge-brand">Recomendado</span>' : ""}
                </div>
                <div class="plan-tagline">
                    <p>${esc(plan.tagline)}</p>
                </div>
            </div>
            <div class="plan-price">
                <span class="plan-amount">${esc(plan.precio)}</span>
                <span class="plan-period">${esc(plan.periodo)}</span>
            </div>
            <div class="check-list plan-features${plan.destacado ? "" : " check-list-muted"}">
                <ul>
                    ${incluye}
                </ul>
            </div>
            <div class="btn btn-block ${plan.destacado ? "btn-brand" : "btn-outline"}">
                <a href="#">${esc(plan.cta)}</a>
            </div>
            <div class="plan-note">
                <p>${esc(plan.nota)}</p>
            </div>
        </article>
    `
}

function tplTestimonio(item) {
    return `
        <article class="card quote-card reveal">
            <blockquote class="quote-text">
                <p>“${esc(item.texto)}”</p>
            </blockquote>
            <div class="quote-author">
                <span class="avatar">${esc(iniciales(item.nombre))}</span>
                <div class="card-text">
                    <h4>${esc(item.nombre)}</h4>
                    <p>${esc(item.negocio)}</p>
                </div>
            </div>
        </article>
    `
}

function tplFaq(item) {
    return `
        <details class="faq-item">
            <summary>${esc(item.pregunta)}</summary>
            <div class="faq-answer">
                <p>${esc(item.respuesta)}</p>
            </div>
        </details>
    `
}

/* JSON-LD de las preguntas frecuentes, armado con los mismos datos. */
function tplFaqSchema(faq) {
    return JSON.stringify({
        "@context": "https://schema.org",
        "@type": "FAQPage",
        mainEntity: faq.map((item) => ({
            "@type": "Question",
            name: item.pregunta,
            acceptedAnswer: { "@type": "Answer", text: item.respuesta },
        })),
    })
}
