
(function($) {
    'use strict';
    let map;
    let currentLocation = {lat:19.4326077, lng:-99.13320799999997};
    var isMobile = window.matchMedia("only screen and (max-width: 800px)");
    const defaultLat = {lat:19.4326077, lng:-99.13320799999997};
    let markerUser;
    let infoWindow;
    let userInfoWindow;

    $(function(){
      if( $("#gp_cash_plugin_container").length){
       
        iniciarGPCash();
      }
    })
    function iniciarGPCash(){
      console.log("inicia plugin gp_cash");
      
      const cashOnUbicacionConcedida = ubicacion => {
  
        console.log("Tengo la ubicación: ", ubicacion);
        const userUbication = new google.maps.LatLng(ubicacion.coords.latitude, ubicacion.coords.longitude);
        console.log("Ubicacion usuario: ", userUbication);

        map.setCenter(userUbication);
        markerUser = new google.maps.Marker({
          position: userUbication,
          draggable: false,
        });
        markerUser.setMap(map);
     
        obtnerserSucursalesCashCercanas('oxxo',userUbication);
          userInfoWindow = new google.maps.InfoWindow({content:`<div style="padding:1em">Estas aquí</div>`});
          userInfoWindow.open({
            anchor: markerUser,
            map,
            shouldFocus: false,
    
          });
          google.maps.event.addListener(markerUser, "click", () => {
          
            userInfoWindow.open({
              anchor: markerUser,
              map,
              shouldFocus: false,
  
            });
          });
      }
      const cashOnErrorDeUbicacion = err => {
        console.log("Error obteniendo ubicación: ", err);
        markerUser.setMap(map);
        //aqui le mando OXXO hardocodeado la primera vez si queremos mostarr otra se debe hacer aqui
        userInfoWindow = new google.maps.InfoWindow({content:''});
        userInfoWindow.setContent(`
            <div style="padding:1em">
            Estas aquí
            </div>
          `);
        userInfoWindow.open({
          anchor: markerUser,
          map,
          shouldFocus: false,
  
        });
        google.maps.event.addListener(markerUser, "click", () => {
          
          userInfoWindow.open({
            anchor: markerUser,
            map,
            shouldFocus: false,

          });
        });
      }
      if(!isMobile.matches){
        GPCashInitMap();

      }
      $(".js-range-slider").ionRangeSlider({
        skin:'round',
        min: 0,
        max: 5000,
        from: 500,
        from_min: 50,
        grid: true,
        grid_num:5,
        step:50,
        prettify_separator:",",
        prefix:'$ ',
      });
      $("#image_cb img").imgCheckbox({
        graySelected:false,
        scaleSelected:false,
        radio:true,
        preselect:[0],
        
        onclick:function(el){
          var isChecked = el.hasClass("imgChked"),
          imgEl = el.children()[0];  // the img element
          $("#gp_cash_payment_selected").text(imgEl.id);
        }
      });
      
      $(document).on( 'click', '#plugin_submit_btn', function(e){
        e.preventDefault();
        setLoadingButtonGenral('#plugin_submit_btn','#gp_cash_form');
        $('#gp_cash_form').submit();
      })

      //SUBMIT DEL FORMULARIO
      $(document).on('submit', '#gp_cash_form', function (e) {
        console.log("Se envia el formulario");
        e.preventDefault();
        $("#response_content").hide();
        $("#gp_cash_result_container").html(`<span class="loader-general blue"></span>`);
        const paramsRAW = $('#gp_cash_form').serializeArray();
        console.log(paramsRAW);
      
        const params = new FormData();
        params.append('action',gp_cash_ajax_param.action);
        params.append('creditos',paramsRAW[0].value);
        params.append('payment_method',paramsRAW[1].name);
        crearOrdenGPCash(params);
      });

      /**Tal vez seria mejor hacer una funcion global en algun lado */
      function crearOrdenGPCash(params){
        //Peticion al backend NO AJAX
                
        const request = new XMLHttpRequest();
        request.open('POST',gp_cash_ajax_param.ajaxurl,true);
        //callback - resultado de la peticion
        request.onload = function(){
          const response = JSON.parse(this.response);
          console.log("respuesta del back",response);
          
          if(response.success){
            
           
            //Si todo esta bien mostramos el resultado
            $("#gp_cash_main_wrapper").html(response.content)
          }
          else{
            console.log("mostramos el error")
            $("#response_content_wrapper").html(response.content);
            $("#response_content").show();
          }
          //Activamos el boton
          setActiveButtonGenral('#plugin_submit_btn','#gp_cash_form');

        }
        request.onerror = function(){
          console.log("Algo salio mal en la peticion",this.error);
          setActiveButtonGenral('#plugin_submit_btn','#gp_cash_form');
        }
        //se envia la peticion
        request.send(params);
      }


      function GPCashInitMap(){
        console.log("inicia el mapa")
        //inicializamos el mapa solo para pintarlo
        map = new google.maps.Map(document.getElementById("mapa_sucursales_cash"), {
          center: defaultLat,
          zoom: 15,
          mapTypeId: google.maps.MapTypeId.ROADMAP,
          streetViewControl: false,
          mapTypeControl: false,
          fullscreenControl: false,
          estriction: {
            latLngBounds: {
                north: 33,
                south: 14,
                east: -85,
                west: -120,
            }
        }
        });
       
        //vamos por la ubicacion actual del usuario
        const opcionesDeSolicitud = {
          enableHighAccuracy: true,
          timeout: 20000,
          maximumAge: 0
        };
        markerUser = new google.maps.Marker({
          position: defaultLat,
          draggable: false,
        });
       
        
  
        if (!"geolocation" in navigator) {
          //se inicial con la ubicacion por default
          console.log("no se puede acceder a la ubicacion en el navegador")
        }
        else{
          //buscamos la ubicacion del usuario
          navigator.geolocation.getCurrentPosition(cashOnUbicacionConcedida, cashOnErrorDeUbicacion, opcionesDeSolicitud);
        }
        console.log("mapa listo");
        infoWindow = new google.maps.InfoWindow({content:''});
          
        //aqui le mando OXXO hardocodeado la primera vez si queremos mostarr otra se debe hacer aqui
        map.addListener("center_changed", () => {
          setTimeout(() => {
            const cords=map.getCenter();
          obtnerserSucursalesCashCercanas('OXXO',cords);
          }, 1500);
          
        });
      
     
        
      }

      /**
       * BUSca las tiendas mas cerccanas en un radio de 5km, falta hacer esto dinamico ahorita como solo es oxxo pues no hya problema
       * @param {string} sucursal 
       * @param {object} ubicacion 
       */
      function obtnerserSucursalesCashCercanas(sucursal,ubicacion){
        const request = {
          keyword : sucursal,
          radius: 2000,
          location: ubicacion,
        };
      
        const service = new google.maps.places.PlacesService(map);
      
        service.nearbySearch(request, function(results, status) {
          if (status === google.maps.places.PlacesServiceStatus.OK) {
            for (var i = 0; i < results.length; i++) {
              createMarker(results[i]);
            }
            //map.setCenter(results[0].geometry.location);
          }
        });
      }
      function createMarker(place) {
        if (!place.geometry || !place.geometry.location) return;
      
        const markerAux = new google.maps.Marker({
          map,
          position: place.geometry.location,
        });
      
        google.maps.event.addListener(markerAux, "click", () => {
          infoWindow.setContent(`
            <div style="padding:1em">
              ${place.name || ""}
            </div>
          `);
          infoWindow.open({
            anchor: markerAux,
            map,
            shouldFocus: false,

          });
        });
      }
      $(document).on( 'click', '#crd_lin_warning_login', function(){
        $("#crd_lin_warning_login").click();
      });
    }
   
})(jQuery);

