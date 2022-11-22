<?php

/**
 * Funcionalidad del plugin específica para el área de admin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Gameplanet_Widgets
 * @subpackage Gameplanet_Widgets/admin
 */

/**
 * Funcionalidad del plugin específica para el área de admin.
 *
 * Define el nombre del plugin, versión y hooks para el área de admin.
 *
 * @package    Gameplanet_Widgets
 * @subpackage Gameplanet_Widgets/admin
 * @author     GamePlanet
 */
class Gameplanet_Widgets_Admin {

	/**
	 * El ID del plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $gameplanet_widgets    El ID del plugin.
	 */
	private $gameplanet_widgets;

	/**
	 * La versión del plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    La versión actual del plugin.
	 */
	private $version;

	/**
	 * Inicializa la clase y define sus propiedades.
	 *
	 * @since    1.0.0
	 * @param      string    $gameplanet_widgets       El nombre del plugin.
	 * @param      string    $version    La versión del plugin.
	 */
	public function __construct( $gameplanet_widgets, $version ) {

		$this->gameplanet_widgets = $gameplanet_widgets;
		$this->version = $version;

	}

	/**
	 * Registra el CSS para el área del admin.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->gameplanet_widgets, plugin_dir_url( __FILE__ ) . 'css/gameplanet-widgets-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Registra el JavaScript para el área del admin.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->gameplanet_widgets, plugin_dir_url( __FILE__ ) . 'js/gameplanet-widgets-admin.js', array( 'jquery' ), $this->version, false );

	}

    /**
	 * Función para obtener disponibilidad de producto (producto simple)
	 */
	public function gp_disponibilidad_domicilio(){

        $params = $_POST;
		$upc = $params['upc'];
		$lat = $params['lat'];
		$lng = $params['lng'];
		$tienda_fav = $params['tienda_fav'];
		$id_cliente = $params['id_cliente'];

        try {
            if (class_exists('Widget_single_product_ps')) {
                $clase = new Widget_single_product_ps();
                if(!method_exists($clase, 'gp_wc_disponibilidad')){
                    echo "Error. Code: WA-002";
                    die();
                }
                // $cantidad, $metodo, $upc, $lat, $lng, $tienda_fav, $id_cliente
                $response = $clase->gp_wc_disponibilidad(1, "cache", $upc, $lat, $lng, $tienda_fav, $id_cliente);
                echo $response;
                die();
            } else{
                echo "Error. Code: WA-001";
                die();
            }
        } catch (\Exception $e) {
            echo ($e->getMessage());
            die();
        }
    }

}
