<?php

/**
 * La funcionalidad del área pública del plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Gameplanet_Widgets
 * @subpackage Gameplanet_Widgets/public
 */

/**
 * La funcionalidad del área pública del plugin.
 *
 * Define el nombre del plugin, versión y hooks para el área pública.
 *
 * @package    Gameplanet_Widgets
 * @subpackage Gameplanet_Widgets/public
 * @author     GamePlanet
 */
class Gameplanet_Widgets_Public {

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
	 * Inisializa la clase y define sus propiedades.
	 *
	 * @since    1.0.0
	 * @param      string    $gameplanet_widgets       Nombre del plugin.
	 * @param      string    $version    La versión del plugin.
	 */
	public function __construct( $gameplanet_widgets, $version ) {

		$this->gameplanet_widgets = $gameplanet_widgets;
		$this->version = $version;

	}

	/**
	 * Registra el CSS para el área pública.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->gameplanet_widgets, plugin_dir_url( __FILE__ ) . 'css/gameplanet-widgets-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Registra el JavaScript para el área pública.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->gameplanet_widgets, plugin_dir_url( __FILE__ ) . 'js/gameplanet-widgets-public.js', array( 'jquery' ), $this->version, false );
		$id_gp = 0;
		if(is_user_logged_in()){
			$user = get_userdata(get_current_user_id());
			$id_gp = $user->id_gp;
		}
		wp_localize_script(
			$this->gameplanet_widgets,
			'gp_disp_dom',
			array(
				'url' => admin_url('admin-ajax.php'),
				'action' => 'gp_disponibilidad_domicilio',
				'id_gp' => $id_gp
			)
		);
	}

	/**
	 * Añade widgets.
	 *
	 * @since    1.0.0
	 */
	public function gp_load_widgets(){
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-gameplanet-widgets-library.php';
		register_widget('Widget_single_product_ps');
	}

	/**
	 * Añade elementos a widget. (pt2)
	 *
	 * @since    1.0.0
	 */	
	public function gp_widget_add_single_product_button() { 
		global $product;
		?>
		<br/>
		<br/>
		<button id="gp_single_product_button"
			type="submit" class="gp_single_product_button_disable"
			value="<?php esc_html_e($product->get_id()); ?>"
			data-button='{"add_to_cart_url":"<?php esc_html_e($product->get_permalink()); ?>?add-to-cart=<?php esc_html_e($product->get_id()); ?>&quantity=1", "variation_id":""}'>
			<span id="gp_single_product_button_txt">Selecciona tipo de entrega</span>
		</button>
	<?php }
	
	/**
	 * Añade elementos a widget. (pt2)
	 *
	 * @since    1.0.0
	 */	
	public function gp_widget_add_single_product_availability() {
		global $product;
		$upc = $product->get_sku();
		if(!str_starts_with($upc, 'P')){ ?>
			<div style="text-align: right;">
				<a href="#modal_disponibilidad" target="_self" rel="nofollow" class="gp_underline gp_disponibilidad" aria-label="" style="width: 100%;">
					<!-- <i class="icon-search gp_underline">&nbsp;</i> -->
					Ver disponibilidad en sucursal
				</a>
			</div>
		<?php }
	}

}

/**
 * Función para generar logs.
 *
 * @since    1.0.0
 * @param      string    $funcion    Nombre de la función.
 * @param      string    $paso       Paso que se ejecutó.
 * @param      mixed     $entry      (opcional) objeto que se quiera guardar.
 * @return	   int|false verdadero si se guardó log.
 */
function gp_widgets_logs($funcion, $paso, $entry = null){
	$directorio = './wp-content/gp/logs_widgets/';
	$extencion = "_widget.log";
	
	if (!file_exists($directorio)) {
		mkdir($directorio, 0755, true);
	}

	$tiempo = current_time('mysql');
	$fecha = strtotime($tiempo);
	$fecha_log = date('M-d', $fecha);

	$file = fopen($directorio . $fecha_log . $extencion, "a") or fopen($directorio . $fecha_log . $extencion, "w");

	if (is_null($entry)) {

		$registro = $tiempo . " :: Función: " . $funcion . " || Bloque: " . $paso . "\n";
	} else {

		if (is_array($entry)) {
			$entry = json_encode($entry);
		}

		$registro = $tiempo . " :: Función: " . $funcion . " || Bloque: " . $paso . " || " . $entry . "\n";
	}

	$bytes = fwrite($file, $registro);
	fclose($file);

	return $bytes;
}

