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
 * @package    Gameplanet_Openpay
 * @subpackage Gameplanet_Openpay/includes
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
 * @package    Gameplanet_Openpay
 * @subpackage Gameplanet_Openpay/includes
 * @author     GamePlanet
 */
class Gameplanet_Openpay {

	/**
	 * Responsable de mantener y registrar todos los hook que utiliza el plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Gameplanet_Openpay_Loader    $loader    Mantiene y registra todos los hooks para el plugin.
	 */
	protected $loader;

	/**
	 * Identificador único para el plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $gameplanet_openpay    String usado para identificar el plugin.
	 */
	protected $gameplanet_openpay;

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
		if ( defined( 'GAMEPLANET_OPENPAY_VERSION' ) ) {
			$this->version = GAMEPLANET_OPENPAY_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->gameplanet_openpay = 'gameplanet-openpay';

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
	 * - Gameplanet_Openpay_Loader. Maneja los hooks del plugin.
	 * - Gameplanet_Openpay_i18n. Define la funcionalidad de la internasionalización.
	 * - Gameplanet_Openpay_Admin. Define los hooks del área de admin.
	 * - Gameplanet_Openpay_Public. Define los hooks del área pública.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-gameplanet-openpay-loader.php';

		/**
		 * Clase responsable de definir la funcionalidad de la internacionalización
		 * del plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-gameplanet-openpay-i18n.php';

		/**
		 * Clase responsable de definir los hooks que ocurren dentro del área de admin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-gameplanet-openpay-admin.php';

		/**
		 * Clase responsable de definir los hooks que ocurren dentro del área pública.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-gameplanet-openpay-public.php';

		$this->loader = new Gameplanet_Openpay_Loader();

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

		$plugin_i18n = new Gameplanet_Openpay_i18n();

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

		$plugin_admin = new Gameplanet_Openpay_Admin( $this->get_gameplanet_openpay(), $this->get_version() );

		//* carga css para área admin
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		//* carga js para área admin
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		//* genera menú en admin
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'gp_openpay_admin_menu', 11);

	}

	/**
	 * Registra los hooks relacionados a la funcionalidaddel plugin
	 * en el área pública.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Gameplanet_Openpay_Public( $this->get_gameplanet_openpay(), $this->get_version() );
		//* carga css para área pública
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		//* carga js para área pública
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		//* inicia gateway
		$this->loader->add_action( 'plugins_loaded', $plugin_public, 'gp_openpay_init' );
		
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
	public function get_gameplanet_openpay() {
		return $this->gameplanet_openpay;
	}

	/**
	 * Referencia a la clase que maneja los hooks del plugin.
	 *
	 * @since     1.0.0
	 * @return    Gameplanet_Openpay_Loader    Maneja los hooks del plugin.
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
