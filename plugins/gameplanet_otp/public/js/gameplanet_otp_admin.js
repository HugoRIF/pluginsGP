
(function($) {
    'use strict';
    $(document).ready(function () {
      console.log("start");
      $('#example').DataTable();
      });
  $("#report_filter").val('ip');//seteamos siempre a ip cuando se actualiza todo
  $(document).on('change','#report_filter', function(e){
    e.preventDefault();
    console.log("cambio el filtor por",$(this).val());
  })
  $(document).on('click','#reload_report', function(e){
    e.preventDefault();
    console.log("Recarga reporte");
  })
})(jQuery);

