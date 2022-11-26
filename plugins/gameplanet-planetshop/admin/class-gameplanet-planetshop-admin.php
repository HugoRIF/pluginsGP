<?php

/**
 * Funcionalidad del plugin específica para el área de admin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Gameplanet_Planetshop
 * @subpackage Gameplanet_Planetshop/admin
 */

/**
 * Funcionalidad del plugin específica para el área de admin.
 *
 * Define el nombre del plugin, versión y hooks para el área de admin.
 *
 * @package    Gameplanet_Planetshop
 * @subpackage Gameplanet_Planetshop/admin
 * @author     GamePlanet
 */
class Gameplanet_Planetshop_Admin
{

	/**
	 * El ID del plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $gameplanet_planetshop    El ID del plugin.
	 */
	private $gameplanet_planetshop;

	/**
	 * La versión del plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    La versión actual del plugin.
	 */
	private $version;

	/**
	 * Inicializa la clase y define sus propiedades.
	 *
	 * @since    1.0.0
	 * @param      string    $gameplanet_planetshop       El nombre del plugin.
	 * @param      string    $version    La versión del plugin.
	 */
	public function __construct($gameplanet_planetshop, $version)
	{

		$this->gameplanet_planetshop = $gameplanet_planetshop;
		$this->version = $version;
	}

	/**
	 * Registra el CSS para el área del admin.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		wp_enqueue_style($this->gameplanet_planetshop, plugin_dir_url(__FILE__) . 'css/gameplanet-planetshop-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Registra el JavaScript para el área del admin.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		wp_enqueue_script($this->gameplanet_planetshop, plugin_dir_url(__FILE__) . 'js/gameplanet-planetshop-admin.js', array('jquery'), $this->version, false);
	}

	/**
	 * Crea las páginas para configuración de PS
	 *
	 * @since    1.0.0
	 */
	public function gp_ps_admin_menu()
	{
		// add_menu_page( "GamePlanet", "GamePlanet", 'manage_options', 'gp-admin', '', 'dashicons-games', 100);

		add_submenu_page('gp-admin', 'Planet Shop', 'Planet Shop', 'manage_options', plugin_dir_path(__FILE__) . '/partials/gameplanet-planetshop-admin-display.php', '', 3);
	}

	/**
	 * Agrega meta data a orden para webhook.
	 *
	 * @since    1.0.0
	 */
	public function gp_ps_data_new_order($order_id)
	{

		$order = new WC_Order($order_id);
		$user_id = get_post_meta($order_id, '_customer_user', true);
		$user = new WC_Customer($user_id);

		$order->set_billing_first_name($user->get_billing_first_name());
		$order->set_billing_last_name($user->get_billing_last_name());
		// $order->set_billing_address_1($user->get_billing_address_1());
		// $order->set_billing_city($user->get_billing_city());
		// $order->set_billing_state($user->get_billing_state());
		// $order->set_billing_postcode($user->get_billing_postcode());
		// $order->set_billing_country($user->get_billing_country());
		$order->set_billing_phone($user->get_billing_phone());

		$order->set_shipping_first_name($user->get_shipping_first_name());
		$order->set_shipping_last_name($user->get_shipping_last_name());
		$order->set_shipping_address_1($user->get_shipping_address_1());
		$order->set_shipping_city($user->get_shipping_city());
		$order->set_shipping_state($user->get_shipping_state());
		$order->set_shipping_postcode($user->get_shipping_postcode());
		$order->set_shipping_country($user->get_shipping_country());
		$order->set_shipping_phone($user->get_billing_phone());

		$order->update_meta_data('_gp_exterior_number', get_user_meta($user_id, '_gp_exterior_number', true));
		$order->update_meta_data('_gp_interior_number', get_user_meta($user_id, '_gp_interior_number', true));
		$order->update_meta_data('_gp_suburb', get_user_meta($user_id, '_gp_suburb', true));

		$order->save();
	}

	public function gp_ps_meta_data_order($order_id)
	{

		// $order = new WC_Order($order_id);
		// $user_id = get_post_meta($order_id, '_customer_user', true);
		// $user = new WC_Customer( $user_id );
		$order = wc_get_order($order_id);
		$user_id = get_post_meta($order_id, '_customer_user', true);
		$user = get_user_by('id', $user_id);

		if (isset($user->id_gp)) {
			$id_gp = $user->id_gp;
			$nombre = $user->first_name . " " . $user->last_name;
		} else {
			$id_gp = "0";
			$nombre = "Público en general";
		}

		$url = site_url();
		$disallowed = array('http://', 'https://');
		$dir = '';
		foreach ($disallowed as $d) {
			if (strpos($url, $d) === 0) {
				$dir = str_replace($d, '', $url);
			}
		}
		$order->update_meta_data('_gp_source', $dir);

		$arr = array('id_cliente' => $id_gp, 'nombre_cliente' => $nombre);

		$order->update_meta_data('_gp_request', json_encode($arr, JSON_UNESCAPED_UNICODE));

		$items = $order->get_items();
		foreach ($items as $key => $item) {
			$tipo = $item['_gp_id_tipo_envio'];
			if ($tipo == 'domicilio') {
				$order->update_meta_data('_gp_lat', $user->gp_lat_shipping);
				$order->update_meta_data('_gp_lng', $user->gp_lng_shipping);
				$order->update_meta_data('_gp_shipping_address', $user->gp_shipping_address);
				break;
			}
		}

		$order->save();
	}

	public function send_custom_email_notifications($order_id, $old_status, $new_status, $order)
	{
		if ($new_status == 'cancelled' || $new_status == 'failed') {
			$wc_emails = WC()->mailer()->get_emails(); // Get all WC_emails objects instances
			$customer_email = $order->get_billing_email(); // The customer email
		}

		if ($new_status == 'cancelled') {
			// change the recipient of this instance
			$wc_emails['WC_Email_Cancelled_Order']->recipient = $customer_email;
			// Sending the email from this instance
			$wc_emails['WC_Email_Cancelled_Order']->trigger($order_id);
		} elseif ($new_status == 'failed') {
			// change the recipient of this instance
			$wc_emails['WC_Email_Failed_Order']->recipient = $customer_email;
			// Sending the email from this instance
			$wc_emails['WC_Email_Failed_Order']->trigger($order_id);
		}
	}

	/**
	 * Función para hacer "ajax" de "sucursales cerca de ti"
	 */
	public function gp_ajax_sucursales()
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
		// $this->gp_ps_log('ps_disponibilidad', 'endpoint', $url);

		$response = wp_remote_get($url, $args);

		if (is_wp_error($response)) {
			$mensaje_error = $response->get_error_message();
			$this->gp_ps_log('ps_disponibilidad', 'Error WP', $mensaje_error);
			$this->gp_ps_log('ps_disponibilidad', "\n-----");
			echo json_encode("Error al obtener las tiendas. Code:PS-002");
			die();
		}

		if ($response['response']['code'] == 200) {
			// obtenemos el body
			$ext_auth = json_decode($response['body'], true); //!

			if ($ext_auth['success']) {
				$this->gp_ps_log('ps_disponibilidad', 'respuesta', $ext_auth);
				$this->gp_ps_log('ps_disponibilidad', 'fin.', "\n-----");
				echo json_encode($ext_auth['data']);
				die();
			} else {
				$this->gp_ps_log('ps_disponibilidad', 'Error', $ext_auth);
				$this->gp_ps_log('ps_disponibilidad', 'fin.', "\n-----");
				echo json_encode("Al obtener la lista de tiendas. Code:PS-009");
				die();
			}
		} else {
			$this->gp_ps_log('ps_disponibilidad', 'Error', $response['response']['code']);
			$this->gp_ps_log('ps_disponibilidad', 'fin.', "\n-----");
			echo json_encode("Al obtener la lista de tiendas. Code:PS-008");
			die();
		}
	}

	/**
	 * Función para generar logs.
	 *
	 * @since    1.0.0
	 * @param      string    $funcion    Nombre de la función.
	 * @param      string    $paso       Paso que se ejecutó.
	 * @param      mixed     $entry      (opcional) objeto que se quiera guardar.
	 * @return	   int|false verdadero si se guardó log.
	 */
	public function gp_ps_log($funcion, $paso, $entry = null)
	{
		$directorio = './gp/logs_ps/';
		$extencion = "_planet_shop.log";

		if (!file_exists($directorio)) {
			mkdir($directorio, 0755, true);
		}

		$tiempo = current_time('mysql');
		$fecha = strtotime($tiempo);
		$fecha_log = date('M-d', $fecha);

		$file = fopen($directorio . $fecha_log . $extencion, "a") or fopen($directorio . $fecha_log . $extencion, "w");

		if (is_null($entry)) {

			$registro = $tiempo . " :: Función: " . $funcion . " || Bloque: " . $paso . "\n";
		} else {

			if (is_array($entry)) {
				$entry = json_encode($entry);
			}

			$registro = $tiempo . " :: Función: " . $funcion . " || Bloque: " . $paso . " || " . $entry . "\n";
		}

		$bytes = fwrite($file, $registro);
		fclose($file);

		return $bytes;
	}


	/**
	 * Funcion para cargar el inicio de sesion dinamicamente
	 */
	public function gp_ajax_mi_cuenta_button()
	{
	
	}
	public function gp_ajax_mi_cuenta_button_no_priv()
	{
	
	}

	public function gp_ajax_disponibilidad(){
		if (class_exists('Widget_single_product_ps')) {
			$clase = new Widget_single_product_ps('', 1);
			if(method_exists($clase, 'gp_wc_disponibilidad')){
				$params = $_POST;
				$id_prod = $params['id_prod'];
				$lat_param = $params['lat'];
				$lng_param = $params['lng'];
				$addrs_long = $params['addrs_long'];
				$tienda_fav_param = $params['tienda_fav'];
				$nom_tienda_fav_param = urldecode($params['nom_tienda_fav']);

				$producto = wc_get_product( $id_prod );

				if(is_null($producto) || $producto == false){
					echo json_encode(array("
						<div id='gp_ps_contenedor_ventas' class='gp-disponibilidad-container' style='margin-bottom: 2em;'>
							<div id='gp_content' class='gp_hi' role='main'>
								<div class='row row-main'>
									<div class='large-12 col' style='padding-bottom: 0px;'>
										<div class='pr-field-wrap'>
											<div id='gp_widget'>
												<p>En este momento no pudimos verificar el inventario para este producto. Por favor, inténtelo más tarde.<span style='color: white;'>Code: PSA-006. Ref-{$id_prod}</span></p>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>",
						"",
						"",
						""
					));
					die();
				}

				if(!$producto->is_type( 'simple' )){
					echo json_encode(array(
						"<p>Error de producto.<span style='color: white;'>Code: PSA-007</span></p>",
						"",
						"",
						""
					));
					die();
				}

				$_gp_lugar_venta = $producto->get_attribute( '_gp_lugar_venta' );
				$atributos = explode(',', $_gp_lugar_venta);
				
				$bandera_ecom = false;
				$bandera_sucursal = false;

				$upc = $producto->get_sku();
				if(str_starts_with($upc, 'P')){
					$bandera_ecom = true;
					$bandera_sucursal = true;
				}

				foreach($atributos as $atributo){
					$atributo = trim($atributo, ' ');
					if('ecom' == $atributo){
						$bandera_ecom = true;
					} elseif('sucursal' == $atributo){
						$bandera_sucursal = true;
					}
				}
				
				$link_catalogo = site_url('/catalogo/?buscar_productos=1&stock=instock');
				if(!$producto->is_in_stock()){
					echo json_encode(array("
						<div id='gp_ps_error_div' class='gp_ps_fail_box'>
							<div id='fail_section_header' class='factura-fail_section_header'>
								<div class='message-container'>
									<h3 class='gp_color_red'>No disponible por el momento</h3>
								</div>
							</div>
							<div id='fail_section_response'>
								<ul id='gp_ps_ul'>
									<li>
										<p>No sabemos si este producto volverá a estar disponible, ni cuándo.</p>
										<p>Te sugerimos buscar más productos en nuestro <a class='gp_underline' href='{$link_catalogo}'>catálogo</a>.<span style='color: white;'>Code: PSA-008</span></p>
									</li>
								</ul>
							</div>
						</div>",
						"",
						"",
						""
					));
					die();
				}

				$precio_block = 0;
                if($producto->is_on_sale()){
                    $precio_block = $producto->get_sale_price();
                } else{
                    $precio_block = $producto->get_regular_price();
                }
                if($precio_block <= 0){
					echo json_encode(array("
						<div id='gp_ps_error_div' class='gp_ps_fail_box'>
							<div id='fail_section_header' class='factura-fail_section_header'>
								<div class='message-container'>
									<h3 class='gp_color_red'>No disponible por el momento</h3>
								</div>
							</div>
							<div id='fail_section_response'>
								<ul id='gp_ps_ul'>
									<li>
										<p>No sabemos si este producto volverá a estar disponible, ni cuándo.1</p>
										<p>Te sugerimos buscar más productos en nuestro <a class='gp_underline' href='{$link_catalogo}'>catálogo</a>.<span style='color: white;'>Code: PSA-003</span></p>
									</li>
								</ul>
							</div>
						</div>",
						"",
						"",
						""
					));
					die();
				}

				if(!$bandera_ecom && !$bandera_sucursal){
					$mensaje_sin_prod = "
						<div id='gp_ps_contenedor_ventas' class='gp-disponibilidad-container' style='margin-bottom: 2em;'>
							<div id='gp_content' class='gp_hi' role='main'>
								<div class='row row-main'>
									<div class='large-12 col' style='padding-bottom: 0px;'>
										<div class='pr-field-wrap'>
											<div id='gp_widget'>
												<p>Por el momento no tenemos disponible este producto en línea</p>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					";
					echo json_encode(array(
						$mensaje_sin_prod,
						"",
						"",
						""
					));
					die();
				}
				$cantidad = 1;
				$metodo = 'cache';
				$lat = $lat_param;
				$lng = $lng_param;
				$tienda_fav = $tienda_fav_param;
				if(is_user_logged_in()){
					$user = get_userdata(get_current_user_id());
					$id_gp = $user->id_gp;
				} else{
					$id_gp = '0';
				}

				$disponibilidad2 = json_decode($clase->gp_wc_disponibilidad( $cantidad, $metodo, $upc, $lat, $lng, $tienda_fav, $id_gp), true);
				if($disponibilidad2['estatus']){
					//*
					$html = '';
					$inp_type = 'hidden';
					$bandera_mensaje = false;
					
					$direccion_larga = $addrs_long;
					if(isset($_COOKIE['_gp_geo_address_long'])){
						$direccion_larga = urldecode(filter_var($_COOKIE['_gp_geo_address_long'], FILTER_SANITIZE_ENCODED));
					}
					if(count($disponibilidad2['tipos_envio']) > 0){
						$error_domicilio = '';
						$error_tienda = '';
						foreach($disponibilidad2['tipos_envio'] as $key => $envio){
							$mensaje_monto_minimo = '';
							switch($envio['id']){
								case 'domicilio':{
									if($bandera_ecom){
										if($envio['estatus']){

											$nombre = $envio['nombre'];
											$entrega = $envio['subtipo']['entrega_estimada'];
											$ship_mensaje = $envio['subtipo']['shipping']['mensaje'];

											$id_tienda = '';
											$nombre_tienda = '';
											$id_tipo_envio = '';
											$id_subtipo_envio = '';
											$entrega_estimada = '';
											$cantidad = '';
											$shipping = '';

											$monto_minimo_mensaje = '';
											$monto_minimo = '';
											foreach($envio['subtipo']['almacenes'] as $key => $valor){
												if($key == 0){
													$id_tienda = $valor['id_sucursal'];
													$nombre_tienda = $valor['nombre'];
													$id_tipo_envio = $envio['id'];
													$id_subtipo_envio = $envio['subtipo']['nombre'];
													$entrega_estimada = $entrega;
													$cantidad = $valor['cantidad'];
													$shipping = $envio['subtipo']['shipping']['valor'];
													$monto_minimo = $envio['subtipo']['monto_minimo']['valor'];
													$monto_minimo_mensaje = $envio['subtipo']['monto_minimo']['mensaje'];
													break;
												}
											}
											$hidden_inputs = '';
											if(str_starts_with($upc, 'P')){
												$mensaje_monto_minimo = "<p class='gp_c_b_green gp_margin0p3'><span>{$monto_minimo_mensaje}</span></p>";
												$hidden_inputs .= "
													<input type='{$inp_type}' name='gp_domicilio_apartalo' id='gp_domicilio_apartalo' value='{$monto_minimo}'>
												";
											}
											$hidden_inputs .= "
												<input type='{$inp_type}' name='domicilio_id_tienda' id='domicilio_id_tienda' value='{$id_tienda}'>
												
												<input type='{$inp_type}' name='domicilio_id_tipo_envio' id='domicilio_id_tipo_envio' value='{$id_tipo_envio}'>
												<input type='{$inp_type}' name='domicilio_id_subtipo_envio' id='domicilio_id_subtipo_envio' value='{$id_subtipo_envio}'>
												<input type='{$inp_type}' name='domicilio_entrega_estimada' id='domicilio_entrega_estimada' value='{$entrega_estimada}'>
												<input type='{$inp_type}' name='domicilio_cantidad' id='domicilio_cantidad' value='{$cantidad}'>
												<input type='{$inp_type}' name='domicilio_shipping' id='domicilio_shipping' value='{$shipping}'>
											";
			
											$html .= "
												<div id='bloque_domicilio'>
													<div style='margin-bottom: 2em;'>
														{$hidden_inputs}
														<span id='gp_radio_recibir_domicilio'>
															<input type='radio' id='domicilio' name='entrega' value='domicilio'>
															<label for='domicilio' id='domicilio_seleccionado'>{$envio['nombre']}</label>
														</span>
														<p class='gp_margin0p3'>
															<a id='gp_recibir_domicilio_w' href='#' class='gp_underline' target='_self'>Cambiar dirección de envío</a>
															{$mensaje_monto_minimo}
														</p>
														<p class='gp_c_b_green gp_margin0p3'><span id='gp_domicilio_tiempo_entrega'>{$entrega_estimada}</span></span>
														<p class='gp_c_b_blue gp_margin0p3'><span id='gp_domicilio_shipping'>{$ship_mensaje}</span></span>
														<p class='gp_single_product_direccion gp_margin0p3'>{$direccion_larga}</span>
													</div>
												</div>
											";
										} elseif($envio['estatus_mensaje_print']){
											$mensaje = $envio['estatus_mensaje'];
											$cambio_dir = '';
											$mensaje_cambio_dir = '';
											if(str_contains($mensaje, 'Code')){
												$bandera_mensaje = true;
												$cambio_dir = "
													<p class='gp_margin0p3'>
														<a id='gp_recibir_domicilio_w' class='gp_underline gp_margin0p3' href='#modal_disp_tiendas' target='_self'>Cambiar dirección de envío</a>
													</p>
													<p class='gp_c_b_green gp_margin0p3'><span id='gp_domicilio_tiempo_entrega'></span></p>
													<p class='gp_c_b_blue gp_margin0p3'><span id='gp_domicilio_shipping'></span></p>
													<p class='gp_single_product_direccion gp_margin0p3'>{$direccion_larga}</p>
												";
												if(str_contains($mensaje, '101')){
													$mensaje_cambio_dir = '<p>Te sugerimos <a href="#" id="gp_error_cambio_dir" class="gp_underline">cambiar la dirección de envío.</a></p>';
												}
											}
											$hide_dom = '';
											if(!$bandera_mensaje){
												$error_domicilio = "";
											} else{
												$hide_dom = " style='display: none;' ";
												$error_domicilio = "
													<li id='li_dom_mensaje'>
														<p>{$mensaje}</p>
														{$mensaje_cambio_dir}
													</li>
												";
											}
											$html .= "
												<div id='bloque_domicilio' {$hide_dom}>
													<div style='margin-bottom: 2em;'>
														<input type='{$inp_type}' name='gp_domicilio_apartalo' id='gp_domicilio_apartalo' value=''>
														<input type='{$inp_type}' name='domicilio_id_tienda' id='domicilio_id_tienda' value=''>
														
														<input type='{$inp_type}' name='domicilio_id_tipo_envio' id='domicilio_id_tipo_envio' value=''>
														<input type='{$inp_type}' name='domicilio_id_subtipo_envio' id='domicilio_id_subtipo_envio' value=''>
														<input type='{$inp_type}' name='domicilio_entrega_estimada' id='domicilio_entrega_estimada' value=''>
														<input type='{$inp_type}' name='domicilio_cantidad' id='domicilio_cantidad' value=''>
														<input type='{$inp_type}' name='domicilio_shipping' id='domicilio_shipping' value=''>
														<span class='disabled' id='gp_radio_recibir_domicilio'>
															<input style='display: none;' type='radio' id='domicilio' name='entrega' value='domicilio'>
															<label for='domicilio' id='domicilio_seleccionado'>{$envio['nombre']}</label>
														</span>
														{$cambio_dir}
													</div>
												</div>
											";
										}
									} else{
										$bandera_mensaje = true;
										$error_domicilio = "
											<li id='li_dom_mensaje'>
												Por el momento este producto no ofrece la opción de 'Entrega a domicilio'.<span style='color: white;'>Code: PSA-010</span>
											</li>
										";
									}
									$mensaje_monto_minimo = '';
									break;
								}
								case 'tienda':{
									if($bandera_sucursal){
										if($envio['estatus']){
											$nombre = $envio['nombre'];
											$entrega = $envio['subtipo']['entrega_estimada'];
											$ship_mensaje = $envio['subtipo']['shipping']['mensaje'];

											$lista = array(
												'Entrega ' => ''
											);
											$val_entrega_estimada = str_replace(array_keys($lista), $lista, $envio['subtipo']['entrega_estimada']);

											$id_tienda = '';
											$nombre_tienda = '';
											$id_tipo_envio = '';
											$id_subtipo_envio = '';
											$entrega_estimada = '';
											$cantidad = '';
											$shipping = '';

											$monto_minimo_mensaje = '';
											$monto_minimo = '';
											foreach($envio['subtipo']['almacenes'] as $key => $valor){
												if($key == 0){
													$id_tienda = $valor['id_sucursal'];
													$nombre_tienda = $valor['nombre'];
													$id_tipo_envio = $envio['id'];
													$id_subtipo_envio = $envio['subtipo']['nombre'];
													$entrega_estimada = $entrega;
													$cantidad = $valor['cantidad'];
													$shipping = $envio['subtipo']['shipping']['valor'];
													$monto_minimo = $envio['subtipo']['monto_minimo']['valor'];
													$monto_minimo_mensaje = $envio['subtipo']['monto_minimo']['mensaje'];
													break;
												}
											}
											
											$mensaje_monto_minimo = '';
											$hidden_inputs = '';
											if(str_starts_with($upc, 'P')){
												$mensaje_monto_minimo = "<p class='gp_c_b_green gp_margin0p3'><span>{$monto_minimo_mensaje}</span></p>";
												$hidden_inputs .= "
													<input type='{$inp_type}' name='gp_sucursal_apartalo' id='gp_sucursal_apartalo' value='{$monto_minimo}'>
												";
											}
											
											$hidden_inputs .= "
												<input type='{$inp_type}' name='sucursal_id_tienda' id='sucursal_id_tienda' value='{$id_tienda}'>
												<input type='{$inp_type}' name='sucursal_nombre_tienda' id='sucursal_nombre_tienda' value='{$nombre_tienda}'>
												<input type='{$inp_type}' name='sucursal_id_tipo_envio' id='sucursal_id_tipo_envio' value='{$id_tipo_envio}'>
												<input type='{$inp_type}' name='sucursal_id_subtipo_envio' id='sucursal_id_subtipo_envio' value='{$id_subtipo_envio}'>
												<input type='{$inp_type}' name='sucursal_entrega_estimada' id='sucursal_entrega_estimada' value='{$val_entrega_estimada}'>
												<input type='{$inp_type}' name='sucursal_cantidad' id='sucursal_cantidad' value='{$cantidad}'>
												<input type='{$inp_type}' name='sucursal_shipping' id='sucursal_shipping' value='{$shipping}'>
											";
											$mens_tienda_dif = "
												<div id='gp_mensaje'>
													<p>
											";
											if($tienda_fav_param != $id_tienda){
												$mens_tienda_dif .= "
													La sucursal '{$nom_tienda_fav_param}' no tiene el producto disponible, te recomendamos 'Recoger en {$nombre_tienda}'.
												";
											}
											$mens_tienda_dif .= "
													</p>
												</div>
											";
			
											$html .= "
												<div id='bloque_sucursal'>
													<div style='margin-bottom: 2em;'>
														{$hidden_inputs}
														<span id='gp_radio_recibir_sucursal'>
															<input type='radio' id='tienda' name='entrega' value='tienda'>
															<label for='tienda' id='tienda_seleccionada'>{$envio['nombre']}</label>
														</span>
														<p class='gp_margin0p3'>
															<a id='gp_cambiar_sucursal' class='gp_underline ' href='#modal_disp_tiendas' target='_self'>Recoger en otra sucursal</a>
														</p>
														{$mensaje_monto_minimo}
														<p class='gp_c_b_green gp_margin0p3'><span id='gp_sucursal_tiempo_entrega'>{$entrega_estimada}</span></span>
														<p class='gp_c_b_blue gp_margin0p3'><span id='gp_sucursal_shipping'>{$ship_mensaje}</span></span>
														{$mens_tienda_dif}
													</div>
												</div>
											";
										} elseif($envio['estatus_mensaje_print']){
											$mensaje = $envio['estatus_mensaje'];
											$bandera_mensaje = true;
											$error_tienda = "
												<li id='li_tienda_mensaje'>
													{$mensaje}
												</li>
											";
										}
									} else{
										$bandera_mensaje = true;
										$error_tienda = "
											<li id='li_tienda_mensaje'>
												Por el momento este producto no ofrece la opción de 'Recoger en sucursal apartándolo en línea', puedes ir directamente a la sucursal para comprarlo. Checa la disponibilidad en sucursal <a id='psa_click_suc' href='#'>aquí</a>.<span style='color: white;'>Code: PSA-011</span>
											</li>
										";
									}
									$mensaje_monto_minimo = '';
									break;
								}
							}
						}
					} else{
						$mensaje_sin_prod = "
							<div id='gp_ps_contenedor_ventas' class='gp-disponibilidad-container' style='margin-bottom: 2em;'>
								<div id='gp_content' class='gp_hi' role='main'>
									<div class='row row-main'>
										<div class='large-12 col' style='padding-bottom: 0px;'>
											<div class='pr-field-wrap'>
												<div id='gp_widget'>
													<p>Por el momento no tenemos disponible este producto en línea</p>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						";
						echo json_encode(array(
							$mensaje_sin_prod,
							"",
							""
						));
						die();
					}
					//*
					
					$action = esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $producto->get_permalink() ) );
					$cantidad = woocommerce_quantity_input(
						array(
							'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $producto->get_min_purchase_quantity(), $producto ),
							'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $producto->get_max_purchase_quantity(), $producto ),
							'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $producto->get_min_purchase_quantity(), // WPCS: CSRF ok, input var ok.
						),
						$producto,
						false
					);
					$valor = esc_attr( $producto->get_id() );
					$texto = esc_html( $producto->single_add_to_cart_text() );
					$cantidad = str_replace('"', "'", $cantidad);
					$ecom = ($bandera_ecom) ? 'ecom ' : '';
					$sucursal = ($bandera_sucursal) ? 'sucursal ' : '';

					if(!$producto->is_sold_individually()){
						$cantidad = "<div style='margin-bottom: 1.5em;'>Cantidad: " . $cantidad . "</div>";
					}
					
					$add_2_cart_data_btn = '{"add_to_cart_url":"' . $producto->get_permalink() . '?add-to-cart=' . $producto->get_id() . '&quantity=1", "variation_id":""}';

					$cats = get_the_terms( $producto->get_id(), 'product_cat' );
					$categorias = '';
					if($cats){
						$arreglo = [];
						foreach ($cats  as $term  ) {
							if(isset($term->name)){
								$product_cat_name = $term->name;
							} else{
								$product_cat_name = '';
							}
							array_push($arreglo, $product_cat_name);
							// break;
						}
						$categorias = implode(' - ', $arreglo);
					}

					$cond = get_the_terms( $producto->get_id(), 'pa_condicion' );
					$condicion = '';
					if($cond){
						$arreglo = [];
						foreach ($cond  as $term  ) {
							if(isset($term->name)){
								$product_tag_name = $term->name;
							} else{
								$product_tag_name = '';
							}
							array_push($arreglo, $product_tag_name);
							// break;
						}
						$condicion = implode(' - ', $arreglo);
					}

					$tags = get_the_terms( $producto->get_id(), 'pa_plataforma' );
					$plataforma = '';
					if($tags){
						$arreglo = [];
						foreach ($tags  as $term  ) {
							if(isset($term->name)){
								$product_tag_name = $term->name;
							} else{
								$product_tag_name = '';
							}
							array_push($arreglo, $product_tag_name);
							// break;
						}
						$plataforma = implode(' - ', $arreglo);
					}

					$disp_en_sucursal = '';
					if(!str_starts_with($upc, 'P')){
						$disp_en_sucursal = "
							<div style='text-align: right;'>
								<a id='disp_prod' href='' target='_self' rel='nofollow' class='gp_underline gp_disponibilidad' aria-label='' style='width: 100%;'>Ver disponibilidad en sucursal</a>
							</div>
						";
					}

					$display_mensajes = "";
					$display_form_cart = "";
					$display_border_ventas = "";
					if(!$bandera_mensaje){
						$display_mensajes = " style='display: none;' ";
					} else{
						if(!empty($error_domicilio) && !empty($error_tienda)){
							$display_form_cart = " style='display: none;' ";
							$display_border_ventas = " style='border: none;' ";
						}
					}

					$div_errores = "
						<div id='gp_ps_error_div' class='gp_ps_fail_box' {$display_mensajes}>
							<div id='fail_section_header' class='factura-fail_section_header'>
								<div class='message-container'>
									<h3 class='gp_color_red'>No disponible por el momento</h3>
								</div>
							</div>
							<div id='fail_section_response'>
								<ul id='gp_ps_ul'>
									{$error_domicilio}
									{$error_tienda}
								</ul>
							</div>
						</div>
					";
					$elemento_widget = "
						{$div_errores}
						<div id='gp_ps_contenedor_ventas' class='gp-disponibilidad-container' {$display_border_ventas}>
							<div id='gp_content' class='gp_hi' role='main'>
								<div class='row row-main'>
									<div class='large-12 col' style='padding-bottom: 0px;'>
										<div class='pr-field-wrap'>
											<div id='gp_widget'>
												<noscript>
													<div>
														Notamos que tu navegador no es compatible con JavaScript o lo tiene desactivado, por favor, asegúrate de utilizar JavaScript para una mejor experiencia.
													</div>
												</noscript>
												{$html}
												<input type='hidden' name='sku' id='sku' value='{$upc}'>
												<input type='hidden' name='categoria' id='categoria' value='{$categorias}'>
												<input type='hidden' name='condicion' id='condicion' value='{$condicion}'>
												<input type='hidden' name='plataforma' id='plataforma' value='{$plataforma}'>
												<input type='hidden' name='lat' id='lat' value='{$lat}'>
												<input type='hidden' name='lng' id='lng' value='{$lng}'>
												<form class='cart' action='{$action}' method='post' enctype='multipart/form-data' {$display_form_cart}>
													{$cantidad}
													<button id='gp_single_product_button'
													type='submit' class='gp_single_product_button_disable'
													value='{$valor}'
													data-button='{$add_2_cart_data_btn}'>
													<span id='gp_single_product_button_txt'>Selecciona tipo de entrega</span>
													</button>

												</form>
												{$disp_en_sucursal}
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					";
					$elemento_msi = "";
					$elemento_sucur_disp = "";
					$elemento_disp_prod = "";
					$gp_msi_temp = get_post_meta( $id_prod, 'gp_msi' );
					$gp_msi = '';
					
					if(is_array($gp_msi_temp) && isset($gp_msi_temp[0]) && is_numeric($gp_msi_temp[0])){
						$gp_msi = $gp_msi_temp[0];
					}

					if($gp_msi == 0){
						if($producto->is_on_sale()){
							$precio = $producto->get_sale_price();
						} else{
							$precio = $producto->get_regular_price();
						}
						if($precio > 0){
							$precio_html = get_woocommerce_currency_symbol() . number_format($precio, 2, '.', ',');
							$plazos = array(12, 9, 6, 3);
							$msi_renglon = '';
							foreach($plazos as $key => $mes){
								$costo_fin = $precio / $mes;
								$costo_fin_html = get_woocommerce_currency_symbol() . number_format($costo_fin, 2, '.', ',');
								$msi_renglon .= "
									<tr>
										<td>{$mes} meses</td>
										<td>{$costo_fin_html}</td>
										<td>Gratis</td>
										<td>{$precio_html}</td>
									</tr>
								";
							}
							$elemento_msi .= "
							<div class='gp-disponibilidad-container msi_div'>
								<label>Aplica meses sin intereses</label>
								<div class='msi_table'>
									<table>
										<tr>
											<th>Plazo</th>
											<th>Por mes</th>
											<th>Costo de<br/>financiamiento</th>
											<th>Total</th>
										</tr>
										{$msi_renglon}
									</table>
								</div>
							</div>";
			
						}
					}
					
					$html = 'Cargando ... Code: PSA-004';
					$html2 = 'Cargando ... Code: PSA-005';
					foreach($disponibilidad2['tipos_envio'] as $key => $tipo_envio){
						if($tipo_envio['id'] == 'tienda'){
							$html = '<ul>';
							$html2 = '<ul>';
							foreach($tipo_envio['subtipo']['almacenes'] as $key => $almacen){
								$color = "gp_c_gris";
								$gp_btn_tienda_selec = '';
								$value = '';
								$li_tiendas = 'li_sin_inventario';
								
								$nombre = $almacen['nombre'];
								$direccion = $almacen['direccion'];
								$cantidad = $almacen['cantidad'];


								$tel_temp = preg_replace("/[^0-9]/", "", $almacen['telefono'] );
								if(preg_match('/^([0-9]{2})([0-9]{4})([0-9]{4})$/', $tel_temp, $value_tel)) {
									// Store value in format variable
									$format = $value_tel[1] . ' ' . $value_tel[2] . ' ' . $value_tel[3];
									$telefono_href = "tel:+" . $tel_temp;
									$telefono_format = $format;
								} else {
									$telefono_href = "#";
									$telefono_format = "Teléfono inválido";
								}

								if ($cantidad > 0) {
									$color = "gp_c_verde";
									$gp_btn_tienda_selec = 'gp_btn_tienda_selec';
									$value = $almacen['id_sucursal'] . "," . $nombre . ',' . $almacen['cantidad'];
									$li_tiendas = 'li_tiendas';
								}
			
								$html .= "
									<li class='{$li_tiendas}'>
										<span class='{$gp_btn_tienda_selec}' value='{$value}'>
											<div class='row row-collapse align-left'>
												<div class='col medium-9 small-12 large-9'>
													<div class='col-inner' style='padding-right: 1em;'>
														<h3 style='margin-bottom: 0px;'>{$nombre}</h3>
														<p>{$direccion}</p>
													</div>
												</div>
												<div class='col medium-3 small-3 large-3'>
													<div class='col-inner'>
														<div style='margin-right: 1em; text-align: center;''>
														<h3 class='gp_h3_disponibilidad {$color}'>{$cantidad}</h3>
														<p class='gp_fs_p8em'>pieza(s)<br/>disponibles</p>
														</div>
													</div>
												</div>
											</div>
											<div class='row row-collapse align-left'>
												<p>
													<a href='{$telefono_href}'>Tel: {$telefono_format}</a>
												</p>
											</div>
										</span>
									</li>
			
									<hr class='hr_gp'>
								";
								$color2 = "gp_c_gris";
								if ($cantidad > 0) {
									$color2 = "gp_c_verde";
								}
								$html2 .= "
									<li class='li_disponibilidad'>
										<div class='row row-collapse align-left'>
											<div class='col medium-9 small-12 large-9'>
												<div class='col-inner' style='padding-right: 1em;'>
													<h3 style='margin-bottom: 0px;'>{$nombre}</h3>
													<p>{$direccion}</p>
												</div>
											</div>
											<div class='col medium-3 small-3 large-3'>
												<div class='col-inner'>
													<div style='margin-right: 1em; text-align: center;'>
														<h3 class='gp_h3_disponibilidad {$color2}'>{$cantidad}</h3>
														<p class='gp_fs_p8em'>pieza(s)<br/>disponibles</p>
													</div>
												</div>
											</div>
										</div>
										<div class='row row-collapse align-left'>
											<p>
												<a href='{$telefono_href}'>Tel: {$telefono_format}</a>
											</p>
										</div>
									</li>

									<hr class='hr_gp'>
								";
								
							}
							$html .= '</ul>';
							$html2 .= '</ul>';
							break;
						}
					}

					$elemento_sucur_disp .= "
						<h1>Sucursales disponibles.</h1>
						<p class='gp_fs_p8em'>Selecciona tu sucursal.</p>
						<div class='gp_modal_tiendas'>
							<span id='producto_disponible' class='gp_tiendas_disponibles'>{$html}</span>
						</div>
					";

					$elemento_disp_prod .= "
						<h1>Disponibilidad</h1>
						<div class='gp_modal_tienda'>
							<span id='status_disponibilidad_tienda'>{$html2}</span>
						</div>
					";
					
					$elementos = array(
						$elemento_widget,
						$elemento_msi,
						$elemento_sucur_disp,
						$elemento_disp_prod
					);
					echo json_encode($elementos);
				} else{
					echo json_encode(array(
						"<div id='gp_ps_contenedor_ventas' class='gp-disponibilidad-container'>
						<p>No sabemos si este producto volverá a estar disponible, ni cuándo.2</p>
						<p>Te sugerimos buscar más productos en nuestro <a class='gp_underline' href='{$link_catalogo}'>catálogo</a>.<span style='color: white;'>Code: PSA-012</span></p></div>",
						"",
						"",
						""
					));
					die();
				}

				die();
			} else{
				echo json_encode(array(
					"<p>Code: PSA-002</p>",
					"",
					"",
					""
				));
				die();
			}
		} else{
			echo json_encode(array(
				"<p>Code: PSA-001</p>",
				"",
				"",
				""
			));
			die();
		}
		
	}
}
