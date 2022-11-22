<?php

function gameplanet_intercambio_logs($funcion, $mensaje, $extra = null)
{
  $directorio = './gp/logs/gameplanet_intercambio_logs/';

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
function ajax_intercambio_buscar()
{

  try {
    $params = $_POST;
    gameplanet_intercambio_logs("Intercambio", "Empieza Intercambio");

    $validInfo = intercambioValidate($params);
    if (!$validInfo['success']) {
      gameplanet_intercambio_logs("Intercambio", "Falló la validacion", $validInfo);
      $validInfo['params'] = $params;
      $validInfo['alert'] = intercambioFailMessage($validInfo);
      echo json_encode($validInfo);
      die();
    }
    $type = "consulta";

    $responseAPI = APIintercambioBuscar($params);

    if ($responseAPI['success']) {
      gameplanet_intercambio_logs("Intercambio", "Peticion exitosa");
      
      $responseAPI['alert'] = intercambioSuccessMessage($responseAPI);
      $responseAPI['content'] = intercambioContentResponse($responseAPI['data']['productos']);
    } else {
      gameplanet_intercambio_logs("Intercambio", "Peticion Fallo",$responseAPI);

      $responseAPI['alert'] = intercambioFailMessage($responseAPI);
      $responseAPI['content'] = '';
    }
    echo json_encode($responseAPI);
    die();
  } catch (\Exception $e) {
    gameplanet_intercambio_logs("Codereddemer", "Error interno ", $e->getLine() . ',' . $e->getMessage());
    echo json_encode([
      "success" => false,
      "message" => "Error interno",
      "code" => 500,
      "data" => null
    ]);
    die();
  }
}
function intercambioValidate($params)
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

      case 'search':
        if (empty($value)) {
          $data['success'] = false;
          $data['message'] = "Información invalida";
          $data['data'][] = "El Nombre del Juego o UPC es obligatorio";
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
function APIintercambioBuscar($params)
{
  gameplanet_intercambio_logs("Inetercambio Busqueda API", "Se hace la peticion de busqueda a la API", $params);

  /*Le llame diferente a proposito para que no sepan como se pasan a la API*/
  $args = array(
    'timeout'     => 30,
    'headers' => array(
      'Content-Type' => 'application/json',
      'data-jwt-master' => get_option('data-jwt-master')
    )
  );


  $url = get_option('ruta_gameplanet') . "intercambio/buscar/" . $params['search'];

  $response = wp_remote_get($url, $args);

  // Si hay un error
  if (is_wp_error($response)) {
    $error_message = $response->get_error_message();
    gameplanet_intercambio_logs("Inetercambio Busqueda API", "Error en la peticion ", $error_message);
    return [
      "success" => false,
      "message" => "Error al buscar",
      "data" => [$error_message],
    ];
  }
  gameplanet_intercambio_logs("Inetercambio Busqueda API", "Peticion Completa");
  $res = json_decode($response['body'], true);
  return $res;
}


/**
 * @param array $data data de productos encontrados al consultar o para redimir
 * @param string $type bandera si es el contenido de usuario logeado o no
 */
function intercambioContentResponse($data,)
{
  ob_start();
  foreach ($data as $item) {
    $a_cuenta = explode(".",$item['a_cuenta']);
    $a_efectivo = explode(".",$item['a_efectivo']);
    $idProduct = wc_get_product_id_by_sku( $item['upc']);
    if($idProduct  >0){

    $dataProduct = new WC_Product($idProduct);
?>
    <div class="product-small col has-hover product type-product post-401785 status-publish first instock product_cat-hardware product_tag-nsw product_tag-splatoon product_tag-splatoon3d1 has-post-thumbnail taxable shipping-taxable purchasable product-type-simple">
      <div class="col-inner">
        <div class="product-small box ">
          <div class="box-image">
            <div class="image-none">
              <a href="<?php echo ($dataProduct->get_permalink());?>">
              <?php
                 echo ($dataProduct->get_image('woocommerce_thumbnail',['class'=>"attachment-woocommerce_thumbnail size-woocommerce_thumbnail"]));
              ?>
                <!-- <img class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail" src="https://planet-53f8.kxcdn.com/wp-content/uploads/2022/07/29175451/014633382662-f1-22-xsx-2-247x296.jpg" alt="" loading="lazy" width="247" height="200"> -->
              </a>
            </div>
            <div class="image-tools is-small top right show-on-hover">
            </div>
            <div class="image-tools is-small hide-for-small bottom left show-on-hover">
            </div>
            <div class="image-tools grid-tools text-center hide-for-small bottom hover-slide-in show-on-hover">
            </div>
          </div>

          <div class="box-text box-text-products text-center grid-style-2">
            <div class="title-wrapper">
              <p class="category uppercase is-smaller no-text-overflow product-cat op-7">
              <?php echo($item['categoria'])?> </p>
              <p class="name product-title woocommerce-loop-product__title"><a target="_blank" href="<?php echo ($dataProduct->get_permalink());?>" class="woocommerce-LoopProduct-link woocommerce-loop-product__link"><?php echo($item['nombre'])?></a></p>
           
            </div>
            
            <div class="price-wrapper intercambio_a_cuenta_txt">
              <span class="price intercambio_price">
                <p class="gp_precio_ps" style="line-height: 0.5em; margin-top: 0.6em; margin-bottom: 0.6em;">A tu cuenta: <ins><span class="price-symbol">$</span><?php echo($a_cuenta[0])?><span class="price-fraction"><?php echo($a_cuenta[1])?></span></ins> </p>
              </span>
            </div>
            <div class="price-wrapper intercambio_a_efectivo_txt">
              <span class="price intercambio_price">
              <p class="gp_precio_ps" style="line-height: 0.5em; margin-top: 0.6em; margin-bottom: 0.6em;"> En efectivo: <ins><span class="price-symbol">$</span><?php echo($a_efectivo[0])?><span class="price-fraction"><?php echo($a_efectivo[1])?></span></ins> </p>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php
    }
  }
  return ob_get_clean();
}




/**
 * Obtenemos los productos a apartir de una array de SKUS
 * Puede llegar a sobrecargar la el backend
 */
function intercambio_getPlatformInfo($skus)
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
function intercambio_getPlatformInfo_mock($skus)
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


function intercambioSuccessMessage($response)
{
  ob_start();
  if (count($response['data']['productos'])) {
  ?>
    <div class="gp_message success gp_float_box mb-0 mt-0 ml-0 mr-0">
      <div class="icon-container">
        <div>
          <i class="icon-checkmark"></i>
        </div>
      </div>
      <p>
        <?php echo ($response['message']) ?>
      </p>
    </div>
  <?php
  } else {
  ?>
    <div class="gp_message warning gp_float_box mb-0 mt-0 ml-0 mr-0">
      <div class="icon-container">
        <span class="material-symbols-outlined">
          warning
        </span>
      </div>
      <p>
        No se encontrarón productos
      </p>
    </div>
  <?php
  }
  return ob_get_clean();
};

function intercambioFailMessage($response)
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
