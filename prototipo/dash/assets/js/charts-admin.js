/* ==========================================================================
   CHARTS ADMIN — gráficos del panel de superadministración (datos simulados)
   ========================================================================== */

(function () {
    "use strict";

    if (typeof Chart === "undefined") {
        return;
    }

    function color(name) {
        return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    }

    var PRIMARY = color("--color-chart-1");
    var ACCENT = color("--color-chart-2");
    var WARNING = color("--color-chart-3");
    var DANGER = color("--color-chart-4");
    var GRID = color("--color-chart-grid");
    var TEXT_SOFT = color("--color-text-soft");
    var SURFACE = color("--color-surface");

    Chart.defaults.font.family = '"Segoe UI", system-ui, -apple-system, Arial, sans-serif';
    Chart.defaults.font.size = 12;
    Chart.defaults.color = TEXT_SOFT;

    var tooltip = {
        backgroundColor: color("--color-surface-hover"),
        titleColor: color("--color-text"),
        bodyColor: color("--color-text"),
        padding: 10,
        cornerRadius: 8,
        displayColors: false,
        titleFont: { weight: "600" }
    };

    /* Altas de tiendas por mes --------------------------------------------- */

    var altas = document.getElementById("chart-altas");

    if (altas) {
        new Chart(altas, {
            type: "line",
            data: {
                labels: ["Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago"],
                datasets: [
                    {
                        label: "Tiendas nuevas",
                        data: [42, 68, 91, 124, 158, 203, 247],
                        borderColor: PRIMARY,
                        backgroundColor: "rgba(91, 106, 224, 0.15)",
                        borderWidth: 2,
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                        pointBackgroundColor: SURFACE,
                        pointBorderColor: PRIMARY,
                        pointBorderWidth: 2
                    },
                    {
                        label: "Tiendas publicadas",
                        data: [28, 45, 62, 88, 112, 147, 181],
                        borderColor: ACCENT,
                        borderWidth: 2,
                        borderDash: [5, 4],
                        fill: false,
                        tension: 0.35,
                        pointRadius: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "bottom",
                        labels: {
                            usePointStyle: true,
                            pointStyle: "circle",
                            boxWidth: 8,
                            padding: 14
                        }
                    },
                    tooltip: tooltip
                },
                scales: {
                    x: {
                        grid: { display: false },
                        border: { color: GRID }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: GRID },
                        border: { display: false },
                        ticks: { maxTicksLimit: 5 }
                    }
                }
            }
        });
    }

    /* Distribución por plan ------------------------------------------------ */

    var planes = document.getElementById("chart-planes");

    if (planes) {
        new Chart(planes, {
            type: "doughnut",
            data: {
                labels: ["Gratis", "Emprendedor", "Negocio"],
                datasets: [{
                    data: [1042, 168, 37],
                    backgroundColor: [PRIMARY, ACCENT, WARNING],
                    borderColor: SURFACE,
                    borderWidth: 3,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: "62%",
                plugins: {
                    legend: {
                        position: "bottom",
                        labels: {
                            usePointStyle: true,
                            pointStyle: "circle",
                            boxWidth: 8,
                            padding: 14
                        }
                    },
                    tooltip: tooltip
                }
            }
        });
    }

    /* Tiendas más visitadas ------------------------------------------------ */

    var top = document.getElementById("chart-top-tiendas");

    if (top) {
        new Chart(top, {
            type: "bar",
            data: {
                labels: ["Aroma Sur", "Kaya Deco", "Mi Tienda", "Luz Beauty", "Nómade"],
                datasets: [{
                    label: "Visitas",
                    data: [8420, 6180, 4930, 3710, 2880],
                    backgroundColor: ACCENT,
                    hoverBackgroundColor: PRIMARY,
                    borderRadius: 6,
                    barThickness: 18
                }]
            },
            options: {
                indexAxis: "y",
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: tooltip
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: GRID },
                        border: { display: false },
                        ticks: { maxTicksLimit: 5 }
                    },
                    y: {
                        grid: { display: false },
                        border: { display: false }
                    }
                }
            }
        });
    }

    /* Ingresos mensuales --------------------------------------------------- */

    var ingresos = document.getElementById("chart-ingresos");

    if (ingresos) {
        new Chart(ingresos, {
            type: "bar",
            data: {
                labels: ["Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago"],
                datasets: [{
                    label: "Ingresos",
                    data: [186000, 254000, 341000, 468000, 612000, 798000, 961000],
                    backgroundColor: PRIMARY,
                    hoverBackgroundColor: ACCENT,
                    borderRadius: 6,
                    barThickness: 22
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: tooltip
                },
                scales: {
                    x: {
                        grid: { display: false },
                        border: { color: GRID }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: GRID },
                        border: { display: false },
                        ticks: { maxTicksLimit: 5 }
                    }
                }
            }
        });
    }

    /* Reportes por motivo -------------------------------------------------- */

    var reportes = document.getElementById("chart-reportes");

    if (reportes) {
        new Chart(reportes, {
            type: "doughnut",
            data: {
                labels: ["Contenido inapropiado", "Producto falsificado", "Spam", "Otro"],
                datasets: [{
                    data: [14, 9, 6, 3],
                    backgroundColor: [DANGER, WARNING, PRIMARY, ACCENT],
                    borderColor: SURFACE,
                    borderWidth: 3,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: "62%",
                plugins: {
                    legend: {
                        position: "bottom",
                        labels: {
                            usePointStyle: true,
                            pointStyle: "circle",
                            boxWidth: 8,
                            padding: 14
                        }
                    },
                    tooltip: tooltip
                }
            }
        });
    }
})();
