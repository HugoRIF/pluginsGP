(function($){
  'use strict';

  $(document).on('click','#open-order-tracking',function(){
    const ticket = $(this).attr('item-ticket');
    const shipping = $(this).attr('item-shipping');
    const orderStatus = $(this).attr('order-status');
    const orderDate = $(this).attr('order-date_create');
    
    console.log(shipping)
    $("#order-tracking-wrapper").html(`
      <div class="loader-container">
        <span class="loader-general"></span>
      </div>
    `);
    
    if(ticket !== 'Pendiente' && ticket !== ''){
      console.log("consulatmos al tecaking");
      const params = new FormData();
      params.append('action',gp_order_tracking.action);
      params.append('ticket',ticket);
  
      const request = new XMLHttpRequest();
      request.open('POST',gp_order_tracking.ajaxurl,true);
      //callback - resultado de la peticion
      request.onload = function(){
        const response = JSON.parse(this.response);
        console.log("ObPeticion Exitosa");

        if(response.success){
          console.log("Obtuvimos informacion del tracking");

          const data = response.data;
          data.shipping = JSON.parse(shipping);
          //data es un array [success,message,list] donde list son los eventos
          create_tracking_timeline(data);
        }
        else{
          //fallo la peticion
          $("#order-tracking-wrapper").html(`
          <p> NO se encontraron eventos, intenta más tarde</p>
        `);
        }
      }
      request.onerror = function(){
        console.log("Algo salio mal en la peticion",this.error);
        //fallo la peticion
        $("#order-tracking-wrapper").html(`
        <p> NO se encontraron eventos, intenta más tarde</p>
      `);
      }
      //se envia la peticion
      request.send(params);
    } 
    else{
      console.log("ticket",ticket);
      console.log("status orden",orderStatus);
      const fakeResponse = {
        success:true,
        list:[{
          "id": "33416005",
          "ticket": "700424075166386593501",
          "id_cliente": "1561402",
          "nombre_evento": "Pedido Creado",
          "fecha_evento":orderDate,
          "id_evento": "10",
          "notificado": null,
          "lugar": "MX, CDMX, Cuajimalpa",
          "nota": null,
          "extra": null,
          "estatus": "ejecutado",
          "acciones_informativas": null
        }],
        summary:{
          "evento": "Pedido Creado",
          "fecha": "2022-09-23 13:06:17",
          "origen": "MX, DF, Cuajimalpa",
          "acciones":null,
          extra:"",
          has_mensajeria:false,
        
        },
        shipping:JSON.parse(shipping)
      };
      create_tracking_timeline(fakeResponse);
    }
   
  })

  function create_tracking_timeline(response){
    if(response.success){
      //hay eventos
      const eventos = response.list;
      const summary = response.summary;
      summary.shipping = response.shipping;
      let content =``;
      //informacion del ultimo evento

      content += create_tracking_lastinfo(summary);
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

  function create_tracking_lastinfo(summary){
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
              <span id='main-address' style='margin-left: 15px'><b>A: </b>  ${summary.shipping.country}, ${summary.shipping.state}, ${summary.shipping.city}, ${summary.shipping.address_1}</span>
            </div>
          </div>
        </div>
      `
    }
    console.log("el resumen del trackin es",content);
    return content;

  }
})(jQuery);