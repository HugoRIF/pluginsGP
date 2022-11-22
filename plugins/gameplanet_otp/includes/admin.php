<?php

/**
 * Provee una vista del plugin en el área de admin
 *
 * Este archivo se usa para marcar el aspecto del área de admin del plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Gameplanet_Planetshop
 * @subpackage Gameplanet_Planetshop/admin/partials
 */
$version = time();
wp_enqueue_style('gp_otp-styles', plugins_url('../public/css/gameplanet_otp_admin.css', __FILE__), array(), $version);
wp_enqueue_script('gp_otp-scripts', plugins_url('../public/js/gameplanet_otp_admin.js', __FILE__), array('jquery'), $version);
wp_enqueue_script('gp_otp-js_tables', "https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js", array('jquery'), false);


if (isset($_POST['submit_btn'])) {
	if (isset($_POST["gp_otp-active"]) && isset($_POST["gp_otp-active"]) != '') {
		update_option('gp_otp-active', $_POST["gp_otp-active"]);
	}
	if (isset($_POST["gp_otp-max_gateway_errors"]) && isset($_POST["gp_otp-max_gateway_errors"]) != '') {
		update_option('gp_otp-max_gateway_errors', $_POST["gp_otp-max_gateway_errors"]);
	}
	if (isset($_POST["gp_otp-time_resend_soft"]) && isset($_POST["gp_otp-time_resend_soft"]) != '') {
		update_option('gp_otp-time_resend_soft', $_POST["gp_otp-time_resend_soft"]);
	}
	if (isset($_POST["gp_otp-max_attends_user_phone"]) && isset($_POST["gp_otp-max_attends_user_phone"]) != '') {
		update_option('gp_otp-max_attends_user_phone', $_POST["gp_otp-max_attends_user_phone"]);
	}
	if (isset($_POST["gp_otp-time_resend_medium"]) && isset($_POST["gp_otp-time_resend_medium"]) != '') {
		update_option('gp_otp-time_resend_medium', $_POST["gp_otp-time_resend_medium"]);
	}
	if (isset($_POST["gp_otp-max_ip_attends_user"]) && isset($_POST["gp_otp-max_ip_attends_user"]) != '') {
		update_option('gp_otp-max_ip_attends_user', $_POST["gp_otp-max_ip_attends_user"]);
	}
	if (isset($_POST["gp_otp-time_resend_hard"]) && isset($_POST["gp_otp-time_resend_hard"]) != '') {
		update_option('gp_otp-time_resend_hard', $_POST["gp_otp-time_resend_hard"]);
	}
	if (isset($_POST["gp_otp-max_ip_attends"]) && isset($_POST["gp_otp-max_ip_attends"]) != '') {
		update_option('gp_otp-max_ip_attends', $_POST["gp_otp-max_ip_attends"]);
	}
	if (isset($_POST["gp_otp-time_resend_hard_ip"]) && isset($_POST["gp_otp-time_resend_hard_ip"]) != '') {
		update_option('gp_otp-time_resend_hard_ip', $_POST["gp_otp-time_resend_hard_ip"]);
	}
}



?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.min.js" integrity="sha384-IDwe1+LCz02ROU9k972gdyvl+AESN10+x7tBKgc9I5HFtuNz0wWnPclzo6p9vxnk" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>

<div class="wrap gp_opt_admin">
	<h1><?php esc_html_e('Gameplanet OTP'); ?></h1>

	<div class="row mt-4">
		<div class="col col-sm-4">
			<div class="row mb-2 mx-0">
				<div class="container">
					<form id="gp_opt_active_form" class="needs-validation" novalidate action="" method="POST" autocomplete="off">
						<div class="row mx-0">
							<div class="col">
								<div class="active_otp_wrapper">
									<h3 for="switch">Validación OTP</h3>
									<div class="switch-container">
										<?php
										if (get_option('gp_otp-active') == 1) {
											echo '	<input type="checkbox" id="switch_active_otp"  checked>';
										} else {
											echo '	<input type="checkbox" id="switch_active_otp" >';
										}
										?>

										<label for="switch_active_otp" class="switch-label">
											<div class="switch-rail">
												<div class="switch-slider"></div>
											</div>
										</label>
									</div>
								</div>

							</div>

						</div>
						<input type="hidden" id="gp_otp-active_input" name="gp_otp-active">
						<div class="otp_rule_box">
							<span class="rule_title">Errores del gateway</span>
							<p>Cuantas respuestas de error del gateway son permitidas antes de desactivar la validación OTP </p>
							<div class="row">
								<div class="col">
									<div class="form-group">
										<label for="gp_otp-max_attends_user_phone">Errores:</label>
										<input class="form-control" type="text" name="gp_otp-max_gateway_errors" value="<?php echo (get_option('gp_otp-max_gateway_errors')) ?>" required>
									</div>
								</div>
								<div class="col">

								</div>
							</div>
						</div>
						<div class="row">
							<div class="col d-flex justify-content-end">
								<button type="submit" class="btn btn-primary  mt-2" name="submit_btn">Guardar</button>
							</div>
						</div>
					</form>
				</div>
			</div>

			<div class="row mt-2 mx-0">
				<div class="container">
					<h3>Configuración General</h3>
					<div>
						<form id="gp_opt_config_form" class="needs-validation" novalidate action="" method="POST" autocomplete="off">


							<div class="otp_rule_box">
								<span class="rule_title">Tiempo entre solcitudes (default)</span>
								<p>Es el tiempo por default que espera el usuario entre cada solicitud, siempre y cuando NO caiga en una de las reglas siguientes </p>
								<div class="form-group">
									<label for="gp_otp-time_resend_soft">Penalizacion:</label>
									<input class="form-control" type="text" name="gp_otp-time_resend_soft" value="<?php echo (get_option('gp_otp-time_resend_soft')) ?>" required>
									<small id="Help-time_resend_soft" class="form-text text-muted">Cuantos segundos debe esperar el usuario para poder reenviar otro codigo</small>
								</div>
							</div>

							<div class="otp_rule_box">
								<span class="rule_title"> Usuario con el mimso Telefono</span>
								<p>Se determina cada cuantos intentos un usuario puede ingresar el mismo telefono y cual es el tiempo de debe esperar cada que llegue a un multiplo de estos. </p>
								<div class="row">
									<div class="col">
										<div class="form-group">
											<label for="gp_otp-max_attends_user_phone">Intentos:</label>
											<input class="form-control" type="text" name="gp_otp-max_attends_user_phone" value="<?php echo (get_option('gp_otp-max_attends_user_phone')) ?>" required>
											<small id="Help-time_resend_soft" class="form-text text-muted">Cuantas veces un usuario puede ingresar el mismo telefeno </small>
										</div>
									</div>
									<div class="col">
										<div class="form-group">
											<label for="gp_otp-time_resend_medium">Penalización:</label>
											<input class="form-control" type="text" name="gp_otp-time_resend_medium" value="<?php echo (get_option('gp_otp-time_resend_medium')) ?>" required>
											<small id="Help-time_resend_medium" class="form-text text-muted">Segundos que debe esperar al llegar a los intentos </small>
										</div>
									</div>
								</div>
							</div>

							<div class="otp_rule_box">
								<span class="rule_title"> Usuario en la misma IP</span>
								<p>Se determina cada cuantos intentos un usuario con la misma IP puede interactuar y cual es el tiempo de debe esperar cada que llegue a un multiplo de estos. </p>
								<div class="row">
									<div class="col">
										<div class="form-group">
											<label for="gp_otp-max_ip_attends_user">Intentos:</label>
											<input class="form-control" type="text" name="gp_otp-max_ip_attends_user" value="<?php echo (get_option('gp_otp-max_ip_attends_user')) ?>" required>
											<small id="Help-time_resend_soft" class="form-text text-muted">Cuantas veces un usuario puede solicitar teniendo la misma IP </small>
										</div>
									</div>
									<div class="col">
										<div class="form-group">
											<label for="gp_otp-time_resend_hard">Penalización:</label>
											<input class="form-control" type="text" name="gp_otp-time_resend_hard" value="<?php echo (get_option('gp_otp-time_resend_hard')) ?>" required>
											<small id="Help-time_resend_medium" class="form-text text-muted">Segundos que debe esperar al llegar a los intentos </small>
										</div>
									</div>
								</div>
							</div>

							<div class="otp_rule_box">
								<span class="rule_title">Misma IP</span>
								<p>Se determina cada cuantos intentos tiene una dirección IP especifica para solicitar codigos y cual es el tiempo de debe esperar cada que llegue a un multiplo de estos. </p>
								<div class="row">
									<div class="col">
										<div class="form-group">
											<label for="gp_otp-max_ip_attends">Intentos:</label>
											<input class="form-control" type="text" name="gp_otp-max_ip_attends" value="<?php echo (get_option('gp_otp-max_ip_attends')) ?>" required>
											<small id="Help-time_resend_soft" class="form-text text-muted">Cuantas veces se puede enviar desde una IP especifica </small>
										</div>
									</div>
									<div class="col">
										<div class="form-group">
											<label for="gp_otp-time_resend_medium">Penalización:</label>
											<input class="form-control" type="text" name="gp_otp-time_resend_hard_ip" value="<?php echo (get_option('gp_otp-time_resend_hard_ip')) ?>" required>
											<small id="Help-time_resend_medium" class="form-text text-muted">Segundos que debe esperar al llegar a los intentos </small>
										</div>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col d-flex justify-content-end">
									<button type="submit" class="btn btn-primary  mt-2" name="submit_btn">Guardar</button>
								</div>
							</div>

						</form>
					</div>
				</div>
			</div>

		</div>
		<div class="col col-sm-8">
			<div class="row">
				<div class="container mb-2 d-flex align-items-center toolbar">
					<button class="btn btn-outline-primary" id="reload_report"><span class="dashicons dashicons-image-rotate"></span></button>
					<span class="label mx-2"> Reporte de Intentos por: </span>
					<select class="form-control" id="report_filter" value="ip" style="width: 130px">
						<option value="ip">Dirección IP</option>
						<option value="user">Usuario</option>
					</select>
				</div>
				<div class="container">
					<table id="example" class="" style="width:100%">
			<thead>
				<tr>
					<th>Name</th>
					<th>Position</th>
					<th>Office</th>
					<th>Age</th>
					<th>Start date</th>
					<th>Salary</th>
					</tr>
				</thead>
			<tbody>
				
				<tr>
					<td>Shad Decker</td>
					<td>Regional Director</td>
					<td>Edinburgh</td>
					<td>51</td>
					<td>2008-11-13</td>
					<td>$183,000</td>
					</tr>
				<tr>
					<td>Michael Bruce</td>
					<td>Javascript Developer</td>
					<td>Singapore</td>
					<td>29</td>
					<td>2011-06-27</td>
					<td>$183,000</td>
					</tr>
				<tr>
					<td>Donna Snider</td>
					<td>Customer Support</td>
					<td>New York</td>
					<td>27</td>
					<td>2011-01-25</td>
					<td>$112,000</td>
					</tr>
				</tbody>
			
			</table>
				</div>


			</div>
		</div>

	</div>

</div>
<script>
	// Example starter JavaScript for disabling form submissions if there are invalid fields
	(function() {
		'use strict';


		window.addEventListener('load', function() {
			// Fetch all the forms we want to apply custom Bootstrap validation styles to
			var forms = document.getElementsByClassName('needs-validation');
			// Loop over them and prevent submission
			var validation = Array.prototype.filter.call(forms, function(form) {
				form.addEventListener('submit', function(event) {
					if (form.checkValidity() === false) {
						event.preventDefault();
						event.stopPropagation();
					}
					form.classList.add('was-validated');
				}, false);
			});

			var switch_otp = document.getElementById('switch_active_otp');
			switch_otp.addEventListener('change', function(e) {
				e.preventDefault();
				let value = e.target.checked;
				const active_input = document.getElementById('gp_otp-active_input');
				active_input.value = value ? 1 : 0;
			})
		}, false);
	})();
</script>