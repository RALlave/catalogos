<!-- Maqueta del prototipo: esta pantalla todavía no tiene backend. -->
<template>
        <div class="page-header">
            <div class="page-title">
                <h1>Planes y suscripciones</h1>
                <p>Precios, límites y cobros de la plataforma</p>
            </div>

            <div class="page-actions">
                <button class="btn btn-outline" type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 3v12" />
                        <path d="m7 10 5 5 5-5" />
                        <path d="M5 21h14" />
                    </svg>
                    Exportar cobros
                </button>

                <button class="btn btn-primary" type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 5v14" />
                        <path d="M5 12h14" />
                    </svg>
                    Nuevo plan
                </button>
            </div>
        </div>

        <section class="stat-grid">
            <article class="stat">
                <div class="stat-top">
                    <div class="stat-label">Ingreso mensual</div>
                    <span class="stat-icon is-success">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="5" width="20" height="14" rx="2" />
                            <path d="M2 10h20" />
                        </svg>
                    </span>
                </div>
                <div class="stat-bottom">
                    <div class="stat-value">$ 961k</div>
                    <span class="stat-delta is-up">+20 %</span>
                </div>
            </article>

            <article class="stat">
                <div class="stat-top">
                    <div class="stat-label">Suscripciones activas</div>
                </div>
                <div class="stat-bottom">
                    <div class="stat-value">205</div>
                    <span class="stat-delta is-up">+18</span>
                </div>
            </article>

            <article class="stat">
                <div class="stat-top">
                    <div class="stat-label">Conversión a pago</div>
                </div>
                <div class="stat-bottom">
                    <div class="stat-value">16 %</div>
                    <span class="stat-delta is-up">+2 pts</span>
                </div>
            </article>

            <article class="stat">
                <div class="stat-top">
                    <div class="stat-label">Bajas del mes</div>
                </div>
                <div class="stat-bottom">
                    <div class="stat-value">7</div>
                    <span class="stat-delta is-down">3,4 % de churn</span>
                </div>
            </article>
        </section>

        <!-- Planes configurables -->
        <section class="card">
            <header class="card-header">
                <div class="card-title">
                    <h2>Planes disponibles</h2>
                    <p>Lo que ve el emprendedor al querer mejorar su cuenta</p>
                </div>
            </header>

            <div class="card-body">
                <div class="plan-grid">

                    <div class="plan-option">
                        <div class="plan-option-head">
                            <h3>Gratis</h3>
                            <span class="badge badge-success badge-dot">Activo</span>
                        </div>

                        <div class="plan-price">
                            <strong>$ 0</strong>
                            <span>/ mes</span>
                        </div>

                        <div class="plan-features">
                            <ul>
                                <li>
                                    <span class="check-mark is-done">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="m4 12 6 6L20 6" />
                                        </svg>
                                    </span>
                                    50 productos · 10 categorías
                                </li>
                                <li>
                                    <span class="check-mark is-done">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="m4 12 6 6L20 6" />
                                        </svg>
                                    </span>
                                    200 MB de imágenes
                                </li>
                                <li>
                                    <span class="check-mark is-done">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="m4 12 6 6L20 6" />
                                        </svg>
                                    </span>
                                    1.042 tiendas usándolo
                                </li>
                            </ul>
                        </div>

                        <button class="btn btn-outline btn-block" type="button">Editar plan</button>
                    </div>

                    <div class="plan-option is-current">
                        <div class="plan-option-head">
                            <h3>Emprendedor</h3>
                            <span class="badge badge-accent">Más vendido</span>
                        </div>

                        <div class="plan-price">
                            <strong>$ 4.900</strong>
                            <span>/ mes</span>
                        </div>

                        <div class="plan-features">
                            <ul>
                                <li>
                                    <span class="check-mark is-done">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="m4 12 6 6L20 6" />
                                        </svg>
                                    </span>
                                    Productos ilimitados
                                </li>
                                <li>
                                    <span class="check-mark is-done">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="m4 12 6 6L20 6" />
                                        </svg>
                                    </span>
                                    5 GB de imágenes
                                </li>
                                <li>
                                    <span class="check-mark is-done">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="m4 12 6 6L20 6" />
                                        </svg>
                                    </span>
                                    168 tiendas usándolo
                                </li>
                            </ul>
                        </div>

                        <button class="btn btn-outline btn-block" type="button">Editar plan</button>
                    </div>

                    <div class="plan-option">
                        <div class="plan-option-head">
                            <h3>Negocio</h3>
                            <span class="badge badge-success badge-dot">Activo</span>
                        </div>

                        <div class="plan-price">
                            <strong>$ 11.900</strong>
                            <span>/ mes</span>
                        </div>

                        <div class="plan-features">
                            <ul>
                                <li>
                                    <span class="check-mark is-done">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="m4 12 6 6L20 6" />
                                        </svg>
                                    </span>
                                    Dominio propio · varias tiendas
                                </li>
                                <li>
                                    <span class="check-mark is-done">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="m4 12 6 6L20 6" />
                                        </svg>
                                    </span>
                                    20 GB de imágenes
                                </li>
                                <li>
                                    <span class="check-mark is-done">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="m4 12 6 6L20 6" />
                                        </svg>
                                    </span>
                                    37 tiendas usándolo
                                </li>
                            </ul>
                        </div>

                        <button class="btn btn-outline btn-block" type="button">Editar plan</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Cobros -->
        <section class="panel-grid">
            <article class="card">
                <header class="card-header">
                    <div class="card-title">
                        <h2>Últimos cobros</h2>
                        <p>Movimientos de los últimos 7 días</p>
                    </div>
                    <select class="select" aria-label="Filtrar cobros">
                        <option>Todos</option>
                        <option>Aprobados</option>
                        <option>Rechazados</option>
                        <option>Pendientes</option>
                    </select>
                </header>

                <div class="card-body is-flush">
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Tienda</th>
                                    <th>Plan</th>
                                    <th>Monto</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th><span class="visually-hidden">Acciones</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="table-cell">
                                            <span class="avatar avatar-sm">AS</span>
                                            <span class="table-cell-text">
                                                <strong>Aroma Sur</strong>
                                                <span>lucia@aromasur.com</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-accent">Emprendedor</span></td>
                                    <td>
                                        <div class="table-price">$ 4.900</div>
                                    </td>
                                    <td>06/08/2026</td>
                                    <td><span class="badge badge-success badge-dot">Aprobado</span></td>
                                    <td>
                                        <div class="table-actions">
                                            <button class="btn btn-ghost btn-sm" type="button">Comprobante</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="table-cell">
                                            <span class="avatar avatar-sm">BN</span>
                                            <span class="table-cell-text">
                                                <strong>Bazar Norte</strong>
                                                <span>sofia@bazarnorte.com</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-warning">Negocio</span></td>
                                    <td>
                                        <div class="table-price">$ 11.900</div>
                                    </td>
                                    <td>05/08/2026</td>
                                    <td><span class="badge badge-success badge-dot">Aprobado</span></td>
                                    <td>
                                        <div class="table-actions">
                                            <button class="btn btn-ghost btn-sm" type="button">Comprobante</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="table-cell">
                                            <span class="avatar avatar-sm">VJ</span>
                                            <span class="table-cell-text">
                                                <strong>Vera Joyas</strong>
                                                <span>ana@verajoyas.com</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-accent">Emprendedor</span></td>
                                    <td>
                                        <div class="table-price">$ 4.900</div>
                                    </td>
                                    <td>05/08/2026</td>
                                    <td><span class="badge badge-danger badge-dot">Rechazado</span></td>
                                    <td>
                                        <div class="table-actions">
                                            <button class="btn btn-outline btn-sm" type="button">Reintentar</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="table-cell">
                                            <span class="avatar avatar-sm">NM</span>
                                            <span class="table-cell-text">
                                                <strong>Nómade</strong>
                                                <span>hola@nomade.ar</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-accent">Emprendedor</span></td>
                                    <td>
                                        <div class="table-price">$ 4.900</div>
                                    </td>
                                    <td>03/08/2026</td>
                                    <td><span class="badge badge-warning badge-dot">Pendiente</span></td>
                                    <td>
                                        <div class="table-actions">
                                            <button class="btn btn-ghost btn-sm" type="button">Ver</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="table-cell">
                                            <span class="avatar avatar-sm">CS</span>
                                            <span class="table-cell-text">
                                                <strong>Casa Sur</strong>
                                                <span>info@casasur.com</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-warning">Negocio</span></td>
                                    <td>
                                        <div class="table-price">$ 11.900</div>
                                    </td>
                                    <td>01/08/2026</td>
                                    <td><span class="badge badge-success badge-dot">Aprobado</span></td>
                                    <td>
                                        <div class="table-actions">
                                            <button class="btn btn-ghost btn-sm" type="button">Comprobante</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="page-info">Mostrando 5 de 218 cobros del mes</div>
                    <button class="btn btn-outline btn-sm" type="button">Ver todos</button>
                </div>
            </article>

            <article class="card">
                <header class="card-header">
                    <div class="card-title">
                        <h2>Reparto de ingresos</h2>
                        <p>Sobre $ 961k del mes</p>
                    </div>
                </header>

                <div class="card-body">
                    <div class="usage">
                        <div class="usage-item">
                            <div class="usage-top">
                                <strong>Emprendedor</strong>
                                <span>$ 823k · 168 tiendas</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" style="width: 86%"></div>
                            </div>
                        </div>

                        <div class="usage-item">
                            <div class="usage-top">
                                <strong>Negocio</strong>
                                <span>$ 138k · 37 tiendas</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" style="width: 14%"></div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="list">
                        <ul>
                            <li>
                                <span class="list-main">
                                    <strong>Cobros aprobados</strong>
                                    <span>Este mes</span>
                                </span>
                                <span class="list-meta">211</span>
                            </li>
                            <li>
                                <span class="list-main">
                                    <strong>Rechazados</strong>
                                    <span>Reintentan en 48 h</span>
                                </span>
                                <span class="list-meta">5</span>
                            </li>
                            <li>
                                <span class="list-main">
                                    <strong>Pendientes</strong>
                                    <span>Esperando acreditación</span>
                                </span>
                                <span class="list-meta">2</span>
                            </li>
                        </ul>
                    </div>

                    <div class="alert alert-warning">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 4 2 20h20L12 4Z" />
                            <path d="M12 10v4" />
                            <path d="M12 17h.01" />
                        </svg>
                        <div class="alert-body">
                            <strong>2 pagos rechazados hoy</strong>
                            <span>Si fallan tres veces, la tienda vuelve al plan Gratis.</span>
                        </div>
                    </div>
                </div>
            </article>
        </section>
</template>
