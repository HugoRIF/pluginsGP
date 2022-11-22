<?php
/**
* Plugin Name: Gameplanet Intercambio
* Plugin URI: https://gamers.vg
* Description: Plugin de la seccion de Intercambio
* Version: 1.0.2
* Author: Hugo Icelo
* Author URI: https://gameplanet.com
* License: GPL2
*/

if (!defined('ABSPATH')) exit;

define('T_INTERCAMBIO_NOMBRE','Plugin Intercambio');
define('C_gameplanet_intercambio_ROUTE',plugin_dir_path(__FILE__));

include(C_gameplanet_intercambio_ROUTE . 'includes/functions.php');
include(C_gameplanet_intercambio_ROUTE . 'includes/shortcodes.php');
const AJAX_ACTION_INTERCAMBIO = 'ajax_intercambio_buscar';


add_action('init', 'f_gameplanet_intercambio_shortcodes_init');

add_action( 'wp_ajax_'.AJAX_ACTION_INTERCAMBIO, 'ajax_intercambio_buscar'); // admin
add_action( 'wp_ajax_nopriv_'.AJAX_ACTION_INTERCAMBIO, 'ajax_intercambio_buscar'); // admin
