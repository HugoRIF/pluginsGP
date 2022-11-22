<?php

/**
 * View Order
 *
 * Shows the details of a particular order on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/view-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.0.0
 */

defined('ABSPATH') || exit;

$notes = $order->get_customer_order_notes();

$gp_status = 'gp_status default';
$tipo_envio = $order->get_meta('_gp_tipo_envio');


if (empty($tipo_envio)) {
	$tipo_envio = $order->get_meta('_ps_tipo_envio');
}
$transacciones = [
	"apartado" => "Apartado",
	"domicilio" => "Envio a Domicilio",
	"credito" => "Credito Gameplanet",
	"tienda" => "Preventa",
];
$transaccion = "";
if (empty($tipo_envio)) {
	$tipo_envio = $order->get_meta('_ps_tipo_envio');
}
switch ($tipo_envio) {
	case 'apartado':
		$transaccion = $transacciones[$tipo_envio];
		switch ($order->get_meta('_gp_estatus_apartado')) {
			case 'A': {
					$gp_status = 'gp_status gp_status_naranja';
					break;
				}
			case 'B':
			case 'E': {
					$gp_status = 'gp_status gp_status_verde';
					break;
				}
			case 'C':
			case 'D': {
					$gp_status = 'gp_status gp_status_rojo';
					break;
				}
			default: {
					$gp_status = 'gp_status';
					break;
				}
		}
		break;
	case 'domicilio':
		$transaccion = $transacciones[$tipo_envio];
		switch ($order->get_meta('_gp_estatus_domicilio')) {
			case 'A': {
					$gp_status = 'gp_status gp_status_naranja';
					break;
				}
			case 'B':
			case 'D': {
					$gp_status = 'gp_status gp_status_verde';
					break;
				}
			case 'C': {
					$gp_status = 'gp_status gp_status_rojo';
					break;
				}
			default: {
					$gp_status = 'gp_status';
					break;
				}
		}
		break;
	case 'tienda': //preventa
		$transaccion = $transacciones[$tipo_envio];
		switch ($order->get_meta('_gp_estatus_preventa')) {
			case 'A': {
					$gp_status = 'gp_status gp_status_naranja';
					break;
				}
			case 'B': {
					$gp_status = 'gp_status gp_status_verde';
					break;
				}
			default: {
					$gp_status = 'gp_status';
					break;
				}
		}
		break;
	case 'credito':
		$transaccion = $transacciones[$tipo_envio];
		switch ($order->get_meta('_gp_status_credito')) {
			case 'A': { //creado
					$gp_status = 'gp_status gp_status_naranja';
					break;
				}
			case 'B': { //procesando y compleatado
					$gp_status = 'gp_status gp_status_verde';
					break;
				}
			case 'C':
			case 'D':{
				$gp_status = 'gp_status gp_status_rojo';
				break;
			}
			default:{
				$gp_status = 'gp_status default';
				break;
			}
			
		}
		break;
	default:
		break;
}
?>
<div class="row vertical-tabs" style="padding-bottom:1em">
	<div class="col large-10 medium-12 pb-0">
		<h2 class="mb-0"><?php	printf(esc_html__('Pedido #%1$s', 'woocommerce'),'<strong class="order-number">' . $order->get_order_number() . '</strong>')	?> </h2>

		<span>
			<?php
				printf(
					esc_html__('Fecha de orden: %1$s', 'woocommerce'),
					'<strong class="order-date">' . wc_format_datetime($order->get_date_created(), 'd') . ' de ' . wc_format_datetime($order->get_date_created(), 'F, Y ') . wc_format_datetime($order->get_date_created(), 'h:i a') . '</strong><br>',
				);
				printf(
					/* translators: 1: order number 2: order date 3: order status */
					esc_html__('%1$s %2$s', 'woocommerce'),
					'<strong class="' . (empty($transaccion) ? '' : 'gp_transaction_type ' . $tipo_envio) . '">' . $transaccion . '</strong>',
					'<strong class="' . $gp_status . '">' . wc_get_order_status_name($order->get_status()) . '</strong>',
				);
			?>
		</span>

	</div>
	<?php if($tipo_envio == 'domicilio')://solo se ve el tracking en envios a domicilio?>
	<div class="col large-2 medium-12 pb-0" style="display:flex;justify-content:end">
		<div id="order-tracking" class="lightbox-by-id lightbox-content mfp-hide lightbox-white " style="max-width:600px ;padding:20px">
				<div class="row row-large">
					<div class="col large-12">
						<div class="order-tracking-container">
							<h3 class="uppercase">Seguimiento de la Orden #<?php echo( $order->get_order_number())?></h3>
							<div id="order-tracking-wrapper" class="tracking_gp">
								
							</div>
						</div>
					</div>
				</div>

		</div>
	</div>
	<?php endif;?>

</div>


<?php if ($notes) : ?>
	<h2><?php esc_html_e('Order updates', 'woocommerce'); ?></h2>
	<ol class="woocommerce-OrderUpdates commentlist notes">
		<?php foreach ($notes as $note) : ?>
			<li class="woocommerce-OrderUpdate comment note">
				<div class="woocommerce-OrderUpdate-inner comment_container">
					<div class="woocommerce-OrderUpdate-text comment-text">
						<p class="woocommerce-OrderUpdate-meta meta"><?php echo date_i18n(esc_html__('l jS \o\f F Y, h:ia', 'woocommerce'), strtotime($note->comment_date)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
																													?></p>
						<div class="woocommerce-OrderUpdate-description description">
							<?php echo wpautop(wptexturize($note->comment_content)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
							?>
						</div>
						<div class="clear"></div>
					</div>
					<div class="clear"></div>
				</div>
			</li>
		<?php endforeach; ?>
	</ol>
<?php endif; ?>

<?php do_action('woocommerce_view_order', $order_id); ?>