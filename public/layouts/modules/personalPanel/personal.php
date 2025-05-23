<?php

require_once '../../../../app/db/db.php';
require_once '../../../../app/db/user_session.php';
require_once '../../../../app/db/user.php';
require_once 'controllers/search_personal.php';
require_once '../../../config.php';


$user = new User();
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);
$servicio_usuario = $user->getServicio();

requireRole(['administrador', 'direccion', 'gest_personal']);

$title = "Gestión de personal";

$db = new DB();
$pdo = $db->connect();

// Obtener el parámetro 'selectServicioFilter' de la URL, si no está se establece en null
$sel = $_GET['selectServicioFilter'] ?? null;

// Si el parámetro 'selectServicioFilter' no coincide con el servicio del usuario
// y el usuario no tiene rol de "Administrador" ni "Dirección"
if (($sel != $servicio_usuario || !$sel) && !hasAccess(['administrador', 'direccion'])) {
    
    // Asignar el servicio del usuario a 'selectServicioFilter' si no es válido
    $selectServicioFilter = $user->getServicio();

    // Redirigir con el nuevo parámetro selectServicioFilter
    $url = "personal.php?pagina=$pagina";
    if ($selectServicioFilter) {
        // Asegurarse de que el valor del servicio sea correctamente escapado para la URL
        $url .= "&selectServicioFilter=" . urlencode($selectServicioFilter);
    }

    // Redirigir al usuario a la URL con el servicio correcto
    header("Location: $url");
    exit();
}



?>

<?php require_once '../../base/header.php'; ?>
<link rel="stylesheet" href="/SGH/public/layouts/modules/personalPanel/css/personal.css">

<script>
  var serviceId = "<?php echo $user->getServicio(); ?>";
</script>


<div class="content">
  <div class="modulo" style="text-align: center;">
    <h3 style="margin-bottom: .5vw;">Sistema de gestión de personal</h3>
    <p>Este sistema está oreintado a la declaración del personal <br> a cargo y administración de privilegios de los
      mismos dentro del sistema.</p>
  </div>

  <div class="modulo">


    <div class="back" id="back" style="display: none;">
      <div class="divBackForm" id="newPersonal" style="display: none;">
        <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
          <button class="btn-red" onclick="back.style.display = 'none'; newPersonal.style.display = 'none'; newPersonalForm.reset(); $('#selectServicio').val(null).trigger('change'); $('#selectEspecialidad').val(null).trigger('change'); $('#selectCargo').val(null).trigger('change');" style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
        </div>
        <h3>Declarar nuevo personal</h3>
        <form action="/SGH/public/layouts/modules/personalPanel/controllers/addPersonal.php" method="post" class="backForm" id="newPersonalForm">
          <div>
            <label for="apellido">Apellido</label>
            <input type="text" name="apellido" id="apellido" required>
          </div>
          <div>
            <label for="nombre">Nombre</label>
            <input type="text" name="nombre" id="nombre" required>
          </div>
          <div>
            <label for="dni">D.N.I.</label>
            <input type="text" name="dni" id="dni" width="100%" oninput="formatNumber(this)" required>
          </div>
          <div>
            <label for="selectServicio">Servicio</label>
            <select id="selectServicio" class="select2" name="servicio" style="width: 100%;" required onchange="selectChange()">
              <option value="" selected disabled>Seleccionar servicio...</option>
              <?php

              // Realiza la consulta a la tabla servicios
              $getServicio = "SELECT id, servicio FROM servicios WHERE estado = 'Activo'";
              $stmtServicio = $pdo->query($getServicio);

              // Itera sobre los resultados y muestra las filas en la tabla
              while ($row = $stmtServicio->fetch(PDO::FETCH_ASSOC)) {
                echo '<option value=' . $row['id'] . '>' . $row['servicio'] . '</option>';
              }

              ?>
            </select>
          </div>
          <div>
            <label for="selectEspecialidad">Especialidad</label>
            <select id="selectEspecialidad" class="select2" name="especialidad" style="width: 100%;">
              <option value="" selected disabled>Seleccionar especialidad...</option>
            </select>
          </div>
          <div style="display: flex; flex-direction: row; justify-content: space-evenly; width: 100%;">
            <div style="display: flex; flex-direction: column;">
              <label for="mn">M.N.</label>
              <input style="width: 100%;" type="number" name="mn" id="mn">
            </div>

            <div style="display: flex; flex-direction: column;">
              <label for="mp">M.P.</label>
              <input style="width: 100%;" type="number" name="mp" id="mp">
            </div>
          </div>
          <div>
            <label for="selectCargo">Cargo</label>
            <select id="selectCargo" class="select2" name="cargo" style="width: 100%;" required>
              <option value="" selected disabled>Seleccionar cargo...</option>
              <?php

              // Realiza la consulta a la tabla cargo
              $getCargo = "SELECT cargo FROM cargos WHERE estado = 'Activo'";
              $stmtCargo = $pdo->query($getCargo);

              // Itera sobre los resultados y muestra las filas en la tabla
              while ($row = $stmtCargo->fetch(PDO::FETCH_ASSOC)) {
                echo '<option value="' . $row['cargo'] . '">' . $row['cargo'] . '</option>';
              }

              ?>
            </select>
          </div>
          <div style="display: flex; flex-direction: row; justify-content: center;">
            <button class="btn-green"><b><i class="fa-solid fa-plus"></i> Declarar nuevo
                personal</b></button>
          </div>
        </form>
      </div>

      <div class="divBackForm" id="editPersonal" style="display: none;">
        <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
          <button class="btn-red" onclick="back.style.display = 'none'; editPersonal.style.display = 'none'; newPersonalForm.reset(); $('#selectServicio').val(null).trigger('change'); $('#selectEspecialidad').val(null).trigger('change'); $('#selectCargo').val(null).trigger('change'); $('#jefeCheckeado').prop('disabled', true);" style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
        </div>

        <h3>Editar personal</h3>
        <form action="/SGH/public/layouts/modules/personalPanel/controllers/modifyPersonal.php" method="post" class="backForm" id="editPersonalForm">
          <input type="hidden" name="editid" id="editid">
          <div>
            <label for="editapellido">Apellido</label>
            <input type="text" name="editapellido" id="editapellido" required>
          </div>
          <div>
            <label for="editnombre">Nombre</label>
            <input type="text" name="editnombre" id="editnombre" required>
          </div>
          <div>
            <label for="editdni">D.N.I.</label>
            <input type="text" name="editdni" id="editdni" width="100%" oninput="formatNumber(this)" required>
          </div>
          <div>
            <label for="editselectServicio">Servicio</label>
            <select id="editselectServicio" class="select2" name="editservicio" style="width: 100%;" required onchange="editselectChange()" disabled>
              <option value="" selected disabled>Seleccionar servicio...</option>
              <?php

              // Realiza la consulta a la tabla servicios
              $getServicio = "SELECT id, servicio FROM servicios WHERE estado = 'Activo'";
              $stmtServicio = $pdo->query($getServicio);

              // Itera sobre los resultados y muestra las filas en la tabla
              while ($row = $stmtServicio->fetch(PDO::FETCH_ASSOC)) {
                echo '<option value=' . $row['id'] . '>' . $row['servicio'] . '</option>';
              }

              ?>
            </select>
          </div>
          <div>
            <label for="editselectespecialidad">Especialidad</label>
            <select id="editselectespecialidad" class="select2" name="editespecialidad" style="width: 100%;">
              <option value="" selected disabled>Seleccionar especialidad...</option>
            </select>
          </div>
          <div style="display: flex; flex-direction: row; justify-content: space-evenly; width: 100%;">
            <div style="display: flex; flex-direction: column;">
              <label for="editmn">M.N.</label>
              <input style="width: 100%;" type="number" name="editmn" id="editmn">
            </div>

            <div style="display: flex; flex-direction: column;">
              <label for="editmp">M.P.</label>
              <input style="width: 100%;" type="number" name="editmp" id="editmp">
            </div>
          </div>
          <div>
            <label for="editselectcargo">Cargo</label>
            <select id="editselectcargo" class="select2" name="editcargo" style="width: 100%;" required>
              <option value="" selected disabled>Seleccionar cargo...</option>
              <option value="Jefe de servicio" disabled="true" id="jefeCheckeado">Jefe de servicio
              </option>
              <?php
              // Realiza la consulta a la tabla cargo
              $getCargo = "SELECT cargo FROM cargos WHERE estado = 'Activo'";
              $stmtCargo = $pdo->query($getCargo);

              // Itera sobre los resultados y muestra las filas en la tabla
              while ($row = $stmtCargo->fetch(PDO::FETCH_ASSOC)) {
                echo '<option value="' . $row['cargo'] . '">' . $row['cargo'] . '</option>';
              }
              ?>
            </select>
          </div>
          <div style="display: flex; flex-direction: row; justify-content: center;">
            <button class="btn-green"><b><i class="fa-solid fa-plus"></i> Confirmar edición</b></button>
          </div>
        </form>
      </div>

      <div class="divBackForm" id="newPase" style="display: none;">
        <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
          <button class="btn-red" onclick="back.style.display = 'none'; newPase.style.display = 'none'; paseForm.reset(); $('#paseSelectServicio').val(null).trigger('change');" style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
        </div>

        <h3>Realizar pase de servicio</h3>
        <form action="/SGH/public/layouts/modules/personalPanel/controllers/paseForm.php" method="POST" class="backForm" id="paseForm">
          <input type="hidden" name="paseId" id="paseId">
          <div>
            <label for="paseApellido">Apellido</label>
            <input type="text" id="paseApellido" disabled>
          </div>
          <div>
            <label for="paseNombre">Nombre</label>
            <input type="text" id="paseNombre" disabled>
          </div>
          <div>
            <label for="paseDni">DNI</label>
            <input type="text" id="paseDni" disabled>
          </div>
          <div>
            <label for="paseSelectServicio">Realizar pase a</label>
            <select name="paseSelectServicio" id="paseSelectServicio" class="select2" style="width: 100%;">
              <option value="" selected disabled>Seleccionar un servicio...</option>
              <?php

              // Realiza la consulta a la tabla servicios
              $getServicio = "SELECT id, servicio FROM servicios WHERE estado = 'Activo'";
              $stmtServicio = $pdo->query($getServicio);

              // Itera sobre los resultados y muestra las filas en la tabla
              while ($row = $stmtServicio->fetch(PDO::FETCH_ASSOC)) {
                echo '<option value=' . $row['id'] . '>' . $row['servicio'] . '</option>';
              }

              ?>
            </select>
          </div>
          <button type="submit" class="btn-green"><b><i class="fa-solid fa-right-from-bracket"></i> Realizar
              pase</b></button>
        </form>
      </div>

      <div class="divBackForm" id="newLicencia" style="display: none;">

        <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
          <button class="btn-red" onclick="back.style.display = 'none'; newLicencia.style.display = 'none'; newLicenciaForm.reset();" style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
        </div>

        <h3>Establecer nueva licencia</h3>
        <form action="/SGH/public/layouts/modules/personalPanel/controllers/licenciaForm.php" method="POST" class="backForm" id="newLicenciaForm">
          <input type="hidden" name="licenciaDniHidden" id="licenciaDniHidden">

          <div>
            <label for="licenciaApellido">Apellido</label>
            <input type="text" id="licenciaApellido" disabled>
          </div>
          <div>
            <label for="licenciaNombre">Nombre</label>
            <input type="text" id="licenciaNombre" disabled>
          </div>
          <div>
            <label for="licenciaDni">DNI</label>
            <input type="text" id="licenciaDni" name="licenciaDni" disabled required>
          </div>
          <div>
            <label for="licenciaDesde">Fecha desde</label>
            <input type="date" id="licenciaDesde" name="licenciaDesde" min="<?php echo date('Y-m-d', strtotime('-15 days')); ?>" required>
          </div>
          <div>
            <label for="licenciaHasta">Fecha hasta</label>
            <input type="date" id="licenciaHasta" name="licenciaHasta" required>
          </div>
          <div>
            <label for="licenciaTipo">Tipo de licencia</label>
            <select name="licenciaTipo" id="licenciaTipo" class="select2" style="width: 100%;" required>
              <option value="" selected disabled>Seleccione tipo de licencia...</option>
              <option value="Clave 1 - Por razones de enfermedad">Clave 1 - Por razones de enfermedad
              </option>
              <option value="Clave 4 - Por accidente de trabajo">Clave 4 - Por accidente de trabajo
              </option>
              <option value="Clave 5 - Por atención de familiar enfermo">Clave 5 - Por atención de
                familiar enfermo</option>
              <option value="Clave 6 - Por maternidad">Clave 6 - Por maternidad</option>
              <option value="Clave 8 - Descanso anual (vacaciones)">Clave 8 - Descanso anual (vacaciones)
              </option>
              <option value="Clave 14 - Duelo por familiar directo">Clave 14 - Duelo por familiar directo
              </option>
              <option value="Clave 15 - Duelo por familiar indirecto">Clave 15 - Duelo por familiar
                indirecto</option>
              <option value="Clave 16 - Por matrimonio">Clave 16 - Por matrimonio</option>
              <option value="Clave 17 - Por pre-examen">Clave 17 - Por pre-examen</option>
              <option value="Clave 18 - Por examen">Clave 18 - Por examen</option>
              <option value="Clave 26 - Por causas particulares">Clave 26 - Por causas particulares
              </option>
              <option value="Clave 31 - Por paternidad">Clave 31 - Por paternidad</option>
              <option value="Clave 33 - Por donación de sangre">Clave 33 - Por donación de sangre</option>
              <option value="Clave 34 - Licencia anual complementaria (estres)">Clave 34 - Licencia anual
                complementaria (estres)</option>
              <option value="Clave 35 - Por alimentación y cuidado del hijo">Clave 35 - Por alimentación y
                cuidado del hijo</option>
              <option value="Clave DF - Examen de Papanicolau y/o radiografía o ecografía mamaria">Clave
                DF - Examen de Papanicolau y/o radiografía o ecografía mamaria</option>
              <option value="Clave JM - Junta médica">Clave JM - Junta médica</option>
              <option value="Clave NP - Nacimiento prematuro">Clave NP - Nacimiento prematuro</option>
              <option value="Clave VV - Mujer víctima de violencia de género">Clave VV - Mujer víctima de
                violencia de género</option>
            </select>
          </div>

          <button class="btn-green" onclick="confirmLicencia.style.display = 'flex';" type="button"><i class="fa-solid fa-person-walking-luggage"></i> Establecer licencia</button>

          <div id="confirmLicencia" class="divBackForm" style="display: none;">
            <h4>¿Está seguro que quiere asignar esta licencia?</h4>
            <p style="margin-top: 1vw;">Esta acción es irreversible. Revise todos los datos.</p>

            <div style="display: flex; flex-direction: row; justify-content: center;">
              <button class="btn-red" type="submit"><i class="fa-solid fa-person-walking-luggage"></i>
                Asignar licencia</button>

              <button class="btn-green" type="button" onclick="confirmLicencia.style.display = 'none';"><i class="fa-solid fa-xmark"></i> Cancelar</button>
            </div>
          </div>

        </form>

      </div>
      <div class="divBackForm" id="newFinContrato" style="display: none;">
        <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
          <button class="btn-red" onclick="back.style.display = 'none'; newFinContrato.style.display = 'none'; finContratoForm.reset(); $('#paseSelectServicio').val(null).trigger('change');" style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
        </div>

        <h3>Informar baja de agente</h3>
        <form action="/SGH/public/layouts/modules/personalPanel/controllers/finContratoForm.php" method="POST" class="backForm" id="finContratoForm">
          <input type="hidden" name="finContratoDniHidden" id="finContratoDniHidden">

          <div>
            <label for="finContratoApellido">Apellido</label>
            <input type="text" name="finContratoApellido" id="finContratoApellido" disabled>
          </div>
          <div>
            <label for="finContratoNombre">Nombre</label>
            <input type="text" name="finContratoNombre" id="finContratoNombre" disabled>
          </div>
          <div>
            <label for="finContratoDni">DNI</label>
            <input type="text" name="finContratoDni" id="finContratoDni" disabled>
          </div>
          <div>
            <label for="finContratoFecha">Fecha de cese</label>
            <input type="date" name="finContratoFecha" id="finContratoFecha" max="<?php echo date('Y-m-d'); ?>">
          </div>
          <div>
            <label for="finContratoMotivo">Motivo del fin de contrato</label>
            <select name="finContratoMotivo" id="finContratoMotivo" class="select2" style="width: 100%;">
              <option value="" disabled selected>Seleccione un motivo de fin de contrato</option>
              <option value="Baja por renuncia">Baja por renuncia</option>
              <option value="Baja por no renovación">Baja por no renovación</option>
              <option value="Baja por despido">Baja por despido</option>
            </select>
          </div>

          <button class="btn-green" onclick="confirmFinContrato.style.display = 'flex';" type="button"><i class="fas fa-calendar-times"></i> Realizar baja de agente</button>

          <div id="confirmFinContrato" class="divBackForm" style="display: none;">
            <h4>¿Está seguro que quiere realizar la baja de agente?</h4>
            <p style="margin-top: 1vw;">Esta acción es irreversible. Revise todos los datos.</p>

            <div style="display: flex; flex-direction: row; justify-content: center;">
              <button class="btn-red" type="submit"><i class="fas fa-calendar-times"></i>
                Realizar baja de agente</button>

              <button class="btn-green" type="button" onclick="confirmFinContrato.style.display = 'none';"><i class="fa-solid fa-xmark"></i> Cancelar</button>
            </div>
          </div>
        </form>
      </div>

      <div class="divBackForm" id="newJubilacion" style="display: none;">
        <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
          <button class="btn-red" onclick="back.style.display = 'none'; newJubilacion.style.display = 'none'; jubilarForm.reset();" style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
        </div>

        <h3>Realizar jubilación</h3>
        <form action="/SGH/public/layouts/modules/personalPanel/controllers/jubilarForm.php" method="POST" class="backForm" id="jubilarForm">
          <input type="hidden" name="jubilarDniHidden" id="jubilarDniHidden">

          <div>
            <label for="jubilarApellido">Apellido</label>
            <input type="text" name="jubilarApellido" id="jubilarApellido" disabled>
          </div>
          <div>
            <label for="jubilarNombre">Nombre</label>
            <input type="text" name="jubilarNombre" id="jubilarNombre" disabled>
          </div>
          <div>
            <label for="jubilarDni">DNI</label>
            <input type="text" name="jubilarDni" id="jubilarDni" disabled>
          </div>
          <div>
            <label for="jubilarFecha">Fecha de jubilación</label>
            <input type="date" name="jubialrFecha" id="jubilarFecha" max="<?php echo date('Y-m-d'); ?>">
          </div>

          <button class="btn-green" onclick="confirmJubilar.style.display = 'flex';" type="button"><i class="fa-solid fa-person-walking-with-cane"></i> Realizar jubilación</button>

          <div id="confirmJubilar" class="divBackForm" style="display: none;">
            <h4>¿Está seguro que quiere realizar esta jubilación?</h4>
            <p style="margin-top: 1vw;">Esta acción es irreversible. Revise todos los datos.</p>

            <div style="display: flex; flex-direction: row; justify-content: center;">
              <button class="btn-red" type="submit"><i class="fa-solid fa-person-walking-with-cane"></i>
                Realizar jubilación</button>

              <button class="btn-green" type="button" onclick="confirmJubilar.style.display = 'none';"><i class="fa-solid fa-xmark"></i> Cancelar</button>
            </div>
          </div>
        </form>
      </div>

    </div>

    <div style="display: flex; flex-direction: row; width: 100%;">
      <button class="btn-green" onclick="back.style.display = 'flex'; newPersonal.style.display = 'flex';" style="width: 20.8vw;"><b><i class="fa-solid fa-plus"></i> Declarar nuevo personal</b></button>

      <div style="width: 100%;">

        <form action="personal.php" method="get" id="formFiltro" style="display: flex; flex-direction: row; flex-wrap: nowrap; align-items: center; overflow-y: hidden;">
          <input type="hidden" name="pagina" value="<?php echo isset($_GET['pagina']) ? htmlspecialchars($_GET['pagina']) : 1; ?>" id="pageInput">

          <div style="display: grid; grid-template-columns: repeat(2, 1fr); grid-template-rows: 1fr; grid-column-gap: 1vw; grid-row-gap: 0px; overflow-y: hidden;">
            <select name="selectServicioFilter" id="selectServicioFilter" class="select2"
            <?php if (!hasAccess(['administrador', 'direccion'])) { echo "disabled"; } ?> onchange="pageInput.value = 1; this.form.submit()">

              <?php
              $selectedServicio = isset($_GET['selectServicioFilter']) ? htmlspecialchars($_GET['selectServicioFilter']) : '';

              if (hasAccess(['administrador', 'direccion'])) {
                echo '<option value="" selected disabled>Seleccionar un servicio...</option>';
                echo '<option value="clr"' . ($selectedServicio === 'clr' ? ' selected' : '') . '>Seleccionar todos los servicios</option>';

                $getServicios = "SELECT id, servicio FROM servicios WHERE estado = 'Activo'";
                $stmtServicios = $pdo->query($getServicios);

                while ($row = $stmtServicios->fetch(PDO::FETCH_ASSOC)) {
                  $selected = ($selectedServicio == $row['id']) ? ' selected' : '';
                  echo '<option value="' . $row['id'] . '"' . $selected . '>' . $row['servicio'] . '</option>';
                }
              } else {
                $servicioUsuario = $user->getServicio();
                $getServicioUsuario = "SELECT id, servicio FROM servicios WHERE id = ?";
                $stmtServicioUsuario = $pdo->prepare($getServicioUsuario);
                $stmtServicioUsuario->execute([$servicioUsuario]);
                $rowServicioUsuario = $stmtServicioUsuario->fetch(PDO::FETCH_ASSOC);

                if ($rowServicioUsuario) {
                  echo '<option value="' . $rowServicioUsuario['id'] . '" selected>' . $rowServicioUsuario['servicio'] . '</option>';
                }
              }
              ?>
            </select>

            <input type="text" name="searchInput" id="searchInput" style="width: 100%;" placeholder="Buscar por DNI o nombre..."
              value="<?php echo isset($_GET['searchInput']) ? htmlspecialchars($_GET['searchInput']) : ''; ?>">

          </div>
          <button type="submit" class="btn-green"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>


      </div>
    </div>

    <div id="tablaPersonal">

      <table>
        <thead>
          <tr>
            <th class="table-middle table-center">ID</th>
            <th class="table-middle">Nombre y apellido</th>
            <th class="table-middle table-center">DNI</th>
            <th class="table-middle">Servicio</th>
            <th class="table-middle">Especialidad</th>
            <th class="table-middle table-center">Matricula</th>
            <th class="table-middle table-center">Cargo</th>
            <th class="table-middle table-center">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($totalregistros >= 1):

            foreach ($registros as $reg):
          ?>
              <tr>
                <td class="table-center table-middle"><?= $reg['id'] ?></td>

                <?php
                $fechaHoy = date("Y-m-d");

                // Consulta de licencias
                $stmtLicencias = $pdo->prepare("SELECT tipo_licencia, fecha_desde, fecha_hasta FROM licencias WHERE dni = ? AND fecha_desde <= ? AND fecha_hasta >= ?");
                $stmtLicencias->execute([$reg['dni'], $fechaHoy, $fechaHoy]);
                $licencias = $stmtLicencias->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <?php if (!empty($licencias)): ?>
                  <td class="table-middle">
                    <div style="display: flex; flex-direction: row; align-items: center;">
                      <div><?= $reg['apellido'] . ' ' . $reg['nombre'] ?></div>
                      <button class="avisoWarButton" onclick="avisoLicencia(<?= $reg['id'] ?>)">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                      </button>
                      <?php foreach ($licencias as $licencia): ?>
                        <?php
                        $fecha_desde = date("d/m/Y", strtotime($licencia['fecha_desde']));
                        $fecha_hasta = date("d/m/Y", strtotime($licencia['fecha_hasta']));
                        ?>
                        <div id="aviso-<?= $reg['id'] ?>" class="avisoWar" style="position: relative">
                          <div class="aviso">
                            <h4>El agente se encuentra de licencia.</h4></br>
                            <b>Tipo de licencia:</b> <?= $licencia['tipo_licencia'] ?>.</br>
                            <div style="margin-top: .3vw;">
                              <b>Desde:</b> <?= $fecha_desde ?></br>
                              <b>Hasta:</b> <?= $fecha_hasta ?>.
                            </div>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </td>
                <?php else: ?>
                  <td class="table-middle"><?= $reg['apellido'] . ' ' . $reg['nombre'] ?></td>
                <?php endif; ?>

                <td class="table-middle table-center"><?= $reg['dni'] ?></td>

                <?php if ($reg['servicio_id'] != "0"): ?>
                  <?php
                  $getservicioStmt = $pdo->prepare("SELECT servicio FROM servicios WHERE id = ?");
                  $getservicioStmt->execute([$reg['servicio_id']]);
                  $servicioInfo = $getservicioStmt->fetch(PDO::FETCH_ASSOC);
                  ?>
                  <td class="table-middle">
                    <?= $servicioInfo ? $servicioInfo['servicio'] : 'No se encontró la información del servicio' ?>
                  </td>
                <?php else: ?>
                  <td class="table-middle">No hay servicio asignado</td>
                <?php endif; ?>

                <td class="table-middle"><?= $reg['especialidad'] ?></td>
                <td class="table-middle">M.N: <?= $reg['mn'] ?></br>M.P: <?= $reg['mp'] ?></td>
                <td class="table-middle"><?= $reg['cargo'] ?></td>

                <td class="table-middle table-center">
                  <div class="contenedor-de-botones">
                    <button class="btn-green" title="Abrir menú de acciones" onclick="menuPersona(<?= $reg['id'] ?>)">
                      <i class="fa-solid fa-hand-pointer"></i>
                    </button>
                    <div class="buttons-div" id="menu-<?= $reg['id'] ?>">
                      <button class="btn-green" title="Editar" onclick="setDatos('<?= $reg['id'] ?>', '<?= $reg['apellido'] ?>', '<?= $reg['nombre'] ?>', '<?= $reg['dni'] ?>', '<?= $reg['servicio_id'] ?>', '<?= $reg['cargo'] ?>', '<?= $reg['especialidad'] ?>', '<?= $reg['mn'] ?>', '<?= $reg['mp'] ?>')">
                        <i class="fa-solid fa-pen"></i> Editar
                      </button>
                      <button class="btn-green" title="Pase" onclick="setDatosPase('<?= $reg['id'] ?>', '<?= $reg['apellido'] ?>', '<?= $reg['nombre'] ?>', '<?= $reg['dni'] ?>')">
                        <i class="fa-solid fa-right-from-bracket"></i> Pase
                      </button>
                      <button class="btn-green" title="Licencias" onclick="setLicencia('<?= $reg['apellido'] ?>', '<?= $reg['nombre'] ?>', '<?= $reg['dni'] ?>')">
                        <i class="fa-solid fa-person-walking-luggage"></i> Licencias
                      </button>
                      <button class="btn-yellow" title="Jubilar" onclick="setDatosJubilar('<?= $reg['apellido'] ?>', '<?= $reg['nombre'] ?>', '<?= $reg['dni'] ?>')">
                        <i class="fa-solid fa-person-walking-with-cane"></i> Jubilar
                      </button>
                      <button class="btn-yellow" title="Fin contrato" onclick="setDatosFinContrato('<?= $reg['apellido'] ?>', '<?= $reg['nombre'] ?>', '<?= $reg['dni'] ?>')">
                        <i class="fas fa-calendar-times"></i> Fin contrato
                      </button>
                      <button class="btn-yellow" title="Generar contraseña" onclick="updatePassword(<?= $reg['id'] ?>, '<?= $reg['dni'] ?>')">
                      <i class="fa-solid fa-key"></i> Reiniciar contraseña
                    </button>
                    </div>
                  </div>
                </td>
              </tr>
            <?php
            endforeach;
          else:
            ?>
            <tr>
              <td class="table-center table-middle" colspan="10">No hay registros para mostrar.</td>
            </tr>
          <?php
          endif;
          ?>
        </tbody>
      </table>

    </div>
    <!-- <div class="lds-dual-ring" style="transform: translate(36vw, 0);"></div> -->


    <?php
if ($totalregistros >= 1):
  // Número máximo de páginas que se mostrarán
  $max_paginacion = 8;

  // Cálculos de inicio y fin para las páginas visibles
  $inicio_pagina = max(1, $pagina - floor($max_paginacion / 2));
  $fin_pagina = min($numeropaginas, $pagina + floor($max_paginacion / 2));

  // Si la cantidad de páginas disponibles es menor que el máximo, ajustamos los límites
  if ($pagina - floor($max_paginacion / 2) < 1) {
    $fin_pagina = min($numeropaginas, $fin_pagina + (1 - ($pagina - floor($max_paginacion / 2))));
  }
  if ($pagina + floor($max_paginacion / 2) > $numeropaginas) {
    $inicio_pagina = max(1, $inicio_pagina - ($pagina + floor($max_paginacion / 2) - $numeropaginas));
  }
?>

  <nav aria-label="Page navigation">
    <ul class="pagination">
      <!-- Botón de "anterior" -->
      <?php if ($pagina == 1): ?>
        <li class="page-item disabled"><a class="page-link" href="#">&laquo;</a></li>
      <?php else: ?>
        <li class="page-item"><a class="page-link" href="personal.php?pagina=<?php echo $pagina - 1; ?>&selectServicioFilter=<?php echo isset($_GET['selectServicioFilter']) ? urlencode($_GET['selectServicioFilter']) : ''; ?>&searchInput=<?php echo isset($_GET['searchInput']) ? urlencode($_GET['searchInput']) : ''; ?>">&laquo;</a></li>
      <?php endif; ?>

      <!-- Página 1 -->
      <?php if ($inicio_pagina > 1): ?>
        <li class="page-item"><a class="page-link" href="personal.php?pagina=1&selectServicioFilter=<?php echo isset($_GET['selectServicioFilter']) ? urlencode($_GET['selectServicioFilter']) : ''; ?>&searchInput=<?php echo isset($_GET['searchInput']) ? urlencode($_GET['searchInput']) : ''; ?>">1</a></li>
        <li class="page-item disabled"><span class="page-link">...</span></li>
      <?php endif; ?>

      <!-- Páginas intermedias -->
      <?php for ($i = $inicio_pagina; $i <= $fin_pagina; $i++): ?>
        <?php if ($pagina == $i): ?>
          <li class="page-item active"><a class="page-link" href="personal.php?pagina=<?php echo $i; ?>&selectServicioFilter=<?php echo isset($_GET['selectServicioFilter']) ? urlencode($_GET['selectServicioFilter']) : ''; ?>&searchInput=<?php echo isset($_GET['searchInput']) ? urlencode($_GET['searchInput']) : ''; ?>"><?php echo $i; ?></a></li>
        <?php else: ?>
          <li class="page-item"><a class="page-link" href="personal.php?pagina=<?php echo $i; ?>&selectServicioFilter=<?php echo isset($_GET['selectServicioFilter']) ? urlencode($_GET['selectServicioFilter']) : ''; ?>&searchInput=<?php echo isset($_GET['searchInput']) ? urlencode($_GET['searchInput']) : ''; ?>"><?php echo $i; ?></a></li>
        <?php endif; ?>
      <?php endfor; ?>

      <!-- Última página -->
      <?php if ($fin_pagina < $numeropaginas): ?>
        <li class="page-item disabled"><span class="page-link">...</span></li>
        <li class="page-item"><a class="page-link" href="personal.php?pagina=<?php echo $numeropaginas; ?>&selectServicioFilter=<?php echo isset($_GET['selectServicioFilter']) ? urlencode($_GET['selectServicioFilter']) : ''; ?>&searchInput=<?php echo isset($_GET['searchInput']) ? urlencode($_GET['searchInput']) : ''; ?>"><?php echo $numeropaginas; ?></a></li>
      <?php endif; ?>

      <!-- Botón de "siguiente" -->
      <?php if ($pagina == $numeropaginas): ?>
        <li class="page-item disabled"><a class="page-link" href="#">&raquo;</a></li>
      <?php else: ?>
        <li class="page-item"><a class="page-link" href="personal.php?pagina=<?php echo $pagina + 1; ?>&selectServicioFilter=<?php echo isset($_GET['selectServicioFilter']) ? urlencode($_GET['selectServicioFilter']) : ''; ?>&searchInput=<?php echo isset($_GET['searchInput']) ? urlencode($_GET['searchInput']) : ''; ?>">&raquo;</a></li>
      <?php endif; ?>
    </ul>
  </nav>

<?php endif; ?>




  </div>
</div>

<script src="js/personal.js"></script>
<?php require_once '../../base/footer.php'; ?>