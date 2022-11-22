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
 * @package    Gameplanet_Planetshop
 * @subpackage Gameplanet_Planetshop/includes
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
 * @package    Gameplanet_Planetshop
 * @subpackage Gameplanet_Planetshop/includes
 * @author     GamePlanet
 */
class Gameplanet_Planetshop {

	/**
	 * Responsable de mantener y registrar todos los hook que utiliza el plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Gameplanet_Planetshop_Loader    $loader    Mantiene y registra todos los hooks para el plugin.
	 */
	protected $loader;

	/**
	 * Identificador único para el plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $gameplanet_planetshop    String usado para identificar el plugin.
	 */
	protected $gameplanet_planetshop;

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
		if ( defined( 'GAMEPLANET_PLANETSHOP_VERSION' ) ) {
			$this->version = GAMEPLANET_PLANETSHOP_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->gameplanet_planetshop = 'gameplanet-planetshop';

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
	 * - Gameplanet_Planetshop_Loader. Maneja los hooks del plugin.
	 * - Gameplanet_Planetshop_i18n. Define la funcionalidad de la internasionalización.
	 * - Gameplanet_Planetshop_Admin. Define los hooks del área de admin.
	 * - Gameplanet_Planetshop_Public. Define los hooks del área pública.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-gameplanet-planetshop-loader.php';

		/**
		 * Clase responsable de definir la funcionalidad de la internacionalización
		 * del plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-gameplanet-planetshop-i18n.php';
		
		/**
		 * Archivo responsable de sobreescribir funciones
		 * de otros plugins.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/gameplanet-planetshop-override.php';

		/**
		 * Clase responsable de definir los hooks que ocurren dentro del área de admin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-gameplanet-planetshop-admin.php';

		/**
		 * Clase responsable de definir los hooks que ocurren dentro del área pública.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-gameplanet-planetshop-public.php';

		$this->loader = new Gameplanet_Planetshop_Loader();

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

		$plugin_i18n = new Gameplanet_Planetshop_i18n();

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

		$plugin_admin = new Gameplanet_Planetshop_Admin( $this->get_gameplanet_planetshop(), $this->get_version() );

		//* carga css para área admin
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		//* carga js para área admin
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		//* genera menú en admin
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'gp_ps_admin_menu', 11);
		//* añade información "billing/shipping"
		$this->loader->add_action( 'woocommerce_new_order', $plugin_admin, 'gp_ps_data_new_order');
		//* metadata (orden) para webhook
		$this->loader->add_action( 'woocommerce_order_status_processing', $plugin_admin, 'gp_ps_meta_data_order');
		$this->loader->add_action( 'woocommerce_order_status_changed', $plugin_admin, 'send_custom_email_notifications', 10, 4);

		//* hooks para ajax
		$this->loader->add_action( 'wp_ajax_gp_ajax_sucursales', $plugin_admin, 'gp_ajax_sucursales');
		$this->loader->add_action( 'wp_ajax_nopriv_gp_ajax_sucursales', $plugin_admin, 'gp_ajax_sucursales');

		//* hooks para ajax
		$this->loader->add_action( 'wp_ajax_gp_ajax_mi_cuenta_button', $plugin_admin, 'gp_ajax_mi_cuenta_button');
		$this->loader->add_action( 'wp_ajax_nopriv_gp_ajax_mi_cuenta_button', $plugin_admin, 'gp_ajax_mi_cuenta_button_no_priv');

		//* hooks ajax "widget" producto simple
		$this->loader->add_action( 'wp_ajax_gp_ajax_disponibilidad', $plugin_admin, 'gp_ajax_disponibilidad');
		$this->loader->add_action( 'wp_ajax_nopriv_gp_ajax_disponibilidad', $plugin_admin, 'gp_ajax_disponibilidad');

	}

	/**
	 * Registra los hooks relacionados a la funcionalidaddel plugin
	 * en el área pública.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Gameplanet_Planetshop_Public( $this->get_gameplanet_planetshop(), $this->get_version() );

		//* carga css para área pública
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		//* carga js para área pública
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		//* Registra shortcodes
		$this->loader->add_action( 'init', $plugin_public, 'gp_ps_register_shortcodes' );
		//* declara ruta(s) para rest api
		$this->loader->add_action( 'rest_api_init', $plugin_public, 'gp_ps_events_endpoint');

		//* mueve "x" dentro de lightbox [template flatsome]
		$this->loader->add_action( 'flatsome_lightbox_close_btn_inside', $plugin_public, '__return_true');
		//* añade meta data al producto
		$this->loader->add_action( 'woocommerce_add_cart_item_data', $plugin_public, 'gp_ps_add_cart_item_data', 10, 3);
		//* imprime meta datos del producto en carrito/checkout
		$this->loader->add_action( 'woocommerce_get_item_data', $plugin_public, 'gp_ps_print_item_data', 10, 2);
		//* crear cookies
		$this->loader->add_action( 'init', $plugin_public, 'gp_ps_cookie_loader');
		//* añade modal a footer
		$this->loader->add_action( 'wp_footer', $plugin_public, 'gp_ps_modal_footer');
		//* cantidad de producto (carrito)
		$this->loader->add_action( 'woocommerce_cart_item_quantity', $plugin_public, 'gp_ps_cart_quantity', 10, 3);
		//* añade botón de disponibilidad de producto
		// $this->loader->add_action( 'woocommerce_single_product_summary', $plugin_public, 'gp_ps_btn_disponibilidad', 49);
		//* añade imágenes a checkout
		// $this->loader->add_filter( 'woocommerce_cart_item_name', $plugin_public, 'gp_ps_image_on_checkout', 20, 3);
		//* añade imágenes a order pay
		// $this->loader->add_filter( 'woocommerce_order_item_name', $plugin_public, 'gp_ps_image_on_order_pay', 20, 3);
		//* agrega texto antes de "cantidad" en producto individual
		$this->loader->add_action( 'woocommerce_before_add_to_cart_quantity', $plugin_public, 'gp_ps_quantity_text');
		//* modifica los campos de checkout
		$this->loader->add_filter( 'woocommerce_checkout_fields', $plugin_public, 'gp_ps_override_checkout_fields', 20, 1);
		//* agrega datos (texto) de shipping
		$this->loader->add_action( 'woocommerce_after_checkout_shipping_form', $plugin_public, 'gp_ps_write_shipping_info');
		//* agrega datos (texto) de facturación
		$this->loader->add_action( 'woocommerce_after_checkout_billing_form', $plugin_public, 'gp_ps_write_billing_info');
		//* cargar css para email
		$this->loader->add_filter( 'woocommerce_email_styles', $plugin_public, 'gp_ps_email_css', 9999, 2);
		//* validación de productos de carrito en checkout
		$this->loader->add_action( 'woocommerce_before_checkout_form', $plugin_public, 'gp_ps_validar_carrito_checkout', 10, 1);
		// $this->loader->add_action( 'woocommerce_checkout_update_order_review', $plugin_public, 'gp_ps_validar_carrito_checkout', 10, 1);
		//* guarda metadata de carrito a orden (por producto)
		$this->loader->add_filter( 'woocommerce_checkout_create_order_line_item', $plugin_public, 'gp_ps_save_cart_metadata', 10, 4);
		//* modifico costo de envio
		$this->loader->add_filter( 'woocommerce_package_rates', $plugin_public, 'gp_ps_update_shipping_cost', 10, 2);
		$this->loader->add_filter( 'woocommerce_cart_shipping_method_full_label', $plugin_public, 'gp_etiqueta_costo_envio', 10, 2);
		//* valida que se agregue meta datos a producto (carrito)
		$this->loader->add_action( 'woocommerce_add_to_cart_validation', $plugin_public, 'gp_ps_add_to_cart_validation', 10, 4);
		//* modifico costo de envio
		$this->loader->add_filter( 'woocommerce_available_payment_gateways', $plugin_public, 'gp_ps_return_payment_method', 20, 1);
		//* añade campo "lat/lng" en formulario de "shipping
		$this->loader->add_filter( 'woocommerce_shipping_fields', $plugin_public, 'gp_add_custom_field_shipping_form', 20, 1);
		//* modifica forma de imprimir meta datos del producto
		// $this->loader->add_filter( 'woocommerce_display_item_meta', $plugin_public, 'gp_ps_modify_display_item_meta', 20, 3);
		//* quita "confirmación" de cerrar sesión
		$this->loader->add_action( 'template_redirect', $plugin_public, 'wc_bypass_logout_confirmation');
		//* quita campos de "datos de tu cuenta"
		$this->loader->add_filter( 'woocommerce_billing_fields', $plugin_public, 'gp_ps_remove_billing_fields', 20, 1);
		//* Quital botón en checkout que elimina producto del carrito
		$this->loader->add_filter( 'woocommerce_cart_item_name', $plugin_public, 'gp_ps_delete_btn_remove_from_checkout', 20, 3);
		//* Añade mensaje en página thx
		$this->loader->add_action( 'woocommerce_thankyou', $plugin_public, 'gp_ps_thx_message');
		//* Añade mensaje en email
		$this->loader->add_action( 'woocommerce_email_before_order_table', $plugin_public, 'gp_ps_email_message', 10, 4);
		//* Modifica etiqueta de envío
		$this->loader->add_filter( 'woocommerce_order_shipping_to_display_shipped_via', $plugin_public, '__return_false');
		$this->loader->add_filter( 'woocommerce_order_shipping_to_display', $plugin_public, 'gp_ps_email_shipping_label', 20, 3);
		//* Elimina botón "ordenar de nuevo" en orden completada
		$this->loader->add_action( 'woocommerce_order_details_after_order_table', $plugin_public, 'gp_ps_remove_order_again_button', 1);
		//* Añade bloque de información en "order-view"
		$this->loader->add_action( 'woocommerce_order_details_before_order_table', $plugin_public, 'gp_ps_info_block_order_view');
		//* Modifica el precio del producto si es una preventa (monto_minimo)
		$this->loader->add_action( 'woocommerce_before_calculate_totals', $plugin_public, 'gp_ps_modifica_precio_carrito_checkout');
		//* Elimina acción "single product summary"
		$this->loader->add_action( 'woocommerce_single_product_summary', $plugin_public, 'gp_remove_single_product_summary', 10);
		
		//* Modifica etiqueta de envío
		$this->loader->add_filter( 'woocommerce_display_item_meta', $plugin_public, 'gp_modify_woocommerce_display_item_meta', 9999, 3);
		
		//* elimina los métodos de pago para hacer pruebas
		// $this->loader->add_action( 'init', $plugin_public, 'gp_remove_payment_for_test');
		
		
		//* función para añadir costo de garantía a tablas (carrito, checkout, email, admin order)
		$this->loader->add_action( 'woocommerce_cart_calculate_fees', $plugin_public, 'gp_add_garantia_fee', 10, 1);
		//* Redirecciona a checkout si producto tiene garantia
		// $this->loader->add_filter( 'woocommerce_add_to_cart_redirect', $plugin_public, 'gp_redirect_product_garantia');
		//* cambio el nombre de los elementos del menú de "my-account"
		// $this->loader->add_action( 'woocommerce_account_menu_items', $plugin_public, 'gp_my_account_menu_text');
		
		//*
		$this->loader->add_filter( 'woocommerce_cart_ready_to_calc_shipping', $plugin_public, 'gp_disable_shipping_row_on_cart');
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
	public function get_gameplanet_planetshop() {
		return $this->gameplanet_planetshop;
	}

	/**
	 * Referencia a la clase que maneja los hooks del plugin.
	 *
	 * @since     1.0.0
	 * @return    Gameplanet_Planetshop_Loader    Maneja los hooks del plugin.
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
