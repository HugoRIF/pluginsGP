<?php
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Make sure WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) return;
function gp_gateway_init()
{

    class Gp_Gateway extends WC_Payment_Gateway
    {

        // constructor de la clase
        public function __construct()
        {
            $this->saldo = 0;
            $this->id = 'saldo_gp';
            $this->has_fields = false;
            $this->method_title = 'Crédito Gameplanet';
            $this->method_description = 'Permite a tus clientes pagar con saldo Gameplanet.';
            $this->init_form_fields();
            $this->init_settings();
            $this->icon = $this->get_icon();
            $this->title = $this->settings['title'];
            $this->description = $this->settings['description'];


            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        }

        // agrega mensaje con cantidad de saldo
        public function get_icon()
        {
            if(!is_user_logged_in()){
                add_filter('woocommerce_available_payment_gateways', 'gp_wallet_unset', 10, 1);
                return false;
            }
            if (is_checkout() && empty(is_wc_endpoint_url('order-received'))) {
                $this->gp_saldo_log("get_icon", "Inicio conseguir saldo");
                if(is_null(WC()->cart)){
                    $total_carrito = 0;
                } else{
                    $total_carrito = WC()->cart->get_total("");
                }

                // obtiene datos del usuario
                $user = get_userdata(get_current_user_id());
                // guarda valores si cuenta con token y id_gp
                if ((isset($user->token) && isset($user->id_gp)) && $user->token != '' && $user->id_gp != '') {
                    $this->gp_saldo_log("get_icon", "Buscar saldo de: " . $user->id_gp);
                    $token = $user->token;
                    $id_gp = $user->id_gp;
                    // no ha iniciado sesión o no tiene token/id_gp
                } else {
                    // elimina método de pago
                    if (is_user_logged_in()) {
                        $this->gp_saldo_log("get_icon", "Error. gp_token/id_gp no encontrado. Usuario: " . $user->ID . "\n--------------------------------------------------------------");
                    } else {
                        $this->gp_saldo_log("get_icon", "Usuario no inició sesión.\n--------------------------------------------------------------");
                    }
                    add_filter('woocommerce_available_payment_gateways', 'gp_wallet_unset', 10, 1);
                    $userdata = array(
                        'ID'       => $user->ID,
                        'saldo_gp' => -5
                    );
                    $user_data = wp_update_user($userdata);
                    remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
                    wc_add_notice('Lo sentimos, no pudimos obtener tu información. Te pedimos que vuelvas a iniciar sesión e intentarlo nuevamente.', 'error');
                    return;
                }

                // llamamos "telefonero" para obtener saldo
                $url = get_option('ruta_telefonero') . "cliente/saldo/" . $token . "/" . $id_gp;
                $this->gp_saldo_log("get_icon", "ENDPOINT: " . $url);
                // $url = get_option('ruta_telefonero') . "cliente/saldo/" . $token ;
                $response = wp_remote_get($url);

                // elimina opción de pago si existe un error al llamar telefonero
                if (is_wp_error($response)) {
                    $this->gp_saldo_log("get_icon", "Error WP: " . $response->get_error_message());
                    add_filter('woocommerce_available_payment_gateways', 'gp_wallet_unset', 10, 1);
                    $userdata = array(
                        'ID'       => $user->ID,
                        'saldo_gp' => -1
                    );
                    $user_data = wp_update_user($userdata);
                    return;
                }
                $this->gp_saldo_log("get_icon", "RESPONSE: ", $response);

                // si hay respuesta de telefonero
                if ($response['response']['code'] == 200) {
                    // decifrar información
                    $result = json_decode($response['body'], true);

                    /* si todo es correcto actualizo saldo para admin
                     * si el saldo es menor que el total del carrito, elimino método de pago
                     */
                    if ($result['success']) {
                        if ($result['code'] == 0) {

                            // se guarda para informacion en admin
                            $userdata = array(
                                'ID'       => $user->ID,
                                'saldo_gp' => $result['result']
                            );
                            $user_data = wp_update_user($userdata);
                            if (is_wp_error($user_data)) {
                                $this->gp_saldo_log('get_icon', "Error wordpress al actualizar saldo a usuario: " . $user->ID . "\n--------------------------------------------------------------");
                                error:log("error"."Algo salió mal, inténtelo más tarde. Code:S-001");
                                add_filter('woocommerce_available_payment_gateways', 'gp_wallet_unset', 10, 1);
                                return false;
                            }

                            // si el saldo es menor al total del carrito
                            if ($total_carrito > $result['result']) {
                                $this->gp_saldo_log('get_icon', "Saldo insuficiente\n--------------------------------------------------------------");
                                add_filter('woocommerce_available_payment_gateways', 'gp_wallet_unset', 10, 1);
                                return false;
                            } else {
                                $this->saldo = $result['result'];
                                $this->gp_saldo_log('get_icon', "Saldo suficiente.\n--------------------------------------------------------------");
                                // muestro saldo en método de pago
                                return " <span class='gp_saldo_g'>| Tu saldo es: $" . number_format($this->saldo, 2, ".", ",") . "</span>";
                            }
                        } else {
                            $this->gp_saldo_log('get_icon', "Código no esperado: " . $result['code'] . "\n--------------------------------------------------------------");
                            add_filter('woocommerce_available_payment_gateways', 'gp_wallet_unset', 10, 1);
                            $userdata = array(
                                'ID'       => $user->ID,
                                'saldo_gp' => -2
                            );
                            $user_data = wp_update_user($userdata);
                            return false;
                        }
                    } else {
                        $this->gp_saldo_log('get_icon', "'Success' no esperado.\n--------------------------------------------------------------");
                        add_filter('woocommerce_available_payment_gateways', 'gp_wallet_unset', 10, 1);
                        $userdata = array(
                            'ID'       => $user->ID,
                            'saldo_gp' => -3
                        );
                        $user_data = wp_update_user($userdata);
                        return false;
                    }
                } else {
                    $this->gp_saldo_log('get_icon', "Respuesta no esperada: " . $response['response']['code'] . "\n--------------------------------------------------------------");
                    add_filter('woocommerce_available_payment_gateways', 'gp_wallet_unset', 10, 1);
                    $userdata = array(
                        'ID'       => $user->ID,
                        'saldo_gp' => -4
                    );
                    $user_data = wp_update_user($userdata);
                    return false;
                }
                // add_filter('woocommerce_available_payment_gateways', 'gp_wallet_unset', 10, 1);
                // return false;
            } else{
                // add_filter('woocommerce_available_payment_gateways', 'gp_wallet_unset', 10, 1);
                // return false;
            }
            
            
        }

        // agrega campos en "pagos" [woocommerce]
        public function init_form_fields()
        {
            $this->form_fields = array(
                'enabled' => array(
                    'title'       => __('Enable/Disable', 'woocommerce'),
                    'label'       => __('Activar GamePlanet Wallet', 'woocommerce'),
                    'type'        => 'checkbox',
                    'description' => '',
                    'default'     => 'no'
                ),
                'title' => array(
                    'title'       => __('Title', 'woocommerce'),
                    'type'        => 'text',
                    'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),
                    'default'     => __('Crédito Gameplanet', 'woocommerce'),
                    'desc_tip'    => false
                ),
                'description' => array(
                    'title'       => __('Description', 'woocommerce'),
                    'type'        => 'textarea',
                    'description' => __('This controls the description which the user sees during checkout.', 'woocommerce'),
                    'default'     => __("Pago con " . $this->title, 'woocommerce'),
                    'desc_tip'    => false
                ),
                'instructions' => array(
                    'title'       => __('Instrucciones', 'woocommerce'),
                    'type'        => 'textarea',
                    'description' => __('Instrucciones que se agregarán en "thank you" e "email".', 'woocommerce'),
                    'default'     => '',
                    'desc_tip'    => false
                )
            );
        }

        // procesar pago
        public function process_payment($order_id)
        {
            wc_add_notice("SALDO FAKE Algo salió mal, inténtelo nuevamente o use otro método de pago. Si el problema continúa, comuníquese al <a href='tel:+5550475954'>+55 5047 5954</a> y lo resolveremos.", 'error');
            return;
            $this->gp_saldo_log('process_payment', '1. Inicio proceso de cobro saldo GP');
            $nota_orden = "<strong>** Saldo GP **</strong>" . PHP_EOL;

            $order = wc_get_order($order_id);
            $user = get_userdata(get_current_user_id());

            // $aux_orden = $order->get_order_number();
            // $orden = '7' . sprintf('%08d', $aux_orden);
            // $ticket = $orden . date_timestamp_get(date_create()) . '01';
            //! modificación PS
            $ticket = $order->get_order_number();

            $monto = $order->get_total();
            if ($monto > $user->saldo_gp) {
                wc_add_notice("Algo salió mal, inténtelo nuevamente o use otro método de pago. Si el problema continúa, comuníquese al <a href='tel:+5550475954'>+55 5047 5954</a> y lo resolveremos.", 'error');
                return;
            }

            // argumentos para consumo de salgo gp
            $args = array(
                'body' => json_encode(array(
                    'id_cliente' => $user->id_gp,
                    'gp_token'   => $user->token,
                    'ticket'     => $ticket,
                    'monto'      => $monto
                )),
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'data'         => get_option('data-telefonero')
                )
            );
            $url = get_option('ruta_telefonero') . "cliente/saldo/consumo";

            $this->gp_saldo_log('process_payment', '2. Endpoint: ' . $url . ' || (POST)');
            $this->gp_saldo_log('process_payment', '3. BODY/HEADER ',  $args);

            // response de la petición
            $response = wp_remote_post($url, $args); //!

            // error de timeout o inalcanzable
            if (is_wp_error($response)) {
                $mensaje_error = $response->get_error_message();
                $this->gp_saldo_log('process_payment', 'Error Wordpress: ' . $mensaje_error);
                $this->gp_saldo_log('process_payment', "Proceso detenido. Salgo cobro saldo GP\n--------------------------------------------------------------");
                $nota_orden .= "1. Error WP: " . $mensaje_error . PHP_EOL;
                $order->add_order_note($nota_orden);
                $order->update_status('failed', "<strong>** Saldo GP **</strong>" . PHP_EOL);
                $order->save();
                wc_add_notice("Algo salió mal, inténtelo nuevamente o use otro método de pago. Si el problema continúa, comuníquese al <a href='tel:+5550475954'>+55 5047 5954</a> y lo resolveremos. Code:S-002", 'error');
                return;
            }

            // log de respuesta
            $this->gp_saldo_log('process_payment', 'RESPONSE ',  $response);

            // cachamos codigo http (['response']['code'])
            if ($response['response']['code'] == 200) {
                // obtenemos el body
                $ext_auth = json_decode($response['body'], true); //!

                // Success == true
                if ($ext_auth['success']) {

                    // cachamos el codigo ( 0 == operacion exitosa)
                    if ($ext_auth['code'] == 0) {
                        // Success!
                        $this->gp_saldo_log('process_payment', '5. Saldo consumido');
                        // borra carrito
                        WC()->cart->empty_cart();

                        $nota_orden .= "1. Saldo consumido" . PHP_EOL .
                            "Ticket: " . $ticket . PHP_EOL .
                            "#referencia: " . $ext_auth['result'] . PHP_EOL;
                        $order->add_order_note($nota_orden);

                        // guarda orden y ticket para siguiente proceso
                        $order->update_meta_data('_ticket', $ticket);
                        $order->update_meta_data('_id_gp', $user->id_gp);

                        // se marca como "procesado" ya consumimos saldo
                        $this->gp_saldo_log('process_payment', '6. Orden marcada como procesada');

                        // se actualiza a "procesado"
                        $order->update_status('processing', "<strong>** Saldo GP **</strong>" . PHP_EOL);
                        // $order->payment_complete($ext_auth['result']);
                        add_post_meta($order->get_id(), '_transaction_id', $ext_auth['result'], true);

                        $this->gp_saldo_log('process_payment', '8. Redireccionando a thankyou');
                        $this->gp_saldo_log('process_payment', "Fin proceso de cobro saldo GP\n--------------------------------------------------------------");

                        // redireccionar a página "thankyou"
                        return array(
                            'result'   => 'success',
                            'redirect' => $this->get_return_url($order)
                        );
                    } else {

                        $nota_orden .= "1. PROCESO DETENIDO" . PHP_EOL . "Se ha regresado un código negativo: " . $ext_auth['code'] . PHP_EOL . "Mensaje: " . $ext_auth['message'] . PHP_EOL;
                        $order->add_order_note($nota_orden);

                        $order->update_status('failed', "<strong>** Saldo GP **</strong>" . PHP_EOL);

                        $this->gp_saldo_log('process_payment', '5. Proceso detenido. Código inválido: ' . $ext_auth['code'] . '. Mensaje: ' . $ext_auth['message']);
                        $this->gp_saldo_log('process_payment', '6. Orden marcada como fallida');

                        $this->gp_saldo_log('process_payment', "Fin proceso de cobro saldo GP\n--------------------------------------------------------------");

                        // mensaje de error 
                        wc_add_notice("Algo salió mal, inténtelo nuevamente o use otro método de pago. Si el problema continúa, comuníquese al <a href='tel:+5550475954'>+55 5047 5954</a> y lo resolveremos!", 'error');
                        return;
                    }
                } else {
                    $nota_orden .= "1. PROCESO DETENIDO" . PHP_EOL .
                        "Consumo fallido" . PHP_EOL .
                        "Código: " . $ext_auth['code'] . PHP_EOL .
                        "Mensaje: " . $ext_auth['message'] . PHP_EOL;
                    $order->add_order_note($nota_orden);

                    $order->update_status('failed', "<strong>** Saldo GP **</strong>" . PHP_EOL);

                    $this->gp_saldo_log('process_payment', '5. Proceso detenido. Código inválido: ' . $ext_auth['code'] . '. Mensaje: ' . $ext_auth['message']);
                    $this->gp_saldo_log('process_payment', '6. Orden marcada como fallida');

                    $this->gp_saldo_log('process_payment', "Fin proceso de cobro saldo GP\n--------------------------------------------------------------");
                    wc_add_notice("Algo salió mal, inténtelo nuevamente o use otro método de pago. Si el problema continúa, comuníquese al <a href='tel:+5550475954'>+55 5047 5954</a> y lo resolveremos!", 'error');
                    return;
                }
            } else {
                $nota_orden .= "1. PROCESO DETENIDO" . PHP_EOL .
                    "Respuesta fallida" . PHP_EOL .
                    "Respuesta: " . $response['response']['code'] . PHP_EOL .
                    "Mensaje: " . $response['response']['message'] . PHP_EOL;
                $order->add_order_note($nota_orden);

                $order->update_status('failed', "<strong>** Saldo GP **</strong>" . PHP_EOL);

                $this->gp_saldo_log('process_payment', '5. Proceso detenido. Respuesta fallida: ' . $response['response']['code'] . " Mensaje: " . $response['response']['message']);
                $this->gp_saldo_log('process_payment', '6. Orden marcada como fallida');

                $this->gp_saldo_log('process_payment', "Fin proceso de cobro saldo GP\n--------------------------------------------------------------");
                add_filter('woocommerce_available_payment_gateways', 'gp_wallet_unset', 10, 1);
                wc_add_notice("Algo salió mal, inténtelo nuevamente o use otro método de pago. Si el problema continúa, comuníquese al <a href='tel:+5550475954'>+55 5047 5954</a> y lo resolveremos!", 'error');
                return;
            }
        }
        
        public function gp_saldo_log($funcion, $paso, $entry = null) {
            if(!is_ajax()){
                return false;
            }
    
            $directorio = "./wp-content/gp/logs_saldo/";
            $extencion = "_gp_saldo.log";
    
            if (!file_exists($directorio)) {
                mkdir($directorio, 0755, true);
            }
            $tiempo = current_time('mysql');
            $fecha = strtotime($tiempo);
            $fecha_log = date('M-d', $fecha);
    
            $file = fopen($directorio . $fecha_log . $extencion, "a") or fopen($directorio . $fecha_log . $extencion, "w");
    
            if (is_null($entry)) {
                $registro = $tiempo . " :: Función: " . $funcion . " || " . $paso . "\n";
            } else {
    
                if (is_array($entry)) {
                    $entry = json_encode($entry);
                }
    
                $registro = $tiempo . " :: Función: " . $funcion . " || " . $paso . " || " . $entry . "\n";
            }
    
            $bytes = fwrite($file, $registro);
            fclose($file);
    
            return $bytes;
        }
    }
}

// para quitar opción de pago
function gp_wallet_unset($gateways)
{
    unset($gateways['saldo_gp']);
    return $gateways;
}
