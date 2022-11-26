<?php

/**
 * La funcionalidad del área pública del plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Gameplanet_Saldo
 * @subpackage Gameplanet_Saldo/public
 */

/**
 * La funcionalidad del área pública del plugin.
 *
 * Define el nombre del plugin, versión y hooks para el área pública.
 *
 * @package    Gameplanet_Saldo
 * @subpackage Gameplanet_Saldo/public
 * @author     GamePlanet
 */
class Gameplanet_Saldo_Public {

	/**
	 * El ID del plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $gameplanet_saldo    El ID del plugin.
	 */
	private $gameplanet_saldo;

	/**
	 * La versión del plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    La versión actual del plugin.
	 */
	private $version;

	/**
	 * Inisializa la clase y define sus propiedades.
	 *
	 * @since    1.0.0
	 * @param      string    $gameplanet_saldo       Nombre del plugin.
	 * @param      string    $version    La versión del plugin.
	 */
	public function __construct( $gameplanet_saldo, $version ) {

		$this->gameplanet_saldo = $gameplanet_saldo;
		$this->version = $version;

	}

	/**
	 * Registra el CSS para el área pública.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->gameplanet_saldo, plugin_dir_url( __FILE__ ) . 'css/gameplanet-saldo-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Registra el JavaScript para el área pública.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->gameplanet_saldo, plugin_dir_url( __FILE__ ) . 'js/gameplanet-saldo-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Añade el gateway a los métodos de pago de WC.
	 *
	 * @since    1.0.0
	 */
	public function gp_add_to_gateways($gateways) {
		$gateways[] = 'Gp_Gateway';
		return $gateways;
	}

	public function gp_saldo_init(){
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-gameplanet-saldo-gateway.php';
		if(is_user_logged_in()){
			gp_gateway_init();
		}
	}

	public function gp_unset_saldo_gateway($gateways){

		if(isset($gateways['saldo_gp']) && is_checkout()){
			if(is_user_logged_in()){
				$user = get_userdata(get_current_user_id());
				$saldo_gp = $user->saldo_gp;
				if(WC()->cart->get_total("") > $saldo_gp){
					unset($gateways['saldo_gp']);
				}
			} else{
				unset($gateways['saldo_gp']);
			}
		}

		return $gateways;
	}
		
}

// function gp_saldo_log($funcion, $paso, $entry = null)
// {
//     if(!is_ajax()){
//         return false;
//     }

// 	$directorio = "./wp-content/gp/logs_saldo/";
// 	$extencion = "_gp_saldo.log";

//     if (!file_exists($directorio)) {
//         mkdir($directorio, 0755, true);
//     }
//     $tiempo = current_time('mysql');
//     $fecha = strtotime($tiempo);
//     $fecha_log = date('M-d', $fecha);

//     $file = fopen($directorio . $fecha_log . $extencion, "a") or fopen($directorio . $fecha_log . $extencion, "w");

//     if (is_null($entry)) {
//         $registro = $tiempo . " :: Función: " . $funcion . " || " . $paso . "\n";
//     } else {

//         if (is_array($entry)) {
//             $entry = json_encode($entry);
//         }

//         $registro = $tiempo . " :: Función: " . $funcion . " || " . $paso . " || " . $entry . "\n";
//     }

//     $bytes = fwrite($file, $registro);
//     fclose($file);

//     return $bytes;
// }