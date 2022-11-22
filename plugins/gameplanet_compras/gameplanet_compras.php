<?php
/**
* Plugin Name: Gameplanet Historico Compras
* Plugin URI: https://gamers.vg
* Description: Plugin de la seccion de Mi cuenta - Mis compras, muesra el historico de compras de un cliente en especifico
* Version: 1.0.0
* Author: Hugo Icelo
* Author URI: https://gameplanet.com
* License: GPL2
*/

if (!defined('ABSPATH')) exit;

define('T_COMPRAS_NOMBRE','Plugin Compras');
define('C_GP_COMPRAS_ROUTE',plugin_dir_path(__FILE__));

include(C_GP_COMPRAS_ROUTE . 'includes/functions.php');
include(C_GP_COMPRAS_ROUTE . 'includes/shortcodes.php');
const AJAX_ACTION_COMPRA_GET = 'ajax_gp_compras_get';
const AJAX_ACTION_COMPRA_SEGUIMIENTO = 'ajax_compra_tracking';


add_action('init', 'f_gameplanet_compras_shortcodes_init');

add_action( 'wp_ajax_'.AJAX_ACTION_COMPRA_GET, 'ajax_gp_compras_get'); // admin
add_action( 'wp_ajax_nopriv_'.AJAX_ACTION_COMPRA_GET, 'ajax_gp_compras_get'); // admin

add_action( 'wp_ajax_'.AJAX_ACTION_COMPRA_SEGUIMIENTO, 'ajax_compra_tracking'); // admin
add_action( 'wp_ajax_nopriv_'.AJAX_ACTION_COMPRA_SEGUIMIENTO, 'ajax_compra_tracking'); // admin
