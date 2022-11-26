<?php

/**
 * Funcionalidad del plugin específica para el área de admin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Gameplanet_Openpay
 * @subpackage Gameplanet_Openpay/admin
 */

/**
 * Funcionalidad del plugin específica para el área de admin.
 *
 * Define el nombre del plugin, versión y hooks para el área de admin.
 *
 * @package    Gameplanet_Openpay
 * @subpackage Gameplanet_Openpay/admin
 * @author     GamePlanet
 */
class Gameplanet_Openpay_Admin {

	/**
	 * El ID del plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $gameplanet_openpay    El ID del plugin.
	 */
	private $gameplanet_openpay;

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
	 * @param      string    $gameplanet_openpay       El nombre del plugin.
	 * @param      string    $version    La versión del plugin.
	 */
	public function __construct( $gameplanet_openpay, $version ) {

		$this->gameplanet_openpay = $gameplanet_openpay;
		$this->version = $version;

	}

	/**
	 * Registra el CSS para el área del admin.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->gameplanet_openpay, plugin_dir_url( __FILE__ ) . 'css/gameplanet-openpay-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Registra el JavaScript para el área del admin.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->gameplanet_openpay, plugin_dir_url( __FILE__ ) . 'js/gameplanet-openpay-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Crea las páginas para configurar las API y
	 * para la portabilidad (si se tienen activados otros plugins de GP, sus 
	 * menús aparecerán aquí).
	 *
	 * @since    1.0.0
	 */
	public function gp_openpay_admin_menu($data) {
		// add_menu_page( "GamePlanet", "GamePlanet", 'manage_options', 'gp-admin', '', 'dashicons-games', 100);
		add_submenu_page( 'gp-admin', 'Openpay', 'Openpay', 'manage_options', plugin_dir_path(__FILE__) . '/partials/gameplanet-openpay-admin-display.php');

	}

}
