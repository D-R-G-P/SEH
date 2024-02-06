<?php

require_once '../../../../app/db/db.php';
require_once '../../../../app/db/user_session.php';
require_once '../../../../app/db/user.php';
require_once '../../../../app/db/user.php';

$user = new User();
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);

$title = "Panel de administración";

$db = new DB();
$pdo = $db->connect();


?>

<?php require_once '../../base/header.php'; ?>
<link rel="stylesheet" href="/SGH/public/layouts/modules/adminPanel/css/adminPanel.css">


<div class="content">
	<?php

	if ($user->getRol() === "Administrador") {

	?>


		<div class="modulo" style="flex-direction: column; text-align: center;">
			<h3>Panel de administración</h3>
			<p>Este modulo esta orientado a la configuración del sistema.</p>
		</div>

		<div class="modulo">
			<div>
				<button onclick="back.style.display = 'flex'" class="btn-green"><b><i class="fa-solid fa-plus"></i> Agregar servicio</b></button>

				<div class="back" id="back">
					<div class="divBackForm" id="addServicio">
						<div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
							<button class="btn-red" onclick="back.style.display = 'none'" style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
						</div>
						<form action="controllers/addServicioForm.php" method="post" class="backForm">
							<div>
								<label for="servicio">Nombre del servicio</label>
								<input type="text" name="servicio" id="servicio" required>
							</div>

							<div style="width: 100%;">
								<label for="miSelect">Jefe del servicio</label>
								<p style="font-size: .8vw;">*Deberá estar previamente cargado en personal</p>
								<select id="miSelect" class="select2" name="jefe" style="width: 100%;" required>
									<option value="" selected disabled>Seleccionar jefe...</option>
									<?php

									// Realiza la consulta a la tabla servicios
									$getPersonal = "SELECT apellido, nombre, dni FROM personal";
									$stmt = $pdo->query($getPersonal);

									// Itera sobre los resultados y muestra las filas en la tabla
									while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
										echo '<option value=' . $row['dni'] . '>' . $row['apellido'] . ' ' . $row['nombre'] . ' - ' . $row['dni'] . '</option>';
									}

									?>
								</select>
							</div>

							<button class="btn-green"><b><i class="fa-solid fa-plus"></i> Añadir servicio</b></button>
						</form>
					</div>
				</div>
			</div>

			<table>
				<thead>
					<tr>
						<th class="table-center table-middle">ID</th>
						<th>Servicio</th>
						<th>Jefe</th>
						<th>Estado</th>
						<th>Acciones</th>
					</tr>
				</thead>
				<tbody>
					<?php

					// Realiza la consulta a la tabla servicios
					$getTable = "SELECT * FROM servicios";
					$stmt = $pdo->query($getTable);

					// Itera sobre los resultados y muestra las filas en la tabla
					while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
						echo '<tr>';
						echo '<td class="table-center table-middle">' . $row['id'] . '</td>';
						echo '<td class="table-middle">' . $row['servicio'] . '</td>';
						// Realiza una consulta para obtener el nombre y apellido del jefe de servicio
						$getJefeQuery = "SELECT nombre, apellido FROM personal WHERE dni = ?";
						$getJefeStmt = $pdo->prepare($getJefeQuery);
						$getJefeStmt->execute([$row['jefe']]);
						$jefeInfo = $getJefeStmt->fetch(PDO::FETCH_ASSOC);
						// Muestra el nombre y apellido del jefe de servicio
						if ($jefeInfo) {
							echo '<td class="table-middle">' . $jefeInfo['apellido'] . ' ' . $jefeInfo['nombre'] . '</td>';
						} else {
							echo '<div>No se encontró información del jefe</div>';
						}
						echo '</td>';
						echo '<td class="table-center table-middle">' . $row['estado'] . '</td>';
						echo '<td>';

						if ($row['estado'] == "Activo") {

							echo '<button class="btn-green" title="Desactivar servicio" onclick="window.location.href = \'/SGH/public/layouts/modules/adminPanel/controllers/turnEstadoServicio\'"><i class="fa-solid fa-circle-check"></i></button>

							<button class="btn-green" title="Editar servicio"><i class="fa-solid fa-pencil"></i></button>';
						} else if ($row['estado'] == "Inactivo") {

							echo '<button class="btn-red" title="Activar servicio"><i class="fa-solid fa-circle-xmark"></i></button>

							<button class="btn-yellow" title="Eliminar servicio"><i class="fa-solid fa-trash"></i></button>';
						} else {

							echo 'Error al generar las acciones.';
						}

						echo '</td>';
						echo '</tr>';
					}


					?>
				</tbody>
			</table>

		</div>


	<?php

	} else {
		echo 'Acceso denegado, no cuenta con los permisos para acceder a este sistema.';
	}

	?>
</div>

<script src="/SGH/public/layouts/modules/adminPanel/js/adminPanel.js"></script>
<?php require_once '../../base/footer.php'; ?>