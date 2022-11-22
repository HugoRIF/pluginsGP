<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.5.0
 * @flatsome-version 3.16.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wrapper_classes = array();
$row_classes     = array();
$main_classes    = array();
$sidebar_classes = array();

$layout = get_theme_mod( 'checkout_layout' );

if ( ! $layout ) {
	$sidebar_classes[] = 'has-border';
}

if ( $layout == 'simple' ) {
	$sidebar_classes[] = 'is-well';
}

$wrapper_classes = implode( ' ', $wrapper_classes );
$row_classes     = implode( ' ', $row_classes );
$main_classes    = implode( ' ', $main_classes );
$sidebar_classes = implode( ' ', $sidebar_classes );

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}

// Social login.
if ( flatsome_option( 'facebook_login_checkout' ) && get_option( 'woocommerce_enable_myaccount_registration' ) == 'yes' && ! is_user_logged_in() ) {
	wc_get_template( 'checkout/social-login.php' );
}
?>

<form name="checkout" method="post" class="checkout woocommerce-checkout <?php echo esc_attr( $wrapper_classes ); ?>" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

	<div class="row pt-0 <?php echo esc_attr( $row_classes ); ?>">
		<div class="large-7 col  <?php echo esc_attr( $main_classes ); ?>">
			<?php if ( $checkout->get_checkout_fields() ) : ?>

				<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

				<div id="customer_details">
					<?php
					$user_id = get_current_user_id();
					$customer = new WC_Customer( $user_id );

					$carrito = WC()->cart->get_cart();
					$tipo_envio = '';
					$subtipo_envio = '';
					foreach($carrito as $item => $value){
						if(isset($value['subtipo']) && $value['subtipo'] == 'preventa'){
							$subtipo_envio = 'preventa';
						}
						if(isset($value['tipo']) && $value['tipo'] == 'tienda'){
							$tipo_envio = 'tienda';

						} elseif(isset($value['tipo']) && $value['tipo'] == 'domicilio'){
							$tipo_envio = 'domicilio';
						}
					}

					$billing_first_name = $customer->get_billing_first_name();
					$billing_last_name = $customer->get_billing_last_name();
					$billing_phone = $customer->get_billing_phone();
					$billing_email = $customer->get_billing_email();

					$shipping_first_name = $customer->get_shipping_first_name();
					$shipping_last_name = $customer->get_shipping_last_name();
					$shipping_add = $customer->get_shipping_address_1();
					$shipping_city = $customer->get_shipping_city();
					$shipping_ps = $customer->get_shipping_postcode();
					$shipping_state = $customer->get_shipping_state();
					
					$num_ext = $customer->get_meta('_gp_exterior_number');
					$colonia = $customer->get_meta('_gp_suburb');

					$direccion_billing = site_url('/my-account/edit-address/facturacion/');
					$direccion_shipping = site_url('/my-account/edit-address/envio/');
					if(empty($billing_first_name) || empty($billing_last_name) || empty($billing_phone) || empty($billing_email)){?>
						<div class="gp_datos_cuenta">
							<p>
								Nos interesa mucho la seguridad de tu compra y que tu cuenta esté completamente segura, por esto mismo te solicitamos que los "DATOS DE TU CUENTA" estén completos, te pedimos nos proporciones tu <strong>nombre completo</strong>, <strong>email</strong> y un <strong>teléfono</strong>, donde te mandaremos un código de autenticación, con el cual podremos validar tu número de teléfono antes de completar tu compra.
								<br/>
								<a href="<?php echo $direccion_billing ?>">Actualizar "DATOS DE TU CUENTA"</a>
							</p>
						</div>
					<?php } elseif($tipo_envio == 'domicilio'){?>
						<?php if(empty($shipping_first_name) || empty($shipping_last_name) || empty($shipping_add) || empty($shipping_city) || empty($shipping_ps) || empty($shipping_state) || empty($num_ext) || empty($colonia)){?>
							<div class="gp_datos_cuenta">
								<p>
									Notamos que tus "DATOS DE ENVÍO" no están completos, te pedimos nos proporciones tu <strong>nombre completo</strong> y <strong>dirección</strong> a la cual quieras que te mandemos tus productos.
									<br/>
									<a href="<?php echo $direccion_shipping ?>">Actualizar "DATOS DE ENVÍO"</a>
								</p>
							</div>
						<?php } else{?>
							<?php 
							if(isset($_COOKIE['_gp_geo_pc']) && isset($_COOKIE['_gp_geo_pc'])){
								$cookie_pc = urldecode(filter_var($_COOKIE['_gp_geo_pc'], FILTER_SANITIZE_ENCODED));
								$cookie_direccion_larga = urldecode(filter_var($_COOKIE['_gp_geo_address_long'], FILTER_SANITIZE_ENCODED));
								$usr_direccion_larga = $shipping_add . ' #' . $num_ext . ', ' . $colonia . ', CP ' . $shipping_ps . ', ' . $shipping_city;
								if($shipping_ps != $cookie_pc){?>
									<div class="gp_datos_cuenta">
										<p>
											La <strong>disponibilidad</strong> y el <strong>costo de envío</strong> de los productos añadidos al carrito se calcularon con la dirección "<?php echo $cookie_direccion_larga; ?>", pero tus <strong>datos de envío</strong> son "<?php echo $usr_direccion_larga; ?>", verifica tu información antes de realizar el pedido.
										</p>
									</div>
								<?php } ?>
							<?php } ?>
							<?php if($subtipo_envio == 'preventa'){?>
								<div class="gp_detalles_apartado">
									<div style="padding: 20px;">
										<?php echo MENSAJE_PREVENTA_A; ?>
									</div>
								</div>
							<?php } ?>
						<?php } ?>
					<?php } elseif($tipo_envio == 'tienda'){?>
							<div class="gp_detalles_apartado">
								<div style="padding: 20px;">
								<?php
								if($subtipo_envio == 'preventa'){
									echo MENSAJE_PREVENTA_A;
								} else{
									echo MENSAJE_APARTADO_A;
								}
								?>
								</div>
							</div>
						<?php } ?>


					<div class="clear">
						<?php do_action( 'woocommerce_checkout_billing' ); ?>
					</div>
					<?php if($tipo_envio == 'domicilio'){?>
						<div class="clear">
							<?php do_action( 'woocommerce_checkout_shipping' ); ?>
						</div>
					<?php }?>
				</div>

				<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

			<?php endif; ?>

		</div>

		<div class="large-5 col">
			<?php flatsome_sticky_column_open( 'checkout_sticky_sidebar' ); ?>

					<div class="col-inner <?php echo esc_attr( $sidebar_classes ); ?>">
						<div class="checkout-sidebar sm-touch-scroll">

							<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>

							<h3 id="order_review_heading"><?php esc_html_e( 'Your order', 'woocommerce' ); ?></h3>

							<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

							<div id="order_review" class="woocommerce-checkout-review-order">
								<?php do_action( 'woocommerce_checkout_order_review' ); ?>
							</div>

							<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
						</div>
					</div>

			<?php flatsome_sticky_column_close( 'checkout_sticky_sidebar' ); ?>
		</div>

	</div>
</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
