<?php
// Si este archivo es llamado directamente, salgo.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Widget de prueba
 */
class Widget_single_product_ps extends WP_Widget {
 
    /**
     * Register widget with WordPress.
     */
    public function __construct() {
        parent::__construct(
            'widget_single_product_ps', // Base ID
            'PlanetShop Producto Simple', // Name
            array( 'description' => __( 'Widget para PlanetShop, se utiliza dentro de los productos simples.', 'text_domain' ), ) // Args
        );
    }
 
    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance ) {
        global $product;
        $upc = $product->get_sku();
        $lat = _GP_GEO_LAT;
        $lng = _GP_GEO_LNG;
        $tienda_fav = _GP_TIENDA_DEFAULT_ID;
        $id_cliente = 0;
        $direccion_larga = _GP_GEO_ADDRESS_LONG;

        $bandera_domicilio = false;
        $bandera_tienda = false;

        extract( $args );
        
        if(isset($_COOKIE['_gp_geo_address_long'])){
            $direccion_larga = urldecode(filter_var($_COOKIE['_gp_geo_address_long'], FILTER_SANITIZE_ENCODED));
        }

        if(isset($_COOKIE['_gp_geo_lat'])){
            $lat = filter_var($_COOKIE['_gp_geo_lat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        }

        if(isset($_COOKIE['_gp_geo_lng'])){
            $lng = filter_var($_COOKIE['_gp_geo_lng'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        }

        if(isset($_COOKIE['_gp_tienda_favorita_id'])){
            $tienda_fav = filter_var($_COOKIE['_gp_tienda_favorita_id'], FILTER_SANITIZE_ENCODED);
        }

        if(is_user_logged_in()){
            $user = get_userdata(get_current_user_id());
            if(isset($user->id_gp)){
                $id_cliente  = $user->id_gp;
            }
        }

        echo $before_widget;

        $response = $this->gp_wc_disponibilidad(1, "cache", $upc, $lat, $lng, $tienda_fav, $id_cliente);

        echo "
            <script>
                let gp_disponibilidad_var = {$response};
            </script>
        ";
        echo "
            <div class='gp-disponibilidad-container'>
                <div >
                    <div id='gp_content' class='gp_hi' role='main'>
                        <div class='row row-main'>
                            <div class='large-12 col' style='padding-bottom: 0px;'>
                                <div class='pr-field-wrap'>
        ";
        

        $ext_auth = json_decode($response, true);
        if($ext_auth['estatus']){
            foreach($ext_auth['tipos_envio'] as $key => $value){
                // error_log(print_r($value, true));
                $html = '';
                $gp_radio = '';
                $id_input_radio = '';
                $id_modal = '';
                $href_modal = '';
                $nombre_modal = '';
                $id_tiempo_entrega = '';
                $id_costo_envio = '';
                $direccion_domicilio = '';
                $id_mensaje = '';
                $mensaje = '';
                //*-- inputs id --
                $i_id_id_tienda = '';
                $i_id_nombre_tienda = '';
                $i_id_id_tipo_envio = '';
                $i_id_id_subtipo_envio = '';
                $i_id_entrega_estimada = '';
                $i_id_cantidad = '';
                $i_id_shipping = '';
                //*-- inputs values --
                $id_tienda = '';
                $nombre_tienda = '';
                $id_tipo_envio = '';
                $id_subtipo_envio = '';
                $entrega_estimada = '';
                $cantidad = '';
                $shipping = '';
                //*----
                if($value['estatus']){
                    if(count($value['subtipo']['almacenes']) == 0){
                        continue;
                    }
                    $lista = array(
                        'Entrega ' => ''
                    );
                    $entrega_estimada = str_replace(array_keys($lista), $lista, $value['subtipo']['entrega_estimada']);
                    foreach($value['subtipo']['almacenes'] as $key => $valor){
                        if($key == 0){
                            $id_tienda = $valor['id_sucursal'];
                            $nombre_tienda = $valor['nombre'];
                            $id_tipo_envio = $value['id'];
                            $id_subtipo_envio = $value['subtipo']['nombre'];
                            $entrega_estimada = $entrega_estimada;
                            $cantidad = $valor['cantidad'];
                            $shipping = $value['subtipo']['shipping']['valor'];
                            $monto_minimo = $value['subtipo']['monto_minimo']['valor'];
                            $monto_minimo_mensaje = $value['subtipo']['monto_minimo']['mensaje'];
                            break;
                        }
                    }

                    if($value['id'] == 'domicilio'){
                        $nombre_tienda = '';
                        $bandera_domicilio = true;
                        $id_label = 'domicilio_seleccionado';
                        $gp_radio .= 'gp_radio_recibir_domicilio';
                        $id_input_radio = 'domicilio';
                        $id_modal = 'gp_recibir_domicilio';
                        $href_modal = 'modal_cambiar_direccion';
                        $nombre_modal = 'Cambiar dirección';
                        $id_tiempo_entrega = 'gp_domicilio_tiempo_entrega';
                        $id_costo_envio = 'gp_domicilio_shipping';
                        $direccion_domicilio = "<span class='gp_single_product_direccion'>{$direccion_larga}<br/></span>";
                        $id_mensaje = 'gp_mensaje_domicilio';

                        $i_id_id_tienda = 'domicilio_id_tienda';
                        $i_id_nombre_tienda = 'domicilio_nombre_tienda';
                        $i_id_id_tipo_envio = 'domicilio_id_tipo_envio';
                        $i_id_id_subtipo_envio = 'domicilio_id_subtipo_envio';
                        $i_id_entrega_estimada = 'domicilio_entrega_estimada';
                        $i_id_cantidad = 'domicilio_cantidad';
                        $i_id_shipping = 'domicilio_shipping';

                        $i_id_gp_apartalo = 'gp_domicilio_apartalo';
                    } elseif($value['id'] == 'tienda' && $value['estatus']){
                        // if(count($value['subtipo']['almacenes']) == 0){
                        //     continue;
                        // }
                        $bandera_tienda = true;
                        $id_label = 'tienda_seleccionada';
                        $gp_radio .= 'gp_radio_recibir_sucursal';
                        $id_input_radio = 'tienda';
                        $id_modal = 'gp_recibir_tienda';
                        $href_modal = 'modal_recoger_tienda';
                        $nombre_modal = 'Cambiar de sucursal';
                        $id_tiempo_entrega = 'gp_sucursal_tiempo_entrega';
                        $id_costo_envio = 'gp_sucursal_shipping';
                        $id_mensaje = 'gp_mensaje';

                        $i_id_id_tienda = 'sucursal_id_tienda';
                        $i_id_nombre_tienda = 'sucursal_nombre_tienda';
                        $i_id_id_tipo_envio = 'sucursal_id_tipo_envio';
                        $i_id_id_subtipo_envio = 'sucursal_id_subtipo_envio';
                        $i_id_entrega_estimada = 'sucursal_entrega_estimada';
                        $i_id_cantidad = 'sucursal_cantidad';
                        $i_id_shipping = 'sucursal_shipping';

                        $i_id_gp_apartalo = 'gp_sucursal_apartalo';
                    }
                    if($value['estatus_mensaje_print']){
                        $mensaje = $value['estatus_mensaje'];
                    }

                    $mensaje_monto_minimo = '';
                    if(str_starts_with($upc, 'P')){
                        $mensaje_monto_minimo = "<span class='gp_c_b_green'><span>{$monto_minimo_mensaje}</span></span><br/>";
                        $html .= "
                            <input type='hidden' name='{$i_id_gp_apartalo}' id='{$i_id_gp_apartalo}' value='{$monto_minimo}'>
                        ";
                    }
                    $html .= "
                        <input type='hidden' name='{$i_id_id_tienda}' id='{$i_id_id_tienda}' value='{$id_tienda}'>
                        <input type='hidden' name='{$i_id_nombre_tienda}' id='{$i_id_nombre_tienda}' value='{$nombre_tienda}'>
                        <input type='hidden' name='{$i_id_id_tipo_envio}' id='{$i_id_id_tipo_envio}' value='{$id_tipo_envio}'>
                        <input type='hidden' name='{$i_id_id_subtipo_envio}' id='{$i_id_id_subtipo_envio}' value='{$id_subtipo_envio}'>
                        <input type='hidden' name='{$i_id_entrega_estimada}' id='{$i_id_entrega_estimada}' value='{$entrega_estimada}'>
                        <input type='hidden' name='{$i_id_cantidad}' id='{$i_id_cantidad}' value='{$cantidad}'>
                        <input type='hidden' name='{$i_id_shipping}' id='{$i_id_shipping}' value='{$shipping}'>
                        <span id='{$gp_radio}'>
                            <input type='radio' id='{$id_input_radio}' name='entrega' value='{$id_input_radio}'>
                            <label for='{$id_input_radio}' id='{$id_label}'>{$value['nombre']}</label>
                        </span>
                        <p>
                            <a id='{$id_modal}' href='#{$href_modal}' class='gp_underline' target='_self'>{$nombre_modal}</a><br/>
                            {$mensaje_monto_minimo}
                            <span class='gp_c_b_green'><span id='{$id_tiempo_entrega}'>{$value['subtipo']['entrega_estimada']}</span></span><br/>
                            <span class='gp_c_b_blue'><span id='{$id_costo_envio}'>{$value['subtipo']['shipping']['mensaje']}</span></span><br/>
                            {$direccion_domicilio}
                        </p>
                        <div id='{$id_mensaje}'>
                            <p>{$mensaje}</p>
                        </div>
                    ";

                    //!
                    $prod_id = $product->get_id();
                    $cats = get_the_terms( $prod_id, 'product_cat' );
                    $categorias = '';
                    if($cats){
                        $arreglo = [];
                        foreach ($cats  as $term  ) {
                            $product_cat_name = $term->name;
                            array_push($arreglo, $product_cat_name);
                            // break;
                        }
                        $categorias = implode(' - ', $arreglo);
                    }
                    
                    // $tags = get_the_terms( $prod_id, 'product_tag' );
                    $tags = get_the_terms( $prod_id, 'pa_plataforma' );
                    $plataforma = '';
                    if($tags){
                        $arreglo = [];
                        foreach ($tags  as $term  ) {
                            $product_tag_name = $term->name;
                            array_push($arreglo, $product_tag_name);
                            // break;
                        }
                        $plataforma = implode(' - ', $arreglo);
                    }
                    
                    $cond = get_the_terms( $prod_id, 'pa_condicion' );
                    $condicion = '';
                    if($cond){
                        $arreglo = [];
                        foreach ($cond  as $term  ) {
                            $product_tag_name = $term->name;
                            array_push($arreglo, $product_tag_name);
                            // break;
                        }
                        $condicion = implode(' - ', $arreglo);
                    }
                    //!

                    $sku_prod = $product->get_sku();
                    

                    $inp_lat = _GP_GEO_LAT;
                    if(isset($_COOKIE["_gp_geo_lat"])){
                        $inp_lat = htmlspecialchars($_COOKIE["_gp_geo_lat"]);
                    }
                    
                    $inp_lng = _GP_GEO_LNG;
                    if(isset($_COOKIE["_gp_geo_lng"])){
                        $inp_lng = htmlspecialchars($_COOKIE["_gp_geo_lng"]);
                    }

                    
                    
                    echo $html;
                }
            }
            $inp = "
                <input type='hidden' name='sku' id='sku' value='{$sku_prod}'>
                <input type='hidden' name='categoria' id='categoria' value='{$categorias}'>
                <input type='hidden' name='condicion' id='condicion' value='{$condicion}'>
                <input type='hidden' name='plataforma' id='plataforma' value='{$plataforma}'>
                <input type='hidden' name='lat' id='lat' value='{$inp_lat}'>
                <input type='hidden' name='lng' id='lng' value='{$inp_lng}'>
            ";

            echo $inp;

            if($bandera_domicilio || $bandera_tienda){
                woocommerce_template_single_add_to_cart();
            } else{
                ?>
                <div>
                    <p>Por el momento este producto no lo ofrecemos a la venta en línea, puedes
                        <?php if(!$bandera_preventa){?>
                            consultar la <a href="#modal_disponibilidad" target="_self" rel="nofollow" class="gp_disponibilidad gp_underline">disponibilidad en sucursales</a> y/o 
                        <?php } ?>
                        buscar más productos relacionados o parecidos a este en nuestro <a class="gp_underline" href="<?php echo site_url('/catalogo/?wpf=base_productos&wpf_con-inventario=1'); ?>">catálogo</a>. Code: W-003</p>
                </div>
                <?php
            }
        } elseif($ext_auth['estatus_mensaje_print']){
            $html = "
                <p>
                    {$ext_auth['estatus_mensaje']}
                </p>
            ";
            echo $html;
        } else{
            $html = "
                <p>
                    Por el momento este producto lo tenemos agotado, puedes buscar más productos relacionados ó parecidos a este producto en nuestro catálogo. Code: W-012
                </p>
            ";
            echo $html;
        }
        
        echo "
            </div>
            </div>
            </div>
            </div>
            </div>
            </div>
        ";
        echo $after_widget;
        $prod_id = $product->get_id();
        $cond = get_the_terms( $prod_id, 'pa__gp_msi' );
        $gp_msi = '';
        if($cond){
            $arreglo = [];
            foreach ($cond  as $term  ) {
                $product_tag_name = $term->name;
                array_push($arreglo, $product_tag_name);
                // break;
            }
            $gp_msi = implode(' - ', $arreglo);
        }
        if($gp_msi == 0){
            if($product->is_on_sale()){
                $precio = $product->get_sale_price();
            } else{
                $precio = $product->get_regular_price();
            }
            if($precio > 0){
                $precio_html = get_woocommerce_currency_symbol() . number_format($precio, 2, '.', ',');
                $plazos = array(12, 9, 6, 3); ?>
                <div class="gp-disponibilidad-container msi_div">
                    <label>Aplica meses sin intereses</label>
                    <div class="msi_table">
                        <table>
                            <tr>
                                <th>Plazo</th>
                                <th>Por mes</th>
                                <th>Costo de<br/>financiamiento</th>
                                <th>Total</th>
                            </tr>
                            <?php foreach($plazos as $key => $mes){
                                $costo_fin = $precio / $mes;
                                $costo_fin_html = get_woocommerce_currency_symbol() . number_format($costo_fin, 3, '.', ',');
                                echo "
                                    <tr>
                                        <td>{$mes} meses</td>
                                        <td>{$costo_fin_html}</td>
                                        <td>Gratis</td>
                                        <td>{$precio_html}</td>
                                    </tr>
                                ";
                            }?>                    
                        </table>
                    </div>
                </div>

            <?php }
        }
    }
 
    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance ) { ?>
        <p>
            <?php esc_html_e(""); ?>
        </p>
    <?php }
    public function gp_wc_disponibilidad( $cantidad, $metodo, $upc, $lat, $lng, $tienda_fav, $id_cliente){
    
        $response = array(
            "estatus" => 0,
            "estatus_mensaje_print" => 0,
            "estatus_mensaje" => "",
            "upc" => $upc,
            "garantias" => null,
            "tipos_envio" => null,
            "nombre" => null,
            "fecha_lanzamiento_confirmada" => null,
            "fecha_lanzamiento" => null,
            "precio_final_confirmado" => null,
            "precio_final" => null,
            "fecha_limite_apartado" => null,
            "fecha_limite_reasignacion_tienda" => null
        );
    
        try {
            $id_producto = wc_get_product_id_by_sku( $upc );
    
            if ( empty( $id_producto ) ) {
                throw new Exception('producto inexistente');
            }   
        } catch ( Exception $e ) {
            $response["estatus_mensaje_print"] = 1;
            $response["estatus_mensaje"] = "El producto no existe en nuestro catálogo. Code: F-001";
            return json_encode($response);
        }
        
        $producto = wc_get_product( $id_producto );
        $precio_producto = $producto->get_price();
        
        if ( !$producto->is_in_stock() ) {
            $response["estatus_mensaje_print"] = 1;
            $response["estatus_mensaje"] = "Por el momento este producto lo tenemos agotado, puedes buscar más productos relacionados ó parecidos a este producto en nuestro catálogo. Code: F-002";
            return json_encode($response);
        }
    
        $surtidor = 'GAM';
        $tipo_producto = 'fisico';
        $datos_domicilio = array(
            'express' => array(
                'solicitud' => $cantidad,
                'metodo' => $metodo,
                'cantidad_min' => 1
            ),
            'sameday' => array(
                'solicitud' => $cantidad,
                'metodo' => $metodo,
                'cantidad_min' => 1
            ),
            'nextday' => array(
                'solicitud' => $cantidad,
                'metodo' => $metodo,
                'cantidad_min' => 1
            ),
            'standard' => array(
                'solicitud' => $cantidad,
                'metodo' => $metodo,
                'cantidad_min' => 1
            )
        );
        $datos_tienda = array(
            'apartado' => array(
                'solicitud' => $cantidad,
                'metodo' => $metodo,
                'cantidad_min' => 1
            )
        );
    
        if(str_starts_with($upc, 'P')){
            $tipo_producto = 'preventa';
            $datos_domicilio = array(
                'preventa' => array(
                    'solicitud' => $cantidad,
                    'metodo' => $metodo,
                    'cantidad_min' => 1
                )
            );
            $datos_tienda = array(
                'preventa' => array(
                    'solicitud' => $cantidad,
                    'metodo' => $metodo,
                    'cantidad_min' => 1
                )
            );
        }
    
        $site = site_url();
        $disallowed = array('http://', 'https://');
        $origen = '';
        foreach($disallowed as $dis) {
            if(strpos($site, $dis) === 0) {
                $origen = str_replace($dis, '', $site);
            }
        }
        $is_admin = current_user_can('administrator');
        $request = array(
            "id_cliente" => $is_admin?0:$id_cliente,
            'productos' => array(
                array(
                    'upc' => $upc,
                    'surtidor' => $surtidor,
                    'origen' => $origen,
                    'tipo' => $tipo_producto,
                    'lat' => $lat,
                    'lng' => $lng,
                    'id_tienda_favorita' => $tienda_fav,
                    'id_tienda_seleccionada' => $tienda_fav,
                    'domicilio' => $datos_domicilio,
                    'tienda' => $datos_tienda,
                )
            )
        );
    
        $args = array(
            'body' => json_encode($request),
            'headers' => array(
                'Content-Type' => 'application/json',
                'data'         => get_option('data-tendero')
            )
        );
        $url = get_option('ruta_tendero') . "producto/disponibilidad2";
    
        $wp_response = wp_remote_post($url, $args);
        if (is_wp_error($wp_response)) {
            $mensaje_error = $wp_response->get_error_message();
            $response["estatus_mensaje_print"] = 1;
            $response["estatus_mensaje"] = 'Por el momento este producto no lo ofrecemos a la venta en línea, puedes consultar la disponibilidad en sucursales y/o buscar más productos relacionados a este en nuestro catálogo. Code: F-003';
            return json_encode($response);
        }
    
        if ($wp_response['response']['code'] == 200) {
            // obtenemos el body
            $ext_auth = json_decode($wp_response['body'], true);
    
            // Success == true
            if ($ext_auth['success']) {
    
                // cachamos el codigo ( 0 == operacion exitosa)
                if ($ext_auth['code'] == 0) {
                    $domicilio = $ext_auth['result'][0]['domicilio'];
                    $tienda = $ext_auth['result'][0]['tienda'];
                    $info_garantias = $ext_auth['result'][0]['garantias'];
                    $garantias = [];
                    gp_widgets_logs('widget', 'Response', $ext_auth);
                    
                    if(!empty($info_garantias)){
                        foreach($info_garantias as $garantia_individual){
                            if($precio_producto >= $garantia_individual['precio_minimo'] && $garantia_individual['precio_maximo'] >= $precio_producto){
                                $costo = null;
                                $calculo_tipo = $garantia_individual['calculo_tipo'];
                                switch($calculo_tipo){
                                    case 'F':{
                                        // $costo = round($garantia_individual['calculo_monto'], 0, PHP_ROUND_HALF_UP);
                                        $costo = $garantia_individual['calculo_monto'];
                                        break;
                                    }
                                    case 'P':{
                                        $porc = $garantia_individual['calculo_monto'];
                                        $porc_valor = ($precio_producto * $porc) / 100;
                                        $costo = round($porc_valor, 0, PHP_ROUND_HALF_UP) + 0.99;
                                        break;
                                    }
                                }
        
                                if(!is_null($costo)){
                                    if($garantia_individual['vigencia'] > 0){
                                        $garantias[] = array(
                                            'upc' => $garantia_individual['upc'],
                                            'nombre' => $garantia_individual['vigencia'] . " año(s) - " . $garantia_individual['nombre'],
                                            'costo' => $costo,
                                            'descripcion' => $garantia_individual['descripcion'],
                                            'instrucciones' => $garantia_individual['instrucciones'],
                                            'vigencia' => $garantia_individual['vigencia']
                                        );
                                    }
                                }
                            }
                        }
                    }
    
                    if(!empty($garantias)){
                        $garantias[] = array(
                            'upc' => "",
                            'nombre' => "No gracias, no deseo proteger mi producto",
                            'costo' => "Sin costo",
                            'vigencia' => 0
                        );
                    }
                    $response['garantias'] = $garantias;
                    if($tipo_producto == 'preventa'){
                        $response['nombre'] = $ext_auth['result'][0]['nombre'];
                        $response["fecha_lanzamiento_confirmada"] = $ext_auth['result'][0]["fecha_lanzamiento_confirmada"];
                        $response["fecha_lanzamiento"] = $ext_auth['result'][0]["fecha_lanzamiento"];
                        $response["precio_final_confirmado"] = $ext_auth['result'][0]["precio_final_confirmado"];
                        $response["precio_final"] = $ext_auth['result'][0]["precio_final"];
                        $response["fecha_limite_apartado"] = $ext_auth['result'][0]["fecha_limite_apartado"];
                        $response["fecha_limite_reasignacion_tienda"] = $ext_auth['result'][0]["fecha_limite_reasignacion_tienda"];
                    }
                    
                    $env_domicilio = [];
                    foreach($domicilio as $key => $tipo){
                        foreach($tipo['almacenes'] as $key => $almacen){
                            if($almacen['cantidad'] >= $cantidad){
                                if($tipo['subtipo'] == 'preventa'){
                                    if(isset($ext_auth['result'][0]['precio_final_confirmado']) && $ext_auth['result'][0]['precio_final_confirmado']){
                                        $entrega_estim = $tipo['disponibilidad']['entrega_estimada'];
                                        if(isset($ext_auth['result'][0]['fecha_lanzamiento_confirmada']) && !$ext_auth['result'][0]['fecha_lanzamiento_confirmada']){
                                            $entrega_estim = 'no definida';
                                            
                                            continue;
                                        }
                                        $env_domicilio += array(
                                            $tipo['subtipo'] =>  array(
                                                'id_tipo_envio' => 'domicilio',
                                                'id_subtipo_envio' => $tipo['subtipo'],
                                                'shipping' => $tipo['shipping'],
                                                'id_tienda' => $almacen['id'],
                                                'nombre_tienda' => $this->gp_short_name($almacen['nombre']),
                                                'ubicacion' => $almacen['ubicacion'],
                                                'entrega_estimada' => $entrega_estim,
                                                'disponible' => $tipo['disponible'],
                                                'cantidad' => $almacen['cantidad'],
                                                'precio_final' => $ext_auth['result'][0]['precio_final'],
                                                'fecha_limite_apartado' => $ext_auth['result'][0]['fecha_limite_apartado'],
                                                'monto_minimo' => $tipo['monto_minimo']
                                            )
                                        );
                                    }
                                } else{
                                    $env_domicilio += array(
                                        $tipo['subtipo'] =>  array(
                                            'id_tipo_envio' => 'domicilio',
                                            'id_subtipo_envio' => $tipo['subtipo'],
                                            'shipping' => $tipo['shipping'],
                                            'id_tienda' => $almacen['id'],
                                            'nombre_tienda' => $this->gp_short_name($almacen['nombre']),
                                            'ubicacion' => $almacen['ubicacion'],
                                            'entrega_estimada' => $tipo['disponibilidad']['entrega_estimada'],
                                            'disponible' => $tipo['disponible'],
                                            'cantidad' => $almacen['cantidad'],
                                            'precio_final' => 0,
                                            'fecha_limite_apartado' => '',
                                            'monto_minimo' => 0
                                        )
                                    );
                                }
                                break;
                            }
                        }
                    }
                    $opcion = '';
                    if(isset($env_domicilio['preventa'])){
                        $opcion = 'preventa';
                    } elseif(isset($env_domicilio['standard'])){
                        $opcion = 'standard';
                    } elseif(isset($env_domicilio['nextday'])){
                        $opcion = 'nextday';
                    } elseif(isset($env_domicilio['sameday'])){
                        $opcion = 'sameday';
                    } elseif(isset($env_domicilio['express'])){
                        $opcion = 'express';
                    }
    
                    $estatus_domicilio = 0;
                    if($opcion){
                        //! cálculo de costos
                        $estatus_domicilio = 1;
                        $seguro_envio = 0;
                        $costo_envalaje = 0;
                        $costo_manejo = 0;
                        $costo_activacion = 0;
                        $monto_minimo = 0;
                        if($tipo_producto == 'preventa'){
                            $monto_minimo_cart = 0;
                            $monto_minimo = $env_domicilio[$opcion]['monto_minimo'];
                            
                        }
                        //! ----
    
                        // $test = $env_domicilio[$opcion];
                        $response['tipos_envio'][] = array(
                            'id' => "domicilio",
                            'nombre' => "Entrega a domicilio",
                            'estatus' => $estatus_domicilio,
                            'estatus_mensaje_print' => 0,
                            'estatus_mensaje' => "Disponible para domicilio",
                            'subtipo' => array(
                                'nombre' => $opcion,
                                'entrega_estimada' => "Entrega " . $env_domicilio[$opcion]['entrega_estimada'],
                                'shipping' => array(
                                    'valor' => $env_domicilio[$opcion]['shipping'],
                                    'mensaje' => "Costo envío: $" . $env_domicilio[$opcion]['shipping'],
                                ),
                                'seguro_envio' => array(
                                    'valor' => $seguro_envio,
                                    'mensaje' => "Seguro envío: $" . $seguro_envio,
                                ),
                                'costo_envalaje' => array(
                                    'valor' => $costo_envalaje,
                                    'mensaje' => "Costo envalaje: $" . $costo_envalaje,
                                ),
                                'costo_manejo' => array(
                                    'valor' => $costo_manejo,
                                    'mensaje' => "Costo manejo: $" . $costo_manejo,
                                ),
                                'costo_activacion' => array(
                                    'valor' => $costo_activacion,
                                    'mensaje' => "Costo manejo: $" . $costo_activacion,
                                ),
                                'monto_minimo' => array(
                                    'valor' => $monto_minimo,
                                    'mensaje' => "Apártalo con: $" . $monto_minimo,
                                ),
                                'almacenes' => array( array(
                                    'id_sucursal' => $env_domicilio[$opcion]['id_tienda'],
                                    'nombre' => $this->gp_short_name($env_domicilio[$opcion]['nombre_tienda']),
                                    'cantidad' => $env_domicilio[$opcion]['cantidad'],
                                    'ubicacion' => $env_domicilio[$opcion]['ubicacion'],
                                    'direccion' => '',
                                    'telefono' => '',
                                    'horarios' => array(),
                                )
                                ),
                            ),
                            'shipping_cart' => 1,
                            'seguro_envio_cart' => 0,
                            'costo_embalaje_cart' => 0,
                            'costo_manejo_cart' => 0,
                            'costo_activacion_cart' => 0,
                            'monto_minimo_cart' => 0,
                            'garantia_cart' => 1,
                        );
                    } else{
                        $response['tipos_envio'][] = array(
                            'id' => "domicilio",
                            'nombre' => "Entrega a domicilio",
                            'estatus' => $estatus_domicilio,
                            'estatus_mensaje_print' => 0,
                            'estatus_mensaje' => "Este producto no está disponible a domicilio",
                            'subtipo' => null,
                            'shipping_cart' => 0,
                            'seguro_envio_cart' => 0,
                            'costo_embalaje_cart' => 0,
                            'costo_manejo_cart' => 0,
                            'costo_activacion_cart' => 0,
                            'monto_minimo_cart' => 0,
                            'garantia_cart' => 0,
                        );
    
                    }
    
                    //!
                    $disp_tienda = [];
                    $lista_almacenes = [];
                    // error_log(print_r($tienda, true));
                    foreach($tienda as $key => $tipo){
                        $nota = '';
                        $nombre = '';
                        $bandera_nota = 0;
                        $monto_minimo_cart = 0;
                        $estatus_tienda = 1;
                        
                        //! calcular costos
                        $seguro_envio = 0;
                        $costo_envalaje = 0;
                        $costo_manejo = 0;
                        $costo_activacion = 0;
                        $monto_minimo = 0;
                        
                        if($tipo_producto == 'preventa'){
                            $monto_minimo_cart = 0;
                            $monto_minimo = $tipo['monto_minimo'];
                            
                        }
                        //! ----
    
                        $subtipo = '';
                        if(isset($tipo['subtipo'])){
                            $subtipo = $tipo['subtipo'];
                        }
                        $shipping = 0;
                        if(isset($tipo['shipping'])){
                            $shipping = $tipo['shipping'];
                        }
                        $entrega_estimada = '';
                        if(isset($tipo['disponibilidad']['entrega_estimada'])){
                            $entrega_estimada = $tipo['disponibilidad']['entrega_estimada'];
                        }
                        $disp_global = 0;
                        foreach($tipo['almacenes'] as $key => $almacen){
                            if($key == 0){
                                $nombre = $this->gp_short_name($almacen['nombre']);
                                $id_tienda = $almacen['id'];
                                if($almacen['id'] != $tienda_fav){
                                    $nota = "La sucursal que tienes seleccionada de forma predeterminada no tiene el producto disponible, te recomendamos recogerlo en '" . $nombre . "'.";
                                    $bandera_nota = 1;
                                }
                            }

                            if($almacen['cantidad'] > 0){
                                $disp_global += 1;
                            }
                            $lista_almacenes[] = array(
                                'id_sucursal' => $almacen['id'],
                                'nombre' => $this->gp_short_name($almacen['nombre']),
                                'cantidad' => $almacen['cantidad'],
                                'ubicacion' => $almacen['ubicacion'],
                                'direccion' => $almacen['direccion'],
                                'telefono' => $almacen['telefono'],
                                'horarios' => $almacen['horarios']
                            );
                        }
                        if(count($lista_almacenes) == 0 || $disp_global == 0){
                            $estatus_tienda = 0;
                            $bandera_nota = 0;
                            $nota = 'Sin almacenes o productos';
                        }
                        $disp_tienda[] = array(
                            'id' => "tienda",
                            'nombre' => "Recoger en " . $nombre,
                            'estatus' => $estatus_tienda,
                            'estatus_mensaje_print' => $bandera_nota,
                            'estatus_mensaje' => $nota,
                            'subtipo' => array(
                                'nombre' => $subtipo,
                                'entrega_estimada' => "Entrega " . $entrega_estimada,
                                'shipping' => array(
                                    'valor' => $shipping,
                                    'mensaje' => "Costo envío: $" . $shipping,
                                ),
                                'seguro_envio' => array(
                                    'valor' => $seguro_envio,
                                    'mensaje' => "Seguro envío: $" . $seguro_envio,
                                ),
                                'costo_envalaje' => array(
                                    'valor' => $costo_envalaje,
                                    'mensaje' => "Costo envalaje: $" . $costo_envalaje,
                                ),
                                'costo_manejo' => array(
                                    'valor' => $costo_manejo,
                                    'mensaje' => "Costo manejo: $" . $costo_manejo,
                                ),
                                'costo_activacion' => array(
                                    'valor' => $costo_activacion,
                                    'mensaje' => "Costo manejo: $" . $costo_activacion,
                                ),
                                'monto_minimo' => array(
                                    'valor' => $monto_minimo,
                                    'mensaje' => "Apártalo con: $" . $monto_minimo,
                                ),
                                'almacenes' => $lista_almacenes,
                            ),
                            'shipping_cart' => 1,
                            'seguro_envio_cart' => 0,
                            'costo_embalaje_cart' => 0,
                            'costo_manejo_cart' => 0,
                            'costo_activacion_cart' => 0,
                            'monto_minimo_cart' => $monto_minimo_cart,
                            'garantia_cart' => 1,
                        );
                    }
                    //*
                    // $disp_tienda
                    foreach($disp_tienda as $valor){
                        $response['tipos_envio'][] = $valor;
                    }
                    //!
    
                    if($estatus_tienda || $estatus_domicilio){
                        $response["estatus"] = 1;
                        if($estatus_tienda && $estatus_domicilio){
                            $response["estatus_mensaje"] = "Disponible en todas las modalidades";
                        } elseif($estatus_tienda){
                            $response["estatus_mensaje"] = "Disponible solo en sucursal";
                        } else{
                            $response["estatus_mensaje"] = "Disponible solo a domicilio";
                        }
                    }
    
                } else{
                    // código 
                    $response["estatus_mensaje"] = "Código de respuesta: " . $ext_auth['code'];
                    $response["estatus_mensaje"] = 'Por el momento este producto no lo ofrecemos a la venta en línea, puedes consultar la disponibilidad en sucursales y/o buscar más productos relacionados a este en nuestro catálogo. Code: F-006';
                    return json_encode($response);
                }
            } else{
                // success false
                $response["estatus_mensaje"] = 'Por el momento este producto no lo ofrecemos a la venta en línea, puedes consultar la disponibilidad en sucursales y/o buscar más productos relacionados a este en nuestro catálogo. Code: F-005';
                return json_encode($response);
            }
        } else{
            // respuesta http
            $response["estatus_mensaje"] = 'Por el momento este producto no lo ofrecemos a la venta en línea, puedes consultar la disponibilidad en sucursales y/o buscar más productos relacionados a este en nuestro catálogo. Code: F-004';
            return json_encode($response);
        }
    
        return json_encode($response);
    }

    public function gp_short_name($nombre){
        $short_name = '';
        $lista = array(
            'Gameplanet' => 'GP'
        );
        $short_name = str_replace(array_keys($lista), $lista, $nombre);
        return $short_name;
    }
 
} // class T_Disponibilidad
