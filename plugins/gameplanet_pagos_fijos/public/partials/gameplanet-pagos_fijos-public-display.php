<?php

/**
 * Provee una vista pública del plugin
 *
 * Este archivo se usa para marcar el aspecto de la vista pública del plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Gameplanet_Pagos_Fijos
 * @subpackage Gameplanet_Pagos_Fijos/public/partials
 */
  $msi         = get_option('gp_pagos_fijos_msi', null);

  $msi_disponibles =  gp_pagos_fijos_obtener_msi_disponibilidad();
  $Cart        = WC()->cart;
  $total_order =  $Cart->get_total("");
  $msi_aplicables = gp_pagos_fijos_msi_aplicables($msi,$total_order);
  $label_next_promo = gp_pagos_fijos_label_next_promo($msi_aplicables,$msi,$total_order);
?>
<input type="hidden" id="gp_pagos_fijos_total_order" value="<?php echo($total_order) ?>">
<div id="gp_pagos_fijos_form" class="plugin_general-form">
  <div style="overflow: hidden; position: relative;">
    <div class="ajax-loader"></div>
    
    <?php
      //solo si hay msi se pinta
      if ($msi_disponibles) {
      ?>
      
      <div id="msi_loader" class="gp-flex-box general_shadow_box" style="display:none" >
        <span class="loader-general blue"></span>
      </div>

      <div id="gp_pagos_fijos_msi-container" style="margin-bottom:0.5em">
        <div class="form-row form-row-wide">
          <label for="openpay_month_interest_free" class="label_openpay_month_interest_free">Plan de pago <span class="required">*</span> 
            <span class="msi_label gp_tooltip">
              <span>HASTA <?php echo($msi[count($msi) -1]) ?> MSI*</span>
              <p class="tooltiptext" style="margin-top:60px"> Meses sin intereses con tarjeta de credito</p>
            </span>
          </label>
          <div class="gp_pagos_fijos_next_promo"><?php echo($label_next_promo)?></div>
          <select name="openpay_month_interest_free" id="openpay_month_interest_free" class="openpay-select" style="margin-bottom:0.5em">
            <option value="1">Pago en una sola exhibición</option>
            <?php
            foreach ($msi as $key => $value) {
              if($total_order/100 >= $value){
                echo ('<option value="' . $value . '">' . $value . ' meses sin intereses</option>');
              }
              else{
                echo ('
                <option disabled value="' . $value . '">' . $value . ' meses sin intereses </option>');
              }
            }
            ?>
          </select>
          <label id="openpay_month_interest_free_warning" class="warning" for="openpay_month_interest_free" style="display:none"></label>
        </div>
        <div id="gp_pagos_fijos_total-monthly-payment" class="form-row form-row-wide " style="display:none">
          <span id="monthly-payment_label"></span>
          <strong class="openpay-total">$ <span id="monthly-payment"> </span></strong> 
        </div>
      </div>
      <?php

    }
    ?>
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
        <input id="openpay-card-cvc" name="openpay-card-cvc" maxlength="4" class="input-text wc-credit-card-form-card-cvc gp_pagos_fijos-card-input-cvc" type="text" autocomplete="off" placeholder="CVC" data-openpay-card="cvv2">
        <label id="openpay-card-cvc_error" class="error" for="openpay-card-cvc" style="display:none"></label>

      </div>

    </div>
    <input type="hidden" name="device_session_id" id="device_session_id" value="">
    <input type="hidden" name="openpay_token" id="openpay_token" value="">

    <div style="height: 1px; clear: both; border-bottom: 1px solid #CCC; margin: 10px 0 10px 0;"></div>

  </div>
</div>
<!-- Este archivo debe consistir primordialmente de HTML con un poco de PHP. -->