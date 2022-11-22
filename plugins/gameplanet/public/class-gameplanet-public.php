<?php

/**
 * La funcionalidad del área pública del plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Gameplanet
 * @subpackage Gameplanet/public
 */

/**
 * La funcionalidad del área pública del plugin.
 *
 * Define el nombre del plugin, versión y hooks para el área pública.
 *
 * @package    Gameplanet
 * @subpackage Gameplanet/public
 * @author     GamePlanet
 */
class Gameplanet_Public {

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
	 * Inisializa la clase y define sus propiedades.
	 *
	 * @since    1.0.0
	 * @param      string    $gameplanet       Nombre del plugin.
	 * @param      string    $version    La versión del plugin.
	 */
	public function __construct( $gameplanet, $version ) {

		$this->gameplanet = $gameplanet;
		$this->version = $version;

	}

	/**
	 * Registra el CSS para el área pública.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->gameplanet, plugin_dir_url( __FILE__ ) . 'css/gameplanet-public.css', array(), $this->version, 'all' );
		//* google api fonts (icon)
		wp_enqueue_style( $this->gameplanet . "_google_icons", "https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200", array(), $this->version, 'all' );

	}

	/**
	 * Registra el JavaScript para el área pública.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->gameplanet, plugin_dir_url( __FILE__ ) . 'js/gameplanet-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Aumenta el tiempo de espera de respuesta http.
	 *
	 * @since    1.0.0
	 * 
	 * @param      int       $time             Tiempo de espera.
	 * 
	 * @return     int
	 */
	public function gp_extend_timeout($time) {

		//? recomendado entre 30 y 120
		// wordpres default = 5
		return 30;

	}

	/**
	 * Aumenta el tiempo de vida de la sesión.
	 *
	 * @since    1.0.0
	 * 
	 */
	public function gp_extend_session($expire, $user, $remember) {

		return YEAR_IN_SECONDS;

	}
	
	/**
	 * Agrega campos para nombre y apellido en
	 * el formulario de registro.
	 *
	 * @since    1.0.0
	 */
	public function gp_add_registration_fields() {
		?>
    	<p class="form-row form-row-first">
			<label for="reg_billing_first_name">
				<?php _e('First name', 'woocommerce'); ?>
				<span class="required">*</span>
			</label>
			<input type="text" class="input-text" name="billing_first_name" id="reg_billing_first_name" value="<?php if (!empty($_POST['billing_first_name'])) esc_attr_e($_POST['billing_first_name']); ?>" />
		</p>

		<p class="form-row form-row-last">
			<label for="reg_billing_last_name">
				<?php _e('Last name', 'woocommerce'); ?>
				<span class="required">*</span>
			</label>
			<input type="text" class="input-text" name="billing_last_name" id="reg_billing_last_name" value="<?php if (!empty($_POST['billing_last_name'])) esc_attr_e($_POST['billing_last_name']); ?>" />
		</p>

		<div class="clear"></div>
		<?php

	}

	/**
	 * Valida que se ingresen los campos de nombre y apellido
	 * en el formulario de registro.
	 *
	 * @since    1.0.0
	 * 
	 * @return   mixed                Regresa lista de errores de WC.
	 * 
	 */
	public function gp_validate_registration_fields($errors, $username, $email){
		if (isset($_POST['billing_first_name']) && empty($_POST['billing_first_name'])) {
			$errors->add('billing_first_name_error', __('<strong>Error</strong>: First name is required!', 'woocommerce'));
		}
		if (isset($_POST['billing_last_name']) && empty($_POST['billing_last_name'])) {
			$errors->add('billing_last_name_error', __('<strong>Error</strong>: Last name is required!.', 'woocommerce'));
		}
		return $errors;
	}

	/**
	 * Crea usuaro GP y guarda nombre/apellido de usuario WP.
	 *
	 * @since    1.0.0
	 * 
	 * @return   mixed                Regresa lista de errores de WC.
	 * 
	 */
	public function gp_create_user($customer_id){
		if (isset($_POST['billing_first_name'])) {
			update_user_meta($customer_id, 'billing_first_name', sanitize_text_field($_POST['billing_first_name']));
			update_user_meta($customer_id, 'first_name', sanitize_text_field($_POST['billing_first_name']));
		}
		if (isset($_POST['billing_last_name'])) {
			update_user_meta($customer_id, 'billing_last_name', sanitize_text_field($_POST['billing_last_name']));
			update_user_meta($customer_id, 'last_name', sanitize_text_field($_POST['billing_last_name']));
		}
	
		// crea usuario en GP
		$user = get_userdata($customer_id);
		$args = array(
			'body' => json_encode(array(
				'email' => $_POST['email'],
				'password' => $_POST['password'],
				'firstname' => $user->user_firstname,
				'lastname' => $user->user_lastname
			)),
			'headers' => array(
				'Content-Type' => 'application/json',
				'data-jwt-master' => get_option('data-jwt-master')
			)
		);
		$url = get_option('ruta_gameplanet') . "cliente/agregar";
		$response = wp_remote_post($url, $args);
	
		if (is_wp_error($response)) {
			$error_message = $response->get_error_message();
			echo "Something went wrong: $error_message";
		}
	
		$ext_auth = json_decode($response['body'], true);
	
		if ($ext_auth['success'] && $ext_auth['code'] == 200) {
			// precarga datos minimos del nuevo usuario
			$userdata = array(
				'ID'           => $user->ID,
				'display_name' => $ext_auth['data']['firstname'] . " " . $ext_auth['data']['lastname'],
				'token'        =>  $ext_auth['data']['gp_token'],
				'id_gp'        =>  $ext_auth['data']['id_cliente'],
				'nip_gp'        =>  $ext_auth['data']['nip'],
				'token_inicio'        =>  $ext_auth['data']['token']
			);
	
			// crea usuario en gamers
			$user_data = wp_update_user($userdata);
			if (is_wp_error($user_data)) {
				// There was an error; possibly this user doesn't exist.
				$user = new WP_Error('denied', __("ERROR: Al actualizar datos"));
				return $user;
			} else {
				// Success!
				update_user_meta($user->ID, 'billing_first_name', $ext_auth['data']['firstname']);
				update_user_meta($user->ID, 'billing_last_name', $ext_auth['data']['lastname']);
			}
		} else{
			$user = new WP_Error('denied', __("ERROR: Al crear usuario"));
			return $user;
		}
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
		$this->gp_logs('gp_authenticate_user', 'Inicio Autenticación');

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
		$this->gp_logs('gp_authenticate_user', 'Email',  $username);

		// validamos que el usuario tenga cuenta GP
		// $url = "https://api.gameplanet.com/v1/customer/login";
		$url = get_option('ruta_gameplanet') . "cliente/inicia_sesion";
		$this->gp_logs('gp_authenticate_user', 'Endpoint (POST)', $url);

		$response = wp_remote_post($url, $args);

		// Si hay un error
		if (is_wp_error($response)) {
			$error_message = $response->get_error_message();
			$this->gp_logs('gp_authenticate_user', 'Error', $error_message);
			$this->gp_logs('gp_authenticate_user', "Fin Autenticación", "\n-----");
			$user = new WP_Error('denied', __("ERROR: Algo salió mal, inténtelo más tarde."));
			remove_action('authenticate', 'wp_authenticate_username_password',  20, 3);
			remove_action('authenticate', 'wp_authenticate_email_password',     20, 3);
			return $user;
		}

		$this->gp_logs('gp_authenticate_user', 'Response ',  $response);
		$ext_auth = json_decode($response['body'], true);

		
		if ($response['response']['code'] == 200 && $ext_auth['success'] && $ext_auth['code'] == 200) {
			
			// el usuario existe en GP, valida si existe en WP
			$userobj = new WP_User();
			$user = $userobj->get_data_by('email', $username);
			$user = new WP_User(@$user->ID);

			if ($user->ID == 0) {
				// el usuario no existe en WP, se crea
				$userdata = array(
					'user_email'   => $ext_auth['data']['cliente']['email'],
					'user_login'   => $ext_auth['data']['cliente']['email'],
					'user_pass'    => $password,
					'first_name'   => $ext_auth['data']['cliente']['firstname'],
					'last_name'    => $ext_auth['data']['cliente']['lastname'],
					'display_name' => $ext_auth['data']['cliente']['firstname'] . " " . $ext_auth['data']['cliente']['lastname'],
					'token' 	   => $ext_auth['data']['cliente']['gp_token'],
					'id_gp' 	   => $ext_auth['data']['cliente']['id_cliente'],
					'nip_gp' 	   => $ext_auth['data']['cliente']['nip'],
					'token_inicio' => $ext_auth['data']['token']
				);

				$new_user_id = wp_insert_user($userdata); // se crea el usuario
	
				$this->gp_logs('gp_authenticate_user', '4. Crear usuario.', $userdata);
	
				// carga informacion del usuario para login
				$user = new WP_User($new_user_id);
				update_user_meta($user->ID, 'billing_first_name', $ext_auth['data']['cliente']['firstname']);
				update_user_meta($user->ID, 'billing_last_name', $ext_auth['data']['cliente']['lastname']);
				$this->gp_logs('gp_authenticate_user', 'Inicio de sesión exitoso');
				$this->gp_logs('gp_authenticate_user', "Fin Autenticación", "\n-----");
			}else{
				// el usuario existe en WP, se actualiza id y token
				$userdata = array(
					'ID'           => $user->ID,
					'user_email'   => $ext_auth['data']['cliente']['email'],
					'user_login'   => $ext_auth['data']['cliente']['email'],
					'first_name'   => $ext_auth['data']['cliente']['firstname'],
					'last_name'    => $ext_auth['data']['cliente']['lastname'],
					'display_name' => $ext_auth['data']['cliente']['firstname'] . " " . $ext_auth['data']['cliente']['lastname'],
					'token'        => $ext_auth['data']['cliente']['gp_token'],
					'id_gp'        => $ext_auth['data']['cliente']['id_cliente'],
					'nip_gp' 	   => $ext_auth['data']['cliente']['nip'],
					'token_inicio' => $ext_auth['data']['token']
				);

				$this->gp_logs('gp_authenticate_user', 'Actualizo usuario');
				$user_data = wp_update_user($userdata); // se actualiza el usuario
				update_user_meta($user->ID, 'billing_first_name', $ext_auth['data']['cliente']['firstname']);
				update_user_meta($user->ID, 'billing_last_name', $ext_auth['data']['cliente']['lastname']);

				// verificar posible error
				if (is_wp_error($user_data)) {
					$user = new WP_Error('denied', __("ERROR: Al actualizar datos"));
					$error_message = $user_data->get_error_message();
					$this->gp_logs('gp_authenticate_user', 'Error', $error_message);
					$this->gp_logs('gp_authenticate_user', "Fin Autenticación", "\n-----");
				} else {
					// Success!
					$this->gp_logs('gp_authenticate_user', 'Inicio de sesión exitoso');
					$this->gp_logs('gp_authenticate_user', "Fin Autenticación", "\n-----");
				}
			}
		} else {
			// el usuario no existe en GP
			$user = new WP_Error('authentication_failed', __("Usuario o contraseña incorrecto, intentelo nuevamente."));
			$this->gp_logs('gp_authenticate_user', 'Datos incorrectos');
			$this->gp_logs('gp_authenticate_user', "Fin Autenticación", "\n-----");
		}
	
		if(!is_wp_error($user)){
			//cookie usuario
			setcookie('_gp_user_name', $userdata['first_name']." ".$userdata['last_name'], time()+31556926, '/');

			$lat = $user->gp_lat_shipping;
			$lng = $user->gp_lng_shipping;
			$calle = $user->shipping_address_1;
			$num_exterior = $user->_gp_exterior_number;
			$colonia = $user->_gp_suburb;
			$cp = $user->shipping_postcode;
			$ciudad = $user->shipping_city;

			if(!empty($lat) && !empty($lng)){
				if(!empty($calle) && !empty($num_exterior) && !empty($colonia) && !empty($cp) && !empty($ciudad)){
					$address_short = urldecode($ciudad . ', CP ' . $cp);
					$address_long = urldecode($calle . ' ' . $num_exterior . ', ' . $colonia . ', CP ' . $cp . ', ' . $ciudad);
	
					setcookie('_gp_geo_lat', $lat, time()+31556926, '/' );
					setcookie('_gp_geo_lng', $lng, time()+31556926, '/' );
					setcookie('_gp_geo_address_short', $address_short, time()+31556926, '/' );
					setcookie('_gp_geo_address_long', $address_long, time()+31556926, '/' );
					setcookie('_gp_geo_pc', $cp, time()+31556926, '/' );
				}
			}
		}

		// quita la verificacion de WP (ya validamos)
		remove_action('authenticate', 'wp_authenticate_username_password',  20, 3);
		remove_action('authenticate', 'wp_authenticate_email_password',     20, 3);
		return $user;
	}
	
	/**
	 * Elimina las cookies del usuario actual
	 * 
	 */
	
	public function gp_logout($x){
		//matar cokies
		setcookie('_gp_user_name', '', 0, '/');
		return true;
	}

	/**
	 * Actualiza datos de la cuenta GP.
	 *
	 * @since    1.0.0
	 */
	public function gp_update_user($user_id, $old_user_data, $userdata){
		// verifica que el usuario ha iniciado sesión
		// if (is_user_logged_in()) {
		// 	// verifica que no se ejecute más de una vez
		// 	if (did_action('profile_update') !== 1) {
		// 		return;
		// 	}

		// 	// verifica que se guarde de my-account
		// 	if (isset($_POST['save_account_details']) && $_POST['save_account_details']) {
		// 		$user = get_userdata($user_id);
	
		// 		$args = array(
		// 			'body' => json_encode(array(
		// 				'id_cliente' => $user->id_gp,
		// 				'gp_token'   => $user->token,
		// 				'nombre'     => $user->user_firstname,
		// 				'apellido'   => $user->user_lastname
		// 			)),
		// 			'headers' => array(
		// 				'Content-Type' => 'application/json',
		// 				'data' => get_option('data-telefonero')
		// 			)
		// 		);
		// 		$url = get_option('ruta_telefonero') . "cliente/actualizacion";
		// 		$response = wp_remote_post($url, $args);
	
		// 		if (is_wp_error($response)) {
		// 			$error_message = $response->get_error_message();
		// 			echo "Something went wrong: $error_message";
		// 			exit;
		// 		}
		// 	}
		// }
		// verifica que el usuario ha iniciado sesión
		if (is_user_logged_in()) {
			// verifica que no se ejecute más de una vez
			if (did_action('profile_update') !== 1) {
				return;
			}
	
			// verifica que se guarde de my-account
			if (isset($_POST['save_account_details']) && $_POST['save_account_details']) {
				$user = get_userdata($user_id);
	
				$args = array(
					'body' => json_encode(array(
						'email' => $user->user_email,
						'nip'   => $user->nip_gp,
						'firstname'     => $user->user_firstname,
						'lastname'   => $user->user_lastname
					)),
					'headers' => array(
						'Content-Type' => 'application/json',
						'data-jwt-master' => get_option('data-jwt-master')
					)
				);
				$url = get_option('ruta_gameplanet') . "cliente/editar";
				$response = wp_remote_post($url, $args);
	
				if (is_wp_error($response)) {
					$error_message = $response->get_error_message();
					echo "Something went wrong: $error_message";
					exit;
				}
				
				$ext_auth = json_decode($response['body'], true);
				if($ext_auth['success'] && $ext_auth['code'] == 200){
					// success
				} else{
					echo "Error al actualizar tus datos";
					exit;
				}
			}
		}
	}

	public function gp_validate_user_nip_len( $user_id ) {
		// For Favorite color
		if( isset( $_POST['nip_gp'] ) ){
			$nip = sanitize_text_field( $_POST['nip_gp'] );
			update_user_meta( $user_id, 'nip_gp', $nip );
		}
	}
	
	/**
	 * Evita que se actualize el email y maneja errores de nip
	 *
	 * @since    1.0.0
	 */
	public function gp_prevent_user_email_update(&$error, &$user){
		$current_user = get_user_by('id', $user->ID);
		$current_email = $current_user->user_email;
		if ($current_email !== $user->user_email) {
			$error->add('error', "<strong>Dirección de correo electrónico</strong> no puede ser modificado.");
		}
		if( isset( $_POST['nip_gp'] ) ){
			$nip = sanitize_text_field( $_POST['nip_gp'] );
			if(empty($nip)){
				$error->add( 'error', "<strong>NIP</strong> es un campo requerido.",'');
			} elseif(strlen($nip) != 4){
				$error->add( 'error', "<strong>NIP</strong> debe tener 4 dígitos.",'');
			} elseif(!is_numeric($nip)){
				$error->add( 'error', "<strong>NIP</strong> debe tener 4 dígitos.",'');
			} else{
				// success
			}
		} else{
			$error->add( 'error', "<strong>NIP</strong> es un campo requerido.",'');
		}
	}
	
	/**
	 * Llama api gameplanet para guardar información
	 *
	 * @since    1.0.0
	 */
	public function gp_update_user_information($user_id, $old_user_data){
		// verifica que el usuario ha iniciado sesión
		if (is_user_logged_in()) {
			// verifica que no se ejecute más de una vez
			if (did_action('profile_update') !== 1) {
				return;
			}
			
			// verifica que se guarde de my-account
			if (isset($_POST['save_account_details']) && $_POST['save_account_details']) {
				$user = get_userdata($user_id);
				$nip = sanitize_text_field( $_POST['nip_gp'] );
				$firstname = sanitize_text_field( $_POST['account_first_name'] );
				$lastname = sanitize_text_field( $_POST['account_last_name'] );
	
	
				$args = array(
					'body' => json_encode(array(
						'email' => $user->user_email,
						'nip' => $nip,
						'firstname' => $firstname,
						'lastname' => $lastname
					)),
					'headers' => array(
						'Content-Type' => 'application/json',
						'data-jwt-master' => get_option('data-jwt-master')
					)
				);
				$url = get_option('ruta_gameplanet') . "cliente/editar";
				$response = wp_remote_post($url, $args);
	
				if (is_wp_error($response)) {
					$error_message = $response->get_error_message();
					wc_clear_notices();
					wc_add_notice( '<strong>Error</strong> al actualizar la información. Code: GP-100', 'error' );
					remove_action('woocommerce_save_account_details', 'gp_save_custome_user_details', 10);
				} else{
					// success
				}
			}
		}
	}
	
	/**
	 * Guarda nuevos campos en WC
	 *
	 * @since    1.0.0
	 */	
	public function gp_save_custome_user_details($user_id){
		$nip = sanitize_text_field( $_POST['nip_gp'] );
		update_user_meta( $user_id, 'nip_gp', $nip );
	}
	
	/**
	 * Script para evitar la manipulación del
	 * email en my-account
	 *
	 * @since    1.0.0
	 */
	public function gp_disable_user_email_input(){
		$script = ' <script>gp_email();</script> ';
    	echo $script;
	}

	/**
	 * Antes de crear una cuenta, verifica que no exista una
	 * cuenta en GP con el mismo email.
	 *
	 * @since    1.0.0
	 */
	public function gp_prevent_old_email($user_login, $user_email, $errors){
		$url = get_option('ruta_gameplanet') . "cliente/info/" . $user_email;
		$args = array(
			'headers' => array(
				'Content-Type' => 'application/json',
				'data-jwt-master' => get_option('data-jwt-master')));
		$response = wp_remote_get($url, $args);
		
		if (is_wp_error($response)) {
			$error_message = $response->get_error_message();
			// echo "Something went wrong: $error_message";
			$errors->add(
				'email_exists',
				' Error al buscar la información. Inténtelo más tarde. '
			);
			return $errors;
		}
		$ext_auth = json_decode($response['body'], true);
		// si el correo existe en magento
		if ($ext_auth['success'] || $ext_auth['success'] == 200) {
			error_log(print_r($ext_auth, true));
			// crea mensaje de error y sale
			$errors->add(
				'email_exists',
				sprintf(
					' Este correo electrónico ya está registrado. Accede o recupera tu contraseña <a href="%s">aquí</a>.',
					wp_lostpassword_url()
				)
			);
			return $errors;
		}
		return true;
	}
	
	/**
	 * Cambia la contraseña de la cuenta GP.
	 *
	 * @since    1.0.0
	 */
	public function gp_change_password($errors, $user){
		$new_pass = sanitize_text_field($_POST['password_1']);
		$args = array(
			'body' => json_encode(array(
				'email' => $user->user_email,
				'newpass' => $new_pass
			)),
			'headers' => array(
				'Content-Type' => 'application/json',
				'data-jwt-master' => get_option('data-jwt-master')
			)
		);
		$url = get_option('ruta_gameplanet') . "cliente/cambia_password2";
		$response = wp_remote_post($url, $args);
		
		if (is_wp_error($response)) {
			gp_logs('gp_change_password', 'args', $args);
			gp_logs('gp_change_password', 'url', $url);
			gp_logs('gp_change_password', 'Error', $response->get_error_message());
			gp_logs('gp_change_password', "Fin recuperar contraseña", "\n-----");
			$errors->add( 'error', 'Error. Inténtelo más tarde.','');
			return $errors;
		}
		
		$resutl = json_decode($response['body'], true);
		
		// si la contraseña es incorrecta devuelve un error
		if (!$resutl['success'] || $resutl['code'] != 200) {
			gp_logs('gp_change_password', 'args', $args);
			gp_logs('gp_change_password', 'url', $url);
			gp_logs('gp_change_password', 'response', $resutl);
			if(isset($resutl['message'])){
				gp_logs('gp_change_password', 'Error', $resutl['message']);
				gp_logs('gp_change_password', "Fin cambio de contraseña", "\n-----");
				$errors->add( 'error', $resutl['message'],'');
				return $errors;
			} else{
				gp_logs('gp_change_password', 'Error');
				gp_logs('gp_change_password', "Fin cambio de contraseña", "\n-----");
				$errors->add( 'error', 'Error. No se pudo actualizar la contraseña','');
				return $errors;
			}
		}
	}
	
	/**
	 * Cambia la contraseña de la cuenta GP.
	 *
	 * @since    1.0.0
	 */
	public function gp_user_lost_password(){
		//? "if" para ejecutar solo en "lost password page" y que el campo del correo no esté vacio
		if (isset($_POST['wc_reset_password'], $_POST['user_login'])) {
			//? Un "nonce" es un "numero usado una vez" usado para proteger URLs y formularios de posibles ataques
			$nonce_value = wc_get_var($_REQUEST['woocommerce-lost-password-nonce'], wc_get_var($_REQUEST['_wpnonce'], ''));
			// Verifica que el nonce de seguridad sea correcto y sea usado dentro del tiempo limite
			if (!wp_verify_nonce($nonce_value, 'lost_password')) {
				return;
			}
	
			$this->gp_logs('gp_user_lost_password', 'Inicio recuperar contraseña');
	
			// obtener datos necesarios
			$email = sanitize_text_field($_POST['user_login']);
			$user = get_user_by('email', $email);
			@$userId = $user->ID;
	
			$args = array(
				'body' => json_encode(array(
					'email' => $email
				)),
				'headers' => array(
					'Content-Type' => 'application/json',
					'data' => get_option('data-telefonero')
				)
			);
			$url = get_option('ruta_telefonero') . "cliente/email/info";
			$this->gp_logs('gp_user_lost_password', 'Endpoint (POST)',  $url);
			$response = wp_remote_post($url, $args);
	
			if (is_wp_error($response)) {
				$this->gp_logs('gp_user_lost_password', 'Error', $response->get_error_message());
				$this->gp_logs('gp_user_lost_password', "Fin recuperar contraseña", "\n-----");
	
				$this->gp_mensaje('error', "Algo salió mal, inténtelo más tarde.");
				exit;
			}
			$this->gp_logs('gp_user_lost_password', '3. RESPONSE ', $response);
			$ext_auth = json_decode($response['body'], true);
	
			// si existe en gamers
			if ($userId) {
				$this->gp_logs('gp_user_lost_password', "4. Termino\n--------------------------------------------------------------");
				// Success
			} else {
				//al no existir en gamers ver si existe en magento
				if (@$ext_auth['result']['id_cliente']) {
					$comb = "a0AbBc1CdDe2EfFg3Gh(Hi4IjJk5KlLm6MnNo7OpPq8QrRs#StTu%UvVw&W)xXy/YzZ@";
					$shfl = str_shuffle($comb);
					$pwd = substr($shfl,0,16);
					// al existir en magento crea usuario en gamers
					$userdata = array(
						'user_email'   => $ext_auth['result']['email'],
						'user_login'   => $ext_auth['result']['email'],
						'user_pass'    => $pwd,
						'first_name'   => $ext_auth['result']['nombre'],
						'last_name'    => $ext_auth['result']['apellido'],
						'display_name' => $ext_auth['result']['nombre'] . " " . $ext_auth['result']['apellido'],
						'token'        =>  $ext_auth['result']['gp_token'],
						'id_gp'        =>  $ext_auth['result']['id_cliente']
					);
	
					$new_user_id = wp_insert_user($userdata);
					$this->gp_logs('gp_user_lost_password', "4. Creo usuario");
					$this->gp_logs('gp_user_lost_password', "5. Termino\n--------------------------------------------------------------");
				} else {
					// manda error al no existir en gamers ni en magento
					$this->gp_mensaje('error', "No se encuentra registrado, favor de crear una cuenta.");
					$this->gp_logs('gp_user_lost_password', "4. Usuario no existe");
					$this->gp_logs('gp_user_lost_password', "5. Termino\n--------------------------------------------------------------");
				}
				return false;
			}
		}
	}
	
	/**
	 * Muestra mensaje en página my-account.
	 *
	 * @since    1.0.0
	 * 
	 * @param    string    	$tipo   	error, notice, success.
	 * @param    string    	$mensaje    Mensaje a guardar.
	 * @param    string    	$direccion  Página a redireccionar ['my-account'].
	 * 
	 */
	function gp_mensaje($tipo, $mensaje, $direccion = '/my-account'){
		if (!WC()->session->has_session()) {
			WC()->session->set_customer_session_cookie(true);
		}
		wc_clear_notices();
		wc_add_notice(__($mensaje), $tipo);
		wp_redirect(site_url($direccion));
	}
	
	/**
	 * Crea log si no existe y/o escribe registro.
	 *
	 * @since    1.0.0
	 * @param    string    	$funcion	Nombre de la función donde se llama.
	 * @param    string    	$mensaje    Mensaje a guardar.
	 * @param    mixed   	$extra    	Opcional. Información extra, codifica en JSON si es array.
	 * 
	 * @return   int|false	Número de bytes escritos o falso en un error.
	 */
	function gp_logs($funcion, $mensaje, $extra = null){
		$directorio = './wp-content/gp/gp_logs/';

		if (!file_exists($directorio)) {
			mkdir($directorio, 777, true);
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
	 * Elimina campo "dashboard" de "my-account".
	 *
	 * @since    1.0.0
	 * @return   array                Regresa campos de "my-account".
	 */
	public function gp_remove_dashboard($items) {
		unset($items['dashboard']);
		return $items;
	}

	/**
	 * Añade mensaje sobre formulario "registro".
	 *
	 * @since    1.0.0
	 */
	public function gp_mensaje_registro(){
		echo "<p>Si aún no tienes cuenta en GamePlanet, Gamers o PlanetShop, aquí podrás registrarte.</p>";
	}

	/**
	 * Añade mensaje sobre formulario "acceder".
	 *
	 * @since    1.0.0
	 */
	public function gp_mensaje_acceder(){
		echo "<p>Inicia sesión con tu cuenta GamePlanet, Gamers o PlanetShop.</p>";
	}

		/**
	 * Añade número exterior/interior a "shipping-address" para orden/checkout
	 * 
	 * @param string   $address     Formatted shipping address string.
	 * @param array    $raw_address Raw shipping address.
	 * @param WC_Order $order       Order data. @since 3.9.0
	 *
	 * @return string
	 */
	public function gp_format_shipping_address( $address, $raw_address, $order ){

		$country_states   = WC()->countries->get_states( $order->get_shipping_country() );

		$nombre = $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();

		$new_addr = array();
		if(!empty($nombre)){
			$new_addr[] = $nombre;
		}
		
		$direccion = $order->get_shipping_address_1();
		if(!empty($direccion)){
			$new_addr[] = $direccion;
		}
		
		$num_ext = $order->get_meta('_gp_exterior_number');
		if(!empty($num_ext)){
			$new_addr[] = '#' . $num_ext;
		}
		
		$num_int = $order->get_meta('_gp_interior_number');
		if(!empty($num_int)){
			$new_addr[] = 'Int ' . $num_int;
		}
		
		$colonia = $order->get_meta('_gp_suburb');
		if(!empty($colonia)){
			$new_addr[] = $colonia;
		}
		
		$ciudad = $order->get_shipping_city();
		if(!empty($ciudad)){
			$new_addr[] = $ciudad;
		}
		
		if(isset($country_states[$order->get_shipping_state()])){
			$estado = $country_states[$order->get_shipping_state()];
			if(!empty($estado)){
				$new_addr[] = $estado;
			}
		}
		
		$postcode = $order->get_shipping_postcode();
		if(!empty($postcode)){
			$new_addr[] = $postcode;
		}
		
		$telefono = $order->get_shipping_phone();
		if(!empty($telefono)){
			$new_addr[] = $telefono;
		}

		// return $address;
		return implode('<br/>', $new_addr);
	}


	public function gp_address_format( $formats ) {
		// error_log(print_r($formats, true));
		if(is_account_page()){
			// $formats[ 'default' ] = "{name}\n{company}\n{address_1}\n{address_2}\n{city}\n{state}\n{postcode}\n{country}\n{phone}\n{email}";
			// $formats[ 'default' ] = "{name}\n{company}\n{address_1}\n{address_2}\n{num_ext}\n{num_int}\n{city}\n{state}\n{postcode}\n{country}\n{phone}\n{email}";
		}
		$formats[ 'default' ] = "{name}\n{address_1}\n{num_ext}\n{num_int}\n{colonia}\n{city}\n{state}\n{postcode}\n{country}\n{phone}\n{email}";
		return $formats;
	}

	public function gp_set_address_values( $args, $customer_id, $name ){
		// the phone is saved as billing_phone and shipping_phone
		// if(is_account_page()){
		// 	if($name == 'billing'){
		// 		$args['phone'] = get_user_meta( $customer_id, $name . '_phone', true );
		// 		$args['email'] = get_user_meta( $customer_id, $name . '_email', true );
		// 	} elseif($name == 'shipping'){
		// 		$num_int = get_user_meta( $customer_id, '_gp_interior_number', true );
		// 		if(!empty($num_int)){
		// 			$args['num_int'] = 'Int. ' . $num_int;
		// 		}
		// 		$num_ext = get_user_meta( $customer_id, '_gp_exterior_number', true );
		// 		if(!empty($num_ext)){
		// 			$args['num_ext'] = '#' . $num_ext;
		// 		}
		// 	}
		// }
		if($name == 'billing'){
			$telefono = get_user_meta( $customer_id, 'billing_phone', true );
			if(!empty($telefono)){
				$args['phone'] = $telefono;
			}
			
			$email = get_user_meta( $customer_id, 'billing_email', true );
			if(!empty($telefono)){
				$args['email'] = $email;
			}
			
		} else{
			
			$num_int = get_user_meta( $customer_id, '_gp_interior_number', true );
			if(!empty($num_int)){
				$args['num_int'] = $num_int;
			}
			
			$num_ext = get_user_meta( $customer_id, '_gp_exterior_number', true );
			if(!empty($num_ext)){
				$args['num_ext'] = $num_ext;
			}
			$colonia = get_user_meta( $customer_id, '_gp_suburb', true );
			if(!empty($colonia)){
				$args['colonia'] = $colonia;
			}
		}
		return $args;
	}

	public function gp_show_address_values_my_account( $replacements, $args ){
		// we want to replace {phone} in the format with the data we populated
		// if(is_account_page()){
		// 	// if(isset($args['phone']) && isset($args['email'])){
		// 	// 	$replacements['{phone}'] = $args['phone'];
		// 	// 	$replacements['{email}'] = $args['email'];
		// 	// } else{
		// 	// 	$replacements['{phone}'] = '';
		// 	// 	$replacements['{email}'] = '';
		// 	// }
		// 	if(isset($args['phone'])){
		// 		$replacements['{phone}'] = $args['phone'];
		// 	} else{
		// 		$replacements['{phone}'] = '';
		// 	}
			
		// 	if(isset($args['email'])){
		// 		$replacements['{email}'] = $args['email'];
		// 	} else{
		// 		$replacements['{email}'] = '';
		// 	}
			
		// 	if(isset($args['num_int'])){
		// 		$replacements['{num_int}'] = $args['num_int'];
		// 	} else{
		// 		$replacements['{num_int}'] = '';
		// 	}
			
		// 	if(isset($args['num_ext'])){
		// 		$replacements['{num_ext}'] = $args['num_ext'];
		// 	} else{
		// 		$replacements['{num_ext}'] = '';
		// 	}
		// }
		if(isset($args['phone'])){
			$replacements['{phone}'] = $args['phone'];
		} else{
			$replacements['{phone}'] = '';
		}

		if(isset($args['email'])){
			$replacements['{email}'] = $args['email'];
		} else{
			$replacements['{email}'] = '';
		}

		if(isset($args['num_int'])){
			$replacements['{num_int}'] = 'Int. ' . $args['num_int'];
		} else{
			$replacements['{num_int}'] = '';
		}

		if(isset($args['num_ext'])){
			$replacements['{num_ext}'] = '#' . $args['num_ext'];
		} else{
			$replacements['{num_ext}'] = '';
		}

		if(isset($args['colonia'])){
			$replacements['{colonia}'] = $args['colonia'];
		} else{
			$replacements['{colonia}'] = '';
		}


		return $replacements;
	}

	/**
	 * Elimina los botones de pago/cancelar en página de "órdenes".
	 *
	 * @since    1.0.0
	 */	
	public function gp_remove_orders_extra_buttons($actions, $order) {
		unset($actions['pay']);
		unset($actions['cancel']);

		return $actions;
	}

	/**
	 * Cambia contraseña desde my-account->detalles de cuenta.
	 *
	 * @since    1.0.0
	 */
	public function gp_change_password_my_account( $check, $password, $hash, $user_id ) {
		if ( ! is_wc_endpoint_url( 'edit-account' ) ){
			return $check;
		}

		if((isset($_POST['password_1']) && !empty($_POST['password_1'])) && (isset($_POST['password_2']) && !empty($_POST['password_2']))){
			$user_info = get_userdata($user_id);
			$user_email = $user_info->user_email;
			$new_password = $_POST['password_1'];

			$args = array(
				'body' => json_encode(array(
					'email'   => $user_email,
					'oldpass' => $password,
					'newpass' => $new_password
				)),
				'headers' => array(
					'Content-Type' => 'application/json',
					'data-jwt-master' => get_option('data-jwt-master')
				)
			);
	
			$url = get_option('ruta_gameplanet') . "cliente/cambia_password1";
			$response = wp_remote_post($url, $args);

			if (is_wp_error($response)) {
				wc_add_notice( 'No pudimos actualizar su contraseña, inténtelo más tarde.', 'error' );
				return false;
			}

			$ext_auth = json_decode($response['body'], true);
			if ($response['response']['code'] == 200 && $ext_auth['success'] && $ext_auth['code'] == 200) {
				$check = true;
			} else{
				$check = false;
			}
		}
		return $check;
	}

	/**
	 * Función para detener ventas
	 */
	public function gp_detener_ventas() {
		if(!current_user_can( 'manage_woocommerce' )){
			remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
			remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
			remove_action('woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20);
			remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
		}
	}

}
