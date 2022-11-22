<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

function gp_sc_donde_estoy(){
	$ubicacion = '';
	$color = '';
	$telefono = '?';

	if(isset($_COOKIE['_gp_geo_address_short'])){
		$ubicacion = urldecode($_COOKIE['_gp_geo_address_short']);
	} else{
		$ubicacion = urldecode(_GP_GEO_ADDRESS_SHORT);
	}
	?>
	<li class="gp_list_menu gp_client_address">
		<a href="#modal_de" target="_self">
			<span class="gp_fs_1p2em">
				<i class="icon-map-pin-fill"></i>&nbsp;&nbsp;
			</span>
			<span id="gp_address" class="gp_direccion gp_overflow_direccion">Enviar a <?php esc_html_e($ubicacion); ?></span>
		</a>
	</li>
	<?php
	
	if( isset($_COOKIE['_gp_tienda_favorita_nombre'])){
		$tienda = $_COOKIE['_gp_tienda_favorita_nombre'];
	} else{
		$tienda = _GP_TIENDA_FAVORITA_NOMBRE;
	}
	

	?>
	<li class="gp_list_menu gp_client_store">
		<a href="#modal_tienda" target="_self">
			<!-- <span class="dashicons dashicons-store"></span>&nbsp;&nbsp; -->
			<span class="gp_fs_1p2em">
				<span class="material-symbols-outlined">in_home_mode</span>&nbsp;
			</span>
			<span id="gp_tienda" class="gp_tienda_fav gp_overflow_tienda">Recoger en <?php esc_html_e($tienda); ?></span>
		</a>
	</li>
	<?php
}

// shortcode para crear lightbox
function test_sc(){ ?>
	<a id="a_test_1" href="#modal_test" target="_self">
		<input type="radio" id="tienda_1" name="entrega_1" value="tienda">
		<label for="tienda_1">test_1</label>
	</a>
	<br>
	<input type="radio" id="tienda_2" name="entrega_1" value="tienda">
	<label for="tienda_2">test_2</label>
	<br>
	<a id="a_test_2" href="#modal_test_2" target="_self">
	</a>
		
	<br>
	<br>
	<span id="gp_single_product_button_txt">Cargando...</span>
	<div id="modal_test" class="lightbox-by-id lightbox-content lightbox-white mfp-hide" style="max-width:45em ;padding:1.5em;">
		<h1>modal test.</h1>
		<p class="gp_fs_p8em">Selecciona tu tienda.</p>
		<div class="gp_modal_tiendas">
			<span id="producto_disponible" class="gp_tiendas_disponibles">cargando...</span>
		</div>
	</div>
	<div id="modal_test_2" class="lightbox-by-id lightbox-content lightbox-white mfp-hide" style="max-width:45em ;padding:1.5em;">
		<h1>modal test 2.</h1>
		<p class="gp_fs_p8em">Selecciona tu tienda.</p>
		<div class="gp_modal_tiendas">
			<span id="producto_disponible" class="gp_tiendas_disponibles">cargando...</span>
		</div>
	</div>
			
	<?php // echo do_shortcode( '[lightbox id="newsletter-signup-link" width="75em" padding="2em" ][short_autocomplete][/lightbox]' ); ?>
	
<?php }
function gp_autocomplete(){ ?>
	<a href="" id="test_fetch">hola mundo</a>
	<br/><span id="resultado"></span>
<?php }

function gp_tienda(){ 
	
	if( isset($_COOKIE['_gp_tienda_favorita_nombre'])){
		$tienda = $_COOKIE['_gp_tienda_favorita_nombre'];
	} else{
		$tienda = _GP_TIENDA_FAVORITA_NOMBRE;
	}
	

	?>
	<li></li>
	<li>
		<a href="#modal_tienda" target="_self">
			<!-- <span class="dashicons dashicons-store"></span>&nbsp;&nbsp; -->
			<span class="gp_fs_1p2em">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-shop" viewBox="0 0 16 16">
					  <path d="M2.97 1.35A1 1 0 0 1 3.73 1h8.54a1 1 0 0 1 .76.35l2.609 3.044A1.5 1.5 0 0 1 16 5.37v.255a2.375 2.375 0 0 1-4.25 1.458A2.371 2.371 0 0 1 9.875 8 2.37 2.37 0 0 1 8 7.083 2.37 2.37 0 0 1 6.125 8a2.37 2.37 0 0 1-1.875-.917A2.375 2.375 0 0 1 0 5.625V5.37a1.5 1.5 0 0 1 .361-.976l2.61-3.045zm1.78 4.275a1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0 1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0 1.375 1.375 0 1 0 2.75 0V5.37a.5.5 0 0 0-.12-.325L12.27 2H3.73L1.12 5.045A.5.5 0 0 0 1 5.37v.255a1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0zM1.5 8.5A.5.5 0 0 1 2 9v6h1v-5a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v5h6V9a.5.5 0 0 1 1 0v6h.5a.5.5 0 0 1 0 1H.5a.5.5 0 0 1 0-1H1V9a.5.5 0 0 1 .5-.5zM4 15h3v-5H4v5zm5-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-3zm3 0h-2v3h2v-3z"/>
				</svg>&nbsp;&nbsp;
			</span>
			<span id="gp_tienda" class="gp_tienda_fav"><?php esc_html_e($tienda); ?></span>
		</a>
	</li>
<?php }

function test_disponibilidad(){
	// if (class_exists('Gameplanet_Planetshop_Public')) {
    //     $clase = new Gameplanet_Planetshop_Public('disponibilidad', '1.0.0');
    //     if(!method_exists($clase, 'gp_wc_disponibilidad')){
	// 		echo "sin metodo";
    //         return;
    //     }
    // } else{
	// 	echo "sin clase";
    //     return;
    // }
	// $response = $clase->gp_wc_disponibilidad(1, "cache", '045496882716', "19.359915", "-99.278583", 250, "0"); //* garantia porcentual    
    // $response = $clase->gp_wc_disponibilidad(1, "cache", '045496882716', "19.359915", "-99.278583", 250, "0"); //* garantia porcentual
    // $response = $clase->gp_wc_disponibilidad(1, "cache", '887256110505', "19.359915", "-99.278583", 250, "0"); //* garantia flat
    // $response = $clase->gp_wc_disponibilidad(1, "cache", 'U083717201762', "19.359915", "-99.278583", 250, "0"); //* solo tienda
    $response = gp_wc_disponibilidad2(1, "cache", 'U083717201762', "19.359915", "-99.278583", 250, "0"); //* solo tienda

    $ext_auth = json_decode($response, true);
    if($ext_auth['estatus']){
        foreach($ext_auth['tipos_envio'] as $key => $value){
            // error_log(print_r($value, true));
            $html = '';
            $gp_radio = '';
            $id_input_radio = '';
            $id_modal = '';
            $href_modal = '';
            $nombre_modal = '';
            $id_tiempo_entrega = '';
            $id_costo_envio = '';
            $direccion_domicilio = '';
            $id_mensaje = '';
            $mensaje = '';
            //*-- inputs id --
            $i_id_id_tienda = '';
            $i_id_nombre_tienda = '';
            $i_id_id_tipo_envio = '';
            $i_id_id_subtipo_envio = '';
            $i_id_entrega_estimada = '';
            $i_id_cantidad = '';
            $i_id_shipping = '';
            //*-- inputs values --
            $id_tienda = '';
            $nombre_tienda = '';
            $id_tipo_envio = '';
            $id_subtipo_envio = '';
            $entrega_estimada = '';
            $cantidad = '';
            $shipping = '';
            //*----
            if($value['estatus']){
                if(count($value['subtipo']['almacenes']) == 0){
                    continue;
                }
                $lista = array(
                    'Entrega ' => ''
                );
                $entrega_estimada = str_replace(array_keys($lista), $lista, $value['subtipo']['entrega_estimada']);
                foreach($value['subtipo']['almacenes'] as $key => $valor){
                    if($key == 0){
                        $id_tienda = $valor['id_sucursal'];
                        $nombre_tienda = $valor['nombre'];
                        $id_tipo_envio = $value['id'];
                        $id_subtipo_envio = $value['subtipo']['nombre'];
                        $entrega_estimada = $entrega_estimada;
                        $cantidad = $valor['cantidad'];
                        $shipping = $value['subtipo']['shipping']['valor'];
                        break;
                    }
                }

                if($value['id'] == 'domicilio'){
                    $direccion_larga = "";
					$nombre_tienda = "";

                    $gp_radio .= 'gp_radio_recibir_domicilio';
                    $id_input_radio = 'domicilio';
                    $id_modal = 'gp_recibir_domicilio';
                    $href_modal = 'modal_cambiar_direccion';
                    $nombre_modal = 'Cambiar dirección';
                    $id_tiempo_entrega = 'gp_domicilio_tiempo_entrega';
                    $id_costo_envio = 'gp_domicilio_shipping';
                    $direccion_domicilio = "<span class='gp_single_product_direccion'>{$direccion_larga}<br/></span>";
                    $id_mensaje = 'gp_mensaje_domicilio';

                    $i_id_id_tienda = 'domicilio_id_tienda';
                    $i_id_nombre_tienda = 'domicilio_nombre_tienda';
                    $i_id_id_tipo_envio = 'domicilio_id_tipo_envio';
                    $i_id_id_subtipo_envio = 'domicilio_id_subtipo_envio';
                    $i_id_entrega_estimada = 'domicilio_entrega_estimada';
                    $i_id_cantidad = 'domicilio_cantidad';
                    $i_id_shipping = 'domicilio_shipping';
                } elseif($value['id'] == 'tienda'){
                    if(count($value['subtipo']['almacenes']) == 0){
                        continue;
                    }
                    $gp_radio .= 'gp_radio_recibir_sucursal';
                    $id_input_radio = 'tienda';
                    $id_modal = 'gp_recibir_tienda';
                    $href_modal = 'modal_recoger_tienda';
                    $nombre_modal = 'Cambiar de sucursal';
                    $id_tiempo_entrega = 'gp_sucursal_tiempo_entrega';
                    $id_costo_envio = 'gp_sucursal_shipping';
                    $id_mensaje = 'gp_mensaje';

                    $i_id_id_tienda = 'sucursal_id_tienda';
                    $i_id_nombre_tienda = 'sucursal_nombre_tienda';
                    $i_id_id_tipo_envio = 'sucursal_id_tipo_envio';
                    $i_id_id_subtipo_envio = 'sucursal_id_subtipo_envio';
                    $i_id_entrega_estimada = 'sucursal_entrega_estimada';
                    $i_id_cantidad = 'sucursal_cantidad';
                    $i_id_shipping = 'sucursal_shipping';
                }
                if($value['estatus_mensaje_print']){
                    $mensaje = $value['estatus_mensaje'];
                }
                $html .= "
                    <input type='hidden' name='{$i_id_id_tienda}' id='domicilio_id_tienda' value='{$id_tienda}'>
                    <input type='hidden' name='{$i_id_nombre_tienda}' id='domicilio_nombre_tienda' value='{$nombre_tienda}'>
                    <input type='hidden' name='{$i_id_id_tipo_envio}' id='domicilio_id_tipo_envio' value='{$id_tipo_envio}'>
                    <input type='hidden' name='{$i_id_id_subtipo_envio}' id='domicilio_id_subtipo_envio' value='{$id_subtipo_envio}'>
                    <input type='hidden' name='{$i_id_entrega_estimada}' id='domicilio_entrega_estimada' value='{$entrega_estimada}'>
                    <input type='hidden' name='{$i_id_cantidad}' id='domicilio_cantidad' value='{$cantidad}'>
                    <input type='hidden' name='{$i_id_shipping}' id='domicilio_shipping' value='{$shipping}'>
                    <span id='{$gp_radio}'>
                        <input type='radio' id='{$id_input_radio}' name='entrega' value='{$id_input_radio}'>
                        <label for='{$id_input_radio}'>{$value['nombre']}</label>
                    </span>
                    <p>
                        <a id='{$id_modal}' href='#{$href_modal}' class='gp_underline' target='_self'>{$nombre_modal}</a><br/>
                        <span class='gp_c_b_green'><span id='{$id_tiempo_entrega}'>{$value['subtipo']['entrega_estimada']}</span></span><br/>
                        <span class='gp_c_b_blue'><span id='{$id_costo_envio}'>{$value['subtipo']['shipping']['mensaje']}</span></span><br/>
                        {$direccion_domicilio}
                    </p>
                    <div id='{$id_mensaje}'>
                        <p>{$mensaje}</p>
                    </div>
                ";
                echo $html;
                
            }
        }
    } elseif($ext_auth['estatus_mensaje_print']){
        $html = "
            <p>
                {$ext_auth['estatus_mensaje']}
            </p>
        ";
        echo $html;
    }
}

function gp_wc_disponibilidad2( $cantidad, $metodo, $upc, $lat, $lng, $tienda_fav, $id_cliente){

	$response = array(
		"estatus" => 0,
		"estatus_mensaje_print" => 0,
		"estatus_mensaje" => "",
		"upc" => $upc,
		"garantias" => null,
		"tipos_envio" => null,
		"nombre" => null,
		"fecha_lanzamiento_confirmada" => null,
		"fecha_lanzamiento" => null,
		"precio_final_confirmado" => null,
		"precio_final" => null,
		"fecha_limite_apartado" => null,
		"fecha_limite_reasignacion_tienda" => null
	);

	try {
		$id_producto = wc_get_product_id_by_sku( $upc );

		if ( empty( $id_producto ) ) {
			throw new Exception('producto inexistente');
		}   
	} catch ( Exception $e ) {
		$response["estatus_mensaje_print"] = 1;
		$response["estatus_mensaje"] = "El producto no existe en nuestro catálogo. Code: F-001";
		return json_encode($response);
	}
	
	$producto = wc_get_product( $id_producto );
	$precio_producto = $producto->get_price();
	
	if ( !$producto->is_in_stock() ) {
		$response["estatus_mensaje_print"] = 1;
		$response["estatus_mensaje"] = "Por el momento este producto lo tenemos agotado, puedes buscar más productos relacionados ó parecidos a este producto en nuestro catálogo. Code: F-002";
		return json_encode($response);
	}

	$surtidor = 'GAM';
	$tipo_producto = 'fisico';
	$datos_domicilio = array(
		'express' => array(
			'solicitud' => $cantidad,
			'metodo' => $metodo,
			'cantidad_min' => 1
		),
		'sameday' => array(
			'solicitud' => $cantidad,
			'metodo' => $metodo,
			'cantidad_min' => 1
		),
		'nextday' => array(
			'solicitud' => $cantidad,
			'metodo' => $metodo,
			'cantidad_min' => 1
		),
		'standard' => array(
			'solicitud' => $cantidad,
			'metodo' => $metodo,
			'cantidad_min' => 1
		)
	);
	$datos_tienda = array(
		'apartado' => array(
			'solicitud' => $cantidad,
			'metodo' => $metodo,
			'cantidad_min' => 1
		)
	);

	if(str_starts_with($upc, 'P')){
		$tipo_producto = 'preventa';
		$datos_domicilio = array(
			'preventa' => array(
				'solicitud' => $cantidad,
				'metodo' => $metodo,
				'cantidad_min' => 1
			)
		);
		$datos_tienda = array(
			'preventa' => array(
				'solicitud' => $cantidad,
				'metodo' => $metodo,
				'cantidad_min' => 1
			)
		);
	}

	$site = site_url();
	$disallowed = array('http://', 'https://');
	$origen = '';
	foreach($disallowed as $dis) {
		if(strpos($site, $dis) === 0) {
			$origen = str_replace($dis, '', $site);
		}
	}

	$request = array(
		'productos' => array(
			array(
				'upc' => $upc,
				'surtidor' => $surtidor,
				'origen' => $origen,
				'tipo' => $tipo_producto,
				'lat' => $lat,
				'lng' => $lng,
				'id_tienda_favorita' => $tienda_fav,
				'id_tienda_seleccionada' => $tienda_fav,
				"id_cliente" => $id_cliente,
				'domicilio' => $datos_domicilio,
				'tienda' => $datos_tienda,
			)
		)
	);

	$args = array(
		'body' => json_encode($request),
		'headers' => array(
			'Content-Type' => 'application/json',
			'data'         => get_option('data-tendero')
		)
	);
	$url = get_option('ruta_tendero') . "producto/disponibilidad2";

	$wp_response = wp_remote_post($url, $args);
	if (is_wp_error($wp_response)) {
		$mensaje_error = $wp_response->get_error_message();
		$response["estatus_mensaje_print"] = 1;
		$response["estatus_mensaje"] = 'Por el momento este producto no lo ofrecemos a la venta en línea, puedes consultar la disponibilidad en sucursales y/o buscar más productos relacionados a este en nuestro catálogo. Code: F-003';
		return json_encode($response);
	}

	if ($wp_response['response']['code'] == 200) {
		// obtenemos el body
		$ext_auth = json_decode($wp_response['body'], true);

		// Success == true
		if ($ext_auth['success']) {

			// cachamos el codigo ( 0 == operacion exitosa)
			if ($ext_auth['code'] == 0) {
				$domicilio = $ext_auth['result'][0]['domicilio'];
				$tienda = $ext_auth['result'][0]['tienda'];
				$info_garantias = $ext_auth['result'][0]['garantias'];
				$garantias = [];
				
				if(!empty($info_garantias)){
					foreach($info_garantias as $garantia_individual){
						if($precio_producto >= $garantia_individual['precio_minimo'] && $garantia_individual['precio_maximo'] >= $precio_producto){
							$costo = null;
							$calculo_tipo = $garantia_individual['calculo_tipo'];
							switch($calculo_tipo){
								case 'F':{
									$costo = round($garantia_individual['calculo_monto'], 0, PHP_ROUND_HALF_UP);
									break;
								}
								case 'P':{
									$porc = $garantia_individual['calculo_monto'];
									$porc_valor = ($precio_producto * $porc) / 100;
									$costo = round($porc_valor, 0, PHP_ROUND_HALF_UP);
									break;
								}
							}
	
							if(!is_null($costo)){
								$garantias[] = array(
									'upc' => $garantia_individual['upc'],
									'nombre' => $garantia_individual['vigencia'] . " año(s) - " . $garantia_individual['nombre'] . " <a target='_blank' href='" . $garantia_individual['politicas'] . "'>Más información</a>",
									'costo' => $costo,
								);
							}
						}
					}
				}

				if(!empty($garantias)){
					$garantias[] = array(
						'upc' => "",
						'nombre' => "No gracias, no deseo proteger mi producto",
						'costo' => "Sin costo",
					);
				}
				$response['garantias'] = $garantias;
				if($tipo_producto == 'preventa'){
					$response['nombre'] = $ext_auth['result'][0]['nombre'];
					$response["fecha_lanzamiento_confirmada"] = $ext_auth['result'][0]["fecha_lanzamiento_confirmada"];
					$response["fecha_lanzamiento"] = $ext_auth['result'][0]["fecha_lanzamiento"];
					$response["precio_final_confirmado"] = $ext_auth['result'][0]["precio_final_confirmado"];
					$response["precio_final"] = $ext_auth['result'][0]["precio_final"];
					$response["fecha_limite_apartado"] = $ext_auth['result'][0]["fecha_limite_apartado"];
					$response["fecha_limite_reasignacion_tienda"] = $ext_auth['result'][0]["fecha_limite_reasignacion_tienda"];
				}
				
				$env_domicilio = [];
				foreach($domicilio as $key => $tipo){
					foreach($tipo['almacenes'] as $key => $almacen){
						if($almacen['cantidad'] >= $cantidad){
							if($tipo['subtipo'] == 'preventa'){
								if(isset($ext_auth['result'][0]['precio_final_confirmado']) && $ext_auth['result'][0]['precio_final_confirmado']){
									$entrega_estim = $tipo['disponibilidad']['entrega_estimada'];
									if(isset($ext_auth['result'][0]['fecha_lanzamiento_confirmada']) && !$ext_auth['result'][0]['fecha_lanzamiento_confirmada']){
										$entrega_estim = 'no definida';
										
										continue;
									}
									$env_domicilio += array(
										$tipo['subtipo'] =>  array(
											'id_tipo_envio' => 'domicilio',
											'id_subtipo_envio' => $tipo['subtipo'],
											'shipping' => $tipo['shipping'],
											'id_tienda' => $almacen['id'],
											'nombre_tienda' => gp_short_name($almacen['nombre']),
											'ubicacion' => $almacen['ubicacion'],
											'entrega_estimada' => $entrega_estim,
											'disponible' => $tipo['disponible'],
											'cantidad' => $almacen['cantidad'],
											'precio_final' => $ext_auth['result'][0]['precio_final'],
											'fecha_limite_apartado' => $ext_auth['result'][0]['fecha_limite_apartado'],
											'monto_minimo' => $tipo['monto_minimo']
										)
									);
								}
							} else{
								$env_domicilio += array(
									$tipo['subtipo'] =>  array(
										'id_tipo_envio' => 'domicilio',
										'id_subtipo_envio' => $tipo['subtipo'],
										'shipping' => $tipo['shipping'],
										'id_tienda' => $almacen['id'],
										'nombre_tienda' => gp_short_name($almacen['nombre']),
										'ubicacion' => $almacen['ubicacion'],
										'entrega_estimada' => $tipo['disponibilidad']['entrega_estimada'],
										'disponible' => $tipo['disponible'],
										'cantidad' => $almacen['cantidad'],
										'precio_final' => 0,
										'fecha_limite_apartado' => '',
										'monto_minimo' => 0
									)
								);
							}
							break;
						}
					}
				}
				$opcion = '';
				if(isset($env_domicilio['preventa'])){
					$opcion = 'preventa';
				} elseif(isset($env_domicilio['standard'])){
					$opcion = 'standard';
				} elseif(isset($env_domicilio['nextday'])){
					$opcion = 'nextday';
				} elseif(isset($env_domicilio['sameday'])){
					$opcion = 'sameday';
				} elseif(isset($env_domicilio['express'])){
					$opcion = 'express';
				}

				$estatus_domicilio = 0;
				if($opcion){
					//! cálculo de costos
					$estatus_domicilio = 1;
					$seguro_envio = 0;
					$costo_envalaje = 0;
					$costo_manejo = 0;
					$costo_activacion = 0;
					$monto_minimo = 0;
					//! ----

					// $test = $env_domicilio[$opcion];
					$response['tipos_envio'][] = array(
						'id' => "domicilio",
						'nombre' => "Entrega a domicilio",
						'estatus' => $estatus_domicilio,
						'estatus_mensaje_print' => 0,
						'estatus_mensaje' => "Disponible para domicilio",
						'subtipo' => array(
							'nombre' => $opcion,
							'entrega_estimada' => "Entrega " . $env_domicilio[$opcion]['entrega_estimada'],
							'shipping' => array(
								'valor' => $env_domicilio[$opcion]['shipping'],
								'mensaje' => "Costo envío: $" . $env_domicilio[$opcion]['shipping'],
							),
							'seguro_envio' => array(
								'valor' => $seguro_envio,
								'mensaje' => "Seguro envío: $" . $seguro_envio,
							),
							'costo_envalaje' => array(
								'valor' => $costo_envalaje,
								'mensaje' => "Costo envalaje: $" . $costo_envalaje,
							),
							'costo_manejo' => array(
								'valor' => $costo_manejo,
								'mensaje' => "Costo manejo: $" . $costo_manejo,
							),
							'costo_activacion' => array(
								'valor' => $costo_activacion,
								'mensaje' => "Costo manejo: $" . $costo_activacion,
							),
							'monto_minimo' => array(
								'valor' => $monto_minimo,
								'mensaje' => "Apártalo con: $" . $monto_minimo,
							),
							'almacenes' => array( array(
								'id_sucursal' => $env_domicilio[$opcion]['id_tienda'],
								'nombre' => gp_short_name($env_domicilio[$opcion]['nombre_tienda']),
								'cantidad' => $env_domicilio[$opcion]['cantidad'],
								'ubicacion' => $env_domicilio[$opcion]['ubicacion'],
								'direccion' => '',
								'telefono' => '',
								'horarios' => array(),
							)
							),
						),
						'shipping_cart' => 1,
						'seguro_envio_cart' => 0,
						'costo_embalaje_cart' => 0,
						'costo_manejo_cart' => 0,
						'costo_activacion_cart' => 0,
						'monto_minimo_cart' => 0,
						'garantia_cart' => 1,
					);
				} else{
					$response['tipos_envio'][] = array(
						'id' => "domicilio",
						'nombre' => "Entrega a domicilio",
						'estatus' => $estatus_domicilio,
						'estatus_mensaje_print' => 0,
						'estatus_mensaje' => "Este producto no está disponible a domicilio",
						'subtipo' => null,
						'shipping_cart' => 0,
						'seguro_envio_cart' => 0,
						'costo_embalaje_cart' => 0,
						'costo_manejo_cart' => 0,
						'costo_activacion_cart' => 0,
						'monto_minimo_cart' => 0,
						'garantia_cart' => 0,
					);

				}

				//!
				$disp_tienda = [];
				$lista_almacenes = [];
				// error_log(print_r($tienda, true));
				foreach($tienda as $key => $tipo){
					$nota = '';
					$nombre = '';
					$bandera_nota = 0;
					$monto_minimo_cart = 0;
					$estatus_tienda = 1;
					
					//! calcular costos
					$seguro_envio = 0;
					$costo_envalaje = 0;
					$costo_manejo = 0;
					$costo_activacion = 0;
					$monto_minimo = 0;
					
					if($tipo_producto == 'preventa'){
						$monto_minimo_cart = 0;
						$monto_minimo = $tipo['monto_minimo'];
						
					}
					//! ----

					$subtipo = '';
					if(isset($tipo['subtipo'])){
						$subtipo = $tipo['subtipo'];
					}
					$shipping = 0;
					if(isset($tipo['shipping'])){
						$shipping = $tipo['shipping'];
					}
					$entrega_estimada = '';
					if(isset($tipo['disponibilidad']['entrega_estimada'])){
						$entrega_estimada = $tipo['disponibilidad']['entrega_estimada'];
					}
					foreach($tipo['almacenes'] as $key => $almacen){
						if($key == 0){
							$nombre = gp_short_name($almacen['nombre']);
							$id_tienda = $almacen['id'];
							if($almacen['id'] != $tienda_fav){
								$nota = "La sucursal que tienes seleccionada de forma predeterminada no tiene el producto disponible, te recomendamos recogerlo en '" . $nombre . "'.";
								$bandera_nota = 1;
							}
						}
						$lista_almacenes[] = array(
							'id_sucursal' => $almacen['id'],
							'nombre' => gp_short_name($almacen['nombre']),
							'cantidad' => $almacen['cantidad'],
							'ubicacion' => $almacen['ubicacion'],
							'direccion' => $almacen['direccion'],
							'telefono' => $almacen['telefono'],
							'horarios' => $almacen['horarios']
						);
					}
					if(count($lista_almacenes) == 0){
						$estatus_tienda = 0;
						$bandera_nota = 0;
						$nota = 'Sin almacenes';
					}
					$disp_tienda[] = array(
						'id' => "tienda",
						'nombre' => "Recoger en " . $nombre,
						'estatus' => $estatus_tienda,
						'estatus_mensaje_print' => $bandera_nota,
						'estatus_mensaje' => $nota,
						'subtipo' => array(
							'nombre' => $subtipo,
							'entrega_estimada' => "Entrega " . $entrega_estimada,
							'shipping' => array(
								'valor' => $shipping,
								'mensaje' => "Costo envío: $" . $shipping,
							),
							'seguro_envio' => array(
								'valor' => $seguro_envio,
								'mensaje' => "Seguro envío: $" . $seguro_envio,
							),
							'costo_envalaje' => array(
								'valor' => $costo_envalaje,
								'mensaje' => "Costo envalaje: $" . $costo_envalaje,
							),
							'costo_manejo' => array(
								'valor' => $costo_manejo,
								'mensaje' => "Costo manejo: $" . $costo_manejo,
							),
							'costo_activacion' => array(
								'valor' => $costo_activacion,
								'mensaje' => "Costo manejo: $" . $costo_activacion,
							),
							'monto_minimo' => array(
								'valor' => $monto_minimo,
								'mensaje' => "Apártalo con: $" . $monto_minimo,
							),
							'almacenes' => $lista_almacenes,
						),
						'shipping_cart' => 1,
						'seguro_envio_cart' => 0,
						'costo_embalaje_cart' => 0,
						'costo_manejo_cart' => 0,
						'costo_activacion_cart' => 0,
						'monto_minimo_cart' => $monto_minimo_cart,
						'garantia_cart' => 1,
					);
				}
				//*
				// $disp_tienda
				foreach($disp_tienda as $valor){
					$response['tipos_envio'][] = $valor;
				}
				//!

				if($estatus_tienda || $estatus_domicilio){
					$response["estatus"] = 1;
					if($estatus_tienda && $estatus_domicilio){
						$response["estatus_mensaje"] = "Disponible en todas las modalidades";
					} elseif($estatus_tienda){
						$response["estatus_mensaje"] = "Disponible solo en sucursal";
					} else{
						$response["estatus_mensaje"] = "Disponible solo a domicilio";
					}
				}

			} else{
				// código 
				$response["estatus_mensaje"] = "Código de respuesta: " . $ext_auth['code'];
				$response["estatus_mensaje"] = 'Por el momento este producto no lo ofrecemos a la venta en línea, puedes consultar la disponibilidad en sucursales y/o buscar más productos relacionados a este en nuestro catálogo. Code: F-006';
				return json_encode($response);
			}
		} else{
			// success false
			$response["estatus_mensaje"] = 'Por el momento este producto no lo ofrecemos a la venta en línea, puedes consultar la disponibilidad en sucursales y/o buscar más productos relacionados a este en nuestro catálogo. Code: F-005';
			return json_encode($response);
		}
	} else{
		// respuesta http
		$response["estatus_mensaje"] = 'Por el momento este producto no lo ofrecemos a la venta en línea, puedes consultar la disponibilidad en sucursales y/o buscar más productos relacionados a este en nuestro catálogo. Code: F-004';
		return json_encode($response);
	}

	wp_localize_script(
		'gameplanet-planetshop',
		'var_disponibilidad', array(
			'disp'    => json_encode($response)
		)
	);

	return json_encode($response);
}