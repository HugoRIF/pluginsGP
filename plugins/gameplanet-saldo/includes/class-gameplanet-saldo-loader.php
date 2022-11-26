<?php

/**
 * Registra todas las acciones y filtros para el plugin
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Gameplanet_Saldo
 * @subpackage Gameplanet_Saldo/includes
 */

/**
 * Registra todas las acciones y filtros para el plugin.
 *
 * Mantiene una lista de todos los hooks que son registrados
 * atravez del plugin y los registra con el API de WordPress.
 * Llama la función que ejecutar la lista de acciones y filtros.
 *
 * @package    Gameplanet_Saldo
 * @subpackage Gameplanet_Saldo/includes
 * @author     GamePlanet
 */
class Gameplanet_Saldo_Loader {

	/**
	 * El arreglo de las acciones registradas con WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $actions    Las acciones registradas con WordPress que ejecutará cuando se cargue el plugin.
	 */
	protected $actions;

	/**
	 * El arreglo de los filtros registrados con WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $filters    Los filtros registrados con WordPress que ejecutará cuando se cargue el plugin.
	 */
	protected $filters;

	/**
	 * Inicializa las colecciones usadas para mantener las acciones y filtros.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->actions = array();
		$this->filters = array();

	}

	/**
	 * Añade una nueva acción a la colección para ser registrada con WordPress.
	 *
	 * @since    1.0.0
	 * @param    string               $hook             El nombre de la acción que está siendo registrada.
	 * @param    object               $component        Una referencia a la instancia del objeto en la que la acción está definida.
	 * @param    string               $callback         Nombre de la definición de la función en el $component.
	 * @param    int                  $priority         Opcional. La prioridad en la que la función deberá lanzarse. Default 10.
	 * @param    int                  $accepted_args    Opcional. El número de argumentos que serán pasados a $callback. Default 1.
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Añade un nuevo filtro a la colección para ser registrada con WordPress.
	 *
	 * @since    1.0.0
	 * @param    string               $hook             El nombre del filtro que está siendo registrada.
	 * @param    object               $component        Una referencia a la instancia del objeto en la que la acción está definida.
	 * @param    string               $callback         Nombre de la definición de la función en el $component.
	 * @param    int                  $priority         Opcional. La prioridad en la que la función deberá lanzarse. Default 10.
	 * @param    int                  $accepted_args    Opcional. El número de argumentos que serán pasados a $callback. Default 1.
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Función que es usada para registrar la colección de acciones y hooks en una sola colección.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    array                $hooks            Colección de hooks que registrarán (acciones y filtros).
	 * @param    string               $hook             Nombre del filtro que se está registrando.
	 * @param    object               $component        Referencia a la instancia del objeto en el que el filtro se ha definido.
	 * @param    string               $callback         El nombre de la definición de la función en $component.
	 * @param    int                  $priority         La prioridad en la que la función deberá lanzarse.
	 * @param    int                  $accepted_args    El número de argumentos que serán pasados a $callback.
	 * @return   array                                  Colección de acciones y filtros registrados con WordPress.
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {

		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args
		);

		return $hooks;

	}

	/**
	 * Registra los filtros y acciones con WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {

		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

	}

}
