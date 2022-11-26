<?php

/**
 * Provee una vista del plugin en el área de admin
 *
 * Este archivo se usa para marcar el aspecto del área de admin del plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Gameplanet
 * @subpackage Gameplanet/admin/partials
 */

if (!defined('ABSPATH')) exit;

if (! current_user_can ('manage_options')) wp_die (__ ('No tienes suficientes permisos para acceder a esta página.'));
global $wpdb;
$tablename = 'gamers_portabilidad';
?>
<!-- Este archivo debe consistir primordialmente de HTML con un poco de PHP. -->

<div class="wrap">
	<h1><?php esc_html_e('GamePlanet'); ?></h1>
	<h3>Bienvenido a la configuración</h3>
	<form action="" method="POST" autocomplete="off">
		<div class="gp_config">
			<div class="gp_columna_config gp_columna_izquierda_config">
				<h2>API Gameplanet</h2>
				<label  for="ruta_gameplanet">ruta_gameplanet (ruta al api Gameplanet)</label>
				<input class="admin_gp_input" type="text" name="ruta_gameplanet" id="ruta_gameplanet" value="<?php esc_html_e( get_option('ruta_gameplanet')) ?>"><br>
				<label  for="data-jwt-master">data-jwt-master (llave maestra para api Gameplanet)</label>
				<input class="admin_gp_input" type="password" name="data-jwt-master" id="data-jwt-master" placeholder='******<?php esc_html_e(substr(get_option('data-jwt-master'), -4)) ?>'><br>
			</div>
			
			<div class="gp_columna_config gp_columna_derecha_config">
				<h2>API Telefonero</h2>
				<label  for="ruta_telefonero">ruta_telefonero (ruta al api Telefonero)</label>
				<input class="admin_gp_input" type="text" name="ruta_telefonero" id="ruta_telefonero" value="<?php esc_html_e( get_option('ruta_telefonero')) ?>"><br>
				<label  for="data-telefonero">data-telefonero (llave para api Telefonero)</label>
				<input class="admin_gp_input" type="password" name="data-telefonero" id="data-telefonero" placeholder='******<?php esc_html_e(substr(get_option('data-telefonero'), -4)) ?>'><br>
			</div>
		</div>

		<div class="gp_config">
			<div class="gp_columna_config gp_columna_izquierda_config">
				<h2>API Tendero</h2>
				<label  for="ruta_tendero">ruta_tendero (ruta al api Tendero)</label>
				<input class="admin_gp_input" type="text" name="ruta_tendero" id="ruta_tendero" value="<?php esc_html_e( get_option('ruta_tendero')) ?>"><br>
				<label  for="data-tendero">data-tendero (llave para api Tendero)</label>
				<input class="admin_gp_input" type="password" name="data-tendero" id="data-tendero" placeholder='******<?php esc_html_e(substr(get_option('data-tendero'), -4)) ?>'><br>
			</div>

			<div class="gp_columna_config gp_columna_derecha_config">
				<h2>Credenciales Google Captcha</h2>
				<label  for="gc_clave_sitio">gc_clave_sitio (clave pública)</label>
				<input class="admin_gp_input" type="text" name="gc_clave_sitio" id="gc_clave_sitio" value="<?php esc_html_e( get_option('gc_clave_sitio')) ?>"><br>
				<label  for="gc-clave-secreta">gc-clave-secreta (clave privada)</label>
				<input class="admin_gp_input" type="password" name="gc-clave-secreta" id="gc-clave-secreta" placeholder='******<?php esc_html_e(substr(get_option('gc-clave-secreta'), -4)) ?>'><br>
			</div>
		</div>
		
		<div class="gp_config">
			<div class="gp_columna_config gp_columna_izquierda_config">
				<?php
				$ruta_bridge = get_option('ruta_bridge');
				$user_bridge = get_option('user-bridge');
				$pass_bridge = get_option('pass-bridge');
				?>
				<h2>API Bridge</h2>
				<label  for="ruta_bridge">ruta_bridge (ruta al api Bridge)</label>
				<input class="admin_gp_input" type="text" name="ruta_bridge" id="ruta_bridge" value="<?php esc_html_e($ruta_bridge) ?>"><br>
				<label  for="user-bridge">user-bridge (usuario para api bridge)</label>
				<input class="admin_gp_input" type="text" name="user-bridge" id="user-bridge" value="<?php esc_html_e($user_bridge) ?>"><br>
				<label  for="pass-bridge">pass-bridge (contraseña para api bridge)</label>
				<input class="admin_gp_input" type="password" name="pass-bridge" id="pass-bridge" placeholder='<?php (!empty($pass_bridge)) ? esc_html_e('******' . substr($pass_bridge, -4)) : '' ?>'><br>
			</div>
			<div class="gp_columna_config gp_columna_derecha_config">
				<?php
				$ruta_link_gp = get_option('ruta_link_gp');
				$user_link_gp = get_option('user-link_gp');
				$pass_link_gp = get_option('pass-link_gp');
				?>
				<h2>Link GP</h2>
				<label  for="ruta_link_gp">ruta_link (ruta al link)</label>
				<input class="admin_gp_input" type="text" name="ruta_link_gp" id="ruta_link_gp" value="<?php esc_html_e($ruta_link_gp) ?>"><br>
				<label  for="user-link_gp">user-link (usuario para link)</label>
				<input class="admin_gp_input" type="text" name="user-link_gp" id="user-link_gp" value="<?php esc_html_e($user_link_gp) ?>"><br>
				<label  for="pass-link_gp">pass-link (contraseña para link)</label>
				<input class="admin_gp_input" type="password" name="pass-link_gp" id="pass-link_gp" placeholder='<?php (!empty($pass_link_gp)) ? esc_html_e('******' . substr($pass_link_gp, -4)) : '' ?>'><br>
			</div>

		</div>

		<div class="gp_config">
			<div class="gp_columna_config gp_columna_izquierda_config">
				<?php
				$ruta_twilio = get_option('ruta_twilio');
				$service_id_twilio = get_option('service_id_twilio');
				$user_twilio = get_option('user_twilio');
				$password_twilio = get_option('password_twilio');
				?>
				<h2>Twilio Claves</h2>
				<label  for="ruta_twilio">Ruta API twilio</label>
				<input class="admin_gp_input" type="text" name="ruta_twilio" id="ruta_twilio" value="<?php esc_html_e($ruta_twilio) ?>"><br>
				
				<label  for="service_id_twilio">ID servicio (parte de la url de la API)</label>
				<input class="admin_gp_input" type="text" name="service_id_twilio" id="service_id_twilio" value="<?php esc_html_e($service_id_twilio) ?>"><br>
				
				<label  for="user_twilio">user_twilio (usuario para api twilio)</label>
				<input class="admin_gp_input" type="text" name="user_twilio" id="user_twilio" value="<?php esc_html_e($user_twilio) ?>"><br>
				
				<label  for="password_twilio">password_twilio (contraseña para api twilio)</label>
				<input class="admin_gp_input" type="password" name="password_twilio" id="password_twilio" placeholder='<?php (!empty($password_twilio)) ? esc_html_e('******' . substr($password_twilio, -4)) : '' ?>'><br>
			
			</div>
			

		</div>
		<div class="clear"></div>
		<input type="submit" value="Guardar Todo" name="submit_btn">
	</form>
</div>
<?php

if(isset($_POST['submit_btn'])){
	if('' != $_POST["ruta_telefonero"]){
		update_option( 'ruta_telefonero', $_POST["ruta_telefonero"] );
	}
	if('' != $_POST["data-telefonero"]){
		update_option( 'data-telefonero', $_POST["data-telefonero"] );
	}

	if('' != $_POST["ruta_gameplanet"]){
		update_option( 'ruta_gameplanet', $_POST["ruta_gameplanet"] );
	}
	if('' != $_POST["data-jwt-master"]){
		update_option( 'data-jwt-master', $_POST["data-jwt-master"] );
	}

	if('' != $_POST["ruta_tendero"]){
		update_option( 'ruta_tendero', $_POST["ruta_tendero"] );
	}
	if('' != $_POST["data-tendero"]){
		update_option( 'data-tendero', $_POST["data-tendero"] );
	}

	if('' != $_POST["gc_clave_sitio"]){
		update_option( 'gc_clave_sitio', $_POST["gc_clave_sitio"] );
	}
	if('' != $_POST["gc-clave-secreta"]){
		update_option( 'gc-clave-secreta', $_POST["gc-clave-secreta"] );
	}

	if('' != $_POST["ruta_bridge"]){
		update_option( 'ruta_bridge', $_POST["ruta_bridge"] );
	}
	if('' != $_POST["user-bridge"]){
		update_option( 'user-bridge', $_POST["user-bridge"] );
	} 
	if('' != $_POST["pass-bridge"]){
		update_option( 'pass-bridge', $_POST["pass-bridge"] );
	} 
	
	if('' != $_POST["ruta_link_gp"]){
		update_option( 'ruta_link_gp', $_POST["ruta_link_gp"] );
	}
	if('' != $_POST["user-link_gp"]){
		update_option( 'user-link_gp', $_POST["user-link_gp"] );
	} 
	if('' != $_POST["pass-link_gp"]){
		update_option( 'pass-link_gp', $_POST["pass-link_gp"] );
	} 
	if('' != $_POST["ruta_twilio"]){
		update_option( 'ruta_twilio', $_POST["ruta_twilio"] );
	} 
	if('' != $_POST["service_id_twilio"]){
		update_option( 'service_id_twilio', $_POST["service_id_twilio"] );
	} 
	if('' != $_POST["user_twilio"]){
		update_option( 'user_twilio', $_POST["user_twilio"] );
	} 
	if('' != $_POST["password_twilio"]){
		update_option( 'password_twilio', $_POST["password_twilio"] );
	} 
	?>
	<div class="updated notice">
		<p>
			<strong>
				<?php  esc_html_e("¡Datos guardados!"); ?>
			</strong>
			<?php esc_html_e("actualizando ventana."); ?>
		</p>
	</div>
	<?php header("Refresh:0");
}