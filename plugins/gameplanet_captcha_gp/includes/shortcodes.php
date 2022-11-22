<?php

if (!defined('ABSPATH')) exit;


function f_plugin_gameplanet_captcha_shortcodes_init(){
	//! shortcode para la facturacion de cliente
    add_shortcode( 'captcha_test', 'test' );
}

function test(){
  
    wp_enqueue_style( 'gameplanet_captcha-styles', plugins_url( '../public/css/gameplanet_captcha.css' , __FILE__ ) );
    wp_enqueue_script( 'gameplanet_captcha-scripts', plugins_url( '../public/js/gameplanet_captcha.js' , __FILE__ ), array( 'jquery' ));
  
}
