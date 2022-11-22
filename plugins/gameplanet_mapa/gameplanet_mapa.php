<?php
/**
* Plugin Name: Gameplanet Mapa Sucursales
* Plugin URI: https://gamers.vg
* Description: Plugin de ejemplo
* Version: 1.5
* Author: Francisco Neri
* Author URI: https://gameplanet.com
* License: GPL2
*/

if (!defined('ABSPATH')) exit;

define('T_GAMEPLANET_MAPA','Gameplanet Mapa');
define('C_GAMEPLANET_MAPA_ROUTE',plugin_dir_path(__FILE__));

include(C_GAMEPLANET_MAPA_ROUTE . 'includes/functions.php');
include(C_GAMEPLANET_MAPA_ROUTE . 'includes/shortcodes.php');
const AJAX_GET_SUCURSALES = 'ajax_mapa_get_sucursales';

// function shortcodes_init(){
//     add_shortcode( 'gameplanet_list_phones', 'gameplanet_list_phones_function' );
// }
// add_action('init', 'shortcodes_init', 1);
add_action('init', 'f_gameplanet_mapa_shortcodes_init');


add_action( 'wp_ajax_'.AJAX_GET_SUCURSALES, 'ajax_mapa_get_sucursales'); // admin
add_action( 'wp_ajax_nopriv_'.AJAX_GET_SUCURSALES, 'ajax_mapa_get_sucursales'); // admin

