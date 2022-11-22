<?php

/**
 * Account element.
 *
 * @package          Flatsome\Templates
 * @flatsome-version 3.16.0
 */
$icon_style = get_theme_mod('account_icon_style');
?>
<li id="gp_mi_cuenta_no_sesion" class="account-item has-icon">
  <a href="#" id="link_login" class="nav-top-link nav-top-not-logged-in is-small" data-open="#login-form-popup">
    <span>Ingreso/Registro</span>
  </a>
</li>
<li id="gp_mi_cuenta_sesion" class="account-item has-icon has-dropdown" style="display:none">
    <a href="<?php echo get_permalink(get_option('woocommerce_myaccount_page_id')); ?>" class="account-link account-login
        <?php if ($icon_style && $icon_style !== 'image') echo get_flatsome_icon_class($icon_style, 'small'); ?>" title="<?php _e('My account', 'woocommerce'); ?>">
      <span class="header-account-title" id="mi_cuenta_user_name"></span>

      <?php if ($icon_style == 'image') {
        //echo '<i class="image-icon circle">' . get_avatar(get_current_user_id()) . '</i>';
      } else  if ($icon_style) {
        echo get_flatsome_icon('icon-user');
      } ?>
    </a>
    <ul class="nav-dropdown  <?php flatsome_dropdown_classes(); ?>">
      <?php wc_get_template('myaccount/account-links.php'); ?>
    </ul>
  </li>