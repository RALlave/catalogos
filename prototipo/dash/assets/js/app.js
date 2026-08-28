/* ==========================================================================
   APP — comportamiento del panel (prototipo, sin backend)
   ========================================================================== */

(function () {
    "use strict";

    var STORAGE_SIDEBAR = "dash.sidebar.collapsed";
    var STORAGE_THEME = "dash.theme";
    var TEXT_INPUTS = ["text", "email", "password", "search", "tel", "url", "number", "date"];

    /* ---------------------------------------------------------------------
       Temas: cacao (por defecto), oceano, grafito
       El tema guardado ya se aplica desde un script inline en el <head>
       para evitar el parpadeo; acá solo queda el cambio en caliente.
       --------------------------------------------------------------------- */

    var themeLocked = document.documentElement.hasAttribute("data-theme-locked");

    function applyTheme(name) {
        if (themeLocked) {
            return;
        }

        if (name && name !== "cacao") {
            document.documentElement.setAttribute("data-theme", name);
        } else {
            document.documentElement.removeAttribute("data-theme");
        }

        window.localStorage.setItem(STORAGE_THEME, name);

        document.querySelectorAll("[data-theme-set]").forEach(function (button) {
            var target = button.closest(".dropdown-item") || button;
            target.classList.toggle("is-current", button.dataset.themeSet === name);
        });

        if (typeof window.dashRenderCharts === "function") {
            window.dashRenderCharts();
        }
    }

    document.querySelectorAll("[data-theme-set]").forEach(function (button) {
        button.addEventListener("click", function () {
            applyTheme(button.dataset.themeSet);
        });
    });

    applyTheme(window.localStorage.getItem(STORAGE_THEME) || "cacao");

    /* ---------------------------------------------------------------------
       Seleccionar todo el texto al hacer clic dentro de un input
       (no aplica a radio, checkbox ni select)
       --------------------------------------------------------------------- */

    function isTextField(el) {
        if (!el) {
            return false;
        }

        if (el.tagName === "TEXTAREA") {
            return true;
        }

        return el.tagName === "INPUT" && TEXT_INPUTS.indexOf(el.type) !== -1;
    }

    document.addEventListener("focusin", function (event) {
        var el = event.target;

        if (!isTextField(el) || el.dataset.selected === "1") {
            return;
        }

        el.dataset.selected = "1";
        window.setTimeout(function () {
            el.select();
        }, 0);
    });

    document.addEventListener("focusout", function (event) {
        if (isTextField(event.target)) {
            delete event.target.dataset.selected;
        }
    });

    /* ---------------------------------------------------------------------
       Sidebar: colapsar en escritorio / off-canvas en móvil
       --------------------------------------------------------------------- */

    var shell = document.querySelector("[data-shell]");

    function isMobile() {
        return window.matchMedia("(max-width: 900px)").matches;
    }

    if (shell) {
        if (window.localStorage.getItem(STORAGE_SIDEBAR) === "1") {
            shell.classList.add("is-collapsed");
        }

        document.querySelectorAll("[data-sidebar-toggle]").forEach(function (button) {
            button.addEventListener("click", function () {
                if (isMobile()) {
                    shell.classList.toggle("is-mobile-open");
                    return;
                }

                var collapsed = shell.classList.toggle("is-collapsed");
                window.localStorage.setItem(STORAGE_SIDEBAR, collapsed ? "1" : "0");
            });
        });

        var overlay = document.querySelector("[data-sidebar-overlay]");

        if (overlay) {
            overlay.addEventListener("click", function () {
                shell.classList.remove("is-mobile-open");
            });
        }
    }

    /* Grupos desplegables del menú lateral */

    document.querySelectorAll("[data-nav-toggle]").forEach(function (button) {
        button.addEventListener("click", function () {
            var branch = button.closest("[data-nav-branch]");

            if (!branch) {
                return;
            }

            var open = branch.classList.toggle("is-open");
            button.setAttribute("aria-expanded", open ? "true" : "false");
        });
    });

    /* ---------------------------------------------------------------------
       Pestañas
       --------------------------------------------------------------------- */

    var tabButtons = document.querySelectorAll("[data-tab]");

    function activateTab(name) {
        tabButtons.forEach(function (button) {
            var item = button.closest(".tab");

            if (item) {
                item.classList.toggle("is-active", button.dataset.tab === name);
            }
        });

        document.querySelectorAll("[data-tab-panel]").forEach(function (panel) {
            panel.classList.toggle("is-active", panel.dataset.tabPanel === name);
        });
    }

    if (tabButtons.length) {
        tabButtons.forEach(function (button) {
            button.addEventListener("click", function () {
                activateTab(button.dataset.tab);

                try {
                    window.history.replaceState(null, "", "#" + button.dataset.tab);
                } catch (error) {
                    window.location.hash = button.dataset.tab;
                }
            });
        });

        var hash = window.location.hash.replace("#", "");

        if (hash) {
            activateTab(hash);
        }
    }

    /* ---------------------------------------------------------------------
       Dropdowns (usuario, notificaciones, acciones)
       --------------------------------------------------------------------- */

    function closeDropdowns(except) {
        document.querySelectorAll("[data-dropdown].is-open").forEach(function (item) {
            if (item !== except) {
                item.classList.remove("is-open");
            }
        });
    }

    document.querySelectorAll("[data-dropdown-toggle]").forEach(function (button) {
        button.addEventListener("click", function (event) {
            event.stopPropagation();

            var dropdown = button.closest("[data-dropdown]");

            if (!dropdown) {
                return;
            }

            closeDropdowns(dropdown);
            var open = dropdown.classList.toggle("is-open");
            button.setAttribute("aria-expanded", open ? "true" : "false");
        });
    });

    document.addEventListener("click", function () {
        closeDropdowns(null);
    });

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            closeDropdowns(null);

            if (shell) {
                shell.classList.remove("is-mobile-open");
            }
        }
    });

    /* ---------------------------------------------------------------------
       Mostrar / ocultar contraseña
       --------------------------------------------------------------------- */

    var EYE_OPEN = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
    var EYE_OFF = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.7 5.1A10.9 10.9 0 0 1 12 5c6.5 0 10 7 10 7a18.4 18.4 0 0 1-2.7 3.7"/><path d="M6.2 6.2A18.5 18.5 0 0 0 2 12s3.5 7 10 7a10.7 10.7 0 0 0 5.1-1.2"/><path d="m2 2 20 20"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>';

    document.querySelectorAll("[data-password-toggle]").forEach(function (button) {
        button.addEventListener("click", function () {
            var input = document.getElementById(button.dataset.passwordToggle);

            if (!input) {
                return;
            }

            var hidden = input.type === "password";
            input.type = hidden ? "text" : "password";
            button.innerHTML = hidden ? EYE_OFF : EYE_OPEN;
            button.setAttribute("aria-label", hidden ? "Ocultar contraseña" : "Mostrar contraseña");
        });
    });

    /* ---------------------------------------------------------------------
       Fortaleza de contraseña
       --------------------------------------------------------------------- */

    var LEVELS = [
        { className: "is-weak", label: "Contraseña débil" },
        { className: "is-medium", label: "Contraseña aceptable" },
        { className: "is-strong", label: "Contraseña segura" }
    ];

    function scorePassword(value) {
        var score = 0;

        if (value.length >= 8) {
            score += 1;
        }

        if (/[A-Z]/.test(value) && /[a-z]/.test(value)) {
            score += 1;
        }

        if (/\d/.test(value)) {
            score += 1;
        }

        if (/[^A-Za-z0-9]/.test(value)) {
            score += 1;
        }

        return score;
    }

    document.querySelectorAll("[data-strength-for]").forEach(function (meter) {
        var input = document.getElementById(meter.dataset.strengthFor);
        var label = meter.querySelector("[data-strength-label]");

        if (!input) {
            return;
        }

        input.addEventListener("input", function () {
            var value = input.value;
            meter.classList.remove("is-weak", "is-medium", "is-strong");

            if (!value) {
                if (label) {
                    label.textContent = "Usá al menos 8 caracteres, con mayúsculas y números";
                }

                return;
            }

            var score = scorePassword(value);
            var level = LEVELS[Math.min(Math.max(score - 1, 0), 2)];
            meter.classList.add(level.className);

            if (label) {
                label.textContent = level.label;
            }
        });
    });

    /* ---------------------------------------------------------------------
       Toasts
       --------------------------------------------------------------------- */

    var ICON_OK = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.1V12a10 10 0 1 1-5.9-9.1"/><path d="m9 11 3 3L22 4"/></svg>';

    function toast(title, message) {
        var stack = document.querySelector("[data-toast-stack]");

        if (!stack) {
            return;
        }

        var node = document.createElement("div");
        node.className = "toast";
        node.innerHTML = ICON_OK +
            '<div class="toast-text"><strong>' + title + "</strong>" +
            (message ? "<span>" + message + "</span>" : "") +
            "</div>";

        stack.appendChild(node);

        window.setTimeout(function () {
            node.remove();
        }, 3200);
    }

    window.dashToast = toast;

    /* ---------------------------------------------------------------------
       Copiar enlace del catálogo
       --------------------------------------------------------------------- */

    document.querySelectorAll("[data-copy]").forEach(function (button) {
        button.addEventListener("click", function () {
            var value = button.dataset.copy;

            if (navigator.clipboard) {
                navigator.clipboard.writeText(value);
            }

            toast("Enlace copiado", value);
        });
    });

    /* ---------------------------------------------------------------------
       Envío simulado de formularios (login, registro)
       --------------------------------------------------------------------- */

    document.querySelectorAll("[data-fake-submit]").forEach(function (form) {
        form.addEventListener("submit", function (event) {
            event.preventDefault();

            var button = form.querySelector('[type="submit"]');

            if (!button) {
                return;
            }

            var original = button.innerHTML;
            button.classList.add("is-loading");
            button.innerHTML = '<span class="btn-loader"></span><span>Procesando…</span>';

            window.setTimeout(function () {
                button.classList.remove("is-loading");
                button.innerHTML = original;

                var target = form.dataset.fakeSubmit;

                if (target) {
                    window.location.href = target;
                }
            }, 900);
        });
    });
})();
