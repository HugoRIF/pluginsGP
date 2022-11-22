<?php

if (!defined('ABSPATH')) exit;


function f_gameplanet_facturacion_shortcodes_init(){
	//! shortcode para la facturacion de cliente
    add_shortcode( 'facturacion_form', 'facturacion_form' );
    
}

//! shortcode para mostrar el formulario de facturacion de cliente
function facturacion_form(){
  $time = '1.02.1';

    wp_enqueue_style( 'facturacion-styles', plugins_url( '../public/css/gameplanet_facturacion.css' , __FILE__ ),array(),$time );
    wp_enqueue_script( 'facturacion-scripts', plugins_url( '../public/js/gameplanet_facturacion.js' , __FILE__ ), array( 'jquery' ),$time);
    wp_enqueue_script( 'facturacion_validacion-scripts', "https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.min.js", array( 'jquery' ),false);
    

    wp_localize_script( 'facturacion-scripts', 'facturacion_ajax_param', array(
        'ajaxurl'   => admin_url( 'admin-ajax.php'), // for frontend ( not admin )
        'action'    => AJAX_FACTURACION, //
    ));

  $captchaKey = get_option('gc_clave_sitio');
  //$captchaKey = "6LcgupshAAAAAJRzbSc9yCKr5tSfrMDl6dRu9sDd";
  $RegimenActual=[];
 //plugin_factura_logs("Facturacion", "inicia");
 
  $catalogos = facturaObtenerCatalogos();
  
  ob_start();

  if($catalogos['success']){
    //se envia el catalogo del regimen a js para hacer el juego de los select
    $regimenCatalogo = $catalogos['data'];
   
    ?>
    <!-- <script src='https://www.google.com/recaptcha/api.js'></script> -->
    <script> const regimenCatalogo= <?php echo json_encode($regimenCatalogo);?>; </script>
    <div id="facturacion_plugin_container" class="plugin_general_container">
      <div class="page-title normal-title">
        <div id="facturacion_header-container" class="plugin_general_header-container page-title-inner text-left ">
          <div class="title_section">
            <h1 class="uppercase mb-0">Facturación</h1>
          </div>
          
        </div>
      </div>
     
      <div class="page-wrapper">
      
        <div class="container" role="main">
        
          <div class="row vertical-tabs plugin_general-row" >
            
            <div class="large-6 col" >

              <div class="col-inner gp_has_border">
                <div>
                  <div class="row row-main">
                    <div class="large-12 col">
                        
                        <h3>Información sobre la facturación - <span style="font-size:0.7em">Factura version 4.0</span> </h3>
                        
                        <p class="facturacion_descrip-text">
                          Aquí podrás generar de forma automática tu factura. Necesitarás los siguientes datos para realizarla: 
                          <ul class="facturacion_descrip-ul">
                            <li>Tu RFC con Homoclave</li>
                            <li>Tu Razón Social igual como aparece en tu Constancia de Sitación Fiscal, verifica no dejar mas de un espacio entre palabras</li>
                            <li>Si eres persona Moral deberás registrar tu razon social sin régimen societario. Ejemplo: RAZÓN SOCIAL SA de CV solo Ingresar RAZÓN SOCIAL</li>
                            <li>Tu Codigo Postal con los 5 Digitos</li>
                            <li>Tu Regimen Fiscal como aparece en tu Constancia de Sitación Fiscal</li>
                            <li>El uso del CFDI, el uso que le daras a la factura</li>
                            <li>Número de transacción (no. de ticket), 21 digitos</li>
                            <li>El total de tu compra, tal cual aparece en tu ticket incluyendo centavos. Ejemplo: 00.00</li>

                          </ul>

                          <strong>Tus datos deben corresponder exactamente a los que estan registrados en el SAT, si no los conoces, puedes consultarlos en tu Constancia de Situacion Fiscal</strong> <br>

                          También debes considerar que solo podrás generar facturas hasta el último día del mes después de tu compra.<br>
                          La factura viene generada en formato a PDF y comprimida en <a href="http://www.7-zip.org" class="facturacion_descrip-a">formato ZIP</a>; por lo cual debes considerar tener algún software que te permita tanto descomprimir el archivo como ver el <a href="https://get.adobe.com/reader/?loc=es" target="_blank" class="facturacion_descrip-a">formato PDF</a>.
                        </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col large-6">
              <div class="row slide-top" id="response_factura_container" style="display:none">
                <div class="col large-12">
                  <div class="col-inner" id="response_factura_wrapper">

                  </div>
                </div>
              </div>
              <div class="row" id="form_factura_container">
                <div  class="col large-12  mb-0 pb-0">
                  <div class="gp_float_box  mt-0 ml-0 mr-0 mb-0 pb-0">
                    <h3 class="uppercase ">Obten tu Factura</h3>
                    <form id="facturacion_form" action="" method="POST"  autocomplete="off" class=" plugin_general-form mb-0">
                      <div class="gp_config">
                        <div class="gp_columna_config gp_columna_izquierda_config">
                          
      
                          <label  for="facturacion_rfc">RFC<span class="required">*</span></label>
                          <input required class="admin_gp_input" type="text" name="facturacion_rfc" id="facturacion_rfc"  maxlength="13" placeholder="(13 digitos persona Fisica - 12 digitos persona Moral)"><br>
      
                          <label  for="facturacion_razon_social">Razón Social<span class="required">*</span></label>
                          <input required class="admin_gp_input" type="text" name="facturacion_razon_social" id="facturacion_razon_social" placeholder="(Tal cual aparece en tu CFDI sin régimien societario)"><br>
                          
                          <label  for="facturacion_cp">Código Postal<span class="required">*</span></label>
                          <input required class="admin_gp_input" type="text" name="facturacion_cp" id="facturacion_cp" maxlength="5" minlength="5" placeholder="(5 digitos)"><br>
                          
                          <p class="form-row form-row-first">
                          <label  for="facturacion_regimen">Régimen Fiscal<span class="required">*</span></label>
                          <select required class="admin_gp_input" name="facturacion_regimen" id="facturacion_regimen" >
                            <option value=""></option>
                            <?php 
                              foreach ($regimenCatalogo as $regimen) {
                                ?> 
                                <option value="<?php echo($regimen['id_regimen'])?>"><?php echo($regimen['descripcion'])?></option> 
                                
                                <?php
                              }
                            ?>
                          </select>
                          </p>
                          <p class="form-row form-row-last">
                            <label  for="facturacion_cfdi">Uso CFDI<span class="required">*</span></label>
                            <select disabled required class="admin_gp_input" name="facturacion_cfdi" id="facturacion_cfdi" >
                                <option value=""></option>
                            </select>
                          </p>
                          <div class="clear">
                          </div>
      
                          <label  for="facturacion_ticket">Número de Transacción (No. Ticket)<span class="required">*</span></label>
                          <input required maxlength="21" class="admin_gp_input" type="text" name="facturacion_ticket" id="facturacion_ticket" placeholder="(21 digitos)" ><br>
                          
                          <label  for="facturacion_total">Monto Total de Compra (00.00) <span class="required">*</span></label>
                          <input required class="admin_gp_input" type="text" name="facturacion_total" id="facturacion_total" placeholder="(ej. 1999.99)"><br>
                        </div>
                        
                      </div>
                      <div class="clear"></div>
                      <div class="form-group">
                        <div class="g-recaptcha" data-sitekey="<?php echo($captchaKey)?>" data-callback="factRecaptchaCallback" data-expired-callback="recaptchaExpired"></div>
                        <input type="hidden" class="hiddenRecaptcha required" name="hiddenRecaptcha" id="hiddenRecaptcha">
                      </div>
                      <button type="submit" class="form-group button" name="submit_btn" id="plugin_submit_btn" loading="0" style="width:200px" >Obtener Factura</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php
    return ob_get_clean();   
  
  }
  else{
    //si no pudimos encontrar los catalagos
    wc_print_notice( "Ocurrio un problema, por favor intenta mas terde o comunicate con Soporte tecnico.", "error" );
    ?>
    <div id="facturacion_plugin_container">
      <div class="page-title normal-title">
        <div id="facturacion_header-container" class="page-title-inner text-left ">
          <div class="title_section">
            <h1 class="uppercase mb-0">Facturación</h1>
          </div>
          
        </div>
      </div>
      
    <?php
  }
 
}

/**
 * atributos de response
 * message = mensaje de la api
 * data = link de factura
 * 
*/
function FacturaSuccessMessage($response){
   ?>
   <div class="gp_success-box gp_float_box mt-0 ml-0 mr-0 mb-0">
      <div id="success_section_header" class="factura-success_section_header">
        <div class="icon-container">
          <i class="icon-checkmark"></i>
        </div>
        <div  class="message-container">
          <h2>¡Solictud de Factura Exitosa!</h2>
        </div>
      </div>
      <div id="success_section_response">
        <div class="response">
          <p><?php echo($response['message'])?></p>         
        </div>
        <div class="link_button">
          <a href="<?php echo($response['data'])?>" target="_blank" class="button">Descargar Aquí</a>

        </div>
        <div class="copy_button">
          <button id="copy_button-button" class="copy_button-button" content="">Copiar enlace</button>   
          <p hidden id="factura_link"> <?php echo($response['data'])?> </p>
        </div>
      </div>   
    </div>  
   <?php
};
function FacturaFailMessage($response){
  $data = $response['data'];
  if(is_array($response['data'])){
    $data = implode(', ',$response['data']);
  }
   ?>
   <div class="gp_fail-box gp_float_box mt-0 ml-0 mr-0 mb-0 ">
      <div id="fail_section_header" class="factura-fail_section_header">
        <div class="icon-container">
          <i class="icon-plus"></i>
        </div>
        <div  class="message-container">
          <h3>¡Algo Salío Mal!</h3>
        </div>
      </div>
      <div id="fail_section_response">
        <div class="response">
          <p><?php echo($response['message'])?></p>         
        </div>
        <div class="data">
          <p><?php echo($data)?></p>         
        </div>
      </div>   
    </div>  
   <?php
};