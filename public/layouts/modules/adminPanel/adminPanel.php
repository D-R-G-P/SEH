<?php

require_once '../../../../app/db/db.php';
require_once '../../../../app/db/user_session.php';
require_once '../../../../app/db/user.php';
require_once '../../../config.php';

$user = new User();
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);

requireRole(['administrador', 'direccion', 'administracion']);

$title = "Panel de administración";

$db = new DB();
$pdo = $db->connect();

$servicioUser = $user->getServicio();


?>

<?php require_once '../../base/header.php'; ?>
<link rel="stylesheet" href="/SGH/public/layouts/modules/adminPanel/css/adminPanel.css">


<div class="content">

	<div class="modulo" style="flex-direction: column; text-align: center;">
		<h3>Panel de administración</h3>
		<p>Este modulo esta orientado a la configuración del sistema.</p>
	</div>

	<?php

	if (hasAccess(['administrador', 'direccion'])) {

	?>


		<div class="modulo">
			<h3 style="width: 100%; text-align: center;">Gestión de servicios</h3>
			<div>
				<button onclick="back.style.display = 'flex'; addServicio.style.display = 'flex'" class="btn-green"><b><i class="fa-solid fa-plus"></i> Agregar servicio</b></button>

				<div class="back" id="back">
					<div class="divBackForm" id="addServicio" style="display: none;">
						<div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
							<button class="btn-red" onclick="back.style.display = 'none'; addServicio.style.display = 'none';" style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
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
							<input type="hidden" name="idMod" id="idMod">
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
						function setDatos(id, servicio, jefe) {
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
					if ($stmt->rowCount() == 0) {
						echo '<td colspan=5 class="table-middle table-center">No hay servicios registradas</td>';
					} else {
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
									echo '<td class="table-middle table-center">No se encontró la información del jefe</td>';
								}
							} else {
								echo '<td class="table-middle table-center">No hay jefe registrado';
							}
							echo '</td>';
							echo '<td class="table-center table-middle">' . $row['estado'] . '</td>';
							echo '<td>';

							if ($row['estado'] == "Activo") {

								echo '<button class="btn-green" title="Desactivar servicio" onclick="window.location.href = \'/SGH/public/layouts/modules/adminPanel/controllers/turnEstadoServicio.php?id=' . $row["id"] . '&action=desactivar\'"><i class="fa-solid fa-circle-check"></i></button>

							<button class="btn-green" title="Editar servicio" onclick="setDatos(' . $row['id'] . ', \'' . $row['servicio'] . '\', \'' . $row['jefe'] . '\')"><i class="fa-solid fa-pen"></i></button>';
							} else if ($row['estado'] == "Inactivo") {

								echo '<button class="btn-red" title="Activar servicio" onclick="window.location.href = \'/SGH/public/layouts/modules/adminPanel/controllers/turnEstadoServicio.php?id=' . $row["id"] . '&action=activar\'"><i class="fa-solid fa-circle-xmark"></i></button>

							<button class="btn-yellow" title="Eliminar servicio" onclick="showDeleteConfirmation(\'' . $row['id'] . '\', \'' . $row['servicio'] . '\', \'' . $jefeInfo['apellido'] . ' ' . $jefeInfo['nombre'] . '\')"><i class="fa-solid fa-trash"></i></button>';
							} else {

								echo 'Error al generar las acciones.';
							}

							echo '</td>';
							echo '</tr>';
						}
					}

					?>
				</tbody>
			</table>

		</div>

		<div class="modulo">

			<div class="back" id="backCargo">
				<div class="divBackForm" id="addCargo" style="display: none;">
					<div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
						<button class="btn-red" onclick="backCargo.style.display = 'none'; addCargo.style.display = 'none'; cargoForm.reset();" style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
					</div>
					<h3>Nuevo cargo</h3>
					<form action="controllers/addCargoForm.php" method="post" class="backForm" id="cargoForm">
						<div>
							<label for="cargo">Nombre del cargo</label>
							<input type="text" name="cargo" id="cargo" required>
						</div>

						<button class="btn-green"><b><i class="fa-solid fa-plus"></i> Añadir cargo</b></button>
					</form>
				</div>

				<div class="divBackForm" id="advertenciaDeleteCargo" style="padding: 1vw; display: none;">
					<h3 style="margin-bottom: 1vw;">¡Atención!</h3>
					<p>Está por eliminar un cargo, no podrá revertir esta acción. De ser un error deberá comunicarse con el administrador general.</p>
					<div class="datosCargo" style="margin-top: 2.5vw; display: flex; flex-direction: column; text-align: start; width: 100%;">
						<b style="margin-bottom: 1vw;">Cargo a eliminar:</b>
						<div class="modulo">
							<b>Nombre del cargo:</b>
							<div class="cargoName" id="cargoName"></div>
						</div>
						<div class="botones" style="width: 100%; display: flex; flex-direction: row; flex-wrap: wrap; align-content: center; justify-content: center; align-items: center;">
							<button class="btn-red" id="btnDeleteCargo"><i class="fa-solid fa-trash"></i> Eliminar cargo</button>
							<button class="btn-green" onclick="backCargo.style.display = 'none'; advertenciaDeleteCargo.style.display='none';"><i class="fa-solid fa-xmark"></i> Cancelar</button>
						</div>
					</div>
				</div>

				<div class="divBackForm" id="modCargo" style="display: none;">
					<div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
						<button class="btn-red" onclick="backCargo.style.display = 'none'; modCargo.style.display = 'none'; formModCargo.reset();" style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
					</div>
					<h3>Modificar cargo</h3>
					<form action="controllers/modifyCargo.php" method="post" id="formModCargo" class="backForm">
						<input type="hidden" name="idModCargo" id="idModCargo">
						<div>
							<label for="cargoMod">Nombre del cargo:</label>
							<input type="text" name="cargoMod" id="cargoMod">
						</div>

						<button class="btn-green"><b><i class="fa-solid fa-plus"></i> Modificar cargo</b></button>
					</form>
				</div>
			</div>

			<h3 style="width: 100%; text-align: center;">Gestión de cargos</h3>

			<div>
				<button class="btn-green" onclick="backCargo.style.display = 'flex'; addCargo.style.display = 'flex';"><b><i class="fa-solid fa-plus"></i> Agregar cargo</b></button>
			</div>

			<table>
				<thead>
					<tr>
						<th>ID</th>
						<th>Cargo</th>
						<th>Estado</th>
						<th>Acciones</th>
					</tr>
				</thead>
				<script>
					function setDatosCargo(id, cargo) {
						$('#backCargo').css('display', 'flex');
						$('#modCargo').css('display', 'flex');

						$('#idModCargo').val(id);
						$('#cargoMod').val(cargo);
					}
				</script>
				<tbody>
					<?php

					// Realiza la consulta a la tabla servicios
					$getTable = "SELECT * FROM cargos WHERE estado != 'Eliminado'";
					$stmt = $pdo->query($getTable);

					// Itera sobre los resultados y muestra las filas en la tabla
					if ($stmt->rowCount() == 0) {
						echo '<td colspan=5 class="table-middle table-center">No hay especialidades registradas</td>';
					} else {
						while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
							echo '<tr>';
							echo '<td class="table-center table-middle">' . $row['id'] . '</td>';
							echo '<td class="table-middle">' . $row['cargo'] . '</td>';
							echo '<td class="table-middle">' . $row['estado'] . '</td>';

							// No hay necesidad de abrir y cerrar una etiqueta </td> aquí, así que he eliminado el </td> extra.

							if ($row['estado'] == "Activo") {
								echo '<td>';
								echo '<button class="btn-green" title="Desactivar cargo" onclick="window.location.href = \'/SGH/public/layouts/modules/adminPanel/controllers/turnEstadoCargo.php?id=' . $row["id"] . '&action=desactivar\'"><i class="fa-solid fa-circle-check"></i></button>';
								echo '<button class="btn-green" title="Editar cargo" onclick="setDatosCargo(\'' . $row['id'] . '\', \'' . $row['cargo'] . '\')"><i class="fa-solid fa-pen"></i></button>';
								echo '</td>';
							} elseif ($row['estado'] == "Inactivo") {
								echo '<td>';
								echo '<button class="btn-red" title="Activar cargo" onclick="window.location.href = \'/SGH/public/layouts/modules/adminPanel/controllers/turnEstadoCargo.php?id=' . $row["id"] . '&action=activar\'"><i class="fa-solid fa-circle-xmark"></i></button>';
								echo '<button class="btn-yellow" title="Eliminar cargo" onclick="showDeleteConfirmationCargo(\'' . $row['id'] . '\', \'' . $row['cargo'] . '\')"><i class="fa-solid fa-trash"></i></button>';
								echo '</td>';
							} else {
								echo '<td>Error al generar las acciones.</td>';
							}

							echo '</tr>';
						}
					}

					?>

				</tbody>
			</table>

		</div>


	<?php

	}

	if (hasAccess(['administrador', 'direccion']) || hasSubAccess(['jefe_servicio'])) {

	?>

		<div class="modulo">

			<div class="back" id="backEsp">
				<div class="divBackForm" id="addEspecialidad" style="display: none;">
					<div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
						<button class="btn-red" onclick="backEsp.style.display = 'none'; addEspecialidad.style.display = 'none'; espForm.reset();" style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
					</div>
					<h3>Nueva especialidad</h3>
					<form action="controllers/addEspecialidadForm.php" method="post" class="backForm" id="espForm">
						<div>
							<label for="especialidad">Nombre de la especialidad</label>
							<input type="text" name="especialidad" id="especialidad" required>
							<input type="hidden" name="servicioEsp" value="<?php echo $servicioUser; ?>">
						</div>

						<button class="btn-green"><b><i class="fa-solid fa-plus"></i> Añadir especialidad</b></button>
					</form>
				</div>
				<div class="divBackForm" id="modEspecialidad" style="display: none;">
					<div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
						<button class="btn-red" onclick="back.style.display = 'none'; modServicio.style.display = 'none'; servicioMod.value = ''; modifyBoss.value = ''" style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
					</div>
					<h3>Modificar especialidad</h3>
					<form action="controllers/modifyEspecialidad.php" method="post" class="backForm">
						<input type="hidden" name="idModEsp" id="idModEsp">
						<div>
							<label for="especialidadMod">Nombre de la especialidad:</label>
							<input type="text" name="especialidadMod" id="especialidadMod">
						</div>

						<button class="btn-green"><b><i class="fa-solid fa-plus"></i> Modificar especialidad</b></button>
					</form>
				</div>

				<div class="divBackForm" id="advertenciaDeleteEsp" style="padding: 1vw; display: none;">
					<h3 style="margin-bottom: 1vw;">¡Atención!</h3>
					<p>Está por eliminar una especialidad, no podrá revertir esta acción. De ser un error deberá comunicarse con el administrador general.</p>
					<div class="datosServicio" style="margin-top: 2.5vw; display: flex; flex-direction: column; text-align: start; width: 100%;">
						<b style="margin-bottom: 1vw;">Especialidad a eliminar:</b>
						<div class="modulo">
							<b>Nombre del servicio:</b>
							<div class="servicioEspName" id="servicioEspName"></div>
							<b>Especialidad:</b>
							<div class="especialidadJefe" id="especialidadJefe"></div>
						</div>

					</div>
					<div class="botones" style="width: 100%; display: flex; flex-direction: row; flex-wrap: wrap; align-content: center; justify-content: center; align-items: center;">
						<button class="btn-red" id="btnDeleteEsp"><i class="fa-solid fa-trash"></i> Eliminar servicio</button>
						<button class="btn-green" onclick="backEsp.style.display = 'none'; advertenciaDeleteEsp.style.display='none';"><i class="fa-solid fa-xmark"></i> Cancelar</button>
					</div>
				</div>
			</div>

			<h3 style="width: 100%; text-align: center;">Gestión de especialidades</h3>

			<div>
				<button class="btn-green" onclick="backEsp.style.display = 'flex'; addEspecialidad.style.display = 'flex';"><b><i class="fa-solid fa-plus"></i> Agregar especialidad</b></button>
			</div>

			<table>
				<thead>
					<tr>
						<th>ID</th>
						<th>Servicio</th>
						<th>Especialidad</th>
						<th>Estado</th>
						<th>Acciones</th>
					</tr>
				</thead>
				<tbody>
					<script>
						function setDatosEspecialidad(id, especialidad) {
							$('#backEsp').css('display', 'flex');
							$('#modEspecialidad').css('display', 'flex');

							$('#idModEsp').val(id);
							$('#especialidadMod').val(especialidad);
						}
					</script>
					<?php

					// Realiza la consulta a la tabla servicios
					$getTable = "SELECT Especialidades.*, Servicios.servicio AS servicio_nombre FROM Especialidades INNER JOIN Servicios ON Especialidades.servicio_id = Servicios.id WHERE Especialidades.estado != 'Eliminado' AND Especialidades.servicio_id = $servicioUser";
					$stmt = $pdo->query($getTable);

					// Itera sobre los resultados y muestra las filas en la tabla
					// Itera sobre los resultados y muestra las filas en la tabla
					if ($stmt->rowCount() == 0) {
						echo '<td colspan=5 class="table-middle table-center">No hay especialidades registradas</td>';
					} else {
						while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
							echo '<tr>';
							echo '<td class="table-center table-middle">' . $row['id'] . '</td>';
							echo '<td class="table-middle">' . $row['servicio_nombre'] . '</td>';


							echo '<td class="table-middle">' . $row['especialidad'] . '</td>';
							echo '</td>';
							echo '<td class="table-center table-middle">' . $row['estado'] . '</td>';
							echo '<td>';

							if ($row['estado'] == "Activo") {

								echo '<button class="btn-green" title="Desactivar especialidad" onclick="window.location.href = \'/SGH/public/layouts/modules/adminPanel/controllers/turnEstadoEspecialidad.php?id=' . $row["id"] . '&action=desactivar\'"><i class="fa-solid fa-circle-check"></i></button>

							<button class="btn-green" title="Editar especialidad" onclick="setDatosEspecialidad(' . $row['id'] . ', \'' . $row['especialidad'] . '\')"><i class="fa-solid fa-pen"></i></button>';
							} else if ($row['estado'] == "Inactivo") {

								echo '<button class="btn-red" title="Activar especialidad" onclick="window.location.href = \'/SGH/public/layouts/modules/adminPanel/controllers/turnEstadoEspecialidad.php?id=' . $row["id"] . '&action=activar\'"><i class="fa-solid fa-circle-xmark"></i></button>

							<button class="btn-yellow" title="Eliminar especialidad" onclick="showDeleteConfirmationEsp(\'' . $row['id'] . '\', \'' . $row['especialidad'] . '\', \'' . $row['servicio_nombre'] . '\')"><i class="fa-solid fa-trash"></i></button>';
							} else {

								echo 'Error al generar las acciones.';
							}

							echo '</td>';
							echo '</tr>';
						}
					}


					?>
				</tbody>
			</table>
		</div>


	<?php

	}
	?>
</div>

<script src="/SGH/public/layouts/modules/adminPanel/js/adminPanel.js"></script>
<?php require_once '../../base/footer.php'; ?>