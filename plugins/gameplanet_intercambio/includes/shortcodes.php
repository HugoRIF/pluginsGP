<?php

if (!defined('ABSPATH')) exit;


function f_gameplanet_intercambio_shortcodes_init()
{
  add_shortcode('gp_intercambio', 'intercambio');
}

function intercambio()
{
  wp_enqueue_style( 'intercambio-styles', plugins_url( '../public/css/gameplanet_intercambio.css' , __FILE__ ),array(),"1.0.3");
  wp_enqueue_script( 'intercambio-scripts', plugins_url( '../public/js/gameplanet_intercambio.js' , __FILE__ ), array( 'jquery' ),'1.0.5');
  wp_enqueue_script( 'intercambio-validation-scripts', "https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.min.js", array( 'jquery' ),false);
  wp_localize_script( 'intercambio-scripts', 'intercambio_ajax_param', array(
      'ajaxurl'   => admin_url( 'admin-ajax.php'), // for frontend ( not admin )
      'action'    => AJAX_ACTION_INTERCAMBIO, //
  ));
  $captchaKey         = get_option('gc_clave_sitio');
  $logged = is_user_logged_in();
  $user = null;
  if($logged){
    $user = wp_get_current_user();
  }
  ob_start();
?>

  <div id="intercambio_plugin_container" class="plugin_general_container">
    <div class="page-title normal-title">
      <div class="plugin_general_header-container page-title-inner text-left ">
        <div class="title_section">
          <h1 class="uppercase mb-0">Intercambio</h1>
        </div>

      </div>
    </div>

    <div class="page-wrapper">
      <div class="container" role="main">

        <div class="row vertical-tabs plugin_general-row">

          <div class="large-6 col">

            <div class="col-inner gp_has_border pb-0" >
              <div>
                <div class="row row-main">
                  <div class="large-12 col">

                    <h3>INFORMACIÓN SOBRE INTERCAMBIO</h3>

                    <p>
                       Checa cuánto te pagamos por tus juegos usados en nuestra calculadora de intercambio, solo ingresa el nombre de tu juego para obtener más información.

                    <ul>
                      <li>El monto que te pagamos por tus juegos usados podrá cambiar diario. Te garantizamos el precio de compra siempre y cuando la consulta y la transacción sean el mismo día.</li>
                     
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

            <div class="row" id="intercambio_form_container">
              <div class="large-12 col mb-0  pb-0">
                <div class="mb-0 mt-0 ml-0 mr-0 pb-0 gp_float_box ">
                  <h3 class="uppercase ">Calculadora de Intercambio</h3>
                  <p>También puedes usar el UPC del juego para una consulta más precisa.</p>
                  <form id="intercambio_form" action="" method="POST" autocomplete="off" class="plugin_general-form mb-0">
                    <div class="gp_config">
                      <div class="gp_columna_config gp_columna_izquierda_config">
                        <label for="game_name">Nombre del Juego<span class="required">*</span></label>
                        <input required class="admin_gp_input" type="text" name="game_name" id="game_name" value="DEALS"><br>
                        <!-- <div class="form-group">
                          <div class="g-recaptcha" data-sitekey="<?php echo ($captchaKey) ?>" data-callback="crdRecaptchaCallback" data-expired-callback="recaptchaExpired"></div>
                          <input type="hidden" class="hiddenRecaptcha required" name="hiddenRecaptcha" id="hiddenRecaptcha">
                        </div> -->
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
          <h4 class="section-title section-title-center"><b></b><span class="section-title-main">Resultado de la Busqueda</span><b></b></h4>
        </div>
        <div class="row">
          <div class="col small-12 large-12 ">
            <div class="col-inner">
              <div class="products row row-small large-columns-8 medium-columns-4 small-columns-2" id="intercambio_result_container">

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

