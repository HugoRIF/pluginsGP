<?php

/**
 * Se lanza durante la activación del plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Gameplanet_Pagos_Fijos
 * @subpackage Gameplanet_Pagos_Fijos/includes
 */

/**
 * Se lanza durante la activación del plugin.
 *
 * Esta clase define todo el código necesario que se ejecutará durante la activación.
 *
 * @since      1.0.0
 * @package    Gameplanet_Pagos_Fijos
 * @subpackage Gameplanet_Pagos_Fijos/includes
 * @author     GamePlanet
 */
class Gameplanet_Pagos_Fijos_Activator {

	/**
	 * Se ejecuta al activar el plugin.
	 *
	 * validacion de version de PHP, instalado woocomerce y plugin principla de woocommerce
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		//* revisar la version de php
		if (version_compare(PHP_VERSION, '7.4', '<')) {
			deactivate_plugins(plugin_basename(__FILE__));
			wp_die('Por favor instale PHP 7.4 o mayor', 'Plugin dependency check', array('back_link' => true));
		}
		
		//* revisar si woocommerce esta instalado/activado
		if (!class_exists('WooCommerce')) {
			deactivate_plugins(plugin_basename(__FILE__));
			wp_die('Por favor instale y/o active WooCommerce.', 'Plugin dependency check', array('back_link' => true));
		}
				
		//* revisar si el plugin de GamePlanet está instalado/activado
		if (!class_exists('Gameplanet')) {
			deactivate_plugins(plugin_basename(__FILE__));
			wp_die('Por favor instale y/o active el plugin principal de GamePlanet primero.', 'Plugin dependency check', array('back_link' => true));
		}

	}

}
