<?php
function gameplanet_mapa_sucursales_logs($funcion, $mensaje, $extra = null)
{
  $directorio = './gp/logs/gameplanet_mapa_sucursales_logs/';

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
function ajax_mapa_get_sucursales(){
  
  try {
    $params = $_POST;
    gameplanet_mapa_sucursales_logs("Mapa Sucursales", "Inicia Mapas");
    $responseAPI = APISucursalesLista($params);
    if(!$responseAPI['success']){
      gameplanet_mapa_sucursales_logs("Mapa Sucursales", "Fallo la peticion",$responseAPI);
    }
    echo json_encode($responseAPI);
    die();
  } catch (Exception $e) {
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
 * Metodo para hacer la consulta de las tiendas
 */
function APISucursalesLista($params)
{
  gameplanet_mapa_sucursales_logs("Sucursales API", "Se hace la peticion de consulta a la API", $params);

  /*Le llame diferente a proposito para que no sepan como se pasan a la API*/

  $args = array(
    'headers' => array(
      'Content-Type' => 'application/json',
      'data-jwt-master' => get_option('data-jwt-master')
    )
  );

  $url = get_option('ruta_gameplanet') . "sucursales/lista/" . $params['lat'].'/'.$params['long'].'/'.$params['radio'];

  $response = wp_remote_get($url, $args);

  // Si hay un error
  if (is_wp_error($response)) {
    $error_message = $response->get_error_message();
    gameplanet_mapa_sucursales_logs("SUCURSALES", "Error en la peticion ", $error_message);
    return [
      "success" => true,
      "message" => "Error al consultar sucursales",
      "data" => $error_message,
    ];
  }
  gameplanet_mapa_sucursales_logs("SUCURSALES", "Peticion Completa");
  $res = json_decode($response['body'], true);
  return $res;
}