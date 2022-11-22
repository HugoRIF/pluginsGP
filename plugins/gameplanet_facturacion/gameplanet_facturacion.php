<?php
/**
* Plugin Name: Gameplanet Facturacion
* Plugin URI: https://gamers.vg
* Description: Plugin Para enviar Factura de compra
* Version: 1.0.1
* Author: Hugo Icelo
* Author URI: https://gameplanet.com
* License: GPL2
*/

if (!defined('ABSPATH')) exit;

define('T_FACTURACION_NOMBRE','Plugin Facturacion');
define('C_gameplanet_facturacion_ROUTE',plugin_dir_path(__FILE__));

include(C_gameplanet_facturacion_ROUTE . 'includes/functions.php');
include(C_gameplanet_facturacion_ROUTE . 'includes/shortcodes.php');
const AJAX_FACTURACION = 'ajax_facturacion_generate';

add_action('init', 'f_gameplanet_facturacion_shortcodes_init');
add_action( 'wp_ajax_'.AJAX_FACTURACION, 'ajax_facturacion_generate'); // admin
add_action( 'wp_ajax_nopriv_'.AJAX_FACTURACION, 'ajax_facturacion_generate'); // admin
       

//! cargar css/js para "admin" (funcion en root para evitar mover url en el futuro)
add_action( 'wp_enqueue_scripts', 'f_gameplanet_facturacion_assets' );
function f_gameplanet_facturacion_assets() {
    $time = time();
    wp_enqueue_style( 'facturacion-success-styles', plugins_url( '/public/css/success_section.css' , __FILE__ ),array(),$time );
    wp_enqueue_style( 'plugin_general-styles', plugins_url( '/public/css/plugin_general.css' , __FILE__ ),array(),$time);
    wp_enqueue_script( 'plugin_general-scripts', plugins_url( '/public/js/plugin_general.js' , __FILE__ ), array( 'jquery' ),$time);
    
}
