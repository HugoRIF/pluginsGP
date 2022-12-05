<?php
  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
function gameplanet_otp_logs($funcion, $mensaje, $extra = null)
{
  $directorio = './gp/logs/gameplanet_otp_logs/';

  if (!file_exists($directorio)) {
    mkdir($directorio, 0755, true);
  }

  $tiempo = current_time('mysql');
  $fecha = strtotime($tiempo);
  $fecha_log = date('M-d', $fecha);

  $file = fopen($directorio . $fecha_log . "_gameplanet.log", "a") or fopen($directorio . $fecha_log . "_gameplanet.log", "w");

  if (is_array($extra)) {
    $extra = json_encode($extra);
  }
  $registro = $tiempo . " :: Función: " . $funcion . " || " . $mensaje . " || " . $extra . "\n";

  $bytes = fwrite($file, $registro);
  fclose($file);

  return $bytes;
}

/**CREACION DE TABLAS */
function gp_otp_add_table_control_user(){
  require_once( ABSPATH . 'wp-config.php' );

  global $table_prefix;
  $nombreTablaUsers = ($table_prefix??'').'gp_otp_control_users';
  $nombreTablaIP = ($table_prefix??'').'gp_otp_control_ip';
  $nombreTablaConfig = ($table_prefix??'').'gp_otp_config';
  $nombreTablaLogs = ($table_prefix??'').'gp_otp_gateway_error_log';
  
  $created = dbDelta("
    DROP TABLE IF EXISTS $nombreTablaUsers;
      CREATE TABLE $nombreTablaUsers (
        id BIGINT NOT NULL AUTO_INCREMENT,
        user_id BIGINT NULL,
        user_ip MEDIUMTEXT NULL,
        user_email MEDIUMTEXT NULL,
        user_phone VARCHAR(15) NULL,
        resend_attends INT NULL DEFAULT 0,
        is_blocked TINYINT NULL DEFAULT 0,
        time_blocked MEDIUMTEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id));

    DROP TABLE IF EXISTS $nombreTablaIP;
      CREATE TABLE $nombreTablaIP (
        id BIGINT NOT NULL AUTO_INCREMENT,
        ip MEDIUMTEXT NULL,
        resend_attends INT NULL DEFAULT 0,
        is_blocked TINYINT NULL DEFAULT 0,
        time_blocked MEDIUMTEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id));

  
    DROP TABLE IF EXISTS $nombreTablaLogs;
      CREATE TABLE $nombreTablaLogs (
        id BIGINT NOT NULL AUTO_INCREMENT,
        gateway VARCHAR(50) NOT NULL,
        event VARCHAR(50) NOT NULL COMMENT 'que evento disparo el error',
        log LONGTEXT NULL COMMENT 'Se guarda la ultima respuesta (error) que envia el gateway',
        admin_watched TINYINT(2) NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id));
  
  ");
  return true;
}

/**** VALIDACIONES EN BD */

/**
 * Verificamos que el telephono no le pertenezca a otro usuario
 * si es el mismo usuario igual manda el error para no estar haciendo mas peticiones
 * @param string $phone Telefono a validar debe incluier el +52
 * @return mixed Si y solo si el telefono esta disponible regresa true
 */
function verify_unique_phone($phone){
  try {
    $args =  array( 'meta_key' => 'billing_phone', 'meta_value' => $phone,'number'=>1 );
    $wp_user_query = new WP_User_Query( $args );
    // Get the results
    $users = $wp_user_query->get_results();
    if(empty($users)){
      return true;
    }
    
    gameplanet_otp_logs('verify_unique_phone','Telefono pertenece a otro usuario o al mismo');

    return false;
   
    
  } catch (\Exception $e) {
    gameplanet_otp_logs('verify_unique_phone','ERROR EN VALIDACION',$e->getMessage());
    return false;
  }
}
/***PETICIONES A TWILIO */

function gp_otp_send_twilio_api($phone,$canal){
  try {
    
    gameplanet_otp_logs("gp_otp_send_twilio_api", "Se envia mensaje por twilio",['phone'=>$phone,"channel"=>$canal]);
    if(get_option('gp_otp-active') == 0){
      gameplanet_otp_logs("gp_otp_send_twilio_api", "el otp esta incativo",['phone'=>$phone,"channel"=>$canal]);

      return [
        "success" => true,
        "message" => "Mensaje enviado",
        "data" => [],
      ];
    }
    $args = array(
      //'timeout'     => 30,
      'headers' => array(
        'Authorization' => 'Basic ' . base64_encode( get_option('user_twilio').':'.get_option('password_twilio'))
      ),
      'body' => [
        "To"=> $phone,
        "Channel"=>$canal,
      ]
    );


    $url = get_option('ruta_twilio')."Services/".get_option('service_id_twilio').'/Verifications';


    $response = wp_remote_post($url, $args);
    // Si hay un error
    if (is_wp_error($response)) {
      $error_message = $response->get_error_message();
      gameplanet_otp_logs("gp_otp_send_twilio_api", "Error en la peticion ", $error_message);
      return [
        "success" => false,
        "message" => "CODE-500-API-TWILIO- Algo salio mal al intentar enviar el mensaje",
        "data" => [$error_message],
      ];
    }
    //gameplanet_otp_logs("gp_otp_send_twilio_api", "Falló la peticion",$response);

    $response_code = $response['response']['code']??500;
    if($response_code != 201){
      gameplanet_otp_logs("gp_otp_send_twilio_api", "Falló la peticion",$response);
      //guardamos el log
      $model = new GP_OTP_CONTROL_MODEL();
      $model->registrarErrorGateway('twilio','send',$response);
      return [
        "success" => false,
        "message" => "CODE-510: No se puede enviar el mensaje, por parte del proveedor",
        "data" => [],
      ];
    }
    
    return [
      "success" => true,
      "message" => "Mensaje enviado",
      "data" => [],
    ];
  } catch (\Exception $e) {
    gameplanet_otp_logs("gp_otp_send_twilio_api", "Error interno", $e->getLine().', '.$e->getMessage());
    return [
      "success" => false,
      "message" => "Error interno",
      "data" => $e->getLine().', '.$e->getMessage(),
    ];
  }
}
function gp_otp_verify_twilio_api($phone,$code){
  try {
   
    gameplanet_otp_logs("gp_otp_verify_twilio_api", "Se envia validcacion a twilio",['phone'=>$phone,"code"=>$code]);
    if(get_option('gp_otp-active') == 0){
      gameplanet_otp_logs("gp_otp_verify_twilio_api", "el otp esta incativo",['phone'=>$phone,"code"=>$code]);

      return [
        "success" => true,
        "message" => "Codigo valido",
        "data" => [],
      ];
    }
    $args = array(
      //'timeout'     => 30,
      'headers' => array(
        'Authorization' => 'Basic ' . base64_encode( get_option('user_twilio').':'.get_option('password_twilio'))
      ),
      'body' => [
        "To"=> $phone,
        "Code"=>$code,
      ]
    );


    $url = get_option('ruta_twilio')."Services/".get_option('service_id_twilio').'/VerificationCheck';


    $response = wp_remote_post($url, $args);
    // Si hay un error
    if (is_wp_error($response)) {
      $error_message = $response->get_error_message();
      gameplanet_otp_logs("gp_otp_verify_twilio_api", "Error en la peticion ", $error_message);
      return [
        "success" => false,
        "message" => "CODE-500-API-TWILIO- Algo salio mal al intentar validar el coidgo",
        "data" => [$error_message],
      ];
    }

    $response_code = $response['response']['code']??500;
    if($response_code != 200){
      $model = new GP_OTP_CONTROL_MODEL();
      $model->registrarErrorGateway('twilio','verify',$response);
      gameplanet_otp_logs("gp_otp_verify_twilio_api", "Error del proveedor",['phone'=>$phone,"code"=>$code]);
        
        return [
          "success" => false,
          "message" => "CODE-510: El proveedor no esta funcionando, intenta mas tarde.",
          "data" => [],
        ];
     
    }
    $response = json_decode($response['body']);

    if($response->status == 'pending'){
      gameplanet_otp_logs("gp_otp_verify_twilio_api", "Codigo incorrecto",['phone'=>$phone,"code"=>$code]);
      
      return [
        "success" => false,
        "message" => "Código Incorrecto",
        "data" => [],
      ];
    }
    return [
      "success" => true,
      "message" => "Codigo valido",
      "data" => [],
    ];
  } catch (\Exception $e) {
    gameplanet_otp_logs("gp_otp_send_twilio_api", "Error interno", $e->getLine().', '.$e->getMessage());
    return [
      "success" => false,
      "message" => "Error interno",
      "data" => $e->getLine().', '.$e->getMessage(),
    ];
  }
}

/***** USUARIOS LOGEADOS ********* */
function ajax_otp_send_billing()
{

  try {
    //validacion de logeo
    if(!is_user_logged_in()){
      echo(json_encode([
        "success"=>false,
        "message"=>"Por favor Inicia sesisón antes.",
        "code"=>401,
        "data"=>"CODE-401-OSBI : Usuario no identificado",
      ]));
      die();
    }
    //validacion de parametros
    $params = $_POST;
   // $usuario = wp_get_current_user();
    $id_usuario = get_current_user_id();
    $ip_usuario = gp_otp_get_the_user_ip();
    $phone = '+52'.$params['phone'];

    //validacion usuario de riesgo
    if(get_user_meta( $id_usuario, "gp_niv_riesgo", true ) == 3){
      echo(json_encode([
        "success"=>false,
        "message"=>"Servicio no disponible por el momento. CODE-RN-300",
        "code"=>302,//
        "data"=>"",
      ]));
      die();
    }
    //validacion de telefono

    $telefono_disponible = verify_unique_phone($phone);
    if($telefono_disponible !== true){
      if($telefono_disponible === 2){
        echo(json_encode([
          "success"=>false,
          "message"=>"El teléfono ingresado es tu telefono actual.",
          "code"=>302,//bloqueado por el tiempo
          "data"=>"",
        ]));
        die();
      }
      echo(json_encode([
        "success"=>false,
        "message"=>"El teléfono le pertenece a otro usuario",
        "code"=>301,//bloqueado por el tiempo
        "data"=>"",
      ]));
      die();
    }
    //validacion por usuario
    $model = new GP_OTP_CONTROL_MODEL();
    //validamos que pueda enviar 
     $user_can_send = $model->validateCanSend($id_usuario,$ip_usuario);
     if(!$user_can_send['success']){
      echo(json_encode([
        "success"=>false,
        "message"=>$user_can_send['message'],
        "code"=>205,//bloqueado por el tiempo
        "data"=>$user_can_send['data'],
         
      ]));
      die();
     }
    
    //se envia el mensaje
    $envioSMS = gp_otp_send_twilio_api($phone,'sms');
    if(!$envioSMS['success']){
      echo(json_encode([
        "success"=>false,
        "message"=>$envioSMS['message'],
        "code"=>400,
        "data"=>$envioSMS['message'],
      ]));
      die();
    }

    //registramos el intento
    $guardar_intento = $model->create_update($id_usuario,$ip_usuario,$phone);

    //envia cuanto tiempo le queda
    echo(json_encode([
      "success"=>true,
      "message"=>"SMS enviado",
      "code"=>200,
      "data"=>$guardar_intento['data'],
    ]));
    die();
  } catch (\Exception $e) {
    echo(json_encode([
      "success"=>false,
      "message"=>"Algo salió mal, por favor intenta mas tarde.",
      "code"=>500,
      "data"=>"CODE-500-OSBI : ".$e->getLine().', '.$e->getMessage(),
    ]));
    die();
  }
 
}
function ajax_otp_verify_billing(){
  try {
    //validacion de logeo
    if(!is_user_logged_in()){
      echo(json_encode([
        "success"=>false,
        "message"=>"POr favor Inicia sesisón antes.",
        "code"=>401,
        "data"=>"CODE-401-OSBI : Usuario no identificado",
      ]));
      die();
    }
    //validacion de parametros
    $phone = '+52'.$_POST['phone'];
    $code = $_POST['code'];

    //peticion a twilio
    $APIVerify = gp_otp_verify_twilio_api($phone,$code);
    if(!$APIVerify['success']){
      echo(json_encode([
        "success"=>false,
        "message"=>$APIVerify['message'],
        "code"=>400,
       
      ]));
      die();
    }

    //actualizamos el telefono del usuario
    $user_id = get_current_user_id();
		$customer = new WC_Customer( $user_id );
    $customer->set_billing_phone($phone);
    $customer->save();
		wc_add_notice( __( 'Teléfono Actualizado Correctamente.', 'woocommerce' ) );
    echo(json_encode([
      "success"=>true,
      "message"=>"Telefono Actualizado",
      "code"=>200,
      "data"=>[],
    ]));
    die();
  } catch (\Exception $e) {
    echo(json_encode([
      "success"=>false,
      "message"=>"Algo salió mal, por favor intenta mas tarde.",
      "code"=>500,
      "data"=>"CODE-500-OVBI : ".$e->getLine().', '.$e->getMessage(),
    ]));
    die();
  }
}
function gp_otp_get_the_user_ip() {
  if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
  //check ip from share internet
  $ip = $_SERVER['HTTP_CLIENT_IP'];
  } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
  //to check ip is pass from proxy
  $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
  } else {
  $ip = $_SERVER['REMOTE_ADDR'];
  }
  return apply_filters( 'wpb_get_ip', $ip );
}


/*** rEGISTRO DE USUARIO */

/**
 * Hook para validar el telefono en twilio
 */
function gp_otp_validate_registration_fields($errors, $username, $email){
 

  if (!isset($_POST['gp_otp_phone']) || empty($_POST['gp_otp_phone'])) {
    $errors->add('billing_phone_error', __('¡El telefono en obligatorio!', 'woocommerce'));
    return $errors;

  }
  if (!isset($_POST['gp_otp_phone_code']) || empty($_POST['gp_otp_phone_code'])) {
    $errors->add('billing_phone_error', __('¡Es necesario validar el teléfono!', 'woocommerce'));
    return $errors;
  }

  //validamos en twilio los datos
  $phone = '+52'.$_POST['gp_otp_phone'];
  $code = $_POST['gp_otp_phone_code'];
  $validate_phone = gp_otp_verify_twilio_api($phone,$code);
  if(!$validate_phone['success']){
    $errors->add('billing_phone_error', __('Verificación de Teléfono: '.$validate_phone['message'], 'woocommerce'));
  }
  return $errors;
}

function gp_otp_save_phone_reg($user_id,$info,$pass){
  try {
    $phone = '+52'.$_POST['gp_otp_phone'];
    $customer = new WC_Customer( $user_id );
    $customer->set_billing_phone($phone);
    $customer->save();
  } catch (\Exception $e) {
    error_log('AGREGAR TELEFONO REG: '.$e->getMessage());
  }

}

function ajax_otp_send_sigin(){

  try {

    //validacion de parametros
    $params = $_POST;
    $email = $params['email'];
    $phone = '+52'.$params['phone'];
    $ip_usuario = gp_otp_get_the_user_ip();

    //validacion de telefono
    $telefono_disponible = verify_unique_phone($phone);
    if($telefono_disponible !== true){
      echo(json_encode([
        "success"=>false,
        "message"=>"Por favor primero ingresa los datos anteriores",
        "code"=>301,//bloqueado por falta de email
        "data"=>"",
      ]));
      die();
    }
    $telefono_disponible = verify_unique_phone($phone);
    if($telefono_disponible !== true){
      echo(json_encode([
        "success"=>false,
        "message"=>"El teléfono le pertenece a otro usuario",
        "code"=>301,//bloqueado por duplicidad
        "data"=>"",
      ]));
      die();
    }
    //validacion por usuario
    $model = new GP_OTP_CONTROL_MODEL();
    //validamos que pueda enviar 
     $user_can_send = $model->validateCanSend(0,$ip_usuario,$email);
     if(!$user_can_send['success']){
      echo(json_encode([
        "success"=>false,
        "message"=>$user_can_send['message'],
        "code"=>205,//bloqueado por el tiempo
        "data"=>$user_can_send['data'],
         
      ]));
      die();
     }
    
    //se envia el mensaje
    $envioSMS = gp_otp_send_twilio_api($phone,'sms');
    if(!$envioSMS['success']){
      echo(json_encode([
        "success"=>false,
        "message"=>$envioSMS['message'],
        "code"=>400,
        "data"=>$envioSMS['message'],
      ]));
      die();
    }

    //registramos el intento
    $guardar_intento = $model->create_update(0,$ip_usuario,$phone,$email);

    //envia cuanto tiempo le queda
    echo(json_encode([
      "success"=>true,
      "message"=>"SMS enviado",
      "code"=>200,
      "data"=>$guardar_intento['data'],
    ]));
    die();
  } catch (\Exception $e) {
    echo(json_encode([
      "success"=>false,
      "message"=>"Algo salió mal, por favor intenta mas tarde.",
      "code"=>500,
      "data"=>"CODE-500-OSBI : ".$e->getLine().', '.$e->getMessage(),
    ]));
    die();
  }
}


/*** ADMIN */

function resporte_IP(){
  $Model = new GP_OTP_CONTROL_MODEL();
  $records = $Model->getIPControl();
  return [
    "success"=>true,
    "data"=>[
      "records"=>$records,
      "total"=>0
    ]
    ];
}