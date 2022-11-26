
(function($) {
    'use strict';
    $(document).ready(function () {
      console.log(data_report);

      $('#example').DataTable({
        data:data_report,
        columns: [
          { data: 'ip'},
          { data: 'resend_attends' },
          { data: 'time_blocked' },
          { data: 'created_at' },
          { data: 'updated_at' }
        ],
        info:false,
        lengthChange:false,
        language:{
          search:'Buscar:',
        }
      });
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

