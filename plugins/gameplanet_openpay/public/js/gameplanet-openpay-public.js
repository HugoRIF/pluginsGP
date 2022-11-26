(function($) {
    'use strict';
    $(document).ready(function (){
      //solo cuando estemos en el checkout
      if( $("#gp_openpay_form").length){
        iniciarGpOpenPay();
      }
    });
    function iniciarGpOpenPay(){
      OpenPay.setId(gp_openpay.id);
      OpenPay.setApiKey(gp_openpay.key);
      OpenPay.setSandboxMode(true);
      const deviceSessionId = OpenPay.deviceData.setup('checkout');
      const formulario =$('form.checkout');
      $("#device_session_id").val(deviceSessionId);

      /**
       * Click en submit
       */
      $(document).on('click', 'form.checkout button:submit', function () {
        console.log('submit del checkout');
        //eliminamos los errores del formulario 
        $('label.error').hide();
        $('input.error').removeClass('error');
        //eliminamos el token si es que existe
  
        //verificamos que el metodo de pago sea open pay
          const payment_method = $('input[name=payment_method]:checked').val();
          //si es openpay continuamos
          if(payment_method === 'openpay_gp'){
            return iniciaPagoOpenPay();
          }
      });

      function iniciaPagoOpenPay(){
        console.log('pago con tarjeta utilizando openpay');
        //desactivamos el formulario
        gp_openpay_bloquearFormulario();
        //validacion de formulario
        const validate = gp_openpay_validate();
        if(!validate){
          // no es valido marcamos el error y mandamos false para evitar submit
          formulario.unblock();
          try {
            document.getElementById("order_review").scrollIntoView(true);
          } catch (error) {
            //no funciona el scroll
          }
          return false;
        }
        //obtnemos el token de la tarjeta
       
        //siempre regresa false el encargado de hacer el submit es el callback del success
        return false;
      }

      //Limpiamos el nombre
      $(document).on('input','#openpay-holder-name',function(event) {
        $('#openpay-holder-name_error').hide();
        $('#openpay-holder-name').removeClass('error');
        const value  = $(this).val().toUpperCase().replace(/[^A-ZÁÉÍÓÚÑ.,/\s]/g, '');
        $(this).val(value);
      })
      //formateo de credencial
      $(document).on('input','#openpay-card-number',function(event) {
        $('#openpay-card-number_error').hide();
        $(this).removeClass('error');
        const value  = $(this).val().replace(/[^0-9]/g, '').replace(/(.{4})/g, '$1 ').trim();
        $(this).val(value);
      })
      //Escuchamos para ver que tipo de tarjeta es
      $(document).on('keyup','#openpay-card-number',function(event) {
        const value = $(this).val().replace(' ','').trim();
        if(value.length == 8){
          gp_openpay_getCardType(value);
        }
      })
      //formateo de fecha de expiracion
      $(document).on('input','#openpay-card-expiry_month',function(event) {
        $('#openpay-card-expiry_error').hide();
        $(this).removeClass('error');
        const value  = $(this).val().replace(/[^0-9]/g, '').trim();
        $(this).val(value);
      
      })
      $(document).on('input','#openpay-card-expiry_year',function(event) {
        $('#openpay-card-expiry_error').hide();
        $(this).removeClass('error');
        const value  = $(this).val().replace(/[^0-9]/g, '').trim();
        $(this).val(value);
      
      })
      //formateo de CVC
      $(document).on('input','#openpay-card-cvc',function(event) {
        $('#openpay-card-cvc_error').hide();
        $(this).removeClass('error');
        const value  = $(this).val().replace(/[^0-9]/g, '').trim();
        $(this).val(value);
      
      })

      /**
       * Valida los campos del formulario
       * 
       * Si alguno falla se rompe todo y manda el error
       * 
       * @return 
       */
      function gp_openpay_validate(){
        //letras, acentos y expeciales
        const pattern_letters = new RegExp('^[A-ZÁÉÍÓÚÑ,. ]+$','i');
        const pattern_numeric = new RegExp('^[0-9 ]+$','i');
        let success = true;
        //Nombre no vacio y valido
        if ($('#openpay-holder-name').val().length < 1 || !pattern_letters.test($('#openpay-holder-name').val())) {
          gp_openpay_error_callback({data:{error_code:1}});
          success =  false;
        }
        //Numero de tarjeta
        const card_number = $('#openpay-card-number').val().replace(' ','').trim();
        if (card_number.length < 16 || !pattern_numeric.test(card_number)) {
          gp_openpay_error_callback({data:{error_code:2}});
          success =  false;
        }
        //Expiracion 
        const hoy = new Date();
        const exp_m = $('#openpay-card-expiry_month').val().replace(' ','').trim();
        const exp_y = $('#openpay-card-expiry_year').val().replace(' ','').trim();
        if (exp_m.length < 2 || parseInt(exp_m) > 12 || parseInt(exp_m) < 1) {
          gp_openpay_error_callback({data:{error_code:3,message:'El mes de expiración es invalido',type:'month'}});
          success =  false;
        }
        if (exp_y.length < 2 || parseInt('20'+exp_y) < parseInt(hoy.getFullYear())) {
          gp_openpay_error_callback({data:{error_code:3,message:'El año de expiración es invalido',type:'year'}});
          success =  false;
        }
        // CVV no vacio y valido
        if ( $('#openpay-card-cvc').val().length < 3  || !pattern_numeric.test($('#openpay-card-cvc').val())) {
          gp_openpay_error_callback({data:{error_code:4}});
          success =  false;
        }
        return success;
      }

      function gp_openpay_error_callback(response){
        const {data} = response;
        switch (data.error_code) {
          case 1://Nombre
            $('#openpay-holder-name_error').text('El nombre del titular de la tarjeta no fue proporcionado o tiene un formato inválido.');
            $('#openpay-holder-name_error').show();
            $('#openpay-holder-name').addClass('error');
            break;
          case 2://Tarjeta
            $('#openpay-card-number_error').text('El Número de tarjeta no fue proporcionado o es invalido.');
            $('#openpay-card-number_error').show();
            $('#openpay-card-number').addClass('error');
            break;
          case 3://Expiracion
            $('#openpay-card-expiry_error').text(data.message);
            $('#openpay-card-expiry_error').show();
            $('#openpay-card-expiry_'+data.type).addClass('error');
            break;
          case 4://cvv
            $('#openpay-card-cvc_error').text('El código de seguridad de la tarjeta (CVV2) no fue proporcionado.');
            $('#openpay-card-cvc_error').show();
            $('#openpay-card-cvc').addClass('error');
            break;
          default:
            break;
        }
       
      }
      /**
       * Callback cuando se pudo generar el token de openpay
       * 
       * se envia el formulario para crear la orden
       * 
       */
      function gp_openpay_cb_token_success(){
        //agreganos el token de openpay al formulario
        //se hace el submit
      }


     /**
      * Callback cuando se NO pudo generar el token de openpay
      * 
      * Enivamos el mensaje de error, se guarda la informacion y el log de por que no se logro 
      */
      function gp_openpay_cb_token_fail(){
        //mostramos los errores
        //guardamos el log del evento
      } 

      /**
       * Solo bloqueamos pero ocupa mucho espacio esto
       */
      function gp_openpay_bloquearFormulario(){
        formulario.block({
          blockMsgClass: 'gp_openpay_progress-wrapper',
          message:`
            <div>
              <span class="loader-general blue "></span>
              <p>Espera un momento</p>
            </div>
          `,
          overlayCSS:{
            backgroundColor:'rgb(230,230,230)',
            zIndex:11
          }
        });
      }

      /**
       * Se consulta en el back que tipo de tarjeta es
       * 
       * 
       * Si es de credito y esta activos los meses se activan los meses
       * Si es de debito no tendria que pasar nada (esperemos)
       * Si es NULL se marca una alerta 
       * 
       * @param {string} cardBin primeros 8 digitos de la tarjeta ingresada
       */
      function gp_openpay_getCardType(cardBin){
        console.log('obtenemos tipo de tarjeta',gp_openpay_ajax_param);
         //se envia al back
         const data = new FormData();
         data.append('action',gp_openpay_ajax_param.action_getcard);
         data.append('card',cardBin);
        
         const request = new XMLHttpRequest();
         request.open('POST',gp_openpay_ajax_param.ajaxurl,true);
         //callback - resultado de la peticion
         request.onload = function(){
           const response = JSON.parse(this.response);
           console.log("Se recibe del back",response);
          
         }
         request.onerror = function(){
           console.log("Algo salio mal en la peticion",this.error);
         }
        
         request.send(data);
      }
    }
   
})(jQuery);