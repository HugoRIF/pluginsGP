(function($) {
    'use strict';
    $(function(){
      if( $("#facturacion_plugin_container").length){
        iniciarPluginFacturacion();
      }
    })
    function iniciarPluginFacturacion(){
      console.log("inicia plugin facturacion");

      $("#facturacion_form").validate({
        ignore: ".ignore",
        rules: {
          facturacion_rfc:{
            required: true,
            minlength:12
          },
          facturacion_razon_social:{
            required: true,
            maxlength:255
          },
          facturacion_cp:{
            required: true,
            maxlength:5,
            minlength:5,
            number:true
          },
          facturacion_regimen:{
            required: true,
          },
          facturacion_cfdi:{
            required: true,
          },
          facturacion_ticket:{
            required: true,
            minlength:21,
            maxlength:21,
            number:true
          },
          facturacion_total:{
            required: true,
            number:true
          },
          hiddenRecaptcha: {
              required: function () {
                  if (grecaptcha.getResponse() == '') {
                      return true;
                  } else {
                      return false;
                  }
              }
          }
        },
        messages : {
          facturacion_rfc: {
            required: "El RFC es obligatorio.",
            minlength: "Por favor ingresa un RFC valido"
          },
          facturacion_razon_social: {
            required: "La Razón Social es obligatoria.",
            maxlength: "La Razón Social es desmasiado larga",
          },
          facturacion_cp: {
            required: "El Código Postal es obligatorio.",
            maxlength: "Por favor ingresa un Código Postal valido.",
            minlength: "Por favor ingresa un Código Postal valido.",
            number: "Por favor ingresa un Código Postal valido.",
          },
          facturacion_regimen: {
            required: "El Régimen Fiscal es obligatorio.",

          },
          facturacion_cfdi: {
            required: "El Uso del CFDI es obligatorio.",

          },
          facturacion_ticket: {
            required: "El Número de ticket es obligatorio.",
            number:"El Número de ticket son solo digitos",
            minlength:"El Número de ticket no es valido (21 digitos)",
            maxlength:"El Número de ticket no es valido (21 digitos)",
          },

          facturacion_total: {
            required: "El Monto Total es obligatorio.",
            number: "El total no es una cantidad valida",

          },
          hiddenRecaptcha:{
            required:"Por favor completa el Captcha"
          }
        }
      });

      $(document).on( 'change', '#facturacion_regimen', function(){
        const currentIdRegimen = $("#facturacion_regimen").val();
        console.log("Cambio el regimen");
        //agregamos las opciones de uso
        $("#facturacion_cfdi").attr('disabled',false);
        $("#facturacion_cfdi").html('');
        try {
          console.log("Regimen Fiscal", currentIdRegimen);
          console.log("Catalogo", regimenCatalogo);
          const currentRegimen = regimenCatalogo.filter(regimen => regimen.id_regimen === currentIdRegimen);
      
          console.log("Usos del regimen", currentRegimen[0].usos);
          const usos = currentRegimen[0].usos;
          $("#facturacion_cfdi").append(`
            <option value=""></option>
          `);
          usos.forEach(uso => {
            $("#facturacion_cfdi").append(`
              <option value="${uso.id_uso}">${uso.descripcion}</option>
            `);
          });
        } catch (error) {
          console.log("error ",error)
          $("#facturacion_cfdi").attr('disabled',true);
          
        }

      });

      $(document).on( 'input', '#facturacion_cp', function(){
        this.value = this.value.replace(/[^0-9]/g, '');
      })
      $(document).on( 'input', '#facturacion_ticket', function(){
        this.value = this.value.replace(/[^0-9]/g, '');
      })
      $(document).on( 'input', '#facturacion_total', function(){
        this.value = this.value.replace(/[^0-9.]/g, '');
      });
      
      $(document).on( 'click', '#copy_button-button', function(){
        console.log("se copia el link");
        /* Select the text field */
          const texto = $("#factura_link").text();
          console.log(texto);
          /* Copy the text inside the text field */
          navigator.clipboard.writeText(texto);
          alert("enlace copiado")
      });

      $(document).on( 'click', '#plugin_submit_btn', function(e){
        e.preventDefault();
        setLoadingButtonGenral('#plugin_submit_btn','#facturacion_form');
        $('#facturacion_form').submit();

      })

      //SUBMIT DEL FORMULARIO
      $(document).on('submit', '#facturacion_form', function (e) {
        console.log("Se envia el formulario facturacion");
        e.preventDefault();
        $("#response_factura_container").hide();
       
        const params = $('#facturacion_form').serializeArray();
        
        const data = new FormData();
        console.log("Se envia la peticion",params);
        data.append('action',facturacion_ajax_param.action);
        data.append('facturacion_rfc',params[0].value);
        data.append('facturacion_razon_social',params[1].value);
        data.append('facturacion_cp',params[2].value);
        data.append('facturacion_regimen',params[3].value);
        data.append('facturacion_cfdi',params[4].value);
        data.append('facturacion_ticket',params[5].value);
        data.append('facturacion_total',params[6].value);
        data.append('g-recaptcha-response',params[7].value);

        //Peticion al backend NO AJAX
        
        const request = new XMLHttpRequest();
        request.open('POST',facturacion_ajax_param.ajaxurl,true);
        //callback - resultado de la peticion
        request.onload = function(){
          const response = JSON.parse(this.response);
          console.log(response);
          grecaptcha.reset();
          setActiveButtonGenral('#plugin_submit_btn','#facturacion_form');
          $("#response_factura_container").show();

          if(response.success){
            $('#form_factura_container').hide();
          }
          $('#response_factura_wrapper').html(response.content)
        }
        request.onerror = function(){
          console.log("Algo salio mal en la peticion",this.error);
        }

        //se envia la peticion
        request.send(data);
      });
    }
   

})(jQuery);


function factRecaptchaCallback() {
  var response = grecaptcha.getResponse();
  jQuery("#hidden-grecaptcha").val(response);
  jQuery("#facturacion_form").valid();
    
};
