<?php

/**
 * Se ejecuta cuando el plugin es desinstalado.
 *
 * Cuando escribas en este archivo, considera lo siguiente:
 *
 * - Este método debe ser estático
 * - Revisa si el contenido de $_REQUEST es el nombre del plugin
 * - Verifica que el contenido $_GET te haga sentido
 * - Repite con los otros roles de usuarios.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Gameplanet_Saldo
 */

// Si WordPress no lo llama, salgo.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}


	