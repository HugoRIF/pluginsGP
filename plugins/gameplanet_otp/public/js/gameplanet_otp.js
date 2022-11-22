
(function($) {
    'use strict';
    

    $(function(){
      if( $("#gp_otp_billing_container").length){
       
        iniciarGPOTPBilling();
      }
      if( $("#gp_otp_register_container").length){
       
        iniciarGPOTPRegister();
      }


    })
    function iniciarGPOTPBilling(){
      console.log('funciona');
      gp_otp_iniciallizaCodeInput('#gp_otp_code','#gp_otp_verify_button');
      let time_resend = 5;
      let current_number = '';
      let timeResendInterval = null;
      $("#gp_otp_phone_form").validate({
        //ignore: ".ignore",
        rules: {
          gp_otp_phone:{
            required: true,
            maxlength: 10,
            minlength: 10,
            number:true,
          }
        
        },
        messages : {
          gp_otp_phone: {
            required: "Por favor ingresa un teléfono valido",
            maxlength: "Por favor ingresa un teléfono valido",
            minlength: "Por favor ingresa un teléfono valido",
            number: "Por favor ingresa un teléfono valido"
          }
        }
      });
      $(document).on( 'input', '#gp_otp_phone', function(){
        this.value = this.value.replace(/[^0-9]/g, '');
      })
     
      $("#gp_otp_verify_form").validate({
        ignore: ".ignore",
        rules: {
          gp_otp_code:{
            required: true,
            minlength: 6,
            maxlength: 6,
          },
        
        },
        messages : {
          gp_otp_code: {
            required: "Por favor ingresa el código enviado",
            minlength:"Por favor ingresa el código enviado",
            maxlength: "Por favor ingresa el código enviado",
          },
       
        },
        invalidHandler: function(form, validator) {
          console.log('reactivamos inputs');
          $('#gp_otp_code_input').attr('disabled',false);     
        }
      });
      
      
      $(document).on('click','#gp_otp_send_button', function(e){
        console.log('click');
        e.preventDefault();
        setLoadingButtonGenral('#gp_otp_send_button','#gp_otp_phone_form');
        $("#gp_otp_verify_form")[0].reset();
        $("#gp_otp_phone-error").hide();
        $('#gp_otp_phone_form').submit();
      });

      //SUBMIT DEL FORMULARIO SEND
      $(document).on('submit', '#gp_otp_phone_form', function (e) {
        console.log("Se envia SMS");
        e.preventDefault();
        current_number = $("#gp_otp_phone").val();
        console.log('telefono actual',current_number);
        const data = new FormData();
        data.append('action',gp_otp_ajax_param.action_send);
        data.append('phone',current_number);

        //se envia al back
        const request = new XMLHttpRequest();
        request.open('POST',gp_otp_ajax_param.ajaxurl,true);
        //callback - resultado de la peticion
        request.onload = function(){
          const response = JSON.parse(this.response);
          console.log("Se recibe del back",response);
          try {
            clearInterval(timeResendInterval);
          } catch (error) {}


          if(!response.success){
            console.log('NO se puede hacer la peticion');
            //no quiero hacer un switch pero creo que si esto crece es necesario
            if(response.code === 205){
              time_resend = response.data.time_blocked;
              gp_otp_falla_tiempo_render();
              return;

            }
            
            gp_otp_falla_interna_render(response);
            return;
          }
          //se envio el mensaje
          //asignamos el tiempo de espera
          time_resend = response.data.time_blocked;
          gp_otp_peticion_exitosa_1_render();
        }
        request.onerror = function(){
          console.log("Algo salio mal en la peticion",this.error);
        }
       
        request.send(data);

      });

      function gp_otp_falla_tiempo_render(){
        $('#gp_otp_send_again-container').show();
        setActiveButtonGenral('#gp_otp_send_button','#gp_otp_phone_form','Enviar Código');
        $("#gp_otp_send_button").attr('disabled',true);
        $('.otp_time_resend').text(time_resend);
        timeResendInterval = setInterval(function() {
          if(time_resend === 0){
            $("#gp_otp_send_again-container").hide();
            $("#gp_otp_send_button").attr('disabled',false);
            clearInterval(timeResendInterval);
          }
          $('.otp_time_resend').text(time_resend);
          console.log("entro al timer");
          time_resend = time_resend - 1;
          
        }, 1000);
      }
      
      function gp_otp_peticion_exitosa_1_render(){
        $('.otp_time_resend').text(time_resend);
        timeResendInterval = setInterval(function() {
          if(time_resend === 0){
            $('.gp_opt_billing_resend_otp_label').hide();
            $('#gp_opt_billing_resend_otp_button').show();
            clearInterval(timeResendInterval);
          }
          $('.otp_time_resend').text(time_resend);
          time_resend = time_resend - 1;
         
        }, 1000);

        
        //cambia seccion
        $('#gp_otp_current_number').text(current_number);
        $("#gp_otp_phone-wrapper").hide();
        $("#gp_otp_verify-wrapper").show();
        $('#gp_otp_code_first-input').focus();
      };
      function gp_otp_falla_interna_render(response){
        $("#gp_otp_phone-error").text(response.message);
        $("#gp_otp_phone-error").show();
        setActiveButtonGenral('#gp_otp_send_button','#gp_otp_phone_form','Enviar Código');

      };
      $(document).on('click','#gp_otp_verify_button', function(e){
        e.preventDefault();
        console.log("se envia codigo",$("#gp_otp_code").val());
        setLoadingButtonGenral('#gp_otp_verify_button','#gp_otp_verify_form');
        
        $('#gp_otp_verify_form').submit();
        
      });
      //SUBMIT del FORMULARIO VALIDATE
      $(document).on('submit', '#gp_otp_verify_form', function (e) {
        console.log("Se verifica OTP");
        e.preventDefault();
        const code = $("#gp_otp_code").val();
        const data = new FormData();
        data.append('action',gp_otp_ajax_param.action_verify);
        data.append('phone',current_number);
        data.append('code',code);
        //se envia al back
        const request = new XMLHttpRequest();
        request.open('POST',gp_otp_ajax_param.ajaxurl,true);
        //callback - resultado de la peticion
        request.onload = function(){
          try {
            const response = JSON.parse(this.response);
            console.log("Se recibe del back",response);
            //aqui si es succes hacemos el reload
            if(response.success){
              window.location.reload();
              return;
            }
            setActiveButtonGenral('#gp_otp_verify_button','#gp_otp_verify_form','Verificar y Continuar');
            $("#gp_otp_code-error").text(response.message);
            $("#gp_otp_code-error").show();
            $('#gp_otp_code_input').attr('disabled',false);
            
          } catch (error) {
            console.log("Error depsues de recibir respuesta",error);
            setActiveButtonGenral('#gp_otp_verify_button','#gp_otp_verify_form','Verificar y Continuar');

          }
          
        }
        request.onerror = function(){
          console.log("Algo salio mal en la peticion verify",this.error);
          $('#gp_otp_code_input').attr('disabled',false);

        }
       
        request.send(data);
        
      });
      //REENVIAR OTP
      $(document).on('click','#gp_opt_billing_resend_otp_button', function(e){
        e.preventDefault();
        $(this).attr('disabled',true);
        $(this).html(`<span class="loader-general blue"></span>`);
        console.log("se renvia el otp",current_number);
        const data = new FormData();
        data.append('action',gp_otp_ajax_param.action_send);
        data.append('phone',current_number);

        //se envia al back
        const request = new XMLHttpRequest();
        request.open('POST',gp_otp_ajax_param.ajaxurl,true);
        //callback - resultado de la peticion
        request.onload = function(){
          const response = JSON.parse(this.response);
          console.log("Se recibe del back",response);
          try {
            clearInterval(timeResendInterval);
          } catch (error) {}


          if(!response.success){
            console.log('NO se puede hacer la peticion');
            //no quiero hacer un switch pero creo que si esto crece es necesario
            if(response.code === 205){
              time_resend = response.data.time_blocked;
              alert('Todavia no puedes reenviar.');
              return;

            }
            alert(response.message);
            return;
          }
          //se envio el mensaje
          //asignamos el tiempo de espera
          time_resend = response.data.time_blocked;
          gp_otp_peticion_exitosa_2_render();
        }
        request.onerror = function(){
          console.log("Algo salio mal en la peticion",this.error);
        }
       
        request.send(data);

      });
      function gp_otp_peticion_exitosa_2_render(){
        $('.otp_time_resend').text(time_resend);
        $('.gp_opt_billing_resend_otp_label').show();
        $('#gp_opt_billing_resend_otp_button').hide();
        timeResendInterval = setInterval(function() {
          if(time_resend === 0){
            $('.gp_opt_billing_resend_otp_label').hide();
            $('#gp_opt_billing_resend_otp_button').show();
            $('#gp_opt_billing_resend_otp_button').html('Enviar de Nuevo SMS');
            $('#gp_opt_billing_resend_otp_button').attr('disabled',false);
            clearInterval(timeResendInterval);
          }
          $('.otp_time_resend').text(time_resend);
          time_resend = time_resend - 1;
         
        }, 1000);
       
      };
      //INGRESAR DE NUEVO TELEFONO
      $(document).on('click','#gp_opt_billing_change_phone', function(e){
        e.preventDefault();
        console.log("se regresa al input del telefono");
        current_number ='';
        setActiveButtonGenral('#gp_otp_send_button','#gp_otp_phone_form','Enviar Código');
        $(".otp_time_resend").text('-');
        $("#gp_otp_phone").val('');
        $('.gp_opt_billing_resend_otp_label').show();
        $("#gp_otp_verify-wrapper").hide();
        $("#gp_otp_phone-wrapper").show();
        $('#gp_opt_billing_resend_otp_button').hide();

        try {
          clearInterval(timeResendInterval);
        } catch (error) {}
      });
    }

    function iniciarGPOTPRegister(){
      let time_resend = 0;
      let current_number = '';
      let current_email = '';
      let timeResendInterval = null;
      gp_otp_iniciallizaCodeInput('#gp_otp_phone_code');
      $(document).on( 'input', '#gp_otp_phone', function(){
        this.value = this.value.replace(/[^0-9]/g, '');
        $('#gp_otp_phone-error').hide();
        $('#gp_otp_phone-success').hide();

      })

      $(document).on('click','#gp_otp_send_button', function(e){
        e.preventDefault();
        //cachamos le vacio
        current_number = $("#gp_otp_phone").val();
        current_email = $("#reg_email").val();
        //validamos antes de enviar
        if(current_email === ''){
          $('#gp_otp_phone-error').text('Porfavor primero ingresa los datos anteriores');
          $('#gp_otp_phone-error').show();
          return;
        }
        if(current_number === '' || current_number.length != 10){
          $('#gp_otp_phone-error').text('Número de teléfono invalido');
          $('#gp_otp_phone-error').show();
          return;
        }

        $(this).html(`<span class="loader-general"></span>`);
        $(this).attr('disabled',true);
      
        console.log('Se envia codigo');
        const data = new FormData();
        data.append('action',gp_otp_register_ajax_param.action_send);
        data.append('phone',current_number);
        data.append('email',current_email);
        try {
          clearInterval(timeResendInterval);
        } catch (error) {}

        //se envia al back
        const request = new XMLHttpRequest();
        request.open('POST',gp_otp_register_ajax_param.ajaxurl,true);
        //callback - resultado de la peticion
        request.onload = function(){
          const response = JSON.parse(this.response);
          console.log("Se recibe del back",response);
          time_resend = response.data.time_blocked;
          if(!response.success){
            console.log('NO se puede hacer la peticion');
            gp_otp_reg_fail_envio(response)
            return;
          }
          //se envio el mensaje
          //asignamos el tiempo de espera
          gp_opt_reg_mensaje_enviado(response)

        }
        request.onerror = function(){
          console.log("Algo salio mal en la peticion",this.error);
        }
       
        request.send(data);
      });

      function gp_opt_reg_mensaje_enviado(response){
        $("#gp_otp_phone-error").hide();
        const response_message = $("#gp_otp_phone-success");
        response_message.html(response.message)
        response_message.show();
        //se bloquea el boton por un tiempo
        timeResendInterval = setInterval(function() {
          
          $('#gp_otp_send_button').html(` Reenviar en ${time_resend} segundos`);
          time_resend = time_resend - 1;
          if(time_resend === 0){
            $("#gp_otp_send_button").html('Enviar código de verificación');
            $("#gp_otp_send_button").attr('disabled',false);
            clearInterval(timeResendInterval);
          }
        }, 1000);
        

      }
      function gp_otp_reg_fail_envio(response){
        //no quiero hacer un switch pero creo que si esto crece es necesario
        $("#gp_otp_phone-success").hide();
        const response_message = $("#gp_otp_phone-error");
        response_message.html(response.message)
        response_message.show();

        if(response.code === 205){
          //se bloquea el boton por un tiempo
          timeResendInterval = setInterval(function() {
            $('#gp_otp_send_button').html(` Reenviar en ${time_resend} segundos`);
            time_resend = time_resend - 1;
            if(time_resend === 0){
              response_message.hide();
              $("#gp_otp_send_button").html('Enviar código de verificación');
              $("#gp_otp_send_button").attr('disabled',false);
              clearInterval(timeResendInterval);
            }
          }, 1000);
          return;
        }
        $("#gp_otp_send_button").html('Enviar código de verificación');
        $("#gp_otp_send_button").attr('disabled',false);
        return;
      }
    }
    
    function gp_otp_iniciallizaCodeInput(input_code,submit_button = ''){


      const inputElements = [...document.querySelectorAll('input.code-input')]

      inputElements.forEach((ele,index)=>{
        ele.addEventListener('focus',(e)=>{
          e.target.select();
        })
        ele.addEventListener('keydown',(e)=>{
          // if the keycode is backspace & the current field is empty
          // focus the input before the current. Then the event happens
          // which will clear the "before" input box.
          if(e.keyCode === 8 && e.target.value==='') inputElements[Math.max(0,index-1)].focus()
        })
        ele.addEventListener('input',(e)=>{
          // take the first character of the input
          // but I'm willing to overlook insane security code practices.
          const [first,...rest] = e.target.value
          e.target.value = first ?? '' // first will be undefined when backspace was entered, so set the input to ""
          const lastInputBox = index===inputElements.length-1
          const didInsertContent = first!==undefined
          if(didInsertContent && !lastInputBox) {
            // continue to input the rest of the string

              inputElements[index+1].focus()
              inputElements[index+1].value = rest.join('')
              inputElements[index+1].dispatchEvent(new Event('input'))

          }
          const current_value=inputElements.map(({value})=>value).join('');
          $(input_code).val(current_value);
          if(submit_button !== ''){
            //si ya esta lleno y el ultimo input hacemos el submit
            if(lastInputBox && current_value.length === 6){
              //desactivamos el input
              $('#gp_otp_code_input').attr('disabled',true);
              console.log('hacemos el submit');
              $(submit_button).click();
            }
          }
         
        })
      })

    }

})(jQuery);

