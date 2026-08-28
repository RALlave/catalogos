/* ============================================================
   TEMA — recuerda la elección del panel de diseño
   prototipo-3

   Va en el <head> y SIN defer a propósito: se ejecuta antes de que
   el navegador pinte, así al recargar no se ve un parpadeo con la
   paleta por defecto antes de aplicar la guardada.

   Es parte del panel de diseño, no del catálogo: cuando se saque el
   panel, se saca también este archivo y su <script> del <head>.
   ============================================================ */

(function () {
    'use strict';

    /* Los valores se validan contra esta lista: lo que venga guardado
       es texto del navegador, no se escribe a ciegas en el <html> */
    var OPTIONS = {
        'data-palette': ['cafe', 'verano', 'primavera', 'oro', 'arcoiris', 'tech',
                        'alegre', 'invierno', 'piel', 'halloween', 'frio', 'noche'],
        'data-radius': ['square', 'round'],
        'data-banner': ['dark', 'light'],
        'data-nav':    ['dark', 'color']
    };

    var PREFIX = 'proto3:';

    /* localStorage puede fallar: ventana privada, cookies bloqueadas,
       o la página abierta con file:// en vez de por el servidor local.
       Si falla, la maqueta sigue funcionando con los valores del HTML. */
    function read(key) {
        try {
            return window.localStorage.getItem(PREFIX + key);
        } catch (e) {
            return null;
        }
    }

    function save(key, value) {
        try {
            window.localStorage.setItem(PREFIX + key, value);
            return true;
        } catch (e) {
            return false;
        }
    }

    function clear() {
        try {
            Object.keys(OPTIONS).forEach(function (attribute) {
                window.localStorage.removeItem(PREFIX + attribute);
            });
            return true;
        } catch (e) {
            return false;
        }
    }

    function applySaved() {
        var root = document.documentElement;
        Object.keys(OPTIONS).forEach(function (attribute) {
            var value = read(attribute);
            if (value && OPTIONS[attribute].indexOf(value) !== -1) {
                root.setAttribute(attribute, value);
            }
        });
    }

    function hasStorage() {
        try {
            var key = PREFIX + 'test';
            window.localStorage.setItem(key, '1');
            window.localStorage.removeItem(key);
            return true;
        } catch (e) {
            return false;
        }
    }

    /* Se expone lo mínimo para que app.js escriba y limpie */
    window.theme = {
        options: OPTIONS,
        save: save,
        clear: clear,
        available: hasStorage()
    };

    applySaved();
})();
