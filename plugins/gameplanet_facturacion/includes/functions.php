<?php

function plugin_factura_logs($funcion, $mensaje, $extra = null)
{
  $directorio = './gp/logs/plugin_factura_logs/';

  if (!file_exists($directorio)) {
    mkdir($directorio, 0755, true);
  }

  $tiempo    = current_time('mysql');
  $fecha     = strtotime($tiempo);
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
 * metodo para validar y obtener la factura al hacer submit
 */
function ajax_facturacion_generate()
{
  try {
    $params = $_POST;
    
    plugin_factura_logs("ajax_facturacion_generate", "Inicia la generacion de factura");
    $validInfo = facturaValidate($params);
    if (!$validInfo['success']) {
      $validInfo['content'] = facturaGenerateContent($validInfo);
      plugin_factura_logs("ajax_facturacion_generate", "Falló la validacion", $validInfo);
      return $validInfo;
    }
    $resposeAPI = facturacionAPIObtenerFactura($params);
    if($resposeAPI['success']){
      plugin_factura_logs("ajax_facturacion_generate", "Peticion exitosa",$resposeAPI['data']);
    }
    else{
      plugin_factura_logs("ajax_facturacion_generate", "Peticion fallo",$resposeAPI);
    }
    $resposeAPI['content'] = facturaGenerateContent($resposeAPI); 

    echo json_encode($resposeAPI);
    die();
  } catch (Exception $e) {
    echo json_encode([
      "success" => false,
      "message" => "Error interno: " . $e->getLine() . ', ' . $e->getMessage(),
      "data"    => []
    ]);
    die();
  }
}


/**
 * Metodo para hacer la peticion a la factura
 */
function facturacionAPIObtenerFactura($params)
{
  $final_params = array(
    'rfc_cliente'            => $params['facturacion_rfc'],
    'razon_social_cliente'   => $params['facturacion_razon_social'],
    'cp_cliente'             => $params['facturacion_cp'],
    'regimen_fiscal_cliente' => $params['facturacion_regimen'],
    'uso_cfdi'               => $params['facturacion_cfdi'],
    'ticket'                 => $params['facturacion_ticket'],
    'total'                  => $params['facturacion_total'],
    'empresa'                => "gp",
    'team_gp'                => 0,
  );
  plugin_factura_logs("facturacionAPIObtenerFactura", "Se hace la peticion a la API", $final_params);

  /*Le llame diferente a proposito para que no sepan como se pasan a la API*/
  $args = array(
    'timeout' => 120,
    'body'    => json_encode($final_params),
    'headers' => array(
      'Content-Type'    => 'application/json',
      'data-jwt-master' => get_option('data-jwt-master')
    )
  );


  $url = get_option('ruta_gameplanet') . "facturacion_v4/timbra_cliente";  
  //$url = "https://api.gameplanet.com/v1/facturacion_v4/timbra_cliente";

  $response = wp_remote_post($url, $args);

  // Si hay un error
  if (is_wp_error($response)) {
    $error_message = $response->get_error_message();
    plugin_factura_logs("facturacionAPIObtenerFactura", "Respuesta de la API", $error_message);
    return [
      "success" => false,
      "message" => "Tenemos problemas de conexión, por favor intenta mas tarde",
      "data"    => $error_message,
    ];
  } else {
    $res = json_decode($response['body'], true);
    plugin_factura_logs("facturacionAPIObtenerFactura", "Peticion completa");

    return $res;
  }
}



function facturaValidate($params)
{

  $data = [
    "success" => true,
    "message" => "Formulario valido",
    "data"    => [],
  ];
  //antes de cualquier cosa se valida el captcha
  $validCaptcha = validaGoogleCaptcha($params['g-recaptcha-response']);
  if (!$validCaptcha['success']) {
    return $validCaptcha;
  }

  foreach ($params as $key => $value) {
    switch ($key) {
      case 'facturacion_rfc':
        if (empty($value) || strlen($value) < 12 || strlen($value) > 13) {
          $data['success'] = false;
          $data['message'] = "Información invalida";
          $data['data'][]  = "RFC invalido";
        }
        break;
      case 'facturacion_razon_social':
        if (empty($value)) {
          $data['success'] = false;
          $data['message'] = "Información invalida";
          $data['data'][]  = "La Razón Social es Requerida";
        }
        break;

      case 'facturacion_cp':
        if (empty($value) || !is_numeric($value) || strlen($value) != 5) {
          $data['success'] = false;
          $data['message'] = "Información invalida";
          $data['data'][]  = "Código Postal Invalido";
        }
        break;
      case 'facturacion_regimen':
        if (empty($value)) {
          $data['success'] = false;
          $data['message'] = "Información invalida";
          $data['data'][]  = "El Regimen Fiscal es obligatorio";
        }
        break;
      case 'facturacion_cfdi':
        if (empty($value)) {
          $data['success'] = false;
          $data['message'] = "Información invalida";
          $data['data'][]  = "El uso de CFDI es obligatorio";
        }
        break;
      case 'facturacion_ticket':
        if (empty($value) || !is_numeric($value) || strlen($value) != 21) {
          $data['success'] = false;
          $data['message'] = "Información invalida";
          $data['data'][]  = "El Número de ticket no es valido";
        }
        break;
      case 'facturacion_total':
        $valueArr = explode('.', $value);
        $value    = (float) $value;


        if (empty($value) || !is_numeric($value) || $value == 0 || count($valueArr) > 2) {
          $data['success'] = false;
          $data['message'] = "Información invalida";
          $data['data'][]  = "El total de tu compra no es valido";
        }
        break;
      default:

        break;
    }
  }
  return $data;
}

function facturaObtenerCatalogos()
{
  /**
   * Vamos por los catalogos del formulario
   */
  $args = array(
    'headers' => array(
      'Content-Type'    => 'application/json',
      'data-jwt-master' => get_option('data-jwt-master')
    )
  );

  $url = get_option('ruta_gameplanet') . "facturacion_v4/catalogos";

  $response = wp_remote_get($url, $args);

  // Si hay un error
  if (is_wp_error($response)) {
    plugin_factura_logs("facturaObtenerCatalogos","Error al obtener catalogos" ,$response->get_error_message());

    $error_message = $response->get_error_message();
    return $error_message;
  }
  $res = json_decode($response['body'], true);
  return $res;
}


function getRegimen($data, $search)
{
  $ids   = array_column($data, 'id_regimen');
  $index = array_search($search, $ids);

  return $data[$index];
}
function getCFDI($data, $search)
{
  $ids   = array_column($data, 'id_uso');
  $index = array_search($search, $ids);
  return $data[$index];
}

function facturaGenerateContent($response)
{
  ob_start();
  if ($response['success']) {
?>
    <div class="row">
      
      <div class="large-12 col pb-0" id="success-section">
        <div class="row">
          <div class="large-12 col">
            <?php FacturaSuccessMessage($response); ?>

          </div>
        </div>
        <div class="row">
          <div class="large-12 col mb-0 pb-0">
            <div class="gp_float_box mt-0 ml-0 mr-0 mb-0">
              <h4>
                La factura se solicito con la siguiente información.
              </h4>
              <p>
                <strong>RFC : </strong> <?php echo ($_POST['facturacion_rfc']) ?> <br>
                <strong>Razón Social : </strong> <?php echo ($_POST['facturacion_razon_social']) ?><br>
                <strong>Código Postal : </strong> <?php echo ($_POST['facturacion_cp']) ?><br>
                <strong>Número de Ticket : </strong> <?php echo ($_POST['facturacion_ticket']) ?><br>
                <strong>Monto total de compra: </strong> $ <?php echo (floatval($_POST['facturacion_total'])) ?><br>

              </p>
              <a href="">Facturar otra compra</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php
  } else {
  ?>
    <div class="row">
      <div class="large-12 col pb-0" >
        <?php
        FacturaFailMessage($response);
        ?>
      </div>
    </div>
<?php
  }
  return ob_get_clean();
}
