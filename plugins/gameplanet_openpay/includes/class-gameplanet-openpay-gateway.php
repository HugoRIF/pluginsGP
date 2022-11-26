<?php
if (!defined('WPINC')) {
  die;
}
class Gp_Openpay_Gateway extends WC_Payment_Gateway
{

  // constructor de la clase
  public function __construct()
  {
    $this->openpay = 0;
    $this->id = 'gp_openpay';
    $this->has_fields = true;
    $this->method_title = 'Pago con tarjeta de credito o debito';
    $this->method_description = 'Permite a tus clientes pagar con trajetas de credito y debito (gameplanet_openpay).';
    $this->init_form_fields();
    $this->init_settings();
    //$this->icon = $this->get_icon();
    $this->title = $this->settings['title']??'';
    $this->description = $this->settings['description']??'';


    add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
  }

  // agrega mensaje con cantidad de openpay
  public function get_icon()
  {
    gp_openpay_log("get_icon", "Inicio pintar Icono");
  }

  // agrega campos en "pagos" [woocommerce]
  public function init_form_fields()
  {
    $this->form_fields = array(
      'enabled' => array(
        'title'       => __('Enable/Disable', 'woocommerce'),
        'label'       => __('Activar GamePlanet Wallet', 'woocommerce'),
        'type'        => 'checkbox',
        'description' => '',
        'default'     => 'no'
      ),
      'title' => array(
        'title'       => __('Title', 'woocommerce'),
        'type'        => 'text',
        'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),
        'default'     => __('Pago con tarjeta de credito o debito', 'woocommerce'),
        'desc_tip'    => false
      ),
      'live_merchant_id' => array(
        'type' => 'text',
        'title' => __('ID de comercio', 'woothemes'),
        'description' => __('Obten tus llaves de producción de tu cuenta de Openpay.', 'woothemes'),
        'default' => __('', 'woothemes')
      ),
      'live_private_key' => array(
        'type' => 'text',
        'title' => __('Llave secreta', 'woothemes'),
        'description' => __('Obten tus llaves de producción de tu cuenta de Openpay ("sk_").', 'woothemes'),
        'default' => __('', 'woothemes')
      ),
      'live_publishable_key' => array(
        'type' => 'text',
        'title' => __('Llave pública', 'woothemes'),
        'description' => __('Obten tus llaves de producción de tu cuenta de Openpay ("pk_").', 'woothemes'),
        'default' => __('', 'woothemes')
      ),

      'charge_type' => array(
        'title' => __('¿Cómo procesar el cargo?', 'woocommerce'),
        'type' => 'select',
        'class' => 'wc-enhanced-select',
        'description' => __('Detecta si el pago se manda con 3D Secure a partir de la cantidad, manda directo o siempre pide 3d secure', 'woocommerce'),
        'default' => 'direct',
        'desc_tip' => true,
        'options' => array(
          'detect' => __('Detectar', 'woocommerce'),
          'direct' => __('Siempre Directo', 'woocommerce'),
          '3d' => __('Siempre 3D Secure', 'woocommerce'),
        ),
      ),
      'min_value_to_3d' => array(
        'type' => 'text',
        'title' => __('Monto minimo 3D secure', 'woothemes'),
        'description' => __('Monto mínimo para empezar hacer el cargo con 3D secure.', 'woothemes'),
        'default' => __('1000', 'woothemes')
      ),
      'msi' => array(
        'title' => __('Meses sin intereses', 'woocommerce'),
        'type' => 'multiselect',
        'class' => 'wc-enhanced-select',
        'css' => 'width: 400px;',
        'default' => '',
        'options' => $this->getMsi(),
        'custom_attributes' => array(
            'data-placeholder' => __('Opciones', 'woocommerce'),
        ),
    ),
    'minimum_amount_interest_free' => array(
        'type' => 'number',
        'title' => __('Monto mínimo MSI', 'woothemes'),
        'description' => __('Monto mínimo para aceptar meses sin intereses.', 'woothemes'),
        'default' => __('1', 'woothemes')
    )
    );
  }
  public function getMsi() {
    return array('3' => '3 meses', '6' => '6 meses', '9' => '9 meses', '12' => '12 meses', '18' => '18 meses');
}
  public function validate_fields()
  {

    error_log("validando datos del formulario");
    error_log(json_encode($_POST));
    //wc_add_notice("Datos Invalidos Code: P-001", 'error');
    return;
  }
  // procesar pago
  public function process_payment($order_id)
  {
    wc_add_notice("FAKE Algo salió mal, inténtelo nuevamente o use otro método de pago. Si el problema continúa, comuníquese al <a href='tel:+5550475954'>+55 5047 5954</a> y lo resolveremos. Code:S-002", 'error');
    return;

    gp_openpay_log('process_payment', '1. Inicio proceso de cobro openpay GP');
    $nota_orden = "<strong>** Openpay GP **</strong>" . PHP_EOL;

    $order = wc_get_order($order_id);
    $user = get_userdata(get_current_user_id());

    // $aux_orden = $order->get_order_number();
    // $orden = '7' . sprintf('%08d', $aux_orden);
    // $ticket = $orden . date_timestamp_get(date_create()) . '01';
    //! modificación PS
    $ticket = $order->get_order_number();

    $monto = $order->get_total();
    if ($monto > $user->openpay_gp) {
      wc_add_notice("Algo salió mal, inténtelo nuevamente o use otro método de pago. Si el problema continúa, comuníquese al <a href='tel:+5550475954'>+55 5047 5954</a> y lo resolveremos.", 'error');
      return;
    }

    // argumentos para consumo de salgo gp
    $args = array(
      'body' => json_encode(array(
        'id_cliente' => $user->id_gp,
        'gp_token'   => $user->token,
        'ticket'     => $ticket,
        'monto'      => $monto
      )),
      'headers' => array(
        'Content-Type' => 'application/json',
        'data'         => get_option('data-telefonero')
      )
    );
    $url = get_option('ruta_telefonero') . "cliente/saldo/consumo";

    gp_openpay_log('process_payment', '2. Endpoint: ' . $url . ' || (POST)');
    gp_openpay_log('process_payment', '3. BODY/HEADER ',  $args);

    // response de la petición
    $response = wp_remote_post($url, $args); //!

    // error de timeout o inalcanzable
    if (is_wp_error($response)) {
      $mensaje_error = $response->get_error_message();
      gp_openpay_log('process_payment', 'Error Wordpress: ' . $mensaje_error);
      gp_openpay_log('process_payment', "Proceso detenido. Salgo cobro openpay GP\n--------------------------------------------------------------");
      $nota_orden .= "1. Error WP: " . $mensaje_error . PHP_EOL;
      $order->add_order_note($nota_orden);
      $order->update_status('failed', "<strong>** Openpay GP **</strong>" . PHP_EOL);
      $order->save();
      wc_add_notice("Algo salió mal, inténtelo nuevamente o use otro método de pago. Si el problema continúa, comuníquese al <a href='tel:+5550475954'>+55 5047 5954</a> y lo resolveremos. Code:S-002", 'error');
      return;
    }

    // log de respuesta
    gp_openpay_log('process_payment', 'RESPONSE ',  $response);

    // cachamos codigo http (['response']['code'])
    if ($response['response']['code'] == 200) {
      // obtenemos el body
      $ext_auth = json_decode($response['body'], true); //!

      // Success == true
      if ($ext_auth['success']) {

        // cachamos el codigo ( 0 == operacion exitosa)
        if ($ext_auth['code'] == 0) {
          // Success!
          gp_openpay_log('process_payment', '5. Openpay consumido');
          // borra carrito
          WC()->cart->empty_cart();

          $nota_orden .= "1. Openpay consumido" . PHP_EOL .
            "Ticket: " . $ticket . PHP_EOL .
            "#referencia: " . $ext_auth['result'] . PHP_EOL;
          $order->add_order_note($nota_orden);

          // guarda orden y ticket para siguiente proceso
          $order->update_meta_data('_ticket', $ticket);
          $order->update_meta_data('_id_gp', $user->id_gp);

          // se marca como "procesado" ya consumimos openpay
          gp_openpay_log('process_payment', '6. Orden marcada como procesada');

          // se actualiza a "procesado"
          $order->update_status('processing', "<strong>** Openpay GP **</strong>" . PHP_EOL);
          // $order->payment_complete($ext_auth['result']);
          add_post_meta($order->get_id(), '_transaction_id', $ext_auth['result'], true);

          gp_openpay_log('process_payment', '8. Redireccionando a thankyou');
          gp_openpay_log('process_payment', "Fin proceso de cobro openpay GP\n--------------------------------------------------------------");

          // redireccionar a página "thankyou"
          return array(
            'result'   => 'success',
            'redirect' => $this->get_return_url($order)
          );
        } else {

          $nota_orden .= "1. PROCESO DETENIDO" . PHP_EOL . "Se ha regresado un código negativo: " . $ext_auth['code'] . PHP_EOL . "Mensaje: " . $ext_auth['message'] . PHP_EOL;
          $order->add_order_note($nota_orden);

          $order->update_status('failed', "<strong>** Openpay GP **</strong>" . PHP_EOL);

          gp_openpay_log('process_payment', '5. Proceso detenido. Código inválido: ' . $ext_auth['code'] . '. Mensaje: ' . $ext_auth['message']);
          gp_openpay_log('process_payment', '6. Orden marcada como fallida');

          gp_openpay_log('process_payment', "Fin proceso de cobro openpay GP\n--------------------------------------------------------------");

          // mensaje de error 
          wc_add_notice("Algo salió mal, inténtelo nuevamente o use otro método de pago. Si el problema continúa, comuníquese al <a href='tel:+5550475954'>+55 5047 5954</a> y lo resolveremos!", 'error');
          return;
        }
      } else {
        $nota_orden .= "1. PROCESO DETENIDO" . PHP_EOL .
          "Consumo fallido" . PHP_EOL .
          "Código: " . $ext_auth['code'] . PHP_EOL .
          "Mensaje: " . $ext_auth['message'] . PHP_EOL;
        $order->add_order_note($nota_orden);

        $order->update_status('failed', "<strong>** Openpay GP **</strong>" . PHP_EOL);

        gp_openpay_log('process_payment', '5. Proceso detenido. Código inválido: ' . $ext_auth['code'] . '. Mensaje: ' . $ext_auth['message']);
        gp_openpay_log('process_payment', '6. Orden marcada como fallida');

        gp_openpay_log('process_payment', "Fin proceso de cobro openpay GP\n--------------------------------------------------------------");
        wc_add_notice("Algo salió mal, inténtelo nuevamente o use otro método de pago. Si el problema continúa, comuníquese al <a href='tel:+5550475954'>+55 5047 5954</a> y lo resolveremos!", 'error');
        return;
      }
    } else {
      $nota_orden .= "1. PROCESO DETENIDO" . PHP_EOL .
        "Respuesta fallida" . PHP_EOL .
        "Respuesta: " . $response['response']['code'] . PHP_EOL .
        "Mensaje: " . $response['response']['message'] . PHP_EOL;
      $order->add_order_note($nota_orden);

      $order->update_status('failed', "<strong>** Openpay GP **</strong>" . PHP_EOL);

      gp_openpay_log('process_payment', '5. Proceso detenido. Respuesta fallida: ' . $response['response']['code'] . " Mensaje: " . $response['response']['message']);
      gp_openpay_log('process_payment', '6. Orden marcada como fallida');

      gp_openpay_log('process_payment', "Fin proceso de cobro openpay GP\n--------------------------------------------------------------");
      add_filter('woocommerce_available_payment_gateways', 'gp_openpay_wallet_unset', 10, 1);
      wc_add_notice("Algo salió mal, inténtelo nuevamente o use otro método de pago. Si el problema continúa, comuníquese al <a href='tel:+5550475954'>+55 5047 5954</a> y lo resolveremos!", 'error');
      return;
    }
  }

  public function payment_fields()
  {
    include plugin_dir_path(dirname(__FILE__)) . 'public/partials/gameplanet-openpay-public-display.php';
  }
  public function old_gp_openpay_log($funcion, $paso, $entry = null)
  {
    if (!is_ajax()) {
      return false;
    }

    $directorio = "./wp-content/gp/logs_openpay/";
    $extencion = "_gp_openpay.log";

    if (!file_exists($directorio)) {
      mkdir($directorio, 0755, true);
    }
    $tiempo = current_time('mysql');
    $fecha = strtotime($tiempo);
    $fecha_log = date('M-d', $fecha);

    $file = fopen($directorio . $fecha_log . $extencion, "a") or fopen($directorio . $fecha_log . $extencion, "w");

    if (is_null($entry)) {
      $registro = $tiempo . " :: Función: " . $funcion . " || " . $paso . "\n";
    } else {

      if (is_array($entry)) {
        $entry = json_encode($entry);
      }

      $registro = $tiempo . " :: Función: " . $funcion . " || " . $paso . " || " . $entry . "\n";
    }

    $bytes = fwrite($file, $registro);
    fclose($file);

    return $bytes;
  }

  /**
   * creamos nuestras propias opciones para no tener que consultar el settigns a cada rato
   */
  public function process_admin_options()
  {
    parent::process_admin_options();
    error_log('se procesa los cambios del admin' . json_encode($this->settings));
    foreach ($this->settings as $key => $value) {
      update_option($this->id.'_'.$key,$value);
    }
  }
}

/**Se agrega en la seccion de PAgos de Woocomerce */
function gp_openpay_add_creditcard_gateway($methods)
{
  array_push($methods, 'Gp_Openpay_Gateway');
  return $methods;
}

add_filter('woocommerce_payment_gateways', 'gp_openpay_add_creditcard_gateway');
// para quitar opción de pago
function gp_openpay_wallet_unset($gateways)
{
  unset($gateways['openpay_gp']);
  return $gateways;
}
