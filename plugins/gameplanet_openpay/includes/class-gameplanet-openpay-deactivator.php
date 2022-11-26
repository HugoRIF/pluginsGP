<?php

/**
 * Se ejecuta durante la desactivación del plugin
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Gameplanet_Openpay
 * @subpackage Gameplanet_Openpay/includes
 */

/**
 * Se ejecuta durante la desactivación del plugin.
 *
 * Esta clase define todo el código necesario que se ejecutará durante la desactivación.
 *
 * @since      1.0.0
 * @package    Gameplanet_Openpay
 * @subpackage Gameplanet_Openpay/includes
 * @author     GamePlanet
 */
class Gameplanet_Openpay_Deactivator {

	/**
	 * Se ejecuta al desactivar el plugin.
	 *
	 * Descripción larga.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		error_log("¡ gameplanet-Openpay desactivado !");

	}

}
