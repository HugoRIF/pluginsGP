<?php

/**
 * Provee una vista pública del plugin
 *
 * Este archivo se usa para marcar el aspecto de la vista pública del plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Gameplanet_Openpay
 * @subpackage Gameplanet_Openpay/public/partials
 */
  $msi         = get_option('gp_openpay_msi', null);

 $msi_disponibles = get_option('gp_openpay_msi_disponibles',false);

?>
<div id="gp_openpay_form" class="plugin_general-form">
  <div style="overflow: hidden; position: relative;">
    <div class="ajax-loader"></div>
    <h3>Información de Pago</h3>

    <div id="respose_token-container" style="display:none">
      <p>
        ERROR: <span id="response_token_error"></span>
      </p>
    </div>

    <div id="payment_form_openpay_cards">
      <div class="form-row form-row-wide openpay-holder-name">
        <label for="openpay-holder-name">Nombre del títular <span class="required">*</span></label>
        <input id="openpay-holder-name" class="input-text" type="text" autocomplete="off" placeholder="Nombre del tarjetahabiente" data-openpay-card="holder_name">
        <label id="openpay-holder-name_error" class="error" for="facturacion_rfc" style="display:none"></label>
      </div>
      <div class="form-row form-row-wide openpay-card-number">
        <label for="openpay-card-number">Número de tarjeta <span class="required">*</span></label>
        <input id="openpay-card-number" class="input-text wc-credit-card-form-card-number unknown" type="text" maxlength="19" autocomplete="off" placeholder="•••• •••• •••• ••••" data-openpay-card="card_number">
        <label id="openpay-card-number_error" class="error" for="facturacion_rfc" style="display:none"></label>
      </div>

      <div class="form-row form-row-first openpay-card-expiry woocommerce-validated">
        <label for="openpay-card-expiry">Expira (MM/AA) <span class="required">*</span></label>
        <fieldset id="openpay-card-expiry" class="openpay-card-expire">
          <input id="openpay-card-expiry_month" class="input-text" type="text" maxlength="2" autocomplete="off" placeholder="MM" data-openpay-card="expiration_year">
          <span>/</span>
          <input id="openpay-card-expiry_year" class="input-tex" type="text" maxlength="2" autocomplete="off" placeholder="AA" data-openpay-card="expiration_year">
        </fieldset>
        <label id="openpay-card-expiry_error" class="error" for="card-expiry" style="display:none"></label>
      </div>
      <div class="form-row form-row-last openpay-card-cvc woocommerce-validated">
        <label for="openpay-card-cvc">CVV <span class="required">*</span></label>
        <input id="openpay-card-cvc" name="openpay-card-cvc" maxlength="4" class="input-text wc-credit-card-form-card-cvc gp_openpay-card-input-cvc" type="text" autocomplete="off" placeholder="CVC" data-openpay-card="cvv2">
        <label id="openpay-card-cvc_error" class="error" for="facturacion_rfc" style="display:none"></label>

      </div>

    </div>
    
    <?php
    //solo si hay msi se pinta
    if ($msi_disponibles) {
    ?>
    
    <div class="gp-flex-box" style="width:100%">
      <span class="loader-general blue" id="msi_loader" style="display:none"></span>
    </div>
    <div id="gp_openpay_msi-container" style="display:none">
      <div class="form-row form-row-wide">
        <label for="openpay_month_interest_free">Pago a meses sin intereses <span class="required">*</span></label>
        <select name="openpay_month_interest_free" id="openpay_month_interest_free" class="openpay-select">
          <option value="1">Pago de contado</option>
          <?php
          foreach ($msi as $key => $value) {
            echo ('<option value="' . $value . '">' . $value . ' meses</option>');
          }
          ?>
        </select>
      </div>
      <div id="total-monthly-payment" class="form-row form-row-wide ">
        <label>Estarías pagando mensualmente:</label>
        <p class="openpay-total">$ <span id="monthly-payment"> </span></p>
      </div>
    </div>
    <?php
    }
    ?>



    <input type="hidden" name="device_session_id" id="device_session_id" value="">
    <input type="hidden" name="openpay_token" id="openpay_token" value="">

    <div style="height: 1px; clear: both; border-bottom: 1px solid #CCC; margin: 10px 0 10px 0;"></div>

    <div class="accepted_cards-container">
      <div class="accepted_cards-wrapper">
        <div style="display:flex;align-items:center;margin-bottom:10px" >
          <span>Tarjetas de crédito </span>
          <?php echo($msi_disponibles?'<div class="msi_label"><span>Hasta '.$msi[count($msi)-1].' MSI</span></div>':'');?>
        </div>
        <img alt="" src="http://localhost/wordpress/wp-content/uploads/2022/12/credit_cards.png">
      </div>
      <div class="accepted_cards-wrapper">
        <span class="">Tarjetas de débito</span>
        <img alt="" src="http://localhost/wordpress/wp-content/uploads/2022/12/debit_cards.png">
      </div>
    </div>
  </div>
</div>
<!-- Este archivo debe consistir primordialmente de HTML con un poco de PHP. -->