(function($) {
    'use strict';
    $(function(){
      if( $("#preventas_gp_container").length){
       
        iniciarMisPreventas();
      }
    })
    function iniciarMisPreventas(){
      console.log("Inicia plugin  Preventas");
      let listaSucursales = [];
      
      //marcamos la seccion como activa
      const navItems = $(".woocommerce-MyAccount-navigation-link--my-presales");
      for (let index = 0; index < navItems.length; index++) {
        const navItem = navItems[index];
        $(navItem).addClass('active is_active');
      }

      obtenerPreventasGP();
      obtenerTiendasPreventasGP();
      function obtenerPreventasGP(){
        const request = new XMLHttpRequest();
        request.open('POST',gp_preventa_ajax_param.ajaxurl,true);
        //callback - resultado de la peticion
        request.onload = function(){
          const response = JSON.parse(this.response);
        
          //console.log("respuesta del back",response);
          if(response.success){
            $("#preventas_gp_wrapper").html(response.content);
          }
          else{
            $("#preventas_gp_wrapper").html(response.content);

          }
        }
        request.onerror = function(){
          console.log("Algo salio mal en la peticion",this.error);
          $("#preventas_gp_wrapper").html(response.content);

        }

        //se envia la peticion
        const data = new FormData();
        data.append('action',gp_preventa_ajax_param.action);
        request.send(data);
      };
      function obtenerTiendasPreventasGP(){

        let lat = "19.42847",
        lng = "-99.12766";
        if (gpPreventasGetCookie('_gp_geo_lat') && gpPreventasGetCookie('_gp_geo_lng')) {
            lat = gpPreventasGetCookie('_gp_geo_lat');
            lng = gpPreventasGetCookie('_gp_geo_lng');
        }

        const data = new FormData();
        data.append('action', gp_preventa_ajax_param.action_sucursales);
        data.append('lat', lat);
        data.append('lng', lng);
        const request = new XMLHttpRequest();
        request.open('POST', gp_preventa_ajax_param.ajaxurl, true);

        //callback - resultado de la peticion
        request.onload = function() {
          try {
            const response = JSON.parse(this.response);
            //Si todo esta bien mostramos el resultado y se oculta el formulario
            if (typeof response === 'object') {
              //guardamos el arreglo de las sucursales
              listaSucursales = response;
            } 
          } catch (error) {
            console.log('no se pudo obtner las sucursales',error);
          }
            
        }

        // error en respuesta
        request.onerror = function() {
            console.log("Algo salio mal obteniendo las sucursales", this.error);
        }

        //se envia la peticion
  
        request.send(data);
      }
      /**
       * Da click en el boton de ver historial de preventa
       */
      $(document).on( 'click', '.ver_historial_abono', function(){
        //como las preventass e cargan despues da la vista entonces debemos de hacer un boton virtual
        $("#historial-abonos-table-container").html(`
          <div class="col large-12 pb-0 preventas_loading" style="height:auto">
            <span class="loader-general"></span>
          </div>
        `);
        $("#open-modal_preventa_historial_abonos").click();
        const transaction = $(this).attr('transaction');
        console.log('consultamos historial de abonos',transaction);
        //consultamos el historial de abonos
        const request = new XMLHttpRequest();
        request.open('POST',gp_preventa_abonos_ajax_param.ajaxurl,true);
        //callback - resultado de la peticion
        request.onload = function(){
          const response = JSON.parse(this.response);
          console.log("respuesta del back",response);
          $("#historial-abonos-table-container").html(response.content);
         
         
        }
        request.onerror = function(){
          console.log("Algo salio mal en la peticion",this.error);
        }

        //se envia la peticion
        const data = new FormData();
        data.append('action',gp_preventa_abonos_ajax_param.action);
        data.append('transaction',transaction);
        request.send(data);
      });

      /**
       * Da click en el boton de ver historial de preventa
       */
      $(document).on( 'click', '#reasignar_preventa', function(){
        //limpiamos el modal
        const current_id_tienda = $(this).attr('current_id_tienda');
        $('#sucursales_search').val('');
        try {
          $('#prev_asignar_tienda_form')[0].reset();
          $('#preventa_re_id_saldo').val($(this).attr('id_saldo'));
          $('#preventa_re_id_tienda_origen').val(current_id_tienda);
        } catch (error) {
          console.log('Algo salio mal recuperando transaccion', error);
        }

        //abrimos modal
        $("#open-modal_preventa_reasignar_tienda").click();

        //generamos la listal
        reasignar_preventa_generateListaSucursales(current_id_tienda);
        $('.preventas_loading').hide();
        $('#reasignar_tienda_preventa-wrapper').show();
       
      });

      $(document).on( 'click', "#preventa_lista_sucursales .li_tiendas", function (){
        const id_tienda = $(this).attr('id_sucursal');
        $("#radio_tienda_"+id_tienda).click();
      });

      //filtro de tiendas
      $(document).on( 'input', "#sucursales_search", function (){
          const current_value = $(this).val();
          const filtro = $("#sucursales_search_filter").val();
          
          const resultado = listaSucursales.filter(sucursal => sucursal[filtro].toLowerCase().includes(current_value.toLowerCase()));
          $(".radio_content").hide();
          resultado.forEach(item=>{
            $(`#li_tienda_${item.id_tienda}`).show();
          })
      });

      //filtro de tiendas
      $(document).on( 'change', "#sucursales_search_filter", function (){
          $("#sucursales_search").val('');
          $(".radio_content").show();
      });
      /**
       * confirma reasignar tienda
       */
      $(document).on( 'click', "#preventa_asignar_tienda_confirm", function (e){
        e.preventDefault();
        $(this).attr('disabled',true);
        let tienda_origen = 0;
        let tienda_destino = 0;
        const formValue  = $('#prev_asignar_tienda_form').serializeArray();
        const data = new FormData();
        data.append('action', gp_preventa_ajax_param.action_reasignar);

        formValue.forEach(item =>{
          data.append(item.name,item.value);
          if(item.name == 'id_tienda_origen'){
            tienda_origen = item.value;
          }
          if(item.name == 'id_tienda_destino'){
            tienda_destino = item.value;
          }
        })

        //no hacemos nada en la misma tienda
        if(tienda_origen == tienda_destino){
          $(this).attr('disabled',false);
          console.log('es la misma tienda',data);
          return;
        }
        //se manda la peticion
        $(this).html(`<span class="loader-general"></span>`);
        const request = new XMLHttpRequest();
        request.open('POST', gp_preventa_ajax_param.ajaxurl, true);

        //callback - resultado de la peticion
        request.onload = function() {
          try {
            const response = JSON.parse(this.response);
          
            console.log(response);
            window.location.reload();
           
          } catch (error) {
            console.log('no se pudo obtner las sucursales',error);
            $('#preventa_asignar_tienda_confirm').html(`Asignar tienda`);
            $('#preventa_asignar_tienda_confirm').attr('disabled',false);  
          }
          
        }

        // error en respuesta
        request.onerror = function() {
            console.log("Algo salio mal reasignando tiendas", this.error);
            $('#preventa_asignar_tienda_confirm').html(`Asignar tienda`);
            $('#preventa_asignar_tienda_confirm').attr('disabled',false);
        }

        //se envia la peticion
  
        request.send(data);

      });
      function gpPreventasGetCookie(name){
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
      }
      function reasignar_preventa_generateListaSucursales(current_id_tienda){
        let content = '';
        let current_tienda_item = '';
        listaSucursales.forEach((element, index, data) => {
            let distancia = parseFloat(element.distancia);
            distancia = distancia.toFixed(1);

            let telefono = element.telefono;
            telefono = telefono.replace(/-/g, "");
            telefono = telefono.replace(/\(/g, "");
            telefono = telefono.replace(/\)/g, "");
            telefono = telefono.replace(/ /g, "");
            telefono = telefono.substring(0, 2) + " " + telefono.substring(2, 6) + " " + telefono.substring(6, 10);

            let nombre = element.tienda;
            if (nombre.length >= 15) {
                nombre = nombre.replace("Gameplanet", "GP");
            }
            if(element.id_tienda === current_id_tienda){
              current_tienda_item = `
              <div class="radio_content" id="li_tienda_${element.id_tienda}">
              <input class="tienda_radio" type="radio" id="radio_tienda_${element.id_tienda}" name="id_tienda_destino" value="${element.id_tienda}" checked>
  
              <li class="li_tiendas"  id_sucursal="${element.id_tienda}">
                  <span >
                      <div class="row row-collapse align-left">
                          <div class="col medium-1 small-12 large-1"   style="display:flex; align-items:center;">
                            <div class="preventa_reasignar_checkmark-wrapper">
                              <div class="preventa_reasignar_checkmark">
                                <span class="dashicons dashicons-saved"></span>
                              </div>
                              <div class="preventa_reasignar_checkmark_fake"></div>
                            </div>
                          </div>
                          <div class="col medium-9 small-12 large-9">
                              <div class="col-inner" style="padding-right: 1em;">
                                  <h3 style="margin-bottom: 0px;">${nombre}</h3>
                                  <p>${element.direccion}</p>
                                  <p>
                                      <a href="tel:+${telefono}">${telefono}</a>
                                  </p>
                              </div>
                          </div>
                          <div class="col medium-2 small-12 large-2">
                              <div class="col-inner">
                                  <div style="margin-right: 1em;">
                                      <h3 style="margin-bottom: 0px;">${distancia} <span class="gp_color_gris">Km</span></h3>
                                      <p class="gp_fs_p8em">de distancia</p>
                                  </div>
                              </div>
                          </div>
                          
                      </div>
                  </span>
              </li>
  
  
              <hr class="hr_gp">
              </div>
  
              `;
            }
            else{
              content += `
              <div class="radio_content" id="li_tienda_${element.id_tienda}">
              <input class="tienda_radio" type="radio" id="radio_tienda_${element.id_tienda}" name="id_tienda_destino" value="${element.id_tienda}">
  
              <li class="li_tiendas"  id_sucursal="${element.id_tienda}">
                  <span >
                      <div class="row row-collapse align-left">
                          <div class="col medium-1 small-12 large-1"   style="display:flex; align-items:center;">
                            <div class="preventa_reasignar_checkmark-wrapper">
                              <div class="preventa_reasignar_checkmark">
                                <span class="dashicons dashicons-saved"></span>
                              </div>
                              <div class="preventa_reasignar_checkmark_fake"></div>
                            </div>
                          </div>
                          <div class="col medium-9 small-12 large-9">
                              <div class="col-inner" style="padding-right: 1em;">
                                  <h3 style="margin-bottom: 0px;">${nombre}</h3>
                                  <p>${element.direccion}</p>
                                  <p>
                                      <a href="tel:+${telefono}">${telefono}</a>
                                  </p>
                              </div>
                          </div>
                          <div class="col medium-2 small-12 large-2">
                              <div class="col-inner">
                                  <div style="margin-right: 1em;">
                                      <h3 style="margin-bottom: 0px;">${distancia} <span class="gp_color_gris">Km</span></h3>
                                      <p class="gp_fs_p8em">de distancia</p>
                                  </div>
                              </div>
                          </div>
                          
                      </div>
                  </span>
              </li>
  
  
              <hr class="hr_gp">
              </div>
  
              `;
            }
            
        });

        let lista = `<ul>${current_tienda_item}${content}</ul>`;
        $('#preventa_lista_sucursales').html(lista);
      }
    }
   
})(jQuery);



