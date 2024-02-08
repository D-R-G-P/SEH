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
				<button onclick="back.style.display = 'flex'; addServicio.style.display = 'flex'" class="btn-green"><b><i class="fa-solid fa-plus"></i> Agregar servicio</b></button>

				<div class="back" id="back">
					<div class="divBackForm" id="addServicio" style="display: none;">
						<div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
							<button class="btn-red" onclick="back.style.display = 'none'; addServicio.style.display = 'none'" style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
						</div>
						<h3>Nuevo servicio</h3>
						<form action="controllers/addServicioForm.php" method="post" class="backForm">
							<div>
								<label for="servicio">Nombre del servicio</label>
								<input type="text" name="servicio" id="servicio" required>
							</div>

							<div style="width: 100%;">
								<label for="selectBoss">Jefe del servicio</label>
								<p style="font-size: .8vw;">*Deberá estar previamente cargado en personal</p>
								<select id="selectBoss" class="select2" name="jefe" style="width: 100%;" required>
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
					<div class="divBackForm" id="modServicio" style="display: none;">
						<div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
							<button class="btn-red" onclick="back.style.display = 'none'; modServicio.style.display = 'none'; servicioMod.value = ''; modifyBoss.value = ''" style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
						</div>
						<h3>Modificar servicio</h3>
						<form action="controllers/modifyService.php" method="post" class="backForm">
							<input type="hidden" name="idMod" id="idMod" value="5">
							<div>
								<label for="servicioMod">Nombre del servicio:</label>
								<input type="text" name="servicioMod" id="servicioMod">
							</div>

							<div style="width: 100%;">
								<label for="modifyBoss">Jefe del servicio</label>
								<p style="font-size: .8vw;">*Deberá estar previamente cargado en personal</p>
								<select id="modifyBoss" class="select2" name="jefeMod" style="width: 100%;" required>
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
							<button class="btn-green"><b><i class="fa-solid fa-plus"></i> Modificar servicio</b></button>
						</form>
					</div>
					<div class="divBackForm" id="advertenciaDelete" style="padding: 1vw; display: none;">
						<h3 style="margin-bottom: 1vw;">¡Atención!</h3>
						<p>Está por eliminar un servicio, no podrá revertir esta acción. De ser un error deberá comunicarse con el administrador general.</p>
						<div class="datosServicio" style="margin-top: 2.5vw; display: flex; flex-direction: column; text-align: start; width: 100%;">
							<b style="margin-bottom: 1vw;">Servicio a eliminar:</b>
							<div class="modulo">
								<b>Nombre del servicio:</b>
								<div class="servicioName" id="servicioName"></div>
								<b>Jefe del servicio:</b>
								<div class="servicioJefe" id="servicioJefe"></div>
							</div>

						</div>
						<div class="botones" style="width: 100%; display: flex; flex-direction: row; flex-wrap: wrap; align-content: center; justify-content: center; align-items: center;">
							<button class="btn-red" id="btnDelete"><i class="fa-solid fa-trash"></i> Eliminar servicio</button>
							<button class="btn-green" onclick="back.style.display = 'none'; advertenciaDelete.style.display='none';"><i class="fa-solid fa-xmark"></i> Cancelar</button>
						</div>
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
					<script>
						function puto(id, servicio, jefe) {
							$('#back').css('display', 'flex');
							$('#modServicio').css('display', 'flex');

							$('#idMod').val(id);
							$('#servicioMod').val(servicio);

							$('#modifyBoss').val(jefe).trigger('change');
						}
					</script>
					<?php

					// Realiza la consulta a la tabla servicios
					$getTable = "SELECT * FROM servicios WHERE estado != 'Eliminado'";
					$stmt = $pdo->query($getTable);

					// Itera sobre los resultados y muestra las filas en la tabla
					while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
						echo '<tr>';
						echo '<td class="table-center table-middle">' . $row['id'] . '</td>';
						echo '<td class="table-middle">' . $row['servicio'] . '</td>';

						if ($row['jefe'] != "") {
							// Realiza una consulta para obtener el nombre y apellido del jefe de servicio
							$getJefeQuery = "SELECT nombre, apellido FROM personal WHERE dni = ?";
							$getJefeStmt = $pdo->prepare($getJefeQuery);
							$getJefeStmt->execute([$row['jefe']]);
							$jefeInfo = $getJefeStmt->fetch(PDO::FETCH_ASSOC);
							// Muestra el nombre y apellido del jefe de servicio
							if ($jefeInfo) {
								echo '<td class="table-middle">' . $jefeInfo['apellido'] . ' ' . $jefeInfo['nombre'] . '</td>';
							} else {
								echo '<div>No se encontró la información del jefe</div>';
							}
						} else {
							echo '<td class="table-middle"> No hay jefe registrado';
						}
						echo '</td>';
						echo '<td class="table-center table-middle">' . $row['estado'] . '</td>';
						echo '<td>';

						if ($row['estado'] == "Activo") {

							echo '<button class="btn-green" title="Desactivar servicio" onclick="window.location.href = \'/SGH/public/layouts/modules/adminPanel/controllers/turnEstadoServicio.php?id=' . $row["id"] . '&action=desactivar\'"><i class="fa-solid fa-circle-check"></i></button>

							<button class="btn-green" title="Editar servicio" onclick="puto(' . $row['id'] . ', \'' . $row['servicio'] . '\', \'' . $row['jefe'] . '\')"><i class="fa-solid fa-pencil"></i></button>';
						} else if ($row['estado'] == "Inactivo") {

							echo '<button class="btn-red" title="Activar servicio" onclick="window.location.href = \'/SGH/public/layouts/modules/adminPanel/controllers/turnEstadoServicio.php?id=' . $row["id"] . '&action=activar\'"><i class="fa-solid fa-circle-xmark"></i></button>

							<button class="btn-yellow" title="Eliminar servicio" onclick="showDeleteConfirmation(\'' . $row['id'] . '\', \'' . $row['servicio'] . '\', \'' . $jefeInfo['apellido'] . ' ' . $jefeInfo['nombre'] . '\')"><i class="fa-solid fa-trash"></i></button>';
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