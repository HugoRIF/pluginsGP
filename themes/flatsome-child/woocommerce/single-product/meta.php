<?php
/**
 * Single Product Meta
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/meta.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;
?>
<div class="product_meta">

	<?php do_action( 'woocommerce_product_meta_start' ); ?>

	<?php $nombre_info_prod = 'Sobre este producto';?>
	<?php $lista_info_prod = '<ul class="gp_mt-1em">';?>

	<?php if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) ) : ?>
		<?php
		$software = FALSE;
		$condicion = $product->get_attribute( 'condicion' );
		if ( $condicion ) {
			echo "<div class='condicion'>";
			echo "<span class='condicion uppercase is-large no-text-overflow product-condicion-principal '>".esc_html(  $condicion )."</span>";
			//buscamos categorias para considerar imprimir la contraparte (Nuevo / Usado)
			$product_cats = wp_get_post_terms( get_the_ID(), 'product_cat' );
			if ( $product_cats && ! is_wp_error ( $product_cats ) ){
				foreach($product_cats as $c){
					if($c->name=="Software"){
						$software = TRUE;
						break;
					}else{
						$software = FALSE;
					}
				}
			}
			if($software && $condicion=="NUEVO"){
				$sku_contra = "U".$product->get_sku();
				$id_contra = wc_get_product_id_by_sku( $sku_contra );
				if($id_contra){
					$link = get_permalink( $id_contra );
					echo "<span class='condicion uppercase is-large no-text-overflow product-condicion-secundario'><a href='".$link."'>Usado</a></span>";
				}
			}elseif($software && $condicion=="USADO"){
				$sku_contra = substr($product->get_sku(),1);
				$id_contra = wc_get_product_id_by_sku( $sku_contra );
				if($id_contra){
					$link = get_permalink( $id_contra );
					echo "<span class='condicion uppercase is-large no-text-overflow product-condicion-secundario'><a href='".$link."'>Nuevo</a></span>";
				}
			}
		}
		echo "</div>";

		//* bloque para diferentes plataformas
		global $wpdb;
		if($wpdb->check_connection()){
			$table_name = $wpdb->posts;
			if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
				$post_type = 'product';
				$page_title = $product->get_name();
				$sql = $wpdb->prepare(
					"
					SELECT ID
					FROM $table_name
					WHERE post_title = %s
					AND post_type = %s
				",
					$page_title,
					$post_type
				);
				$page = $wpdb->get_results( $sql );
				// error_log(print_r($page, true));
				//* foreach para obtener id
				$hyperlinks_plat = '';
				
				$btns_plataforma = '<div class="gp_plataformas">';
				$cont_plat = 0;
				foreach($page as $key => $value){
					$prod = wc_get_product($value->ID);
					$plataforma_prod = $prod->get_attribute('plataforma');
					if(isset($value->ID) && !empty($plataforma_prod)){
						$cont_plat += 1;
						$id_prod = $value->ID;
						$img_prod = $prod->get_image();
						$nombre_producto = $prod->get_name();
						$perma_prod = get_permalink( $value->ID );
						$plataforma_prod_simp = $product->get_attribute('plataforma');
						$sku_prod = $prod->get_sku();

						if($condicion == 'USADO' && str_starts_with($sku_prod, 'U')){
							$plat_selecc = '';
							if($plataforma_prod == $plataforma_prod_simp){
								$plat_selecc = ' class="gp_plat_selecc" ';
							}
							$hyperlinks_plat .= "
								<a href='$perma_prod' $plat_selecc>{$plataforma_prod}</a>
							";
						} elseif($condicion == 'NUEVO' && !str_starts_with($sku_prod, 'U')){
							$plat_selecc = '';
							if($plataforma_prod == $plataforma_prod_simp){
								$plat_selecc = ' class="gp_plat_selecc" ';
							}
							$hyperlinks_plat .= "
								<a href='$perma_prod' $plat_selecc>{$plataforma_prod}</a>
							";
						}
		
						// error_log(print_r($prod, true));
					}
				}
				if($cont_plat >= 1){
					$btns_plataforma .= '<p>Plataformas disponibles</p>' . $hyperlinks_plat;
				}
				$btns_plataforma .= '</div>';
				echo $btns_plataforma;
			} else{
				echo "Code: M-001";
			}
		}

		//* bloque para preventas
		if(str_starts_with($product->get_sku(), 'P')){
			$nombre_info_prod = 'Sobre esta preventa';
			$lat = _GP_GEO_LAT;
			$lng = _GP_GEO_LNG;
			$tienda_fav = _GP_TIENDA_DEFAULT_ID;

			if(isset($_COOKIE['_gp_tienda_favorita_id'])){
				$tienda_fav = filter_var($_COOKIE['_gp_tienda_favorita_id'], FILTER_SANITIZE_ENCODED);
			}

			if(isset($_COOKIE['_gp_geo_lat']) && isset($_COOKIE['_gp_geo_lng'])){
				$lat = filter_var($_COOKIE['_gp_geo_lat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
				$lng = filter_var($_COOKIE['_gp_geo_lng'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
			}

			$id_gp = 0;
			if(is_user_logged_in()){
				$user_id = get_current_user_id();
				$id_gp = get_user_meta($user_id, 'id_gp', true);
			}
			$tipo_producto = 'preventa';
			$datos_domicilio = [
				'preventa' => [
					'solicitud' => 1,
					'metodo' => 'cache',
					'cantidad_min' => 1,
				],
			];
			$datos_tienda = [
				'preventa' => [
					'solicitud' => 1,
					'metodo' => 'cache',
					'cantidad_min' => 1,
				],
			];

			$datos = [
				'productos' => [
					0 => [
						'upc' => $product->get_sku(),
						'surtidor' => 'GAM',
						'tipo' => $tipo_producto,
						'lat' => $lat,
						'lng' => $lng,
						'id_tienda_favorita' => $tienda_fav,
						'id_tienda_seleccionada' => $tienda_fav,
						"id_cliente" => $id_gp,
						'domicilio' => $datos_domicilio,
						'tienda' => $datos_tienda,
					],
				],
			];

			$args = array(
				'body' => json_encode($datos),
				'headers' => array(
					'Content-Type' => 'application/json',
					'data'         => '4c8a7ac9d9a724b4f5db012be3c8f2cbeb4aa325f154319cbd41dd52da27ebe0'
				)
			);
			$url = "http://54.172.203.98:5005/api/vl2/tendero/producto/disponibilidad2";
			$response = wp_remote_post($url, $args); //!
			if (is_wp_error($response)) {
				$mensaje_error = $response->get_error_message();
				?>
				<span>Code:W-008</span>
				<?php
			} else {
				// cachamos codigo http (['response']['code'])
				if ($response['response']['code'] == 200) {
					// obtenemos el body
					$ext_auth = json_decode($response['body'], true); //!
					// gp_widgets_logs('widget', 'Response', $ext_auth);

					// Success == true
					if ($ext_auth['success']) {

						// cachamos el codigo ( 0 == operacion exitosa)
						if ($ext_auth['code'] == 0) {
							//! -----
							$fecha_lanzamiento = '';
							$precio_final_confirmado = '';
							$precio_final = '';
							$fecha_limite_apartado = '';
							$nombre = '';

							$temp = '';

							$meses = array("enero","febrero","marzo","abril","mayo","junio","julio","agosto","septiembre","octubre","noviembre","diciembre");

							if(isset($ext_auth['result'][0]['nombre'])){
								if($ext_auth['result'][0]['nombre']){
									$nombre = $ext_auth['result'][0]['nombre'];
								}
							}

							if($nombre){?>
								<input value="<?php esc_html_e($nombre) ?>" id="gp_nombre_producto" class="hidden">
							<?php }

							if(isset($ext_auth['result'][0]['precio_final_confirmado'])){
								if($ext_auth['result'][0]['precio_final_confirmado']){
									$precio_final_confirmado = 'SI';
								} else{
									$precio_final_confirmado = 'NO';
								}
								$lista_info_prod .= '<li><span class="sku_wrapper"><span class="gp_fw-b">Precio final confirmado: </span>' . $precio_final_confirmado . '</span></li>';
							}

							if(isset($ext_auth['result'][0]['fecha_lanzamiento']) && isset($ext_auth['result'][0]['fecha_lanzamiento_confirmada'])){
								if($ext_auth['result'][0]['fecha_lanzamiento_confirmada']){
									$temp = strtotime($ext_auth['result'][0]['fecha_lanzamiento']);
									$lista_info_prod .= '<li><span class="sku_wrapper"><span class="gp_fw-b">Fecha de lanzamiento confirmada: </span>SI</span></li>';
									$fecha_lanzamiento = date('d', $temp) . ' de ' . $meses[date('n', $temp)-1] . date(', Y ', $temp);
									$temp = '';
								} else{
									$lista_info_prod .= '<li><span class="sku_wrapper"><span class="gp_fw-b">Fecha de lanzamiento confirmada: </span>NO</span></li>';
									$fecha_lanzamiento = 'No confirmado';
								}
								$lista_info_prod .= '<li><span class="sku_wrapper"><span class="gp_fw-b">Fecha de lanzamiento: </span>' . $fecha_lanzamiento . '</span></li>';
							}

							if(isset($ext_auth['result'][0]['fecha_limite_apartado'])){
								if($ext_auth['result'][0]['fecha_limite_apartado']){
									$temp = strtotime($ext_auth['result'][0]['fecha_limite_apartado']);
									$fecha_limite_apartado = date('d', $temp) . ' de ' . $meses[date('n', $temp)-1] . date(', Y', $temp);
									$temp = '';
								} else{
									$fecha_limite_apartado = 'por definir';
								}
								$lista_info_prod .= '<li><span class="sku_wrapper"><span class="gp_fw-b">Fecha límite de apartado: </span>' . $fecha_limite_apartado . '</span></li>';
							}

							//! -----
						} else{
							?>
							<span>Code: W-011</span>
							<?php
						}
					} else{
						?>
						<span>Code: W-010</span>
						<?php
					}
				} else{
					?>
					<span>Code: W-009</span>
					<?php
				}
			}
			?>
			<?php
		}
		//* -----

		$sku_html = esc_html__( 'N/A', 'woocommerce' );
		if($sku = $product->get_sku()){
			$sku_html = $sku;
		}
		$lista_info_prod .= '<li><span class="sku_wrapper"><span class="gp_fw-b">' . esc_html__( 'SKU:', 'woocommerce' ) . ' </span><span class="sku">' . $sku_html . '</span></span></li>';
		?>
	<?php endif; ?>


	<?php
	$lista_info_prod .= '<li>' . wc_get_product_category_list( $product->get_id(), ', ', '<span class="posted_in"><span class="gp_fw-b">' . _n( 'Category:', 'Categories:', count( $product->get_category_ids() ), 'woocommerce' ) . ' </span>', '</span>' ) . "</li>"; ?>

	<?php

	$output = array();

	// get an array of the WP_Term objects for a defined product ID
	$terms = wp_get_post_terms( get_the_id(), 'product_tag' );

	// Loop through each product tag for the current product
	if( count($terms) > 0 ){
	    foreach($terms as $term){
	        $term_id = $term->term_id; // Product tag Id
	        $term_name = $term->name; // Product tag Name
	        $term_slug = $term->slug; // Product tag slug
	        $term_link = get_term_link( $term, 'product_tag' ); // Product tag link

	        // Set the product tag names in an array

					if(substr($term_name,0,1)!='_')  $output[] = '<a href="'.$term_link.'">'.$term_name.'</a>';
	    }
	    // Set the array in a coma separated string of product tags for example
	    $output = implode( ', ', $output );

	    $lista_info_prod .= ' <li><span class="tagged_as"><span class="gp_fw-b"> Etiquetas: </span> '.$output.'</li>';
	}

 ?>

	<?php echo '<h3 class="gp_mt-1em">' . $nombre_info_prod . '</h2>' . $lista_info_prod . '</ul>' ?>

	<?php do_action( 'woocommerce_product_meta_end' ); ?>

</div>
