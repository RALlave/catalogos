<!-- Maqueta del prototipo: esta pantalla todavía no tiene backend. -->
<template>
        <div class="page-header">
            <div class="page-title">
                <h1>Moderación</h1>
                <p>Contenido reportado por visitantes y por el sistema</p>
            </div>

            <div class="page-actions">
                <button class="btn btn-outline" type="button">Ver historial</button>
            </div>
        </div>

        <section class="stat-grid">
            <article class="stat">
                <div class="stat-top">
                    <div class="stat-label">Pendientes</div>
                    <span class="stat-icon is-accent">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 4 2 20h20L12 4Z" />
                            <path d="M12 10v4" />
                            <path d="M12 17h.01" />
                        </svg>
                    </span>
                </div>
                <div class="stat-bottom">
                    <div class="stat-value">8</div>
                    <span class="stat-delta is-up">+3 hoy</span>
                </div>
            </article>

            <article class="stat">
                <div class="stat-top">
                    <div class="stat-label">Resueltos este mes</div>
                </div>
                <div class="stat-bottom">
                    <div class="stat-value">54</div>
                </div>
            </article>

            <article class="stat">
                <div class="stat-top">
                    <div class="stat-label">Tiendas suspendidas</div>
                </div>
                <div class="stat-bottom">
                    <div class="stat-value">38</div>
                </div>
            </article>

            <article class="stat">
                <div class="stat-top">
                    <div class="stat-label">Tiempo medio de respuesta</div>
                </div>
                <div class="stat-bottom">
                    <div class="stat-value">6 h</div>
                    <span class="stat-delta is-up">–2 h</span>
                </div>
            </article>
        </section>

        <section class="panel-grid">
            <article class="card">
                <header class="card-header">
                    <div class="card-title">
                        <h2>Cola de moderación</h2>
                        <p>Lo más antiguo primero</p>
                    </div>
                    <select class="select" aria-label="Filtrar por motivo">
                        <option>Todos los motivos</option>
                        <option>Contenido inapropiado</option>
                        <option>Producto falsificado</option>
                        <option>Spam</option>
                        <option>Otro</option>
                    </select>
                </header>

                <div class="card-body is-flush">
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Contenido</th>
                                    <th>Tienda</th>
                                    <th>Motivo</th>
                                    <th>Reportes</th>
                                    <th>Fecha</th>
                                    <th><span class="visually-hidden">Acciones</span></th>
                                </tr>
                            </thead>
                            <tbody>

                                <tr>
                                    <td>
                                        <div class="table-cell">
                                            <img class="thumb" src="/img/1e62ae41aac2041a531652e044d726d2.jpg" alt="Producto reportado">
                                            <span class="table-cell-text">
                                                <strong>iPhone 15 Pro réplica AAA</strong>
                                                <span>Producto</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td>Todo Celu</td>
                                    <td><span class="badge badge-warning">Falsificado</span></td>
                                    <td>6</td>
                                    <td>Hace 2 días</td>
                                    <td>
                                        <div class="table-actions">
                                            <button class="btn btn-ghost btn-sm" type="button">Ver</button>
                                            <button class="btn btn-outline btn-sm" type="button">Descartar</button>
                                            <button class="btn btn-danger btn-sm" type="button">Quitar</button>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <div class="table-cell">
                                            <img class="thumb" src="/img/25a5c7c085e06f8e5801e0fe7d8a9d45.jpg" alt="Producto reportado">
                                            <span class="table-cell-text">
                                                <strong>Kit gel importado sin sellos</strong>
                                                <span>Producto</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td>Luz Beauty</td>
                                    <td><span class="badge badge-danger">Inapropiado</span></td>
                                    <td>4</td>
                                    <td>Hace 1 día</td>
                                    <td>
                                        <div class="table-actions">
                                            <button class="btn btn-ghost btn-sm" type="button">Ver</button>
                                            <button class="btn btn-outline btn-sm" type="button">Descartar</button>
                                            <button class="btn btn-danger btn-sm" type="button">Quitar</button>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <div class="table-cell">
                                            <span class="avatar avatar-sm">PR</span>
                                            <span class="table-cell-text">
                                                <strong>Promos Rápidas</strong>
                                                <span>Tienda completa</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td>Promos Rápidas</td>
                                    <td><span class="badge">Spam</span></td>
                                    <td>11</td>
                                    <td>Hace 22 horas</td>
                                    <td>
                                        <div class="table-actions">
                                            <button class="btn btn-ghost btn-sm" type="button">Ver</button>
                                            <button class="btn btn-outline btn-sm" type="button">Descartar</button>
                                            <button class="btn btn-danger btn-sm" type="button">Suspender</button>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <div class="table-cell">
                                            <img class="thumb" src="/img/5ed50abd7c25dfda3b8a88692b277757.jpg" alt="Producto reportado">
                                            <span class="table-cell-text">
                                                <strong>Gafas marca premium 90 % off</strong>
                                                <span>Producto</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td>Bazar Norte</td>
                                    <td><span class="badge badge-warning">Falsificado</span></td>
                                    <td>3</td>
                                    <td>Hace 14 horas</td>
                                    <td>
                                        <div class="table-actions">
                                            <button class="btn btn-ghost btn-sm" type="button">Ver</button>
                                            <button class="btn btn-outline btn-sm" type="button">Descartar</button>
                                            <button class="btn btn-danger btn-sm" type="button">Quitar</button>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <div class="table-cell">
                                            <img class="thumb" src="/img/2592623453a33ff3f7b3d8b89e3d2cea.jpg" alt="Producto reportado">
                                            <span class="table-cell-text">
                                                <strong>Remera con logo de club</strong>
                                                <span>Producto</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td>Nómade</td>
                                    <td><span class="badge">Otro</span></td>
                                    <td>1</td>
                                    <td>Hace 5 horas</td>
                                    <td>
                                        <div class="table-actions">
                                            <button class="btn btn-ghost btn-sm" type="button">Ver</button>
                                            <button class="btn btn-outline btn-sm" type="button">Descartar</button>
                                            <button class="btn btn-danger btn-sm" type="button">Quitar</button>
                                        </div>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="page-info">Mostrando 5 de 8 pendientes</div>
                    <button class="btn btn-outline btn-sm" type="button">Ver todos</button>
                </div>
            </article>

            <article class="card">
                <header class="card-header">
                    <div class="card-title">
                        <h2>Motivos</h2>
                        <p>32 reportes del mes</p>
                    </div>
                </header>
                <div class="card-body">
                    <div class="chart-box">
                        <canvas id="chart-reportes"></canvas>
                    </div>
                </div>
            </article>
        </section>

        <section class="panel-grid is-even">
            <article class="card">
                <header class="card-header">
                    <div class="card-title">
                        <h2>Últimas resoluciones</h2>
                    </div>
                </header>

                <div class="card-body is-flush">
                    <div class="list">
                        <ul>
                            <li>
                                <span class="check-mark is-done">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m4 12 6 6L20 6" />
                                    </svg>
                                </span>
                                <span class="list-main">
                                    <strong>Todo Celu suspendida</strong>
                                    <span>Reincidencia en productos falsificados · Hace 3 días</span>
                                </span>
                                <span class="list-meta">
                                    <span class="badge badge-danger badge-dot">Suspendida</span>
                                </span>
                            </li>
                            <li>
                                <span class="check-mark is-done">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m4 12 6 6L20 6" />
                                    </svg>
                                </span>
                                <span class="list-main">
                                    <strong>Reporte descartado en Kaya Deco</strong>
                                    <span>El producto no infringe las normas · Hace 4 días</span>
                                </span>
                                <span class="list-meta">
                                    <span class="badge badge-success badge-dot">Descartado</span>
                                </span>
                            </li>
                            <li>
                                <span class="check-mark is-done">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m4 12 6 6L20 6" />
                                    </svg>
                                </span>
                                <span class="list-main">
                                    <strong>2 productos quitados en Vera Joyas</strong>
                                    <span>Imágenes de terceros sin permiso · Hace 6 días</span>
                                </span>
                                <span class="list-meta">
                                    <span class="badge badge-warning badge-dot">Quitados</span>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </article>

            <article class="card">
                <header class="card-header">
                    <div class="card-title">
                        <h2>Reglas automáticas</h2>
                        <p>Qué revisa el sistema solo</p>
                    </div>
                </header>

                <div class="card-body">
                    <label class="switch">
                        <input type="checkbox" checked>
                        <span>Detectar palabras prohibidas en títulos</span>
                    </label>

                    <label class="switch">
                        <input type="checkbox" checked>
                        <span>Marcar tiendas con más de 5 reportes</span>
                    </label>

                    <label class="switch">
                        <input type="checkbox">
                        <span>Suspender automáticamente tras 10 reportes</span>
                    </label>

                    <hr>

                    <div class="field-hint">
                        <p>Las suspensiones automáticas se pueden revertir desde la ficha de la tienda.</p>
                    </div>
                </div>
            </article>
        </section>
</template>
