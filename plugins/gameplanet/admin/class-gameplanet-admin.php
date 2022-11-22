<?php

/**
 * Funcionalidad del plugin específica para el área de admin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Gameplanet
 * @subpackage Gameplanet/admin
 */

/**
 * Funcionalidad del plugin específica para el área de admin.
 *
 * Define el nombre del plugin, versión y hooks para el área de admin.
 *
 * @package    Gameplanet
 * @subpackage Gameplanet/admin
 * @author     GamePlanet
 */
class Gameplanet_Admin {

	/**
	 * El ID del plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $gameplanet    El ID del plugin.
	 */
	private $gameplanet;

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
	 * @param      string    $gameplanet       El nombre del plugin.
	 * @param      string    $version    La versión del plugin.
	 */
	public function __construct( $gameplanet, $version ) {

		$this->gameplanet = $gameplanet;
		$this->version = $version;

	}

	/**
	 * Registra el CSS para el área del admin.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->gameplanet, plugin_dir_url( __FILE__ ) . 'css/gameplanet-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Registra el JavaScript para el área del admin.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->gameplanet, plugin_dir_url( __FILE__ ) . 'js/gameplanet-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Agrega campos usados en GP al perfil de usuario.
	 *
	 * @since    1.0.0
	 * @return   array                Regresa campos de información de contacto.
	 */
	public function gp_add_contact_info($arr) {
		$arr['token'] = __('Token');
		$arr['id_gp'] = __('ID Cliente GP');
		$arr['token_inicio'] = __('Token de inicio');
		$arr['_gp_autocompletado'] = __('Dirección autocompletado');
		$arr['_gp_exterior_number'] = __('Número exterior');
		$arr['_gp_interior_number'] = __('Número interior');
		$arr['_gp_suburb'] = __('Colonia');
		$arr['gp_shipping_address'] = __('gp_shipping');
		$arr['nip_gp'] = __('NIP');
		return $arr;
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
	 * Función miscelánea para regresar un string vacío.
	 *
	 * @since    1.0.0
	 * @return   string               Regresa ''.
	 */
	public function __return_empty_string(){
		return '';
	}
	
	/**
	 * Agrega columna a la tabla de órdenes en admin.
	 *
	 * @since    1.0.0
	 * @return   array                Regresa columnas.
	 */
	public function gp_add_column_to_admin_orders($columns){
		$reordered_columns = array();
		
		foreach ($columns as $key => $column) {
			$reordered_columns[$key] = $column;
			if ($key ==  'order_status') {
				// insertar despues del estado de la orden
				$reordered_columns['gamers-metodo-pago'] = __('Método de pago', 'theme_domain');
			}
		}
		return $reordered_columns;
	}
	
	/**
	 * Agrega el método de pago a la tabla de órdenes en admin.
	 *
	 * @since    1.0.0
	 */
	public function gp_add_payment_method_to_admin_orders($column, $post_id){
		switch ($column) {
			case 'gamers-metodo-pago':
				$my_var_one = get_post_meta($post_id, '_payment_method_title', true);
				if (!empty($my_var_one))
					echo $my_var_one;
				else
					echo '<small>(<em>Sin método de pago</em>)</small>';
				break;
		}
	}
	
	/**
	 * Registro de plugin activado.
	 *
	 * @since    1.0.0
	 */
	public function gp_plugin_activated_log($plugin, $plugin_action){
		if (!$plugin_action) {
			$this->gp_admin_logs($plugin, "activado");
		}
	}
	
	/**
	 * Registro de plugin desactivado.
	 *
	 * @since    1.0.0
	 */
	public function gp_plugin_deactivated_log($plugin, $plugin_action){
		if (!$plugin_action) {
			$this->gp_admin_logs($plugin, "desactivado");
		}
	}
	
	/**
	 * Registro de plugin eliminado.
	 *
	 * @since    1.0.0
	 */
	public function gp_plugin_deleted_log($plugin, $plugin_action){
		if ($plugin_action) {
			$this->gp_admin_logs($plugin, "eliminado");
		}
	}

	/**
	 * Crea las páginas para configurar las API y
	 * para la portabilidad (si se tienen activados otros plugins de GP, sus 
	 * menús aparecerán aquí).
	 *
	 * @since    1.0.0
	 */
	public function gp_admin_menu() {
		add_menu_page( "GamePlanet", "GamePlanet", 'manage_options', 'gp-admin', '', 'dashicons-games', 100);
		
		add_submenu_page( 'gp-admin', 'Configuración', 'Configuración', 'manage_options', plugin_dir_path(__FILE__) . '/partials/gameplanet-admin-display.php', '', 1);
		
		remove_submenu_page( 'gp-admin', 'gp-admin');
	}

	/**
	 * Agrega url "no segura" para que el webhook la use.
	 *
	 * @since    1.0.0
	 */
	public function gp_whitelist_url ( $args, $url ) {
		$lista = [
			'http://54.172.203.98:5011/api/v1/portero/ingreso/woocommerce',
			'http://54.172.203.98:5011/api/v1/portero/actualizacion/woocommerce'
		];
	
		if(in_array($url, $lista)){
			$args['reject_unsafe_urls'] = false;
		}
		
		return $args;
	}

	/**
	 * Crea log si no existe y/o escribe registro.
	 *
	 * @since    1.0.0
	 * @param    string    	$accion		Nombre de la función donde se llama.
	 * @param    string    	$mensaje    Mensaje a guardar.
	 * @param    mixed   	$extra    	Opcional. Información extra, codifica en JSON si es array.
	 * 
	 * @return   int|false	Número de bytes escritos o falso en un error.
	 */
	function gp_admin_logs($accion, $mensaje, $extra = null){
		if (!file_exists('./gp/logs/')) {
			mkdir('./gp/logs/', 0755, true);
		}
	
		$current_user = wp_get_current_user();
	
		$tiempo = current_time('mysql');
	
		$file = fopen("./gp/logs/admin_gameplanet.log", "a") or fopen("./gp/logs/admin_gameplanet.log", "w");
		
		if (is_array($extra)) {
			$extra = json_encode($extra);
		}
		$registro = $tiempo . " :: Usuario: " . $current_user->display_name . " || " . $accion . " || " . $mensaje . " || " . $extra . "\n";
	
		$bytes = fwrite($file, $registro);
		fclose($file);
	
		return $bytes;
	}

	
	/**
	 * Permite el acceso si el usuario tiene cuenta en GP,
	 * crea o actualiza usuario de WP si tiene cuenta GP,
	 * actualiza id y token en WP.
	 *
	 * @since    1.0.0
	 * 
	 * @return   object               Regresa WP_User o WP_Error si falló.
	 * 
	 */
	public function gp_authenticate_user($user, $username, $password){
		// Verifica campos
		$username = filter_var($username, FILTER_VALIDATE_EMAIL);
		$password = htmlspecialchars($password);
		if ($username == '') {
			$user = new WP_Error('denied', __("Favor de ingresar un correo válido."));
			return;
		}
		if ($password == '') {
			$user = new WP_Error('denied', __("Favor de ingresar tu contraseña."));
			return;
		}
		$this->gp_admin_logs('gp_authenticate_user', 'Inicio Autenticación');

		$args = array(
			'body' => json_encode(array(
				'email'    => $username,
				'password' => $password
			)),
			'headers' => array(
				'Content-Type' => 'application/json',
				// 'data-jwt-master' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJkYXRhIjp7InRpbWVfdG9fbGl2ZSI6IjM2MDAiLCJpc19tYXN0ZXIiOnRydWV9LCJpYXQiOjE2NDc2MzIyNTYsImV4cCI6MTk2Mjk5MjI1Nn0.k08KyaU5H4uOMDVVJHIv6RtarvjrjVVhdS_VpDs4EG8'
				'data-jwt-master' => get_option('data-jwt-master')
			)
		);
		$this->gp_admin_logs('gp_authenticate_user', 'Email',  $username);

		// validamos que el usuario tenga cuenta GP
		// $url = "https://api.gameplanet.com/v1/customer/login";
		$url = get_option('ruta_gameplanet') . "customer/login";
		$this->gp_admin_logs('gp_authenticate_user', 'Endpoint (POST)', $url);

		$response = wp_remote_post($url, $args);

		// Si hay un error
		if (is_wp_error($response)) {
			$error_message = $response->get_error_message();
			$this->gp_admin_logs('gp_authenticate_user', 'Error', $error_message);
			$this->gp_admin_logs('gp_authenticate_user', "Fin Autenticación", "\n-----");
			$user = new WP_Error('denied', __("ERROR: Algo salió mal, inténtelo más tarde."));
			remove_action('authenticate', 'wp_authenticate_username_password',  20, 3);
			remove_action('authenticate', 'wp_authenticate_email_password',     20, 3);
			return $user;
		}

		$this->gp_admin_logs('gp_authenticate_user', 'Response ',  $response);
		$ext_auth = json_decode($response['body'], true);

		
		if ($response['response']['code'] == 200 && $ext_auth['success'] && $ext_auth['code'] == 200) {
			
			// el usuario existe en GP, valida si existe en WP
			$userobj = new WP_User();
			$user = $userobj->get_data_by('email', $username);
			$user = new WP_User(@$user->ID);

			if ($user->ID == 0) {
				// el usuario no existe en WP, se crea
				$userdata = array(
					'user_email'   => $username,
					'user_login'   => $username,
					'user_pass'    => $password,
					'first_name'   => $ext_auth['data']['cliente']['firstname'],
					'last_name'    => $ext_auth['data']['cliente']['lastname'],
					'display_name' => $ext_auth['data']['cliente']['firstname'] . " " . $ext_auth['data']['cliente']['lastname'],
					'token' 	   => $ext_auth['data']['cliente']['gp_token'],
					'id_gp' 	   => $ext_auth['data']['cliente']['id_cliente'],
					'token_inicio' => $ext_auth['data']['token']
				);
				$new_user_id = wp_insert_user($userdata); // se crea el usuario
	
				$this->gp_admin_logs('gp_authenticate_user', '4. Crear usuario.', $userdata);
	
				// carga informacion del usuario para login
				$user = new WP_User($new_user_id);
				$this->gp_admin_logs('gp_authenticate_user', 'Inicio de sesión exitoso');
				$this->gp_admin_logs('gp_authenticate_user', "Fin Autenticación", "\n-----");
			}else{
				// el usuario existe en WP, se actualiza id y token
				$userdata = array(
					'ID'           => $user->ID,
					'user_email'   => $username,
					'user_login'   => $username,
					'first_name'   => $ext_auth['data']['cliente']['firstname'],
					'last_name'    => $ext_auth['data']['cliente']['lastname'],
					'display_name' => $ext_auth['data']['cliente']['firstname'] . " " . $ext_auth['data']['cliente']['lastname'],
					'token'        => $ext_auth['data']['cliente']['gp_token'],
					'id_gp'        => $ext_auth['data']['cliente']['id_cliente'],
					'token_inicio' => $ext_auth['data']['token']
				);
				$this->gp_admin_logs('gp_authenticate_user', 'Actualizo usuario');
				$user_data = wp_update_user($userdata); // se actualiza el usuario

				// verificar posible error
				if (is_wp_error($user_data)) {
					$user = new WP_Error('denied', __("ERROR: Al actualizar datos"));
					$error_message = $user_data->get_error_message();
					$this->gp_admin_logs('gp_authenticate_user', 'Error', $error_message);
					$this->gp_admin_logs('gp_authenticate_user', "Fin Autenticación", "\n-----");
				} else {
					// Success!
					$this->gp_admin_logs('gp_authenticate_user', 'Inicio de sesión exitoso');
					$this->gp_admin_logs('gp_authenticate_user', "Fin Autenticación", "\n-----");
				}
			}
		} else {
			// el usuario no existe en GP
			$user = new WP_Error('authentication_failed', __("Usuario o contraseña incorrecto, intentelo nuevamente."));
			$this->gp_admin_logs('gp_authenticate_user', 'Datos incorrectos');
			$this->gp_admin_logs('gp_authenticate_user', "Fin Autenticación", "\n-----");
		}
	
		// quita la verificacion de WP (ya validamos)
		remove_action('authenticate', 'wp_authenticate_username_password',  20, 3);
		remove_action('authenticate', 'wp_authenticate_email_password',     20, 3);
		return $user;
	}

	public function gp_show_order_thx_page_link( $order ){
	
		$llave = $order->get_order_key();
		$id = $order->get_id();
		$url = site_url( '/checkout/order-received/' ) . $id . "/?key=" . $llave;
		echo "
			<div class='panel woocommerce-order-data'>
				<div class='form-field form-field-wide'>
					<h3>Información de venta</h3>
					<p> <strong>URL pedido: </strong><a href='" . $url . "'>" . $url . "</a></p>
				</div>
			</div>
		";
	}
}
