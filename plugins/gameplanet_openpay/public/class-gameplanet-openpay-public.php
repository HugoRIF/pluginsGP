<?php

/**
 * La funcionalidad del área pública del plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Gameplanet_Openpay
 * @subpackage Gameplanet_Openpay/public
 */

/**
 * La funcionalidad del área pública del plugin.
 *
 * Define el nombre del plugin, versión y hooks para el área pública.
 *
 * @package    Gameplanet_Openpay
 * @subpackage Gameplanet_Openpay/public
 * @author     GamePlanet
 */
class Gameplanet_Openpay_Public {

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
	 * Inisializa la clase y define sus propiedades.
	 *
	 * @since    1.0.0
	 * @param      string    $gameplanet_openpay       Nombre del plugin.
	 * @param      string    $version    La versión del plugin.
	 */
	public function __construct( $gameplanet_openpay, $version ) {

		$this->gameplanet_openpay = $gameplanet_openpay;
		$this->version = $version;

	}

	/**
	 * Registra el CSS para el área pública.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->gameplanet_openpay, plugin_dir_url( __FILE__ ) . 'css/gameplanet-openpay-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Registra el JavaScript para el área pública.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->gameplanet_openpay, plugin_dir_url( __FILE__ ) . 'js/gameplanet-openpay-public.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->gameplanet_openpay.'_openpayjs', 'https://js.openpay.mx/openpay.v1.min.js', array('jquery'), false);
		wp_enqueue_script( $this->gameplanet_openpay.'_openpayjs_service', 'https://resources.openpay.mx/lib/openpay-data-js/1.2.38/openpay-data.v1.min.js', array('jquery'), false);
		wp_localize_script($this->gameplanet_openpay, 'gp_openpay_ajax_param', array(
			'ajaxurl'   => admin_url('admin-ajax.php'),
			'action_getcard'    => AJAX_ACTION_OPENPAY_GETCARD,
		));
		wp_localize_script($this->gameplanet_openpay, 'gp_openpay', array(
			'id'   => get_option('gp_openpay_live_merchant_id'),
			'key'    => get_option('gp_openpay_live_publishable_key'),
		));
	}


	public function gp_openpay_init(){
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-gameplanet-openpay-gateway.php';
		Gp_Openpay_Gateway::class;
	}

		
}
