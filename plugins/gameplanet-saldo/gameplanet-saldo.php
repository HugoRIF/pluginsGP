<?php

/**
 * WordPress lee este archivo para generar la información en el área de plugin (admin).
 * Este archivo también contiene todas las dependencias que usa el plugin,
 * registra las funciones de activación/desactivación y define una función
 * que inicia el plugin.
 * 
 * Todos los hooks están documentados en includes/class-gameplanet-saldo.php
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           Gameplanet_Saldo
 *
 * @wordpress-plugin
 * Plugin Name:       Gameplanet Saldo
 * Description:       Plugin que activa el método de pago "Salgo GamePlanet".
 * Version:           1.0.1
 * Author:            Diego Noriega
 * Author URI:        http://www.gameplanet.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       gameplanet-saldo
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
define( 'GAMEPLANET_SALDO_VERSION', '0.0.14' );

/**
 * El código que se ejecuta durante la activación del plugin.
 * Esta acción está documentada en includes/class-gameplanet-saldo-activator.php.
 */
function activate_gameplanet_saldo() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-gameplanet-saldo-activator.php';
	Gameplanet_Saldo_Activator::activate();
}

/**
 * El código que se ejecuta durante la desactivación del plugin.
 * Esta acción está documentada en includes/class-gameplanet-saldo-deactivator.php
 */
function deactivate_gameplanet_saldo() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-gameplanet-saldo-deactivator.php';
	Gameplanet_Saldo_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_gameplanet_saldo' );
register_deactivation_hook( __FILE__, 'deactivate_gameplanet_saldo' );

/**
 * El núcleo de la clase del plugin que es usado para definir internacionalización,
 * hooks específicos para admin y para público.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-gameplanet-saldo.php';

/**
 * Inicia la ejecución del plugin.
 *
 * Ya que todo dentro del plugin es registrado por hooks,
 * iniciar el plugin desde este punto no afectará el
 * ciclo de vida de la página.
 *
 * @since    1.0.0
 */
function run_gameplanet_saldo() {

	$plugin = new Gameplanet_Saldo();
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
					<strong>' . esc_html__('GamePlanet Saldo') . '</strong>' .
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
					<strong>' . esc_html__('GamePlanet Saldo' ) . '</strong>'.
					esc_html__(' requiere que el plugin principal de GamePlanet esté instalado y activado.' ) .
				'</p>
			</div>
		';
	}
	);
	return false;
}

run_gameplanet_saldo();
