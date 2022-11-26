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

        echo "
            <div id='gp_div_disp'>
                <noscript>
                    <div>
                        Notamos que tu navegador no es compatible con JavaScript o lo tienes desactivado, por favor, asegúrate de utilizar JavaScript para una mejor experiencia.
                    </div>
                </noscript>
                <div style='margin-top: 5em;'>
                    <center>
                        <span class='loader-general blue'></span>
                    </center>
                </div>
            </div>
            
            <div id='gp_div_msi'>
            </div>
            <a id='btn_tiendas_disp' href='#modal_disp_tiendas' target='_self'></a>
            <div id='modal_disp_tiendas' class='lightbox-by-id lightbox-content lightbox-white mfp-hide' style='max-width:45em ;padding:1.5em;'>
            </div>
            <a id='btn_disp_prod' href='#modal_disp_prod' target='_self'></a>
            <div id='modal_disp_prod' class='lightbox-by-id lightbox-content lightbox-white mfp-hide' style='max-width:45em ;padding:1.5em;'>
            </div>
            <a id='btn_mensajes_errores' href='#modal_mensajes_errores' target='_self'></a>
            <div id='modal_mensajes_errores' class='lightbox-by-id lightbox-content lightbox-white mfp-hide' style='max-width:45em ;padding:1.5em;'>
                <h2>Lo sentimos</h2>
                <div id='gp_mensaje_domicilio'>
                </div>
            </div>
        ";
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
                'solicitud' => 1,
                'metodo' => $metodo,
                'cantidad_min' => $cantidad
            ),
            'sameday' => array(
                'solicitud' => 1,
                'metodo' => $metodo,
                'cantidad_min' => $cantidad
            ),
            'nextday' => array(
                'solicitud' => 1,
                'metodo' => $metodo,
                'cantidad_min' => $cantidad
            ),
            'standard' => array(
                'solicitud' => 1,
                'metodo' => $metodo,
                'cantidad_min' => $cantidad
            )
        );
        $datos_tienda = array(
            'apartado' => array(
                'solicitud' => 1,
                'metodo' => $metodo,
                'cantidad_min' => $cantidad
            )
        );
    
        if(str_starts_with($upc, 'P')){
            $tipo_producto = 'preventa';
            $datos_domicilio = array(
                'preventa' => array(
                    'solicitud' => 1,
                    'metodo' => $metodo,
                    'cantidad_min' => $cantidad
                )
            );
            $datos_tienda = array(
                'preventa' => array(
                    'solicitud' => 1,
                    'metodo' => $metodo,
                    'cantidad_min' => $cantidad
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
        error_log("current_cliente:".$id_cliente);
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

                    $bandera_domicilio_disp = false;
                    $bandera_cantidad = false;
                    $bandera_preventa = false;
                    $bandera_preventa_var = false;
                    $bandera_domicilio_almacenes = false;
                    
                    $precio_preventa_confirmada = false;
                    $entrega_preventa_confirmada = false;
                    if($tipo_producto == 'preventa'){
                        if(isset($ext_auth['result'][0]['precio_final_confirmado']) && $ext_auth['result'][0]['precio_final_confirmado']){
                            $precio_preventa_confirmada = true;
                        }
                        if(isset($ext_auth['result'][0]['fecha_lanzamiento_confirmada']) && !$ext_auth['result'][0]['fecha_lanzamiento_confirmada']){
                            $entrega_preventa_confirmada = true;
                        }
                    }
                    
                    foreach($domicilio as $key => $tipo){
                        $bandera_domicilio_disp = true;
                        foreach($tipo['almacenes'] as $key => $almacen){
                            $bandera_domicilio_almacenes = true;
                            if($almacen['cantidad'] >= $cantidad){
                                $bandera_cantidad = true;
                                if($tipo['subtipo'] == 'preventa'){
                                    $bandera_preventa = true;
                                    if($precio_preventa_confirmada){
                                        $entrega_estim = $tipo['disponibilidad']['entrega_estimada'];
                                        if($entrega_preventa_confirmada){
                                            $entrega_estim = 'no definida';
                                            continue;
                                        }
                                        $bandera_preventa_var = true;
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
                        //* no se tiene opción de envío
                        if($bandera_domicilio_disp){
                            if($bandera_domicilio_almacenes){
                                if($bandera_cantidad){
                                    if($bandera_preventa){
                                        if($bandera_preventa_var){
                                            //* entra como preventa, pero no está en lista de opciones [express, same day, etc]
                                            $estatus_mensaje_txt = "Por el momento no hay disponibilidad de entrega a domicilio para este producto. <span style='color: white;'>Code: W-102</span>";
                                        } else{
                                            //* no se ha confirmado el precio final y/o fecha de lanzamiento
                                            $estatus_mensaje_txt = "Por el momento este producto no ofrece la opción de 'Entrega a domicilio', debido a que el precio final y/o la fecha de lanzamiento no se han confirmado.<span style='color: white;'>Code: W-103</span>";
                                        }
                                    } else{
                                        //* no es preventa
                                        $estatus_mensaje_txt = 'Por el momento no hay disponibilidad de "Entrega a domicilio" para esta dirección de envío, te sugerimos <a href="#" id="gp_error_cambio_dir" class="gp_underline">cambiar la dirección de envío.</a> <span style="color: white;">Code: W-101</span>';
                                    }
                                } else{
                                    //* sin envío a domicilio
                                    $estatus_mensaje_txt = "Por el momento este producto no ofrece la opción de 'Entrega a domicilio'.<span style='color: white;'>Code: W-104</span>";
                                }
                            } else{
                                if($tipo_producto == 'preventa' && !$precio_preventa_confirmada){
                                    //* precio preventa no confirmada
                                    // $estatus_mensaje_txt = "Por el momento no hay disponibilidad de entrega a domicilio para este producto. <span style='color: white;'>Code: W-105</span>";
                                    $estatus_mensaje_txt = "Por el momento este producto no ofrece la opción de 'Entrega a domicilio', debido a que el precio final y/o la fecha de lanzamiento no se han confirmado.<span style='color: white;'>Code: W-105</span>";
                                } else{
                                    //* sin respuesta de almacenes
                                    $estatus_mensaje_txt = 'Por el momento no hay disponibilidad de "Entrega a domicilio" para esta dirección de envío.</span> <span style="color: white;">Code: W-101.1</span>';
                                }
                            }
                        } else{
                            //* sin envío a domicilio
                            $estatus_mensaje_txt = "Por el momento no hay disponibilidad de 'Entrega a domicilio' para este producto. <span style='color: white;'>Code: W-100</span>";
                        }
                        $response['tipos_envio'][] = array(
                            'id' => "domicilio",
                            'nombre' => "Entrega a domicilio",
                            'estatus' => $estatus_domicilio,
                            'estatus_mensaje_print' => 1,
                            'estatus_mensaje' => $estatus_mensaje_txt,
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
                            $nota = 'No sabemos si este producto volverá a estar disponible, ni cuándo.';
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
