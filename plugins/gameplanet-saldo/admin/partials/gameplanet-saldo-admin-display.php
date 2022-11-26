<?php

/**
 * Provee una vista del plugin en el área de admin
 *
 * Este archivo se usa para marcar el aspecto del área de admin del plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Gameplanet_Saldo
 * @subpackage Gameplanet_Saldo/admin/partials
 */

if (!defined('ABSPATH')) exit;

if (! current_user_can ('manage_options')) wp_die (__ ('No tienes suficientes permisos para acceder a esta página.'));
?>

<!-- Este archivo debe consistir primordialmente de HTML con un poco de PHP. -->
<div class="wrap">
	<h1><?php esc_html_e('Crédito Gameplanet'); ?></h1>
	<h3>Bienvenido a la configuración</h3>
</div>