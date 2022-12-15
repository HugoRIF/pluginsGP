<?php

function gp_pagos_fijos_log($funcion, $paso, $entry = null)
  {
    $directorio = "./wp-content/gp/logs_openpay/";

    
    $extencion = "_gp_pagos_fijos.log";

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
function gp_pagos_fijos_ajax_getCardType(){
  try {
    gp_pagos_fijos_log('Obtener tipo de tarjeta','Inicio');

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
  $merchant_id = get_option('gp_pagos_fijos_live_merchant_id');
  $path       = sprintf('/%s/bines/man/%s', $merchant_id, $card_bin);
  $is_sandbox = get_option('gp_pagos_fijos_sandbox','yes')=='no'?false:true;
  //RECUERDA CAMBIAR EL TRUE POR FALSE en PRODUCCION ¿lo paso a una configuracion?
  $getCardType = requestOpenpay($path,'mx',$is_sandbox,null,null);

   //no se muy bien como cachar los errores de su API
  if(isset($getCardType['error_code'])){
    gp_pagos_fijos_log('Obtener tipo de tarjeta','No se encontro tarjeta', $getCardType);
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
    gp_pagos_fijos_log('Obtener tipo de tarjeta','Error Intrerno', $e->getMessage());

    echo json_encode([
      "success"=>false,
      "message"=>"Error Interno",
      "code"=>500,
    ]);
    die();
  }
  
}


function gp_pagos_fijos_obtener_msi_disponibilidad(){
  try {
         
    //leeemos si hay meses sin intereses
    $msi         = get_option('gp_pagos_fijos_msi', null);
    $msi_type    = get_option('gp_pagos_fijos_msi_type', '');                                        //a que productos se aplica
    $Cart        = WC()->cart;
    $total_order = (float) $Cart->get_total("");
    $items_order =  $Cart->get_cart();
    $msi_disponibles = false;

    if(!is_array($msi) || empty($msi)){
      return false;
    }

    if(empty($msi_type)){
      //no hay un tipo, nunca deberia pasar
      return false;
    }
    $min_msi = $msi[0] *100;//monto minimo que entra en la promo
  
    switch ($msi_type) {
      case 'all':
        //todos los productos tienen MSI
        
        //validamos que el monto aplique a la promocion
        $msi_disponibles = $total_order>=$min_msi; 
        break;
      case 'meta':
        $meta_key = get_option('gp_pagos_fijos_msi_product_meta_data_key', '');
        $meta_value = get_option('gp_pagos_fijos_msi_product_meta_data_value', '');
        
        if(empty($meta_key)){
          return false;//no se especifico un meta dato
        }

        //Solo los productos con el meta especifico tienen descuento
        //si hay por lo menos uno sin el metakey se cancelan los MSI
        
        foreach ($items_order as $key => $item) {
          $existe_meta= get_post_meta($item['product_id'],$meta_key,true);
          if($existe_meta === false){
            $msi_disponibles = false;//uno no tiene el meta

            break;
          }
          if($meta_value == ''){
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
        if( $total_order<$min_msi){
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
    gp_pagos_fijos_log('gp_pagos_fijos_obtener_msi_disponibilidad',"Error interno",$e->getLine().': '.$e->getMessage());
    return false;
  }
}

/**
 * obtiene en que promocion cae la orden actual 
 * 
 * Solo llamar cuando los MSI son aplicables
 * */
function gp_pagos_fijos_msi_aplicables($msi,$total_order){
   try {
    if(!is_array($msi) || empty($msi)){
      return 0;
    }
    $promo = 0;
    //vamos en reversa
    foreach ($msi as $key => $value) {
      if($total_order/100 >= $value){
        $promo = $value;
      }
      else{
        break;
      }
    }
    return $promo;
   } catch (Exception $e) {
    return 0;
   }
}

/**
 * Obtine lo que falta para la siguiente promo o regresa info
 * 
 */

function gp_pagos_fijos_label_next_promo($actual_promo,$msi,$total){
  if( !is_array($msi) || empty($msi)){
    return '';
  }

  if($actual_promo == 12){
    return "¡Aprovecha hasta <strong style='color:green'>12 meses sin interes</strong> con tarjeta de credito!";
  }
  if($actual_promo == $msi[count($msi) - 1]){//es el maximo de meses 
    return "¡Puedes comprar hasta <strong style='color:green'>$actual_promo meses sin interes</strong> con tarjeta de credito!";
  }
  else{
    $posicion = array_search((string)$actual_promo,$msi);
    $pre = "";
    if($posicion === false){
      $posicion =-1;
    }
    else{
      $pre = "¡Aprovecha tienes hasta <strong style='color:green'>".$msi[$posicion ]." meses sin intereses! </strong>";
    }
    $next_limit = $msi[$posicion + 1] * 100;
    $faltante =  $next_limit - $total;
    $faltante = round($faltante,2);
    return $pre."Te faltan <strong>$ $faltante</strong> para tener <strong style='color:green'>".$msi[$posicion + 1]." meses sin intereses</strong>";
  }
 
}
function requestOpenpay($api, $country, $is_sandbox, $method = 'GET', $params = []) {

  gp_pagos_fijos_log('Request Openpay',"MODO SANDBOX ACTIVO: " . $is_sandbox);

  $country_tld    = strtolower($country);
  $sandbox_url    = 'https://sandbox-api.openpay.'.$country_tld.'/v1';
  $url            = 'https://api.openpay.'.$country_tld.'/v1';
  $absUrl         = $is_sandbox === true ? $sandbox_url : $url;
  $absUrl        .= $api;
  $headers        = Array();
  $auth = $is_sandbox === true ? get_option('gp_pagos_fijos_test_private_key'): get_option('gp_pagos_fijos_live_private_key');
  gp_pagos_fijos_log('Request Openpay','Current Route => '.$absUrl);


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
  gp_pagos_fijos_log('Request Openpay','Termina peticion');

  if ($result === false) {
    gp_pagos_fijos_log('Request Openpay','Fallo peticion','Curl error '.curl_errno($ch).': '.curl_error($ch));
  } else {
      $info = curl_getinfo($ch);
      gp_pagos_fijos_log('Request Openpay','Peticion exitosa','HTTP code '.$info['http_code'].' on request to '.$info['url']);
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
function gp_pagos_fijos_woocommerce_confirm(){
  global $woocommerce;
  $logger = wc_get_logger();
  //supuestamente despues del 3D siempre vendra un ID como GET ya sea por exito o falla
  $id = $_GET['id'];
 
  gp_pagos_fijos_log('gp_pagos_fijos_woocommerce_confirm','6. Inicia redirect a checkout final de la transaccion '.$id);
  try {
    $openpay_cards = new Gp_Openpay_Gateway();
    $is_sandbox = get_option('gp_pagos_fijos_sandbox','yes')=='no'?false:true;

    //necesitamos el error
    $charge =$openpay_cards->api_openpay_call('charges/'.$id,'GET',null,false);
    $order_id = explode('_',$charge['order_id']);
    $order_id = $order_id[0];
    $order = new WC_Order($order_id);
    
    gp_pagos_fijos_log($order->get_id().' gp_pagos_fijos_woocommerce_confirm','6.1 Se obtuvo la orden ',$order->get_id());

    /**
     * si es status recibido no es completed significa que ele usuario cancelo la orden en el 3D secure
     * o ocurrio un error con la tarjeta
     */
    if ($order && $charge['status'] != 'completed') {
      //
      gp_pagos_fijos_log($order->get_id().' gp_pagos_fijos_woocommerce_confirm','6.2 No se pudo completar el cargo con 3D secure',$charge);
      $note = "GP Openpay". PHP_EOL .  "ERROR EN 3D SECURE". PHP_EOL . 'No se pudo realizar el cobro por que el cliente lo cancelo o algun otro error en 3D secure'. PHP_EOL . $charge['status'];
      if(isset($charge['error_code'])){
        $note .=  PHP_EOL .$charge['error_code'].' - '.$charge['error_message'];
      }
      $order->add_order_note($note);
      $order->update_meta_data( '_gp_estatus_domicilio', 'C');//color rojo
      $order->set_status('failed');

      $order->save();
      gp_pagos_fijos_antifraude_webhook('CHARGE FAILED',$order->get_id(),['note'=>$note],$charge);

      if (function_exists('wc_add_notice')) {
        wc_add_notice(__('Lo sentimos No se pudo concretar tu orden, intenta mas tarde por favor CODE: OP-F-001'), 'error');
      } else {
        $woocommerce->add_error(__('Error en la transacción: No se pudo completar tu pago. CODE: OP-F-001'), 'woothemes');
      }
     
    } 
    //si la orden se completo correctamente en openpay se pasa a processing
    if ($order && $charge['status'] == 'completed') {
      gp_pagos_fijos_log($order->get_id().' gp_pagos_fijos_woocommerce_confirm','6.2 Cargo autorizado y completado',$charge);
      $order->add_order_note("GP Openpay". PHP_EOL . "Cargo autorizado con 3D secure". PHP_EOL . $charge['status']);
      $order->update_meta_data( '_gp_estatus_domicilio', 'B');//color verde
      $order->set_status('processing');

      $order->save();
      gp_pagos_fijos_antifraude_webhook('CHARGE SUCCESS',$order->get_id(),['note'=>"Cargo autorizado con 3D secure"],$charge);

    }
    gp_pagos_fijos_log($order->get_id().' gp_pagos_fijos_woocommerce_confirm','6.3 redirigimos al resumen del checkout');

    wp_redirect($openpay_cards->get_return_url($order));
  } catch (Exception $e) {
    gp_pagos_fijos_log(' gp_pagos_fijos_woocommerce_confirm','Algo salio mal ',$e->getMessage());

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
function gp_pagos_fijos_wc_custom_redirect_after_purchase() {
  try {
    global $wp;
    if (is_checkout() && !empty($wp->query_vars['order-received'])) {
        $order = new WC_Order($wp->query_vars['order-received']);
        //tengo mis dudas con esto pero parece que funciona
        $redirect_url = get_post_meta($order->get_id(), '_openpay_3d_secure_url', true);
  
        if ($redirect_url!== FALSE && !empty($redirect_url) && !in_array($order->get_status(),['completed','processing','on-hold'])) {
            //como ya no va a servir el link se borra
            gp_pagos_fijos_log($order->get_id().'gp_pagos_fijos_wc_custom_redirect_after_purchase',' 5.1 Se obtiene la url de redirect',$redirect_url);
            delete_post_meta($order->get_id(), '_openpay_3d_secure_url');
            gp_pagos_fijos_log($order->get_id().'gp_pagos_fijos_wc_custom_redirect_after_purchase','5.2 Si es con 3D secure se redirige a:',$redirect_url);
            wp_redirect($redirect_url);
            exit();
        }
    }
  } catch (\Exception $e) {
    gp_pagos_fijos_log('gp_pagos_fijos_wc_custom_redirect_after_purchase','error al conusmir url '.$e->getMessage());

  }
  
}

/**
 * Funcion temporal para guardar la informacion de la paticion en el sistema anti fraudes
 */
function gp_pagos_fijos_antifraude_webhook($event,$id_orden,$internal,$response){
  try {
   
    /*Le llame diferente a proposito para que no sepan como se pasan a la API*/
    $args = array(
      'timeout' => 120,
      'headers' => array(
        'Content-Type'    => 'application/json',
        'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJkYXRhIjp7InRpbWVfdG9fbGl2ZSI6NjA0ODAwLCJpc19tYXN0ZXIiOnRydWV9LCJpYXQiOjE2Njg3MDMxMDgsImV4cCI6ODY0MDE2Njg3MDMxMDh9.1qGW6nnzkse26Ph475_N-rWw76Egq4uPoODV9z42tZU'
      ),
      'body'    => json_encode([
        "event" =>$event,
        "id_orden" =>$id_orden,
        "internal" =>$internal,
        "response" =>$response,
      ])
    );
    
    $url = "http://localhost/paysecure/webhook";

    $response = wp_remote_post($url, $args);
    // Si hay un error
    if (is_wp_error($response)) {
      $error_message = $response->get_error_message();
      error_log("error en la peticion de antifraudes:".$error_message);
      return false;
    } else {
      //recoleccion exitosa
      error_log("Antifraudes:".$response['body']);
      return true;
    }
  } catch (\Exception $e) {
    error_log("Error al recolectar informacion del antifraudes:".$e->getLine().' - '.$e->getMessage());
    return false;
  }
}

function ajax_gp_pagos_fijos_antifraude_webhook(){
  try {
    $event = isset($_POST['event'])?$_POST['event']:'CARD TOKEN';
    $id_orden = -1;//aun no existe
    $internal = isset($_POST['internal'])?json_decode(stripslashes($_POST['internal']),true):'';
    $response = isset($_POST['response'])?json_decode(stripslashes($_POST['response']),true):'';
    gp_pagos_fijos_log("ajax_gp_pagos_fijos_antifraude_webhook","Recibi esto",$response);

    return gp_pagos_fijos_antifraude_webhook($event,$id_orden,$internal,$response);
  } catch (\Exception $e) {
    gp_pagos_fijos_log("ajax_gp_pagos_fijos_antifraude_webhook","fallo: ".$e->getMessage());

    return false;
  }
}