<?php
//define( 'SHORTINIT', true );
//require_once ('../../../wp-load.php');

// [SHORTINIT MODE] Run this snippet in the SHORTINIT mode (fibosearch.php file). Take a look at the Implementation section above.
/*add_filter( 'dgwt/wcas/tnt/product/score', function ( $score, $product_id, $product ) {
    $factor = isset( $product->meta['menu_order_factor'] ) ? (float) $product->meta['menu_order_factor'] : 0;
    return $score * $factor;
}, 10, 3 );
*/


/*
add_filter( 'dgwt/wcas/tnt/product/score', function ( $score, $product_id, $product ) {
    if ( ! empty( $product->meta['out_of_stock'] ) ) {
        $score -= 1000;
    }
  //  var_dump("test");
    return $score;
}, 10, 3 );
*/

/**
 * Handles order by alphabetical
 * Works only with PHP 7 and higher
 */
 /*
add_filter( 'dgwt/wcas/tnt/sort_products', function ( $products, $order ) {

    if ( $order === 'menu_order' ) {
        usort( $products, function ( $a, $b ) {
            if ( $a->menu_order != $b->menu_order ) {
                return $a->menu_order <=> $b->menu_order;
            }
        });
    }
    return array_reverse($products);
}, 10, 2 );
*/


// [SHORTINIT MODE] Run this snippet in the SHORTINIT mode (fibosearch.php file). Take a look at the Implementation section above.
add_filter( 'dgwt/wcas/tnt/product/score', function ( $score, $product_id, $product ) {
    $factor = isset( $product->meta['total_sales_factor'] ) ? (float) $product->meta['total_sales_factor'] : 0;

    $score = $score * $factor;

    if ( ! empty( $product->meta['out_of_stock'] ) ) {
        $score -= 100;
    }

    return $score;
}, 10, 3 );

add_filter( 'dgwt/wcas/tnt/search_results/suggestion/product', function ( $data, $suggestion ) {

    $html = '<div class="suggestion">';

    if ( ! empty( $suggestion->meta['pa_plataforma'] ) ) {
        $html .= $suggestion->meta['pa_plataforma'];
    }

    if ( ! empty( $suggestion->meta['pa_condicion'] ) ) {
        $html .= $suggestion->meta['pa_condicion'];
    }
        $html .= '</div>';
        $data['content_after'] = $html;

    return $data;
}, 10, 2 );
