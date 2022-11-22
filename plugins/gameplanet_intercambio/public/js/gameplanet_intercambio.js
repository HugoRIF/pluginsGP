(function($) {
    'use strict';
    $(function(){
      if( $("#intercambio_plugin_container").length){
       
        iniciarIntercambio();
      }
    })
    function iniciarIntercambio(){
      console.log("inicia plugin intercambio");
      peticionInicial();
      $("#intercambio_form").validate({
        ignore: ".ignore",
        rules: {
          game_name:{
            required: true,
          },
          /* hiddenRecaptcha: {
              required: function () {
                  if (grecaptcha.getResponse() == '') {
                      return true;
                  } else {
                      return false;
                  }
              }
          } */
        },
        messages : {
          
          game_name: {
            required: "El nombre es requerido",
          },

          
          hiddenRecaptcha:{
            required:"Por favor completa el Captcha"
          }
        }
      });
      
      $(document).on( 'click', '#plugin_submit_btn', function(e){
        e.preventDefault();
        setLoadingButtonGenral('#plugin_submit_btn','#intercambio_form');
        $('#intercambio_form').submit();
      })

      //SUBMIT DEL FORMULARIO
      $(document).on('submit', '#intercambio_form', function (e) {
        console.log("Se envia el formulario");
        e.preventDefault();
        $("#response_content").hide();
        $("#intercambio_result_container").html(`<span class="loader-general blue"></span>`);
        const paramsRAW = $('#intercambio_form').serializeArray();
        const params = new FormData();
        params.append('action',intercambio_ajax_param.action);
        params.append('search',paramsRAW[0].value);
        consultarIntercambio(params);
      });

      function peticionInicial(){
        $("#intercambio_result_container").html(`<span class="loader-general blue"></span>`);
        const params = new FormData();
        params.append('action',intercambio_ajax_param.action);
        params.append('search','DEALS');
        consultarIntercambio(params,true);
      }
      /**Tal vez seria mejor hacer una funcion global en algun lado */
      function consultarIntercambio(params,init=false){
        //Peticion al backend NO AJAX
                
        const request = new XMLHttpRequest();
        request.open('POST',intercambio_ajax_param.ajaxurl,true);
        //callback - resultado de la peticion
        request.onload = function(){
          const response = JSON.parse(this.response);
          console.log("respuesta del back",response);
          
          if(response.success){
            if(!init){
              $("#response_content_wrapper").html(response.alert);
              $("#response_content").show();
            }
           
            //Si todo esta bien mostramos el resultado
            $("#intercambio_result_container").html(response.content)
          }
          else{
            $("#response_content_wrapper").html(response.alert);
            $("#response_content").show();
            //fallo algo entonces se muestra el mensaje se error
            $("#intercambio_result_container").html('')
          }
          //Activamos el boton
          setActiveButtonGenral('#plugin_submit_btn','#intercambio_form');

        }
        request.onerror = function(){
          console.log("Algo salio mal en la peticion",this.error);
          setActiveButtonGenral('#plugin_submit_btn','#intercambio_form');
        }

        //se envia la peticion
     
        request.send(params);
      }
    }
   
})(jQuery);

/* 
function intercambioRecaptchaCallback() {
  var response = grecaptcha.getResponse();
  jQuery("#hidden-grecaptcha").val(response);
  jQuery("#intercambio_form").valid();
    
}; */

