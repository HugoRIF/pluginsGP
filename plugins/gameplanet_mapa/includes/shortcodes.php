<?php

if (!defined('ABSPATH')) exit;


function f_gameplanet_mapa_shortcodes_init(){
	//! shortcode para pagina planes, seccion recargas

    add_shortcode( 'buscador_sucursales', 'buscador_sucursales' );
}

//! shortcode para pagina planes, seccion recargas [planes_gamers]
function buscador_sucursales(){
  $version = "1.02";
  wp_enqueue_style( 'maps-styles', plugins_url( '../public/css/gameplanet_mapa.css' , __FILE__ ),array(),$version );
  wp_enqueue_style( 'maps-loader-styles', plugins_url( '../public/css/loader.css' , __FILE__ ) ,array(),$version);
  wp_enqueue_style( 'maps-infoWindow-styles', plugins_url( '../public/css/info_window.css' , __FILE__ ) ,array(),$version);
  wp_enqueue_script( 'maps-scripts', plugins_url( '../public/js/gameplanet_mapa.js' , __FILE__ ), array( 'jquery' ),$version);
  wp_localize_script( 'maps-scripts', 'keys_maps', array(
    'ajaxurl'   => admin_url( 'admin-ajax.php'), // for frontend ( not admin )
    'action'    => AJAX_GET_SUCURSALES, //
  ));
 
  //los scripts de google maps solo es para un ambiente local
   ?>
    <script src = "https://polyfill.io/v3/polyfill.min.js?features=default" defer></script>
    <!-- <script src = "https://maps.googleapis.com/maps/api/js?key=AIzaSyBQe0wH40d8oR-f5cBru1-bvlHB_Gj_sdU&libraries=places" defer></script>
     -->
    <div id="mapa_plugin_container">
      <div class="page-title normal-title">
        <div id="header-container" class="page-title-inner text-left ">
          <div class="title_section">
            <h1 class="uppercase mb-0">BUSCADOR DE SUCURSALES</h1>
          </div>
          <div id="search-container" class="">
            <h4 class="uppercase mb-0">Encontrar en:</h4>
            <input id="search_input" placeholder="Ingresa una dirección">
          </div>
        </div>
      </div>
     
      <div id="body-container">
        <div id="list-container">

          <div id="list">
            <div class="list_title-container">
              <p id="list_title">SUCURSALES CERCANAS</p>
            </div>
            <div class="list_separador"></div>
            <ul id="lista-tiendas"></ul>
          </div>
          <div id="list-shadow">
            <div class="lds-ring"><div></div><div></div><div></div><div></div></div>
          </div>
        </div>

        <div id="map-inner-container">
          <div class="inner-shadow"></div>
          <div id="map-container">
            <div id="mapa_sucursales"></div>
            <div id="map-shadow">
              <div class="lds-ring"><div></div><div></div><div></div><div></div></div>
            </div>
          </div>
         

        </div>

        
      </div>
      
    </div>
    


   <?php
}