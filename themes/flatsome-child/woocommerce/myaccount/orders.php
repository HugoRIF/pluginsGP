<?php
/**
 * Orders
 *
 * Shows orders on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/orders.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.7.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_orders', $has_orders ); ?>
<?php
function gp_ps_obtener_estatus_productos(){
}
/** Customizacion de columnas */

/**Para no modificar el arragleo tan discirmanadamente, usamos el hook
 * se filtra columnas y se le agregan las columnas nuevas
 */
add_filter( 'woocommerce_account_orders_columns', 'add_account_orders_column', 10, 1 );
function add_account_orders_column( $columns ){
		//nueva columna
		$transaccion = [
			'order-transaction' => __( 'Transacción', 'woocommerce' ),
		];
		$posicion_transaccion = 2;
		//insertamos la columna en la posicion que necesitamos
    $columns = array_merge(array_slice($columns, 0, $posicion_transaccion), $transaccion, array_slice($columns, $posicion_transaccion));


    return $columns;
}

/****** CONTENIDO DE LA TABLA
 * Para una mejor lectura se usaran los hooks de woocomerce para dar formato a cada celda
 * El template de woocomerce ya tenia la validacion de has_action asi que solo las vamos agregando
 */


	//numero de orden
	add_action( 'woocommerce_my_account_my_orders_column_order-number', 'add_account_orders_column_order_number' );
	function add_account_orders_column_order_number( $order ) {
			?>
			<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>">
				<?php echo esc_html( _x( '#', 'hash before order number', 'woocommerce' ) . $order->get_order_number() ); ?>
			</a>
			<?php
	}

	//fecha de orden
	add_action( 'woocommerce_my_account_my_orders_column_order-date', 'add_account_orders_column_order_date' );
	function add_account_orders_column_order_date( $order ) {
		?>
		<time datetime="<?php echo esc_attr( $order->get_date_created()->date( 'c' ) ); ?>"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></time>
		<?php
	}

	//transaccion 
	add_action( 'woocommerce_my_account_my_orders_column_order-transaction', 'add_account_orders_column_order_transaction' );
	function add_account_orders_column_order_transaction( $order ) {
		//el tipo de envio determina la transaccion
		$tipo_envio = $order->get_meta('_gp_tipo_envio');

	
		if(empty($tipo_envio)){
			$tipo_envio = $order->get_meta('_ps_tipo_envio');
		}
		//si aun sigue vacio hardcodeamso esto
		if(empty($tipo_envio)){
			$tipo_envio = "";
			?>
				<span>
					<?php echo "" ?>
				</span>
			<?php
		} else{
			//Solo formateamos no tal vez hay otra solucion mas optima
			$transacciones = [
				"apartado"=>"Apartado",
				"domicilio"=>"Envio a Domicilio",
				"credito"=>"Credito Gameplanet",
			];
			$transacciones['tienda'] = "Preventa";//no estoy seguro que con esto sea suficiente
			
			?>
				<span class="gp_transaction_type <?php echo $tipo_envio; ?>">
					<?php echo $transacciones[$tipo_envio] ?>
				</span>
			<?php
		}

	}

	//estado
	add_action( 'woocommerce_my_account_my_orders_column_order-status', 'add_account_orders_column_order_status' );
	function add_account_orders_column_order_status( $order ) {
		$gp_status = 'gp_status default';
		$tipo_envio = $order->get_meta('_gp_tipo_envio');
		if(empty($tipo_envio)){
		$tipo_envio = $order->get_meta('_ps_tipo_envio');
			
		}
		switch ($tipo_envio) {
			case 'apartado':
				switch($order->get_meta('_gp_estatus_apartado')){
					case 'A':{
						$gp_status = 'gp_status gp_status_naranja';
						break;
					}
					case 'B':
					case 'E':{
						$gp_status = 'gp_status gp_status_verde';
						break;
					}
					case 'C':
					case 'D':{
						$gp_status = 'gp_status gp_status_rojo';
						break;
					}
					default:{
						$gp_status = 'gp_status';
						break;
					}
				}
				break;
			case 'domicilio':
				switch($order->get_meta('_gp_estatus_domicilio')){
					case 'A':{
						$gp_status = 'gp_status gp_status_naranja';
						break;
					}
					case 'B':
					case 'D':{
						$gp_status = 'gp_status gp_status_verde';
						break;
					}
					case 'C':{
						$gp_status = 'gp_status gp_status_rojo';
						break;
					}
					default:{
						$gp_status = 'gp_status';
						break;
					}
				}
				break;
			case 'tienda'://preventa
				switch($order->get_meta('_gp_estatus_preventa')){
					case 'A':{
						$gp_status = 'gp_status gp_status_naranja';
						break;
					}
					case 'B':{
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
			case 'credito':
				switch($order->get_meta('_gp_status_credito')){
					case 'A':{//creado
						$gp_status = 'gp_status gp_status_naranja';
						break;
					}
					case 'B':{//procesando y compleatado
						$gp_status = 'gp_status gp_status_verde';
						break;
					}
					default:{
						$gp_status = 'gp_status';
						break;
					}
				}
				break;
			default:
				break;
		}
		?>
			<span class="<?php echo $gp_status; ?>">
				<?php echo (esc_html( wc_get_order_status_name( $order->get_status() ) )); ?>
			</span>
		<?php
	}

	//total
	add_action( 'woocommerce_my_account_my_orders_column_order-total', 'add_account_orders_column_order_total' );
	function add_account_orders_column_order_total( $order ) {
			
			echo $order->get_formatted_order_total();
	}

	//acciones
	add_action( 'woocommerce_my_account_my_orders_column_order-actions', 'add_account_orders_column_order_actions' );
	function add_account_orders_column_order_actions( $order ) {
		$actions = wc_get_account_orders_actions( $order );

		if ( ! empty( $actions ) ) {
			foreach ( $actions as $key => $action ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				echo '<a href="' . esc_url( $action['url'] ) . '" class="woocommerce-button button ' . sanitize_html_class( $key ) . '">' . esc_html( $action['name'] ) . '</a>';
			}
		}
	}
?>

<?php if ( $has_orders ) : ?>

	<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
		<thead>
			<tr>
				<?php foreach ( (wc_get_account_orders_columns()) as $column_id => $column_name ) : ?>
					<th class="woocommerce-orders-table__header woocommerce-orders-table__header-<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo esc_html( $column_name ); ?></span></th>
				<?php endforeach; ?>
			</tr>
		</thead>

		<tbody>
			<?php
			foreach ( $customer_orders->orders as $customer_order ) {
				$order      = wc_get_order( $customer_order ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				$item_count = $order->get_item_count() - $order->get_item_count_refunded();
				?>
				<tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr( $order->get_status() ); ?> order" order-url="<?php echo esc_url( $order->get_view_order_url() ); ?>">
						<?php foreach ( wc_get_account_orders_columns() as $column_id => $column_name ) : ?>
							<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
								<?php if ( has_action( 'woocommerce_my_account_my_orders_column_' . $column_id ) ) : ?>
									<?php do_action( 'woocommerce_my_account_my_orders_column_' . $column_id, $order ); ?>
								
								<?php else : ?>
									<span>-</span>
								<?php endif; ?>
							</td>
						<?php endforeach; ?>
				</tr>
				<?php
			}
			?>
		</tbody>
	</table>

	<?php do_action( 'woocommerce_before_account_orders_pagination' ); ?>

	<?php if ( 1 < $customer_orders->max_num_pages ) : ?>
		<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
			<?php if ( 1 !== $current_page ) : ?>
				<a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page - 1 ) ); ?>"><?php esc_html_e( 'Previous', 'woocommerce' ); ?></a>
			<?php endif; ?>

			<?php if ( intval( $customer_orders->max_num_pages ) !== $current_page ) : ?>
				<a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page + 1 ) ); ?>"><?php esc_html_e( 'Next', 'woocommerce' ); ?></a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

<?php else : ?>
	<div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
		<a class="woocommerce-Button button" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>"><?php esc_html_e( 'Browse products', 'woocommerce' ); ?></a>
		<?php esc_html_e( 'No order has been made yet.', 'woocommerce' ); ?>
	</div>
<?php endif; ?>

<?php do_action( 'woocommerce_after_account_orders', $has_orders ); ?>
