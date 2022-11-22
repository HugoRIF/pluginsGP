<?php

function gameplanet_compras_logs($funcion, $mensaje, $extra = null)
{
  $directorio = './gp/logs/gameplanet_compras_logs/';

  if (!file_exists($directorio)) {
    mkdir($directorio, 0755, true);
  }

  $tiempo = current_time('mysql');
  $fecha = strtotime($tiempo);
  $fecha_log = date('M-d', $fecha);

  $file = fopen($directorio . $fecha_log . "_gameplanet.log", "a") or fopen($directorio . $fecha_log . "_gameplanet.log", "w");

  if (is_array($extra)) {
    $extra = json_encode($extra);
  }
  $registro = $tiempo . " :: Función: " . $funcion . " || " . $mensaje . " || " . $extra . "\n";

  $bytes = fwrite($file, $registro);
  fclose($file);

  return $bytes;
}

/**
 * Peticion recibida del ajax se llama a la api y se construye una respuesta y su front
 * el get al final esta mal luego le cambio el nombre
 */
function ajax_gp_compras_get()
{
  try {
    //$params = $_POST;
    gameplanet_compras_logs("ajax_gp_compras_get", "Peticion Inicial para obtener compras");
    $comprasAPI = get_link_compras_gp();
    if($comprasAPI['success']){
      gameplanet_compras_logs("ajax_gp_compras_get", "Peticion Exitosa");
      $comprasAPI['content'] = generate_compras_table($comprasAPI['data']);
    }
    else{
      gameplanet_compras_logs("ajax_gp_compras_get", "Peticion Fallo",$comprasAPI);

      $comprasAPI['content'] = ' <h2> Mis Compras</h2><p> Aun no tienes compras</p>';

    }
      
    echo json_encode($comprasAPI);
    die();
  } catch (\Exception $e) {
    gameplanet_compras_logs("ajax_gp_compras_get", "Error interno", $e->getLine().', '.$e->getMessage());
    echo json_encode([
      "success" => false,
      "message" => "Error interno",
      "code" => 500,
      "data" => null
    ]);
    die();
  }
}

/**
 * Se consultan las compras del cliente logeado
 * endpint: http://localhost/linkgp/cliente/cuenta/historial_compras
 */
function get_link_compras_gp(){
  try {
    gameplanet_compras_logs("get_link_compras_gp", "Se hace la peticion a historial compras en link");
    $wpUser = get_current_user_id();
    /* $id_cliente = get_user_meta($wpUser, "id_gp", true);
    $gp_token = get_user_meta($wpUser, "token", true); */

    $id_cliente = "867918";
    $gp_token = "8579c9306f422d9ab64615821b5d8fac";
    $dataBody = [
      "id_cliente"=> $id_cliente,
      "gp_token"=>$gp_token,
      "dias"=>182,
      "filtro"=>"4"
    ];
    //print_r($dataBody);die();
    $args = array(
      'timeout'     => 30,
      'headers' => array(
        'Content-Type' => 'application/json',
        'Authorization' => 'Basic ' . base64_encode( get_option('user-link_gp').':'.get_option('pass-link_gp'))
      ),
      'body' => json_encode($dataBody)
    );


    $url = get_option('ruta_link_gp')."cliente/cuenta/historial_compra";
    $response = wp_remote_post($url, $args);

    // Si hay un error
    if (is_wp_error($response)) {
      $error_message = $response->get_error_message();
      gameplanet_compras_logs("get_link_compras_gp", "Error en la peticion ", $error_message);
      return [
        "success" => false,
        "message" => "Error al buscar",
        "data" => [$error_message],
      ];
    }
    gameplanet_compras_logs("get_link_compras_gp", "Peticion Completa");
    $res = json_decode($response['body'], true);
    return $res;
  } catch (\Exception $e) {
    gameplanet_compras_logs("get_link_compras_gp", "Error interno", $e->getLine().', '.$e->getMessage());
    return [
      "success" => false,
      "message" => "Error interno",
      "data" => $e->getLine().', '.$e->getMessage(),
    ];
  }
}


function ajax_compra_tracking(){
	try {
		$ticket = $_POST['ticket'];

    $responseAPI = api_call_compra_tracking($ticket);

    echo json_encode($responseAPI);
    die();
  } catch (\Exception $e) {
    echo json_encode([
      "success" => false,
      "message" => "Error interno",
      "code" => 500,
      "data" => null
    ]);
    die();
  }
}
function api_call_compra_tracking($ticket)
{

  /*Le llame diferente a proposito para que no sepan como se pasan a la API*/
  $args = array(
    'timeout'     => 30,
    'headers' => array(
      'Content-Type' => 'application/json',
      'data-jwt-master' => get_option('data-jwt-master'),
    )
  );


  $url = get_option('ruta_gameplanet') . "tracking/info/".$ticket."/0/venta";

  $response = wp_remote_get($url, $args);

  // Si hay un error
  if (is_wp_error($response)) {
    $error_message = $response->get_error_message();
    return [
      "success" => false,
      "message" => "Error al buscar",
      "data" => [$error_message],
    ];
  }
  $res = json_decode($response['body'], true);
  return $res;
}