<?php

/**
 * Se lanza durante la activación del plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Gameplanet
 * @subpackage Gameplanet/includes
 */

/**
 * Se lanza durante la activación del plugin.
 *
 * Esta clase define todo el código necesario que se ejecutará durante la activación.
 *
 * @since      1.0.0
 * @package    Gameplanet
 * @subpackage Gameplanet/includes
 * @author     GamePlanet
 */
class Gameplanet_Activator {

	/**
	 * Se ejecuta al activar el plugin.
	 *
	 * Verifica la versión instalada de PHP.
	 * 
	 * Verifica que WooCommerce esté instalado y activado.
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

		//* inicialización de opciones
		$opciones = [
			'ruta_gameplanet',
			'data-jwt-master',
			'ruta_telefonero',
			'data-telefonero'
		];
		foreach ($opciones as $opcion) {
			// "add_option" no sobreescribe los valores, no hace nada si se activa el plugin una segunda vez
			add_option($opcion);
		}
		
		error_log("¡ gameplanet activado !");

	}

}
