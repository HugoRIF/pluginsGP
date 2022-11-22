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
 * @package    Gameplanet_Widgets
 * @subpackage Gameplanet_Widgets/includes
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
 * @package    Gameplanet_Widgets
 * @subpackage Gameplanet_Widgets/includes
 * @author     GamePlanet
 */
class Gameplanet_Widgets {

	/**
	 * Responsable de mantener y registrar todos los hook que utiliza el plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Gameplanet_Widgets_Loader    $loader    Mantiene y registra todos los hooks para el plugin.
	 */
	protected $loader;

	/**
	 * Identificador único para el plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $gameplanet_widgets    String usado para identificar el plugin.
	 */
	protected $gameplanet_widgets;

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
		if ( defined( 'GAMEPLANET_WIDGETS_VERSION' ) ) {
			$this->version = GAMEPLANET_WIDGETS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->gameplanet_widgets = 'gameplanet-widgets';

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Carga las dependencias requeridas para el plugin.
	 *
	 * Incluye los siguientes archivos que hacen el plugin:
	 *
	 * - Gameplanet_Widgets_Loader. Maneja los hooks del plugin.
	 * - Gameplanet_Widgets_Admin. Define los hooks del área de admin.
	 * - Gameplanet_Widgets_Public. Define los hooks del área pública.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-gameplanet-widgets-loader.php';
		
		/**
		 * Clase responsable de definir los hooks que ocurren dentro del área de admin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-gameplanet-widgets-admin.php';

		/**
		 * Clase responsable de definir los hooks que ocurren dentro del área pública.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-gameplanet-widgets-public.php';

		$this->loader = new Gameplanet_Widgets_Loader();

	}
	
	/**
	 * Registra los hooks relacionados a la funcionalidaddel plugin
	 * en el área de admin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Gameplanet_Widgets_Admin( $this->get_gameplanet_widgets(), $this->get_version() );

		//* carga css para área admin
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		//* carga js para área admin
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		//* hooks para disponibilidad de producto a domicilio (producto simple)
		$this->loader->add_action( 'wp_ajax_gp_disponibilidad_domicilio', $plugin_admin, 'gp_disponibilidad_domicilio');
		$this->loader->add_action( 'wp_ajax_nopriv_gp_disponibilidad_domicilio', $plugin_admin, 'gp_disponibilidad_domicilio');

	}

	/**
	 * Registra los hooks relacionados a la funcionalidaddel plugin
	 * en el área pública.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Gameplanet_Widgets_Public( $this->get_gameplanet_widgets(), $this->get_version() );

		//* carga css para área pública
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		//* carga js para área pública
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		//* añade widgets
		$this->loader->add_action( 'widgets_init', $plugin_public, 'gp_load_widgets' );

		//* si el widget está activo
		if(is_active_widget(false, false, 'widget_single_product_ps', true)){
			//* añade objetos en el widget pt1
			// $this->loader->add_action( 'woocommerce_before_add_to_cart_button', $plugin_public, 'gp_widget_add_single_product_element');
			//* añade objetos en el widget pt2
			$this->loader->add_action( 'woocommerce_after_add_to_cart_button', $plugin_public, 'gp_widget_add_single_product_button');
			$this->loader->add_action( 'woocommerce_after_add_to_cart_form', $plugin_public, 'gp_widget_add_single_product_availability');
		}
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
	public function get_gameplanet_widgets() {
		return $this->gameplanet_widgets;
	}

	/**
	 * Referencia a la clase que maneja los hooks del plugin.
	 *
	 * @since     1.0.0
	 * @return    Gameplanet_Widgets_Loader    Maneja los hooks del plugin.
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
