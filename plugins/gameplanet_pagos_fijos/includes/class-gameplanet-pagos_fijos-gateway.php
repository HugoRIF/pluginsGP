<?php
if (!defined('WPINC')) {
  die;
}
class Gp_Openpay_Gateway extends WC_Payment_Gateway
{

  // constructor de la clase
  public function __construct()
  {
    $this->openpay = 0;
    $this->id = 'gp_pagos_fijos';
    $this->has_fields = true;
    $this->method_title = 'GP Openpay';
    $this->title = 'Tarjeta de debito o credito';
    $this->method_description = 'Permite pagar con trajetas de credito y debito (gameplanet_pagos_fijos).';
    $this->init_form_fields();
    $this->init_settings();
    
    //$this->icon = $this->get_op_icon();
    $this->description = $this->settings['description']??'';
    $this->order = null;
    $this->is_sandbox = $this->settings['sandbox']=='no'?false:true;
    $this->api_url_sandbox = "https://sandbox-api.openpay.mx/v1/";
    $this->api_url = "https://api.openpay.mx/v1/";
    $this->nonce = "";
    $this->with_preauth = false;
    $this->supports = $this->get_supports($this->supports);
    add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

  }

  // agrega mensaje con cantidad de openpay
  public function get_icon()
  {
    $accept_msi = gp_pagos_fijos_obtener_msi_disponibilidad();
    if($accept_msi){
      echo('
          <div class="gp_tooltip">
          <img class="gp_pagos_fijos_icon_credit_cards" alt="" src="https://cdn.gameplanet.com/wp-content/uploads/2022/12/05165405/msi_credit_cards.png">
            <span class="tooltiptext">Meses Sin Intereses en productos seleccionados con un monto minimo de compra.</span>
          </div>
      ');
    }
    else{
      echo('<img class="gp_pagos_fijos_icon_credit_cards" alt="" src="https://cdn.gameplanet.com/wp-content/uploads/2022/12/05165056/credit_cards.png">');
    }
  }
  public function get_supports($supports){
    $supports[]='refunds';
    return $supports;
  }
  // agrega campos en "pagos" [woocommerce]
  public function init_form_fields()
  {
    $this->form_fields = array(
      'enabled' => array(
        'title'       => __('Enable/Disable', 'woocommerce'),
        'label'       => __('Activar Gameplanet Pagos_Fijos', 'woocommerce'),
        'type'        => 'checkbox',
        'description' => '',
        'default'     => 'no'
      ),
      'title' => array(
        'title'       => __('Title', 'woocommerce'),
        'type'        => 'text',
        'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),
        'default'     => __('Pago con tarjeta de credito o debito', 'woocommerce'),
        'desc_tip'    => false
      ),
      'sandbox' => array(
        'type' => 'checkbox',
        'title' => __('Modo de pruebas', 'woothemes'),
        'label' => __('Habilitar', 'woothemes'),                
        'default' => 'no'
      ),
      'test_merchant_id' => array(
        'type' => 'text',
        'title' => __('ID de comercio de pruebas', 'woothemes'),
        'description' => __('Obten tus llaves de prueba de tu cuenta de Openpay.', 'woothemes'),
        'default' => __('', 'woothemes')
      ),
      'test_private_key' => array(
          'type' => 'text',
          'title' => __('Llave secreta de pruebas', 'woothemes'),
          'description' => __('Obten tus llaves de prueba de tu cuenta de Openpay ("sk_").', 'woothemes'),
          'default' => __('', 'woothemes')
      ),
      'test_publishable_key' => array(
          'type' => 'text',
          'title' => __('Llave pública de pruebas', 'woothemes'),
          'description' => __('Obten tus llaves de prueba de tu cuenta de Openpay ("pk_").', 'woothemes'),
          'default' => __('', 'woothemes')
      ),
      'live_merchant_id' => array(
        'type' => 'text',
        'title' => __('ID de comercio', 'woothemes'),
        'description' => __('Obten tus llaves de producción de tu cuenta de Openpay.', 'woothemes'),
        'default' => __('', 'woothemes')
      ),
      'live_private_key' => array(
        'type' => 'text',
        'title' => __('Llave secreta', 'woothemes'),
        'description' => __('Obten tus llaves de producción de tu cuenta de Openpay ("sk_").', 'woothemes'),
        'default' => __('', 'woothemes')
      ),
      'live_publishable_key' => array(
        'type' => 'text',
        'title' => __('Llave pública', 'woothemes'),
        'description' => __('Obten tus llaves de producción de tu cuenta de Openpay ("pk_").', 'woothemes'),
        'default' => __('', 'woothemes')
      ),

      'charge_type' => array(
        'title' => __('¿Cómo procesar el cargo?', 'woocommerce'),
        'type' => 'select',
        'class' => 'wc-enhanced-select',
        'description' => __('Detecta si el pago se manda con 3D Secure a partir de la cantidad, manda directo o siempre pide 3d secure', 'woocommerce'),
        'default' => 'direct',
        'desc_tip' => true,
        'options' => array(
          'detect' => __('Detectar', 'woocommerce'),
          'direct' => __('Siempre Directo', 'woocommerce'),
          '3d' => __('Siempre 3D Secure', 'woocommerce'),
        ),
      ),
      
      'min_value_to_3d' => array(
        'type' => 'text',
        'title' => __('Monto minimo 3D secure', 'woothemes'),
        'description' => __('Monto mínimo para empezar hacer el cargo con 3D secure.', 'woothemes'),
        'default' => __('1000', 'woothemes')
      ),

      'msi' => array(
        'title' => __('Meses sin intereses', 'woocommerce'),
        'type' => 'multiselect',
        'class' => 'wc-enhanced-select',
        'css' => 'width: 400px;',
        'default' => '',
        'options' => $this->getMsi(),
        'custom_attributes' => array(
            'data-placeholder' => __('Opciones', 'woocommerce'),
        ),
    ),
    
    'msi_type' => array(
      'title' => __('¿A que productos se les aplicaran los MSI?', 'woocommerce'),
      'type' => 'select',
      'class' => 'wc-enhanced-select',
      'description' => __('Se elige si es para todos o solo los que tengan un metakey especifico', 'woocommerce'),
      'default' => 'all',
      'desc_tip' => true,
      'options' => array(
        'all' => __('Todos', 'woocommerce'),
        'meta' => __('Solo Meta dato especifico', 'woocommerce'),
        //'tag' => __('Tag', 'woocommerce'),
        //'category' => __('Categoria', 'woocommerce'),
      ),
    ),
    'msi_product_meta_data_key' => array(
      'type' => 'text',
      'title' => __('MSI Nombre del Meta dato', 'woothemes'),
      'description' => __('Que nombre tiene el meta dato a identificar', 'woothemes'),
      'default' => __('', 'woothemes')
    ),
    'msi_product_meta_data_value' => array(
      'type' => 'text',
      'title' => __('MSI Valor Nombre del Meta dato', 'woothemes'),
      'description' => __('Indica si el meta dato debera tener un valor especifico.', 'woothemes'),
      'default' => __('', 'woothemes')
    ),
    
    );
  }

  /**
   * creamos nuestras propias opciones para no tener que consultar el settigns a cada rato
   */
  public function process_admin_options()
  {
    parent::process_admin_options();
    error_log('se procesa los cambios del admin' . json_encode($this->settings));
    foreach ($this->settings as $key => $value) {
      update_option($this->id.'_'.$key,$value);
    }
  }
  public function payment_fields()
  {
    include plugin_dir_path(dirname(__FILE__)) . 'public/partials/gameplanet-pagos_fijos-public-display.php';
  }
  public function getMsi() {
    return array('3' => '3 meses', '6' => '6 meses', '9' => '9 meses', '12' => '12 meses', '18' => '18 meses');
  }
  public function validate_fields()
  {
    gp_pagos_fijos_log('validate_fields',"validando datos del formulario");
    if(!isset($_POST['payment_method']) && $_POST['payment_method'] != $this->id){
      gp_pagos_fijos_log('validate_fields', 'GOV-001 El metodo de pago no es reconocido:'.$_POST['payment_method']);
      wc_add_notice("Algo salió mal, inténtelo nuevamente o use otro método de pago. CODE: GOV-001", 'error');
      return;
    }
    if(!isset($_POST['openpay-card-cvc']) && empty($_POST['openpay-card-cvc'])){
      gp_pagos_fijos_log('validate_fields', 'GOV-002 NO se recibio CVV2',$_POST);
      wc_add_notice("Algo salió mal, inténtelo nuevamente o use otro método de pago. CODE: GOV-002", 'error');
      return;
    }
    if(!isset($_POST['device_session_id']) && empty($_POST['device_session_id'])){
      gp_pagos_fijos_log('validate_fields', 'GOV-003 NO se recibio device_session_id',$_POST);
      wc_add_notice("Algo salió mal, inténtelo nuevamente o use otro método de pago. CODE: GOV-003", 'error');
      return;
    }
    if(!isset($_POST['openpay_token']) && empty($_POST['openpay_token'])){
      gp_pagos_fijos_log('validate_fields', 'GOV-004 NO se recibio openpay_token',$_POST);
      wc_add_notice("Algo salió mal, inténtelo nuevamente o use otro método de pago. CODE: GOV-004", 'error');
      return;
    }
    if(!isset($_POST['openpay_card_number']) && empty($_POST['openpay_card_number'])){
      gp_pagos_fijos_log('validate_fields', 'GOV-005 NO se recibio openpay_card_number',$_POST);
      wc_add_notice("Algo salió mal, inténtelo nuevamente o use otro método de pago. CODE: GOV-005", 'error');
      return;
    }
    gp_pagos_fijos_log('validate_fields',"Todo bien, continua",$_POST);
    
  }
  // procesar pago
  public function process_payment($order_id)
  {
    $nota_orden = "<strong>** Pago con Tarjeta **</strong>" . PHP_EOL;

    $this->nonce = isset($_POST['woocommerce-process-checkout-nonce'])?$_POST['woocommerce-process-checkout-nonce']:time();
    gp_pagos_fijos_log($this->nonce.' process_payment', '1. Inicio proceso de cobro openpay GP',$_POST);
    //recuperamos informacion
    $device_session_id = isset($_POST['device_session_id']) ? wc_clean($_POST['device_session_id']) : '';
    $openpay_token = $_POST['openpay_token'];

    //obtenemos la orden
    $this->order = wc_get_order($order_id);
    
    //El payment plan dictamina los msi, puede que  recibamos la orden justo cuando quitamos lo meses se valida esto
    $generate_payment_plan = $this->get_payment_plan();        
    if($generate_payment_plan['success'] === false){
      gp_pagos_fijos_log($this->nonce.' process_payment', '1.2 error en los msi',json_encode($generate_payment_plan));

      wc_add_notice($generate_payment_plan['message'] , 'error');
      return;
    }
    $payment_plan = $generate_payment_plan['data'];        

    //procesamos con openpay
    $openpay_cargo = $this->openpay_procesar_orden($device_session_id,$openpay_token,$payment_plan);
    if(!$openpay_cargo){
      //pasamos la orden a fail
      gp_pagos_fijos_log($this->nonce.' process_payment','CARGO FALLIDO');

      $this->order->update_meta_data('_gp_estatus_domicilio', 'C');
      $this->order->update_status('failed', "<strong>** No se pudo generar el cargo a la tarjeta **</strong>" . PHP_EOL);
      return array(
        'result'   => 'fail',
        'redirect' => $this->get_return_url($this->order)
      );
    }

    //verificamos si el cargo viene con 3D
    $tipo_cargo =get_post_meta( $this->order->get_id(),'_openpay_charge_type',true);
    gp_pagos_fijos_log($this->nonce.' process_payment','3.2 Cargo realizado tipo '.$tipo_cargo);

    if($tipo_cargo == '3d'){
      gp_pagos_fijos_log($this->nonce.' process_payment','4. El cargo con con 3D secure se redirigira a: ',get_post_meta($this->order->get_id(), '_openpay_3d_secure_url', true));
    }
    else if($tipo_cargo == 'auth'){
      //actualizamos el estatus
      $this->order->update_meta_data( '_gp_estatus_domicilio', 'A');
      $this->order->update_status('on-hold', "<strong>** GP openpay Cargo en espera de autorizacion  **</strong>" . PHP_EOL);
      
    }
    else{
      $this->order->update_meta_data( '_gp_estatus_domicilio', 'B');
      $this->order->update_status('processing', "<strong>** GP openpay Cargo directo**</strong>" . PHP_EOL);

    }
    //redirigimos
    return array(
      'result'   => 'success',
      'redirect' => $this->get_return_url($this->order)
    );
  }
  
  /**
   * Obtine el plan de pagos segun la confgifuracion del gateway y lo ingresado por el ccliente
   */
  private function get_payment_plan(){
    //vemos si hay meses sin interses y se cumple el monto minimo
    $accept_msi = gp_pagos_fijos_obtener_msi_disponibilidad();
    gp_pagos_fijos_log($this->nonce.' process_payment', '1.1.8',$accept_msi);
    
    if($accept_msi){
      //si hay meses, recuperamos las mensualidades si es que viene
      if(isset($_POST['openpay_month_interest_free']) && !empty($_POST['openpay_month_interest_free']) && in_array($_POST['openpay_month_interest_free'],['3','6','9','12','18'])){
         //guardamos la info usuario
         $selected_msi = $_POST['openpay_month_interest_free'];
         $amount_min = $selected_msi * 100;
         $amount = number_format((float)$this->order->get_total(), 2, '.', '');
         if($amount>=$amount_min){
          update_post_meta($this->order->get_id(), 'openpay_msi', $_POST['openpay_month_interest_free']);
       
          return [
            "success"=>true,
            "message"=>"Plan de pago generado",
            "data"=>[
              "payments"=>$_POST['openpay_month_interest_free'],
              "payments_type"=>'WITHOUT_INTEREST'
            ]
          ];
         }
         return [
          "success"=>false,
          "message"=>"Algo salió mal, inténtelo nuevamente. Code:PP-MSI-300: Total de compra menor al necesario para la promción",
          "data"=>[
            "payments"=>$_POST['openpay_month_interest_free'],
            "payments_type"=>'WITHOUT_INTEREST'
          ]
        ];
      }
    }
    //no hay meses
    //validamos si recibimos algo 
    if(!$accept_msi &&( isset($_POST['openpay_month_interest_free']) && $_POST['openpay_month_interest_free'] != '1')){
      //no se hace el cobro, forzamos a que el usuario vuelva a recargar la pagina
      return [
        "success"=>false,
        "message"=>"Algo salió mal, inténtelo nuevamente. Code:PP-MSI-100: No se encotro promoción activa".$_POST['openpay_month_interest_free'],
        "data"=>[
          "payments"=>$_POST['openpay_month_interest_free'],
          "payments_type"=>'WITHOUT_INTEREST'
        ]
      ];
    }
    return [
      "success"=>true,
      "message"=>"No hay meses activos, ni seleccionados",
      "data"=>null
    ];;
  }

  /**
   * Se procesa la orden en openpay
   * 
   * Se genera un cargo temporal en la tarjeta del cliente con los datos ingresados,
   * se manda con 3D secure si se alcanza el minimo, 
   * 
   */
  private function openpay_procesar_orden($device_session_id,$openpay_token,$payment_plan){
    try {
      gp_pagos_fijos_log($this->nonce.' openpay_procesar_orden', '2. empieza a generar request');

      $amount = number_format((float)$this->order->get_total(), 2, '.', '');
      $openpay_customer = $this->openpay_get_customer();
      gp_pagos_fijos_log($this->nonce.' openpay_procesar_orden','2.1 Cliente encontrado',$openpay_customer);
      if(!$openpay_customer){
        return false;
      }
      $charge_data = array(
          "method" => "card",
          "amount" => $amount,
          "currency" => strtolower(get_woocommerce_currency()),
          "source_id" => $openpay_token,
          "device_session_id" => $device_session_id,
          "description" => sprintf("Items: %s", $this->getProductsDetail()),            
          "order_id" => $this->order->get_id()."_".time(),
      );
      if(is_array($payment_plan)){
        $charge_data["payment_plan"] =$payment_plan;
        
      }
      //vemos como hacer el cargo
      $charge_data = $this->analizarTipoCargo($charge_data);
     
      //creamos el cargo al cliente
      gp_pagos_fijos_log($this->nonce.' openpay_procesar_orden','2.9 se manda el cargo',$charge_data);
      try {
        $charge = $this->api_openpay_call("/customers/".$openpay_customer['id']."/charges",'POST',$charge_data,true,'CREATE CHARGE');
        
        gp_pagos_fijos_log($this->nonce.' openpay_procesar_orden','3. Se creo el cargo al cliente',$charge);
        
        if($this->is_sandbox){
          //guardamos la info usuario
          update_post_meta($this->order->get_id(), '_openpay_customer_sandbox_id', $openpay_customer['id']);
          //guardamos la informacion del cargo
          update_post_meta($this->order->get_id(), '_openpay_charge_sandbox_id', $charge['id']);
        }
        else{
          //guardamos la info usuario
          update_post_meta($this->order->get_id(), '_openpay_customer_id', $openpay_customer['id']);
          //guardamos la informacion del cargo
          update_post_meta($this->order->get_id(), '_openpay_charge_id', $charge['id']);
        }
        
        //si la orden se creo con 3D secure obtenemos la url a redireccionar
        if (isset($charge['payment_method']) && $charge['payment_method']['type'] == 'redirect') {
          update_post_meta($this->order->get_id(), '_openpay_3d_secure_url', $charge['payment_method']['url']);                
        }else{
            delete_post_meta($this->order->get_id(), '_openpay_3d_secure_url');
        }

        $nota_orden = "GP Openpay". PHP_EOL . "Cargo creado con el id de transaccion: ".$charge['id'];
        //asignamos el transaction id
        try {
          $this->order->set_transaction_id( (string)$charge['id'] );
          $this->order->save();
        } catch (\Throwable $th) {
          gp_pagos_fijos_log($this->nonce.'No se puede asignar el id transaction',$charge['id']);
        }
        gp_pagos_fijos_log($this->nonce.' id_transaccion:',$this->order->get_transaction_id());


        $this->order->add_order_note($nota_orden);
        return true;
      } catch (\Exception $e) {
        //fallo algo al crear el cargo
        gp_pagos_fijos_log($this->nonce.' openpay_procesar_orden','3.1 No se pudo crear el cargo');
        $this->api_error($e);
        return false;
      }

      return true;
    } catch (\Exception $e) {
      $this->api_error($e);
      return false;
    }
  }

  /**
   * Obtiene el id de openpay del cliente necesario para crear cargos
   * 
   * Se usa el mismo metakey del usuario (_openpay_customer_id) que el plugin de openpay_cards
   * esto para conservar los que ya se habian creado con este plugin
   */
  private function openpay_get_customer(){
    gp_pagos_fijos_log($this->nonce." openpay_get_customer:", "Obtener infor del cliente");

    $id_customer = null;
    if ($this->is_sandbox) {
      $id_customer = get_user_meta(get_current_user_id(), '_openpay_customer_sandbox_id', true);
    } else {
        $id_customer = get_user_meta(get_current_user_id(), '_openpay_customer_id', true);
    }  
    gp_pagos_fijos_log($this->nonce." openpay_get_customer:", "Metakey obtenido: ".$id_customer);
    
    //si el usuario no tiene el metakey lo creamos
    if(is_null($id_customer) || empty($id_customer)){

      return $this->openpay_create_customer();
    }

    //recuperamos la informacion del cliente en openpay (Talvez se puede evitar esto ya que solo necesitamos el id, lo mantenemos como validacion)
    try {
      gp_pagos_fijos_log($this->nonce." openpay_get_customer:", "Se solicita el cliente de openpay");
      $cliente = $this->api_openpay_call('customers/'.$id_customer,'GET',null,true,'GET CUSTOMER');
      return $cliente;
    } catch (Exception $e) {
        $this->api_error($e);
        return false;
    }
  }

  /**
   * Genera y guarda el id de openpay del cliente 
   * 
   * se obtine toda la informacion de billing y la direccion de envio 
   */

  private function openpay_create_customer()
  {
    gp_pagos_fijos_log($this->nonce." openpay_create_customer:", "Se crea el cliente");

    $customer_data = array(
      'external_id' => get_user_meta(get_current_user_id(), 'id_gp', true),
      'name' => $this->order->get_billing_first_name(),
      'last_name' => $this->order->get_billing_last_name(),
      'email' => $this->order->get_billing_email(),
      'requires_account' => false,
      'phone_number' => $this->order->get_billing_phone()
    );
    try {
      $customer = $this->api_openpay_call('customers','POST',$customer_data,true,'CREATE CUSTOMER');

      if (is_user_logged_in()) {
        if ($this->is_sandbox) {
          update_user_meta(get_current_user_id(), '_openpay_customer_sandbox_id', $customer['id']);
        } else {
          update_user_meta(get_current_user_id(), '_openpay_customer_id', $customer['id']);
        }
      }

      return $customer;
    } catch (Exception $e) {
      $this->api_error($e);
      return false;
    }

  }

  
  /**
   * Genera la descricpion de los productos de la orden
   */
  private function getProductsDetail() {
    $order = $this->order;
    $products = [];
    foreach( $order->get_items() as $item_product ){                        
        $product = $item_product->get_product();                        
        $products[] = $product->get_name();
    }
    return substr(implode(', ', $products), 0, 249);
  }

  /**
   * Analiza como se hara el cargo de la orden al cliente
   * 
   * Se determina si el cobro pasa directo con 3D Secure o si pasa a un analizis mayor
   */
  private function analizarTipoCargo($charge_data){
    
    //revisamos si se debe mandar con 3D secure
      //si es menor que la cantidad de peligro entonces enviamos con 3D secure
      //si el charge_type es 3d se manda siempre 3D secure
      if(($this->settings['charge_type']=='detect' && $charge_data['amount'] >= (float) $this->settings['min_value_to_3d']) || $this->settings['charge_type']=='3d'){
        gp_pagos_fijos_log($this->nonce.' analizarTipoCargo','2.8 se manda el cargo directo con 3D');
        update_post_meta($this->order->get_id(), '_openpay_charge_type', '3d');
        $charge_data['use_3d_secure'] = true;
        $charge_data['redirect_url'] =  site_url('/').'?wc-api=gp_pagos_fijos_confirm';
      }
      else{
        //es mayor a la cantidad entonces se hara una pre autorizacion para despues hacer la autorizacion
        gp_pagos_fijos_log($this->nonce.' analizarTipoCargo','2.8 se manda una cargo directo');
        update_post_meta($this->order->get_id(), '_openpay_charge_type', 'direct');
        //$charge_data['capture'] = false;//dejamos pendiente el cargo preauthorizado 

      }
    return $charge_data;
  }
  /**
   * Peticion general a la API de Openpay
   * 
   * Parece que funciona bien en GET y POST,Hay que revisar los PUT
   */
  public function api_openpay_call($endpoint,$method = 'GET',$params=null,$force_fail = true,$action = "EMPTY"){
    
    gp_pagos_fijos_log($this->nonce." api_openpay_call:".$endpoint, "Inicia Peticion", $params);
    /*Le llame diferente a proposito para que no sepan como se pasan a la API*/
    $args = array(
      'timeout' => 120,
      'headers' => array(
        'Content-Type'    => 'application/json',
        'Authorization' => 'Basic ' . base64_encode( $this->settings['live_private_key'].':')
      ),
      //'body'    => json_encode($params)
    );

    //pasar a opcion
    if($this->is_sandbox){
      $url = $this->api_url_sandbox . $this->settings['test_merchant_id'] .'/'. $endpoint;  

    }
    else{
      $url = $this->api_url . $this->settings['live_merchant_id'] .'/'. $endpoint;  
    }
    
    if($method == "GET"){
      $response = wp_remote_get($url, $args);
    }
    else{
      //POST
      $args['body'] = json_encode($params);
      $response = wp_remote_post($url, $args);
    }
    // Si hay un error
    if (is_wp_error($response)) {
      $error_message = $response->get_error_message();
      gp_pagos_fijos_log($this->nonce." api_openpay_call:".$endpoint, "Respuesta de la API", $error_message);
      throw new Exception("Error en la peticion: ".$endpoint, 501);
      return false;
    } else {
      $res = json_decode($response['body'],true);
      //recoleccion de informacion antifraudes
      if($this->order){
        gp_pagos_fijos_antifraude_webhook($action,$this->order->get_id(),$params,$res);

      }

      if(isset($res['error_code']) && $force_fail){
        gp_pagos_fijos_log($this->nonce." api_openpay_call:".$endpoint, "Error en la peticion", $res);
        throw new Exception($res['description'], $res['error_code']);
        return false;
      }
      gp_pagos_fijos_log($this->nonce." api_openpay_call:".$endpoint, "Peticion completa");
      return $res;
    }
  }
  private function api_error(Exception $e){
    gp_pagos_fijos_log(($this->nonce??$this->order->get_id())." api_error:", $e->getCode().': '.$e->getMessage());
    wc_add_notice("Algo salió mal, inténtelo nuevamente o use otro método de pago. Code: GPOP-". $e->getCode(), 'error');
    $nota_orden = "GP Openpay". PHP_EOL .  "PROCESO CANCELADO" . PHP_EOL . "Opepay no pudo hacer el cargo: " . $e->getCode().': '.$e->getMessage();
    $this->order->add_order_note($nota_orden);
    error_log($e->getCode().': '.$e->getMessage());
  }

  /**
	 * Process refund.
	 *
	 * If the gateway declares 'refunds' support, this will allow it to refund.
	 * a passed in amount.
	 *
	 * @param  int        $order_id Order ID.
	 * @param  float|null $amount Refund amount.
	 * @param  string     $reason Refund reason.
	 * @return boolean True or false based on success, or a WP_Error object.
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {

    if(!$amount){
      //nada que hacer
      return false;
    }
    $this->order = wc_get_order($order_id);
    gp_pagos_fijos_log($this->order_id." process_refund:","INCIA devolucion de orden");

    if ($this->is_sandbox) {
      $id_customer_op = $this->order->get_meta('_openpay_customer_sandbox_id',true);
      $id_transaction_op = $this->order->get_meta('_openpay_charge_sandbox_id',true);
    } else {
      $id_customer_op = $this->order->get_meta('_openpay_customer_id',true);
      $id_transaction_op = $this->order->get_meta('_openpay_charge_id',true);
    }  
    
    //Se hace la peticion a openpay
    $data = [
      "amount"=>$amount,
      "description"=>$reason??'Reembolso',
    ];
    $endpoint = "customers/{$id_customer_op}/charges/{$id_transaction_op}/refund";
    try {
      $reembolso = $this->api_openpay_call($endpoint,'POST',$data);
      
      if(isset($reembolso['refund'])){
        gp_pagos_fijos_log($this->order_id." process_refund:","Reembolso exitoso");
        $nota_orden = "GP Openapy". PHP_EOL .  "Reembolso Exitoso" . PHP_EOL . " id operacion: ".$reembolso['refund']['id'];
        $this->order->add_order_note($nota_orden);
        return true;
      }
      gp_pagos_fijos_log($this->order_id." process_refund:","Reembolso Falló",$reembolso);

      $nota_orden = "GP Openapy". PHP_EOL .  "Reembolso Falló" . PHP_EOL . "Motivo desocnocido revisar Log";
        $this->order->add_order_note($nota_orden);
        return false;
    } catch (Exception $e) {
      gp_pagos_fijos_log($this->order_id." process_refund:","Reembolso Falló - exception",$e->getCode().''.$e->getMessage());

      $nota_orden = "GP Openapy". PHP_EOL .  "Reembolso Falló" . PHP_EOL . $e->getCode().''.$e->getMessage();
      $this->order->add_order_note($nota_orden);
      return false;
    }


    
	}

}

/**Se agrega en la seccion de PAgos de Woocomerce */
function gp_pagos_fijos_add_creditcard_gateway($methods)
{
  array_push($methods, 'Gp_Openpay_Gateway');
  return $methods;
}

add_filter('woocommerce_payment_gateways', 'gp_pagos_fijos_add_creditcard_gateway');
// para quitar opción de pago
function gp_pagos_fijos_wallet_unset($gateways)
{
  unset($gateways['openpay_gp']);
  return $gateways;
}
