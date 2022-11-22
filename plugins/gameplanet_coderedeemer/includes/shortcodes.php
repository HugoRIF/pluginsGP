<?php

if (!defined('ABSPATH')) exit;


function f_gameplanet_coderedeemer_shortcodes_init()
{
  //! shortcode para la facturacion de cliente
  add_shortcode('coderedeemer', 'coderedeemer');
  add_shortcode('coderedeemer_modal', 'coderedeemer_modal');
}

//! shortcode para mostrar el formulario de facturacion de cliente
function coderedeemer()
{
  wp_enqueue_style( 'coderedeemer-styles', plugins_url( '../public/css/gameplanet_coderedeemer.css' , __FILE__ ),array(),"1.0.3");
  wp_enqueue_script( 'coderedeemer-scripts', plugins_url( '../public/js/gameplanet_coderedeemer.js' , __FILE__ ), array( 'jquery' ),'1.0.6');
  wp_enqueue_script( 'coderedeemer-validation-scripts', "https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.min.js", array( 'jquery' ),false);
  wp_localize_script( 'coderedeemer-scripts', 'crd_ajax_param', array(
      'ajaxurl'   => admin_url( 'admin-ajax.php'), // for frontend ( not admin )
      'action'    => AJAX_ACTION_TEST, //
      'nonce'    => wp_create_nonce('coderedeemer_consulta_ticket'), //
  ));
  
  $captchaKey         = get_option('gc_clave_sitio');
  $formularioValido   = true;
  $logged = is_user_logged_in();
  $user = null;
  if($logged){
    $user = wp_get_current_user();
  }
  $listaProductos = coderedeemerObtenerLista();

  ob_start();


?>
  <script src='https://www.google.com/recaptcha/api.js'></script>
  <script src='https://npmcdn.com/flickity@2/dist/flickity.pkgd.js'></script>
  <div id="coderedeemer_plugin_container" class="plugin_general_containe">
    <div class="page-title normal-title">
      <div class="plugin_general_header-container page-title-inner text-left ">
        <div class="title_section">
          <h1 class="uppercase mb-0">Code Redeemer</h1>
        </div>

      </div>
    </div>

    <div class="page-wrapper">
      <div class="container" role="main">

        <div class="row vertical-tabs plugin_general-row">

          <div class="large-6 col">

            <div class="col-inner gp_has_border" style="height:fit-content;">
              <div>
                <div class="row row-main">
                  <div class="large-12 col">

                    <h3>INFORMACIÓN SOBRE CODEREDEEMER</h3>

                    <p class="facturacion_descrip-text">
                    <?php if($logged){?>
                      Bienvenido  <strong><?php echo($user->display_name);?></strong> a la sección de coderedeemer, obtén tu código de descarga con tu número de recibo de compra. Puedes ver en la lista, los productos que participan para obtener uno o más códigos de descarga.
                    <?php } else {?>
                      Obtén tu código de descarga con tu número de recibo de compra. Puedes ver en la lista, los productos que participan para obtener uno o más códigos de descarga.

                    <?php }?>
                    <ul class="facturacion_descrip-ul">
                      <li>Aplica para juegos nuevos y usados.</li>
                      <?php if(!$logged){?>
                        <li>Tienes que <strong style="text-decoration: underline;"><a href="#" data-open="#login-form-popup" id="crd_login_button">Iniciar Sesión</a> </strong>para obtener tus codigos</li>

                      <?php } else {?>
                        <li>Todos los códigos que descargues se asignaran a tu cuenta con tu número de cliente</li>
                        <!-- <li>Puedes ver la lista de tus códigos redimidos aqui</li> -->

                      <?php } ?>
                      <li>Recuerda que si relaizaste tu compra con un número de cliente solo tu puedes redimir tus codigos.</li>
                    </ul>
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>


          <div class="large-6 col">

            <div class="row slide-top" id="response_content" style="display:none">
              <div class="large-12 col ">
                <div class="col-inner" id="response_content_wrapper">
                 
                </div>
              </div>
            </div>

            <div class="row" id="crd_form_container">
              <div class="large-12 col mb-0  pb-0">
                <div class="mb-0 mt-0 ml-0 mr-0 pb-0 gp_float_box ">
                  <h3 class="uppercase ">OBTEN TU CÓDIGO</h3>
                  <p>Introduce el número de tu recibo de compra.</p>
                  <form id="coderedeemer_form" action="" method="POST" autocomplete="off" class="plugin_general-form mb-0">
                    <div class="gp_config">
                      <div class="gp_columna_config gp_columna_izquierda_config">
                        <label for="tickect_number">Número de ticket<span class="required">*</span></label>
                        <input required class="admin_gp_input" type="text" name="tickect_number" id="tickect_number" value="<?php echo ($formularioValido ? '' : $_POST['coderedeemer_code']) ?>" maxlength="21"><br>
                        <div class="form-group">
                          <div class="g-recaptcha" data-sitekey="<?php echo ($captchaKey) ?>" data-callback="crdRecaptchaCallback" data-expired-callback="recaptchaExpired"></div>
                          <input type="hidden" class="hiddenRecaptcha required" name="hiddenRecaptcha" id="hiddenRecaptcha">
                        </div>
                        <button type="submit" class="button" name="submit_btn" id="plugin_submit_btn" loading="0" style="width:200px">Consultar</button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>


        </div>

        <div class="container section-title-container" style="max-width:100%;">
          <h4 class="section-title section-title-center"><b></b><span class="section-title-main">Productos con Códigos Disponibles</span><b></b></h4>
        </div>
        <?php if ($listaProductos['success']) { ?>
          <div class="row">
            <div class="col small-12 large-12 ">
              <div class="col-inner">
                <div class="row">
                  <?php
                  foreach ($listaProductos['data'] as $item) {
                    codeReddemerProductItem($item);
                  }

                  ?>

                </div>
              </div>
            </div>
          </div>

        <?php } else { ?>
          <div class="row">
            <div class="col small-12 large-12">
              <div class="col-inner">
                <div class="row">
                  No podemos obtener la lista intenta mas tarde
                </div>
              </div>
            </div>
          </div>
        <?php }


        ?>
      </div>
    </div>
  </div>
<?php

  return ob_get_clean();
}


function codeReddemerProductItem($item)
{

?>

  <div class="col medium-4 small-12 large-3  crd_item-wrapper">
    <div class="col-inner gp_float_box crd_item-container">
      <div class="row row-small">
        <div class="col small-12 large-12 crd_item-header ">
          <div class="col-inner">
            <div class="una_linea">
              <h4 class="mb-0"> <?php echo ($item['nombre']) ?></h4>

            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="large-12 col pb-0">
          <div class="col-inner">
            <div class="slider-wrapper relative">
              <div class="slider slider-nav-dots-dashes-spaced slider-nav-circle slider-nav-large slider-nav-light slider-style-normal slider-show-nav is-draggable flickity-enabled" data-flickity='{
                    "cellAlign"           : "center",
                    "imagesLoaded"        : true,
                    "lazyLoad"            : 1,
                    "freeScroll"          : false,
                    "wrapAround"          : false,
                    "autoPlay"            : false,
                    "pauseAutoPlayOnHover": true,
                    "prevNextButtons"     : true,
                    "contain"             : true,
                    "adaptiveHeight"      : true,
                    "dragThreshold"       : 10,
                    "percentPosition"     : true,
                    "pageDots"            : true,
                    "rightToLeft"         : false,
                    "draggable"           : true,
                    "selectedAttraction"  : 0.1,
                    "parallax"            : 0,
                    "friction"            : 0.6         }'>
                <?php
                if($item['internal_info']['link']){
                  foreach ($item['internal_info']['link'] as $key => $internal) {
                    $dataProduct = new WC_Product($item['internal_info']['ids'][$key]);
                   ?>
                     <div class="crd-carousel-cell hover02">
                       <a href="<?php echo ($internal) ?>" target="_blank">
                         <div class="crd-carousel-cell-container">
                           <figure>
                             <?php
                              
                               echo ($dataProduct->get_image('woocommerce_thumbnail',['class'=>"crd-carousel-image"]));
                             ?>
                             <!-- <img src="https://gameplanet-53f8.kxcdn.com/media/gameplanetgamers2.png" alt="" class="crd-carousel-image"> -->
   
                           </figure>
                         </div>
                       </a>
                     </div>
   
                   <?php
   
                   }
                }else{
                  echo('<div style="height:200px"></div>');
                }
                
                ?>
              </div>

            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col small-12 large-12 pb-0">
          <div class="col-inner">
            <div>
              <p class="pb-0">
                <strong>Inicio de campaña: </strong> <?php echo date_i18n(esc_html__('l jS \o\f F Y, h:ia', 'woocommerce'), strtotime($item['fecha_inicio'])) ?><br>
                <strong>Fin de campaña: </strong> <?php echo date_i18n(esc_html__('l jS \o\f F Y, h:ia', 'woocommerce'), strtotime($item['fecha_fin'])) ?>

              </p>
            </div>

            <div class="crd_item-descripcion-collapsed" id="descripcion_<?php echo ($item['id_campania']) ?>">
              <span class="mb-0 pb-0 ">
                <?php echo ($item['detalles']) ?>
              </span>

            </div>
            <div style="display:flex;justify-content:end">
              <button class="cdr-ver-mas-button" id="button_ver_mas_<?php echo ($item['id_campania']) ?>" for="<?php echo ($item['id_campania']) ?>" collapsed="1"> Ver más</button>
            </div>


          </div>
        </div>



      </div>

    </div>
  </div>
<?php
}

function coderedeemer_modal()
{
  ob_start();
?>
  <div class="lightbox-inner">
    <div class="col2-set row row-divided row-large">
      <div class="col large-12">

        <div class="col-inner" id="crd-modal-container">
          <h3 id="crd-modal-title"> CODEREDEEMER</h3>
          <div id="crd-modal-body">
            <div class="g_message warning">

              <div class="icon-container">
                <span class="material-symbols-outlined">
                  warning
                </span>
              </div>
              <p>
                Tienes que iniciar sesión para redimir tus codigos
              </p>
            </div>
          </div>
          <div id="crd-modal-footer"></div>
        </div>
      </div>

    </div>
  </div>
  <button title="Close (Esc)" type="button" class="mfp-close" style="top: 0 !important;"><svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x">
      <line x1="18" y1="6" x2="6" y2="18"></line>
      <line x1="6" y1="6" x2="18" y2="18"></line>
    </svg></button>

<?php
  return ob_get_clean();
}
