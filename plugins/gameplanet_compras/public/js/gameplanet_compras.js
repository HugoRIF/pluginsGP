(function($) {
    'use strict';
    $(function(){
      if( $("#compras_gp_container").length){
       
        iniciarMisCompras();
      }
    })
    function iniciarMisCompras(){
      console.log("Inicia plugin  Compras");
      //marcamos la seccion como activa
      const navItems = $(".woocommerce-MyAccount-navigation-link--my-shopping");
      console.log(navItems);
      for (let index = 0; index < navItems.length; index++) {
        const navItem = navItems[index];
        $(navItem).addClass('active is_active');
      }
      



      obtenerComprasGP();


      function obtenerComprasGP(){
        const request = new XMLHttpRequest();
        request.open('POST',gp_compra_ajax_param.ajaxurl,true);
        //callback - resultado de la peticion
        request.onload = function(){
          const response = JSON.parse(this.response);
        
          console.log("respuesta del back",response);
          if(response.success){
            $("#compras_gp_wrapper").html(response.content);
          }
          else{
            $("#compras_gp_wrapper").html(response.content);

          }
        }
        request.onerror = function(){
          console.log("Algo salio mal en la peticion",this.error);
          $("#compras_gp_wrapper").html(response.content);

        }

        //se envia la peticion
        const data = new FormData();
        data.append('action',gp_compra_ajax_param.action);
        request.send(data);
      };

       /**
       * Da click en eñ boton de ver historial de preventa
       */
      $(document).on( 'click', '.seguimiento_envio_compra', function(){
        //como las preventass e cargan despues da la vista entonces debemos de hacer un boton virtual
        $("#order-tracking-wrapper").html(`
          <div class="col large-12 pb-0 compras_loading" style="height:auto">
            <span class="loader-general"></span>
          </div>
        `);
        $("#open-seguimiento_envio_compra").click();
        
        const ticket = $(this).attr('ticket');
        const shipping = $(this).attr('shipping');
        const orderDate = $(this).attr('date');
        console.log('Segumineto de envio de',shipping);

        //consultamos el seguimiento de la compra
        const request = new XMLHttpRequest();
        request.open('POST',gp_compra_ajax_param.ajaxurl,true);
        //callback - resultado de la peticion
        request.onload = function(){
          const response = JSON.parse(this.response);
          console.log("respuesta del back",response);
          if(response.success){
            console.log("Obtuvimos informacion del tracking");
  
            const data = response.data;
            data.shipping = JSON.parse(shipping);
            //data es un array [success,message,list] donde list son los eventos
            compra_create_tracking_timeline(data);
          }
          else{
            //fallo la peticion
            $("#order-tracking-wrapper").html(`
            <p> NO se encontraron eventos, intenta más tarde, CODE-404</p>
          `);
          }
          
          
        }
        request.onerror = function(){
          console.log("Algo salio mal en la peticion",this.error);
          $("#order-tracking-wrapper").html(`
          <p> NO se encontraron eventos, intenta más tarde, CODE-500</p>
        `);
        }

        //se envia la peticion
        const data = new FormData();
        data.append('action',gp_compra_ajax_param.action_tracking);
        data.append('ticket',ticket);
        request.send(data);
      });
      
      function compra_create_tracking_timeline(response){
        if(response.success){
          //hay eventos
          const eventos = response.list;
          const summary = response.summary;
          summary.shipping = response.shipping;
          let content =``;
          //informacion del ultimo evento

          content += compra_tracking_lastinfo(summary);
          console.log("eventos del summary",response);
          console.log("eventos del tracking",eventos);
          console.log("direccion de envio",summary.shipping);
          content += `
            <div id='timeline'></div>
            <div class='timeline '>
          `;
          if(eventos.length){
            content += `<ul>`
            let fecha_temp = "";
            eventos.forEach((evento,index) => {
              const fecha_evento_raw = new Date(evento.fecha_evento);
              const date_options = {month:'long',day: 'numeric',year:'numeric'};
              const fecha_evento = fecha_evento_raw.toLocaleDateString('es-MX',date_options);
              const hora_evento = (fecha_evento_raw.getHours().toString().padStart(2, '0') +':'+fecha_evento_raw.getMinutes().toString().padStart(2, '0'));
              if(fecha_evento != fecha_temp) {
                content += `
                  <li id="normal">
                    <div id="main-fecha">
                      <label style="padding-top:3px;">
                        ${fecha_evento}
                      </label>
                    </div>
                  </li>
                `;
                fecha_temp = fecha_evento;
              }

              if(index == 0){
                content += `
                  <li id="normal">
                    <label id="last-hora">
                      ${hora_evento}
                    </label>
                    <div class="event" id="last-event">
                `;
                if(evento.lugar != null){
                  content += `<span id="last-lugar"> ${evento.lugar} </span>`
                }
                else{
                  content += `<span id='last-lugar'>N / D</span>`
                }

                let texto_evento = evento.id_evento +' '+evento.nombre_evento;
                if(evento.nota != null){
                  texto_evento += evento.id_evento + ", " + evento.nota;
                }

                content += `
                    <label id='last-text'>${texto_evento}</label>
                  </div>
                </li>
                `;
              }
              else{
                content += `
                  <li id='normal'>
                    <label id='hora'>
                    ${hora_evento}

                    </label>
                    <div class="event" id='event'>
                `;
                if(evento.lugar != null){
                  content += `<label id='lugar'>${evento.lugar}</label>`;
                }
                else{
                  content += `<span id='lugar'>N / D</span>`;
                }
                
                let texto_evento = evento.id_evento + " " + evento.nombre_evento;

                if(evento.nota != null) {
                    texto_evento +=  ", " + eventos[i].nota;
                }
              
                  content += `<label id='text'>${texto_evento}`;
                  if(evento.id_evento == 50) {
                    let meta=JSON.parse(evento.extra);
                    let meta_=JSON.stringify(meta);
                    content += `<span class="mas-info-event dashicons dashicons-info-outline" title='${ meta_ }'></span>`;
                  }
                  content += `</label>`;
                content += "</div>" +
                    "</li>";
              }
            });
            content += `</ul>`
          }
          content += "</div>";

          $("#order-tracking-wrapper").html(content);
        }
        
        else{
          //no se encontraron eventos
          $("#order-tracking-wrapper").html(`
            <p> NO se encontraron eventos, intenta más tarde</p>
          `);
        }
      
      }

      function compra_tracking_lastinfo(summary){
        let content = "";
    
        if(summary){
          const fecha_evento_raw = new Date(summary.fecha);
          const date_options = {month:'long',day: 'numeric',year:'numeric'};
          const fecha_evento = fecha_evento_raw.toLocaleDateString('es-MX',date_options);
        
          content = `
            <span class='current-event'>${summary.evento}</span>
            <div class='detail-event'>
            `
            if(summary.has_mensajeria){
              content += `
              <p class="mb-0">
                Paquetería <span class='paqueteria-event'>${summary.mensajeria.carrier}</span>
                número de guía:
                <a class='estilo_link' style='margin-left: 5px;' title='Rastrear el envío' href='${summary.mensajeria.link}' target='_blank'> ${summary.mensajeria.tracking_number} <span class="dashicons dashicons-external"></span></a>
              </p>
              `;
            }
          content +=`
            <span class='current-event-date'>${fecha_evento}</span>
              <div id='origen-destino'>
                <div class='direcciones-event' >
                  <span id='main-address'><b>De: </b> ${summary.origen} </span
                  <span id='main-address' style='margin-left: 15px'><b>A: </b>  ${summary.shipping.direccion}</span>
                </div>
              </div>
            </div>
          `
        }
        console.log("el resumen del trackin es",content);
        return content;

      }
    }
   
})(jQuery);



