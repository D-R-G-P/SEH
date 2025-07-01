<?php
// --- INCLUSIÓN DE DEPENDENCIAS ---
// Es crucial que las rutas sean correctas. Considerar definir una constante BASE_PATH si la estructura es compleja.
// define('BASE_PATH', realpath(__DIR__ . '/../../../../')); // Ejemplo si este archivo está 4 niveles adentro
// require_once BASE_PATH . '/app/db/db.php';

require_once '../../../../app/db/db.php';
require_once '../../../../app/db/user_session.php';
require_once '../../../../app/db/user.php';
require_once '../../../config.php'; // Archivo de configuración general
require_once 'api/decrypt.php';   // Funciones de desencriptación (¡ASEGURAR SU ROBUSTEZ!)

// --- GESTIÓN DE SESIÓN Y USUARIO ---
$user = new User();
$userSession = new UserSession();

$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser); // Configura el usuario actual en el objeto User

// --- CONTROL DE ROLES Y SUBROLES ---
// Estas funciones deberían estar definidas globalmente o incluidas.
// requireRole y requireSubRole deberían detener la ejecución o redirigir si no se cumplen los roles.
requireRole(['administrador', 'direccion', 'gestion_turnos']);
requireSubRole(['chat_turnos']);

// --- CONFIGURACIÓN DE LA PÁGINA ---
$title = "GDT -> Chat"; // Título de la página

// --- CONEXIÓN A BASE DE DATOS ---
$db = new DB();
$pdo = $db->connect(); // Obtiene el objeto PDO para las consultas

// --- DATOS DEL USUARIO ACTUAL ---
$dni = $user->getDni();
if (!$dni) {
	// Manejar caso donde el DNI no está disponible (ej. error, sesión inválida)
	// Podría ser un error fatal para esta página.
	die("Error: No se pudo obtener el DNI del usuario.");
}

// --- VALIDACIÓN DE PARÁMETRO 'estado' EN URL ---
// Redirige a un estado por defecto si 'estado' no es válido o no está presente.
$allowed_states = ["chatting", "pendiente", "finalizado"];
$current_state = $_GET['estado'] ?? 'chatting'; // Valor por defecto 'chatting'

if (!in_array($current_state, $allowed_states)) {
	header('Location: chatting.php?estado=chatting');
	exit; // Detener ejecución después de redirigir
}

// --- OBTENER COMANDOS ACTIVOS PARA SUGERENCIAS ---
// Estos comandos se pasarán a JavaScript.
try {
	$stmt_comandos = $pdo->prepare("SELECT comando, texto FROM comandos WHERE estado = 'activo'");
	$stmt_comandos->execute();
	$comandos = $stmt_comandos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
	// Manejar error de base de datos (ej. log, mensaje genérico)
	error_log("Error al obtener comandos: " . $e->getMessage());
	$comandos = []; // Devolver un array vacío en caso de error para no romper JS
}

// --- INCLUSIÓN DEL HEADER HTML ---
// El header.php probablemente contiene el inicio del HTML, <head>, y la parte superior del <body>
require_once '../../base/header.php';
?>

<link rel="stylesheet" href="/SGH/public/layouts/modules/gestion_turnos/css/chatting.css">
<script src="js/telefono-argentino.min.js"></script>

<script>
	// Es importante escapar correctamente las variables si pudieran contener caracteres especiales de JS.
	// json_encode es seguro para strings y estructuras complejas.
	var dni = <?php echo json_encode($dni); ?>;
	const comandosCache = <?php echo json_encode($comandos); ?>; // Comandos para la funcionalidad de autocompletar
</script>

<div class="content">
	<div class="back" id="back" style="display: none;">

		<div class="divBackForm" id="editPatientModal" style="dispay: none;">
			<div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
				<button class="btn-red"
					onclick="document.getElementById('back').style.display = 'none'; document.getElementById('derivar').style.display = 'none'; document.getElementById('derivarForm').reset(); $('#agenteSelect').val(null).trigger('change');"
					style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
			</div>

			<h3 class="formTitle">Editar paciente</h3>
			<form id="editPatientForm" class="backForm" style="align-items: flex-start;">
				<input type="hidden" id="edit_patient_id" name="id_paciente">

				<label for="edit_apellidos">Apellidos:</label>
				<input type="text" id="edit_apellidos" name="apellidos" required>

				<label for="edit_nombres">Nombres:</label>
				<input type="text" id="edit_nombres" name="nombres" required>

				<label>Sexo:</label>
				<div style="display: flex; gap: 1em; align-items: center; flex-direction: row;">
					<label style="display: flex; flex-wrap: nowrap; align-items: center;">
						<input type="radio" name="sexo" id="edit_sexo_masculino" value="Masculino">
						Masculino
					</label>
					<label style="display: flex; flex-wrap: nowrap; align-items: center;">
						<input type="radio" name="sexo" id="edit_sexo_femenino" value="Femenino">
						Femenino
					</label>
					<label style="display: flex; flex-wrap: nowrap; align-items: center;">
						<input type="radio" name="sexo" id="edit_sexo_x" value="X">
						X
					</label>
				</div>

				<label for="edit_tipo_documento" style="margin-top: 1vw;">Tipo de Documento:</label>
				<input type="text" id="edit_tipo_documento" name="tipo_documento">

				<label for="edit_documento">Documento:</label>
				<input type="text" id="edit_documento" name="documento">

				<label for="edit_fecha_nacimiento">Fecha de Nacimiento:</label>
				<input type="date" id="edit_fecha_nacimiento" name="fecha_nacimiento">

				<label for="edit_identidad_genero">Identidad de Género:</label>
				<input type="text" id="edit_identidad_genero" name="identidad_genero">

				<label for="edit_nombre_autopercibido">Nombre Autopercibido:</label>
				<input type="text" id="edit_nombre_autopercibido" name="nombre_autopercibido">

				<label for="edit_provincia">Provincia:</label>
				<input type="text" id="edit_provincia" name="provincia">

				<label for="edit_partido">Partido:</label>
				<input type="text" id="edit_partido" name="partido">

				<label for="edit_ciudad">Ciudad:</label>
				<input type="text" id="edit_ciudad" name="ciudad">

				<label for="edit_calle">Calle:</label>
				<input type="text" id="edit_calle" name="calle">

				<label for="edit_numero">Número:</label>
				<input type="text" id="edit_numero" name="numero">

				<label for="edit_piso">Piso:</label>
				<input type="text" id="edit_piso" name="piso">

				<label for="edit_departamento">Departamento:</label>
				<input type="text" id="edit_departamento" name="departamento">

				<label for="edit_telefono">Teléfono:</label>
				<input type="text" id="edit_telefono" name="telefono" required>

				<label for="edit_mail">Email:</label>
				<input type="email" id="edit_mail" name="mail">

				<label for="edit_obra_social">Obra Social:</label>
				<input type="text" id="edit_obra_social" name="obra_social">

				<button type="submit" class="btn-green">Guardar Cambios</button>
			</form>
		</div>

		<div class="divBackForm" id="derivar" style="display: none;">
			<div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
				<button class="btn-red"
					onclick="document.getElementById('back').style.display = 'none'; document.getElementById('derivar').style.display = 'none'; document.getElementById('derivarForm').reset(); $('#agenteSelect').val(null).trigger('change');"
					style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
			</div>
			<h3>Derivar chat</h3>
			<form action="api/modificar_estado_chat.php" method="post" id="derivarForm" style="margin-top: 1vw;">
				<input type="hidden" name="id_chat" id="id_chat">
				<div>
					<label for="agenteSelect">Seleccionar agente</label>
					<select name="agenteSelect" id="agenteSelect" class="select2" style="width: 100%;" required>
						<option value="" disabled selected>Seleccionar una opción</option>
						<?php
						try {
							// Obtener agentes disponibles para derivar (que tengan subrol 'chat_turnos' y no sean el usuario actual)
							$sql_agentes = "SELECT p.dni, p.nombre, p.apellido 
                                            FROM personal p
                                            JOIN usuarios_subroles us ON p.dni = us.dni
                                            JOIN subroles s ON us.subrol_id = s.id
                                            WHERE s.subrol = 'chat_turnos' AND p.dni != :current_dni AND p.estado = 'activo'"; // Añadir p.estado = 'activo' si es relevante
							$stmt_agentes = $pdo->prepare($sql_agentes);
							$stmt_agentes->bindParam(':current_dni', $dni, PDO::PARAM_STR);
							$stmt_agentes->execute();
							$agentes = $stmt_agentes->fetchAll(PDO::FETCH_ASSOC);

							foreach ($agentes as $agente) {
								echo "<option value='" . htmlspecialchars($agente['dni']) . "'>" . htmlspecialchars($agente['nombre'] . " " . $agente['apellido']) . "</option>";
							}
						} catch (PDOException $e) {
							error_log("Error al obtener agentes para derivar: " . $e->getMessage());
							echo "<option value=''>Error al cargar agentes</option>";
						}
						?>
					</select>
				</div>
				<button type="submit" class="btn-green"><b>Derivar</b></button>
			</form>
		</div>

		<div class="divBackForm" id="newChat" style="display: none;">
			<div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
				<button class="btn-red"
					onclick="document.getElementById('back').style.display = 'none'; document.getElementById('newChat').style.display = 'none'; document.querySelectorAll('#newChat form').forEach(form => form.reset()); $('#contacto').val(null).trigger('change'); $('#paciente').val(null).trigger('change');"
					style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
			</div>
			<h3 class="formTitle">Nuevo chat</h3>

			<div class="radio-group">
				<input type="radio" id="radioContacto" name="chatType" value="contacto" checked
					onchange="changeForm(this.value)">
				<label for="radioContacto" class="radio-label">Contacto</label>

				<input type="radio" id="radioPaciente" name="chatType" value="paciente"
					onchange="changeForm(this.value)">
				<label for="radioPaciente" class="radio-label">Paciente</label>

				<input type="radio" id="radioNumero" name="chatType" value="numero" onchange="changeForm(this.value)">
				<label for="radioNumero" class="radio-label">Número</label>
			</div>

			<form action="api/iniciar_chat.php" method="post" id="newContacto"
				style="width: 80%; display: flex; justify-content: center; flex-direction: column;">
				<input type="hidden" name="agente" value="<?php echo htmlspecialchars($dni); ?>">
				<input type="hidden" name="tipo" value="contacto">
				<div style="margin-top: 1vw;">
					<label for="contacto">Seleccionar contacto</label>
					<select name="telefono" id="contacto" class="select2" style="width: 100%;" required>
						<?php
						try {
							// 'number' y 'name' son placeholders, ajusta a los nombres reales de tus columnas en 'contacts'
							$sql_contacts = "SELECT `number`, `name` FROM contacts WHERE status = 'activo'"; // Asumiendo que las columnas se llaman 'number' y 'name'
							$stmt_contacts = $pdo->prepare($sql_contacts);
							$stmt_contacts->execute();
							$contacts = $stmt_contacts->fetchAll(PDO::FETCH_ASSOC);

							if (count($contacts) == 0) {
								echo "<option value='' disabled>No hay contactos activos</option>";
							} else {
								echo '<option value="" disabled selected>Seleccionar un contacto</option>';
								foreach ($contacts as $contact) {
									echo "<option value='" . htmlspecialchars($contact['number']) . "'>" . htmlspecialchars($contact['name']) . "</option>";
								}
							}
						} catch (PDOException $e) {
							error_log("Error al obtener contactos: " . $e->getMessage());
							echo "<option value=''>Error al cargar contactos</option>";
						}
						?>
					</select>
				</div>
				<div style="margin-top: 1vw;"><button type="submit" class="btn-green">Abrir chat</button></div>
			</form>

			<form action="api/iniciar_chat.php" method="post" id="newPaciente"
				style="width: 80%; display: none; justify-content: center; flex-direction: column;">
				<input type="hidden" name="agente" value="<?php echo htmlspecialchars($dni); ?>">
				<input type="hidden" name="tipo" value="paciente">
				<div style="margin-top: 1vw;">
					<label for="paciente">Seleccionar paciente</label>
					<select name="telefono" id="paciente" class="select2" style="width: 100%;" required>
						<?php
						try {
							// La función decryptData se usa aquí. ¡Asegurar su seguridad!
							$sql_pacientes = "SELECT telefono, nombres, apellidos, documento FROM pacientes_chat"; // Asumiendo que hay un campo 'estado'
							$stmt_pacientes = $pdo->prepare($sql_pacientes);
							$stmt_pacientes->execute();
							$pacientes = $stmt_pacientes->fetchAll(PDO::FETCH_ASSOC);

							if (count($pacientes) == 0) {
								echo "<option value='' disabled>No hay pacientes activos</option>";
							} else {
								echo '<option value="" disabled selected>Seleccionar un paciente</option>';
								foreach ($pacientes as $pac) {
									// IMPORTANTE: La desencriptación debe ser segura.
									// Considera si es mejor desencriptar solo al mostrar o si es seguro aquí.
									$nombre_completo = decryptData($pac['nombres']) . " " . decryptData($pac['apellidos']) . " - " . decryptData($pac['documento']);
									echo "<option value='" . htmlspecialchars($pac['telefono']) . "'>" . htmlspecialchars($nombre_completo) . "</option>";
								}
							}
						} catch (PDOException $e) {
							error_log("Error al obtener pacientes: " . $e->getMessage());
							echo "<option value=''>Error al cargar pacientes</option>";
						} catch (Exception $e) { // Capturar excepciones de decryptData si las lanza
							error_log("Error al desencriptar datos de paciente: " . $e->getMessage());
							echo "<option value=''>Error al procesar datos de pacientes</option>";
						}
						?>
					</select>
				</div>
				<div style="margin-top: 1vw;"><button type="submit" class="btn-green">Abrir chat</button></div>
			</form>

			<form action="api/iniciar_chat.php" method="post" id="newNumero" style="display: none;">
				<input type="hidden" name="agente" value="<?php echo htmlspecialchars($dni); ?>">
				<input type="hidden" name="tipo" value="numero">
				<div style="margin-top: 1vw;">
					<label for="numeroInput">Ingresar número</label>
					<div style="display: flex; flex-direction: row; gap: 5px;">
						<div style="margin: 0; width: auto;"> <select id="countrySelect" class="js-example-templating"
								name="country" style="width: 100%;" onchange="checkOther(this.value)">
								<option value="549" data-flag="ar" selected>AR (+54 9)</option>
								<option value="559" data-flag="br">BR (+55 9)</option>
								<option value="5959" data-flag="py">PY (+595 9)</option>
								<option value="5989" data-flag="uy">UY (+598 9)</option>
								<option value="58" data-flag="ve">VE (+58)</option>
								<option value="57" data-flag="co">CO (+57)</option>
								<option value="5939" data-flag="ec">EC (+593 9)</option>
								<option value="un" data-flag="un">Otro</option>
							</select>
							<input type="number" name="otherCountry" id="otherCountry"
								style="display: none; width: 95%; margin-top: 5px;" placeholder="Código país"
								oninput="this.value = this.value.replace(/[^0-9]/g, '')">
						</div>
						<input type="tel" name="telefono" id="numeroInput" placeholder="Área y número" required
							style="flex-grow: 1;">
					</div>
				</div>
				<div style="margin-top: 1vw; margin-left: 0;"><button type="submit" class="btn-green">Abrir
						chat</button></div>
			</form>
		</div>

		<?php if (hasSubAccess(['chat_turnos_adm'])) { ?>
			<div class="divBackForm" id="contactDiv" style="display: none;">
				<div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
					<button class="btn-red"
						onclick="document.getElementById('back').style.display = 'none'; document.getElementById('contactDiv').style.display = 'none';"
						style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
				</div>
				<h3 class="formTitle">Lista de contactos</h3>

				<form action="controllers/newContact.php" method="post" id="newContact"
					style="display: flex; flex-direction: row; align-items: flex-end;">
					<div style="margin: 0 .8vw">
						<label for="nombre">Nombre</label>
						<input type="text" name="nombre" id="nombre" placeholder="Nombre del contacto" required
							style="width: 100%;">
					</div>
					<div>
						<label for="telefono">Número</label>
						<input type="number" name="telefono" id="telefono" placeholder="Ej: 5492216480617" required
							style="width: 100%;">
					</div>
					<button class="btn-green" type="submit" style="margin: 0 .5vw; width: 2.8vw; height: 2.8vw;"><i
							class="fa-solid fa-user-plus"></i></button>
				</form>

				<form action="controllers/newContact.php" method="post" id="editContact"
					style="display: none; flex-direction: row; align-items: flex-end;">
					<h2>Editando contacto</h2>
					<input type="hidden" name="id_contact" id="id_contact">
					<div style="margin: 0 .8vw">
						<label for="nombre">Nombre</label>
						<input type="text" name="nombre" id="editNombre" placeholder="Nombre del contacto" required
							style="width: 100%;">
					</div>
					<div>
						<label for="telefono">Número</label>
						<input type="number" name="telefono" id="editTelefono" placeholder="Ej: 5492216480617" required
							style="width: 100%;">
					</div>
					<button class="btn-green" type="submit" style="margin: 0 .5vw; width: 2.8vw; height: 2.8vw;"><i
							class='fa-solid fa-pen'></i></button>
				</form>

				<div style="margin: .8vw;">
					<table>
						<thead>
							<tr>
								<th>Nombre</th>
								<th>Número</th>
								<th style="width: fit-content;">Acción</th>
							</tr>
						</thead>
						<tbody>
							<?php
							try {
								// Obtener contactos
								$sql_contacts = "SELECT `id`, `number`, `name`, `status` FROM contacts";
								$stmt_contacts = $pdo->prepare($sql_contacts);
								$stmt_contacts->execute();
								$contacts = $stmt_contacts->fetchAll(PDO::FETCH_ASSOC);

								if (count($contacts) === 0) {
									echo '<tr><td colspan="3">No hay contactos activos</td></tr>';
								} else {
									foreach ($contacts as $contact) {
										$id = (int) $contact["id"];
										$name = htmlspecialchars($contact["name"]);
										$number = htmlspecialchars($contact["number"]);
										$status = $contact["status"];
										$btnClass = ($status === "activo") ? 'btn-green' : 'btn-red';

										echo '<tr>';
										echo "<td class='table-middle'>{$name}</td>";
										echo "<td class='table-middle'>{$number}</td>";
										echo "<td style='white-space: nowrap;'>";

										// Botón editar
										echo "<button class='btn-tematico' onClick='editContact(" .
											json_encode((string) $id) . ", " .
											json_encode($name) . ", " .
											json_encode($number) . ")'>" .
											"<i class='fa-solid fa-pen'></i>" .
											"</button> ";

										// Botón activar/desactivar
										echo "<button class='{$btnClass}' onClick='toggleContactStatus(" .
											json_encode((string) $id) . ", " .
											json_encode($status) . ")'>" .
											"<i class='fa-solid fa-power-off'></i>" .
											"</button>";

										echo '</td>';
										echo '</tr>';
									}
								}
							} catch (PDOException $e) {
								error_log("Error al obtener contactos: " . $e->getMessage());
								echo '<tr><td colspan="3">Error al cargar contactos</td></tr>';
							}
							?>

						</tbody>
					</table>
				</div>

			</div>
		<?php } ?>

		<?php if (hasSubAccess(['chat_turnos_adm'])) { ?>
			<div class="divBackForm" id="comandosDiv" style="display: none;">
				<div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
					<button class="btn-red"
						onclick="document.getElementById('back').style.display = 'none'; document.getElementById('comandosDiv').style.display = 'none';"
						style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
				</div>
				<h3 class="formTitle">Lista de comandos</h3>

				<form action="controllers/newCommand.php" method="post" id="newCommand"
					style="display: flex; flex-direction: row; align-items: flex-end;">
					<div style="margin: 0 .8vw">
						<label for="comando">Comando</label>
						<input type="text" name="comando" id="comando" placeholder="Nombre del comando" required
							style="width: 100%;">
					</div>
					<div>
						<label for="texto">Texto</label>
						<textarea name="texto" id="texto" required></textarea>
					</div>
					<button class="btn-green" type="submit" style="margin: 0 .5vw; width: 2.8vw; height: 2.8vw;"><i
							class="fa-solid fa-plus"></i></button>
				</form>

				<form action="controllers/newCommand.php" method="post" id="editCommandD"
					style="display: none; flex-direction: row; align-items: flex-end;">
					<h2>Editando comando</h2>
					<input type="hidden" name="id_command" id="id_command">
					<div style="margin: 0 .8vw">
						<label for="comando">Comando</label>
						<input type="text" name="comando" id="editComando" placeholder="Nombre del comando" required
							style="width: 100%;">
					</div>
					<div>
						<label for="Texto">texto</label>
						<textarea name="texto" id="editTexto" required></textarea>
					</div>
					<button class="btn-green" type="submit" style="margin: 0 .5vw; width: 2.8vw; height: 2.8vw;"><i
							class='fa-solid fa-pen'></i></button>
				</form>

				<div style="margin: .8vw;">
					<table>
						<thead>
							<tr>
								<th>Comando</th>
								<th>Texto</th>
								<th style="width: fit-content;">Acción</th>
							</tr>
						</thead>
						<tbody>
							<?php
							try {
								// Obtener contactos
								$sql_commands = "SELECT `id`, `comando`, `texto`, `estado` FROM comandos";
								$stmt_commands = $pdo->prepare($sql_commands);
								$stmt_commands->execute();
								$commands = $stmt_commands->fetchAll(PDO::FETCH_ASSOC);

								if (count($commands) === 0) {
									echo '<tr><td colspan="3">No hay comandos activos</td></tr>';
								} else {
									foreach ($commands as $command) {
										$id = (int) $command["id"];
										$comand = htmlspecialchars($command["comando"]);
										$texto = htmlspecialchars($command["texto"]);
										$status = $command["estado"];
										$btnClass = ($status === "activo") ? 'btn-green' : 'btn-red';

										echo '<tr>';
										echo "<td class='table-middle'>{$comand}</td>";
										echo "<td class='table-middle'>{$texto}</td>";
										echo "<td style='white-space: nowrap;'>";

										// Botón editar
										echo "<button class='btn-tematico' onClick='editCommand(" .
											json_encode((string) $id) . ", " .
											json_encode($comand) . ", " .
											json_encode($texto) . ");'>" .
											"<i class='fa-solid fa-pen'></i>" .
											"</button> ";

										// Botón activar/desactivar
										echo "<button class='{$btnClass}' onClick='toggleCommandStatus(" .
											json_encode((string) $id) . ", " .
											json_encode($status) . ")'>" .
											"<i class='fa-solid fa-power-off'></i>" .
											"</button>";

										echo '</td>';
										echo '</tr>';
									}
								}
							} catch (PDOException $e) {
								error_log("Error al obtener comandos: " . $e->getMessage());
								echo '<tr><td colspan="3">Error al cargar comandos</td></tr>';
							}
							?>

						</tbody>
					</table>
				</div>

			</div>
		<?php } ?>
	</div>

	<div class="modulo" style="position: relative;">
		<div id="patientData" class="patient-panel"></div>
		<div id="patientOptions" class="patient-panel"></div>

		<div class="chats">
			<div class="chats_buttons">
				<button class="btn-chat" id="chatting"><i class="fas fa-inbox"></i> Entrada</button>
				<button class="btn-chat" id="pendiente"><i class="fas fa-hourglass-half"></i> Esperando</button> <button
					class="btn-chat" id="finalizado"><i class="fas fa-box-archive"></i> Cerrados</button>
			</div>
			<div class="search-box">
				<i class="fa-solid fa-magnifying-glass"></i>
				<input type="text" placeholder="Buscar chat..." id="search-input">
			</div>
			<div class="chat-list">
			</div>
			<?php if (hasSubAccess(['chat_turnos_adm'])) { ?>

				<div class="adm_line">
					<label class="switch">
						<input type="checkbox" id="adm_mode" class="role-checkbox">
						<span class="slider round"></span>
					</label>
					<label for="adm_mode" style="margin-left: .3vw;">Modo administrador</label>

					<button class="btn-tematico" id="contactButton" title="Editar lista de contactos">
						<i class="fa-solid fa-user-plus"></i>
					</button>

					<button class="btn-tematico" id="commandosButton" title="Editar lista de comandos">
						<i class="fa-solid fa-code"></i>
				</div>

			<?php } ?>
		</div>

		<div class="chat">
			<div class="chat-header" id="chatHeader">
			</div>
			<div class="chat-body" id="chatBody">
			</div>
			<div class="chat-footer" style="position: relative; display: flex; flex-direction: column; gap: .5vw;">
				<div id="filePreview"
					style="display: none; flex-wrap: wrap; gap: 5px; padding: 5px; border-top: 1px solid #eee;">
				</div>

				<div class="chat-input-area" style="display: flex; align-items: center; width: 100%; gap: .5vw;">
					<button id="emojiList" class="chat-control-button" disabled title="Emojis"><i
							class="fa-regular fa-face-smile"></i></button>

					<label for="docInput" class="custom-file-upload chat-control-button disabled"
						title="Adjuntar archivo">
						<i class="fa-solid fa-paperclip"></i>
					</label>
					<input type="file" id="docInput"
						accept="image/*, .pdf, .doc, .docx, .xls, .xlsx, .ppt, .pptx, .txt, .csv, .zip, .rar, audio/*, video/*"
						multiple disabled style="display: none;">

					<ul id="sugerencias" class="suggestions-list" style="display: none; /* Estilos en JS */"></ul>

					<textarea id="messageInput" autocapitalize="sentences" autocomplete="off" autofocus required
						placeholder="Escribe un mensaje..." disabled class="chat-message-input"></textarea>

					<button id="sendMessageButton" class="chat-control-button send-button" disabled
						title="Enviar mensaje">
						<img src="/SGH/public/resources/image/send.svg" alt="Enviar"> </button>
				</div>

				<emoji-picker locale="es" class="light emoji-table"
					style="display: none; position: absolute; bottom: 100%; right: 0; z-index: 1000;"></emoji-picker>
			</div>
		</div>
	</div>
</div>

<script src="/SGH/public/layouts/modules/gestion_turnos/js/chatting.js"></script>
<script type="module" src="/SGH/node_modules/emoji-picker-element/index.js"></script>

<?php
// --- INCLUSIÓN DEL FOOTER HTML ---
// El footer.php probablemente contiene el cierre del <body>, </html> y scripts comunes.
require_once '../../base/footer.php';
?>