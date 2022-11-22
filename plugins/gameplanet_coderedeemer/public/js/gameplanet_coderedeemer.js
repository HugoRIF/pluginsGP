(function($) {
    'use strict';
    $(function(){
      if( $("#coderedeemer_plugin_container").length){
       
        iniciarCodeReddemer();
      }
    })
    function iniciarCodeReddemer(){
      console.log("inicia plugin coderedeemer");

      $("#coderedeemer_form ").validate({
        ignore: ".ignore",
        rules: {
          tickect_number:{
            required: true,
            minlength:21,
            maxlength:21
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
          
          tickect_number: {
            required: "El Número de ticket es obligatorio.",
            number:"El Número de ticket son solo digitos",
            minlength:"El Número de ticket no es valido (21 digitos)",
            maxlength:"El Número de ticket no es valido (21 digitos)",
          },

          
          hiddenRecaptcha:{
            required:"Por favor completa el Captcha"
          }
        }
      });
      
      $(document).on( 'input', '#tickect_number', function(){
        this.value = this.value.replace(/[^0-9]/g, '');
      })
      $(document).on( 'click', '#plugin_submit_btn', function(e){
        e.preventDefault();
        setLoadingButtonGenral('#plugin_submit_btn','#coderedeemer_form');
        $('#coderedeemer_form').submit();
      })

      $(document).on( 'click', '.cdr-ver-mas-button', function(){
        const index = this.attributes.for.value;
        const collapsed = this.attributes.collapsed.value;
        if(collapsed === '1'){
          //mostramos
          $("#button_ver_mas_"+index).attr('collapsed','0');
          $("#button_ver_mas_"+index).html('Ver menos');
          $("#descripcion_"+index).addClass('crd_item-descripcion').removeClass('crd_item-descripcion-collapsed');
        }
        else{
          //acultamos
          $("#button_ver_mas_"+index).attr('collapsed','1');
          $("#button_ver_mas_"+index).html('Ver más');
          $("#descripcion_"+index).addClass('crd_item-descripcion-collapsed').removeClass('crd_item-descripcion');
        }          

      })


      //SUBMIT DEL FORMULARIO
      $(document).on('submit', '#coderedeemer_form', function (e) {
        console.log("Se envia el formulario crd");
        e.preventDefault();
        $("#response_content").hide();
       
        const params = $('#coderedeemer_form').serializeArray();
        console.log("Se envia la peticion",params)
        console.log("Se envia a url",crd_ajax_param.ajaxurl)
        console.log("Se envia a accionurl",crd_ajax_param.action)

        //Peticion al backend NO AJAX
        
        const request = new XMLHttpRequest();
        request.open('POST',crd_ajax_param.ajaxurl,true);
        //callback - resultado de la peticion
        request.onload = function(){
          const response = JSON.parse(this.response);
        
          console.log("respuesta del back",response);
          //Si todo esta bien mostramos el resultado y se oculta el formulario
          $("#response_content_wrapper").html(response.content);
          grecaptcha.reset();
         
          if(response.success){
            $('#coderedeemer_form')[0].reset();
            $("#response_content").show();
            $("#crd_form_container").hide();
          }
          else{
            //fallo algo entonces se muestra el mensaje se error
            $("#response_content").show();
      
            setActiveButtonGenral('#plugin_submit_btn','#coderedeemer_form');
     
          }
        }
        request.onerror = function(){
          console.log("Algo salio mal en la peticion",this.error);
        }

        //se envia la peticion
        const data = new FormData();
        data.append('action',crd_ajax_param.action);
        data.append('nonce',crd_ajax_param.nonce);
        data.append('ticket',params[0].value);
        data.append('g-recaptcha-response',params[1].value);
        request.send(data);
      });



      return true;
    }
    $(document).on( 'click', '#crd_lin_warning_login', function(){
      $("#crd_login_button").click();
    });
    $(document).on( 'click', '.crd_copy_code', function(){
      
      const texto = $('.crd_copy_code').attr('code');
      console.log("Copia codigo",texto);
      navigator.clipboard.writeText(texto);
      alert("Código copiado",texto);
    });
})(jQuery);


function crdRecaptchaCallback() {
  var response = grecaptcha.getResponse();
  jQuery("#hidden-grecaptcha").val(response);
  jQuery("#coderedeemer_form").valid();
    
};

