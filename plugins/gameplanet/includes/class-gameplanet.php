<?php

/**
 * Archivo que define clase núcleo del plugin
 *
 * Una definición de la clase que incluye atributos y funciones usados através del área
 * pública y de admin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Gameplanet
 * @subpackage Gameplanet/includes
 */

/**
 * La clase núcleo del plugin.
 *
 * Es usado para definir la internasionalización, hooks específicos para
 * área de admin y público.
 *
 * También mantiene el identificador único del plugin y la versión actual.
 *
 * @since      1.0.0
 * @package    Gameplanet
 * @subpackage Gameplanet/includes
 * @author     GamePlanet
 */
class Gameplanet {

	/**
	 * Responsable de mantener y registrar todos los hook que utiliza el plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Gameplanet_Loader    $loader    Mantiene y registra todos los hooks para el plugin.
	 */
	protected $loader;

	/**
	 * Identificador único para el plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $gameplanet    String usado para identificar el plugin.
	 */
	protected $gameplanet;

	/**
	 * Versión actual del plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    Versión actual del plugin.
	 */
	protected $version;

	/**
	 * Define la funcionabilidad núcleo del plugin.
	 *
	 * Define el nombre del plugin y la versión que puede ser usado a traves del plugin.
	 * Carga las dependencias, el local y los hooks para las áreas de admin y publica del sitio.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'GAMEPLANET_VERSION' ) ) {
			$this->version = GAMEPLANET_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->gameplanet = 'gameplanet';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Carga las dependencias requeridas para el plugin.
	 *
	 * Incluye los siguientes archivos que hacen el plugin:
	 *
	 * - Gameplanet_Loader. Maneja los hooks del plugin.
	 * - Gameplanet_i18n. Define la funcionalidad de la internasionalización.
	 * - Gameplanet_Admin. Define los hooks del área de admin.
	 * - Gameplanet_Public. Define los hooks del área pública.
	 *
	 * Crea una instancia del "loader" que será usado para registrar los hooks
	 * con WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * Clase responsable de manejar las acciones y filtros del
		 * núcleo del plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-gameplanet-loader.php';

		/**
		 * Clase responsable de definir la funcionalidad de la internacionalización
		 * del plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-gameplanet-i18n.php';

		/**
		 * Clase responsable de definir los hooks que ocurren dentro del área de admin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-gameplanet-admin.php';

		/**
		 * Clase responsable de definir los hooks que ocurren dentro del área pública.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-gameplanet-public.php';

		$this->loader = new Gameplanet_Loader();

	}

	/**
	 * Define el local para la internasionalización del plugin.
	 *
	 * Usa la clase Telefonica_Gamers_i18n para definir el dominio y registrar el hook
	 * con WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Gameplanet_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Registra los hooks relacionados a la funcionalidaddel plugin
	 * en el área de admin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Gameplanet_Admin( $this->get_gameplanet(), $this->get_version() );

		//! generales
		//* carga css para área admin
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		//* carga js para área admin
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		//* borrar footer admin
		$this->loader->add_action( 'admin_footer_text', $plugin_admin, '__return_empty_string');
		$this->loader->add_action( 'update_footer', $plugin_admin, '__return_empty_string', 11);
		//* añade link para ver página thx de orden
		$this->loader->add_action( 'woocommerce_admin_order_data_after_order_details', $plugin_admin, 'gp_show_order_thx_page_link');
		//! --

		//! gp
		//* información de contacto GP
		$this->loader->add_action( 'user_contactmethods', $plugin_admin, 'gp_add_contact_info');
		//* agrega columna a la tabla de órdenes en WC
		$this->loader->add_action( 'manage_edit-shop_order_columns', $plugin_admin, 'gp_add_column_to_admin_orders');
		//* agrega el método de pago a la tabla de órdenes en WC
		$this->loader->add_action( 'manage_shop_order_posts_custom_column', $plugin_admin, 'gp_add_payment_method_to_admin_orders', 10, 2);
		//* registro de plugins instalados
		$this->loader->add_action( 'activated_plugin', $plugin_admin, 'gp_plugin_activated_log', 10, 2);
		//* registro de plugins desactivados
		$this->loader->add_action( 'deactivated_plugin', $plugin_admin, 'gp_plugin_deactivated_log', 10, 2);
		//* registro de plugins borrados
		$this->loader->add_action( 'deleted_plugin', $plugin_admin, 'gp_plugin_deleted_log', 10, 2);
		//* genera menú en admin
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'gp_admin_menu');
		//* lista de url "no segura" para webhook
		$this->loader->add_action( 'http_request_args', $plugin_admin, 'gp_whitelist_url', 10, 2);
		//* autenticar usuario con datos GP
		$this->loader->add_action( 'authenticate', $plugin_admin, 'gp_authenticate_user', 10, 3);
		//! --

	}

	/**
	 * Registra los hooks relacionados a la funcionalidaddel plugin
	 * en el área pública.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Gameplanet_Public( $this->get_gameplanet(), $this->get_version() );

		//! generales
		//* carga css para área pública
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		//* carga js para área pública
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		//* quita los botones pagar/cancelar de página "órdenes"
		$this->loader->add_action( 'woocommerce_my_account_my_orders_actions', $plugin_public, 'gp_remove_orders_extra_buttons', 10, 2);
		//! --

		//! gp
		//* aumenta tiempo de respuesta http
		$this->loader->add_action( 'http_request_timeout', $plugin_public, 'gp_extend_timeout');
		//* autenticar usuario con datos GP
		$this->loader->add_action( 'authenticate', $plugin_public, 'gp_authenticate_user', 10, 3);
		//* añade campos de nombre/apellido en formulario de registro
		$this->loader->add_action( 'woocommerce_register_form_start', $plugin_public, 'gp_add_registration_fields' );
		//* valida campos de nombre/apellido en formulario de registro
		$this->loader->add_action( 'woocommerce_registration_errors', $plugin_public, 'gp_validate_registration_fields', 10, 3);
		//* crea usuario GP
		$this->loader->add_action( 'woocommerce_created_customer', $plugin_public, 'gp_create_user');
		//* recupera contraseña del usuario
		$this->loader->add_action( 'wp_loaded', $plugin_public, 'gp_user_lost_password');
		//* verificar email al crear usuario
		$this->loader->add_action( 'validate_password_reset', $plugin_public, 'gp_change_password', 10, 2);
		//* evita que el usuario modifique su correo
		$this->loader->add_action( 'woocommerce_save_account_details_errors', $plugin_public, 'gp_prevent_user_email_update', 10, 2);
		$this->loader->add_action( 'woocommerce_after_edit_account_form', $plugin_public, 'gp_disable_user_email_input');
		//* actualiza datos GP
		$this->loader->add_action( 'profile_update', $plugin_public, 'gp_update_user', 10, 3);
		//* verificar email al crear usuario
		$this->loader->add_action( 'woocommerce_register_post', $plugin_public, 'gp_prevent_old_email', 10, 3);
		//* quitar "dashboard" de menú
		$this->loader->add_action( 'woocommerce_account_menu_items', $plugin_public, 'gp_remove_dashboard');
		//* Agrega mensaje de registro
		$this->loader->add_action( 'woocommerce_register_form_start', $plugin_public, 'gp_mensaje_registro', 9);
		//* Agrega mensaje de inicio de sesión
		$this->loader->add_action( 'woocommerce_login_form_start', $plugin_public, 'gp_mensaje_acceder');
		//! --
		
		//* aumenta tiempo de vida de la sesión
		$this->loader->add_action( 'auth_cookie_expiration', $plugin_public, 'gp_extend_session', 10, 3);
		
		//* muestra email y teléfono en escritorio > direcciones
		$this->loader->add_filter( 'woocommerce_localisation_address_formats', $plugin_public, 'gp_address_format', 9999, 1);
		$this->loader->add_filter( 'woocommerce_my_account_my_address_formatted_address', $plugin_public, 'gp_set_address_values', 9999, 3);
		$this->loader->add_filter( 'woocommerce_formatted_address_replacements', $plugin_public, 'gp_show_address_values_my_account', 9999, 2);
		
		//* cambia contraseña desde my-account->detalles de cuenta
		$this->loader->add_filter( 'check_password', $plugin_public, 'gp_change_password_my_account', 1, 4);

		//* añade número exterior/interior a "shipping-address" para orden/checkout
		$this->loader->add_filter( 'woocommerce_order_get_formatted_shipping_address', $plugin_public, 'gp_format_shipping_address', 20, 3);

		$this->loader->add_action( 'woocommerce_save_account_details', $plugin_public, 'gp_validate_user_nip_len', 12, 1);

		$this->loader->add_action( 'profile_update', $plugin_public, 'gp_update_user_information', 11, 2);
		$this->loader->add_action( 'woocommerce_save_account_details', $plugin_public, 'gp_save_custome_user_details', 10, 1);
		$this->loader->add_action( 'wp_logout', $plugin_public, 'gp_logout',10,1);

		//! Función para detener ventas (comentar para permitir ventas)
		// $this->loader->add_action( 'init', $plugin_public, 'gp_detener_ventas' );
		
	}

	/**
	 * Corre el "loader" para ejecutar todos los hooks con WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * El nombre del plugin usado para identificarlo con el contexto de WordPress
	 * y para definir la funcionalidad de la internasionalización.
	 *
	 * @since     1.0.0
	 * @return    string    El nombre del plugin.
	 */
	public function get_gameplanet() {
		return $this->gameplanet;
	}

	/**
	 * Referencia a la clase que maneja los hooks del plugin.
	 *
	 * @since     1.0.0
	 * @return    Gameplanet_Loader    Maneja los hooks del plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Recupera el número de versión del plugin.
	 *
	 * @since     1.0.0
	 * @return    string    El número de versión del plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
