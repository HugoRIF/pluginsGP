<?php

/**
 * Plugin propio para utilizar openpay como meido de pago
 *
 * @link              http://example.com
 * @since             1.0.1
 * @package           Gameplanet_Openpay
 *
 * @wordpress-plugin
 * Plugin Name:       Gameplanet Openpay
 * Description:       Plugin que activa el método de pago con tarjeta de Openpay.
 * Version:           1.0.1
 * Author:            Hugo Icelo
 * Author URI:        http://www.gameplanet.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       gameplanet-openpay
 * Domain Path:       /languages
 */

// Si este archivo es llamado directamente, salgo.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Versión actual del plugin.
 * Usa SemVer - https://semver.org
 * -
 * versión MAYOR.MENOR.PARCHE
 * 
 * se incrementa:
 * la versión MAYOR cuando realizas un cambio incompatible en el plugin,
 * la versión MENOR cuando añades funcionalidad que es compatible con versiones anteriores, y
 * la versión PARCHE cuando reparas errores compatibles con versiones anteriores.
 * 
 * Actualizala cuando liberes nuevas versiones.
 */


$version = time();//eliminar en prod
define( 'GAMEPLANET_OPENPAY_VERSION', $version );
include(plugin_dir_path( __FILE__ ) . 'public/functions.php');

/**
 * El código que se ejecuta durante la activación del plugin.
 * Esta acción está documentada en includes/class-gameplanet-openpay-activator.php.
 */
function activate_gameplanet_openpay() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-gameplanet-openpay-activator.php';
	Gameplanet_Openpay_Activator::activate();
}

/**
 * El código que se ejecuta durante la desactivación del plugin.
 * Esta acción está documentada en includes/class-gameplanet-openpay-deactivator.php
 */
function deactivate_gameplanet_openpay() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-gameplanet-openpay-deactivator.php';
	Gameplanet_Openpay_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_gameplanet_openpay' );
register_deactivation_hook( __FILE__, 'deactivate_gameplanet_openpay' );

/**
 * El núcleo de la clase del plugin que es usado para definir internacionalización,
 * hooks específicos para admin y para público.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-gameplanet-openpay.php';

/**
 * Inicia la ejecución del plugin.
 *
 * Ya que todo dentro del plugin es registrado por hooks,
 * iniciar el plugin desde este punto no afectará el
 * ciclo de vida de la página.
 *
 * @since    1.0.0
 */
function run_gameplanet_openpay() {
	$plugin = new Gameplanet_Openpay();
	$plugin->run();
}

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    // WC está activado
} else {
    // WC no está activado
	add_action( 'admin_notices', function() {
		echo '
			<div class="error">
				<p>
					<strong>' . esc_html__('GamePlanet Openpay') . '</strong>' .
					sprintf( esc_html__( ' requiere que WooCommerce esté instalado y activado. Puedes descargar WooCommerce %s.' ),
					'<a href="https://woocommerce.com/" target="_blank">aquí</a>' ) .
				'</p>
			</div>
		';
	}
	);
	return false;
}
if ( in_array( 'gameplanet/gameplanet.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    // GP está activado
} else {
    // GP no está activado
	add_action( 'admin_notices', function() {
		echo '
			<div class="error">
				<p>
					<strong>' . esc_html__('GamePlanet Openpay' ) . '</strong>'.
					esc_html__(' requiere que el plugin principal de GamePlanet esté instalado y activado.' ) .
				'</p>
			</div>
		';
	}
	);
	return false;
}

/**
 * funciones ajax para el front
 * estan en la carpeta publica aunque no se si dejaralas ahi 
 */
const AJAX_ACTION_OPENPAY_GETCARD = 'gp_openpay_ajax_getCardType';

add_action( 'wp_ajax_'.AJAX_ACTION_OPENPAY_GETCARD, AJAX_ACTION_OPENPAY_GETCARD);
add_action( 'wp_ajax_nopriv_'.AJAX_ACTION_OPENPAY_GETCARD, AJAX_ACTION_OPENPAY_GETCARD);

run_gameplanet_openpay();
