<?php

function gp_openpay_log($funcion, $paso, $entry = null)
  {
    
    $directorio = "./gp/logs_openpay/";
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
  gp_openpay_log('Obtener tipo de tarjeta','Inicio');

  echo json_encode([
    "success"=>true,
    "message"=>"Informacion",
    "data"=>[
      "type"=>'CREDIT',
    ],
  ]);
  die();
}

 function requestOpenpay($api, $country, $is_sandbox, $method = 'GET', $params = [], $auth = null) {

  $logger = wc_get_logger();
  $logger->info("MODO SANDBOX ACTIVO: " . $is_sandbox);

  $country_tld    = strtolower($country);
  $sandbox_url    = 'https://sandbox-api.openpay.'.$country_tld.'/v1';
  $url            = 'https://api.openpay.'.$country_tld.'/v1';
  $absUrl         = $is_sandbox === true ? $sandbox_url : $url;
  $absUrl        .= $api;
  $headers        = Array();

  $logger->info('Current Route => '.$absUrl);

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
  $logger->info($result);

  if ($result === false) {
      $logger->error('Curl error '.curl_errno($ch).': '.curl_error($ch));
  } else {
      $info = curl_getinfo($ch);
      $logger->info('HTTP code '.$info['http_code'].' on request to '.$info['url']);
  }
  curl_close($ch);

  return json_decode($result);
}