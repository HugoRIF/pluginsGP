<?php

function gameplanet_coderedeemer_logs($funcion, $mensaje, $extra = null)
{
  $directorio = './gp/logs/gameplanet_coderedeemer_logs/';

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
function ajax_crd_send_ticket()
{
 
  try {
    $params = $_POST;
    gameplanet_coderedeemer_logs("ajax_crd_send_ticket", "Inicia Coderedeemer");

    //validamos el nonce 
   /*  $nonce = $params['nonce'];
    if(! wp_verify_nonce($nonce,'coderedeemer_consulta_ticket')){
      echo json_encode([
        "success" => false,
        "message" => "No estas autorizado para entrar a esta seccion",
        "code" => 401,
        "data" => "CODE-00401 Sin autoización"
      ]);
      die();
    } */
    $validInfo = coderedeemerValidate($params);
    if (!$validInfo['success']) {
      gameplanet_coderedeemer_logs("ajax_crd_send_ticket", "Falló la validacion", $validInfo);
      $validInfo['params'] = $params;
      echo json_encode($validInfo);
      die();
    }
    $type = "consulta";
    //vemos si el usuario esta logeado hacemos la redencion
    $logged = is_user_logged_in();
    $responseAPI = [
      "success" => false,
      "message" => "no se puede determinar estatus del usuario",
      "data" => "WP ERROR"
    ];
    if ($logged) {
      $type = "redimir";

      //esta loggeado se intenta redimir
      $wpUser = get_current_user_id();
      $params = [
        "origen" => 2,
        "ticket" => $params['ticket'],
      ];
      
      $params["id_cliente"] = get_user_meta($wpUser, "id_gp", true);
      $params["gp_token"] = get_user_meta($wpUser, "token", true);

     /*  $params["id_cliente"] = 1608173;
      $params["gp_token"] = "aab41499c67a1f009fe7b91ac6ad723b"; */

      $responseAPI = APICodeRedeemerRedimir($params);
    } else {
      //no esta logeado consultamos el ticket nada mas
      $responseAPI = APICodeRedeemerConsultar($params['ticket']);
    }


    if ($responseAPI['success']) {
      $responseAPI['content'] = crdContentResponse($responseAPI['data'], $type, $params['ticket']);
    } else {
      gameplanet_coderedeemer_logs("ajax_crd_send_ticket", "La peticion Fallo",$responseAPI);

      $responseAPI['content'] = CoderedeemerFailMessage($responseAPI);
    }
    echo json_encode($responseAPI);
    die();
  } catch (\Exception $e) {
    gameplanet_coderedeemer_logs("Codereddemer", "Error interno ", $e->getLine() . ',' . $e->getMessage());
    echo json_encode([
      "success" => false,
      "message" => "Error interno",
      "code" => 500,
      "data" => null
    ]);
    die();
  }
}

/**
 * Metodo para hacer la redencion de la factura
 */
function APICodeRedeemerRedimir($params)
{
  gameplanet_coderedeemer_logs("Codereddemer Redimir Ticket", "Se hace la peticion de redencion a la API", $params);

  /*Le llame diferente a proposito para que no sepan como se pasan a la API*/
  $args = array(
    'body' => json_encode($params),
    'headers' => array(
      'Content-Type' => 'application/json',
      'data-jwt-master' => get_option('data-jwt-master')
    )
  );


  $url = get_option('ruta_gameplanet') . "coderedeemer/redimir";

  $response = wp_remote_post($url, $args);

  // Si hay un error
  if (is_wp_error($response)) {
    $error_message = $response->get_error_message();
    gameplanet_coderedeemer_logs("Codereddemer Redimir Ticket", "Error en la peticion ", $error_message);
    return [
      "success" => true,
      "message" => "Error al redimir",
      "data" => $error_message,
    ];
  }
  gameplanet_coderedeemer_logs("Codereddemer Redimir Ticket", "Peticion Completa");
  $res = json_decode($response['body'], true);
  return $res;
}


/**
 * Consultamos la informacion de coderedeemer de un tikcet en especifico (solo consulta)
 * @param number $ticket nunmero del ticket a recibir
 * @return array Response respuesta de la API
 */
function APICodeRedeemerConsultar($ticket)
{
  gameplanet_coderedeemer_logs("Codereddemer Consulta Ticket", "inicia la consulta del ticket: ", $ticket);

  $args = array(
    'headers' => array(
      'Content-Type' => 'application/json',
      'data-jwt-master' => get_option('data-jwt-master')
    )
  );

  $url = get_option('ruta_gameplanet') . "coderedeemer/consultar/" . $ticket;

  $response = wp_remote_get($url, $args);

  // Si hay un error
  if (is_wp_error($response)) {
    $error_message = $response->get_error_message();
    gameplanet_coderedeemer_logs("Codereddemer Consulta Ticket", "Error en la peticion ", $error_message);
    return [
      "success" => true,
      "message" => "Error al consultar",
      "data" => $error_message,
    ];
  }
  gameplanet_coderedeemer_logs("Codereddemer Consulta Ticket", "Peticion Completa");
  $res = json_decode($response['body'], true);
  return $res;
}

/**
 * @param array $data data de productos encontrados al consultar o para redimir
 * @param string $type bandera si es el contenido de usuario logeado o no
 */
function crdContentResponse($data, $type, $ticket)
{
  switch ($type) {
    case 'redimir':
      return crdContentRedimir($data, $ticket);
    default:
      return crdContentConsulta($data['lista'], $ticket);
  }
}

function crdContentConsulta($lista, $ticket)
{
  ob_start();

?>
  <div class="gp_message warning gp_float_box mt-0 ml-0 mr-0">
    <div class="icon-container">
      <span class="material-symbols-outlined">
        warning
      </span>
    </div>
    <p>
      Tienes que <strong style="text-decoration: underline;"><a href="#" id="crd_lin_warning_login">Iniciar Sesión</a></strong> para obtener tus codigos
    </p>
  </div>
  <div class="gp_float_box mb-0 ml-0 mr-0">
    <h3>Tu recibo de compra <strong># <?php echo ($ticket) ?></strong> tiene los siguientes códigos:</h3>

    <?php
    foreach ($lista as $item) {
      if($item['con_redeemer']){
      $idProduct = wc_get_product_id_by_sku( $item['upc']);
    $dataProduct = new WC_Product($idProduct);

    ?>
      <div class="row crd-consulta-container">
        <div class="col medium-6 small-12 large-2 pb-0 image-wrapper">
          <div class="col-inner  gp-flex-box">
            <div class="has-hover x md-x lg-x y md-y lg-y">
              <?php
               echo ($dataProduct->get_image('woocommerce_thumbnail',['class'=>"crd-img-consulta"]));
              ?>
              <!-- <img class="crd-img-consulta" src="https://planet-53f8.kxcdn.com/wp-content/uploads/2022/07/29175451/014633382662-f1-22-xsx-2-247x296.jpg" alt="" loading="lazy" width="247" height="200"> -->
            </div>
          </div>
        </div>
        <div class="col medium-6 small-12 large-9 pb-0 descript-wrapper">
          <div class="col-inner product-info summary entry-summary product-summary text-left form-flat">
            <h1 class="product-title product_title entry-title"><?php echo ($item['nombre']) ?></h1>
            <div class="product-title-sub">
              <span> <strong>Plataforma: </strong><?php echo ($item['plataforma']) ?>
            </div>
            <div class="is-divider small"></div>
            <div class="product_meta">
              <!-- <div class="condicion"><span class="condicion uppercase is-large no-text-overflow product-condicion-principal ">NUEVO</span></div> -->
               <span class="sku_wrapper"> SKU: 
                <span class="sku">
                  <?php echo ($item['upc']) ?> 
                </span>
              </span>
              <span class="posted_in">Categoría: <?php echo ($item['categoria']) ?></span>
              <span>Cantidad: <?php echo ($item['cantidad']) ?></span>
              <span style="color:green"> <strong>Tiene código de descarga</strong> <i class="icon-checkmark"></i></span>
              
            </div>
          </div>
        </div>
      </div>
      <div class="is-divider small"></div>
    <?php
      }
    }
    ?>
  </div>

<?php
  return ob_get_clean();
}
function crdContentRedimir($data, $ticket)
{
  ob_start();

?>
  <div class="gp_message success gp_float_box mt-0 ml-0 mr-0">
    <div class="icon-container">
      <div>
        <i class="icon-checkmark"></i>
      </div>
    </div>
    <p>
      Operación exitosa
    </p>
  </div>
  <div class="gp_float_box mb-0 ml-0 mr-0">
    <h3>Tu recibo de compra <strong># <?php echo ($ticket) ?></strong> tiene los siguientes códigos:</h3>

    <?php
    foreach ($data as $item) {
      $idProduct = wc_get_product_id_by_sku($item['upc']);
      $dataProduct = new WC_Product($idProduct);

    ?>
      <div class="row crd-consulta-container">
        <div class="col medium-6 small-12 large-3 pb-0 image-wrapper">
          <div class="col-inner gp-flex-box">
            <div class="has-hover x md-x lg-x y md-y lg-y">
              <?php
              echo ($dataProduct->get_image('woocommerce_thumbnail', ['class' => "crd-img-consulta"]));
              ?>
              <!-- <img class="crd-img-consulta" src="https://planet-53f8.kxcdn.com/wp-content/uploads/2022/07/29175451/014633382662-f1-22-xsx-2-247x296.jpg" alt="" loading="lazy" width="247" height="200"> -->
            </div>
          </div>
        </div>
        <div class="col medium-6 small-12 large-9 pb-0 descript-wrapper">
          <div class="col-inner product-info summary entry-summary product-summary text-left form-flat">
            <h1 class="product-title product_title entry-title"><?php echo ($item['nombre']) ?></h1>  
            <div class="product-title-sub">
              <span> <strong>Plataforma: </strong><?php echo ($item['plataforma']) ?>
            </div>
            <div class="is-divider small"></div>
            <div class="crd-codigo">
              <span>Código: <?php echo ($item['codigo']) ?></span> 
              <span class="material-symbols-outlined crd_copy_code" code="<?php echo ($item['codigo']) ?>">
                content_copy
              </span>
            </div>
            <?php if($item['error'] != "Correcto"){?>
                <div>
                  <span  class="gp_badge error"> <?php echo($item['error'])?></span>
                </div>
              <?php }?>
            <div class="product_meta">
             
             
              <span>Fecha de Redención: <?php echo ($item['fecha']) ?></span>
              <span class="sku_wrapper"> SKU: 
                <span class="sku">
                  <?php echo ($item['upc']) ?> 
                </span>
              </span>
            </div>
            <div class="product-short-description">
              <p>
              <?php echo ($item['detalle']) ?>
              </p>
            </div>
          </div>
        </div>
      </div>
      <div class="is-divider small"></div>
    <?php
    }
    ?>
  </div>

<?php
  return ob_get_clean();
}
function coderedeemerValidate($params)
{
  $data = [
    "success" => true,
    "message" => "Formulario valido",
    "data" => [],
  ];
  //antes de cualquier cosa se valida el captcha
  $validCaptcha = validaGoogleCaptcha($params['g-recaptcha-response']);
  if (!$validCaptcha['success']) {
    return $validCaptcha;
  }

  foreach ($params as $key => $value) {
    switch ($key) {

      case 'ticket':
        if (empty($value) || !is_numeric($value) || strlen($value) != 21) {
          $data['success'] = false;
          $data['message'] = "Información invalida";
          $data['data'][] = "El Número de ticket no es valido";
        }
        break;

      default:

        break;
    }
  }
  return $data;
}

function coderedeemerObtenerLista()
{
  gameplanet_coderedeemer_logs("Codereddemer Lista de productos", "Se consulta la lista");

  /**
   * Vamos por la lista de productos
   */
  $args = array(
    'headers' => array(
      'Content-Type' => 'application/json',
      'data-jwt-master' => get_option('data-jwt-master')
    )
  );

  $url = get_option('ruta_gameplanet') . "coderedeemer/lista";

  $response = wp_remote_get($url, $args);

  // Si hay un error
  if (is_wp_error($response)) {
    $error_message = $response->get_error_message();
    return $error_message;
  }
  $res = json_decode($response['body'], true);

  if (!$res['success']) {
    return $res;
  }
  //si todo esta bien vamos por la rutas y las imagenes
  $productos = $res['data'];
  foreach ($productos as $key => $producto) {
    $productos[$key]['internal_info'] = crdmr_getPlatformInfo($producto['sku']);
  }
  $res['data'] = $productos;
  return $res;
}

/**
 * Obtenemos los productos a apartir de una array de SKUS
 * Puede llegar a sobrecargar la el backend
 */
function crdmr_getPlatformInfo($skus)
{
  //vamos por los productos con el sku
  $link = [];
  $ids = [];

  $query = new WP_Query(array(
    'post_type'      => 'product',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'meta_query' => array(array(
      'key'     => '_sku',
      'value'   => $skus,
      'compare' => 'IN',
    )),
  ));

  if ($query->have_posts()) {
    while ($query->have_posts()) {
      $query->the_post();
      $link[] = get_permalink();
      $ids[] = get_the_ID();
      //$images[] = new WC_Product(get_the_ID());
    }
  } else {
    $link = null;
    $ids = null;
  }
  return [
    "link" => $link,
    "ids" => $ids,
  ];
}
function crdmr_getPlatformInfo_mock($skus)
{
  //vamos por los productos con el sku
  $link = [
    "https://www.google.com/",
    "https://www.google.com/",
    "https://www.google.com/",
  ];
  $ids = [
    1,
    2,
    3,

  ];

  return [
    "link" => $link,
    "ids" => $ids,
  ];
}

/**
 * atributos de response
 * message = mensaje de la api
 * data    = link de coderedeemer
 * 
 */
function CoderedeemerSuccessMessage($response)
{

?>
  <div class="gp_success-box gp_float_box ">
    <div id="success_section_header" class="factura-success_section_header">
      <div class="icon-container">
        <i class="icon-checkmark"></i>
      </div>
      <div class="message-container">
        <h2>¡Solictud de Factura Exitosa!</h2>
      </div>
    </div>
    <div id="success_section_response">
      <div class="response">
        <p><?php echo ($response['message']) ?></p>
      </div>
      <div class="link_button">
        <a href="<?php echo ($response['data']) ?>" target="_blank" class="button">Descargar Aquí</a>

      </div>
      <div class="copy_button">
        <button id="copy_button-button" class="copy_button-button" content="">Copiar enlace</button>
        <p hidden id="factura_link"> <?php echo ($response['data']) ?> </p>
      </div>
    </div>
  </div>
<?php
};
function CoderedeemerFailMessage($response)
{
  $data = $response['data'];
  if (is_array($response['data'])) {
    $data = implode(', ', $response['data']);
  }
  ob_start();

?>
  <div class="gp_fail-box gp_float_box mt-0 ml-0 mr-0 mb-0">
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
