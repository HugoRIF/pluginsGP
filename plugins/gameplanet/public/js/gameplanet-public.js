(function($) {
    'use strict';

    /**
     * El código JavaScript para las páginas públicas deben
     * estar en este archivo
     *
     * Nota: Se asume que escribirás código jQuery aquí, por lo que la
     * referencia de la función $ se ha preparado para el uso dentro
     * de esta función
     *
     * Esto te permitirá definir "handlers", para cuando tu DOM esté listo:
     *
     * $(function() {
     *
     * });
     *
     * Cuando la ventana ha cargado:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...y/u otras posibilidades.
     *
     * Idealmente, no se considera una buena práctica añadir más de un
     * DOM-ready o window-load handler para una página en particular.
     * Aunque scripts del core de WordPress, Plugins y Temas practican
     * esto, nosotros debemos luchar para marcar un mejor ejemplo
     * en nuestro trabajo.
     */

})(jQuery);

function gp_email() {
    var account_email = document.getElementById("account_email");
    var account_name = document.getElementById("account_display_name");
    if (account_name) {
        account_name.maxLength = 20;
    }
    if (account_email) {
        account_email.readOnly = true;
        account_email.className += " disable-input";
    }
}