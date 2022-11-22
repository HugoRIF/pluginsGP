(function($) {
    'use strict';

    let map;
    let markerUser;
    let markers = [];
    let dataShops = [];
    let autocomplete;
    let infoWindow;
    let currentLocation = {lat:19.4326077, lng:-99.13320799999997};
    var isMobile = window.matchMedia("only screen and (max-width: 800px)");

    const defaultLat = {lat:19.4326077, lng:-99.13320799999997};
    $(function(){
      if( $("#mapa_plugin_container").length){
        console.log("inicia Plugin Mapa");
        iniciarPluginMapa();
      }
    })
 
    function iniciarPluginMapa() {
      $('#search_input').val('');
      if(!isMobile.matches){
        PMinitMap();

      }
      else{
        PMinitOnlyList();
      }

      $("#search_input").ready(function() {

        PMinitAutoComplete();
      });
      
    }

    const onUbicacionConcedida = ubicacion => {
      quitarMarkers();
      $('#map-shadow').show();
      $('#list-shadow').show();

      console.log("Tengo la ubicación: ", ubicacion);
      const userUbication = new google.maps.LatLng(ubicacion.coords.latitude, ubicacion.coords.longitude);
      map.setCenter(userUbication);
      markerUser = new google.maps.Marker({
        position: userUbication,
        draggable: false,
      });
      markerUser.setMap(map);
      getSucursales(ubicacion.coords.latitude,ubicacion.coords.longitude);
    }
    const onErrorDeUbicacion = err => {
      console.log("Error obteniendo ubicación: ", err);
      markerUser.setMap(map);
      getSucursales();
    }
    const onUbicacionConcedidaList = ubicacion => {
      $('#list-shadow').show();
      console.log("Tengo la ubicación: ", ubicacion);
      getSucursales(ubicacion.coords.latitude,ubicacion.coords.longitude);
    }
    const onErrorDeUbicacionList = err => {
      console.log("Error obteniendo ubicación: ", err);
      getSucursales();
    }
    function PMinitMap(){
      console.log("inicia el mapa")
      //inicializamos el mapa solo para pintarlo
      map = new google.maps.Map(document.getElementById("mapa_sucursales"), {
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
      infoWindow = new google.maps.InfoWindow({content:''});
      infoWindow.addListener('closeclick', ()=>{
        $(".item-active").removeClass("item-active");
      })
      //vamos por la ubicacion actual del usuario
      const opcionesDeSolicitud = {
        enableHighAccuracy: true,
        timeout: 2000,
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
        navigator.geolocation.getCurrentPosition(onUbicacionConcedida, onErrorDeUbicacion, opcionesDeSolicitud);
      }
      console.log("mapa listo");
    }
    function PMinitOnlyList(){
     
      //vamos por la ubicacion actual del usuario
      const opcionesDeSolicitud = {
        enableHighAccuracy: true,
        timeout: 2000,
        maximumAge: 0
      };

      if (!"geolocation" in navigator) {
        //se inicial con la ubicacion por default
        console.log("no se puede acceder a la ubicacion en el navegador")
      }
      else{
        //buscamos la ubicacion del usuario
        navigator.geolocation.getCurrentPosition(onUbicacionConcedidaList, onErrorDeUbicacionList, opcionesDeSolicitud);
      }
      console.log("solo lista listo");
    }
    function PMinitAutoComplete(){
      const options = {
        componentRestrictions: { country: "mx" },
      };
      var input = document.getElementById('search_input');

        autocomplete = new google.maps.places.Autocomplete(input, options);
        autocomplete.addListener('place_changed', handleChangePlace);
    }
   
    function handleChangePlace(){

     

      const place = autocomplete.getPlace();
      console.log("cambia el input",place.place_id);
      if(place.place_id === undefined){//nop hacemos nada si esta vacio
        return true;
      }
      $('#list-shadow').show();
      const lat = place.geometry.location.lat(),
      lng = place.geometry.location.lng();
      if(!isMobile.matches){
        quitarMarkers();
        $('#map-shadow').show();
      
        const userUbication = new google.maps.LatLng(lat, lng);
        map.setCenter(userUbication);
        markerUser = new google.maps.Marker({
          position: userUbication,
          draggable: false,
        });
       markerUser.setMap(map);
      }
      currentLocation={lat:lat,lng:lng};
      getSucursales(lat,lng);
    }
    function quitarMarkers(){
      markerUser.setMap(null);
      markers.forEach(mark=>{
        mark.setMap(null);
      })
    }
    function generateInfoWindow(item){
      if(item !== null || item !== undefined){
        return `
        <div class="info_window-container ">
          <div class="info_window_top-bar"></div>
          <div class="tab">
            <button class="tablinks active" id="tab_datos">Datos Principales</button>
            <button class="tablinks" id="tab_horarios">Horarios</button>
            <button class="tablinks" id="tab_galeria" style="display:none">Galeria</button>
          </div>
          <div id="datos" class="tabcontent" style="display:block">
            <h3>${item.tienda}</h3>
            <p>
              <span class="gp_status gp_status_${parseInt(item.tipo_operacion) === 1?'verde':'rojo'}">${item.operacion}</span>
              <br>
              <h4>Dirección:</h4> ${item.direccion} 
              <br>
              <br>
              <strong>Telelfono:</strong> ${item.telefono} 
              
            </p>
          </div>

          <div id="horarios" class="tabcontent">
            <h3>${item.tienda}</h3><br>
            <ul>
            ${
              item.horarios && item.horarios.map(horario=>{
                return `
                <li>
                  <h4>${horario.txt_rango_dia}:</h4> <p>${horario.txt_rango_hora}</p>
                </li>
                `;
              }).flat().join('')
            }
          </ul>
          </div>

          <div id="galeria" class="tabcontent">
            ${
              item.media && item.media.map(media=>{
                if(media.tipo === "imagen"){
                  return `
                  <div class="shop_media_marco">
                    <img class="shop_media" src="https://bibliotecario.s3.amazonaws.com/${media.media_ruta}">
                  </div>
                  `;
                }
                else{
                  return '';
                }
              })
            }
            
          </div>
          <div id="button_info-container">
            <a href="https://maps.google.com/?q=${item.lat},${item.lng}" target="_blank" class=" gp_status gp_status_azul">Cómo Llegar</a>
          </div>
        </div>
        `;
      }
    }
    $(document).on( 'click', '.tablinks', function(){
      var i, tabcontent, tablinks;
      const city =  this.id.split('tab_')[1];
      // Get all elements with class="tabcontent" and hide them
      $(".tabcontent").css('display','none');
    
      // Get all elements with class="tablinks" and remove the class "active"

      tablinks = document.getElementsByClassName("tablinks");
      for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
      }
    
      // Show the current tab, and add an "active" class to the button that opened the tab
      document.getElementById(city).style.display = "block";
      this.classList.add("active");
    })
  
    function generateListaTiendas(data){
      let html = ``;
      if(data.length){
        data.forEach((item,index) =>{
          let image = "https://bibliotecario.s3.amazonaws.com/comercial/imagenes_tiendas/globo-gameplanet.png";
          if(item.tienda.includes('Gamers')){
            image = "https://bibliotecario.s3.amazonaws.com/comercial/imagenes_tiendas/globo-gamers.png";
          }
          html = html+`
            <li>
              <div class="list_item-container" id="shop_item_${index}">
                <div class="list_item-inner-container">
                  <div class="list_item_img-container" >
                    <img class="list_item-img" src="${image}"></img>
                  </div>
                  <div class="list_item_info-container">
                    <h4>${item.tienda}</h4>
                    <p class="list_item_info_text"><span class="gp_status gp_status_${parseInt(item.tipo_operacion) === 1?'verde':'rojo'}">${item.operacion}</span></p>
                    <p class="list_item_info_text">${item.direccion}</p>
                    <div class="list_item_horarios-container">
                      ${
                        item.horarios && item.horarios.map(horario=>{
                          return `
                          <p class="list_item_info_text">
                            <strong>${horario.txt_rango_dia}: </strong>${horario.txt_rango_hora}
                          </p>
                          `;
                        }).flat().join('')
                      }
                      <div id="button_info-container">
                        <button  class=" list_item_button gp_status gp_status_azul">Cómo Llegar</button>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="list_item_border"></div>
                
              </div>

            </li>
          `;
        });
      }
      else{
        html = `
          <div id="co_suc-container">
            NO se encontrarón sucursales intenta con otra dirección o puedes:
            <div id="button_info-container">
              <button  id="button_suc_cercana" class=" list_item_button gp_status gp_status_azul">Ir a Sucursal mas cercana</button>
            </div>
          </div>
          
        `
      }
      

      return html;
    }
    function setMarkers(data){
      data.forEach((item,index) =>{
        let image = "https://bibliotecario.s3.amazonaws.com/comercial/imagenes_tiendas/globo-gameplanet.png";
        if(item.tienda.includes('Gamers')){
          image = "https://bibliotecario.s3.amazonaws.com/comercial/imagenes_tiendas/globo-gamers.png";
        }
        const shopMarker = new google.maps.Marker({
          position: new google.maps.LatLng(item.lat, item.lng),
          draggable: false,
          map:map,
          title:item.tienda,
          icon:image
        });
        shopMarker.addListener('click',function(){
          infoWindow.setContent(generateInfoWindow(item));
          infoWindow.open({
            anchor: shopMarker,
            map,
            shouldFocus: false,

          })
          $(".item-active").removeClass("item-active");
          $("#shop_item_"+index).addClass("list_item-container item-active")
        })
        markers.push(shopMarker);
      });
    }
   
    async function getSucursales(lat=19.4326077,long=-99.13320799999997,radio = 20) {
      //hacemos la peticion AL back}
      const request = new XMLHttpRequest();
      request.open('POST',keys_maps.ajaxurl,true);
        //callback - resultado de la peticion
        request.onload = function(){
          const res = JSON.parse(this.response);
        
          if(res.success){
            
            dataShops = res.data;
            markers=[];
            $('#lista-tiendas').html(generateListaTiendas(res.data));
            $('#list-shadow').hide();
            if(!isMobile.matches){
              $('#map-shadow').hide();
              setMarkers(res.data);

            }
          }
          else{
            alert("Tenemos problemas para obtener información. Intenta mas tarde.")
            window.location.href = "https://planet.shop";
          }
        }
        request.onerror = function(){
          alert("Tenemos problemas para obtener información. Intenta mas tarde.")
          window.location.href = "https://planet.shop";
          return false; //hay que mejorar la peticion fallida
        }

        //se envia la peticion
        const data = new FormData();
        data.append('action',keys_maps.action);
        data.append('lat',lat);
        data.append('long',long);
        data.append('radio',radio);
        request.send(data);

       
    }
    $(document).on( 'click', '.list_item-container', function(){
      console.log("click en item", this.id);
      const currentItem = $("#"+this.id);
      const indexOfItem = parseInt(this.id.split('shop_item_')[1]);
      const itemInfo = dataShops[indexOfItem];
      if(isMobile.matches){
        const url = `https://maps.google.com/?q=${itemInfo.lat},${itemInfo.lng}`
        const win = window.open(url, '_blank');
        // Cambiar el foco al nuevo tab (punto opcional)
        win.focus();
      }
      else{
        $(".item-active").removeClass("item-active");
   
        const itemMarker = markers[indexOfItem];
        console.log("index del item",indexOfItem)
        currentItem.addClass("list_item-container item-active");
        infoWindow.setContent(generateInfoWindow(itemInfo));
        infoWindow.open({
          anchor: itemMarker,
          map,
          shouldFocus: false,
  
        })
      }
     
    } );
    $(document).on( 'click', '#button_info-container', function(){
      $('#list-shadow').show();
      if(!isMobile.matches){
        quitarMarkers();
        $('#mapa-shadow').show();

      }
      getSucursales(currentLocation.lat,currentLocation.lng,1000000).then(()=>{
        if(!isMobile.matches){
          const firstSuc = dataShops[0];
          const userUbication = new google.maps.LatLng(firstSuc.lat, firstSuc.lng);
          map.setCenter(userUbication);
          markerUser = new google.maps.Marker({
            position: userUbication,
            draggable: false,
          });
         markerUser.setMap(map);
        }
      });
      
    } );
   
})(jQuery);



