(function($) {
    'use strict';

    /**
     * El código JavaScript para las páginas de admin deben
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

function gp_config_logs() {
    var e = document.getElementById("gp_logs");
    var logValue = e.value;
    var xmlHttp = new XMLHttpRequest();
    xmlHttp.open("GET", logValue, false);
    xmlHttp.send(null);
    if (xmlHttp.status == 200) {
        document.getElementById('gp_frame_logs').src = logValue;
    } else {
        alert('Log no encontrado');
        document.getElementById('gp_frame_logs').src = '';
    }
}