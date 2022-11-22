<?php
/**
 * Checkout Payment Section
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/payment.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.3
 */

defined( 'ABSPATH' ) || exit;

if ( ! wp_doing_ajax() ) {
	do_action( 'woocommerce_review_order_before_payment' );
}
?>
<div id="payment" class="woocommerce-checkout-payment">
	<?php if ( WC()->cart->needs_payment() ) : ?>
		<ul class="wc_payment_methods payment_methods methods">
			<?php
			if ( ! empty( $available_gateways ) ) {
				foreach ( $available_gateways as $gateway ) {
					wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
				}
			} else {
				echo '<li class="woocommerce-notice woocommerce-notice--info woocommerce-info">' . apply_filters( 'woocommerce_no_available_payment_methods_message', WC()->customer->get_billing_country() ? esc_html__( 'Sorry, it seems that there are no available payment methods for your state. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' ) : esc_html__( 'Please fill in your details above to see available payment methods.', 'woocommerce' ) ) . '</li>'; // @codingStandardsIgnoreLine
			}
			//! ------
            if(is_user_logged_in()){
				$user = get_userdata(get_current_user_id());
				if ((isset($user->token) && isset($user->id_gp)) && $user->token != '' && $user->id_gp != '') {
					$saldo_usuario = $user->saldo_gp;
					$total = WC()->cart->get_total("");
					$carrito = WC()->cart->get_cart();
					
					$tipo_envio = '';
					foreach($carrito as $item => $value){
						if(isset($value['tipo']) && $value['tipo'] == 'tienda'){
							$tipo_envio = 'tienda';
						}
					}
	
					if($tipo_envio != 'tienda'){
						if($total > $saldo_usuario){
							echo "
									<li class='disabled op-5'>
										<input type='radio'>
										<label>
											Crédito Gameplanet | Tu saldo es: $" . $saldo_usuario . "
										</label>
									</li>
								<br/>
							";
						}
		
						echo "<br/><p style='font-size:20px'><strong>¿Necesitas más crédito Gameplanet?</strong><br/><span style='font-size:16px'>Haz click <a class='gp_underline' target='_blank' rel='noopener noreferrer' href='https://gameplanet.com/cash'>aquí</a> para realizar un abono.</span></p>";
					}
				}

            } else{
				echo "
                        <li class='disabled op-5'>
							<input type='radio'>
                            <label>
                                Si quieres pagar utilizando tu crédito Gameplanet, inicia sesión.
                            </label>
                        </li>
                    <br/>
                ";
			}
			//! ------
			?>
		</ul>
	<?php endif; ?>
	<div class="form-row place-order">
		<noscript>
			<?php
			/* translators: $1 and $2 opening and closing emphasis tags respectively */
			printf( esc_html__( 'Since your browser does not support JavaScript, or it is disabled, please ensure you click the %1$sUpdate Totals%2$s button before placing your order. You may be charged more than the amount stated above if you fail to do so.', 'woocommerce' ), '<em>', '</em>' );
			?>
			<br/><button type="submit" class="button alt" name="woocommerce_checkout_update_totals" value="<?php esc_attr_e( 'Update totals', 'woocommerce' ); ?>"><?php esc_html_e( 'Update totals', 'woocommerce' ); ?></button>
		</noscript>

		<?php wc_get_template( 'checkout/terms.php' ); ?>

		<?php do_action( 'woocommerce_review_order_before_submit' ); ?>

		<?php echo apply_filters( 'woocommerce_order_button_html', '<button type="submit" class="button alt" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">' . esc_html( $order_button_text ) . '</button>' ); // @codingStandardsIgnoreLine ?>

		<?php do_action( 'woocommerce_review_order_after_submit' ); ?>

		<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
	</div>
</div>
<?php
if ( ! wp_doing_ajax() ) {
	do_action( 'woocommerce_review_order_after_payment' );
}
