<?php

/**
 * Define la funcionalidad de internacionalización
 *
 * Carga y define los archivos de internacionalización para este plugin
 * para que esté listo para traducir.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Gameplanet_Saldo
 * @subpackage Gameplanet_Saldo/includes
 */

/**
 * Define la funcionalidad de internacionalización.
 *
 * Carga y define los archivos de internacionalización para este plugin
 * para que esté listo para traducir.
 *
 * @since      1.0.0
 * @package    Gameplanet_Saldo
 * @subpackage Gameplanet_Saldo/includes
 * @author     GamePlanet
 */
class Gameplanet_Saldo_i18n {


	/**
	 * Carga el dominio del plugin para traducir.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'gameplanet-saldo',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}

}
