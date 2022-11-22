<?php

if (!defined('ABSPATH')) exit;

function f_gameplanet_otp_shortcodes_init()
{

  add_shortcode('gp_otp_billing', 'gp_otp_billing');
  add_shortcode('gp_otp_billing_modal', 'gp_otp_billing_modal');
  add_shortcode('gp_otp_register', 'gp_otp_register');
}

function gp_otp_billing()
{

  $user_id = get_current_user_id();

  if ($user_id <= 0) {
    return;
  }

  $version = time();
  wp_enqueue_style('gp_otp-styles', plugins_url('../public/css/gameplanet_otp.css', __FILE__), array(), $version);
  wp_enqueue_script('gp_otp-scripts', plugins_url('../public/js/gameplanet_otp.js', __FILE__), array('jquery'), $version);
  wp_enqueue_script('gp_otp-validation-scripts', "https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.min.js", array('jquery'), false);
  wp_localize_script('gp_otp-scripts', 'gp_otp_ajax_param', array(
    'ajaxurl'   => admin_url('admin-ajax.php'),
    'action_send'    => AJAX_ACTION_OTP_SEND_BILLING,
    'action_verify'    => AJAX_ACTION_OTP_VERIFY_BILLING,
  ));
  ob_start();
  $phone =  get_user_meta($user_id, 'billing_phone', true);
?>
  <div id="gp_otp_billing_container" class="row">
    <div class="gp_otp_billing_wrapper col large-6">
      <p class="form-row form-row-wide validate-required validate-email" id="billing_email_field" data-priority="110">
        <label>Teléfono</label>
      <div class="woocommerce-input-wrapper">
        <div class="gp_otp_input_wrapper">
          <input type="text" class="input-text" value="<?php echo ($phone) ?>" placeholder="+52 000000000" disabled>
        </div>
        <div class="gp_otp_link_wrapper">
          <a id="open-modal_gp_otp_billing" href="#modal_gp_otp_billing"><?php echo ($phone ? 'Modificar' : 'Agregar') ?> Teléfono</a>
        </div>
      </div>
      </p>
    </div>

  </div>
<?php

  return ob_get_clean();
}
function gp_otp_billing_modal()
{

  ob_start();
?>
  <div id="modal_gp_otp_billing" class="lightbox-by-id lightbox-content mfp-hide lightbox-white " style="max-width:600px ;padding:20px">

    <div class="row row-large">
      <div class="col large-12 pb-0">
        <div class="col-inner" id="modal_gp_otp_billing-wrapper">
          <h3>Agregar Teléfono</h3>

         
          <div id="form_gp_opt_billing-container">

            <div id="gp_otp_phone-wrapper">
              <p id="gp_otp-message_info">Te enviaremos un <strong>código de 6 dígitos por SMS</strong> para validar tu número teléfonico.</p>
              <form id="gp_otp_phone_form" action="" method="POST" autocomplete="off" class="mb-0">
                <div class="form-group">
                  <label for="gp_otp_phone" class="">Nuevo Teléfono<abbr class="required" title="obligatorio">*</abbr></label>
                  <div class="gameplanet_otp_phone_input_wrapper">
                    <span class="prefix">MX (+52) </span>
                    <input type="text" class="admin_gp_input" name="gp_otp_phone" id="gp_otp_phone" placeholder="ej. 5555555555 (10 digitos) " required maxlength="10">
                    <label id="gp_otp_phone-error" class="error" for="gp_otp_phone" style="display:none">Por favor ingresa un teléfono valido</label>
                  </div>
                </div>
                <div style="width:100%;display:flex;flex-direction:column;align-items:center">
                  <button id="gp_otp_send_button" style="width:180px" type="submit" class="button" maxlength="6">Enviar Código</button>
                  <div id="gp_otp_send_again-container" style="display:none" >
                    <p class="gp_opt_billing_resend_otp_label">Demasiadas peticiones por favor intenta en <strong><span class="otp_time_resend">-</span> segundos</strong></p>
                  </div>
                </div>
                
              </form>
            </div>

            <div id="gp_otp_verify-wrapper" style="display:none">
              <p>
                Se envio un SMS al <a id="gp_opt_billing_change_phone" href="#">(+52) <span id="gp_otp_current_number">-</span>  <span class="dashicons dashicons-edit-large" style="font-size: 0.8em;margin-top: 0.5em;"></span></a>  con el código de verificación. <br>
                <span style="font-size:0.8em">En cuanto recibas el código solo tendras 5 minutos para validar tu número.</span>
              </p>
              <form id="gp_otp_verify_form" action="" method="POST" autocomplete="off" class="mb-0">
                <div class="form-group">
                  <label for="gp_otp_code" class="">Código de Verificación<abbr class="required" title="obligatorio">*</abbr></label>
                  <div class="modal_gp_otp_billing_code_wrapper">
                    <input hidden type="text" class="input-text " name="gp_otp_code" id="gp_otp_code" maxlength="6">
                    <fieldset class='gp_otp-number-code' id="gp_otp_code_input">
                      <div id="inputs_container">
                        <input id="gp_otp_code_first-input" name='code' class='code-input' type="text"/>
                        <input name='code' class='code-input' type="text"/>
                        <input name='code' class='code-input' type="text"/>
                        <span>-</span>
                        <input name='code' class='code-input' type="text"/>
                        <input name='code' class='code-input' type="text"/>
                        <input name='code' class='code-input' type="text"/>
                      </div>
                      <label id="gp_otp_code-error" class="error" for="gp_otp_code" style="display:none">Por favor ingresa un teléfono valido</label>
                    </fieldset>
                  </div>
                </div>
                <div class="form-row flex actions">
                  <Button id="gp_otp_verify_button" class="button" style="width:250px" type="submit">Verificar y Continuar</Button>
                  <div id="gp_otp_send_again-container">
                    <p class="gp_opt_billing_resend_otp_label">Enviar de nuevo en <span class="otp_time_resend">-</span> segundos</p>
                    <a id="gp_opt_billing_resend_otp_button" style="display:none" href="#">Enviar de nuevo SMS</a>
                  </div>
                </div>
               
              </form>

            </div>

          </div>
        </div>
      </div>
    </div>

  </div>
<?php

  return ob_get_clean();
}


function gp_otp_register()
{

  $version = time();
  wp_enqueue_style('gp_otp-styles', plugins_url('../public/css/gameplanet_otp.css', __FILE__), array(), $version);
  wp_enqueue_script('gp_otp-scripts', plugins_url('../public/js/gameplanet_otp.js', __FILE__), array('jquery'), $version);
  wp_enqueue_script('gp_otp-validation-scripts', "https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.min.js", array('jquery'), false);
  wp_localize_script('gp_otp-scripts', 'gp_otp_register_ajax_param', array(
    'ajaxurl'   => admin_url('admin-ajax.php'),
    'action_send'    => AJAX_ACTION_OTP_SEND_SIGN_IN,
    'action_verify'    => AJAX_ACTION_OTP_VERIFY_SIGN_IN,
  ));
  ob_start();
?>
  <div id="gp_otp_register_container" class="row">
    <div class="gp_otp_register_wrapper col large-12 pb-0 mb-0">
      <div class="col-inner">
        <div class="form-group">
          <label for="gp_otp_phone" class="">Teléfono<abbr class="required" title="obligatorio">*</abbr></label>
          <div class="gameplanet_otp_phone_input_wrapper">
            <span class="prefix">MX (+52) </span>
            <input type="text" class="admin_gp_input" name="gp_otp_phone" id="gp_otp_phone" placeholder="ej. 5555555555 (10 digitos) " required maxlength="10">
            <label id="gp_otp_phone-error" class="error" for="gp_otp_phone" style="display:none">Por favor ingresa un teléfono valido</label>
            <label id="gp_otp_phone-success" for="gp_otp_phone" style="display:none">Por favor ingresa un teléfono valido</label>

          </div>
          <button id="gp_otp_send_button" style="width:315px"  class="button">Enviar Código de verificación</button>
          <span id="gp_otp_resend_text_register" style="display:none">Renviar Código en <span id="gp_otp_resend_text_register">-</span> segundos</span>
        </div>
        <div class="form-group">
          <label for="gp_otp_phone_code" class="">Código de Verificación<abbr class="required" title="obligatorio">*</abbr></label>
          <div class="">
            <input hidden type="text" class="admin_gp_input" name="gp_otp_phone_code" id="gp_otp_phone_code" required>
            <fieldset class='gp_otp-number-code slim' id="gp_otp_code_input">
              <div>
                <input id="gp_otp_code_first-input" name='code' class='code-input' type="text"/>
                <input name='code' class='code-input' type="text"/>
                <input name='code' class='code-input' type="text"/>
                <input name='code' class='code-input' type="text"/>
                <input name='code' class='code-input' type="text"/>
                <input name='code' class='code-input' type="text"/>
              </div>
              <label id="gp_otp_code-error" class="error" for="gp_otp_code" style="display:none">Por favor ingresa un teléfono valido</label>
            </fieldset>
            <label id="gp_otp_phone_code-error" class="error" for="gp_otp_phone_code" style="display:none">Por favor ingresa un teléfono valido</label>
          </div>
        </div>
        
        <div id="gp_otp_send_again-container" style="display:none">
          <p class="gp_opt_billing_resend_otp_label">Demasiadas peticiones por favor intenta en <span class="otp_time_resend">-</span> segundos</p>
        </div>
      </div>
     
    </div>

  </div>
<?php

  return ob_get_clean();
}
