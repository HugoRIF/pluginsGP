<?php
/**
* Plugin Name: Gameplanet Preventas
* Plugin URI: https://gamers.vg
* Description: Plugin de la seccion de Mi cuenta - Preventas
* Version: 1.0.0
* Author: Hugo Icelo
* Author URI: https://gameplanet.com
* License: GPL2
*/

if (!defined('ABSPATH')) exit;

define('T_PREVENTAS_NOMBRE','Plugin Preventas');
define('C_GP_PREVENTAS_ROUTE',plugin_dir_path(__FILE__));

include(C_GP_PREVENTAS_ROUTE . 'includes/functions.php');
include(C_GP_PREVENTAS_ROUTE . 'includes/shortcodes.php');
const AJAX_ACTION_PREVENTA_GET = 'ajax_gp_preventas_get';
const AJAX_ACTION_PREVENTA_SUCURSALES = 'ajax_sucursales_preventas';
const AJAX_ACTION_PREVENTA_ABONOS = 'ajax_gp_preventas_historial_abonos';
const AJAX_ACTION_PREVENTA_REASIGNAR = 'ajax_gp_preventas_reasignar_tienda';


add_action('init', 'f_gameplanet_preventas_shortcodes_init');

add_action( 'wp_ajax_'.AJAX_ACTION_PREVENTA_GET, 'ajax_gp_preventas_get'); // admin
add_action( 'wp_ajax_nopriv_'.AJAX_ACTION_PREVENTA_GET, 'ajax_gp_preventas_get'); // admin

add_action( 'wp_ajax_'.AJAX_ACTION_PREVENTA_ABONOS, 'ajax_gp_preventas_historial_abonos'); // admin
add_action( 'wp_ajax_nopriv_'.AJAX_ACTION_PREVENTA_ABONOS, 'ajax_gp_preventas_historial_abonos'); // admin

add_action( 'wp_ajax_'.AJAX_ACTION_PREVENTA_REASIGNAR, 'ajax_gp_preventas_reasignar_tienda'); // admin
add_action( 'wp_ajax_nopriv_'.AJAX_ACTION_PREVENTA_REASIGNAR, 'ajax_gp_preventas_reasignar_tienda'); // admin


add_action( 'wp_ajax_'.AJAX_ACTION_PREVENTA_SUCURSALES, 'ajax_sucursales_preventas'); // admin
add_action( 'wp_ajax_nopriv_'.AJAX_ACTION_PREVENTA_SUCURSALES, 'ajax_sucursales_preventas'); // admin
