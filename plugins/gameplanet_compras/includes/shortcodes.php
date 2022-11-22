<?php

if (!defined('ABSPATH')) exit;


function f_gameplanet_compras_shortcodes_init()
{
  //! shortcode para la facturacion de cliente
  add_shortcode('hist_compras_gp', 'compras_gp');
}

//Shortcode para mostrar le historial de compras del cliente
function compras_gp()
{
  $version = time();
  wp_enqueue_style( 'compras-styles', plugins_url( '../public/css/gameplanet_compras.css' , __FILE__ ),array(),$version);
  wp_enqueue_script( 'compras-scripts', plugins_url( '../public/js/gameplanet_compras.js' , __FILE__ ), array( 'jquery' ),$version);
  wp_localize_script( 'compras-scripts', 'gp_compra_ajax_param', array(
      'ajaxurl'   => admin_url( 'admin-ajax.php'), // for frontend ( not admin )
      'action'    => AJAX_ACTION_COMPRA_GET, //
      'action_tracking'    => AJAX_ACTION_COMPRA_SEGUIMIENTO, //

     
  ));
  
  if(!is_user_logged_in()){
    //hay que mejorar
    ob_start();
    ?>
      <h1>Por favor inicia sesión para ver tus compras</h1>
    <?php
  
    return ob_get_clean();
  }

  ob_start();
  ?>
    <div id="compras_gp_container">

      <div class="row" id="compras_gp_wrapper">
        <div class="col large-12 compras_loading">
          <span class="loader-general"></span>

        </div>
      </div>
      
    </div>

    <a id="open-seguimiento_envio_compra" href="#modal_seguimiento_envio_compra" hidden> abrir</a>
    <div id="modal_seguimiento_envio_compra" class="lightbox-by-id lightbox-content mfp-hide lightbox-white " style="max-width:600px ;padding:20px">
      <div class="row row-large">
					<div class="col large-12">
						<div class="order-tracking-container">
							<h3 class="uppercase">Seguimiento de la Orden #</h3>
							<div id="order-tracking-wrapper" class="tracking_gp">
								
							</div>
						</div>
					</div>
				</div>
		  </div>
  <?php

  return ob_get_clean();
}

function generate_compras_table($compras){
  ob_start();
  if(count($compras)){

    
    foreach ($compras as $key => $compra) {
   


    ?>
      <div class="col large-12 gp_float_box compra_box_container">
          <div class="row compra_header">
            <div class="col medium-12 small-12 pb-0">
              <h3 class="mb-0">Pedido #<?php echo $compra['orden']?></h3>
              <div>
                <span><strong>Fecha Compra: </strong><?php echo $compra['fecha_format']?></span>
              </div>
              <div>
                <span><strong>Ticket: </strong><?php echo $compra['id_venta']?></span>
              </div>
              <?php if(isset($compra['estatus_tracking']) && !is_null($compra['estatus_tracking'])){?>
                <div>
                  <span><strong><?php echo $compra['estatus_tracking']['evento']?></strong></span>
                </div>
              <?php }?>
            </div>
          </div>
          <hr>
          <div class="row compra_body">
            <div class="col small-12 pb-0">
              <div class="col-inner">
                <?php foreach ($compra['items'] as $keyItem => $item) {
                     $idProduct =  wc_get_product_id_by_sku( $item['upc']);
                     $dataProduct = new WC_Product($idProduct);
                  ?>
                  <div class="row compra_body">
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
                        <?php if($dataProduct->id){?>
                        <a href="<?php echo ($dataProduct->get_permalink());?>"><?php echo $item['nombre_producto'] ?></a> <br>
                        <?php }else{?>
                          <strong><?php echo $item['nombre_producto'] ?></strong> <br>
                        <?php }?>

                        <span class="value" style="margin:0"><?php echo $item['upc'] ?></span>
                        <ul class="compra-item-meta value">
                          <li>
                            <strong >Cantidad:</strong> <?php echo $item['cantidad'] ?>
                          </li>
                          <li>
                            <strong >Precio:</strong> <?php echo $item['precio'] ?>
                          </li>
                        </ul>
                      </div>
                    </div>
                    <div class="col medium-5 small-12 pb-0 float_box">
                      <div class="col-inner compra_info">
                       
                      </div>
                    </div>
                  </div>
                <?php }?>
                
              </div>
            </div>
            
          </div>
          <hr>
          <div class="row compra_summary">
            <?php if(isset($compra['direccion_envio']) && !is_null($compra['direccion_envio'])){?>
            <div class="col medium-4 small-12 pb-0">
              <div class="col-inner compra_envio">
                <h5>Dirección de Envio</h5>
                <div>
                  <p>
                    <?php echo $compra['direccion_envio']['nombre'].' '. $compra['direccion_envio']['apellido']?>, <?php echo $compra['direccion_envio']['direccion']?>, <?php echo $compra['direccion_envio']['telefono']?>
                  </p>
                </div>
              </div>
            </div>
            <?php }?>

            <div class="col medium-3 small-12 pb-0">
              <div class="col-inner">
                <h5>Método de Pago</h5>
                <div>
                  <p><?php echo $compra['metodos_de_pago']?></p>
                </div>

              </div>
            </div>
            <div class="col medium-5 small-12 pb-0">
              <div class="col-inner">
                <h5>Resumen del Pedido</h5>
                <div>
                  <?php 
                    foreach ($compra['totales'] as  $totales) {
                      foreach ($totales as  $total) {
                        if($total['label'] == 'Total (IVA incluido)'){?>
                        <div class="row">
                          <div class="col small-6 pb-0 "><strong> Total (IVA incluido): </strong></div>
                          <div class="col small-6 pb-0 force_right"><strong class="por_pagar"><?php echo $total['valor']?></strong></div>
                        </div>
                        <?php } else{ ?>
                          <div class="row">
                            <div class="col small-6 pb-0"><?php echo $total['label']?></div>
                            <div class="col small-6 pb-0 force_right"><?php echo $total['valor']?></div>
                          </div>
                  <?php 
                        }
                      }
                    }
                  ?>
                 
                </div>

              </div>
            </div>
          </div>
          <hr>
          <div class="row compra_actions">
            <div class="col medium-4 small-12 pb-0">
              <div class="col-inner">
               <!--  <a class="button">Abonar A mi preventa</a> -->
              </div>
            </div>
            <div class="col medium-4 small-12 pb-0">
              <div class="col-inner">
              </div>
            </div>
            <div class="col medium-4 small-12 pb-0">
              <?php if(isset($compra['direccion_envio']) && !is_null($compra['direccion_envio'])){?>
                <button class="seguimiento_envio_compra button" ticket="<?php echo $compra['id_venta']?>" shipping='<?php echo json_encode($compra['direccion_envio'])?>' date="<?php echo $compra['fecha']?>"> Seguimiento de Envio</button>
              <?php }?>
            </div>
          </div>
      </div>

    <?php
    }
  }
  else{
    ?> 
      <h2> Mis Compras</h2>
      <p> Aun no tienes ninguna compra</p>
     
    <?php
  }

  return ob_get_clean();
  
}


