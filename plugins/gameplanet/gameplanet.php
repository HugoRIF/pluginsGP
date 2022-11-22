<?php

/**
 * WordPress lee este archivo para generar la información en el área de plugin (admin).
 * Este archivo también contiene todas las dependencias que usa el plugin,
 * registra las funciones de activación/desactivación y define una función
 * que inicia el plugin.
 * 
 * Todos los hooks están documentados en includes/class-gameplanet.php
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           Gameplanet
 *
 * @wordpress-plugin
 * Plugin Name:       Gameplanet
 * Description:       Todo relacionado con los usuarios (login, creación, actualización y añadir campos id y token en admin)
 * Version:           1.0.1
 * Author:            Diego Noriega
 * Author URI:        http://www.gameplanet.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       gameplanet
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
define( 'GAMEPLANET_VERSION', '0.0.47' );

/**
 * El código que se ejecuta durante la activación del plugin.
 * Esta acción está documentada en includes/class-gameplanet-activator.php.
 */
function activate_gameplanet() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-gameplanet-activator.php';
	Gameplanet_Activator::activate();
}

/**
 * El código que se ejecuta durante la desactivación del plugin.
 * Esta acción está documentada en includes/class-gameplanet-deactivator.php
 */
function deactivate_gameplanet() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-gameplanet-deactivator.php';
	Gameplanet_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_gameplanet' );
register_deactivation_hook( __FILE__, 'deactivate_gameplanet' );

/**
 * El núcleo de la clase del plugin que es usado para definir internacionalización,
 * hooks específicos para admin y para público.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-gameplanet.php';

/**
 * Inicia la ejecución del plugin.
 *
 * Ya que todo dentro del plugin es registrado por hooks,
 * iniciar el plugin desde este punto no afectará el
 * ciclo de vida de la página.
 *
 * @since    1.0.0
 */
function run_gameplanet() {

	$plugin = new Gameplanet();
	$plugin->run();

}

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    // WC está activado
} else {
    // WC no está activado
	add_action( 'admin_notices', function() {
		echo '
			<div class="error">
				<p><strong>' .
					sprintf( esc_html__(
						'GamePlanet Widgets requiere que WooCommerce esté instalado y activado. Puedes descargar WooCommerce %s.' ),
					'<a href="https://woocommerce.com/" target="_blank">aquí</a>' ) .
				'</strong></p>
			</div>
		';
	}
	);
	return false;
}


$opciones = [
	'ruta_gameplanet',
	'data-jwt-master',
	'ruta_telefonero',
	'data-telefonero'
];
global $pagenow;
// paginas a mostrar mensaje
$admin_pages = ['index.php', 'edit.php', 'plugins.php'];
if (in_array($pagenow, $admin_pages)) {
	foreach ($opciones as $opcion) {
		if (get_option($opcion) == '') {
			add_action( 'admin_notices', function() {
				echo '
					<div class="error">
						<p>
							<strong>' . esc_html__('¡OYE!') . '</strong>' .
							sprintf( esc_html__(' No olvides configurar el plugin de GamePlanet %s.'),'<a href="' . site_url() . '/wp-admin/admin.php?page=gameplanet/admin/partials/gameplanet-admin-display.php">aquí</a>' ) .
						'</p>
					</div>
				';
			});
			break;
		}
	}
}

run_gameplanet();
