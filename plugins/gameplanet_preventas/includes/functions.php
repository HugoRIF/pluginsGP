<?php

function gameplanet_preventas_logs($funcion, $mensaje, $extra = null)
{
  $directorio = './gp/logs/gameplanet_preventas_logs/';

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
	 * Función para hacer "ajax" de "sucursales cerca de ti"
	 */
	function ajax_sucursales_preventas()
	{

		$params = $_POST;
		$latitud = $params['lat'];
		$longitud = $params['lng'];


		// $url = get_option('ruta_bridge') . "tiendas/list/lat/$latitud/lng/$longitud/radius/1000000";
		$url = get_option('ruta_gameplanet') . "sucursales/lista/$latitud/$longitud/1000000";
		$args = array(
			'headers' => array(
				'Content-Type' => 'application/json',
				'data-jwt-master' => get_option('data-jwt-master')
			)
		);

		$response = wp_remote_get($url, $args);

		if (is_wp_error($response)) {
			$mensaje_error = $response->get_error_message();
			echo json_encode("Error al obtener las tiendas. Code:PS-002");
			die();
		}

		if ($response['response']['code'] == 200) {
			// obtenemos el body
			$ext_auth = json_decode($response['body'], true); //!

			if ($ext_auth['success']) {
        $data = $ext_auth['data'];
        $sucrusales_admiten_preventa = [];
        foreach ($data as $sucursal) {
          if(isset($sucursal['servicios'])){
            //no me gusta hacer esto anidado
            foreach ($sucursal['servicios'] as $servicio) {
              //tienda que admite una preventa
              if($servicio['id_tipo_envio'] == 'tienda' && $servicio['id_subtipo_envio'] == 'preventa'){
                $sucrusales_admiten_preventa[] = $sucursal;
              }
            }
          }
        }
				echo json_encode($sucrusales_admiten_preventa);
				die();
			} else {
		
				echo json_encode("Al obtener la lista de tiendas. Code:PS-009");
				die();
			}
		} else {
		
			echo json_encode("Al obtener la lista de tiendas. Code:PS-008");
			die();
		}
	}
/**
 * Peticion recibida del ajax se llama a la api y se contrulle una respuesta y su front
 */
function ajax_gp_preventas_get()
{
  try {
    //$params = $_POST;
    gameplanet_preventas_logs("ajax_gp_preventas_get", "Peticion Inicial para obtener preventas");
    $preventasAPI = get_link_preventas_gp();
    if($preventasAPI['success']){
      gameplanet_preventas_logs("ajax_gp_preventas_historial_abonos_get", "Paticion Exitosa");
      $preventasAPI['content'] = generate_preventas_table($preventasAPI['data']);
    }
    else{
      gameplanet_preventas_logs("ajax_gp_preventas_historial_abonos_get", "Paticion Fallo",$preventasAPI);

      $preventasAPI['content'] = ' <h2> Mis Preventas</h2><p> Todavia no tines preventas, puedes ver las preventas disponibles para comprar aquí <a href="/atributo-producto/condicion/preventa/">Ver Preventas</a></p>';

    }
      
    echo json_encode($preventasAPI);
    die();
  } catch (\Exception $e) {
    gameplanet_preventas_logs("ajax_gp_preventas_get", "Error interno", $e->getLine().', '.$e->getMessage());
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
 * Se consultan las preventas del cliente logeado
 * endpint: http://localhost/linkgp/cliente/cuenta/historial_preventas
 */
function get_link_preventas_gp(){
  try {
    $wpUser = get_current_user_id();
    $id_cliente = get_user_meta($wpUser, "id_gp", true);
    $gp_token = get_user_meta($wpUser, "token", true);

  /*   $id_cliente = "1300824";
    $gp_token = "40d7c6d403419997b25e80f719fd85a0"; */
    gameplanet_preventas_logs("get_link_preventas_gp", "Se hace la peticion a historial preventas en link", $id_cliente);

    $args = array(
      'timeout'     => 30,
      'headers' => array(
        'Content-Type' => 'application/json',
        'Authorization' => 'Basic ' . base64_encode( get_option('user-link_gp').':'.get_option('pass-link_gp'))
      ),
      'body' => json_encode([
        "id_cliente"=> $id_cliente,
        "gp_token"=>$gp_token,
        "filtro"=>"4"
      ])
    );


    $url = get_option('ruta_link_gp')."cliente/cuenta/historial_preventas";
    $response = wp_remote_post($url, $args);

    // Si hay un error
    if (is_wp_error($response)) {
      $error_message = $response->get_error_message();
      gameplanet_preventas_logs("get_link_preventas_gp", "Error en la peticion ", $error_message);
      return [
        "success" => false,
        "message" => "Error al buscar",
        "data" => [$error_message],
      ];
    }
    gameplanet_preventas_logs("get_link_preventas_gp", "Peticion Completa");
    $res = json_decode($response['body'], true);
    return $res;
  } catch (\Exception $e) {
    gameplanet_preventas_logs("get_link_preventas_gp", "Error interno", $e->getLine().', '.$e->getMessage());
    return [
      "success" => false,
      "message" => "Error interno",
      "data" => $e->getLine().', '.$e->getMessage(),
    ];
  }
}

/**
 * Consultamos el historial de abonos de la preventa
 * 
 */
function ajax_gp_preventas_historial_abonos()
{
  try {
    $params = $_POST;
    gameplanet_preventas_logs("ajax_gp_preventas_historial_abonos_get", "Peticion Inicial para obtener historial de abonos");
    $preventasAPI = get_link_preventas_historial_abonos_gp($params['transaction']);
    if($preventasAPI['success']){
      gameplanet_preventas_logs("ajax_gp_preventas_historial_abonos_get", "Peticion exitosa");

      $preventasAPI['content'] = generate_preventas_abonos_table($preventasAPI['data']);
    }
    else{
      gameplanet_preventas_logs("ajax_gp_preventas_historial_abonos_get", "Paticion Fallo",$preventasAPI);

      $preventasAPI['content'] = '';

    }
      
    echo json_encode($preventasAPI);
    die();
  } catch (\Exception $e) {
    gameplanet_preventas_logs("ajax_gp_preventas_get", "Error interno", $e->getLine().', '.$e->getMessage());
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
 * Se consultan las preventas del cliente logeado
 * endpint: http://localhost/linkgp/cliente/cuenta/historial_preventas
 */
function get_link_preventas_historial_abonos_gp($transaction){
  try {
    gameplanet_preventas_logs("get_link_preventas_historial_abonos_gp", "Se hace la peticion a historial de abonos en preventas en link",$transaction);
  
    //esta peticion no admite json solo form-data
    $args = array(
      'timeout'     => 30,
      'headers' => array(
        'Authorization' => 'Basic ' . base64_encode( get_option('user-link_gp').':'.get_option('pass-link_gp'))
      ),
      'body' => [
        "id_transaccion"=> $transaction,
        "orden"=>"DESC",
      ]
    );


    $url = get_option('ruta_link_gp')."cliente/preventas/list_abonos";
    $response = wp_remote_post($url, $args);

    // Si hay un error
    if (is_wp_error($response)) {
      $error_message = $response->get_error_message();
      gameplanet_preventas_logs("get_link_preventas_historial_abonos_gp", "Error en la peticion ", $error_message);
      return [
        "success" => false,
        "message" => "Error al buscar",
        "data" => [$error_message],
      ];
    }
    gameplanet_preventas_logs("get_link_preventas_historial_abonos_gp", "Peticion Completa");
    $res = json_decode($response['body'], true);
    return $res;
  } catch (\Exception $e) {
    gameplanet_preventas_logs("get_link_preventas_historial_abonos_gp", "Error interno", $e->getLine().', '.$e->getMessage());
    return [
      "success" => false,
      "message" => "Error interno",
      "data" => $e->getLine().', '.$e->getMessage(),
    ];
  }
}
function generate_preventas_abonos_table($abonos){
  ob_start();
  if(count($abonos)){
    ?>
    <table class="shop_table shop_table_responsive historial-abonos-table">
      <thead>
        <tr>
          <th class="historial-abonos-table_header">
            <span class="">#</span>
          </th>
          <th class="historial-abonos-table_header">
            <span class="">Nombre Transacción</span>
          </th>
          <th class="historial-abonos-table_header">
            <span class="" >No. Ticket</span>
          </th>
          <th class="historial-abonos-table_header" style="min-width:200px">
            <span class="" >Fecha</span>
          </th>
          <th class="historial-abonos-table_header"style="min-width:120px">
            <span class=""  >Monto</span>
          </th>
          <th class="historial-abonos-table_header" style="min-width:150px">
            <span class="">Sucursal</span>
          </th>
          <th class="historial-abonos-table_header" style="min-width:150px">
            <span class="">Movimiento</span>
          </th>
        </tr>
      </thead>
      <tbody>
      <?php 
        $total_abonado =0;
        $total_abonos =0;
        foreach ($abonos as $key => $abono) {
          $total_abonos++;  
          $total_abonado += $abono['monto'];  
        ?>
        <tr>
          <td data-title="#"><?php echo ($key+1)?></td>
          <td data-title="transaccion"><?php echo $abono['nombre_transaccion']?></td>
          <td data-title="ticket" ><?php echo $abono['transaccion']?></td>
          <td data-title="fecha" ><?php echo $abono['fecha_movimiento']?></td>
          <td data-title="monto" style="text-align:right">$ <?php echo $abono['monto']?></td>
          <td data-title="sucursal" ><?php echo $abono['nombre_tienda']?></td>
          <td data-title="Movimiento"><?php echo $abono['movimiento']?></td>
        </tr>
      <?php }?>
      </tbody>
      <tfoot>
        <tr >
            <td colspan="3" style="text-align:left !important"><strong>Total de Abonos: </strong><?php echo ($total_abonos)?></td>
            <td ></td>
            <td style="text-align:right">$ <?php echo ($total_abonado==0?'0.00':$total_abonado)?></td>
            <td></td>
            <td></td>
          </tr>
      </tfoot>
    </table>
    <?php
  }
  else{
    ?> 
    <p>Parece que no hay abonos, intenta de nuevo o más tarde</p>
  <?php
  }
  return ob_get_clean();
}

/********** REASIGNACION DE TIENDA  */

/**
 * Recibimos del front la peticion de reasignacion de tienda
 * @param int id_saldo
 * @param int id_tienda_origen
 * @param int id_tienda_destino
 */
function ajax_gp_preventas_reasignar_tienda()
{
  try {
    gameplanet_preventas_logs("ajax_gp_preventas_reasignar_tienda", "Empieza reasignacion de tienda");

    $params = $_POST;
    //validacion de parametros
    if((!isset($params['id_saldo']) || empty($params['id_saldo'])) || (!isset($params['id_tienda_origen']) || empty($params['id_tienda_origen'])) || (!isset($params['id_tienda_destino']) || empty($params['id_tienda_destino']))){
      gameplanet_preventas_logs("ajax_gp_preventas_reasignar_tienda", "parametros fallaron",$params);
      wc_add_notice('No se puedo reasignar, parametros invalidos','error');
      echo json_encode([
        "success" => false,
        "message" => "Parametros invalidos",
        "code" => 409,
        "data" => $params
      ]);
      die();
    }

    $preventasReasignaAPI = reasignar_preventa_api($params);
    if($preventasReasignaAPI['success']){
      gameplanet_preventas_logs("ajax_gp_preventas_reasignar_tienda", "Peticion exitosa");
      wc_add_notice('Se reasigno la tienda de tu preventa','success');
    }
    else{
      gameplanet_preventas_logs("ajax_gp_preventas_reasignar_tienda", "Paticion Fallo",$preventasReasignaAPI);
      wc_add_notice('No se pudo reasignar tu preventa: '.$preventasReasignaAPI['message'],'error');
    }
      
    echo json_encode($preventasReasignaAPI);
    die();
  } catch (\Exception $e) {
    gameplanet_preventas_logs("ajax_gp_preventas_reasignar_tienda", "Error interno", $e->getLine().', '.$e->getMessage());
    wc_add_notice('No se pudo reasignar tu preventa: CODE-500 Error interno','error');
   
    echo json_encode([
      "success" => false,
      "message" => "Error interno",
      "code" => 500,
      "data" => null
    ]);
    die();
  }
}

function reasignar_preventa_api($params){
  try {
    gameplanet_preventas_logs("reasignar_preventa_api", "Se hace la peticion areasignar tienda de preventa",$params);
    $wpUser = get_current_user_id();
    $id_cliente = get_user_meta($wpUser, "id_gp", true);
    $gp_token = get_user_meta($wpUser, "token", true);

    /* $id_cliente = "1300824";
    $gp_token = "40d7c6d403419997b25e80f719fd85a0"; */
    //esta peticion no admite json solo form-data
    $args = array(
      'timeout'     => 30,
      'headers' => array(
        'Authorization' => 'Basic ' . base64_encode( get_option('user-link_gp').':'.get_option('pass-link_gp'))
      ),
      'body' => [
        "id_cliente"=> $id_cliente,
        "gp_token"=>  $gp_token,
        "id_saldo"=>$params['id_saldo'],
        "id_tienda_reasignada"=>$params['id_tienda_destino'],
        "id_tienda_origen"=>$params['id_tienda_origen'],
      ]
    );


    $url = get_option('ruta_link_gp')."cliente/preventas/reasignar_tienda";
    $response = wp_remote_post($url, $args);

    // Si hay un error
    if (is_wp_error($response)) {
      $error_message = $response->get_error_message();
      gameplanet_preventas_logs("reasignar_preventa_api", "Error en la peticion ", $error_message);
      return [
        "success" => false,
        "message" => "Error al buscar",
        "data" => [$error_message],
      ];
    }
    gameplanet_preventas_logs("reasignar_preventa_api", "Peticion Completa");
    $res = json_decode($response['body'], true);
    return $res;
  } catch (\Exception $e) {
    gameplanet_preventas_logs("reasignar_preventa_api", "Error interno", $e->getLine().', '.$e->getMessage());
    return [
      "success" => false,
      "message" => "Error interno",
      "data" => $e->getLine().', '.$e->getMessage(),
    ];
  }
}