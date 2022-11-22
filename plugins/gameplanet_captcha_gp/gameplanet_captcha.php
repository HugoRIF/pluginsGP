<?php
/**
* Plugin Name: Gameplanet Captcha
* Plugin URI: https://gamers.vg
* Description: Plugin para incluir captchas de Google en cualquier formulario
* Version: 1.0.0
* Author: Hugo Icelo
* Author URI: https://gameplanet.com
* License: GPL2
*/

if (!defined('ABSPATH')) exit;

define('T_GAMEPLANET_CAPTCHA','Plugin Captcha GP');
define('C_GAMEPLANET_CAPTCHA',plugin_dir_path(__FILE__));

include(C_GAMEPLANET_CAPTCHA . 'includes/functions.php');
include(C_GAMEPLANET_CAPTCHA . 'includes/shortcodes.php');

add_action('init', 'f_plugin_gameplanet_captcha_shortcodes_init');

