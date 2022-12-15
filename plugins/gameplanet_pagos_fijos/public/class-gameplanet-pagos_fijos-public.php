<?php

/**
 * La funcionalidad del área pública del plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Gameplanet_Pagos_Fijos
 * @subpackage Gameplanet_Pagos_Fijos/public
 */

/**
 * La funcionalidad del área pública del plugin.
 *
 * Define el nombre del plugin, versión y hooks para el área pública.
 *
 * @package    Gameplanet_Pagos_Fijos
 * @subpackage Gameplanet_Pagos_Fijos/public
 * @author     GamePlanet
 */
class Gameplanet_Pagos_Fijos_Public {

	/**
	 * El ID del plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $gameplanet_pagos_fijos    El ID del plugin.
	 */
	private $gameplanet_pagos_fijos;

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
	 * @param      string    $gameplanet_pagos_fijos       Nombre del plugin.
	 * @param      string    $version    La versión del plugin.
	 */
	public function __construct( $gameplanet_pagos_fijos, $version ) {

		$this->gameplanet_pagos_fijos = $gameplanet_pagos_fijos;
		$this->version = $version;

	}

	/**
	 * Registra el CSS para el área pública.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->gameplanet_pagos_fijos, plugin_dir_url( __FILE__ ) . 'css/gameplanet-pagos_fijos-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Registra el JavaScript para el área pública.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		$total       = WC()->cart->get_total('');
		$msi         = get_option('gp_pagos_fijos_msi', null);
	
		$msi_disponibles = is_array($msi) && !empty($msi) ; //si es un array con algo dentro es que si hay meses
		$is_sandbox = get_option('gp_pagos_fijos_sandbox') == 'no'?false:true;
		$key_prefix = $is_sandbox?'test':'live';//que claves su usaran

		wp_enqueue_script( $this->gameplanet_pagos_fijos, plugin_dir_url( __FILE__ ) . 'js/gameplanet-pagos_fijos-public.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->gameplanet_pagos_fijos.'_openpayjs', 'https://js.openpay.mx/openpay.v1.min.js', array('jquery'), false);
		wp_enqueue_script( $this->gameplanet_pagos_fijos.'_openpayjs_service', 'https://resources.openpay.mx/lib/openpay-data-js/1.2.38/openpay-data.v1.min.js', array('jquery'), false);
		wp_localize_script($this->gameplanet_pagos_fijos, 'gp_pagos_fijos_ajax_param', array(
			'ajaxurl'        => admin_url('admin-ajax.php'),
			'action_getcard' => AJAX_ACTION_PAGOS_FIJOS_GETCARD,
			'action_af_log' => AJAX_ACTION_GP_PAGOS_FIJOS_AF_WEBHOOK,
		));
		wp_localize_script($this->gameplanet_pagos_fijos, 'gp_pagos_fijos', array(
			'id'             => get_option('gp_pagos_fijos_'.$key_prefix.'_merchant_id'),
			'key'             => get_option('gp_pagos_fijos_'.$key_prefix.'_publishable_key'),
			'cart_total'      => $total,
			'msi_disponibles' => $msi_disponibles,
			'is_sandbox'      => $is_sandbox,
			'current_user_id' => get_user_meta(get_current_user_id(), 'id_gp', true)
		));
	}


	public function gp_pagos_fijos_init(){
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-gameplanet-pagos_fijos-gateway.php';
			Gp_Openpay_Gateway::class;
	}

		
}
