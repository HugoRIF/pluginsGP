<?php

// Redirect Registration Page
function gp_login_page_redirect()
{
	global $pagenow;

	if (  strtolower($pagenow) == 'wp-login.php' ) {
		wp_redirect( home_url('/my-account'));
	}
}




//! verifica que woocommerce esté activado


//https://rudrastyh.com/woocommerce/sorting-options.html#remove-options






/*
add_filter( 'woocommerce_catalog_orderby', 'gp_change_sorting_options_order', 10 );

function gp_change_sorting_options_order( $options ){

	$options = array(

		'menu_order' => __( 'Orden por popularidad', 'woocommerce' ), // you can change the order of this element too
    'title'  => 'Orden alfabético',
		'date'       => __( 'Orden por fecha lanzamiento', 'woocommerce' ), // Let's make "Sort by latest" the second one
		'price'      => __( 'Sort by price: low to high', 'woocommerce' ), // I need sorting by price to be the first
		'price-desc' => __( 'Sort by price: high to low', 'woocommerce' )

		// and leave everything else without changes
	//	'popularity' => __( 'Sort by popularity', 'woocommerce' ),
		//'rating'     => 'Sort by average rating', // __() is not necessary


	);

	return $options;
}
*/

//wp_enqueue_script( 'script', get_template_directory_uri() . '/js/script.js', array ( 'jquery' ), 1.1, true);
const AJAX_ORDER_TRACKING = 'ajax_order_tracking';
add_action('wp_enqueue_scripts', 'gp_enqueue_scripts');
function gp_enqueue_scripts() {
		// $date = date('YmdHis');
		$date = time();
		wp_enqueue_script( 'script-flatsome', '/wp-content/themes/flatsome-child/js/script.js', array ( 'jquery' ), $date, true);
		wp_enqueue_style( 'gp-flatsome-child-styles', '/wp-content/themes/flatsome-child/style-gp.css', array(), $date, 'all' );
		// wp_register_style( 'custom-style', '/wp-content/themes/flatsome-child/style.css', array(), $date, 'all' );
		wp_enqueue_style( 'custom-style-2', '/wp-content/themes/flatsome-child/style.css', array(), $date, 'all' );
		wp_enqueue_style( 'tracking-styles', '/wp-content/themes/flatsome-child/tracking.css', array(), $date, 'all' );
		//credenciales para obtener el tracking de una orden
		wp_localize_script( 'script-flatsome', 'gp_order_tracking', array(
			'ajaxurl'   => admin_url( 'admin-ajax.php'),
			'action'    => AJAX_ORDER_TRACKING,
		));
}


/* cambia el orden del box en las listas*/
function woocommerce_catalog_ordering() {
		global $wp_query;

		if ( 1 === (int) $wp_query->found_posts || ! woocommerce_products_will_display() ) {
				return;
		}

		$orderby = isset( $_GET['orderby'] ) ? wc_clean( $_GET['orderby'] ) : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) );
		$show_default_orderby = 'popularity' === apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) );
		$catalog_orderby_options = apply_filters( 'woocommerce_catalog_orderby', array(
			//	'menu_order' => __( 'Sort by popularity', 'woocommerce' ),
				'popularity' => __( 'Sort by popularity', 'woocommerce' ),
				'rating' => __( 'Sort by average rating', 'woocommerce' ),
				'title'  => 'Orden alfabético',
				'date' => __( 'Orden por fecha', 'woocommerce' ),
				'price-desc' => __( 'Sort by price: high to low', 'woocommerce' ),
				'price' => __( 'Sort by price: low to high', 'woocommerce' )
 ) );

		if ( ! $show_default_orderby ) {
				unset( $catalog_orderby_options['popularity'] );
		}

		if ( 'no' === get_option( 'woocommerce_enable_review_rating' ) ) {
				unset( $catalog_orderby_options['rating'] );
		}

		wc_get_template( 'loop/orderby.php', array( 'catalog_orderby_options' => $catalog_orderby_options, 'orderby' => $orderby, 'show_default_orderby' => $show_default_orderby ) );
}






/* agrega condicion y plataforma al loop */
if ( ! function_exists( 'gp_woocommerce_shop_loop_condicion_plataforma' ) ) {
	function gp_woocommerce_shop_loop_condicion_plataforma() {
		if ( ! flatsome_option( 'product_box_category' ) ) {
			return;
		}
			global $product;
			$product_id = $product->get_id();
		// plataformas
		$c ="";
		$plataformas = wc_get_product_terms( $product_id, 'pa_plataforma', array( 'fields' => 'all' ) );
		 if ( $plataformas ) {
		    $c .= '<span> ';
				$c_in = "";
				foreach($plataformas as $val) $c_in .= '<a class="plataforma-loop" href="/catalogo/?wpf=base_productos&wpf_con-inventario=1&wpf_plataforma=' .$val->slug . '">' . $val->name . '</a>, ';
				$c .= substr($c_in, 0, -2);
        $c .= ' | </span>';
		 }
		  echo substr($c, 0, -10).'</span>';
	?>
		<p class="condicion uppercase is-smaller no-text-overflow product-condicion">
			<?php

		//	$product_cats = function_exists( 'wc_get_product_category_list' ) ? wc_get_product_category_list( get_the_ID(), '\n', '', '' ) : $product->get_categories( '\n', '', '' );
		//	$product_cats = strip_tags( $product_cats );

		$condicion = $product->get_attribute( 'condicion' );

			if ( $condicion ) {
				list( $first_part ) = explode( '\n', $condicion );
				echo esc_html( apply_filters( 'gp_woocommerce_shop_loop_condicion_plataforma', $first_part, $product ) );
			}



			?>
		</p>
	<?php
	}
}
add_action( 'woocommerce_shop_loop_item_title', 'gp_woocommerce_shop_loop_condicion_plataforma', 10 );


/*
THE HOOK NAME:  woocommerce_single_product_summary hook.

THE TEMPLATES HOOKED (+priority order number)  => corresponding template file name:
— woocommerce_template_single_title       (5) => single-product/title.php
— woocommerce_template_single_rating     (10) => single-product/rating.php
— woocommerce_template_single_price      (10) => single-product/price.php
— woocommerce_template_single_excerpt    (20) => single-product/short-description.php
— woocommerce_template_single_add_to_cart(30) => single-product/add-to-cart/ (6 files depending on product type)
— woocommerce_template_single_meta       (40) => single-product/review-meta.php
— woocommerce_template_single_sharing -  (50) => single-product/share.php
*/



// https://www.businessbloomer.com/woocommerce-shorten-product-titles/
/*
if(!function_exists("is_shop")){

add_filter( 'the_title', 'gp_shorten_woo_product_title', 9999, 2 );
function gp_shorten_woo_product_title( $title, $id ) {
   if ( is_shop() && get_post_type( $id ) === 'product' ) {
      return wp_trim_words( $title, 6 ); // last number = words
   } else {
      return $title;
   }
}
*/


remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
function gp_custom_product_title() {
    global $product;

    $product_id = $product->get_id(); // The product ID

    // Your custom field "Book author"
    $title = $product->get_title();

    // Displaying your custom field under the title
    echo '<h1 class="product-title product_title entry-title"> '.$title.'</h1>';
		echo '<div class="product-title-sub">';

	  $c ="";

		// plataformas
		$plataformas = wc_get_product_terms( $product_id, 'pa_plataforma', array( 'fields' => 'all' ) );
		 if ( $plataformas ) {
		    $c .= '<span> <strong>Plataforma: </strong>';
				$c_in = "";
				foreach($plataformas as $val) $c_in .= '<a href="/catalogo/?wpf=base_productos&wpf_con-inventario=1&wpf_plataforma=' .$val->slug . '">' . $val->name . '</a>, ';
				$c .= substr($c_in, 0, -2);
        $c .= ' | </span>';
		 }
		 // clasificacion
 		$clasificacion = wc_get_product_terms( $product_id, 'pa_clasificacion', array( 'fields' => 'all' ) );
 		 if ( $clasificacion ) {
 		    $c .= '<span> <strong>Clasificación: </strong>';
				$c_in = "";
 				foreach($clasificacion as $val) $c_in .= '<a href="/catalogo/?wpf=base_productos&wpf_con-inventario=1&wpf_clasificacion=' .$val->slug . '">' . $val->name . '</a>, ';
 				$c .= substr($c_in, 0, -2);
        $c .= ' | </span>';
 		 }
		 // generos
 		$genero = wc_get_product_terms( $product_id, 'pa_genero', array( 'fields' => 'all' ) );
 		 if ( $genero ) {
 		    $c .= '<span> <strong>Género: </strong>';
				$c_in = "";
 				foreach($genero as $val) $c_in .= '<a href="/catalogo/?wpf=base_productos&wpf_con-inventario=1&wpf_genero=' .$val->slug . '">' . $val->name . '</a>, ';
 				$c .= substr($c_in, 0, -2);
        $c .= ' | </span>';
 		 }

		 echo substr($c, 0, -10).'</span>';

		 	echo '</div>';

		//	$plataformas = $product->get_attribute( 'plataforma' );
	  //  if ( $plataformas ) echo '<span class="title_plataforma"><strong>' . 'Plataforma: </strong>' .  $plataformas . '</span>';
		//	$clasificacion = $product->get_attribute( 'clasificacion' );
		//	if ( $clasificacion ) echo ' | <span class="title_clasificacion"><strong>' . 'Clasificación: </strong>' .  $clasificacion . '</span>';
	  //  $generos = $product->get_attribute( 'genero' );
		//	if ( $generos ) echo ' | <span class="title_genero"><strong>' . 'Género: </strong>' .  $generos . '</span>';


		echo '<div class="is-divider small"></div>';
}
add_action( 'woocommerce_single_product_summary', 'gp_custom_product_title', 5 );

//! --- bloque relacionado a precios del producto (html) ---
// https://stackoverflow.com/questions/41410174/display-sale-price-before-regular-price-woocommerce
// https://www.tychesoftwares.com/how-to-format-woocommerce-prices/
add_filter( 'wc_price', 'woo_format_decimal_value', 10, 4 );
function woo_format_decimal_value( $return, $price, $args, $unformatted_price ) {
	$price = floatval(str_replace(',', '', $price));
	$unit = intval( $price );
	$decimal = sprintf( '%02d', ( $price-$unit ) * 100 );
	$decimal_formateado = number_format($unit, 0, '.', ',');
	return sprintf( '<span class="price-symbol">%s</span>%s<span class="price-fraction">%s</span>', get_woocommerce_currency_symbol(), $decimal_formateado, $decimal );
}
if (!function_exists('gp_edit_price_html')) {
	/**
	 * Esta función regresa los precios formateados en html
	 * @param      string    $price_amt			Precio del producto.
	 * @param      string    $regular_price 	Precio regular del producto.
	 * @param      mixed     $sale_price    	Oferta del producto.
	 * @return	   string	 Precio(s) formateado(s) [html].
	 */
    function gp_edit_price_html($price_amt, $regular_price, $sale_price) {
		if(is_product()){
			$html_price_init = '<p class="price product-page-price price-on-sale gp_precio_ps" style="line-height: 0.5em;margin-top: 0.6em;margin-bottom: 0.6em;">';
			$html_price_end = '</p>';
		} else{
			$html_price_init = '<p class="gp_precio_ps" style="line-height: 0.5em; margin-top: 0.6em; margin-bottom: 0.6em;">';
			$html_price_end = '</p>';
		}
        $html_price = '';
        //* si el producto tiene descuento
        if (($price_amt == $sale_price) && ($sale_price != 0)) {
            $porc = (100 * $sale_price) / $regular_price;
            $porc_desc = 100 - $porc;
            if(is_product()){
                $html_price .= '<span class="gp_descuento_promo" style="color: red;font-weight: normal;opacity: 0.8; font-size: 0.6em;">-' . (int)$porc_desc . '%  </span>';
            }
            $html_price .= '<ins>' . wc_price($sale_price) . '</ins><br/>';
            if(is_product()){
                $html_price .= '<span style="font-size: 0.6em;"><span class="gp_precio_lista" style="font-size: 0.7em;color: #565959;font-weight: normal;">Precio de lista: </span>';
				$html_price .= '<del aria-hidden="true">' . wc_price($regular_price) . '</del>';
				$html_price .= '</span>';
            } else{
				$html_price .= '<del class="gp_precio_promo" aria-hidden="true" style="font-size: 0.6em;">' . wc_price($regular_price) . '</del>';
			}

        }
        //* si el producto está en descuento y es gratis
        else if (($price_amt == $sale_price) && ($sale_price == 0)) {
            $html_price .= '<ins>¡Gratis!</ins><br/>';
            $html_price .= '<del>' . wc_price($regular_price) . '</del>';
        }
        //* no tiene descuento
        else if (($price_amt == $regular_price) && ($regular_price != 0)) {
            $html_price .= '<ins>' . wc_price($regular_price) . '</ins> ';
        }
        //* si es gratis
        else if (($price_amt == $regular_price) && ($regular_price == 0)) {
            $html_price .= '<ins>¡Gratis!</ins> ';
        }
        return $html_price_init . $html_price . $html_price_end;
    }

}

add_filter('woocommerce_get_price_html', 'gp_simple_product_price_html', 99999, 2);
function gp_simple_product_price_html($price, $product) {
    if ($product->is_type('simple')) {
        $regular_price = $product->get_regular_price();
        $sale_price = $product->get_sale_price();
        $price_amt = $product->get_price();
				$regular_price  = (empty($regular_price))?0:$regular_price;
				$sale_price  = (empty($sale_price))?0:$sale_price;
				$price_amt  = (empty($price_amt))?0:$price_amt;

        return gp_edit_price_html($price_amt, $regular_price, $sale_price);
    } else {
        return $price;
    }
}

//! ------



/******Verifica que los productos relacionados tengan inventario******/
/*
	 add_filter( 'woocommerce_product_related_posts_query', 'gp_alter_product_related_posts_query', 10, 3 );
	 function gp_alter_product_related_posts_query( $query, $product_id, $args ){
	     global $wpdb;

	     $query['join']  .= " INNER JOIN {$wpdb->postmeta} as pm ON p.ID = pm.post_id ";
	     $query['where'] .= " AND pm.meta_key = '_stock_status' AND meta_value = 'instock' ";

	     return $query;
	 }
*/
/*******************/


// https://stackoverflow.com/questions/54951859/hide-out-of-stock-related-products-in-woocommerce
/*
add_action('init', 'delete_related_products_cached_data');
function delete_related_products_cached_data() {
    global $wpdb;

    $wpdb->query("DELETE FROM {$wpdb->prefix}options WHERE `option_name` LIKE '_transient_wc_related_%'");
}
*/




/*

// Override product data tabs
add_filter( 'woocommerce_product_tabs', 'woocommerce_override_tabs', 98 );
function woocommerce_override_tabs( $tabs ) {
    // Change the default sequence of the product tabs
	$tabs['reviews']['priority'] = 15; // Reviews first
	$tabs['description']['priority'] = 10; // Description second
	$tabs['additional_information']['priority'] = 5; // Ingredients last


    // Done - return the result of the modifications made above
    return $tabs;
}
*/





/// agregar tab a productos de preventas
add_filter( 'woocommerce_product_tabs', 'gp_custom_tab',98 );
function gp_custom_tab( $tabs ) {

	global $product;
  $condicion = $product->get_attribute( 'condicion' );

   if ( $condicion == 'PREVENTA'  ) {
				    $tabs['gp_custom_faq_preventas_tab'] = array(
				        'title'    => 'FAQ',
				        'callback' => 'gp_custom_faq_preventas_tab_content',
				        'priority' => 50,
								'condicion' => $condicion
				    );
		}

    return $tabs;
}
function gp_custom_faq_preventas_tab_content($slug, $tab) {

	  	$url = home_url( '', 'https' );
			if ($url =='https://planet.shop'){
					// para planet
					echo do_shortcode('[yith_faq_preset id="400995"]' );
				}else{
					// para gameplanet
					echo do_shortcode('[yith_faq_preset id="461831"]' );
				}

}
/*

add_filter( 'woocommerce_product_tabs', 'woo_remove_product_tabs', 98 );

function woo_remove_product_tabs( $tabs ) {

    unset( $tabs['description'] );      	// Remove the description tab
  //  unset( $tabs['reviews'] ); 			// Remove the reviews tab
    unset( $tabs['additional_information'] );  	// Remove the additional information tab

    return $tabs;
}
*/

/**** Modificacionespara el buscador Fibo Search****/

add_filter( 'dgwt/wcas/indexer/taxonomies', function ( $taxonomies ) {
    $taxonomies[] = 'pa_plataforma';
    $taxonomies[] = 'pa_publisher';
    return $taxonomies;
} );



add_filter( 'dgwt/wcas/tnt/indexer/readable/product/data', function ( $data, $product_id, $product ) {
    global $wpdb;

    $max = absint($wpdb->get_var( "SELECT MAX(meta_value) FROM $wpdb->postmeta WHERE meta_key = 'total_sales'" ));
    $sales = $product->getWooObject()->get_total_sales();

    if ( empty( $max ) ) {
        $max = 1;
    }

    $factor_weight = 0.3;

    // 1 = the best
    // 0.7 = the worst
    $factor = 1 - ( (1 - ($sales / $max)) * $factor_weight );
    $data['meta']['total_sales_factor'] = $factor;


		if ( $product->getWooObject()->get_stock_status() === 'outofstock' ) {
        $data['meta']['out_of_stock'] = true;
    }



		$term = $product->getTerms( 'pa_plataforma', 'string' );

    if ( ! empty( $term ) ) {

        $html = '<span class="suggestion-plataforma">';
        $html .= $term;
        $html .= ' - </span>';


        $data['meta']['pa_plataforma'] = $html;
    }



		$term = $product->getTerms( 'pa_condicion', 'string' );

    if ( ! empty( $term ) ) {

        $html = '<span class="suggestion-condicion">';
        $html .= $term;
        $html .= '</span>';


        $data['meta']['pa_condicion'] = $html;
    }




    return $data;
}, 10, 3 );


/**********/


/* quita el titulo de BILLING ADDRESS*/
/*
function wc_billing_field_strings( $translated_text, $text, $domain ) {
    switch ( $translated_text ) {
        case 'Billing Address' :
            $translated_text = __( '', 'woocommerce' );
            break;
    }
    return $translated_text;
}
add_filter( 'gettext', 'wc_billing_field_strings', 20, 3 );
*/
/**********/




/***** reorden en los campos de la direcion  ******/
// https://rudrastyh.com/woocommerce/reorder-checkout-fields.html




/*********/

// Do NOT include the opening php tag.
// Place in your theme's functions.php file




// https://woocommerce.com/document/tutorial-customising-checkout-fields-using-actions-and-filters/
//add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields',30 );

// Our hooked in function - $fields is passed via the filter!
//function custom_override_checkout_fields( $fields ) {
    // unset($fields['billing']['billing_email']);
		// unset($fields['billing']['billing_first_name']);
//     return $fields;
//}

/***********************/


/*
add_action( 'woocommerce_after_edit_account_form', 'disable_edit_email_address' );

function disable_edit_email_address( ) {
    $script = '<script type="text/javascript">'.
              'var account_display_name = document.getElementById("account_display_name");'.
              'if(account_display_name) { '.
              '     account_display_name.readOnly = true; '.
              '     account_display_name.className += " disable-input";'.
              '}'.
              '</script>';
    echo $script;
}

add_action( 'woocommerce_save_account_details_errors', 'prevent_user_update_email', 10, 2 );

function prevent_user_update_email( &$error, &$user ){
	$current_user = get_user_by( 'id', $user->ID );
	$current_email = $current_user->account_display_name;
	if( $current_email !== $user->account_display_name){
		$error->add( 'error', 'E-mail cannot be updated.');
	}
}

*/

// woocommerce_after_save_address_validation
/*
add_action( 'woocommerce_after_edit_address_form_billing', 'disable_edit_email_address' );

function disable_edit_email_address( ) {
    $script = '<script type="text/javascript">'.
              'var billing_phone = document.getElementById("billing_phone");'.
              'if(billing_phone) { '.
              '     billing_phone.readOnly = true; '.
              '     billing_phone.className += " disable-input";'.
              '}'.
              '</script>';
    echo $script;

}
*/
/*
add_filter( 'update_user_meta', 'ts_unique_wc_phone_field');
function ts_unique_wc_phone_field( $errors ) {

  $errors->add( 'billing_phone_error', __( '<strong>Error</strong>: Mobile number is already used!.', 'woocommerce' ) );

    if ( isset( $_POST['billing_phone'] ) ) {
        $hasPhoneNumber= get_users('meta_value='.$_POST['billing_phone']);
            if ( !empty($hasPhoneNumber)) {
        $errors->add( 'billing_phone_error', __( '<strong>Error</strong>: Mobile number is already used!.', 'woocommerce' ) );
    }
  }

    return $errors;
}
*/

// https://stackoverflow.com/questions/33980439/woocommerces-hook-that-runs-on-update-checkout-or-update-order-review
// https://tutorialmeta.com/question/how-to-reload-checkout-after-update-checkout-using-woocommerce-checkout-update-o
// https://stackoverflow.com/questions/44958686/check-if-there-are-out-of-stock-items-in-cart-woocommerce
// https://www.digital-noir.com/woocommerce-cart-quantity-validation-tutorial/
// wc-ajax=update_order_review

/*
add_action( 'woocommerce_update_product', 'woocommerce_update_product_to_gameplanet', 10, 1 );
function woocommerce_update_product_to_gameplanet($post_id){
	$product_id = $post_id;
	$stores = get_option( 'wc_api_mps_stores' );
	wc_api_mps_integration( $product_id, $stores );

}
*/





// add
add_filter( 'manage_edit-product_columns', 'gp_condicion_column', 20 );
function gp_condicion_column( $columns_array ) {

	// I want to display Brand column just after the product name column
	return array_slice( $columns_array, 0, 3, true )
	+ array( 'condicion' => 'Condición' )
	+ array_slice( $columns_array, 3, NULL, true );


}
// populate
add_action( 'manage_posts_custom_column', 'gp_populate_condicion' );
function gp_populate_condicion( $column_name ) {

	if( $column_name  == 'condicion' ) {
		// if you suppose to display multiple values, use foreach();
		$x = get_the_terms( get_the_ID(), 'pa_condicion'); // taxonomy name
		if (@$x[0]->name) {
			echo $x[0]->name;
		}else{
			echo "-";
		}
	}

}



function gp_header_note(){

  //  echo get_user_meta( $customer_id, 'billing_phone', true )

  $current_user = wp_get_current_user();
//  $current_user->ID

	if ($current_user->exists()){

 		$billing_phone = get_user_meta( $current_user->ID, 'billing_phone', true );
			if ($billing_phone==''){
				echo '<div class="status_phone_barra">Actualmente <strong>NO</strong> tienes un número de teléfono con el cual podamos validar tu cuenta para tus envios, para poder comprar sera necesario que primero lo registres <a href="/my-account/edit-address/facturacion/">aquí</a>!</div>';
			}

 }


}

add_action('init','gp_notificaciones_header');

function gp_notificaciones_header(){
  add_shortcode( "header_note" , 'gp_header_note' );
}


/********* manejo de cabeceras en la vista del producto**/

add_action('woocommerce_before_main_content','gp_before_main_content_product', 10 );

 function gp_before_main_content_product(){

	 if ( is_product() ){

					 $plataformas = false;
					 $a = array();
					 $product_id = get_the_ID(); // The product ID

  			 		// plataformas
	    	 		$plataformas = wc_get_product_terms( $product_id, 'pa_plataforma', array( 'fields' => 'all' ) );
				 		 if ( $plataformas ) {
							 $s = false;
				 			foreach($plataformas as $val){

								switch($val->name){
									case 'XONE':
									case 'XSX':
									case '360':
									case 'XBX':
									case 'XSS':
													echo '<div class="product_barra"><div class="microsoft"> <h3>Más productos MICROSOFT <a href="../../catalogo/?wpf=base_productos&wpf_page=1&wpf_con-inventario=1&wpf_plataforma=360%2Cxbx%2Cxone%2Cxss%2Cxsx">click aquí.</a></h3> </div></div>';
													$s = true;
											  break;
									case 'PS2':
									case 'PS3':
									case 'PS4':
									case 'PS5':
									case 'PSP':
									case 'PSV':
													echo '<div class="product_barra"><div class="sony"> <h3>Más productos SONY <a href="../../catalogo/?wpf=base_productos&wpf_page=1&wpf_con-inventario=1&wpf_plataforma=ps2%2Cps3%2Cps4%2Cps5%2Cpsp%2Cpsv">click aquí.</a></h3> </div></div>';
													$s = true;
											  break;
									case '3DS':
									case 'N2DS':
									case 'N3DS':
									case 'NDS':
									case 'NSW':
									case 'WII':
									case 'WIIU':
													echo '<div class="product_barra"><div class="nintendo"> <h3>Más productos NINTENDO <a href="../../catalogo/?wpf=base_productos&wpf_page=1&wpf_con-inventario=1&wpf_plataforma=3ds%2Cn2ds%2Cn3ds%2Cnds%2Cnsw%2Cwii%2Cwiiu">click aquí.</a></h3> </div></div>';
													$s = true;
											  break;
								}

							if ($s) break;

							}




				 		 }


	     }

 }





  // funciones especiales
	 function get_product_by_sku( $sku ) {

    global $wpdb;

    $product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $sku ) );

    if ( $product_id ) return new WC_Product( $product_id );

    return null;
}


/****** MY ACCOUNT ******/

/** View-orders */

//variables para hacer ajax


add_action( 'wp_ajax_'.AJAX_ORDER_TRACKING, AJAX_ORDER_TRACKING); // admin
add_action( 'wp_ajax_nopriv_'.AJAX_ORDER_TRACKING, AJAX_ORDER_TRACKING); // admin

function ajax_order_tracking(){
	try {
		$ticket = $_POST['ticket'];

    $responseAPI = api_call_tracking($ticket);

    echo json_encode($responseAPI);
    die();
  } catch (\Exception $e) {
    echo json_encode([
      "success" => false,
      "message" => "Error interno",
      "code" => 500,
      "data" => null
    ]);
    die();
  }
}
function api_call_tracking($ticket)
{

  /*Le llame diferente a proposito para que no sepan como se pasan a la API*/
  $args = array(
    'timeout'     => 30,
    'headers' => array(
      'Content-Type' => 'application/json',
      'data-jwt-master' => get_option('data-jwt-master'),
    )
  );


  $url = get_option('ruta_gameplanet') . "tracking/info/".$ticket."/0/venta";

  $response = wp_remote_get($url, $args);

  // Si hay un error
  if (is_wp_error($response)) {
    $error_message = $response->get_error_message();
    return [
      "success" => false,
      "message" => "Error al buscar",
      "data" => [$error_message],
    ];
  }
  $res = json_decode($response['body'], true);
  return $res;
}

/***Menu de my account */

add_filter( 'woocommerce_get_endpoint_url', function ( $url, $endpoint, $value, $permalink ) {
	if ( $endpoint === 'my-presales' ) {
			$url = home_url( 'my-account/preventas' );
	}
	if ( $endpoint === 'my-shopping' ) {
		$url = home_url( 'my-account/compras' );
	}
	return $url;
}, 10, 4 );
add_action( 'woocommerce_account_menu_items', 'gp_my_account_menu_text');
function gp_my_account_menu_text($items) {
return array(
'orders' => 'Compras',
'my-presales' => __( 'Preventas', 'woocommerce' ),
'my-shopping' => __( 'Historial de Compras', 'woocommerce' ),
'ywar-reviews' => __( 'Reseñas', 'woocommerce' ),
'edit-address' => __( 'Datos de envio', 'woocommerce' ),
'edit-account' => __( 'Detalles de la cuenta', 'woocommerce' ),
);

}