<?php
/**
* Plugin Name: Gameplanet OTP
* Plugin URI: https://gamers.vg
* Description: Plugin opara verificacion SMS
* Version: 1.0.1
* Author: Hugo Icelo
* Author URI: https://gameplanet.com
* License: GPL2
*/

if (!defined('ABSPATH')) exit;



define('T_GP_OTP_NOMBRE','Plugin GP OTP');
define('C_GAMEPLANET_OTP_ROUTE',plugin_dir_path(__FILE__));

include(C_GAMEPLANET_OTP_ROUTE . 'includes/functions.php');
include(C_GAMEPLANET_OTP_ROUTE . 'includes/shortcodes.php');
include(C_GAMEPLANET_OTP_ROUTE . 'includes/gp_otp_control_model.php');

register_activation_hook(__FILE__,function (){
  gp_otp_add_table_control_user();
  update_option( 'gp_otp-active',1 );
  update_option( 'gp_otp-time_resend_soft',60 );
  update_option( 'gp_otp-time_resend_medium',180 );
  update_option( 'gp_otp-time_resend_hard',3600 );
  update_option( 'gp_otp-time_resend_hard_ip',7200 );
  update_option( 'gp_otp-max_attends_user_phone',3 );
  update_option( 'gp_otp-max_ip_attends_user',5 );
  update_option( 'gp_otp-max_ip_attends',17 );
  update_option( 'gp_otp-max_gateway_errors',5 );
});

//funciones para la seccion se address_billing (actulizar telefono de usuarios logeados)
const AJAX_ACTION_OTP_SEND_BILLING = 'ajax_otp_send_billing';
const AJAX_ACTION_OTP_VERIFY_BILLING = 'ajax_otp_verify_billing';

add_action( 'wp_ajax_'.AJAX_ACTION_OTP_SEND_BILLING, AJAX_ACTION_OTP_SEND_BILLING); // admin
add_action( 'wp_ajax_nopriv_'.AJAX_ACTION_OTP_SEND_BILLING, AJAX_ACTION_OTP_SEND_BILLING); // admin
add_action( 'wp_ajax_'.AJAX_ACTION_OTP_VERIFY_BILLING, AJAX_ACTION_OTP_VERIFY_BILLING); // admin
add_action( 'wp_ajax_nopriv_'.AJAX_ACTION_OTP_VERIFY_BILLING, AJAX_ACTION_OTP_VERIFY_BILLING); // admin
        
//funciones para la seccion de sigin (Registro de usuario)
const AJAX_ACTION_OTP_SEND_SIGN_IN = 'ajax_otp_send_sigin';
const AJAX_ACTION_OTP_VERIFY_SIGN_IN = 'ajax_otp_verify_sigin';

add_action( 'wp_ajax_'.AJAX_ACTION_OTP_SEND_SIGN_IN, AJAX_ACTION_OTP_SEND_SIGN_IN); // admin
add_action( 'wp_ajax_nopriv_'.AJAX_ACTION_OTP_SEND_SIGN_IN, AJAX_ACTION_OTP_SEND_SIGN_IN); // admin
add_action( 'wp_ajax_'.AJAX_ACTION_OTP_VERIFY_SIGN_IN, AJAX_ACTION_OTP_VERIFY_SIGN_IN); // admin
add_action( 'wp_ajax_nopriv_'.AJAX_ACTION_OTP_VERIFY_SIGN_IN, AJAX_ACTION_OTP_VERIFY_SIGN_IN); // admin



 
add_action('init', 'f_gameplanet_otp_shortcodes_init');
add_action( 'woocommerce_registration_errors', 'gp_otp_validate_registration_fields', 11, 3);
add_action( 'woocommerce_created_customer', 'gp_otp_save_phone_reg', 11, 3);
add_action('woocommerce_register_form', function (){

  echo do_shortcode("[gp_otp_register]");
}, 10,0);

add_action( 'admin_menu', function (){
  $model = new GP_OTP_CONTROL_MODEL();
  $count = $model->countErrors();
  $alerts = ' <span class="awaiting-mod">'.$count.'</span>';
  if(!$count){
    //solo se pinta la opcion
    add_submenu_page('gp-admin', 'GP OTP', 'OTP ', 'manage_options', plugin_dir_path(__FILE__) . '/includes/admin.php');
   return;
  }
  //pintamos el submenu
   //alerta en la seccion principal de gameplanet
   global $menu;
   $menu_item = wp_list_filter(
     $menu,
     array( 0 => 'GamePlanet' ) // 2 is the position of an array item which contains URL, it will always be 2!
   );
 
   if ( ! empty( $menu_item )  ) {
     $menu_item_position = key( $menu_item ); // get the array key (position) of the element
     $menu[ $menu_item_position ][0] .= $alerts;
   }
   add_submenu_page('gp-admin', 'GP OTP', 'OTP '.$alerts, 'manage_options', plugin_dir_path(__FILE__) . '/includes/admin.php');
   return;

}, 11);
