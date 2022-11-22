<?php
// function wc_display_item_meta( $item, $args = array() ) {
//     $strings = array(
//         'Categoría' => '',
//         'espacio' => '<p style="margin-bottom: 1em !important;"> </p>',
//         'Ticket' => '',
//         'Estatus' => '',
//         'Entrega' => '',
//         'Precio final' => '',
//         'Apartado con' => '',
//         'Costo envío' => '',
//         'Recoge en' => '',
//         'Recoge de' => '',
//         'Fecha procesado' => '',
//         'espacio1' => '<p style="margin-bottom: 1em !important;"> </p>',
//     );
//     $html    = '';
//     $args    = wp_parse_args(
//         $args,
//         array(
//             'before'       => '<div class="wc-item-meta "><p class="gp-p-meta">',
//             'after'        => '</p></div>',
//             'separator'    => '</p><p class="gp-p-meta">',
//             'echo'         => true,
//             'autop'        => true,
//             'label_before' => '<strong class="wc-item-meta-label">',
//             'label_after'  => '</strong> ',
//         )
//     );

//     $item_estatus = $item['_gp_estatus'];
//     $item_tipo = $item['_gp_id_tipo_envio'];
//     foreach ( $item->get_all_formatted_meta_data() as $meta_id => $meta ) {
//         $value = $args['autop'] ? wp_kses_post( $meta->display_value ) : wp_kses_post( make_clickable( trim( $meta->display_value ) ) );
        
//         switch($meta->display_key){

//             // sin etiqueta
//             case 'Categoría':{
//                 $strings[$meta->display_key] = wp_strip_all_tags($value);
//                 break;
//             }

//             // con etiqueta
//             case 'Fecha procesado':
//             case 'Recoge de':{
//                 $texto = $args['label_before'] . ( $meta->display_key ) . $args['label_after'] . ' <span >' . wp_strip_all_tags($value) . '</span>';
//                 $meses = array(
//                     'January' => 'enero',
//                     'February' => 'febrero',
//                     'March' => 'marzo',
//                     'April' => 'abril',
//                     'May' => 'mayo',
//                     'June' => 'junio',
//                     'July' => 'julio',
//                     'August' => 'agosto',
//                     'September' => 'septiembre',
//                     'October' => 'octubre',
//                     'November' => 'noviembre',
//                     'Decembe' => 'diciembre',
//                 );
//                 $texto = str_ireplace(  array_keys($meses),  $meses,  $texto );
//                 $strings[$meta->display_key] = $texto;
//                 break;
//             }

//             case 'Precio final':
//             case 'Ticket':
//             case 'Tienes':{
//                 $strings[$meta->display_key] = $args['label_before'] . ( $meta->display_key ) . $args['label_after'] . ' <span >' . wp_strip_all_tags($value) . '</span>';
//                 break;
//             }

//             case 'Apartado con':
//             case 'Apártalo con':{
//                 $strings['Apartado con'] = '<span class="gp_item_status_verde">' . ( $meta->display_key ) . ' <span>' . wp_strip_all_tags($value) . '</span></span>';
//             }


//             case 'Costo envío':
//             case 'Entrega':{
//                 if($item_tipo == 'domicilio'){
//                     if($meta->display_key == 'Costo envío'){
//                         $strings[$meta->display_key] = '<span class="gp_item_status_azul">' . ( $meta->display_key ) . ' <span>' . wp_strip_all_tags($value) . '</span></span>';
//                     } else{
//                         $strings[$meta->display_key] = '<span class="gp_item_status_verde">' . ( $meta->display_key ) . ' <span>' . wp_strip_all_tags($value) . '</span></span>';
//                     }
//                 } else{
//                     unset($strings[$meta->display_key]);
//                 }
//                 break;
//             }

//             // forzar borrado
//             case 'Plataforma':
//             case 'Condición':{
//                 break;
//             }

//             // especiales
//             case 'Recoge en':{
//                 $strings[$meta->display_key] = '<span class="gp_entrega_en">' . ( $meta->display_key ) . ' <span>' . wp_strip_all_tags($value) . '</span></span>';
//                 break;
//             }
            
//             case 'Estatus':{
//                 if($item_estatus == 'ok'){
//                     $strings[$meta->display_key] = '<span class="gp_item_status_verde">' . ( $meta->display_key ) . ' <span>' . wp_strip_all_tags($value) . '</span></span>';
//                 } elseif($item_estatus == 'fail'){
//                     $strings[$meta->display_key] = '<span class="gp_item_status_rojo">' . ( $meta->display_key ) . ' <span>' . wp_strip_all_tags($value) . '</span></span>';
//                 } else{
//                     $strings[$meta->display_key] = $args['label_before'] . ( $meta->display_key ) . $args['label_after'] . ' <span >' . wp_strip_all_tags($value) . '</span>';
//                 }
//                 break;
//             }

//             default:{
//                 $strings[$meta->display_key] = $args['label_before'] . ( $meta->display_key ) . $args['label_after'] . $value;
//                 break;
//             }
//         }
//     }

//     if ( $strings ) {
//         $html = $args['before'] . implode( $args['separator'], array_filter($strings) ) . $args['after'];
//     }

//     $html = apply_filters( 'woocommerce_display_item_meta', $html, $item, $args );

//     if ( $args['echo'] ) {
//         // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
//         echo $html;
//     } else {
//         return $html;
//     }
// }

