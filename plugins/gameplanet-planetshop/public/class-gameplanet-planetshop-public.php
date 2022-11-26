<?php

/**
 * La funcionalidad del área pública del plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Gameplanet_Planetshop
 * @subpackage Gameplanet_Planetshop/public
 */

/**
 * La funcionalidad del área pública del plugin.
 *
 * Define el nombre del plugin, versión y hooks para el área pública.
 *
 * @package    Gameplanet_Planetshop
 * @subpackage Gameplanet_Planetshop/public
 * @author     GamePlanet
 */
class Gameplanet_Planetshop_Public {

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
	 * Inisializa la clase y define sus propiedades.
	 *
	 * @since    1.0.0
	 * @param      string    $gameplanet_planetshop       Nombre del plugin.
	 * @param      string    $version    La versión del plugin.
	 */
	public function __construct( $gameplanet_planetshop, $version ) {

		$this->gameplanet_planetshop = $gameplanet_planetshop;
		$this->version = $version;

	}

	/**
	 * Registra el CSS para el área pública.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->gameplanet_planetshop, plugin_dir_url( __FILE__ ) . 'css/gameplanet-planetshop-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Registra el JavaScript para el área pública.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->gameplanet_planetshop, plugin_dir_url( __FILE__ ) . 'js/gameplanet-planetshop-public.js', array( 'jquery' ), $this->version, false );

		wp_enqueue_script( $this->gameplanet_planetshop . '_gg_captcha_api', 'https://www.google.com/recaptcha/api.js', array( 'jquery' ), $this->version, false );

		wp_enqueue_script( $this->gameplanet_planetshop . '_gg_api', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyBQe0wH40d8oR-f5cBru1-bvlHB_Gj_sdU&libraries=places', array( 'jquery' ), $this->version, false );

		$nonce = wp_create_nonce( '7#Ez&G2tZ{>z]KUn' );
		wp_localize_script( $this->gameplanet_planetshop, 'ajax_var', array(
			'url'    => rest_url( '/gp/disponible' ),
			'gp_nonce'  => $nonce
		));
		wp_localize_script( $this->gameplanet_planetshop, 'ajax_disponibilidad', array(
			'url'    => rest_url( '/gp/disponibilidad' ),
			'gp_nonce'  => $nonce
		));
		wp_localize_script( $this->gameplanet_planetshop, 'ajax_disp_producto', array(
			'url'    => rest_url( '/gp/disp_producto' ),
			'gp_nonce'  => $nonce
		));
		wp_localize_script( $this->gameplanet_planetshop, 'site_url', array(
			'home'    => site_url()
		));
		wp_localize_script( $this->gameplanet_planetshop, 'user_logged', array(
			'check'    => is_user_logged_in()
		));
		wp_localize_script( $this->gameplanet_planetshop, 'ajax_test', array(
			'url'    => rest_url( '/gp/test' )
		));

		//! cambios ajax "sucursales cerca de ti"
		wp_localize_script(
			$this->gameplanet_planetshop,
			'var_ajax_sucursal',
			array(
				'url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('gpsucursales_ajax'),
				'action' => 'gp_ajax_sucursales',
			)
		);

		//! ajax "widget" producto simple
		wp_localize_script(
			$this->gameplanet_planetshop,
			'var_ajax_disponibilidad',
			array(
				'url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('gpdisponibilidad_ajax'),
				'action' => 'gp_ajax_disponibilidad'
			)
		);
	}

	/**
	 * Ruta donde se "ejecutan".
	 *
	 * @since    1.0.0
	 */
	public function gp_ps_events_endpoint(){
		register_rest_route( 'gp', 'disponible', array(
			// 'methods'  => WP_REST_Server::CREATABLE,
			'methods'  => WP_REST_Server::ALLMETHODS,
			'callback' => [$this, 'ps_disponibilidad'],
			'permission_callback' => '__return_true',
		));
		register_rest_route( 'gp', 'disponibilidad', array(
			'methods'  => WP_REST_Server::ALLMETHODS,
			'callback' => [$this, 'ps_f_disponibilidad'],
			'permission_callback' => '__return_true',
		));
		register_rest_route( 'gp', 'disp_producto', array(
			'methods'  => WP_REST_Server::ALLMETHODS,
			'callback' => [$this, 'ps_f_disponibilidad_tienda'],
			'permission_callback' => '__return_true',
		));
	}

	/**
	 * Función genérica para regresar "true".
	 *
	 * @since    1.0.0
	 */
	public function __return_true(){
		return true;
	}

	/**
	 * Función miscelánea para regresar falso.
	 *
	 * @since    1.0.0
	 * @return   bool                 Regresa false.
	 */
	public function __return_false(){
		return false;
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
	public function gp_ps_log($funcion, $paso, $entry = null){
		$directorio = './wp-content/gp/logs_ps/';
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
	 * Agrega meta dato al producto (carrito).
	 *
	 * @since    1.0.0
	 */
	public function gp_ps_add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
		$this->gp_ps_log('gp_ps_add_cart_item_data', 'inicio');
		if(isset($_COOKIE['_gp_data'])){
			// $cookie = sanitize_text_field($_COOKIE['_gp_data']);
			// $this->gp_ps_log('gp_ps_add_cart_item_data', 'cookie', $cookie);

			// $datos = explode(",", $cookie);
			// foreach($datos as $metadatos){
			// 	$dato = explode("|", $metadatos);
			// 	if(!empty($dato[0]) && !empty($dato[1])){
			// 		$cart_item_data[$dato[0]] = $dato[1];
			// 	}
			// }

			// $datos = json_decode(stripslashes($_COOKIE['_gp_data_test']), true);
			$datos = json_decode(stripslashes($_COOKIE['_gp_data']), true);
			$this->gp_ps_log('gp_ps_add_cart_item_data', "tipo cookie test", gettype($datos));
			$this->gp_ps_log('gp_ps_add_cart_item_data', "datos cookie test", print_r($datos, true));

			foreach($datos as $key => $val){
				$cart_item_data[$key] = $val;
			}
			/**
			 * Validar que no se tiene un producto con diferente atributo al que se está agregando
			 */
			// borra carrito si el producto añadido tiene garantía
			if((isset($cart_item_data['tipo']) && isset($cart_item_data['id_garantia'])) && ($cart_item_data['tipo'] == 'domicilio' && $cart_item_data['id_garantia'] != 'gp_no_garantia')){
				WC()->cart->empty_cart();
				return $cart_item_data;
			} else{
				foreach(WC()->cart->get_cart() as $key => $item){
					if(isset($item['id_garantia']) && $item['id_garantia'] != 'gp_no_garantia'){
						WC()->cart->remove_cart_item($key);
					}
					if($product_id == $item['product_id']){
						if($cart_item_data['tienda'] != $item['tienda']){
							WC()->cart->remove_cart_item($key);
						}
					}
				}
			}

			/**
			 * eliminar cookie
			 */
			unset($_COOKIE['_gp_data']);
			setcookie('_gp_data', null, -1, '/');

		} else{
		}
		// if(isset($_COOKIE['_gp_data_test'])){

		// 	$tempo = json_decode(stripslashes($_COOKIE['_gp_data_test']), true);
		// 	$this->gp_ps_log('gp_ps_add_cart_item_data', "tipo cookie test", gettype($tempo));
		// 	$this->gp_ps_log('gp_ps_add_cart_item_data', "datos cookie test", print_r($tempo, true));

		// 	foreach($tempo as $key => $val){
		// 		$cart_item_data[$key] = $val;
		// 	}
		// }

		$this->gp_ps_log('gp_ps_add_cart_item_data', "fin\n-----");
		return $cart_item_data;
	}
	
	/**
	 * Redirecciona a checkout si se añade al carrito un producto con garantía
	 */
	public function gp_redirect_product_garantia( $url ) {
		foreach(WC()->cart->get_cart() as $key => $item){
			if(isset($item['id_garantia']) && $item['id_garantia'] != 'gp_no_garantia'){
				$url = wc_get_checkout_url();
			}
		}
		return $url;
	}

	/**
	 * Imprime meta datos del producto carrito/checkout.
	 *
	 * @since    1.0.0
	 */
	public function gp_ps_print_item_data( $item_data, $cart_item_data ) {

		// if( isset( $cart_item_data['sku'] ) ) {
		// 	$sku = wc_clean( $cart_item_data['sku'] );
		// 	if( isset( $cart_item_data['categoria'] ) ) {
		// 		$valor = wc_clean( $cart_item_data['categoria'] );
		// 		$item_data[] = array(
		// 			'key' => __( '_gp_categoria' ),
		// 			'value' => wc_clean( $cart_item_data['categoria'] ),
		// 			'display' => __("<span>{$sku} - {$valor}</span>"),
		// 		);
		// 	}
		// }

		// if( isset( $cart_item_data['condicion'] ) ) {
		// 	$valor = wc_clean( $cart_item_data['condicion'] );
		// 	$item_data[] = array(
		// 		'key' => __( '_gp_condicion' ),
		// 		'value' => wc_clean( $cart_item_data['condicion'] ),
		// 		'display' => __("<span>{$valor}</span>"),
		// 	);
		// }
		// if( isset( $cart_item_data['plataforma'] ) ) {
		// 	$valor = wc_clean( $cart_item_data['plataforma'] );
		// 	$item_data[] = array(
		// 		'key' => __( '_gp_plataforma' ),
		// 		'value' => wc_clean( $cart_item_data['plataforma'] ),
		// 		'display' => __("<span>{$valor}</span>"),
		// 	);
		// }
		$arreglo = array();

		if( isset( $cart_item_data['sku'] ) && !empty($cart_item_data['sku'])) {
		 	$sku = wc_clean( $cart_item_data['sku'] );
			array_push($arreglo, $sku);
		}
		if( isset( $cart_item_data['plataforma'] ) && !empty($cart_item_data['plataforma']) ) {
		 	$plataforma = wc_clean( $cart_item_data['plataforma'] );
			array_push($arreglo, $plataforma);
		}
		if( isset( $cart_item_data['condicion'] ) && !empty($cart_item_data['condicion']) ) {
		 	$condicion = wc_clean( $cart_item_data['condicion'] );
			array_push($arreglo, $condicion);
		}

		$categoria = implode(' - ', array_filter($arreglo));
		$item_data[] = array(
			'key' => __( '_gp_categoria' ),
			'value' => $categoria,
			'display' => __("<span>{$categoria}</span>"),
		);


		if( isset( $cart_item_data['nombre_sucursal'] ) && !empty($cart_item_data['nombre_sucursal'])) {
			$valor = wc_clean( $cart_item_data['nombre_sucursal'] );
			$item_data[] = array(
				'key' => __( '_gp_sucursal' ),
				'value' => wc_clean( $cart_item_data['nombre_sucursal'] ),
				'display' => __("<span>Recoger en {$valor}</span>"),
			);
		}

		if( isset( $cart_item_data['precio_final'] ) && !empty($cart_item_data['precio_final'])) {
			$temp = wc_clean( $cart_item_data['precio_final'] );
			if(is_numeric($temp)){
				$valor = '$' . $temp;
			} else{
				$valor = $temp;
			}
			$item_data[] = array(
				'key' => __( '_gp_precio_final' ),
				'value' => wc_clean( $cart_item_data['precio_final'] ),
				'display' => __("<span>Precio final: {$valor}</span>"),
			);
		}

		if( isset( $cart_item_data['monto_minimo'] ) && !empty($cart_item_data['monto_minimo'])) {
			$valor = '$' . wc_clean( $cart_item_data['monto_minimo'] );
			$item_data[] = array(
				'key' => __( '_gp_monto_minimo' ),
				'value' => wc_clean( $cart_item_data['monto_minimo'] ),
				'display' => __("<span>Apártado con: {$valor}</span>"),
			);
		}

		if( isset( $cart_item_data['entrega_estimada'] ) && !empty($cart_item_data['entrega_estimada'])) {
			$valor = wc_clean( $cart_item_data['entrega_estimada'] );
			$item_data[] = array(
				'key' => __( '_gp_entrega_estimada' ),
				'value' => wc_clean( $cart_item_data['entrega_estimada'] ),
				'display' => __("<span>Entrega {$valor}</span>"),
			);
		}

		//!
		if( isset( $cart_item_data['shipping'] ) && !empty($cart_item_data['shipping'])) {
			$valor = get_woocommerce_currency_symbol() . wc_clean( $cart_item_data['shipping'] );
			$item_data[] = array(
				'key' => __( '_gp_costo' ),
				'value' => wc_clean( $cart_item_data['shipping'] ),
				'display' => __("<span>Costo envío: " . '+' . "{$valor}</span>"),
			);
		}

		//! test garantia
		if( isset( $cart_item_data['id_garantia'] ) && !empty($cart_item_data['id_garantia'])) {
			$id_garantia = wc_clean( $cart_item_data['id_garantia'] );
			if($id_garantia != 'gp_no_garantia' && str_starts_with($id_garantia, 'G')){
				
				if( isset( $cart_item_data['costo_garantia'] ) && !empty($cart_item_data['costo_garantia'])) {
					$costo_garantia = wc_clean( $cart_item_data['costo_garantia'] );
					
					if( isset( $cart_item_data['nombre_garantia'] ) && !empty($cart_item_data['nombre_garantia'])) {
						$nombre_garantia = wc_clean( $cart_item_data['nombre_garantia'] );

						// $item_data[] = array(
						// 	'key' => __( '_gp_id_garantia' ),
						// 	'value' => wc_clean( $cart_item_data['id_garantia'] ),
						// 	'display' => __("<span>{$id_garantia}</span>"),
						// );
						$mas_info = site_url('garantias-xtendia');
						$item_data[] = array(
							'key' => __( '_gp_nombre_garantia' ),
							'value' => wc_clean( $cart_item_data['nombre_garantia'] ),
							'display' => __("<p><span class=\"dashicons dashicons-shield gp_dashicon_garantia\"></span><span>Producto Protegido</span></p><p>{$nombre_garantia}</p>"),
						);

						$item_data[] = array(
							'key' => __( '_gp_costo_garantia' ),
							'value' => wc_clean( $cart_item_data['costo_garantia'] ),
							'display' => __("<p>Precio garantía: +" . get_woocommerce_currency_symbol() . "{$costo_garantia}</p><a class=\"gp_underline gp_fs_p7em\" href=\"{$mas_info}\" target=\"_blank\">Más información</a>"),
						);
						
					} else{
						if(isset( $cart_item_data['id_garantia'] )){
							unset($cart_item_data['id_garantia']);
						}
						if(isset( $cart_item_data['costo_garantia'] )){
							unset($cart_item_data['costo_garantia']);
						}
						if(isset( $cart_item_data['nombre_garantia'] )){
							unset($cart_item_data['nombre_garantia']);
						}
					}
					
				} else{
					if(isset( $cart_item_data['id_garantia'] )){
						unset($cart_item_data['id_garantia']);
					}
					if(isset( $cart_item_data['costo_garantia'] )){
						unset($cart_item_data['costo_garantia']);
					}
					if(isset( $cart_item_data['nombre_garantia'] )){
						unset($cart_item_data['nombre_garantia']);
					}
				}
			} else{
				if(isset( $cart_item_data['id_garantia'] )){
					unset($cart_item_data['id_garantia']);
				}
				if(isset( $cart_item_data['costo_garantia'] )){
					unset($cart_item_data['costo_garantia']);
				}
				if(isset( $cart_item_data['nombre_garantia'] )){
					unset($cart_item_data['nombre_garantia']);
				}
			}
		}
		//! ----
		if( isset( $cart_item_data['nota'] ) && !empty($cart_item_data['nota'])) {
			$valor = wc_clean( $cart_item_data['nota'] );
			$item_data[] = array(
				'key' => __( '_gp_nota' ),
				'value' => wc_clean( $cart_item_data['nota'] ),
				'display' => __("<span>{$valor}</span>"),
			);
		}
		if( isset( $cart_item_data['cambio'] ) && !empty($cart_item_data['cambio'])) {
			$valor = wc_clean( $cart_item_data['cambio'] );
			$item_data[] = array(
				'key' => __( '_gp_cambio' ),
				'value' => wc_clean( $cart_item_data['cambio'] ),
				'display' => __("<span>{$valor}</span>"),
			);
		}
		if( isset( $cart_item_data['tienda'] ) ) {
			$valor = wc_clean( '[' . $cart_item_data['tienda'] ) . '][' . wc_clean( $cart_item_data['tipo'] ) . '][' . wc_clean( $cart_item_data['subtipo'] . ']' );
			$item_data[] = array(
				'key' => __( '_gp_extra' ),
				'value' => $valor,
				'display' => __("<span style='color: white'>{$valor}</span>"),
			);
		}
		return $item_data;
	}

	/**
	 * Inicia las cookies.
	 *
	 * @since    1.0.0
	 */
	public function gp_ps_cookie_loader(){

		//* crea cookies para "donde estoy"
		if( !isset($_COOKIE['_gp_geo_lng']) || !isset($_COOKIE['_gp_geo_lat']) || !isset($_COOKIE['_gp_geo_address_short']) || !isset($_COOKIE['_gp_geo_address_long']) ){
			setcookie('_gp_geo_lng', '-99.12766', time()+31556926, '/' );
			setcookie('_gp_geo_lat', '19.42847', time()+31556926, '/' );
			setcookie('_gp_geo_address_short', 'CDMX, CP 06000', time()+31556926, '/' );
			setcookie('_gp_geo_address_long', 'Talavera, República de El Salvador, Centro, Cuauhtémoc, 06000 Ciudad de México, CDMX', time()+31556926, '/' );
			setcookie('_gp_geo_pc', '06000', time()+31556926, '/' );
		}

		//* crea cookies para "tienda favorita"
		// if( !isset($_COOKIE['_gp_tienda_favorita_id']) || !isset($_COOKIE['_gp_tienda_favorita_nombre']) || !isset($_COOKIE['_gp_tienda_seleccionada_id'])){
		if( !isset($_COOKIE['_gp_tienda_favorita_id']) || !isset($_COOKIE['_gp_tienda_favorita_nombre'])){
			setcookie('_gp_tienda_favorita_id', _GP_TIENDA_DEFAULT_ID, time()+31556926, '/' );
			setcookie('_gp_tienda_favorita_nombre', _GP_TIENDA_DEFAULT_NOMBRE, time()+31556926, '/' );
			// setcookie('_gp_tienda_seleccionada_id', 0, time()+31556926, '/' );
		}
	}


	/**
	 * Añade los modal en el footer para no cargarlos varias veces.
	 *
	 * @since    1.0.0
	 */
	public function gp_ps_modal_footer(){
		$direccion_larga = _GP_GEO_ADDRESS_LONG;
		if(isset($_COOKIE['_gp_geo_address_long'])){
			$direccion_larga = urldecode(filter_var($_COOKIE['_gp_geo_address_long'], FILTER_SANITIZE_ENCODED));
		}
		?>
		<div id="modal_de" class="lightbox-by-id lightbox-content lightbox-white mfp-hide" style="max-width:45em ;padding:1.5em;">
			<h2>¿Dónde estoy?</h2>
			<span class="gp_tu_ubicacion_de">
				Tu ubicación actual es: "
				<span class="gp_de_ubicacion"><?php esc_html_e($direccion_larga); ?></span>
				"
			</span>
			
			<h2 class="gp_de_h2">Busca tu ubicación</h2>
			<input type="text" name="autocomplete" id="autocomplete" placeholder="Dirección completa de envío">
			<i class="icon-map-pin-fill" style=" margin-right: 5px; color: #334862;"></i>
			<a href='#' id="mi_ubicacion" class="gp_link_ubicacion">Usar mi ubicación actual.</a>
			<span id = "ubicacion_status"></span>

			<h2 class="gp_de_h2">Dirección guardada</h2>
			<?php
			if(is_user_logged_in()){ ?>
				<?php
					$user_id = get_current_user_id();
					$customer = new WC_Customer( $user_id );
					$user = get_user_by('id', $user_id);
					// $direccion = $customer->get_billing_address_1();
					// $ciudad = $customer->get_billing_city();
					// $codigo_postal = $customer->get_billing_postcode();
					$direccion = $customer->get_shipping_address_1();
					$num_ext = $user->_gp_exterior_number;
					$col = $user->_gp_suburb;
					$ciudad = $customer->get_shipping_city();
					$codigo_postal = $customer->get_shipping_postcode();
					if(empty($direccion) || empty($num_ext) || empty($col) || empty($ciudad) || empty($codigo_postal)){
						?>
						<span class="gp_direccion_guardada">No tienes ningúna dirección de envío guardada.</span>
						<a href="<?php esc_html_e(site_url('/my-account/edit-address/envio/')) ?>">Añadir</a>
						<?php
					} else{
						?>
						<!-- <p class="gp_direccion_guardada">Direccion de envío guardada </p> -->
						<?php
							$temp = $user->gp_shipping_address;
							$datos = explode('|', $temp);
							$direccion_completa = $direccion . ' ' . $num_ext . ', ' . $col . ', CP ' . $codigo_postal . ', ' . $ciudad;
						?>
						<span class="gp_btn_ship gp_underline" gp_dir="<?php echo $direccion_completa . ';' . $ciudad . ', CP ' . $codigo_postal . ';' . $user->gp_lat_shipping . ';' . $user->gp_lng_shipping . ';' . $codigo_postal; ?>"><?php esc_html_e($direccion_completa); ?></span><a href="<?php esc_html_e(site_url('/my-account/edit-address/envio/')) ?>" class="gp_underline">Editar</a>
						<?php
					}
				?>
			<?php } else{ ?>
				<span class="gp_direccion_guardada">Si quieres usar tu direccion guardada, inicia sesión.</span>
			<?php } ?>
			<!-- <span></span> -->
			<p class="gp_mensaje_de">* El costo y la velocidad de envío puede variar según la dirección ingresada.</p>
		</div>

		<div id="modal_tienda" class="lightbox-by-id lightbox-content lightbox-white mfp-hide" style="max-width:45em ;padding:1.5em;">

			<h1>Sucursal cerca de ti.</h1>
			<p class="gp_fs_p8em">Selecciona tu sucursal.</p>
			<div class="gp_modal_tiendas">
				<span id="tiendas_disponibles" class="gp_tiendas_disponibles">cargando...</span>
			</div>
		</div>

		<div id="modal_disp_producto" class="lightbox-by-id lightbox-content lightbox-white mfp-hide" style="max-width:45em ;padding:1.5em;">
			<h1>Sucursales disponibles.</h1>
			<p class="gp_fs_p8em">Selecciona tu sucursal.</p>
			<div class="gp_modal_tiendas">
				<span id="producto_disponible" class="gp_tiendas_disponibles">cargando...</span>
			</div>
		</div>

		<div id="modal_cambiar_direccion" class="lightbox-by-id lightbox-content lightbox-white mfp-hide" style="max-width:45em ;padding:1.5em;">
			<h2>Cambiar dirección de envío.</h2>
			<span class="gp_tu_ubicacion_de">
				Tu ubicación actual es: "
				<span class="gp_de_ubicacion"><?php esc_html_e($direccion_larga); ?></span>
				"
			</span>
			<h2 class="gp_de_h2">Busca tu ubicación</h2>
			<input type="text" name="cambiar_direccion" id="cambiar_direccion" placeholder="Dirección completa de envío">
			<i class="icon-map-pin-fill" style=" margin-right: 5px; color: #334862;"></i>
			<a href='#' class="mi_ubicacion_d">Usar mi ubicación actual.</a>
			<span id="mi_ubicacion_larga_d"></span>
			
			<h2 class="gp_de_h2">Dirección guardada</h2>
			<?php
			if(is_user_logged_in()){ ?>
				<?php
					$user_id = get_current_user_id();
					$customer = new WC_Customer( $user_id );
					$direccion = $customer->get_shipping_address_1();
					$num_ext = $user->_gp_exterior_number;
					$col = $user->_gp_suburb;
					$ciudad = $customer->get_shipping_city();
					$codigo_postal = $customer->get_shipping_postcode();
					if(empty($direccion) || empty($num_ext) || empty($col) || empty($ciudad) || empty($codigo_postal)){
						?>
						<span>No tienes ningúna dirección de envío guardada.</span>
						<br/>
						<a href="<?php esc_html_e(site_url('/my-account/edit-address/envio/')) ?>">Añadir</a>
						<?php
					} else{
						?>
						<!-- <p class="gp_direccion_guardada">Direccion de envío guardada </p> -->
						<?php
							$temp = $user->gp_shipping_address;
							$datos = explode('|', $temp);

							$direccion_completa = $direccion . ' ' . $num_ext . ', ' . $col . ', CP ' . $codigo_postal . ', ' . $ciudad;
						?>
						<span class="gp_btn_ship gp_underline" gp_dir="<?php echo $direccion_completa . ';' . $ciudad . ', CP ' . $codigo_postal . ';' . $user->gp_lat_shipping . ';' . $user->gp_lng_shipping . ';' . $codigo_postal; ?>"><?php esc_html_e($direccion_completa); ?></span><a href="<?php esc_html_e(site_url('/my-account/edit-address/envio/')) ?>" class="gp_underline">Editar</a>
						<?php
					}
				?>
			<?php } else{ ?>
				<p class="gp_direccion_guardada">Si quieres usar tu direccion guardada, inicia sesión.</p>
			<?php } ?>
			<p class="gp_mensaje_de">* El costo y la velocidad de envío puede variar según la dirección ingresada.</p>
		</div>

		<div id="modal_disponibilidad" class="lightbox-by-id lightbox-content lightbox-white mfp-hide" style="max-width:45em ;padding:1.5em;">
			<h1>Disponibilidad</h1>
			<div class="gp_modal_tienda">
				<span id="status_disponibilidad_tienda">&nbsp;<br/>&nbsp;</span>
			</div>
		</div>
		<div id="modal_recoger_tienda" class="lightbox-by-id lightbox-content lightbox-white mfp-hide" style="max-width:45em ;padding:1.5em;">
			<h1>Sucursales disponibles.</h1>
			<p class="gp_fs_p8em">Selecciona tu sucursal.</p>
			<div class="gp_modal_tiendas">
				<span id="recoger_tienda" class="gp_tiendas_disponibles">cargando...</span>
			</div>
		</div>
		<?php
	}

	/**
	 * Evita que se edite la cantidad de los productos en el carrito.
	 *
	 * @since    1.0.0
	 */
	public function gp_ps_cart_quantity( $product_quantity, $cart_item_key, $cart_item ){
		if( is_cart() ){
			$product_quantity = sprintf( '<span class="amount">%1$s</span>', $cart_item['quantity'] );
		}
		return $product_quantity;
	}

	/**
	 * Añade botón de diponibilidad en tienda.
	 *
	 * @since    1.0.0
	 */
	public function gp_ps_btn_disponibilidad(){
		global $product;
		$upc = $product->get_sku();
		if(!str_starts_with($upc, 'P')){
		?>
		<div class='disponibilidad_tienda'>
			<div class="social-icons share-icons share-row relative">
				<a href="#modal_disponibilidad" target="_self" rel="nofollow" class="icon button circle is-outline tooltip search gp_disponibilidad" aria-label="">
					&nbsp;&nbsp;
					<i class="icon-search">
						Ver disponibilidad en sucursal.
					</i>
					&nbsp;&nbsp;
				</a>
			</div>
		</div>
		<div id="modal_disponibilidad" class="lightbox-by-id lightbox-content lightbox-white mfp-hide" style="max-width:45em ;padding:1.5em;">
			<h1>Disponibilidad</h1>
			<div class="gp_modal_tienda">
				<span id="status_disponibilidad_tienda">&nbsp;<br/>&nbsp;</span>
			</div>
		</div>
		<?php }
	}

	/**
	 * Añade imágenes a checkout.
	 *
	 * @since    1.0.0
	 */
	public function gp_ps_image_on_checkout( $name, $cart_item, $cart_item_key ) {
		/* Return if not checkout page */
		if ( ! is_checkout() ) {
			return $name;
		}

		/* Get product object */
		$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

		/* Get product thumbnail */
		$thumbnail = $_product->get_image();

		/* Add wrapper to image and add some css */
		$image = '<div class="ts-product-image gp_thumbnail alignright">'
			. $thumbnail .
		'</div>';

		/* Prepend image to name and return it */
		return $image . $name;
	}

	/**
	 * Añade imágenes a order pay.
	 *
	 * @since    1.0.0
	 */
	public function gp_ps_image_on_order_pay( $name, $item, $extra ) {

		/* Return if not checkout page */
		if ( ! is_checkout() ) {
			return $name;
		}

		$product_id = $item->get_product_id();
		/* Get product object */
		$_product = wc_get_product( $product_id );

		/* Get product thumbnail */
		$thumbnail = $_product->get_image();

		/* Add wrapper to image and add some css */
		$image = '<div class="ts-product-image gp_thumbnail">'
					. $thumbnail .
				'</div>';

		/* Prepend image to name and return it */
		return $image . $name;
	}

	/**
	 * Agrega texto junto a cantidad de producto individual.
	 *
	 * @since    1.0.0
	 */
	public function gp_ps_quantity_text(){
		global $product;
		if(!is_null($product)){
			$sku = $product->get_sku();
			if(!str_starts_with($sku, 'P')){
				esc_html_e("Cantidad: ");
			}
		}
	}

	/**
	 * Modifica los campos del checkout.
	 *
	 * @since    1.0.0
	 */
	public function gp_ps_override_checkout_fields( $fields ) {
		unset($fields['billing']['billing_address_1']);
		unset($fields['billing']['billing_address_2']);
		unset($fields['billing']['billing_city']);
		unset($fields['billing']['billing_postcode']);
		unset($fields['billing']['billing_country']);
		unset($fields['billing']['billing_state']);
		unset($fields['billing']['billing_email']);
		unset($fields['billing']['billing_first_name']);
		unset($fields['billing']['billing_last_name']);
		unset($fields['billing']['billing_phone']);

		unset($fields['shipping']['shipping_first_name']);
		unset($fields['shipping']['shipping_last_name']);
		unset($fields['shipping']['shipping_country']);
		unset($fields['shipping']['shipping_address_1']);
		unset($fields['shipping']['shipping_address_2']);
		unset($fields['shipping']['shipping_city']);
		unset($fields['shipping']['shipping_state']);
		unset($fields['shipping']['shipping_postcode']);
		unset($fields['shipping']['gp_lat_shipping']);
		unset($fields['shipping']['gp_lng_shipping']);

		unset($fields['shipping']['_gp_autocompletado']);
		unset($fields['shipping']['_gp_exterior_number']);
		unset($fields['shipping']['_gp_interior_number']);
		unset($fields['shipping']['_gp_suburb']);

		return $fields;
	}

	/**
	 * Agrega datos (texto) de shipping
	 *
	 * @since    1.0.0
	 */
	public function gp_ps_write_shipping_info(){
		if(is_user_logged_in()){

			$carrito = WC()->cart->get_cart();
			$total_carrito = count($carrito);
			$cont = false;

			foreach($carrito as $key => $item){
				if($item['tipo'] == 'domicilio'){
					$cont = true;
				}
			}

			// if($cont == $total_carrito){
			if($cont){

				$user_id = get_current_user_id();
				$customer = new WC_Customer( $user_id );

				$shipping_first_name = $customer->get_shipping_first_name();
				$shipping_last_name  = $customer->get_shipping_last_name();
				$shipping_address_1  = $customer->get_shipping_address_1();
				$shipping_city       = $customer->get_shipping_city();
				$shipping_state      = $customer->get_shipping_state();
				$shipping_postcode   = $customer->get_shipping_postcode();
				$direccion = site_url('/my-account/edit-address/envio/');
				
				$shipping_country   = $customer->get_shipping_country();
				$country_states   = WC()->countries->get_states( $shipping_country );
				$estado = '';
				if(isset($country_states[$shipping_state])){
					$estado = $country_states[$shipping_state];
				}

				$num_ext = $customer->get_meta('_gp_exterior_number');
				$num_int = $customer->get_meta('_gp_interior_number');
				$colonia = $customer->get_meta('_gp_suburb');

				echo "
				<div style='margin-bottom: 2em;'>
					<h3>Datos de envío</h3>
					<p><a class='gp_underline' href='" . $direccion . "'>Actualizar datos de envío</a></p>
					<p class='form-row-first'><strong>Nombre: </strong>" . (empty($shipping_first_name) ? '' : $shipping_first_name) . "</p>
					<p class='form-row-last'><strong>Apellidos: </strong>" . (empty($shipping_last_name) ? '' : $shipping_last_name) . "</p>
					<p class='form-row-wide'><strong>Dirección: </strong>" . (empty($shipping_address_1) ? '' : $shipping_address_1) . "</p>

					<p class='form-row-first'><strong>Número exterior: </strong>" . (empty($num_ext) ? '' : $num_ext) . "</p>" . 
					(empty($num_int) ? '' : "<p class='form-row-last'><strong>Número interior: </strong>" . $num_int . "</p>") .
					"<p class='form-row-first'><strong>Colonia: </strong>" . (empty($colonia) ? '' : $colonia) . "</p>

					<p class='form-row-last'><strong>Ciudad: </strong>" . (empty($shipping_city) ? '' : $shipping_city) . "</p>
					<p class='form-row-first'><strong>Estado: </strong>" .(empty($estado) ? '' : $estado) . "</p>
					<p class='form-row-last'><strong>Código Postal: </strong>" . (empty($shipping_postcode) ? '' : $shipping_postcode) . "</p>
				</div>
				";
				if(empty($shipping_first_name) || empty($shipping_last_name) || empty($shipping_address_1) || empty($shipping_city) || empty($shipping_state) || empty($shipping_postcode) || empty($num_ext) || empty($colonia)){
					remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
				}
			} else{
				echo "
				<div style='margin-bottom: 2em;'>
					<h3>Datos de envío</h3>
					<p><strong>Error: </strong>Producto sin tipo 'domicilio'</p>
				</div>
				";
				if(empty($shipping_first_name) || empty($shipping_last_name) || empty($shipping_address_1) || empty($shipping_city) || empty($shipping_state) || empty($shipping_postcode)){
					remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
				}
			}
		} else{
			remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
			echo "
			<div style='margin-bottom: 2em;'>
				<h3>Datos de envío</h3>
				<p>¡El usuario no ha iniciado sesión!</p>
			</div>
			";
		}
	}

	/**
	 * Agrega datos (texto) de billing
	 *
	 * @since    1.0.0
	 */
	public function gp_ps_write_billing_info(){
		if(is_user_logged_in()){
			$user_id = get_current_user_id();
			$customer = new WC_Customer( $user_id );

			$billing_first_name = $customer->get_billing_first_name();
			$billing_last_name  = $customer->get_billing_last_name();
			$billing_phone   = $customer->get_billing_phone();
			$billing_email   = $customer->get_billing_email();
			$direccion = site_url('/my-account/edit-address/facturacion/');

			echo "
				<div>
					<p><a class='gp_underline' href='$direccion'>Actualizar datos de tu cuenta</a></p>
					<p class='form-row-first'><strong>Nombre: </strong>". (empty($billing_first_name) ? '' : $billing_first_name) . "</p>
					<p class='form-row-last'><strong>Apellidos: </strong>". (empty($billing_last_name) ? '' : $billing_last_name) . "</p>
					<p class='form-row-wide'><strong>Teléfono: </strong>" . (empty($billing_phone) ? '' : $billing_phone) . "</p>
					<p class='form-row-wide'><strong>Correo: </strong>" . (empty($billing_email) ? '' : $billing_email) . "</p>
				</div>
			";
			if(empty($billing_first_name) || empty($billing_last_name) || empty($billing_phone) || empty($billing_email)){
				remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
			}
		} else{
			remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
			echo '
			<div>
				<p>¡El usuario no ha iniciado sesión!</p>
			</div>
			';
		}
	}

	/**
	 * Validación de productos de carrito en checkout.
	 *
	 * @since    1.0.0
	 */
	public function gp_ps_validar_carrito_checkout($wc){
		// desactivar pagos si no inicia sesión
		if(!is_user_logged_in()){
			remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
			return;
		}

		$this->gp_ps_log('gp_ps_validar_carrito_checkout', 'obtener disponibilidad carrito checkout');
		$user_id = get_current_user_id();
		$customer = new WC_Customer( $user_id );
		$user = get_userdata($user_id);

		$shipping_address_1  = $customer->get_shipping_address_1();
		$shipping_city       = $customer->get_shipping_city();
		$shipping_state      = $customer->get_shipping_state();
		$shipping_postcode   = $customer->get_shipping_postcode();

		$direccion = $shipping_address_1 . '+' . $shipping_city . '+' . $shipping_state . '+' . $shipping_postcode;
		$direccion = str_replace(' ', '+', $direccion);

		$lat = _GP_GEO_LAT;
		$lng = _GP_GEO_LNG;
		$tienda_fav = _GP_TIENDA_DEFAULT_ID;

		// if(isset($_COOKIE['_gp_tienda_favorita_id'])){
		// 	$tienda_fav = filter_var($_COOKIE['_gp_tienda_favorita_id'], FILTER_SANITIZE_ENCODED);
		// }

		// validar carrito
		$carrito = WC()->cart->get_cart();
		foreach($carrito as $llave => $datos){
			if(isset($datos['tienda'])){
				$tienda_fav = $datos['tienda'];
			}
			$carrito[$llave]['nota'] = '';
			if($datos['tipo'] == 'tienda'){
				if(isset($_COOKIE['_gp_geo_lat']) && isset($_COOKIE['_gp_geo_lng'])){
					$lat = filter_var($_COOKIE['_gp_geo_lat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
					$lng = filter_var($_COOKIE['_gp_geo_lng'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
				}
			}
			if($datos['tipo'] == 'domicilio'){
				if(!empty($user->gp_lat_shipping) && !empty($user->gp_lng_shipping)){
					$lat = $user->gp_lat_shipping;
					$lng = $user->gp_lng_shipping;
				}
			}

			$producto = wc_get_product($datos['product_id']);
			$sku = $producto->get_sku();
			$solicitud = $datos['quantity'];

			$disponible = $this->gp_disponibilidad2($datos['product_id'], $datos['tipo'], $sku, $lat, $lng, $tienda_fav, $solicitud);
			$this->gp_ps_log('gp_ps_validar_carrito_checkout', "disponibilidad", $disponible);
			$carrito[$llave]['nota'] = $disponible['result']['nota'];
			$carrito[$llave]['tienda'] = $disponible['result']['id_tienda'];
			$carrito[$llave]['subtipo'] = $disponible['result']['id_subtipo_envio'];
			$carrito[$llave]['entrega_estimada'] = $disponible['result']['entrega_estimada'];
			$carrito[$llave]['shipping'] = $disponible['result']['shipping'];
			if(isset($disponible['result']['nombre_sucursal'])){
				$carrito[$llave]['nombre_sucursal'] = $disponible['result']['nombre_sucursal'];
			}
			if(isset($disponible['result']['cambio'])){
				$carrito[$llave]['cambio'] = $disponible['result']['cambio'];
			} else{
				$carrito[$llave]['cambio'] = '';
			}

			//* preventa
			if(str_starts_with($sku, 'P')){
				if(isset($disponible['result']['precio_final'])){
					$carrito[$llave]['precio_final'] = $disponible['result']['precio_final'];
				} else{
					$carrito[$llave]['precio_final'] = 'sin confirmar';
				}
				if(isset($disponible['result']['fecha_limite_apartado'])){
					$carrito[$llave]['fecha_limite_apartado'] = $disponible['result']['fecha_limite_apartado'];
				} else{
					$carrito[$llave]['fecha_limite_apartado'] = '';
				}
				if(isset($disponible['result']['monto_minimo'])){
					$carrito[$llave]['monto_minimo'] = $disponible['result']['monto_minimo'];
				} else{
					$carrito[$llave]['monto_minimo'] = '';
				}
			}
			//* -----

			if(!$disponible['success']){
				remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
				if($disponible['code'] == 0){
					echo "
						<div class='gp_error'>
							<p>Algo salió mal, cargue de nuevo la página o inténtelo más tarde.</p>
						</div>
					";
					WC()->cart->set_cart_contents($carrito);
					WC()->cart->set_session();
					break;
				}
			}
		}

		$this->gp_ps_log('gp_ps_validar_carrito_checkout', "carrito", $carrito);
		$this->gp_ps_log('gp_ps_validar_carrito_checkout', "actualizo carrito disponibilidad");
		WC()->cart->set_cart_contents($carrito);
		WC()->cart->set_session();

		$this->gp_ps_log('gp_ps_validar_carrito_checkout', "borro sesión (shipping)");
		$packages = WC()->cart->get_shipping_packages();
		foreach( $packages as $package_key => $package ) {
			$session_key  = 'shipping_for_package_'.$package_key;
			$stored_rates = WC()->session->__unset( $session_key );
		}

		$this->gp_ps_log('gp_ps_validar_carrito_checkout', "'calcular envío'");
		$packages = WC()->cart->get_shipping_packages();
		foreach ( $packages as $package ) {
			$shipping_packages = WC()->shipping()->calculate_shipping_for_package( $package );
		}
		//!

		// WC()->cart->calculate_shipping();
		// WC()->cart->calculate_totals();

		// WC()->cart->set_session();
		$this->gp_ps_log('gp_ps_validar_carrito_checkout', "fin actualizar carrito checkout\n-----");
	}

	/**
	 * Función para obtener disponibilidad de un producto en checkout.
	 *
	 * @since    1.0.0
	 */
	public function gp_disponibilidad2( $id_producto, $tipo, $upc, $lat, $lng, $tienda_fav, $solicitud){
		$bandera_ecom = false;
		$bandera_sucursal = false;
		$bandera_preventa = false;

		if(str_starts_with($upc, 'P')){
			$bandera_preventa = true;
			if($tipo == 'domicilio'){
				$bandera_ecom = true;
			} elseif($tipo == 'tienda'){
				$bandera_sucursal = true;
			}
		} else{
			$product = wc_get_product( $id_producto );
			$_gp_lugar_venta = $product->get_attribute( '_gp_lugar_venta' );
			$atributos = explode(',', $_gp_lugar_venta);

			foreach($atributos as $atributo){
				$atributo = trim($atributo, ' ');
				if('ecom' == $atributo && $tipo == 'domicilio'){
					$bandera_ecom = true;
				} elseif('sucursal' == $atributo && $tipo == 'tienda'){
					$bandera_sucursal = true;
				}
			}
		}

		if(!$bandera_ecom && !$bandera_sucursal){
			$this->gp_ps_log('gp_disponibilidad2', 'Error, tipo de envio en producto diferente a atributo');
			$this->gp_ps_log('gp_disponibilidad2', 'tipo en producto: ' . $tipo);
			$this->gp_ps_log('gp_disponibilidad2', 'atributos del producto', $atributos);
			$this->gp_ps_log('gp_disponibilidad2', "Fin\n-----");
			return array("success" => false, "code" => -6, "result" => array( "nota" => 'El tipo de envío de este producto no concuerda, elimínalo para poder completar la orden..', "entrega_estimada" => '', "shipping" => '', "id_tienda" => '', "id_subtipo_envio" => ''));
		}

		$id_gp = 0;
		if(is_user_logged_in()){
			$user_id = get_current_user_id();
			$id_gp = get_user_meta($user_id, 'id_gp', true);
		}

		$tipo_producto = 'fisico';
		$metodo = 'cache';
		if(is_checkout()){
			$metodo = 'real';
		}

		if($bandera_preventa){
			$tipo_producto = 'preventa';
			$datos_domicilio = [
				'preventa' => [
					'solicitud' => 1,
					'metodo' => $metodo,
					'cantidad_min' => $solicitud,
				],
			];
			$datos_tienda = [
				'preventa' => [
					'solicitud' => 1,
					'metodo' => $metodo,
					'cantidad_min' => $solicitud,
				],
			];
		} else{
			$datos_domicilio = [
				'express' => [
					'solicitud' => 1,
					'metodo' => $metodo,
					'cantidad_min' => $solicitud,
				],
				'sameday' => [
					'solicitud' => 1,
					'metodo' => $metodo,
					'cantidad_min' => $solicitud,
				],
				'standard' => [
					'solicitud' => 1,
					'metodo' => $metodo,
					'cantidad_min' => $solicitud,
				],
				'nextday' => [
					'solicitud' => 1,
					'metodo' => $metodo,
					'cantidad_min' => $solicitud,
				],
			];

			$datos_tienda = [
				'apartado' => [
					'solicitud' => 1,
					'metodo' => $metodo,
					'cantidad_min' => $solicitud,
				],
			];
		}

		$url = site_url();
		$disallowed = array('http://', 'https://');
		$dir = '';
		foreach($disallowed as $d) {
			if(strpos($url, $d) === 0) {
				$dir = str_replace($d, '', $url);
			}
		}
		$is_admin = current_user_can('administrator');
		
		$datos = [
			"id_cliente" => $is_admin?0:$id_gp,
			'productos' => [
				0 => [
					'upc' => $upc,
					'surtidor' => 'GAM',
					'origen' => $dir,
					'tipo' => $tipo_producto,
					'lat' => $lat,
					'lng' => $lng,
					'id_tienda_favorita' => $tienda_fav,
					'id_tienda_seleccionada' => $tienda_fav,
					'domicilio' => $datos_domicilio,
					'tienda' => $datos_tienda,
				],
			],
		];

		$args = array(
			'body' => json_encode($datos),
			'headers' => array(
				'Content-Type' => 'application/json',
				'data'         => get_option('data-tendero')
			)
		);
		$url = get_option('ruta_tendero') . "producto/disponibilidad2";

		$response = wp_remote_post($url, $args); //!
		if (is_wp_error($response)) {
			$this->gp_ps_log('gp_disponibilidad2', 'body', $args);
			$this->gp_ps_log('gp_disponibilidad2', 'url', $url);
			$mensaje_error = $response->get_error_message();
			$this->gp_ps_log('gp_disponibilidad2', 'error', $mensaje_error);
			$this->gp_ps_log('gp_disponibilidad2', "Fin\n-----");
			return array("success" => false, "code" => 0, "result" => array( "nota" => '', "entrega_estimada" => '', "shipping" => '', "id_tienda" => '', "id_subtipo_envio" => ''));
		}
		// cachamos codigo http (['response']['code'])
		if ($response['response']['code'] == 200) {
			// obtenemos el body
			$ext_auth = json_decode($response['body'], true); //!

			// Success == true
			if ($ext_auth['success']) {

				// cachamos el codigo ( 0 == operacion exitosa)
				if ($ext_auth['code'] == 0) {
					$domicilio = $ext_auth['result'][0]['domicilio'];
					$tienda = $ext_auth['result'][0]['tienda'];

					$informacion_envio = array();

					$apart_tienda = [];
					$entrega_estimada = '';

					$env_domicilio = [];
					$disp_tienda = [];

					if($bandera_ecom){
						foreach($domicilio as $key => $tipo){
							foreach($tipo['almacenes'] as $key => $almacen){
								if($almacen['cantidad'] >= $solicitud){
									if($tipo['subtipo'] == 'preventa'){
										if(isset($ext_auth['result'][0]['precio_final_confirmado']) && $ext_auth['result'][0]['precio_final_confirmado']){
											$entrega_estim = $tipo['disponibilidad']['entrega_estimada'];
											if(isset($ext_auth['result'][0]['fecha_lanzamiento_confirmada']) && !$ext_auth['result'][0]['fecha_lanzamiento_confirmada']){
												$entrega_estim = 'no definida';
											}
											$env_domicilio += array(
												$tipo['subtipo'] =>  array(
													'id_tipo_envio' => 'domicilio',
													'id_subtipo_envio' => $tipo['subtipo'],
													'shipping' => $tipo['shipping'],
													'id_tienda' => $almacen['id'],
													'nombre_tienda' => $almacen['nombre'],
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
												'nombre_tienda' => $almacen['nombre'],
												'entrega_estimada' => $tipo['disponibilidad']['entrega_estimada'],
												'disponible' => $tipo['disponible'],
												'cantidad' => $almacen['cantidad']
												)
										);
									}
									break;
								}
							}
						}
						if(count($env_domicilio)){
							$opcion = '';
							if(isset($env_domicilio['preventa']) && $env_domicilio['preventa']['disponible']){
								$opcion = 'preventa';
								return array("success" => true, "code" => 1, "result" => array( "nota" => '',  "precio_final" => $env_domicilio[$opcion]['precio_final'],  "fecha_limite_apartado" => $env_domicilio[$opcion]['fecha_limite_apartado'],  "monto_minimo" => $env_domicilio[$opcion]['monto_minimo'],"entrega_estimada" => $env_domicilio[$opcion]['entrega_estimada'], "shipping" => $env_domicilio[$opcion]['shipping'], "id_tienda" => $env_domicilio[$opcion]['id_tienda'], "id_subtipo_envio" => $env_domicilio[$opcion]['id_subtipo_envio']));
							}elseif(isset($env_domicilio['standard']) && $env_domicilio['standard']['disponible']){
								$opcion = 'standard';
							} elseif(isset($env_domicilio['nextday']) && $env_domicilio['nextday']['disponible']){
								$opcion = 'nextday';
							} elseif(isset($env_domicilio['sameday']) && $env_domicilio['sameday']['disponible']){
								$opcion = 'sameday';
							} elseif(isset($env_domicilio['express']) && $env_domicilio['express']['disponible']){
								$opcion = 'express';
							} else{
								return array("success" => false, "code" => -4, "result" => array( "nota" => 'Por el momento no está disponible este producto para esta dirección. (Elimínalo para poder completar la orden).', "entrega_estimada" => '', "shipping" => '', "id_tienda" => '', "id_subtipo_envio" => ''));
							}
							// if(isset($env_domicilio['express'])){
							// 	$opcion = 'express';
							// } elseif(isset($env_domicilio['sameday'])){
							// 	$opcion = 'sameday';
							// } elseif(isset($env_domicilio['nextday'])){
							// 	$opcion = 'nextday';
							// } elseif(isset($env_domicilio['standard'])){
							// 	$opcion = 'standard';
							// } else{
							// 	return array("success" => false, "code" => -4, "result" => array( "nota" => 'Por el momento no está disponible este producto para esta dirección. (Elimínalo para poder completar la orden).', "entrega_estimada" => '', "shipping" => '', "id_tienda" => '', "id_subtipo_envio" => ''));
							// }
							// //! BORRAR
							// if($upc == '820650850233'){
							// 	$opcion = 'express';
							// }
							// //!-----

							return array("success" => true, "code" => 1, "result" => array( "nota" => '', "entrega_estimada" => $env_domicilio[$opcion]['entrega_estimada'], "shipping" => $env_domicilio[$opcion]['shipping'], "id_tienda" => $env_domicilio[$opcion]['id_tienda'], "id_subtipo_envio" => $env_domicilio[$opcion]['id_subtipo_envio']));

						} else{
							return array("success" => false, "code" => -3, "result" => array( "nota" => 'Por el momento no está disponible este producto para esta dirección. (Elimínalo para poder completar la orden)..', "entrega_estimada" => '', "shipping" => '', "id_tienda" => '', "id_subtipo_envio" => ''));
						}
					} elseif($bandera_sucursal){
						foreach($tienda as $key => $tipo){
							if($tipo['subtipo'] == 'preventa'){
								foreach($tipo['almacenes'] as $key => $almacen){
									if($almacen['cantidad'] >= $solicitud){
										$precio_final = 'sin confirmar';
										if((isset($ext_auth['result'][0]['precio_final_confirmado']) && $ext_auth['result'][0]['precio_final_confirmado']) && isset($ext_auth['result'][0]['precio_final'])){
											$precio_final = $ext_auth['result'][0]['precio_final'];
										}
										$entrega_estim_tienda = $tipo['disponibilidad']['entrega_estimada'];
										if(isset($ext_auth['result'][0]['fecha_lanzamiento_confirmada']) && !$ext_auth['result'][0]['fecha_lanzamiento_confirmada']){
											$entrega_estim_tienda = 'no definida';
										}
										$disp_tienda += [
											$tipo['subtipo'] => [
												'id_tipo_envio' => 'tienda',
												'id_subtipo_envio' => $tipo['subtipo'],
												'shipping' => $tipo['shipping'],
												'id_tienda' => $almacen['id'],
												'nombre_tienda' => $almacen['nombre'],
												'entrega_estimada' => $entrega_estim_tienda,
												'disponible' => $tipo['disponible'],
												'cantidad' => $almacen['cantidad'],
												'fecha_limite_apartado' => $ext_auth['result'][0]['fecha_limite_apartado'],
												'precio_final' => $precio_final,
												'monto_minimo' => $tipo['monto_minimo']
											]
										];
										break 2;
									}
								}
							} elseif($tipo['subtipo'] == 'apartado'){
								foreach($tipo['almacenes'] as $key => $almacen){
									if($almacen['cantidad'] >= $solicitud){
										$disp_tienda += [
											$tipo['subtipo'] => [
												'id_tipo_envio' => 'tienda',
												'id_subtipo_envio' => $tipo['subtipo'],
												'shipping' => $tipo['shipping'],
												'id_tienda' => $almacen['id'],
												'nombre_tienda' => $almacen['nombre'],
												'entrega_estimada' => $tipo['disponibilidad']['entrega_estimada'],
												'disponible' => $tipo['disponible'],
												'cantidad' => $almacen['cantidad']
											]
										];
										break 2;
									}
								}
							}
						}
						if(count($disp_tienda)){
							if(isset($disp_tienda['preventa'])){
								$cambio = '';
								$nombre_sucursal = str_replace('Gameplanet', 'GP', $disp_tienda['preventa']['nombre_tienda']);
								if($tienda_fav != $disp_tienda['preventa']['id_tienda']){
									$nombre_tienda_fav = sanitize_text_field($_COOKIE['_gp_tienda_favorita_nombre']);
									$cambio = '(En "'. $nombre_tienda_fav .'" no hay disponibilidad, puedes recogerlo en "' . $nombre_sucursal . '")';
								}
								return array("success" => true, "code" => 1, "result" => array( "nota" => '',  "precio_final" => $disp_tienda['preventa']['precio_final'],"fecha_limite_apartado" => $disp_tienda['preventa']['fecha_limite_apartado'], "monto_minimo" => $disp_tienda['preventa']['monto_minimo'], "nombre_sucursal" => $nombre_sucursal, "entrega_estimada" => $disp_tienda['preventa']['entrega_estimada'], "shipping" => $disp_tienda['preventa']['shipping'], "id_tienda" => $disp_tienda['preventa']['id_tienda'], "id_subtipo_envio" => $disp_tienda['preventa']['id_subtipo_envio'], "cambio" => $cambio));
							} elseif(isset($disp_tienda['apartado'])){
								$cambio = '';
								$nombre_sucursal = str_replace('Gameplanet', 'GP', $disp_tienda['apartado']['nombre_tienda']);
								if($tienda_fav != $disp_tienda['apartado']['id_tienda']){
									$nombre_tienda_fav = sanitize_text_field($_COOKIE['_gp_tienda_favorita_nombre']);
									$cambio = '(En "'. $nombre_tienda_fav .'" no hay disponibilidad, puedes recogerlo en "' . $nombre_sucursal . '")';
								}
								return array("success" => true, "code" => 1, "result" => array( "nota" => '', "nombre_sucursal" => $nombre_sucursal, "entrega_estimada" => $disp_tienda['apartado']['entrega_estimada'], "shipping" => $disp_tienda['apartado']['shipping'], "id_tienda" => $disp_tienda['apartado']['id_tienda'], "id_subtipo_envio" => $disp_tienda['apartado']['id_subtipo_envio'], "cambio" => $cambio));
							}
						} else{
							return array("success" => false, "code" => -5, "result" => array( "nota" => 'Este producto no está disponible para apartarlo en tienda, elimínalo para poder completar la orden.', "nombre_sucursal" => '', "entrega_estimada" => '', "shipping" => '', "id_tienda" => '', "id_subtipo_envio" => ''));
						}
					}

				} else{
					$respuesta = 'Código: ' . $ext_auth['code'];
					return array("success" => false, "code" => -2, "result" => array( "nota" => 'Este producto no está disponible para apartarlo en tienda, elimínalo para poder completar la orden.', "entrega_estimada" => '', "shipping" => '', "id_tienda" => '', "id_subtipo_envio" => ''));
				}
			} else{
				$respuesta = 'Respuesta "success" falso';
				return array("success" => false, "code" => -1, "result" => array( "nota" => 'Este producto no está disponible para apartarlo en tienda, elimínalo para poder completar la orden.', "entrega_estimada" => '', "shipping" => '', "id_tienda" => '', "id_subtipo_envio" => ''));
			}
		} else{
			$this->gp_ps_log('gp_disponibilidad2', 'body', $args);
			$this->gp_ps_log('gp_disponibilidad2', 'url', $url);
			$this->gp_ps_log('gp_disponibilidad2', 'error', $response);
			$this->gp_ps_log('gp_disponibilidad2', "Fin\n-----");
			return array("success" => false, "code" => 0, "result" => array( "nota" => 'Este producto no está disponible, elimínalo para poder completar la orden.', "entrega_estimada" => '', "shipping" => '', "id_tienda" => '', "id_subtipo_envio" => ''));
		}
	}

	/**
	 * Añadir css para email (mailpoet).
	 *
	 * @since    1.0.0
	 */
	public function gp_ps_email_css($css, $email){
		$css .= '
    		.mailpoet_text { padding-top: 5px !important; padding-bottom: 5px !important; }
    		.mailpoet_text h1 { font-size: 20px !important; line-height: 25px !important;}
			.gp-p-meta { margin-bottom: 0 !important;}
			.is-well { padding: 20px; background-color: rgba(0,0,0,.02); -webkit-box-shadow: 1px 1px 3px 0px rgb(0 0 0 / 20%), 0 1px 0 rgb(0 0 0 / 7%), inset 0 0 0 1px rgb(0 0 0 / 5%); box-shadow: 1px 1px 3px 0px rgb(0 0 0 / 20%), 0 1px 0 rgb(0 0 0 / 7%), inset 0 0 0 1px rgb(0 0 0 / 5%);}
			.gp_list p { display: list-item; }
			.gp_entrega_en { color: green !important; font-weight: bold; }
			.gp_item_status_rojo { color: red !important; font-weight: bold; }
			.gp_item_status_verde { color: green !important; font-weight: bold; }
			.price-fraction { font-size: 0.6em; top: -0.40em; position: relative; font-weight: normal; }
   		';
   		return $css;
	}

	/**
	 * Guarda meta datos de carrito a orden (por producto).
	 *
	 * @since    1.0.0
	 */
	public function gp_ps_save_cart_metadata( $item, $cart_item_key, $values, $order ) {

		$arreglo = array();

		if( isset( $values['sku'] ) && !empty($values['sku'])) {
		 	$sku =  $values['sku'] ;
			array_push($arreglo, $sku);
		}
		if( isset( $values['plataforma'] ) && !empty($values['plataforma']) ) {
		 	$plataforma =  $values['plataforma'] ;
			array_push($arreglo, $plataforma);
		}
		if( isset( $values['condicion'] ) && !empty($values['condicion']) ) {
		 	$condicion =  $values['condicion'] ;
			array_push($arreglo, $condicion);
		}

		$categoria = implode(' - ', array_filter($arreglo));
		$item->add_meta_data( 'Categoría', $categoria );

		// $item->add_meta_data( 'Categoría', $values['sku'] . ' - ' . $values['categoria'] );
		// $item->add_meta_data( 'Condición', $values['condicion'] );
		// $item->add_meta_data( 'Plataforma', $values['plataforma'] );
		$item->add_meta_data( 'Ticket', 'Pendiente' );
		// if($values['tipo'] == 'tienda'){
		// 	$item->add_meta_data( 'Entrega', $values['entrega_estimada'] . ' a la confirmación de la orden.');
		// } else{
		// 	$item->add_meta_data( 'Entrega', $values['entrega_estimada'] );
		// }
		if($values['tipo'] == 'tienda'){
			if($values['subtipo'] == 'preventa'){
				$item->add_meta_data( 'Estatus', 'pendiente de aprobación.' );
				if(isset($values['entrega_estimada'])){
					$item->add_meta_data( 'Entrega', $values['entrega_estimada'] );
				}
			} else{
				$item->add_meta_data( 'Estatus', 'verificando disponibilidad en sucursal.' );
			}
			$item->add_meta_data( 'Recoge en', $values['nombre_sucursal'] );
			// $item->add_meta_data( 'Recoge de', '11:00 AM a 08:00 PM de Lunes a Domingo. (Tienes 48hrs para recoger tu pedido)' );
		} else{
			$item->add_meta_data( 'Estatus', 'pendiente de autorización de sucursal.' );
			$item->add_meta_data( 'Entrega', $values['entrega_estimada'] );
			$item->add_meta_data( 'Costo envío', '$' . $values['shipping'] );
		}
		$item->add_meta_data( '_gp_shipping', $values['shipping'] );
		$item->add_meta_data( '_gp_id_tienda', $values['tienda'] );

		if($values['tipo'] == 'tienda'){
			if($values['subtipo'] == 'preventa'){
				$item->add_meta_data( '_gp_id_tipo_envio', 'tienda');
				$order->update_meta_data('_ps_tipo_envio', 'tienda');
				$order->update_meta_data('_gp_estatus_preventa', 'A');
				$order->update_meta_data('Apartado con', $values['monto_minimo']);
			} else{
				$item->add_meta_data( '_gp_id_tipo_envio', 'apartado');
				$order->update_meta_data('_ps_tipo_envio', 'apartado');
				$order->update_meta_data('_gp_estatus_apartado', 'A');
			}
		} else{
			$order->update_meta_data('_ps_tipo_envio', 'domicilio');
			$item->add_meta_data( '_gp_id_tipo_envio', $values['tipo']);
			$order->update_meta_data('_gp_estatus_domicilio', 'A');
		}

		if($values['subtipo'] == 'apartado'){
			$item->add_meta_data( '_gp_id_subtipo_envio', 'standard' );
		} else{
			$item->add_meta_data( '_gp_id_subtipo_envio', $values['subtipo'] );
		}
		//!-----
		// $item->add_meta_data( '_gp_id_garantia', 0 );
		// $item->add_meta_data( '_gp_costo_garantia', 0 );
		$garantia_id = '';
		$garantia_costo = '';
		$garantia_nombre = '';
		
		if(isset($values['id_garantia']) && !empty($values['id_garantia'])){
			$garantia_id = $values['id_garantia'] ;
		}
		if($garantia_id == 'gp_no_garantia'){
			$garantia_id = '';
		} else{
			if(isset($values['costo_garantia']) && !empty($values['costo_garantia'])){
				$garantia_costo = $values['costo_garantia'];
			}
			if(isset($values['nombre_garantia']) && !empty($values['nombre_garantia'])){
				$garantia_nombre = $values['nombre_garantia'] ;
			}
		}
		
		if(!empty($garantia_id)){
			$item->add_meta_data( '_gp_id_garantia', $garantia_id);
		} else{
			$item->add_meta_data( '_gp_id_garantia', '0');
		}

		if(!empty($garantia_costo)){
			$item->add_meta_data( '_gp_costo_garantia', $garantia_costo);
		} else{
			$item->add_meta_data( '_gp_costo_garantia', '0');
		}

		if(!empty($garantia_nombre)){
			$item->add_meta_data( '_gp_nombre_garantia', $garantia_nombre);
		} else{
			$item->add_meta_data( '_gp_nombre_garantia', '');
		}
		//!-----
		$item->add_meta_data( '_gp_seguro_envio', 0 );
		$item->add_meta_data( '_gp_costo_manejo', 0 );
		$item->add_meta_data( '_gp_costo_activacion', 0 );
	}

	/**
	 * Actualiza el costo de envío.
	 *
	 * @since    1.0.0
	 */
	public function gp_ps_update_shipping_cost( $rates, $packages ) {
		//! -----
		$this->gp_ps_log('gp_ps_update_shipping_cost', 'inicio test disp');

		// desactivar pagos si no inicia sesión
		if(!is_user_logged_in()){
			remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
			return $rates;
		}

		$this->gp_ps_log('gp_ps_update_shipping_cost', 'obtener disponibilidad carrito (shipping)');

		$user_id = get_current_user_id();
		$user = get_userdata($user_id);

		$tipo_producto = 'fisico';

		$lat = _GP_GEO_LAT;
		$lng = _GP_GEO_LNG;
		$tienda_fav = _GP_TIENDA_DEFAULT_ID;
		$id_gp = get_user_meta($user_id, 'id_gp', true);
		$bandera_preventa = 0;

		if(isset($_COOKIE['_gp_tienda_favorita_id'])){
			$tienda_fav = filter_var($_COOKIE['_gp_tienda_favorita_id'], FILTER_SANITIZE_ENCODED);
		}
		if(isset($_COOKIE['_gp_geo_lat']) && isset($_COOKIE['_gp_geo_lng'])){
			$lat_cookie = filter_var($_COOKIE['_gp_geo_lat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
			$lng_cookie = filter_var($_COOKIE['_gp_geo_lng'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
		} else{
			$lat_cookie = $lat;
			$lng_cookie = $lng;
		}
		if(!empty($user->gp_lat_shipping) && !empty($user->gp_lng_shipping)){
			$lat_user = $user->gp_lat_shipping;
			$lng_user = $user->gp_lng_shipping;
		} else{
			$lat_user = $lat;
			$lng_user = $lng;
		}

		$productos = [];
		// validar carrito
		$carrito = WC()->cart->get_cart();
		foreach($carrito as $llave => $datos){

			if($datos['tipo'] == 'tienda'){
				$lat = $lat_cookie;
				$lng = $lng_cookie;
			}
			if($datos['tipo'] == 'domicilio'){
				$lat = $lat_user;
				$lng = $lng_user;
			}

			$producto = wc_get_product($datos['product_id']);
			$sku = $producto->get_sku();

			$tipo_producto = 'fisico';
			if(str_starts_with($sku, 'P')){
				$bandera_preventa = 1;
				$tipo_producto = 'preventa';
			}

			$productos[$llave] = array("upc" => $sku, "solicitud" => $datos['quantity'], "tipo_producto" => $tipo_producto, 'tienda_fav' => $datos['tienda']);
		}

		$datos_domicilio = [];
		$metodo = 'cache';
		if(is_checkout()){
			$metodo = 'real';
		}
		if($bandera_preventa && count($productos) != 1){
			$this->gp_ps_log('gp_ps_update_shipping_cost', "error, varias preventas, borrar carrito");
			remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
			return $rates;
		} elseif($bandera_preventa && count($productos) == 1){
			foreach($productos as $llave => $valor){
				$datos_domicilio[$llave] = [
					'preventa' => [
						'solicitud' => 1,
						'metodo' => $metodo,
						'cantidad_min' => $valor['solicitud'],
					],
				];
				$datos_tienda[$llave] = [
					'preventa' => [
						'solicitud' => 1,
						'metodo' => $metodo,
						'cantidad_min' => $valor['solicitud'],
					],
				];
			}
		} elseif(!$bandera_preventa){
			foreach($productos as $llave => $valor){
				$datos_domicilio[$llave] = [
					'express' => [
						'solicitud' => 1,
						'metodo' => $metodo,
						'cantidad_min' => $valor['solicitud'],
					],
					'sameday' => [
						'solicitud' => 1,
						'metodo' => $metodo,
						'cantidad_min' => $valor['solicitud'],
					],
					'standard' => [
						'solicitud' => 1,
						'metodo' => $metodo,
						'cantidad_min' => $valor['solicitud'],
					],
					'nextday' => [
						'solicitud' => 1,
						'metodo' => $metodo,
						'cantidad_min' => $valor['solicitud'],
					],
				];
				$datos_tienda[$llave] = [
					'apartado' => [
						'solicitud' => 1,
						'metodo' => $metodo,
						'cantidad_min' => $valor['solicitud'],
					],
				];
			}
		} else{
			$this->gp_ps_log('gp_ps_update_shipping_cost', "error carrito, borrar carrito");
			remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
			return $rates;
		}

		$url = site_url();
		$disallowed = array('http://', 'https://');
		$dir = '';
		foreach($disallowed as $d) {
			if(strpos($url, $d) === 0) {
				$dir = str_replace($d, '', $url);
			}
		}

		$body = [];
		$list_productos = [];
		foreach($productos as $llave => $valor){
			$list_productos[] = array(
			'upc' => $valor['upc'],
			'surtidor' => 'GAM',
			'origen' => $dir,
			'tipo' => $valor['tipo_producto'],
			'lat' => $lat,
			'lng' => $lng,
			'id_tienda_favorita' => $valor['tienda_fav'],
			'id_tienda_seleccionada' => $valor['tienda_fav'],
			"id_cliente" => $id_gp,
			'domicilio' => @$datos_domicilio[$llave],
			'tienda' => @$datos_tienda[$llave]);
		}

		$body['productos'] = $list_productos;

		// $this->gp_ps_log('gp_ps_update_shipping_cost', "llamar disponibilidad", $body);
		$args = array(
			'body' => json_encode($body),
			'headers' => array(
				'Content-Type' => 'application/json',
				'data'         => get_option('data-tendero')
			)
		);
		$url = get_option('ruta_tendero') . "producto/disponibilidad2";

		$response = wp_remote_post($url, $args); //!
		$bandera_error = 1;
		if (is_wp_error($response)) {
			$mensaje_error = $response->get_error_message();
			$this->gp_ps_log('gp_ps_update_shipping_cost', "error WP", $mensaje_error);
			$this->gp_ps_log('gp_ps_update_shipping_cost', "borrar métodos de pago");
			$bandera_error = 0;
			remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
			return $rates;

		}

		$carrito_ship = [];
		if(!is_null(WC()->cart)){
			$carrito_ship = WC()->cart->get_cart();
			$carrito_ship_temp = WC()->cart->get_cart();
		} else{
			remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
			return $rates;
		}

		$this->gp_ps_log('gp_ps_update_shipping_cost', "carrito", $carrito_ship);
		// cachamos codigo http (['response']['code'])
		if ($response['response']['code'] == 200) {
			// obtenemos el body
			$ext_auth = json_decode($response['body'], true); //!

			// Success == true
			if ($ext_auth['success']) {
				// cachamos el codigo ( 0 == operacion exitosa)
				if ($ext_auth['code'] == 0) {
					$this->gp_ps_log('gp_ps_update_shipping_cost', "respuesta disponibilidad correcta");
					$disp_domicilio = [];
					$disp_domicilio_test = [];
					$disp_tienda_test = [];
					$disp_tienda = [];
					foreach($ext_auth['result'] as $llave => $producto_simple){
						$disp_domicilio_temp = [];
						$disp_tienda_temp = [];

						foreach($producto_simple['domicilio'] as $subtipo_envio){
							if(isset($subtipo_envio['subtipo']) && $subtipo_envio['disponible']){
								foreach($subtipo_envio['almacenes'] as $almacen){
									$disp_domicilio_temp[] = array(
										'upc' => $producto_simple['upc'],
										'subtipo' => $subtipo_envio['subtipo'],
										'id' => $almacen['id'],
										'cantidad' => $almacen['cantidad'],
										'shipping' => $subtipo_envio['shipping'],
										'disponibilidad' => $subtipo_envio['disponibilidad']['entrega_estimada'],
									);
									break;
								}
							}
						}
						//! ---test---
						//* ---obtener toda disponibilidad domicilio---
						$disp_domicilio_temp_test = [];
						foreach($producto_simple['domicilio'] as $subtipo_envio){
							foreach($subtipo_envio['almacenes'] as $almacen){
								if(isset($subtipo_envio['subtipo']) && $subtipo_envio['disponible']){
									$disp_domicilio_temp_test[$subtipo_envio['subtipo']] = array(
										'upc' => $producto_simple['upc'],
										'subtipo' => $subtipo_envio['subtipo'],
										'id' => $almacen['id'],
										'cantidad' => $almacen['cantidad'],
										'shipping' => $subtipo_envio['shipping'],
										'entrega_estimada' => $subtipo_envio['disponibilidad']['entrega_estimada'],
										'disponible' => $subtipo_envio['disponible'],
									);
								}
								break;
							}
						}
						$this->gp_ps_log('gp_ps_update_shipping_cost', "test disp docmicilio", @$disp_domicilio_temp_test);

						//* ---obtener toda disponibilidad tienda---
						$disp_tienda_temp_test = [];
						foreach($producto_simple['tienda'] as $subtipo_envio){
							foreach($subtipo_envio['almacenes'] as $almacen){
								if(isset($subtipo_envio['subtipo']) && $subtipo_envio['disponible']){
									$disp_tienda_temp_test[$subtipo_envio['subtipo']] = array(
										'upc' => $producto_simple['upc'],
										'subtipo' => $subtipo_envio['subtipo'],
										'id' => $almacen['id'],
										'cantidad' => $almacen['cantidad'],
										'shipping' => $subtipo_envio['shipping'],
										'entrega_estimada' => $subtipo_envio['disponibilidad']['entrega_estimada'],
										'disponible' => $subtipo_envio['disponible'],
									);
								}
								break;
							}
						}
						$this->gp_ps_log('gp_ps_update_shipping_cost', "test disp tienda", @$disp_tienda_temp_test);
						//! ------

						foreach($producto_simple['tienda'] as $subtipo_envio){
							if(isset($subtipo_envio['subtipo']) && $subtipo_envio['disponible']){
								foreach($subtipo_envio['almacenes'] as $almacen){
									$disp_tienda_temp[$subtipo_envio['subtipo']] = array(
										'upc' => $producto_simple['upc'],
										'subtipo' => $subtipo_envio['subtipo'],
										'id' => $almacen['id'],
										'cantidad' => $almacen['cantidad'],
										'shipping' => $subtipo_envio['shipping'],
										'disponibilidad' => $subtipo_envio['disponibilidad']['entrega_estimada'],
									);
									break;
								}
							}
						}

						//! ---test---
						//* ---seleccionar tipo de envío domicilio---
						$opcion_envio_test = '';
						if(isset($disp_domicilio_temp_test['preventa'])){
							$opcion_envio_test = 'preventa';
						} elseif(isset($disp_domicilio_temp_test['standard'])){
							$opcion_envio_test = 'standard';
						} elseif(isset($disp_domicilio_temp_test['nextday'])){
							$opcion_envio_test = 'nextday';
						} elseif(isset($disp_domicilio_temp_test['sameday'])){
							$opcion_envio_test = 'sameday';
						} elseif(isset($disp_domicilio_temp_test['express'])){
							$opcion_envio_test = 'express';
						}
						$this->gp_ps_log('gp_ps_update_shipping_cost', "test tipo disp docmicilio", $opcion_envio_test);

						if($opcion_envio_test){
							$disp_domicilio_test[$disp_domicilio_temp_test[$opcion_envio_test]['upc']] = array(
								'subtipo' => $disp_domicilio_temp_test[$opcion_envio_test]['subtipo'],
								'tienda' => $disp_domicilio_temp_test[$opcion_envio_test]['id'],
								'entrega_estimada' => $disp_domicilio_temp_test[$opcion_envio_test]['entrega_estimada'],
								'shipping' => $disp_domicilio_temp_test[$opcion_envio_test]['shipping']
							);
						}

						$this->gp_ps_log('gp_ps_update_shipping_cost', "test docmicilio info carrito", $disp_domicilio_test);
						$disp_tienda_temp_test = [];
						//* ---seleccionar tipo de envío tienda---
						$opcion_envio_test = '';
						if(isset($disp_tienda_temp_test['preventa'])){
							$opcion_envio_test = 'preventa';
						} elseif(isset($disp_tienda_temp_test['apartado'])){
							$opcion_envio_test = 'apartado';
						}
						$this->gp_ps_log('gp_ps_update_shipping_cost', "test tipo disp tienda", $opcion_envio_test);

						if($opcion_envio_test){
							$disp_tienda_test[$disp_tienda_temp_test[$opcion_envio_test]['upc']] = array(
								'subtipo' => $disp_tienda_temp_test[$opcion_envio_test]['subtipo'],
								'tienda' => $disp_tienda_temp_test[$opcion_envio_test]['id'],
								'entrega_estimada' => $disp_tienda_temp_test[$opcion_envio_test]['entrega_estimada'],
								'shipping' => $disp_tienda_temp_test[$opcion_envio_test]['shipping']
							);
						}

						$this->gp_ps_log('gp_ps_update_shipping_cost', "test tienda info carrito", $disp_tienda_test);
						$disp_tienda_temp_test = [];
						//! ------
						$opcion_envio = '';
						if(isset($disp_domicilio_temp['preventa'])){
							$opcion_envio = 'preventa';
						} elseif(isset($disp_domicilio_temp['standar'])){
							$opcion_envio = 'standar';
						} elseif(isset($disp_domicilio_temp['nextday'])){
							$opcion_envio = 'nextday';
						} elseif(isset($disp_domicilio_temp['sameday'])){
							$opcion_envio = 'sameday';
						} elseif(isset($disp_domicilio_temp['express'])){
							$opcion_envio = 'express';
						}

						if($opcion_envio){
							$disp_domicilio[$disp_domicilio_temp[$opcion_envio]['upc']] = array(
								'subtipo' => $disp_domicilio_temp[$opcion_envio]['subtipo'],
								'tienda' => $disp_domicilio_temp[$opcion_envio]['id'],
								'entrega_estimada' => $disp_domicilio_temp[$opcion_envio]['disponibilidad'],
								'shipping' => $disp_domicilio_temp[$opcion_envio]['shipping']
							);
						}

						$opcion_tienda = '';
						if(isset($disp_tienda_temp['preventa'])){
							$opcion_tienda = 'preventa';
						} elseif(isset($disp_tienda_temp['apartado'])){
							$opcion_tienda = 'apartado';
						}

						if($opcion_tienda){
							$disp_tienda[$disp_tienda_temp[$opcion_tienda]['upc']] = array(
								'subtipo' => $disp_tienda_temp[$opcion_tienda]['subtipo'],
								'tienda' => $disp_tienda_temp[$opcion_tienda]['id'],
								'entrega_estimada' => $disp_tienda_temp[$opcion_tienda]['disponibilidad'],
								'shipping' => $disp_tienda_temp[$opcion_tienda]['shipping']
							);
						}
					}
					foreach($carrito_ship as $llave => $item){
						$arr_disponibilidad = [];
						if(!isset($item['tipo']) || !isset($item['sku'])){
							WC()->cart->remove_cart_item( $llave );
							continue;
						} elseif($item['tipo'] == "domicilio"){
							$arr_disponibilidad = $disp_domicilio;
						} elseif($item['tipo'] == "tienda"){
							$arr_disponibilidad = $disp_tienda;
						} else{
							WC()->cart->remove_cart_item( $llave );
							continue;
						}

						foreach($arr_disponibilidad as $upc => $disp){
							if($item['sku'] == $upc){
								$carrito_ship[$llave]['tienda'] = $disp['tienda'];
								$carrito_ship[$llave]['subtipo'] = $disp['subtipo'];
								$carrito_ship[$llave]['shipping'] = $disp['shipping'];
								$carrito_ship[$llave]['entrega_estimada'] = $disp['entrega_estimada'];
							}
						}
					}
				} else{
					$this->gp_ps_log('gp_ps_update_shipping_cost', "disponibilidad code != 0", $ext_auth);
					remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
					return $rates;
				}
			} else{
				$this->gp_ps_log('gp_ps_update_shipping_cost', "disponibilidad success != true", $ext_auth);
				remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
				return $rates;
			}
		} else{
			$this->gp_ps_log('gp_ps_update_shipping_cost', "disponibilidad respuesta != 200", $response['response']);
			remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
			return $rates;
		}

		//! ---test---
		//* ---actualizar carrito disponibilidad---
		foreach($carrito_ship_temp as $llave_test => $item_test){
			$arr_disponibilidad_test = [];
			if(!isset($item_test['tipo']) || !isset($item_test['sku'])){
				WC()->cart->remove_cart_item( $llave_test );
				continue;
			} elseif($item_test['tipo'] == "domicilio"){
				$arr_disponibilidad_test = $disp_domicilio_test;
			} elseif($item_test['tipo'] == "tienda"){
				$arr_disponibilidad_test = $disp_tienda_test;
			}
			else{
				// WC()->cart->remove_cart_item( $llave );
				$this->gp_ps_log('gp_ps_update_shipping_cost', "carrito ship temp quitar", $llave_test);
				continue;
			}

			foreach($arr_disponibilidad_test as $upc_test => $disp_test){
				if($item_test['sku'] == $upc_test){
					$carrito_ship_temp[$llave_test]['tienda'] = $disp_test['tienda'];
					$carrito_ship_temp[$llave_test]['subtipo'] = $disp_test['subtipo'];
					$carrito_ship_temp[$llave_test]['shipping'] = $disp_test['shipping'];
					$carrito_ship_temp[$llave_test]['entrega_estimada'] = $disp_test['entrega_estimada'];
				}
			}
		}
		$this->gp_ps_log('gp_ps_update_shipping_cost', "carrito ship temp", $carrito_ship_temp);
		$datos_agrupados_temp = [];
		foreach($carrito_ship_temp as $item_temp => $value_temp){
			if(isset($value_temp['tienda']) && isset($value_temp['tipo']) && isset($value_temp['subtipo']) && isset($value_temp['shipping']) ){
				$datos_agrupados_temp[$value_temp['tipo']][$value_temp['tienda']][$value_temp['subtipo']][] = $value_temp['shipping'];
			} else{ //! añadir borrar carrito
				$this->gp_ps_log('gp_ps_update_shipping_cost', 'borrar carrito (temp)');
				remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
				WC()->cart->empty_cart();
				return $rates;
			}
		}
		$this->gp_ps_log('gp_ps_update_shipping_cost', 'datos agrupados test', $datos_agrupados_temp);

		$envios_temp = [];
		foreach($datos_agrupados_temp as $tipo_test => $tiendas_test){
			foreach($tiendas_test as $tienda_test => $subtipos_test){
				$suma_test = 0;
				// $suma_total_test = 0;
				foreach($subtipos_test as $subtipo_test => $productos_test){
					foreach($productos_test as $llave_test => $producto_test){
						foreach($carrito_ship_temp as $llave_test => $value_test){
							if($value_test['tienda'] == $tienda_test && $value_test['tipo'] == $tipo_test && $value_test['subtipo'] == $subtipo_test){
								$carrito_ship_temp[$llave_test]['shipping'] = number_format((float)$producto_test/count($productos_test), 2, '.', ',');
							}
						}
						// $suma_total_test += (float)$producto_test;
						// $this->gp_ps_log('gp_ps_update_shipping_cost', "test val suma total", (float)$producto_test);
						// $suma_total += (int)$productos[0];
						if(is_numeric($producto_test)){
							$suma_test += $producto_test/count($productos_test);
						} else{
							$this->gp_ps_log('gp_ps_update_shipping_cost', 'error al obtener costo, borrar carrito');
							remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
							// WC()->cart->empty_cart();
							return $rates;
						}
					}
				}
				array_push($envios_temp, $suma_test);
				// array_push($envios_test, $suma_total_test);
			}
		}
		$envio_temp = array_sum($envios_temp);
		$this->gp_ps_log('gp_ps_update_shipping_cost', "test suma", $envio_temp);
		$this->gp_ps_log('gp_ps_update_shipping_cost', "carrito ship temp edit", $carrito_ship_temp);
		//* ---actualiza costo total de envío---
		foreach($rates as $rate_key => $rate_value){
			$this->gp_ps_log('gp_ps_update_shipping_cost', 'modifico costo de envio');
			$rates[$rate_key]->cost = $envio_temp;
		}
		//* ---actualiza carrito---
		WC()->cart->set_cart_contents($carrito_ship_temp);
		WC()->cart->set_session();
		//* ---regreso costos---
		return $rates;
		//! ------

		foreach($carrito_ship as $item => $value){
			if(isset($value['tienda']) && isset($value['tipo']) && isset($value['subtipo']) && isset($value['shipping']) ){
				$datos_agrupados[$value['tipo']][$value['tienda']][$value['subtipo']][] = $value['shipping'];
			} else{ //! añadir borrar carrito
				$this->gp_ps_log('gp_ps_update_shipping_cost', 'borrar carrito');
				remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
				return $rates;
			}
		}
		$this->gp_ps_log('gp_ps_update_shipping_cost', "carrito ship disponibilidad", $carrito_ship);
		$this->gp_ps_log('gp_ps_update_shipping_cost', 'datos agrupados', $datos_agrupados);
		$envios = [];
		$envios_test = [];
		foreach($datos_agrupados as $tipo => $tiendas){
			foreach($tiendas as $tienda => $subtipos){
				$suma = 0;
				$suma_total = 0;
				foreach($subtipos as $subtipo => $productos){
					foreach($productos as $llave => $producto){
						foreach($carrito_ship as $llave => $value){
							if($value['tienda'] == $tienda && $value['tipo'] == $tipo && $value['subtipo'] == $subtipo){
								$carrito_ship[$llave]['shipping'] = number_format((float)$producto/count($productos), 2, '.', ',');
							}
						}
						$suma_total += (float)$producto;
						$this->gp_ps_log('gp_ps_update_shipping_cost', "test val suma total", (float)$producto);
						// $suma_total += (int)$productos[0];
						$suma += $producto/count($productos);
					}
				}
				array_push($envios, $suma);
				array_push($envios_test, $suma_total);
			}
		}
		$this->gp_ps_log('gp_ps_update_shipping_cost', "test suma total", $suma_total);
		$envio_t = array_sum($envios);
		$envio_test = array_sum($envios);
		$this->gp_ps_log('gp_ps_update_shipping_cost', "carrito ship actualizado", $carrito_ship);
		$this->gp_ps_log('gp_ps_update_shipping_cost', 'suma total', $envio_t);
		$this->gp_ps_log('gp_ps_update_shipping_cost', 'suma total test', $envio_test);

		foreach($rates as $rate_key => $rate_value){
			$this->gp_ps_log('gp_ps_update_shipping_cost', 'modifico costo de envio');
			// $rates[$rate_key]->cost = $envio;
			$rates[$rate_key]->cost = $envio_t;
		}
		if(did_action("woocommerce_after_get_rates_for_package") == 1){
			$this->gp_ps_log('gp_ps_update_shipping_cost', "actualizo etiquetas");
			// WC()->cart->set_cart_contents($carrito_ship);
			// WC()->cart->set_session();
		}

		// $this->gp_ps_log('gp_ps_update_shipping_cost', "array disponibilidad domicilio", $disp_domicilio);
		// $this->gp_ps_log('gp_ps_update_shipping_cost', "array disponibilidad tienda", $disp_tienda);
		$this->gp_ps_log('gp_ps_update_shipping_cost', "fin test disp\n-----");
		return $rates;
		//! -----
		// $this->gp_ps_log('gp_ps_update_shipping_cost', 'inicio');
		// $carrito = WC()->cart->get_cart();
		// $carrito_t = WC()->cart->get_cart();
		// $this->gp_ps_log('gp_ps_update_shipping_cost', 'carrito inicial', $carrito_t);

		// //! -----
		// $this->gp_ps_log('gp_ps_update_shipping_cost', '--test--');
		// $datos_agrupados = [];
		// foreach($carrito_t as $item => $value){
		// 	if(isset($value['tienda']) && isset($value['tipo']) && isset($value['subtipo']) && isset($value['shipping']) ){
		// 		$datos_agrupados[$value['tipo']][$value['tienda']][$value['subtipo']][] = $value['shipping'];
		// 	} else{ //! añadir borrar carrito
		// 		$this->gp_ps_log('gp_ps_update_shipping_cost', 'borrar carrito');
		// 	}
		// }

		// $envios = [];
		// foreach($datos_agrupados as $tipo => $tiendas){
		// 	foreach($tiendas as $tienda => $subtipos){
		// 		$suma = 0;
		// 		$suma_total = 0;
		// 		foreach($subtipos as $subtipo => $productos){
		// 			foreach($productos as $llave => $producto){
		// 				foreach($carrito_t as $llave => $value){
		// 					if($value['tienda'] == $tienda && $value['tipo'] == $tipo && $value['subtipo'] == $subtipo){
		// 						$carrito_t[$llave]['shipping'] = (string)number_format((float)$producto/count($productos), 2, '.', ',');
		// 					}
		// 				}
		// 				$suma_total += (int)$productos[0];
		// 				$suma += (int)$producto/count($productos);
		// 			}
		// 		}
		// 		array_push($envios, $suma);
		// 	}
		// }
		// $envio_t = 0;
		// $envio_t = array_sum($envios);
		// $this->gp_ps_log('gp_ps_update_shipping_cost', 'suma total', $envio_t);
		// $this->gp_ps_log('gp_ps_update_shipping_cost', 'carrito modificado', $carrito_t);

		// $this->gp_ps_log('gp_ps_update_shipping_cost', '--test--');
		//! -----

		// $costos = [];
		// foreach($carrito as $item => $value){
		// 	if(isset($value['shipping']) || isset($value['tipo'])){
		// 		$this->gp_ps_log('gp_ps_update_shipping_cost', 'shipping', $value['shipping']);
		// 		array_push($costos, $value['shipping']);
		// 	} else{
		// 		WC()->cart->empty_cart();
		// 	}
		// }

		// $envio = array_sum($costos);
		// $this->gp_ps_log('gp_ps_update_shipping_cost', 'suma', $envio);

		// foreach($rates as $rate_key => $rate_value){
		// 	$this->gp_ps_log('gp_ps_update_shipping_cost', 'modifico costo de envio');
		// 	// $rates[$rate_key]->cost = $envio;
		// 	$rates[$rate_key]->cost = $envio_t;
		// }

		// if(is_checkout() && did_action("woocommerce_after_get_rates_for_package") == 1){
		// 	foreach($rates as $rate_key => $rate_value){
		// 		$this->gp_ps_log('gp_ps_update_shipping_cost', 'modifico costo de envio');
		// 		// $rates[$rate_key]->cost = $envio;
		// 		$rates[$rate_key]->cost = $envio_t;
		// 	}
		// 	$this->gp_ps_log('gp_ps_update_shipping_cost', "actualizo etiquetas");
		// 	WC()->cart->set_cart_contents($carrito_t);
		// 	// WC()->cart->set_session();
		// } else{
		// 	foreach($rates as $rate_key => $rate_value){
		// 		$this->gp_ps_log('gp_ps_update_shipping_cost', 'modifico costo de envio');
		// 		$rates[$rate_key]->cost = $suma_total;
		// 	}
		// 	$this->gp_ps_log('gp_ps_update_shipping_cost', "envío suma total");
		// }
		// $this->gp_ps_log('gp_ps_update_shipping_cost', "fin\n-----");
		// return $rates;
	}

	/**
	 * Modifica etiqueta de costo de envío (carrito/checkout).
	 *
	 * @since    1.0.0
	 */
	public function gp_etiqueta_costo_envio( $label, $method ) {
		// $label     = $method->get_label();
		$label     = '';
		$has_cost  = 0 <= $method->cost;
		$hide_cost = ! $has_cost && in_array( $method->get_method_id(), array( 'free_shipping', 'local_pickup' ), true );

		if ( $has_cost && ! $hide_cost ) {
			if ( WC()->cart->display_prices_including_tax() ) {
				$label .= ' ' . wc_price( $method->cost + $method->get_shipping_tax() );
				if ( $method->get_shipping_tax() > 0 && ! wc_prices_include_tax() ) {
					$label .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
				}
			} else {
					$label .= '<span>$' . number_format(floatval($method->cost), 2) . '</span>';
				if ( $method->get_shipping_tax() > 0 && wc_prices_include_tax() ) {
					$label .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
				}
			}
		}
		return $label;
	}

	/**
	 * Modifica etiqueta de costo de envío (órdenes/email).
	 *
	 * @since    1.0.0
	 */
	public function gp_ps_email_shipping_label( $shipping, $order, $tax_display ) {
		if ( ! ( 0 < abs( (float) $order->get_shipping_total() ) ) && $order->get_shipping_method() ) {
		   $shipping = wc_price( 0 );
		}
		return $shipping;
	 }

	/**
	 * Verifica que se tenga la información del producto en cookie.
	 *
	 * @since    1.0.0
	 */
	public function gp_ps_add_to_cart_validation( $passed, $product_id, $quantity, $variation_id=null ) {
		if($quantity > 12){
			$passed = false;
			wc_add_notice( 'Inventario insuficiente.', 'error' );
		}

		$tipo_seleccionado = false;
		$product = wc_get_product( $product_id );

		// bloqueo venta digital
		$link_catalogo = site_url('/catalogo/?buscar_productos=1&stock=instock');
        if ($product->is_virtual()) {
            wc_add_notice( "Por el momento no podemos ofrecerte este producto en línea. Te sugerimos buscar más productos en nuestro <a class='gp_underline' style='margin: 0;' href='{$link_catalogo}'>catálogo</a>.<span style='color: #e9cfcf;'>Code: PS-020</span>", 'error' );
            $passed = false;
			return $passed;
        }

		if(!isset($_COOKIE["_gp_data"])){
			$passed = false;
			wc_add_notice( 'Habilite las cookies en su navegador antes de continuar. Code: PS-016', 'error' );
			return $passed;
		} else{
			//! validar que cookie por lo menos tenga tipo de envio
			$tempo = json_decode(stripslashes($_COOKIE['_gp_data']), true);

			foreach($tempo as $key => $val){
				if($key == 'id_garantia' && $val != 'gp_no_garantia'){
					if($quantity > 1){
						$passed = false;
						wc_add_notice( 'Por el momento solo se puede añadir 1 producto con garantía. Code: PS-019', 'error' );
						return $passed;
					}
				}
				
				if($key == 'tipo' && ($val == 'tienda' || $val == 'domicilio')){
					if($val == 'tienda' && $quantity > 1){
						$passed = false;
						wc_add_notice( 'Por el momento solo se puede apartar 1 producto a la vez. Code: PS-018', 'error' );
						return $passed;
					}
					$tipo_seleccionado = true;
				}
			}
			$plat = $product->get_attribute('pa_plataforma');
			$plat_digital = array('BH', 'GP');
        	if(in_array($plat, $plat_digital)){
				if($quantity > 1){
					wc_add_notice( 'Por el momento solo se puede añadir uno de estos productos al carrito.', 'error' );
					$passed = false;
					return $passed;
				} elseif(!WC()->cart->is_empty()){
					foreach(WC()->cart->get_cart() as $key => $values ) {
						$prod_cart_id = $values['product_id'];
						if($prod_cart_id == $product_id ){
							WC()->cart->remove_cart_item($key);
						}
					}
				}
			}
		}

		if(!$tipo_seleccionado){
			$passed = false;
			wc_add_notice( 'Algo salió mal al obtener la información del producto. <span style="color: #e9cfcf;">Code: PS-017</span>', 'error' );
		}

		return $passed;
	}

	/**
	 * Añade shortcodes.
	 *
	 * @since    1.0.0
	 */
	public function gp_ps_register_shortcodes(){
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/gameplanet-planetshop-shortcodes.php';
		add_shortcode("short_de", "gp_sc_donde_estoy");
		add_shortcode("short_autocomplete", "gp_autocomplete");
		add_shortcode("short_tienda", "gp_tienda");
		add_shortcode("sc_test", "test_sc");
		//! test disponibilidad
		add_shortcode("short_disp", "test_disponibilidad");
	}

	/**
	 * Obtiene disponibilidad de producto simple para botón "ver disponibilidad en sucursal".
	 *
	 * @since    1.0.0
	 */
	public function ps_disponibilidad( $request ) {
		$this->gp_ps_log('ps_disponibilidad', 'inicio');
		$data = json_decode(file_get_contents("php://input"));
		if($data){
			if(isset($data->productos)){
				$headers = array();
				foreach(getallheaders() as $name => $value){
					$headers[$name] = $value;
				}

				// if(!isset($headers['x_wp_n11']) || !wp_verify_nonce( $headers['x_wp_n11'], '7#Ez&G2tZ{>z]KUn') ){
				// 	return "Alto, sin N11!";
				// }

				$args = array(
					'body' => json_encode(($data)),
					'headers' => array(
						'Content-Type' => 'application/json',
						'data'         => get_option('data-tendero')
					)
				);
				$this->gp_ps_log('ps_disponibilidad', 'body', $args);

				$url = get_option('ruta_tendero') . "producto/disponibilidad2";
				$this->gp_ps_log('ps_disponibilidad', 'endpoint', $url);

				// response de la petición
				$response = wp_remote_post($url, $args); //!
				if (is_wp_error($response)) {
					$mensaje_error = $response->get_error_message();
					$this->gp_ps_log('ps_disponibilidad', 'Error WP', $mensaje_error);
					$this->gp_ps_log('ps_disponibilidad', "\n-----");
					return "Error al obtener la disponibilidad de este producto. Code:PS-001";
				}

				// cachamos codigo http (['response']['code'])
				if ($response['response']['code'] == 200) {
					// obtenemos el body
					$ext_auth = json_decode($response['body'], true);
					//! $this->gp_ps_log('ps_disponibilidad', 'Response', $ext_auth);

					// Success == true
					if ($ext_auth['success']) {

						// cachamos el codigo ( 0 == operacion exitosa)
						if ($ext_auth['code'] == 0) {
							$domicilio = $ext_auth['result'][0]['domicilio'];
							$tienda = $ext_auth['result'][0]['tienda'];

							$informacion_envio = array();

							$env_domicilio = [];
							$apart_tienda = [];
							$entrega_estimada = '';

							foreach($domicilio as $opcion => $tipo){
								if($tipo['disponible']){
									$entrega_estimada = $tipo['disponibilidad']['entrega_estimada'];
									foreach($tipo['almacenes'] as $almacen => $datos){
										if($datos['cantidad']){
											$env_domicilio = array(
												'tipo' => 'domicilio',
												'subtipo' => $tipo['subtipo'],
												'id' => $datos['id'],
												'nombre' => $datos['nombre'],
												'direccion' => $datos['ubicacion'],
												'cantidad' => $datos['cantidad'],
												'entrega' => $entrega_estimada,
											);
											array_push($informacion_envio, $env_domicilio);
											break 2;
										}
									}
								}
							}

							if(count($env_domicilio) == 0){
								$env_domicilio = array(
									'mensaje_domicilio' => ''
								);
								array_push($informacion_envio, $env_domicilio);
							}

							$num_almacenes = 0;
							$almacenes_s_disp = 0;
							foreach($tienda as $opcion => $tipo){
								// if($tipo['disponible']){
									foreach($tipo['almacenes'] as $almacen => $datos){
										$num_almacenes++;
										if($datos['cantidad'] == 0){
											$almacenes_s_disp++;
										}
										$apart_tienda = array(
											'tipo' => 'tienda',
											'subtipo' => $tipo['subtipo'],
											'id' => $datos['id'],
											'nombre' => $datos['nombre'],
											'direccion' => $datos['direccion'],
											'telefono' => $datos['telefono'],
											'cantidad' => $datos['cantidad']);
										array_push($informacion_envio, $apart_tienda);
											// break 2;
									}
								// }
							}

							if(count($apart_tienda) == 0 || $num_almacenes == $almacenes_s_disp){
								$apart_tienda = array(
									'mensaje_tienda' => ''
								);
								array_push($informacion_envio, $apart_tienda);
							}

							$this->gp_ps_log('ps_disponibilidad', 'Datos test', [$num_almacenes, $almacenes_s_disp]);
							$this->gp_ps_log('ps_disponibilidad', 'Domicilio', $env_domicilio);
							$this->gp_ps_log('ps_disponibilidad', 'Tienda', $apart_tienda);
							$this->gp_ps_log('ps_disponibilidad', 'Información', $informacion_envio);
							$this->gp_ps_log('ps_disponibilidad', "\n-----" );
							return $informacion_envio;

						} else{
							$this->gp_ps_log('ps_disponibilidad', 'Código no esperado', $ext_auth['code']);
							$this->gp_ps_log('ps_disponibilidad', "\n-----" );
							return "Parece que no encontramos ningún producto disponible. Code:PS007";
						}
					} else{
						$this->gp_ps_log('ps_disponibilidad', '1 Respuesta no esperada', $ext_auth['success']);
						$this->gp_ps_log('ps_disponibilidad', "\n-----" );
						return "Parece que no encontramos ningún producto disponible. Code:PS006";
					}
				} else{
					$this->gp_ps_log('ps_disponibilidad', '2 Respuesta no esperada', $response['response']['code']);
					$this->gp_ps_log('ps_disponibilidad', "\n-----" );
					return "No pudimos obtener la información, inténtelo más tarde. Code:PS005";
				}


			} else{
				$this->gp_ps_log('ps_disponibilidad', 'sin producto/tienda.', "\n-----");
				return "Alto, desconocido!";
			}
		} else{
			$this->gp_ps_log('ps_disponibilidad', 'sin data.', "\n-----");
			return "Alto, sin data!";
		}
	}

	/**
	 * Obtiene disponibilidad de producto simple para widget.
	 *
	 * @since    1.0.0
	 */
	public function ps_f_disponibilidad(){
		$data = json_decode(file_get_contents("php://input"));

		$headers = array();
		foreach(getallheaders() as $name => $value){
			$headers[$name] = $value;
		}

		if(isset($data)){
			$upc_temp = trim($data->upc);
			$tienda_fav_temp = trim($data->tienda_fav);
			$tienda_selec_temp = trim($data->tienda_selec);
			$solicitud_temp = trim($data->solicitud);
			$lat_temp = trim($data->lat);
			$lng_temp = trim($data->lng);
		} else{
			return null;
		}

		$upc = filter_var($upc_temp, FILTER_SANITIZE_ENCODED);
		$tienda_fav = filter_var($tienda_fav_temp, FILTER_SANITIZE_ENCODED);
		$tienda_selec = filter_var($tienda_selec_temp, FILTER_SANITIZE_ENCODED);
		$solicitud = filter_var($solicitud_temp, FILTER_SANITIZE_ENCODED);
		$lat = filter_var($lat_temp, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
		$lng = filter_var($lng_temp, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);


		// 65e9285c6d
		if(!isset($headers['x_wp_n11']) || !wp_verify_nonce( $headers['x_wp_n11'], '7#Ez&G2tZ{>z]KUn') ){
			// return "n11 diferente";
		}

		$tipo_producto = 'fisico';
		if(str_starts_with($upc, 'P')){
			$tipo_producto = 'preventa';
			$datos_domicilio = [
				'preventa' => [
					'solicitud' => 1,
					'metodo' => 'cache',
					'cantidad_min' => $solicitud,
				],
			];
			$datos_tienda = [
				'preventa' => [
					'solicitud' => 1,
					'metodo' => 'cache',
					'cantidad_min' => $solicitud,
				],
			];
		} else{

			$datos_domicilio = [
				'express' => [
				  'solicitud' => 1,
				  'metodo' => 'cache',
				  'cantidad_min' => $solicitud,
				],
				'sameday' => [
				  'solicitud' => 1,
				  'metodo' => 'cache',
				  'cantidad_min' => $solicitud,
				],
				'standard' => [
				  'solicitud' => 1,
				  'metodo' => 'cache',
				  'cantidad_min' => $solicitud,
				],
				'nextday' => [
				  'solicitud' => 1,
				  'metodo' => 'cache',
				  'cantidad_min' => $solicitud,
				],
			];

			$datos_tienda = [
				'apartado' => [
					'solicitud' => 1,
					'metodo' => 'cache',
					'cantidad_min' => $solicitud,
				],
			];
		}

		$url = site_url();
		$disallowed = array('http://', 'https://');
		$dir = '';
		foreach($disallowed as $d) {
			if(strpos($url, $d) === 0) {
				$dir = str_replace($d, '', $url);
			}
		}

		$datos = [
			'productos' => [
			  0 => [
				'upc' => $upc,
				'surtidor' => 'GAM',
				'origen' => $dir,
				'tipo' => $tipo_producto,
				'lat' => $lat,
				'lng' => $lng,
				'id_tienda_favorita' => $tienda_fav,
				'id_tienda_seleccionada' => $tienda_selec,
				'domicilio' => $datos_domicilio,
				'tienda' => $datos_tienda,
			  ],
			],
		];
		$args = array(
			'body' => json_encode($datos),
			'headers' => array(
				'Content-Type' => 'application/json',
				'data'         => get_option('data-tendero')
			)
		);
		$this->gp_ps_log("ps_f_disponibilidad", "body", $args);

		$url = get_option('ruta_tendero') . "producto/disponibilidad2";

		// response de la petición
		$response = wp_remote_post($url, $args); //!
		$this->gp_ps_log("ps_f_disponibilidad", "response", $response);
		if (is_wp_error($response)) {
			$mensaje_error = $response->get_error_message();
			$this->gp_ps_log("ps_f_disponibilidad", "fin\n-----");
			return "Error al obtener la disponibilidad de este producto. Code:PS-003";
		}

		if ($response['response']['code'] == 200) {
			$ext_auth = json_decode($response['body'], true);

			if ($ext_auth['success']) {
				if ($ext_auth['code'] == 0) {
					$resultado = $ext_auth['result'];

					$disp_domicilio = [];
					foreach($resultado[0]['domicilio'] as $key => $tipo){
						if($tipo['subtipo'] == 'preventa'){
							foreach($tipo['almacenes'] as $key => $almacen){
								if($almacen['cantidad'] >= $solicitud){
									$entrega_estimada = $tipo['disponibilidad']['entrega_estimada'];
									if(isset($resultado[0]['fecha_lanzamiento_confirmada']) && !$resultado[0]['fecha_lanzamiento_confirmada']){
										$entrega_estimada = "no definida";
									}
									$disp_domicilio += array(
										$tipo['subtipo'] =>  array(
											'id_tipo_envio' => 'domicilio',
											'id_subtipo_envio' => $tipo['subtipo'],
											'shipping' => $tipo['shipping'],
											'id_tienda' => $almacen['id'],
											'nombre_tienda' => $almacen['nombre'],
											'entrega_estimada' => $entrega_estimada,
											'disponible' => $tipo['disponible'],
											'cantidad' => $almacen['cantidad'],
											'monto_minimo' => $tipo['monto_minimo'],
										 )
									);
									break 2;
								}
							}
						} else{
							foreach($tipo['almacenes'] as $key => $almacen){
								if($almacen['cantidad'] >= $solicitud){
									$disp_domicilio += array(
										$tipo['subtipo'] =>  array(
											'id_tipo_envio' => 'domicilio',
											'id_subtipo_envio' => $tipo['subtipo'],
											'shipping' => $tipo['shipping'],
											'id_tienda' => $almacen['id'],
											'nombre_tienda' => $almacen['nombre'],
											'entrega_estimada' => $tipo['disponibilidad']['entrega_estimada'],
											'disponible' => $tipo['disponible'],
											'cantidad' => $almacen['cantidad']
										 )
									);
									break;
								}
							}
						}
					}

					$disp_tienda = [];
					foreach($resultado[0]['tienda'] as $key => $tipo){
						if($tipo['subtipo'] == 'apartado'){
							foreach($tipo['almacenes'] as $key => $almacen){
								if($almacen['cantidad'] >= $solicitud){
									$disp_tienda += [
										$tipo['subtipo'] => [
											'id_tipo_envio' => 'tienda',
											'id_subtipo_envio' => $tipo['subtipo'],
											'shipping' => $tipo['shipping'],
											'id_tienda' => $almacen['id'],
											'nombre_tienda' => $almacen['nombre'],
											'entrega_estimada' => $tipo['disponibilidad']['entrega_estimada'],
											'disponible' => $tipo['disponible'],
											'cantidad' => $almacen['cantidad']
										]
									];
									break 2;
								}
							}
						} elseif($tipo['subtipo'] == 'preventa'){
							foreach($tipo['almacenes'] as $key => $almacen){
								if($almacen['cantidad'] >= $solicitud){
									$entrega_estimada = $tipo['disponibilidad']['entrega_estimada'];
									if(isset($resultado[0]['fecha_lanzamiento_confirmada']) && !$resultado[0]['fecha_lanzamiento_confirmada']){
										$entrega_estimada = "no definida";
									}
									$disp_tienda += [
										$tipo['subtipo'] => [
											'id_tipo_envio' => 'tienda',
											'id_subtipo_envio' => $tipo['subtipo'],
											'shipping' => $tipo['shipping'],
											'id_tienda' => $almacen['id'],
											'nombre_tienda' => $almacen['nombre'],
											'entrega_estimada' => $entrega_estimada,
											'disponible' => $tipo['disponible'],
											'cantidad' => $almacen['cantidad'],
											'monto_minimo' => $tipo['monto_minimo'],
										]
									];
									break 2;
								}
							}
						}
					}

					$respuesta_disp = ['domicilio' => $disp_domicilio] + ['tienda' => $disp_tienda];
					// $this->gp_ps_log("ps_f_disponibilidad", "test", ['domicilio' => $disp_domicilio] + ['tienda' => $disp_tienda]);
					$this->gp_ps_log("ps_f_disponibilidad", "fin\n-----");
					return $respuesta_disp;
				} else{
					$this->gp_ps_log("ps_f_disponibilidad", "fin\n-----");
					return "Error al obtener la disponibilidad de este producto. Code:PS-012";
				}
			} else{
				$this->gp_ps_log("ps_f_disponibilidad", "fin\n-----");
				return "Error al obtener la disponibilidad de este producto. Code:PS-011";
			}

		} else{
			$this->gp_ps_log("ps_f_disponibilidad", "fin\n-----");
			return "Error al obtener la disponibilidad de este producto. Code:PS-010";
		}

		// return $headers['x_wp_n11'];
	}

	public function ps_f_disponibilidad_tienda(){
		$data = json_decode(file_get_contents("php://input"));

		if($data){
			$this->gp_ps_log('ps_f_disponibilidad_tienda', 'obtenemos headers');

			$headers = array();
			foreach(getallheaders() as $name => $value){
				$headers[$name] = $value;
			}
			if(!isset($headers['x_wp_n11']) || !wp_verify_nonce( $headers['x_wp_n11'], '7#Ez&G2tZ{>z]KUn') ){
				$this->gp_ps_log('ps_f_disponibilidad_tienda', 'nonce no es igual');
				// return "Alto, sin N11!";
			}

			$upc_temp = trim($data->upc);
			$tienda_fav_temp = trim($data->tienda_fav);
			$tienda_selec_temp = trim($data->tienda_selec);
			$solicitud_temp = trim($data->solicitud);
			$lat_temp = trim($data->lat);
			$lng_temp = trim($data->lng);

			$upc = filter_var($upc_temp, FILTER_SANITIZE_ENCODED);
			$tienda_fav = filter_var($tienda_fav_temp, FILTER_SANITIZE_ENCODED);
			$tienda_selec = filter_var($tienda_selec_temp, FILTER_SANITIZE_ENCODED);
			$solicitud = filter_var($solicitud_temp, FILTER_SANITIZE_ENCODED);
			$lat = filter_var($lat_temp, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
			$lng = filter_var($lng_temp, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

			$tipo_producto = 'fisico';
			if(str_starts_with($upc, 'P')){
				$tipo_producto = 'preventa';
				$datos_domicilio = [
					'preventa' => [
					  'solicitud' => 1,
					  'metodo' => 'cache',
					  'cantidad_min' => $solicitud,
					],
				];
				$datos_tienda = [
					'preventa' => [
					  'solicitud' => 1,
					  'metodo' => 'cache',
					  'cantidad_min' => $solicitud,
					],
				];
			} else{
				$datos_domicilio = [
					'express' => [
					  'solicitud' => 1,
					  'metodo' => 'cache',
					  'cantidad_min' => $solicitud,
					],
					'sameday' => [
					  'solicitud' => 1,
					  'metodo' => 'cache',
					  'cantidad_min' => $solicitud,
					],
					'standard' => [
					  'solicitud' => 1,
					  'metodo' => 'cache',
					  'cantidad_min' => $solicitud,
					],
					'nextday' => [
					  'solicitud' => 1,
					  'metodo' => 'cache',
					  'cantidad_min' => $solicitud,
					],
				];
				$datos_tienda = [
					'apartado' => [
					  'solicitud' => 1,
					  'metodo' => 'cache',
					  'cantidad_min' => $solicitud,
					],
				];
			}

			$url = site_url();
			$disallowed = array('http://', 'https://');
			$dir = '';
			foreach($disallowed as $d) {
				if(strpos($url, $d) === 0) {
					$dir = str_replace($d, '', $url);
				}
			}

			$datos = [
				'productos' => [
				  0 => [
					'upc' => $upc,
					'surtidor' => 'GAM',
					'origen' => $dir,
					'tipo' => $tipo_producto,
					'lat' => $lat,
					'lng' => $lng,
					'id_tienda_favorita' => $tienda_fav,
					'id_tienda_seleccionada' => $tienda_selec,
					'domicilio' => $datos_domicilio,
					'tienda' => $datos_tienda,
				  ],
				],
			];

			$args = array(
				'body' => json_encode($datos),
				'headers' => array(
					'Content-Type' => 'application/json',
					'data'         => get_option('data-tendero')
				)
			);
			// $this->gp_ps_log('ps_f_disponibilidad_tienda', 'body', $args);

			$url = get_option('ruta_tendero') . "producto/disponibilidad2";
			// $this->gp_ps_log('ps_f_disponibilidad_tienda', 'endpoint', $url);

			// response de la petición
			$response = wp_remote_post($url, $args); //!
			if (is_wp_error($response)) {
				$mensaje_error = $response->get_error_message();
				$this->gp_ps_log('ps_f_disponibilidad_tienda', 'Error WP', $mensaje_error);
				$this->gp_ps_log('ps_f_disponibilidad_tienda', "Fin\n-----");
				return "Error al obtener la disponibilidad de este producto. Code:PS-004";
			}

			// cachamos codigo http (['response']['code'])
			if ($response['response']['code'] == 200) {
				// obtenemos el body
				$ext_auth = json_decode($response['body'], true); //!
				$this->gp_ps_log('ps_f_disponibilidad_tienda', 'Response', $ext_auth);

				// Success == true
				if ($ext_auth['success']) {

					// cachamos el codigo ( 0 == operacion exitosa)
					if ($ext_auth['code'] == 0) {
						$domicilio = $ext_auth['result'][0]['domicilio'];
						$tienda = $ext_auth['result'][0]['tienda'];

						$informacion_envio = array();

						$apart_tienda = [];
						$entrega_estimada = '';

						foreach($tienda as $opcion => $tipo){
							// if($tipo['disponible']){
								foreach($tipo['almacenes'] as $almacen => $datos){
									// if($datos['cantidad']){
										$apart_tienda = array(
											'tipo' => 'tienda',
											'subtipo' => $tipo['subtipo'],
											'id' => $datos['id'],
											'tienda' => $datos['nombre'],
											'nombre' => $datos['nombre'],
											'direccion' => $datos['direccion'],
											'telefono' => $datos['telefono'],
											'cantidad' => $datos['cantidad']);
										array_push($informacion_envio, $apart_tienda);
										// break 2;
									// }
								}
							// }
						}

						if(count($apart_tienda) == 0){
							$apart_tienda = array(
								'mensaje_tienda' => ''
							);
							array_push($informacion_envio, $apart_tienda);
						}

						$this->gp_ps_log('ps_f_disponibilidad_tienda', 'Tienda', $apart_tienda);
						$this->gp_ps_log('ps_f_disponibilidad_tienda', "Fin\n-----" );
						return $informacion_envio;

					} else{
						$this->gp_ps_log('ps_f_disponibilidad_tienda', 'Código no esperado', $ext_auth['code']);
						$this->gp_ps_log('ps_f_disponibilidad_tienda', "Fin\n-----" );
						return "Parece que no encontramos ningún producto disponible. Code:PS-015";
					}
				} else{
					$this->gp_ps_log('ps_f_disponibilidad_tienda', '3 Respuesta no esperada', $ext_auth['success']);
					$this->gp_ps_log('ps_f_disponibilidad_tienda', "Fin\n-----" );
					return "Parece que no encontramos ningún producto disponible. Code:PS-014";
				}
			} else{
				$this->gp_ps_log('ps_f_disponibilidad_tienda', '4 Respuesta no esperada', $response['response']['code']);
				$this->gp_ps_log('ps_f_disponibilidad_tienda', "Fin\n-----" );
				return "No pudimos obtener la información, inténtelo más tarde. Code:PS-013";
			}
		}
	}

	/**
	 * Despliega el método de pago correspondiente al tipo de entrega.
	 *
	 * @since    1.0.0
	 */
	public function gp_ps_return_payment_method($available_gateways ){
		if(!is_admin()){
			$cantidad = 0;
			if(is_object(WC()->cart)){
				$cantidad = WC()->cart->get_cart_contents_count();
			}
			if($cantidad == 0 || is_null(WC()->cart->get_cart())){
				return false;
			}
			// if(!current_user_can( 'administrator' )){
			// 	if ( isset( $available_gateways['stripe'] )){
			// 		unset( $available_gateways['stripe'] );
			// 	}
			// }
			$carrito = WC()->cart->get_cart();
			foreach($carrito as $item => $value){
				if(isset($value['subtipo'])){
					if($value['subtipo'] == 'preventa'){
						if ( isset( $available_gateways['conektaoxxopay'] )){
							unset( $available_gateways['conektaoxxopay'] );
						}
					}
				}
				if(isset($value['tipo'])){
					if($value['tipo'] == 'domicilio'){
						if ( isset( $available_gateways['cod'] )){
							unset( $available_gateways['cod'] );
						}
						if ( isset( $available_gateways['saldo_gp'] )){
							$available_gateways['saldo_gp']->order_button_text = __( 'Pagar con saldo Gameplanet', 'woocommerce' );
						}
						if ( isset( $available_gateways['woo-mercado-pago-basic'] )){
							$available_gateways['woo-mercado-pago-basic']->order_button_text = __( 'Pagar con Mercado Pago', 'woocommerce' );
						}
					}
					if($value['tipo'] == 'tienda'){
						if($value['subtipo'] == 'preventa'){
							if ( isset( $available_gateways['cod'] )){
								unset( $available_gateways['cod'] );
							}
						} else{
							if ( isset( $available_gateways['ppcp-gateway'] )){
								unset( $available_gateways['ppcp-gateway'] );
							}
							if ( isset( $available_gateways['saldo_gp'] )){
								unset( $available_gateways['saldo_gp'] );
							}
							if ( isset( $available_gateways['woo-mercado-pago-basic'] )){
								unset( $available_gateways['woo-mercado-pago-basic'] );
							}
							if ( isset( $available_gateways['conektacard'] )){
								unset( $available_gateways['conektacard'] );
							}
							if ( isset( $available_gateways['openpay_cards'] )){
								unset( $available_gateways['openpay_cards'] );
							}
							if ( isset( $available_gateways['conektaoxxopay'] )){
								unset( $available_gateways['conektaoxxopay'] );
							}
							if ( isset( $available_gateways['wc_rappipay'] )){
								unset( $available_gateways['wc_rappipay'] );
							}
							if ( isset( $available_gateways['stripe'] )){
								unset( $available_gateways['stripe'] );
							}
						}
					}
				} else{
					WC()->cart->empty_cart();
					remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
				}
			}

			if(count($available_gateways) == 0){
				remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
			}
		}

		return $available_gateways;

	}

	public function gp_add_custom_field_shipping_form($fields){
		$fields[ 'gp_lat_shipping' ]   = array(
			'label'        => 'latitud',
			'required'     => true,
			'class'        => array( 'form-row', 'form-row-first', 'hidden'),
			'priority'     => 20
		);
		$fields[ 'gp_lng_shipping' ]   = array(
			'label'        => 'longitud',
			'required'     => true,
			'class'        => array( 'form-row', 'form-row-last', 'hidden'),
			'priority'     => 20
		);
		$fields[ 'gp_shipping_address' ]   = array(
			'label'        => 'datos dirección',
			'required'     => false,
			'class'        => array( 'form-row', 'form-row-last', 'hidden'),
			'priority'     => 20
		);

		$fields[ '_gp_autocompletado' ]   = array(
            'label'        => 'Dirección <span class="gp_autocompletado_tag">autocompletado</span>',
            'required'     => true,
            'class'        => array( 'form-row', ''),
            'priority'     => 41,
            'placeholder'  => ''
        );
        $fields[ '_gp_exterior_number' ]   = array(
            'label'        => 'Número exterior',
            'required'     => true,
            'class'        => array( 'form-row-first', ''),
            'priority'     => 51,
        );
        $fields[ '_gp_interior_number' ]   = array(
            'label'        => 'Número interior',
            'required'     => false,
            'class'        => array( 'form-row-last', ''),
            'priority'     => 51,
        );
		$fields[ '_gp_suburb' ]   = array(
            'label'        => 'Colonia',
            'required'     => true,
            'class'        => array( 'form-row', ''),
            'priority'     => 71,
        );

		unset($fields['shipping_address_2']);
		unset($fields['shipping_company']);

		return $fields;
	}

	public function gp_ps_modify_display_item_meta( $html, $item, $args ) {
		$strings = array(
			'Categoría' => '',
			'espacio' => '<p style="margin-bottom: 1em !important;"> </p>',
			'Ticket' => '',
			'Estatus' => '',
			'Recoge en' => '',
			'Recoge de' => '',
			'espacio1' => '<p style="margin-bottom: 1em !important;"> </p>',
		);
		$html    = '';
		$args    = wp_parse_args(
			$args,
			array(
				'before'       => '<div class="wc-item-meta "><p class="gp-p-meta">',
				'after'        => '</p></div>',
				'separator'    => '</p><p class="gp-p-meta">',
				'echo'         => true,
				'autop'        => true,
				'label_before' => '<strong class="wc-item-meta-label">',
				'label_after'  => '</strong> ',
			)
		);

		foreach ( $item->get_all_formatted_meta_data() as $meta_id => $meta ) {
			$value = $args['autop'] ? wp_kses_post( $meta->display_value ) : wp_kses_post( make_clickable( trim( $meta->display_value ) ) );

			switch($meta->display_key){

				// sin etiqueta
				case 'Categoría':{
					$strings[$meta->display_key] = wp_strip_all_tags($value);
					break;
				}

				// con etiqueta
				case 'Recoge de':{
					$texto = $args['label_before'] . ( $meta->display_key ) . $args['label_after'] . ' <span >' . wp_strip_all_tags($value) . '</span>';
					$meses = array(
						'January' => 'enero',
						'February' => 'febrero',
						'March' => 'marzo',
						'April' => 'abril',
						'May' => 'mayo',
						'June' => 'junio',
						'July' => 'julio',
						'August' => 'agosto',
						'September' => 'septiembre',
						'October' => 'octubre',
						'November' => 'noviembre',
						'Decembe' => 'diciembre',
					);
					$texto = str_ireplace(  array_keys($meses),  $meses,  $texto );
					$strings[$meta->display_key] = $texto;
					break;
				}
				case 'Ticket':
				case 'Costo envío':
				case 'Tienes':
				case 'Estatus':{
					$strings[$meta->display_key] = $args['label_before'] . ( $meta->display_key ) . $args['label_after'] . ' <span >' . wp_strip_all_tags($value) . '</span>';
					break;
				}

				// forzar borrado
				case 'Entrega':
				case 'Plataforma':
				case 'Condición':{
					break;
				}

				// especiales
				case 'Recoge en':{
					$strings[$meta->display_key] = '<span class="gp_entrega_en">' . ( $meta->display_key ) . ' <span>' . wp_strip_all_tags($value) . '</span></span>';
					break;
				}

				default:{
					$strings[$meta->display_key] = $args['label_before'] . ( $meta->display_key ) . $args['label_after'] . $value;
					break;
				}
			}
		}

		if ( $strings ) {
			$html = $args['before'] . implode( $args['separator'], array_filter($strings) ) . $args['after'];
		}

		$html = apply_filters( 'woocommerce_display_item_meta', $html, $item, $args );

		// if ( $args['echo'] ) {
		// 	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		// 	echo $html;
		// } else {
		// }
		return $html;
	}

	/**
	 * Quital a confirmación de "salir de cuenta".
	 *
	 * @since    1.0.0
	 */
	public function wc_bypass_logout_confirmation() {
		global $wp;

		if ( isset( $wp->query_vars['customer-logout'] ) ) {
			wp_redirect( str_replace( '&amp;', '&', wp_logout_url( wc_get_page_permalink( 'myaccount' ) ) ) );
			exit;
		}
	}

	public function gp_ps_create_addrss_cookie_login($user_login, $user){
		if(!empty($user->gp_lat) && !empty($user->gp_lng) && !empty($user->shipping_address_1) && !empty($user->gp_shipping_address_field) && !empty($user->shipping_postcode) ){

			$datos = explode('|', $user->gp_shipping_address_field);
			if(isset($datos[3])){

			}

			setcookie('_gp_geo_lng', $user->gp_lng_shipping, time()+31556926, '/' );
			setcookie('_gp_geo_lat', $user->gp_lat_shipping, time()+31556926, '/' );
			setcookie('_gp_geo_address_short', urlencode($user->gp_shipping_address_field), time()+31556926, '/' );
			setcookie('_gp_geo_address_long', urlencode($user->shipping_address_1), time()+31556926, '/' );
			setcookie('_gp_geo_pc', $user->shipping_postcode, time()+31556926, '/' );
		}
	}

	/**
	 * Quital campos de "datos de tu cuenta".
	 *
	 * @since    1.0.0
	 */
	public function gp_ps_remove_billing_fields( $fields ) {
		// unset($fields['billing_postcode']);
		// unset($fields['billing_state']);
		// unset($fields['billing_country']);
		// unset($fields['billing_address_1']);
		// unset($fields['billing_address_2']);
		// unset($fields['billing_city']);
		// return $fields;
		//!--

		$fields[ 'billing_country' ][ 'priority' ] = -100;
		//$fields[ 'billing_email' ][ 'priority' ] = 0;
		//$fields[ 'billing_phone' ][ 'priority' ] = -100;
		$fields['billing_company']['required'] = false;
		$fields['billing_city']['required'] = false;
		$fields['billing_postcode'] = array('label' => 'Código postal', 'required' => false);
		//$fields['billing_country'] = array('label' => 'País', 'required' => false);
		$fields['billing_state'] = array('label' => 'Región - Estado', 'required' => false);;
		$fields['billing_address_1']['required'] = false;
		$fields['billing_address_2']['required'] = false;

		//	unset( $fields['billing_first_name'] );
		//	unset( $fields['billing_last_name'] );
		unset( $fields['billing_company'] );
		unset( $fields['billing_city'] );
		unset( $fields['billing_postcode'] );
		//	unset( $fields['billing_country'] );
		unset( $fields['billing_state'] );
		unset( $fields['billing_address_1'] );
		unset( $fields['billing_address_2'] );
			unset( $fields['billing_phone'] );
		//	unset( $fields['billing_email'] );

		return $fields;
	}

	/**
	 * Quital botón en checkout que elimina producto del carrito.
	 *
	 * @since    1.0.0
	 */
	public function gp_ps_delete_btn_remove_from_checkout( $product_name, $cart_item, $cart_item_key ){
		$name = preg_replace('~<a(.*?)title="Remove"(.*?)</a>~Usi', "", $product_name);
		return $name;
	}

	/**
	 * Añade mensaje en página thx si tiene apartado.
	 *
	 * @since    1.0.0
	 */
	public function gp_ps_thx_message($order_id) {
		$order = wc_get_order( $order_id );
		// foreach($order->get_items() as $key => $item){
		// 	if($item['_gp_id_tipo_envio'] == 'apartado'){
		// 		echo MENSAJE_APARTADO_A;
		// 		break;
		// 	} elseif($item['_gp_id_tipo_envio'] == 'domicilio'){
		// 		echo MENSAJE_DOMICILIO_A;
		// 		break;
		// 	}
		// }

		if($order->get_meta('_ps_tipo_envio') == 'apartado'){
			?>
			<div class="is-well" style="margin-bottom: 1em; margin-top: 1em; padding: 20px;">
				<?php echo MENSAJE_APARTADO_A; ?>
			</div>
			<?php
		} elseif($order->get_meta('_ps_tipo_envio') == 'domicilio'){
			?>
			<div class="is-well" style="margin-bottom: 1em; margin-top: 1em; padding: 20px;">
				<?php echo MENSAJE_DOMICILIO_A; ?>
			</div>
			<?php
		} elseif($order->get_meta('_ps_tipo_envio') == 'tienda'){
			?>
			<div class="is-well" style="margin-bottom: 1em; margin-top: 1em; padding: 20px;">
				<?php echo MENSAJE_PREVENTA_A; ?>
			</div>
			<?php
		}
	}

	/**
	 * Añade mensaje en email si tiene apartado.
	 *
	 * @since    1.0.0
	 */
	public function gp_ps_email_message( $order, $sent_to_admin, $plain_text, $email ) {
		if($order->get_meta('_ps_tipo_envio') == 'apartado'){
			switch($order->get_meta('_gp_estatus_apartado')){
				case 'A':{
					?>
					<div class="is-well" style="margin-bottom: 1em; margin-top: 1em; padding: 20px;">
						<?php echo MENSAJE_APARTADO_A; ?>
					</div>
					<?php
					break;
				}
				case 'B':{
					?>
					<div class="is-well" style="margin-bottom: 1em; margin-top: 1em; padding: 20px;">
						<?php echo MENSAJE_APARTADO_B; ?>
					</div>
					<?php
					break;
				}
				case 'C':{
					?>
					<div class="is-well" style="margin-bottom: 1em; margin-top: 1em; padding: 20px;">
						<?php echo MENSAJE_APARTADO_C; ?>
					</div>
					<?php
					break;
				}
				case 'D':{
					?>
					<div class="is-well" style="margin-bottom: 1em; margin-top: 1em; padding: 20px;">
						<?php echo MENSAJE_APARTADO_D; ?>
					</div>
					<?php
					break;
				}
				case 'E':{
					?>
					<div class="is-well" style="margin-bottom: 1em; margin-top: 1em; padding: 20px;">
						<?php echo MENSAJE_APARTADO_E; ?>
					</div>
					<?php
					break;
				}
			}
		} elseif($order->get_meta('_ps_tipo_envio') == 'domicilio'){
			switch($order->get_meta('_gp_estatus_domicilio')){
				case 'A':{
					?>
					<div class="is-well" style="margin-bottom: 1em; margin-top: 1em; padding: 20px;">
						<?php echo MENSAJE_DOMICILIO_A; ?>
					</div>
					<?php
					break;
				}
				case 'B':{
					?>
					<div class="is-well" style="margin-bottom: 1em; margin-top: 1em; padding: 20px;">
						<?php echo MENSAJE_DOMICILIO_B; ?>
					</div>
					<?php
					break;
				}
				case 'C':{
					?>
					<div class="is-well" style="margin-bottom: 1em; margin-top: 1em; padding: 20px;">
						<?php echo MENSAJE_DOMICILIO_C; ?>
					</div>
					<?php
					break;
				}
				case 'D':{
					?>
					<div class="is-well" style="margin-bottom: 1em; margin-top: 1em; padding: 20px;">
						<?php echo MENSAJE_DOMICILIO_D; ?>
					</div>
					<?php
					break;
				}
				case 'E':{
					?>
					<div class="is-well" style="margin-bottom: 1em; margin-top: 1em; padding: 20px;">
						<?php echo MENSAJE_DOMICILIO_E; ?>
					</div>
					<?php
					break;
				}
			}
		}elseif($order->get_meta('_ps_tipo_envio') == 'tienda'){
			switch($order->get_meta('_gp_estatus_preventa')){
				case 'A':{
					?>
					<div class="is-well" style="margin-bottom: 1em; margin-top: 1em; padding: 20px;">
						<?php echo MENSAJE_PREVENTA_A; ?>
					</div>
					<?php
					break;
				}
				case 'B':{
					?>
					<div class="is-well" style="margin-bottom: 1em; margin-top: 1em; padding: 20px;">
						<?php echo MENSAJE_PREVENTA_B; ?>
					</div>
					<?php
					break;
				}
				case 'C':{
					?>
					<div class="is-well" style="margin-bottom: 1em; margin-top: 1em; padding: 20px;">
						<?php echo MENSAJE_PREVENTA_C; ?>
					</div>
					<?php
					break;
				}
			}
		}
	}

	/**
	 * Elimina botón "ordenar de nuevo" en orden completada.
	 *
	 * @since    1.0.0
	 */
	public function gp_ps_remove_order_again_button(){
		remove_action( 'woocommerce_order_details_after_order_table', 'woocommerce_order_again_button');
	}

	/**
	 * Añade bloque de información en "order-view".
	 *
	 * @since    1.0.0
	 */
	public function gp_ps_info_block_order_view($order){

		if(!is_checkout()){

			//* apartado
			if($order->get_meta('_ps_tipo_envio') == 'apartado'){
				switch($order->get_meta('_gp_estatus_apartado')){
					case 'A':{
						?>
						<div class="is-well" style="margin-bottom: 1em; margin-top: 1em; padding: 20px;">
							<?php echo MENSAJE_APARTADO_A; ?>
						</div>
						<?php
						break;
					}
					case 'B':{
						?>
						<div class="is-well" style="margin-bottom: 1em; margin-top: 1em; padding: 20px;">
							<?php echo MENSAJE_APARTADO_B; ?>
						</div>
						<?php
						break;
					}
					case 'C':{
						?>
						<div class="is-well" style="margin-bottom: 1em; margin-top: 1em; padding: 20px;">
							<?php echo MENSAJE_APARTADO_C; ?>
						</div>
						<?php
						break;
					}
					case 'D':{
						?>
						<div class="is-well" style="margin-bottom: 1em; margin-top: 1em; padding: 20px;">
							<?php echo MENSAJE_APARTADO_D; ?>
						</div>
						<?php
						break;
					}
					case 'E':{
						?>
						<div class="is-well" style="margin-bottom: 1em; margin-top: 1em; padding: 20px;">
							<?php echo MENSAJE_APARTADO_E; ?>
						</div>
						<?php
						break;
					}
				}
			} elseif($order->get_meta('_ps_tipo_envio') == 'domicilio'){
				switch($order->get_meta('_gp_estatus_domicilio')){
					case 'A':{
						?>
						<div class="is-well" style="margin-bottom: 1em; margin-top: 1em; padding: 20px;">
							<?php echo MENSAJE_DOMICILIO_A; ?>
						</div>
						<?php
						break;
					}
					case 'B':{
						?>
						<div class="is-well" style="margin-bottom: 1em; margin-top: 1em; padding: 20px;">
							<?php echo MENSAJE_DOMICILIO_B; ?>
						</div>
						<?php
						break;
					}
					case 'C':{
						?>
						<div class="is-well" style="margin-bottom: 1em; margin-top: 1em; padding: 20px;">
							<?php echo MENSAJE_DOMICILIO_C; ?>
						</div>
						<?php
						break;
					}
					case 'D':{
						?>
						<!-- <div class="is-well" style="margin-bottom: 1em; margin-top: 1em; padding: 20px;"> -->
							<?php // echo MENSAJE_DOMICILIO_D; ?>
						<!-- </div> -->
						<?php
						break;
					}
					case 'E':{
						?>
						<div class="is-well" style="margin-bottom: 1em; margin-top: 1em; padding: 20px;">
							<?php echo MENSAJE_DOMICILIO_E; ?>
						</div>
						<?php
						break;
					}
				}
			} elseif($order->get_meta('_ps_tipo_envio') == 'tienda'){
				switch($order->get_meta('_gp_estatus_preventa')){
					case 'A':{
						?>
						<div class="is-well" style="margin-bottom: 1em; margin-top: 1em; padding: 20px;">
							<?php echo MENSAJE_PREVENTA_A; ?>
						</div>
						<?php
						break;
					}
					case 'B':{
						?>
						<div class="is-well" style="margin-bottom: 1em; margin-top: 1em; padding: 20px;">
							<?php echo MENSAJE_PREVENTA_B; ?>
						</div>
						<?php
						break;
					}
					case 'C':{
						?>
						<div class="is-well" style="margin-bottom: 1em; margin-top: 1em; padding: 20px;">
							<?php echo MENSAJE_PREVENTA_C; ?>
						</div>
						<?php
						break;
					}
				}
			}
		}
	}

	public function gp_ps_modifica_precio_carrito_checkout( $cart ) {

		if ( is_admin() && ! defined( 'DOING_AJAX' ) )
			return;

		if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
			return;

		// Loop Through cart items
		foreach ( $cart->get_cart() as $cart_item ) {
			// Get the product id (or the variation id)
			$sku = $cart_item['data']->get_sku();

			if(str_starts_with( $sku, 'P' )){
				if(isset($cart_item['monto_minimo'])){
					$new_price = $cart_item['monto_minimo'];
					$cart_item['data']->set_price( $new_price );
				}
			} elseif(isset($cart_item['tipo']) && $cart_item['tipo'] == 'tienda'){
				$product_id = $cart_item['product_id'];
				$precios_tienda = get_post_meta( $product_id, 'gp_precio_tiendas' );
				foreach($precios_tienda as $precio){
					if(!is_null($precio) && is_numeric($precio)){
						$cart_item['data']->set_price( $precio );
					}
				}
			}
		}
	}

	public function gp_remove_single_product_summary(){
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
		//  add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 50);
		
		// regresar??
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
		add_action( 'woocommerce_single_product_summary', function(){
			global $product;
			if(!is_null($product)){
				$prod_sku = $product->get_sku();
				// sin, Flat, Porc
				// $upc_test = ['818858029582', 'U014633194098', '045496782580'];
				$upc_test = ['U014633194098'];
				// if( $prod_sku == 'U014633194098'){
				// if( in_array($prod_sku, $upc_test)){
				if (class_exists('Widget_single_product_ps')) {
					$clase = new Widget_single_product_ps('', 1);
					if(method_exists($clase, 'gp_wc_disponibilidad')){
						$lat = _GP_GEO_LAT;
						$lng = _GP_GEO_LNG;
						$tienda_fav = _GP_TIENDA_DEFAULT_ID;
						$id_gp = 0;

						if(isset($_COOKIE['_gp_geo_lat']) && isset($_COOKIE['_gp_geo_lng'])){
							$lat = filter_var($_COOKIE['_gp_geo_lat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
							$lng = filter_var($_COOKIE['_gp_geo_lng'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
						}

						if(isset($_COOKIE['_gp_tienda_favorita_id'])){
							$tienda_fav = filter_var($_COOKIE['_gp_tienda_favorita_id'], FILTER_SANITIZE_ENCODED);
						}

						if(is_user_logged_in()){
							$user_id = get_current_user_id();
							$id_gp = get_user_meta($user_id, 'id_gp', true);
						}

						$response = $clase->gp_wc_disponibilidad(1, "cache", $prod_sku, $lat, $lng, $tienda_fav, $id_gp); //* solo tienda
						$ext_auth = json_decode($response, true);
						if($ext_auth['estatus']){
							$col1 = 1;
							$col2 = 8;
							$col3 = 3;
							
							$cont1 = 1;
							$cont2 = 11;

							//* renglón 1
							$html_col1 = "col small-{$col1} medium-{$col1} large-{$col1} gp_col_1";
							$html_col2 = "col small-{$col2} medium-{$col2} large-{$col2} gp_col_2";
							$html_col3 = "col small-{$col3} medium-{$col3} large-{$col3} gp_col_3";
							
							//* renglón 2 y 3
							$html_col4 = "col small-0 medium-{$col1} large-{$col1} gp_col_1";
							$html_col5 = "col small-9 medium-{$col2} large-{$col2} gp_col_2";
							$html_col6 = "col small-2 medium-{$col3} large-{$col3} gp_col_3";
							
							$html_cont1 = "col small-{$cont1} medium-{$cont1} large-{$cont1} gp_cont_1";
							$html_cont2 = "col small-{$cont2} medium-{$cont2} large-{$cont2} gp_cont_2";

							if(!empty($ext_auth['garantias'])){
								$site_garantias = site_url('/garantias-xtendia');
								echo "
								<div class=\"is-divider small\"></div>
								<div id=\"gp_garantia\" class=\"gp_margin_top_3em\">
									<div class=\"row row-collapse row-full-width gp_garantia_row\">
										<div class=\"{$html_col1}\">
											<div class=\"col-inner\">
												<center>
													<img style=\"width: 30px\" src=\"https://cdn.gameplanet.com/wp-content/uploads/2022/11/02155226/protectionicon.png\">
												</center>
											</div>
										</div>
										
										<div class=\"{$html_col2}\">
											<div class=\"col-inner\" style=\"line-height: 1em;\">
												<h4>Protege tu compra</h4>
												<p class=\"gp_margin0\">Nuestros planes de protección</p>
												<a class=\"gp_underline gp_fs_p7em\" href=\"{$site_garantias}\">Detalles de los planes</a>
											</div>
										</div>
										<div class=\"{$html_col3}\">
											<div class=\"col-inner\">
											</div>
										</div>
									</div>
								";
								foreach($ext_auth['garantias'] as $key => $value){
									$garantia_upc = $value['upc'];
									$garantia_costo = $value['costo'];
									if(is_null($garantia_upc) || empty($garantia_upc)){
										$garantia_upc = 'gp_no_garantia';
										$garantia_costo = 0;
									}

									$costo = $value['costo'];
									$vigencia = $value['vigencia'];
									$html_costo = $costo;
									$modal_garantia = '';
									$html_cost_mes = '';
									if(is_numeric($costo)){
										//* precio garantia
										$price = floatval(str_replace(',', '', $costo));
										$unit = intval( $price );
										$decimal = sprintf( '%02d', ( $price-$unit ) * 100 );
										$decimal_formateado = number_format($unit, 0, '.', ',');
										//* mensualidad garantia
										$costo_m = $costo / (12 * $vigencia);
										$price_m = floatval(str_replace(',', '', $costo_m));
										$unit_m = intval( $price_m );
										$decimal_m = sprintf( '%02d', ( $price_m - $unit_m ) * 100 );
										$decimal_formateado_m = number_format($unit_m, 0, '.', ',');
										//*------


										$html_costo = sprintf( '<span class="price-symbol">%s</span>%s<span class="price-fraction">%s</span>', get_woocommerce_currency_symbol(), $decimal_formateado, $decimal );
										
										echo "
										<div id=\"modal_garantia_{$garantia_upc}\" class=\"lightbox-by-id lightbox-content lightbox-white mfp-hide\" style=\"max-width:45em ;padding:1.5em;\">
											<div class=\"gp_garantia_header\">
												<img src=\"https://planet-53f8.kxcdn.com/wp-content/uploads/2022/09/21123617/extendia-logo.png\" width=\"150\">
												<h3 style=\"color:#2398c9 !important\">{$value['nombre']}</h3>
											</div>
											<div class=\"gp_modal_garantias\">
												<span>
													<h4>Descripción</h4>
													{$value['descripcion']}
													<h4>Indicaciones</h4>
													{$value['instrucciones']}
												</span>
											</div>
										</div>
										";
										$modal_garantia = "<a href=\"#modal_garantia_{$garantia_upc}\" target=\"_self\" class=\"gp_underline gp_fs_p7em\">Más información sobre esta garantía</a>";
										
										$costo_x_mes = sprintf( '<span class="price-symbol">%s</span>%s', get_woocommerce_currency_symbol(), $decimal_formateado_m);
										$html_cost_mes = "
											<span class=\"gp_fs_p6em\">
												<p class=\"gp_margin0\">
													aprox.
												</p>
												<p class=\"gp_margin0\">
													{$costo_x_mes}/mes
												</p>
											</span>
										";
									}
									$checked = '';
									if($key != 0){
										$checked = 'checked';
									}

									
									echo "
									<div class=\"row row-collapse row-full-width gp_garantia_row\">
										<div class=\"{$html_col4}\">
											<div class=\"col-inner\">
											</div>
										</div>
										<div class=\"{$html_col5}\">
											<div class=\"col-inner\">
												<div class=\"row row-collapse row-full-width gp_garantia_row\">
													<div class=\"{$html_cont1}\">
														<div class=\"col-inner\">
															<input type=\"radio\" id=\"{$garantia_upc}\" name=\"garantia\" value=\"{$garantia_upc}\" {$checked} gp-gc=\"{$garantia_costo}\" gp-gname=\"{$value['nombre']}\" style=\"margin: 0;\">
														</div>
													</div>
													<div class=\"{$html_cont2}\">
														<div class=\"col-inner\">
															<p class=\"gp_margin0 gp_garantia_name\">
																<label for=\"{$garantia_upc}\">{$value['nombre']}</label>
																{$modal_garantia}
															</p>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class=\"{$html_col6}\">
											<div class=\"col-inner\">
												<p class=\"gp_fw-b gp_margin0\">
													{$html_costo}
												</p>
												{$html_cost_mes}
											</div>
										</div>
									</div>
									";
								}
								echo "
									</div>
									<div class=\"is-divider small\"></div>
								";
							}
						}
					} else{
						echo "<div id=\"gp_garantia\">Code: PS-100</div>";
					}
				} else{
					echo "<div id=\"gp_garantia\">Code: PS-101</div>";
				}
				
			} else{
				echo "<div id=\"gp_garantia\">Code: PS-102</div>";
			}
		}, 45 );

		// add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 45 );
	}

	public function gp_modify_woocommerce_display_item_meta( $html, $item, $args ) {
		$strings = array(
			'Categoría' => '',
			'espacio' => '<p style="margin-bottom: 1em !important;"> </p>',
			'Ticket' => '',
			'Estatus' => '',
			'Entrega' => '',
			'Precio final' => '',
			'Apartado con' => '',
			'Costo envío' => '',
			'Recoge en' => '',
			'Recoge de' => '',
			'_gp_nombre_garantia' => '',
			'_gp_costo_garantia' => '',
			'Fecha procesado' => '',
			'espacio1' => '<p style="margin-bottom: 1em !important;"> </p>',
		);
		$html    = '';
		$args    = wp_parse_args(
			$args,
			array(
				'before'       => '<div class="wc-item-meta "><p class="gp-p-meta">',
				'after'        => '</p></div>',
				'separator'    => '</p><p class="gp-p-meta">',
				'echo'         => true,
				'autop'        => true,
				'label_before' => '<strong class="wc-item-meta-label">',
				'label_after'  => '</strong> ',
			)
		);

		$item_estatus = $item['_gp_estatus'];
		$item_tipo = $item['_gp_id_tipo_envio'];
		$item_meta_data = $item->get_all_formatted_meta_data('');
		$gp_include_meta = array('_gp_nombre_garantia', '_gp_costo_garantia');

		$temp_nombre_gar = '';
		$temp_costo_gar = '';
		foreach ( $item_meta_data as $meta_id => $meta ) {
			$value = $args['autop'] ? wp_kses_post( $meta->display_value ) : wp_kses_post( make_clickable( trim( $meta->display_value ) ) );

			if(str_starts_with($meta->display_key, '_')){
				if(!in_array($meta->display_key, $gp_include_meta)){
					continue;
				}
			}

			switch($meta->display_key){

				// sin etiqueta
				case 'Categoría':{
					$strings[$meta->display_key] = wp_strip_all_tags($value);
					break;
				}

				case '_gp_nombre_garantia':{
					$temp_nombre_gar = wp_strip_all_tags($value);
					$strings[$meta->display_key] = '<span class="">Producto Protegido<br/>'. wp_strip_all_tags($value) . '</span>';
					break;
				}
				case '_gp_costo_garantia':{
					$temp_costo_gar = wp_strip_all_tags($value);
					$url_info = site_url('/garantias-xtendia');
					$strings[$meta->display_key] = '<span class="">Precio garantía: +'. get_woocommerce_currency_symbol() . wp_strip_all_tags($value) . '</span><br/><a class="gp_underline gp_fs_p7em" href="' . $url_info . '">Más información</a>';
					break;
				}

				// con etiqueta
				case 'Fecha procesado':
				case 'Recoge de':{
					$texto = $args['label_before'] . ( $meta->display_key ) . $args['label_after'] . ' <span >' . wp_strip_all_tags($value) . '</span>';
					$meses = array(
						'January' => 'enero',
						'February' => 'febrero',
						'March' => 'marzo',
						'April' => 'abril',
						'May' => 'mayo',
						'June' => 'junio',
						'July' => 'julio',
						'August' => 'agosto',
						'September' => 'septiembre',
						'October' => 'octubre',
						'November' => 'noviembre',
						'Decembe' => 'diciembre',
					);
					$texto = str_ireplace(  array_keys($meses),  $meses,  $texto );
					$strings[$meta->display_key] = $texto;
					break;
				}

				case 'Precio final':
				case 'Ticket':
				case 'Tienes':{
					$strings[$meta->display_key] = $args['label_before'] . ( $meta->display_key ) . $args['label_after'] . ' <span >' . wp_strip_all_tags($value) . '</span>';
					break;
				}

				case 'Apartado con':
				case 'Apártalo con':{
					$strings['Apartado con'] = '<span class="gp_item_status_verde">' . ( $meta->display_key ) . ' <span>' . wp_strip_all_tags($value) . '</span></span>';
				}


				case 'Costo envío':
				case 'Entrega':{
					if($item_tipo == 'domicilio'){
						if($meta->display_key == 'Costo envío'){
							$strings[$meta->display_key] = '<span class="gp_item_status_azul">' . ( $meta->display_key ) . ' <span>' . wp_strip_all_tags($value) . '</span></span>';
						} else{
							$strings[$meta->display_key] = '<span class="gp_item_status_verde">' . ( $meta->display_key ) . ' <span>' . wp_strip_all_tags($value) . '</span></span>';
						}
					} else{
						unset($strings[$meta->display_key]);
					}
					break;
				}

				// forzar borrado
				case 'Plataforma':
				case 'Condición':{
					break;
				}

				// especiales
				case 'Recoge en':{
					$strings[$meta->display_key] = '<span class="gp_entrega_en">' . ( $meta->display_key ) . ' <span>' . wp_strip_all_tags($value) . '</span></span>';
					break;
				}

				case 'Estatus':{
					if($item_estatus == 'ok'){
						$strings[$meta->display_key] = '<span class="gp_item_status_verde">' . ( $meta->display_key ) . ' <span>' . wp_strip_all_tags($value) . '</span></span>';
					} elseif($item_estatus == 'fail'){
						$strings[$meta->display_key] = '<span class="gp_item_status_rojo">' . ( $meta->display_key ) . ' <span>' . wp_strip_all_tags($value) . '</span></span>';
					} else{
						$strings[$meta->display_key] = $args['label_before'] . ( $meta->display_key ) . $args['label_after'] . ' <span >' . wp_strip_all_tags($value) . '</span>';
					}
					break;
				}

				default:{
					$strings[$meta->display_key] = $args['label_before'] . ( $meta->display_key ) . $args['label_after'] . $value;
					break;
				}
			}
		}

		if(empty($temp_nombre_gar)){
			unset($strings['_gp_nombre_garantia']);
			unset($strings['_gp_costo_garantia']);
		}

		if ( $strings ) {
			$html = $args['before'] . implode( $args['separator'], array_filter($strings) ) . $args['after'];
		}

		// $html = apply_filters( 'woocommerce_display_item_meta', $html, $item, $args );

		if ( $args['echo'] ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $html;
		} else {
			return $html;
		}
	}

	
	public function gp_remove_payment_for_test() {
		if(!current_user_can( 'manage_woocommerce' )){
			remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
			remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
			remove_action('woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20);
			remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
		}
	}

	//! test disponibilidad3
	public function gp_wc_disponibilidad( $cantidad, $metodo, $upc, $lat, $lng, $tienda_fav, $id_cliente){

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
			'gameplanet-telefonia',
			'var_disponibilidad', array(
				'disp'    => json_encode($response)
			)
		);
	
		return json_encode($response);
	}

	/**
	 * función para añadir costo de garantía a tablas (carrito, checkout, email, admin order)
	 */
	public function gp_add_garantia_fee($cart){
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;
		
		$bandera_garantia = false;
		$suma = 0;
		foreach($cart->get_cart() as $key => $val){
			if(isset($val['costo_garantia']) && !empty($val['costo_garantia'])){
				$suma += $val['costo_garantia'];
				$bandera_garantia = true;
			}
		}
		if($bandera_garantia){
			$cart->add_fee('Gastos Garantía', $suma, false);
		}
		
	
	}

	/**
	 * Función para cambiar el texto de las opciones del menú de "my-account"
	 *
	 * @since    1.0.0
	 */
	public function gp_my_account_menu_text($items) {
		$items['edit-address'] = "Datos de envío";
		return $items;
	}

	/**
	 * Función para quitar línea de "shipping" en la tabla del carrito
	 */
	public function gp_disable_shipping_row_on_cart( $show_shipping ) {
		if( is_cart() ) {
			return false;
		}
		return $show_shipping;
	}
}

//! mensajes para apartados
define('MENSAJE_APARTADO_A', '
	<div>
		<h4>Al hacer tu apartado en sucursal es importante que consideres los siguientes puntos.</h4>
		<span>Al procesar tu orden se verificará la disponibilidad en la sucursal (esto tomará unos minutos), posteriormente podrás seguir los siguientes pasos.</span>
		<ul class="gp_list">
			<p>Tienes 48hrs para recoger tu producto en la sucursal. Al no recoger ni liquidar tu compra a tiempo, este se cancelará inmediatamente.</p>
			<p>Puedes pasar a recoger tu producto en la sucursal que seleccionaste en un horario de 11:00 AM a 08:00 PM de Lunes a Domingo.</p>
			<p>Puedes liquidar tu producto con la forma de pago que prefieras en la sucursal seleccionada.</p>
		</ul>
	</div>
');
define('MENSAJE_APARTADO_B', '
	<div>
		<h4>Al hacer tu apartado en sucursal es importante que consideres los siguientes puntos.</h4>
		<ul class="gp_list">
			<p>Tienes 48hrs para recoger tu producto en la sucursal. Al no recoger ni liquidar tu compra a tiempo, este se cancelará inmediatamente.</p>
			<p>Puedes pasar a recoger tu producto en la sucursal que seleccionaste en un horario de 11:00 AM a 08:00 PM de Lunes a Domingo.</p>
			<p>Puedes liquidar tu producto con la forma de pago que prefieras en la sucursal seleccionada.</p>
		</ul>
	</div>
');
define('MENSAJE_APARTADO_C', '
	<div>
		<h4>Tu apartado ha sido cancelado.</h4>
		<p>Por el momento este producto no está disponible en la sucursal seleccionada, lamentamos los inconvenientes. Te sugerimos apartar el producto en otra sucursal de tu preferencia.</p>
	</div>
');
define('MENSAJE_APARTADO_D', '
	<div>
		<h4>Tu orden ha sido cancelada.</h4>
		<span>Debido a que no recogiste tu apartado en la sucursal seleccionada hemos cancelado tu orden. Debes de considerar que esto puede afectar tu reputación en nuevas compras y apartados de producto.</span>
	</div>
');
define('MENSAJE_APARTADO_E', '');

//! mensajes para envío a domicilio
define('MENSAJE_DOMICILIO_A', '
	<div>
		<h4>Hemos generado tu orden.</h4>
		<p>Al procesar tu orden se verificará la disponibilidad de tus productos (esto tomará unos minutos).</p>
		<span>Toma en cuenta los siguientes puntos:</span>
		<ul class="gp_list">
			<p>Si un producto no está disponible se cancelará el envío de ese producto.</p>
			<p>Si alguno de tus productos se ha cancelado, este será reembolsado.</p>
		</ul>
	</div>
');
define('MENSAJE_DOMICILIO_B', '
	<div>
		<h4>Tu orden fue procesada correctamente.</h4>
		<p>Los productos de tu orden fueron procesados satisfactoriamente y se enviarán de acuerdo a los tiempos de entrega que se indican para cada uno. Puedes hacer seguimiento de cada uno de ellos.</p>
	</div>
');
define('MENSAJE_DOMICILIO_C', '
	<div>
		<h4>Tu orden ha sido cancelada.</h4>
		<p>Lamentablemente los productos de tu orden fueron cancelados, esto puede suceder por varios factores (no hay existencia del producto, no hay disponibilidad por la zona de entrega, etc).</p>
		<p>Se te hará la devolución de la misma forma en la que realizaste tu pago.</p>
	</div>
');
define('MENSAJE_DOMICILIO_D', '

');
define('MENSAJE_DOMICILIO_E', '
	<div>
		<h4>Hemos procesado tu orden.</h4>
		<p>Algunos productos de tu orden tuvieron que ser cancelados (no hay existencia del producto, no hay disponibilidad por la zona de entrega, etc). Los productos que fueron aceptados para su envío se enviarán de acuerdo a los tiempos de entrega que se indican para cada uno.</p>
		<p>Se te hará la devolución correspondiente de la misma forma en la que realizaste tu pago.</p>
	</div>
');

//! mensajes preventa
define('MENSAJE_PREVENTA_A', '
	<div>
		<h4>Al hacer tu apartado de preventa es importante que consideres los siguientes puntos.</h4>
		<span>Si realizaste tu apartado para envío a domicilio:</span>
		<ul class="gp_list">
			<p>Tu producto será enviado para que llegue a tu domicilio en la fecha de lanzamiento, si no hay fecha de lanzamiento confirmada, te mantendremos informado cuando se confirme la fecha.</p>
		</ul>
		<span>Si realizaste tu apartado para recoger en sucursal:</span>
		<ul class="gp_list">
			<p>El día del lanzamiento deberás pasar a recoger tu producto a la sucursal seleccionada, si no hay fecha de lanzamiento confirmada, te mantendremos informado cuando se confirme la fecha.</p>
			<p>Deberás liquidar la diferencia de tu apartado cuando recojas tu producto.</p>
		</ul>
	</div>
');
define('MENSAJE_PREVENTA_B', '
	<div>
		<h4>Al hacer tu apartado de preventa es importante que consideres los siguientes puntos.</h4>
		<span>Si realizaste tu apartado para envío a domicilio:</span>
		<ul class="gp_list">
			<p>Tu producto será enviado para que llegue a tu domicilio en la fecha de lanzamiento, si no hay fecha de lanzamiento confirmada, te mantendremos informado cuando se confirme la fecha.</p>
		</ul>
		<span>Si realizaste tu apartado para recoger en sucursal:</span>
		<ul class="gp_list">
			<p>El día del lanzamiento deberás pasar a recoger tu producto a la sucursal seleccionada, si no hay fecha de lanzamiento confirmada, te mantendremos informado cuando se confirme la fecha.</p>
			<p>Deberás liquidar la diferencia de tu apartado cuando recojas tu producto.</p>
		</ul>
	</div>
');
define('MENSAJE_PREVENTA_C', '');
