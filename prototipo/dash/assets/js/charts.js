/* ==========================================================================
   CHARTS — gráficos del dashboard con datos simulados (Chart.js)
   Se redibujan al cambiar de tema para tomar la paleta nueva.
   ========================================================================== */

(function () {
    "use strict";

    if (typeof Chart === "undefined") {
        return;
    }

    var instances = [];

    function color(name) {
        return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    }

    function build(canvasId, config) {
        var canvas = document.getElementById(canvasId);

        if (!canvas) {
            return;
        }

        instances.push(new Chart(canvas, config));
    }

    function render() {
        instances.forEach(function (chart) {
            chart.destroy();
        });

        instances = [];

        var PRIMARY = color("--color-chart-1");
        var ACCENT = color("--color-chart-2");
        var GREEN = color("--color-chart-3");
        var ROSE = color("--color-chart-4");
        var GRID = color("--color-chart-grid");
        var TEXT_SOFT = color("--color-text-soft");
        var SURFACE = color("--color-surface");
        var ACCENT_SOFT = color("--color-accent-soft");

        Chart.defaults.font.family = '"Segoe UI", system-ui, -apple-system, Arial, sans-serif';
        Chart.defaults.font.size = 12;
        Chart.defaults.color = TEXT_SOFT;

        var tooltip = {
            backgroundColor: PRIMARY,
            padding: 10,
            cornerRadius: 8,
            displayColors: false,
            titleFont: { weight: "600" }
        };

        /* Visitas por día -------------------------------------------------- */

        build("chart-visitas", {
            type: "line",
            data: {
                labels: ["Lun", "Mar", "Mié", "Jue", "Vie", "Sáb", "Dom"],
                datasets: [{
                    label: "Visitas",
                    data: [128, 165, 142, 210, 264, 318, 287],
                    borderColor: PRIMARY,
                    backgroundColor: ACCENT_SOFT,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                    pointBackgroundColor: SURFACE,
                    pointBorderColor: PRIMARY,
                    pointBorderWidth: 2,
                    pointHoverRadius: 5
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

        /* Productos más vistos --------------------------------------------- */

        build("chart-vistos", {
            type: "bar",
            data: {
                labels: ["Perfume Ámbar", "Gafas Retro", "Zapatilla Urbana", "Camisa Lino", "Kit de Uñas"],
                datasets: [{
                    label: "Vistas",
                    data: [412, 358, 291, 244, 187],
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

        /* Productos compartidos -------------------------------------------- */

        build("chart-compartidos", {
            type: "doughnut",
            data: {
                labels: ["WhatsApp", "Facebook", "Enlace directo", "Instagram"],
                datasets: [{
                    data: [148, 62, 45, 31],
                    backgroundColor: [GREEN, PRIMARY, ACCENT, ROSE],
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

    window.dashRenderCharts = render;
    render();
})();
