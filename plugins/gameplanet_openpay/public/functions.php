<?php

function gp_openpay_log($funcion, $paso, $entry = null)
  {
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
function gp_openpay_ajax_getCardType(){
  try {
    gp_openpay_log('Obtener tipo de tarjeta','Inicio');

  //validaciones
  if(! is_user_logged_in()){
    echo json_encode([
      "success"=>false,
      "message"=>"Usuario no reconocido",
      "code"=>401,
    ]);
    die();
  }
  $card_bin = isset($_POST['cardBin'])?$_POST['cardBin']:null;
  if($card_bin && !empty($card_bin) && strlen($card_bin)<8){
    echo json_encode([
      "success"=>false,
      "message"=>"Número invalido",
      "code"=>409,
    ]);
    die();
  }

  //consulta a open pay
  $merchant_id = get_option('gp_openpay_live_merchant_id');
  $path       = sprintf('/%s/bines/man/%s', $merchant_id, $card_bin);

  //RECUERDA CAMBIAR EL TRUE POR FALSE en PRODUCCION ¿lo paso a una configuracion?
  $getCardType = requestOpenpay($path,'mx',true,null,null,get_option('gp_openpay_live_private_key'));
  //no se muy bien como cachar los errores de su API
  if(isset($getCardType['error_code'])){
    gp_openpay_log('Obtener tipo de tarjeta','No se encontro tarjeta', $getCardType);
    echo json_encode([
      "success"=>false,
      "message"=>"Informacion Tarjeta",
      "code"=>404,
    ]);
    die();

  }
  echo json_encode([
    "success"=>true,
    "message"=>"Informacion Tarjeta",
    "data"=>$getCardType,
  ]);
  die();
  } catch (Exception $e) {
    gp_openpay_log('Obtener tipo de tarjeta','Error Intrerno', $e->getMessage());

    echo json_encode([
      "success"=>false,
      "message"=>"Error Interno",
      "code"=>500,
    ]);
    die();
  }
  
}

function gp_openpay_obtener_msi_disponibilidad(){
  try {
         
    //leeemos si hay meses sin intereses
    $msi         = get_option('gp_openpay_msi', null);
    $msi_type    = get_option('gp_openpay_msi_type', '');                                        //a que productos se aplica
    $min_msi     = (float) get_option('gp_openpay_minimum_amount_interest_free', 1);
    $Cart        = WC()->cart;
    $total_order = (float) $Cart->total;
    $items_order =  $Cart->get_cart();
    $msi_disponibles = false;

    if(empty($msi_type)){
      //no hay un tipo, nunca deberia pasar
      return false;
    }
    
  
    switch ($msi_type) {
      case 'all':
        //todos los productos tienen MSI
        $msi_disponibles = is_array($msi) && !empty($msi) && $total_order>=$min_msi; //si es un array con algo dentro es que si hay meses
        break;
      case 'meta':
        $meta_key = get_option('gp_openpay_msi_product_meta_data_key', '');
        $meta_value = get_option('gp_openpay_msi_product_meta_data_value', '');
        
        if(empty($meta_key)){
          return false;//no se especifico un meta dato
        }

        //Solo los productos con el meta especifico tienen descuento
        //si hay por lo menos uno sin el metakey se cancelan los MSI
        
        foreach ($items_order as $key => $item) {
          $product = wc_get_product($item['product_id']);

          $existe_meta= $product->get_attribute($meta_key);
          if(!$existe_meta){
            $msi_disponibles = false;//uno no tiene el meta

            break;
          }
          if(empty($meta_value)){
            //el value no esta espeficado entonces con que traiga el meta data
            $msi_disponibles = true;
          }
          elseif($existe_meta == $meta_value){
            $msi_disponibles = true;
          } 
          else{
            $msi_disponibles = false;
          }
        }
        if(!is_array($msi) || empty($msi) || $total_order<$min_msi){
          //no cumple con el minimo
          $msi_disponibles=false;
        }
        break;
      default:
        return false;
        break;
    }
    return $msi_disponibles;
  } catch (\Exception $e) {
    gp_openpay_log('gp_openpay_obtener_msi_disponibilidad',"Error interno",$e->getLine().': '.$e->getMessage());
    return false;
  }
}
 function requestOpenpay($api, $country, $is_sandbox, $method = 'GET', $params = [], $auth = null) {

  gp_openpay_log('Request Openpay',"MODO SANDBOX ACTIVO: " . $is_sandbox);

  $country_tld    = strtolower($country);
  $sandbox_url    = 'https://sandbox-api.openpay.'.$country_tld.'/v1';
  $url            = 'https://api.openpay.'.$country_tld.'/v1';
  $absUrl         = $is_sandbox === true ? $sandbox_url : $url;
  $absUrl        .= $api;
  $headers        = Array();
  gp_openpay_log('Request Openpay','Current Route => '.$absUrl);


  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $absUrl);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

  if(!empty($params)){
      $data = json_encode($params);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
      $headers[] = 'Content-Type:application/json';
  }

  if(!empty($auth)){
      $auth = base64_encode($auth.":");
      $headers[] = 'Authorization: Basic '.$auth;
  }

  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $result = curl_exec($ch);
  gp_openpay_log('Request Openpay','Termina peticion');

  if ($result === false) {
    gp_openpay_log('Request Openpay','Fallo peticion','Curl error '.curl_errno($ch).': '.curl_error($ch));
  } else {
      $info = curl_getinfo($ch);
      gp_openpay_log('Request Openpay','Peticion exitosa','HTTP code '.$info['http_code'].' on request to '.$info['url']);
  }
  curl_close($ch);

  return json_decode($result,true);
}

/**
 * Callback despues del 3D secure
 * 
 * Si la orden se necesita confirmar con 3D secure el usuario debe validar esto
 * dependiendo el caso se debe cambiar a processibg o failed
 */
function gp_openpay_woocommerce_confirm(){
  global $woocommerce;
  $logger = wc_get_logger();
  //supuestamente despues del 3D siempre vendra un ID como GET ya sea por exito o falla
  $id = $_GET['id'];
 
  gp_openpay_log('gp_openpay_woocommerce_confirm','6. Inicia redirect a checkout final de la transaccion '.$id);
  try {
    $openpay_cards = new Gp_Openpay_Gateway();
   
    $charge = $openpay_cards->api_openpay_call('charges/'.$id);
    $order = new WC_Order($charge['order_id']);
    gp_openpay_log($order->get_id().' gp_openpay_woocommerce_confirm','6.1 Se obtuvo la orden ',$order->get_id());

    /**
     * si es status recibido no es completed significa que ele usuario cancelo la orden en el 3D secure
     * o ocurrio un error con la tarjeta
     */
    if ($order && $charge['status'] != 'completed') {
      //
      gp_openpay_log($order->get_id().' gp_openpay_woocommerce_confirm','6.2 No se pudo completar el cargo con 3D secure',$charge);

      $order->add_order_note("GP Openpay". PHP_EOL .  "ERROR EN 3D SECURE". PHP_EOL . 'No se pudo realizar el cobro por que el cliente lo cancelo o algun otro error en 3D secure'. PHP_EOL . $charge['status']);
      $order->set_status('failed');
      $order->save();

      if (function_exists('wc_add_notice')) {
        wc_add_notice(__('Lo sentimos No se pudo concretar tu orden, intenta mas tarde por favor CODE: OP-F-001'), 'error');
      } else {
        $woocommerce->add_error(__('Error en la transacción: No se pudo completar tu pago. CODE: OP-F-001'), 'woothemes');
      }
     
    } 
    //si la orden se completo correctamente en openpay se pasa a processing
    if ($order && $charge['status'] == 'completed') {
      gp_openpay_log($order->get_id().' gp_openpay_woocommerce_confirm','6.2 Cargo autorizado y completado',$charge);
      $order->add_order_note("GP Openpay". PHP_EOL . "Cargo autorizado con 3D secure". PHP_EOL . $charge['status']);
      $order->set_status('processing');
      $order->save();
    }
    gp_openpay_log($order->get_id().' gp_openpay_woocommerce_confirm','6.3 redirigimos al resumen del checkout');

    wp_redirect($openpay_cards->get_return_url($order));
  } catch (Exception $e) {
    gp_openpay_log(' gp_openpay_woocommerce_confirm','Algo salio mal ',$e->getMessage());

    status_header(404);
    nocache_headers();
    include(get_query_template('404'));
    die();
  }  
}

/**
 * En caso de que el cargo se realizo con 3D secure loq ue debemos hacer
 * es redirigir a la Autenticacion del banco
 * 
 * 
 */
function gp_openpay_wc_custom_redirect_after_purchase() {
  global $wp;
  if (is_checkout() && !empty($wp->query_vars['order-received'])) {
      $order = new WC_Order($wp->query_vars['order-received']);
      //tengo mis dudas con esto pero parece que funciona
      $redirect_url = get_post_meta($order->get_id(), '_openpay_3d_secure_url', true);
      gp_openpay_log($order->get_id().'gp_openpay_wc_custom_redirect_after_purchase',' 5.1 Se obtiene la url de redirect',$redirect_url);

      if ($redirect_url && !in_array($order->get_status(),['completed','processing','on-hold'])) {
          //como ya no va a servir el link se borra
          delete_post_meta($order->get_id(), '_openpay_3d_secure_url');
          gp_openpay_log($order->get_id().'gp_openpay_wc_custom_redirect_after_purchase','5.2 Si es con 3D secure se redirige a:',$redirect_url);
          wp_redirect($redirect_url);
          exit();
      }
  }
}