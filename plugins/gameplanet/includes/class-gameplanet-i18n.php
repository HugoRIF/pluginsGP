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
 * @package    Gameplanet
 * @subpackage Gameplanet/includes
 */

/**
 * Define la funcionalidad de internacionalización.
 *
 * Carga y define los archivos de internacionalización para este plugin
 * para que esté listo para traducir.
 *
 * @since      1.0.0
 * @package    Gameplanet
 * @subpackage Gameplanet/includes
 * @author     GamePlanet
 */
class Gameplanet_i18n {


	/**
	 * Carga el dominio del plugin para traducir.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'gameplanet',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}

}
