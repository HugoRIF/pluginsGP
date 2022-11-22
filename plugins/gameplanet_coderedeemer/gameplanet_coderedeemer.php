<?php
/**
* Plugin Name: Gameplanet CodeRedeemer
* Plugin URI: https://gamers.vg
* Description: Plugin de la seccion de Code Redemer
* Version: 1.0.0
* Author: Hugo Icelo
* Author URI: https://gameplanet.com
* License: GPL2
*/

if (!defined('ABSPATH')) exit;

define('T_CODEREDEEMER_NOMBRE','Plugin Coderedeemer');
define('C_gameplanet_coderedeemer_ROUTE',plugin_dir_path(__FILE__));

include(C_gameplanet_coderedeemer_ROUTE . 'includes/functions.php');
include(C_gameplanet_coderedeemer_ROUTE . 'includes/shortcodes.php');
const AJAX_ACTION_TEST = 'ajax_crd_send_ticket';
// function shortcodes_init(){
//     add_shortcode( 'gameplanet_list_phones', 'gameplanet_list_phones_function' );
// }
// add_action('init', 'shortcodes_init', 1);

add_action('init', 'f_gameplanet_coderedeemer_shortcodes_init');

add_action( 'wp_ajax_'.AJAX_ACTION_TEST, 'ajax_crd_send_ticket'); // admin
add_action( 'wp_ajax_nopriv_'.AJAX_ACTION_TEST, 'ajax_crd_send_ticket'); // admin
       
