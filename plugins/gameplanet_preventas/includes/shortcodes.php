<?php

if (!defined('ABSPATH')) exit;


function f_gameplanet_preventas_shortcodes_init()
{
  //! shortcode para la facturacion de cliente
  add_shortcode('preventas_gp', 'preventas_gp');
}

//Shortcode para mostrar le historial de preventas del cliente
function preventas_gp()
{

  $version = date('Ymds');
  wp_enqueue_style( 'preventas-styles', plugins_url( '../public/css/gameplanet_preventas.css' , __FILE__ ),array(),$version);
  wp_enqueue_script( 'preventas-scripts', plugins_url( '../public/js/gameplanet_preventas.js' , __FILE__ ), array( 'jquery' ),$version);
  wp_localize_script( 'preventas-scripts', 'gp_preventa_ajax_param', array(
      'ajaxurl'   => admin_url( 'admin-ajax.php'), // for frontend ( not admin )
      'action'    => AJAX_ACTION_PREVENTA_GET, //
      'action_sucursales'    => AJAX_ACTION_PREVENTA_SUCURSALES, //
      'action_reasignar'    => AJAX_ACTION_PREVENTA_REASIGNAR, //
     
  ));
  wp_localize_script( 'preventas-scripts', 'gp_preventa_abonos_ajax_param', array(
      'ajaxurl'   => admin_url( 'admin-ajax.php'), // for frontend ( not admin )
      'action'    => AJAX_ACTION_PREVENTA_ABONOS, //
     
  ));

  if(!is_user_logged_in()){
    //hay que mejorar
    ob_start();
    ?>
      <h1>Por favor inicia sesión para ver tus preventas</h1>
    <?php
  
    return ob_get_clean();
  }

  ob_start();
  ?>
    <div id="preventas_gp_container">

      <div class="row" id="preventas_gp_wrapper">
        <div class="col large-12 preventas_loading">
          <span class="loader-general"></span>

        </div>
      </div>
      
    </div>

    <a id="open-modal_preventa_historial_abonos" href="#modal_preventa_historial_abonos" hidden> abrir</a>
    <div id="modal_preventa_historial_abonos" class="lightbox-by-id lightbox-content mfp-hide lightbox-white " style="max-width:1080px ;overflow:auto ;padding:20px">
      <div class="row row-large">
        <div class="col large-12">
          <div class="col-inner">
            <h3>Historial de Abonos</h3>
            <div id="historial-abonos-table-container">
            </div>
          </div>
        </div>
      </div>

    </div>
    <a id="open-modal_preventa_reasignar_tienda" href="#modal_preventa_reasignar_tienda" hidden> abrir</a>

    <div id="modal_preventa_reasignar_tienda" class="lightbox-by-id lightbox-content mfp-hide lightbox-white " style="max-width:700px ;padding:20px">
      <div class="row row-large">
        <div class="col large-12 pb-0">
          <div class="col-inner">
            <h3>Reasignar Tienda</h3>
            <p>Por favor selecciona la tienda donde quieres recibir tu producto.</p>
            <div id="reasignar_tienda_preventa-container">
              <div class="col large-12 pb-0 preventas_loading" style="height:auto">
                <span class="loader-general"></span>
              </div>
              <div id="reasignar_tienda_preventa-wrapper" class="row row-large"style="display:none">
                <div  class="col large-12 pb-0">
                  <div id="sucursales_search-container">
                    <label for="sucursales_search" id="sucursales_search_filter-container">
                      <span >
                        Buscar por:
                      </span>
                      <select name="sucursales_search_filter" id="sucursales_search_filter">
                        <option value="tienda">Nombre</option>
                        <option value="direccion">Dirección</option>
                        <option value="estado">Estado</option>
                      </select>
                    </label>
                  
                    <input type="text" id="sucursales_search">
                  </div>
                  <form action="POST" id="prev_asignar_tienda_form">
                    <input type="hidden" id="preventa_re_id_saldo" name="id_saldo">
                    <input type="hidden" id="preventa_re_id_tienda_origen" name="id_tienda_origen">
                    <div class="col-inner" id="preventa_lista_sucursales"></div>
                  </form>
                <div class="col small-12 pb-0">
                  <div class="preventa_asignar_tienda_actions">
                    <button id="preventa_asignar_tienda_confirm" class="button">Asignar Tienda</button>
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
}

function generate_preventas_table($preventas){
  ob_start();
  if(count($preventas)){

    $preventas = array_reverse($preventas);
    
    foreach ($preventas as $key => $preventa) {
      $idProduct = null;
      if(!is_null($preventa['upc_venta'])){
        $idProduct = wc_get_product_id_by_sku( $preventa['upc_venta']);
        if(is_null($idProduct)){
          $idProduct = wc_get_product_id_by_sku( $preventa['upc_preventa']);
        }
      }
      else{
        $idProduct = wc_get_product_id_by_sku( $preventa['upc_preventa']);
      }
      $dataProduct = new WC_Product($idProduct);

      $puedeReasignar = false;
      if($preventa['premitir_reasignacion_otra_tienda'] && $preventa['id_tienda'] != 250){
        $puedeReasignar = true;
        /**
         * 2022-11-10 Quitar cuando EPOD funciones correctamente al desactivar preventas
         * hardcodeo de la fecha actual esto por que no esta funcioanando EPOD
         */
        //$hoy = date('Y-m-d');

      }

    ?>
      <div class="col large-12 gp_float_box preventa_box_container">
          <div class="row preventa_header">
            <div class="col medium-12 small-12 pb-0">
              <h3 class="mb-0">Pedido #<?php echo $preventa['orden']?></h3>
              <div>
                <span><strong>Fecha Apartado: </strong><?php echo $preventa['fecha_preventa_adquirida']?></span>
              </div>
              <div>
                <span><strong>Ticket: </strong><?php echo $preventa['transaccion']?></span>
              </div>
              <div class="message_status <?php echo $preventa['message_status']['status_color']?>">
                <span><strong><?php echo $preventa['message_status']['message']?> </strong></span>
              </div>
            </div>
          </div>
          <hr>
          <div class="row preventa_body">
            <div class="col medium-2 small-6 pb-0 imagen-producto">
              <div class="col-inner">
                <div class="box-image">
                  <div class="image-none">
                    <?php
                    echo ($dataProduct->get_image('woocommerce_thumbnail',['class'=>"attachment-woocommerce_thumbnail size-woocommerce_thumbnail"]));
                    ?>
                      <!-- <img class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail" src="https://planet-53f8.kxcdn.com/wp-content/uploads/2022/07/29175451/014633382662-f1-22-xsx-2-247x296.jpg" alt="" loading="lazy" width="247" height="200"> -->
                  </div>
                  <div class="image-tools is-small top right show-on-hover">
                  </div>
                  <div class="image-tools is-small hide-for-small bottom left show-on-hover">
                  </div>
                  <div class="image-tools grid-tools text-center hide-for-small bottom hover-slide-in show-on-hover">
                  </div>
                </div>
              </div>
              
            </div>
            <div class="col medium-5 small-12 pb-0 float_box info-tienda">
              <div class="col-inner">
                <a href="<?php echo ($dataProduct->get_permalink());?>"><?php echo $preventa['nombre'] ?></a> <br>
                <span class="value" style="margin:0"><?php echo $preventa['upc_preventa'] ?></span>
                <ul class="preventa-item-meta value">
                  <li>
                    <strong >Cantidad:</strong> <?php echo $preventa['cantidad'] ?>
                  </li>
                  <li>
                    <strong >Tienda de Redención:</strong> <span class="gp_entrega_en"><?php echo $preventa['nombre_tienda'] ?></span>  <?php echo $preventa['message_store']?>
                  </li>

                </ul>
              </div>
            </div>
            <div class="col medium-5 small-12 pb-0 float_box">
              <div class="col-inner preventa_info">
                <div class="row fecha">
                  <div class="col small-12 pb-0">
                    <strong>Fecha Lanzamiento:</strong>
                  </div>
                  <div class="col small-12 pb-0 ">
                    <span><?php echo $preventa['fecha_lanzamiento']?></span>
                  </div>
                </div>
                <div class="row fecha">
                  <div class="col small-12 pb-0">
                    <strong>Fecha Límite Redención:</strong>
                  </div>
                  <div class="col small-12 pb-0 ">
                    <span><?php echo $preventa['fecha_limite_redencion']?></span>
                  </div>
                </div>
                <div class="row fecha">
                  <div class="col small-12 pb-0">
                    <strong>Fecha Límite Reasignación de Tienda:</strong>
                  </div>
                  <div class="col small-12 pb-0 ">
                    <span><?php echo $preventa['fecha_limite_reasignacion_tienda']?></span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <hr>
          <div class="row preventa_summary">
            <div class="col medium-4 small-12 pb-0">
              <div class="col-inner preventa_envio">
                <h5>Dirección de Envio</h5>
                <div>
                  <?php if($preventa['direccion_envio']['id_tienda'] == 250){?>
                    <p>
                      <?php echo $preventa['direccion_envio']['enviado_a']?>, <?php echo $preventa['direccion_envio']['direccion']?>
                    </p>
                  <?php } else {?>
                    <p>
                    <span class="gp_entrega_en"><?php echo $preventa['direccion_envio']['tienda']?></span>, <?php echo $preventa['direccion_envio']['direccion']?> <br>
                    <span class="tipo_operacion op-<?php echo $preventa['direccion_envio']['id_tipo_operacion']?>"> <?php echo $preventa['direccion_envio']['tipo_operacion']?></span>
                    </p>
                  <?php } ?>
                </div>
              </div>
            </div>
            <div class="col medium-3 small-12 pb-0">
              <div class="col-inner">
                <h5>Método de Pago</h5>
                <div>
                  <p><?php echo $preventa['metodo_pago']?></p>
                </div>

              </div>
            </div>
            <div class="col medium-5 small-12 pb-0">
              <div class="col-inner">
                <h5>Resumen del Pedido</h5>
                <div>
                  <div class="row">
                    <div class="col small-5 pb-0">Total Abonado:</div>
                    <div class="col small-7 pb-0 force_right"><?php echo $preventa['payment_summary']['total_abonado']?></div>
                  </div>
                  <div class="row" style="margin-bottom:1em">
                    <div class="col small-5  pb-0">Precio Final:</div>
                    <div class="col small-7  pb-0 force_right"><?php echo $preventa['payment_summary']['precio_final']?></div>
                  </div>
                  <div class="row">
                    <div class="col small-5 pb-0 "><strong> Total por pagar (IVA incluido): </strong></div>
                    <div class="col small-7 pb-0 force_right"><strong class="por_pagar <?php echo $preventa['message_status']['color_por_pagar']?>"><?php echo $preventa['payment_summary']['total_por_pagar']?></strong></div>
                  </div>
                </div>

              </div>
            </div>
          </div>
          <hr>
          <div class="row preventa_actions">
            <div class="col medium-4 small-12 pb-0">
              <div class="col-inner">
               <!--  <a class="button">Abonar A mi preventa</a> -->
              </div>
            </div>
            <div class="col medium-4 small-12 pb-0">
              <div class="col-inner">
              <?php  
              //solo se puede reasignar entre tiendas
              if($puedeReasignar){?>
                <button id="reasignar_preventa" class="button" id_saldo="<?php echo $preventa['id_saldo']?>" current_id_tienda="<?php echo $preventa['id_tienda']?>">Reasignar Tienda</button>
              <?php }?>
              </div>
            </div>

            <div class="col medium-4 small-12 pb-0">
                <button class="ver_historial_abono button" transaction="<?php echo $preventa['transaccion']?>"> Historial de Abonos</button>
            </div>
          </div>
      </div>

    <?php
    }
  }
  else{
    ?> 
      <h2> Mis Preventas</h2>
      <p> Todavia no tines preventas, puedes ver las preventas disponibles para comprar aquí <a href="/atributo-producto/condicion/preventa/">Ver Preventas</a></p>
    <?php
  }

  return ob_get_clean();
  
}


