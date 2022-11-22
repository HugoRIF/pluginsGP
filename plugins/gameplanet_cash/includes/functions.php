<?php

function gameplanet_cash_logs($funcion, $mensaje, $extra = null)
{
  $directorio = './gp/logs/gameplanet_cash_logs/';

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

/**
 * Peticion recibida del ajax se llama a la api y se contrulle una respuesta y su front
 */
function ajax_cash_crear_orden()
{
 
  try {
    $params = $_POST;
    $logged = is_user_logged_in();
    $user = null;
    if ($logged) {
      $user = wp_get_current_user();
    }
    else{
      gameplanet_cash_logs("ajax_cash_crear_orden", "NO hay una sesion");
      $data = [
        "success" => false,
        "message" => "No hay una sesion activa",
        "code" => 401,
        "data" => "CODE-USER-401",
      ];
      $data["content"]=cashFailMessage($data);
      echo json_encode($data);
      die();
    }
    gameplanet_cash_logs("ajax_cash_crear_orden", "Inicia CASH", ['id_cliente'=>$user->id,"creditos"=>$params['creditos']]);

    //vamos el el producto de CREDITO GAMEPLANET
    $product = wc_get_product_id_by_sku('888844440001');//la tendremos que validar??
    
    $validInfo = cashValidate($params);
    if (!$validInfo['success']) {
      gameplanet_cash_logs("ajax_cash_crear_orden", "Falló la validacion", $validInfo);
      $validInfo['params'] = $params;
      $validInfo['alert'] = cashFailMessage($validInfo);
      echo json_encode($validInfo);
      die();
    }
    $params = [
      "gp_source"=>$_SERVER["HTTP_HOST"],//obtiene planet.shop, gameplanet.com
      "creditos"=>$params['creditos'],
      "payment_method"=>$params['payment_method'],
      "wc_id_customer"=>$user->id,
      "wc_id_product"=>$product,
      //"token"=>"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJkYXRhIjp7ImlkX2NsaWVudGUiOiIxNTYxNDAyIiwiZW1haWwiOiJodWdvLmZsb3Jlc0BnYW1lcGxhbmV0LmNvbSIsIm5vbWJyZSI6Ikh1Z29JY2VsbyJ9LCJpYXQiOjE2NjM3OTc5MTYsImV4cCI6MTY2Mzg4NDMxNn0.M_mwbTIdhOCWgNB2-Kgb7U29F8V3u2z0U0ibGZRjMK0",
      "token"=>$user->token_inicio//esto si funciona en planet hayq ue ver en los demas entornos
    ];
    //se hace la peticion
    $responseAPI = APICashCrearOrden($params);

    $responseAPI['params']=$params;
    if ($responseAPI['success']) {
      gameplanet_cash_logs("ajax_cash_crear_orden", "Peticion Exitosa", $responseAPI['data']['referencia']);

      $responseAPI['data']['store']=cashGetInfoPaymentMethod($params['payment_method']);
      $responseAPI['content'] = cashSuccessMessage($responseAPI);
    } else {
      gameplanet_cash_logs("ajax_cash_crear_orden", "Peticion Fallo", $responseAPI);
      $responseAPI['content'] = cashFailMessage($responseAPI);
    }
    echo json_encode($responseAPI);
    die();
  } catch (\Exception $e) {
    gameplanet_cash_logs("Codereddemer", "Error interno ", $e->getLine() . ',' . $e->getMessage());
    echo json_encode([
      "success" => false,
      "message" => "Error interno",
      "code" => 500,
      "data" => null
    ]);
    die();
  }
}
function cashValidate($params)
{
  $data = [
    "success" => true,
    "message" => "Formulario valido",
    "data" => [],
  ];
  //antes de cualquier cosa se valida el captcha
  /*  $validCaptcha = validaGoogleCaptcha($params['g-recaptcha-response']);
  if (!$validCaptcha['success']) {
    return $validCaptcha;
  }
 */
  foreach ($params as $key => $value) {
    switch ($key) {

      case 'creditos':
        if (empty($value)) {
          $data['success'] = false;
          $data['message'] = "Información invalida";
          $data['data'][] = "Se debe especificar el monto del credito";
        }
        break;
      case 'payment_method':
        if (empty($value)) {
          $data['success'] = false;
          $data['message'] = "Información invalida";
          $data['data'][] = "Se debe especificar el establecimiento donde se hará el abono";
        }
        break;
      default:

        break;
    }
  }
  return $data;
}
/**
 * Metodo para hacer la busqueda de intercambios
 */
function APICashCrearOrden($params)
{
  gameplanet_cash_logs("APICashCrearOrden", "Se hace la peticion de crear orden a la API");

  /*Le llame diferente a proposito para que no sepan como se pasan a la API*/
  $token = $params['token'];
  unlink($params['token']);
  $args = array(
    'timeout'     => 30,
    'body' => json_encode($params),
    'headers' => array(
      'Content-Type' => 'application/json',
      'data-jwt-master' => get_option('data-jwt-master'),
      'data-jwt-customer' => $token
    )
  );


  $url = get_option('ruta_gameplanet') . "gp_cash/crear";

  $response = wp_remote_post($url, $args);

  // Si hay un error
  if (is_wp_error($response)) {
    $error_message = $response->get_error_message();
    gameplanet_cash_logs("APICashCrearOrden", "Error en la peticion ", $error_message);
    return [
      "success" => false,
      "message" => "Error al buscar",
      "data" => [$error_message],
    ];
  }
  gameplanet_cash_logs("APICashCrearOrden", "Peticion Completa");
  $res = json_decode($response['body'], true);
  return $res;
}


function cashSuccessMessage($response)
{
  $message = $response['message'];
  $response = $response['data'];
  ob_start();

  ?>
    <div class="large-4 col" id="cash_form_wrapper" style="margin:auto">
      <div class="row slide-top" id="response_content">
        <div class="large-12 col ">
          <div class="col-inner" id="response_content_wrapper">
            <div class="gp_message success gp_float_box mb-0 mt-0 ml-0 mr-0" style="display:grid">
              <div class="row">
                <div class="large-12 col mb-0 pb-0">
                  <div class="col-inner" style="display:flex">
                    <div class="icon-container">
                      <div>
                        <i class="icon-checkmark"></i>
                      </div>
                    </div>
                    <div style="text-align:center;padding:0 1em">
                      <h3 ><?php echo($response['store']['message'])?></h3>

                    </div>
                  </div>

                </div>
              </div>
              <div class="row">
                <div class="large-12 col mb-0 pb-0">
                  <div class="col-inner">

                    <div class="cash_response_inst" >
                      <p>Lleva este codigo al establecimiento, <i><?php echo($response['store']['name'])?></i>, mas cercano. <br>
                        Acude a la Caja registradora y menciona que harás un pago. </p>
                    </div>

                    <div class="cash_response_info" >
                      
                      <p>Realiza tu pago antes del: <strong><?php echo($response['expiracion'])?></strong> <br>
                        Tipo de pago: <strong>Solamente Efectivo</strong> </p>

                      <img id="cash_response_payemnt_method" class="image_option" src="<?php echo($response['store']['image'])?>" alt="" width=80>

                      <div id="cash_response_barcode_container">
                        <img id="cash_response_barcode_img" class="image_option" src="<?php echo($response['barcode_url'])?>" alt="" width=400 height=50>
                        <h3 id="cash_response_barcode_reference"><?php echo($response['referencia'])?></h3>
                      </div>
                    </div>
                    <div id="cash_response_print_button_container">
                      <a href="<?php echo($response['file_url'])?>" target="_blank" id="cash_response_pdf_link" class="button">Imprimir Formato de Pago</a>
                    </div>
                    <div class="cash_response_footer">
                      <p>¡Una vez procesado tu deposito podras ver el saldo en tu Cuenta al instante!</p>
                      <p><strong>IMPORTANTE: De no realizar el depósito antes de la fecha y hora mencionada tu código dejará de funciona y tendras que generar uno nuevo.</strong> </p>
                      <p><strong>Imprime solo si es necesario, también puedes sacar una foto del código de barras y al presentarte en la tienda seleccionada dictar el número al personal en la caja registradora.</strong> </p>
                      <a href="" >Continuar Comprando</a>

                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php
  return ob_get_clean();
};

/**Revisar en shortcode.php si se agregan nuevos */
function cashGetInfoPaymentMethod($method){
  switch ($method) {
    case 'oxxo_cash':
      return[
        "name"=>"OXXO",
        "image"=>"https://upload.wikimedia.org/wikipedia/commons/thumb/6/66/Oxxo_Logo.svg/1200px-Oxxo_Logo.svg.png",
      ];
      break;
    
    default:
      return[
        "name"=>"OXXO",
        "image"=>"https://upload.wikimedia.org/wikipedia/commons/thumb/6/66/Oxxo_Logo.svg/1200px-Oxxo_Logo.svg.png",
      ];
      break;
  }
}
function cashFailMessage($response)
{
  $data = $response['data'];
  if (is_array($response['data'])) {
    $data = implode(', ', $response['data']);
  }
  if($response['code']==401){
    $response['message'] = "Por favor vuelve a iniciar sesión e intenta de nuevo";
  }
  ob_start();

  ?>
  <div class="gp_fail-box gp_float_box mt-0 ml-0 mr-0 mb-1">
    <div id="fail_section_header" class="factura-fail_section_header">
      <div class="icon-container">
        <i class="icon-plus"></i>
      </div>
      <div class="message-container">
        <h3>¡Algo Salío Mal!</h3>
      </div>
    </div>
    <div id="fail_section_response">
      <div class="response">
        <p><?php echo ($response['message']) ?></p>
      </div>
      <div class="data">
        <p><?php echo ($data) ?></p>
      </div>
    </div>
  </div>
<?php
  return ob_get_clean();
};
