<?php

/**
 * Account element.
 *
 * @package          Flatsome\Templates
 * @flatsome-version 3.16.0
 */
$icon_style = get_theme_mod('account_icon_style');
?>
<li id="gp_mi_cuenta_no_sesion_mob" class="account-item has-icon">
  <a href="#" class="nav-top-link nav-top-not-logged-in is-small" data-open="#login-form-popup">
    <span>Ingreso/Registro</span>
  </a>
</li>
<li id="gp_mi_cuenta_sesion_mob" class="account-item has-icon" style="display:none">
    <a href="<?php echo get_permalink(get_option('woocommerce_myaccount_page_id')); ?>" class="account-link-mobile
        <?php if ($icon_style && $icon_style !== 'image') echo get_flatsome_icon_class($icon_style, 'small'); ?>" title="<?php _e('My account', 'woocommerce'); ?>">
      <span  id="mi_cuenta_user_name_mob">Mi Cuenta</span>

      <?php echo get_flatsome_icon('icon-user');?> 
    </a>
    
  </li>