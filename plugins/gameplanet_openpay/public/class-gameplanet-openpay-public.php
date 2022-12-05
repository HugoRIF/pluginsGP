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
		$total       = WC()->cart->total;
		$msi         = get_option('gp_openpay_msi', null);
		$min_msi     = (float) get_option('gp_openpay_minimum_amount_interest_free', 1);
	
		$msi_disponibles = is_array($msi) && !empty($msi) && (float)$total>=$min_msi; //si es un array con algo dentro es que si hay meses
		
		$is_sandbox = get_option('gp_openpay_sandbox') == 'no'?false:true;
		$key_prefix = $is_sandbox?'test':'live';//que claves su usaran

		wp_enqueue_script( $this->gameplanet_openpay, plugin_dir_url( __FILE__ ) . 'js/gameplanet-openpay-public.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->gameplanet_openpay.'_openpayjs', 'https://js.openpay.mx/openpay.v1.min.js', array('jquery'), false);
		wp_enqueue_script( $this->gameplanet_openpay.'_openpayjs_service', 'https://resources.openpay.mx/lib/openpay-data-js/1.2.38/openpay-data.v1.min.js', array('jquery'), false);
		wp_localize_script($this->gameplanet_openpay, 'gp_openpay_ajax_param', array(
			'ajaxurl'        => admin_url('admin-ajax.php'),
			'action_getcard' => AJAX_ACTION_OPENPAY_GETCARD,
		));
		wp_localize_script($this->gameplanet_openpay, 'gp_openpay', array(
			'id'             => get_option('gp_openpay_'.$key_prefix.'_merchant_id'),
			'key'             => get_option('gp_openpay_'.$key_prefix.'_publishable_key'),
			'cart_total'      => floatval($total),
			'msi_disponibles' => $msi_disponibles,
			'is_sandbox'      => $is_sandbox,
		));
	}


	public function gp_openpay_init(){
		if( current_user_can( 'manage_options' )){
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-gameplanet-openpay-gateway.php';
			Gp_Openpay_Gateway::class;
		}
		
	}

		
}
