<?php

if (!defined('ABSPATH')) exit;


function f_gameplanet_cash_shortcodes_init()
{

  add_shortcode('gp_cash', 'gp_cash');
}
//add_action( 'wp_enqueue_scripts', 'f_gameplanet_cash_assets' );

function gp_cash()
{
  $version = "1.0.9";
  wp_enqueue_style( 'gp_cash-styles', plugins_url( '../public/css/gameplanet_cash.css' , __FILE__ ),array(),$version);
  wp_enqueue_script( 'gp_cash-scripts', plugins_url( '../public/js/gameplanet_cash.js' , __FILE__ ), array( 'jquery' ),$version);
  wp_enqueue_script( 'gp_cash_validate-scripts', "https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.min.js", array( 'jquery' ),false);
  wp_localize_script( 'gp_cash-scripts', 'gp_cash_ajax_param', array(
    'ajaxurl'   => admin_url( 'admin-ajax.php'), 
    'action'    => AJAX_ACTION_CASH, 
  ));
  /**
   * Obtenemos lista e methodos de pago disponibles: esto esta hardcodeado por el momento no se como hacerlo
   * Revisar en functions.php->cashGetInfoPaymentMethod si se agregan nuevos 
   * */
  $paymentMethods = [
    [
      "name" => "oxxo_cash",
      "store_name" => "OXXO",
      "image" => "https://upload.wikimedia.org/wikipedia/commons/thumb/6/66/Oxxo_Logo.svg/1200px-Oxxo_Logo.svg.png"
    ]
  ];
  $logged = is_user_logged_in();
  $user = null;
  if ($logged) {
    $user = wp_get_current_user();
  }
  ob_start();
  wp_enqueue_style('gp_cash_slider-styles', plugins_url('../public/css/ion.rangeSlider.css', __FILE__), array(), "1.0.0");
  wp_enqueue_script('gp_cash_slider-scripts', plugins_url('../public/js/ion.rangeSlider.js', __FILE__), array(), '1.0.0');
  wp_enqueue_script('gp_cash_image_cb-scripts', plugins_url('../public/js/imgcheckbox.js', __FILE__), array(), '1.0.0');

?>
  <!-- <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBQe0wH40d8oR-f5cBru1-bvlHB_Gj_sdU&libraries=places" defer></script> -->

  <div id="gp_cash_plugin_container" class="plugin_general_container">
    <div class="page-title normal-title">
      <div class="plugin_general_header-container page-title-inner text-left ">
        <div class="title_section">
          <h1 class="uppercase mb-0">GAMEPLANET<span class="cash_green">CASH</span></h1>
        </div>

      </div>
    </div>

    <div class="page-wrapper">
      <div class="container" role="main">
        <div class="row vertical-tabs" id="gp_cash_main_wrapper">
          
          <div class="large-4 col " id="cash_form_wrapper">
            <div class="row slide-top" id="response_content" style="display:<?php echo($logged?'none':'block')?>">
              <div class="large-12 col pb-1">
                <div class="col-inner" id="response_content_wrapper" >
                  <?php if (!$logged) { ?>
                    <div class="gp_message warning gp_float_box mb-0 mt-0 ml-0 mr-0">
                      <div class="icon-container">
                        <span class="material-symbols-outlined">
                          warning
                        </span>
                      </div>
                      <p>
                        Por favor <strong style="text-decoration: underline;"><a href="#" id="cash_login_link" data-open="#login-form-popup" >Inicia Sesión / Crea una Cuenta</a></strong> para obtener tus codigos
                      </p>
                    </div>
                  <?php } ?>

                </div>
              </div>
            </div>

            <div class="row" id="gp_cash_form_container">
              <div class="large-12 col mb-0  pb-0">
                <div class="mb-0 mt-0 ml-0 mr-0 gp_float_box " id="gp_cash_form_wrapper" >
                  <div>
                    <div style="display:flex;align-items:center">
                      <img src="https://cdn4.iconfinder.com/data/icons/add-1/60/barcode-512.png" style="width:100px; height: auto; margin-left:-10px;margin-right:10px" alt="">
                      <p class="mb-0">
                      Compra de manera segura <strong class="cash_green">sin tarjeta de débito o crédito,</strong> depositando en más de <strong class="cash_green">40 mil establecimientos participantes.</strong>
                      </p>
                    </div>
                    <div>
                      <p class="mb-1">
                        Sigue estos 3 sencillos pasos para realizar un abono:
                      </p>
                    </div>
                    <form id="gp_cash_form" action="" method="POST" autocomplete="off" class="plugin_general-form mb-0">
                      <div>
                        <div>
                          <p>1. Selecciona la cantidad que deseas abonar a tu cuenta de Planet Shop.</p>
                          <div id="slider_container">
                            <input id="creditos" type="text" class="js-range-slider" name="creditos" value="" />
                          </div>

                        </div>
                        <div>
                          <p>2. Selecciona el establecimiento participante donde deseas realizar el abono a tu cuenta de Planet Shop.</p>
                          <div id="image_cb">
                            <?php
                            foreach ($paymentMethods as $key => $paymentMethod) {
                            ?>
                              <img name="<?php echo ($paymentMethod['name']) ?>" id="<?php echo ($paymentMethod['store_name']) ?>" class="image_option" src="<?php echo ($paymentMethod['image']) ?>" alt="" width=100>

                            <?php
                            }
                            ?>

                          </div>
                          <p style="padding-left:1em">Tienda seleccionada: <strong id="gp_cash_payment_selected"><?php echo ($paymentMethods[0]['store_name']) ?></strong></p>
                        </div>
                        <div>
                          <p>3. Obten tu código: (Esto puedo tardar unos segundos por favor espera)</p>
                          <?php
                          if ($logged) {
                          ?>
                            <button type="submit" class="button" name="submit_btn" id="plugin_submit_btn" loading="0" style="width:250px">Obtener Mi Código</button>
                          <?php
                          } else {
                          ?>
                            <button disabled="" class="button" name="submit_btn" loading="0" style="width:250px">Obtener Mi Código</button>

                          <?php
                          }
                          ?>
                         
                        </div>
                        <span style="font-size:0.8em"><strong> Importante: La comisión por recepción del pago varía de acuerdo a los
                          términos y condiciones que cada cadena comercial
                          establece.</strong></span>

                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>

          </div>
          <div class="large-8 col">


            <div class="row" id="gp_cash_map_container">
              <div class="large-12 col mb-0  pb-0" style="padding-left:0">
                <div id="cash_map-wrapper">
                  <div id="mapa_sucursales_cash"></div>

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
