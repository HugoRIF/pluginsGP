<?php
/**
* Plugin Name: Gameplanet Cash
* Plugin URI: https://gamers.vg
* Description: Plugin de la seccion de Cash
* Version: 1.0.1
* Author: Hugo Icelo
* Author URI: https://gameplanet.com
* License: GPL2
*/

if (!defined('ABSPATH')) exit;

define('T_GP_CASH_NOMBRE','Plugin GP Cash');
define('C_GAMEPLANET_CASH_ROUTE',plugin_dir_path(__FILE__));

include(C_GAMEPLANET_CASH_ROUTE . 'includes/functions.php');
include(C_GAMEPLANET_CASH_ROUTE . 'includes/shortcodes.php');
const AJAX_ACTION_CASH = 'ajax_cash_crear_orden';


add_action( 'wp_ajax_'.AJAX_ACTION_CASH, 'ajax_cash_crear_orden'); // admin
add_action( 'wp_ajax_nopriv_'.AJAX_ACTION_CASH, 'ajax_cash_crear_orden'); // admin
       
add_action('init', 'f_gameplanet_cash_shortcodes_init');

