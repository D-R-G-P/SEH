<?php

require_once '../../../../app/db/db.php';
require_once '../../../../app/db/user_session.php';
require_once '../../../../app/db/user.php';

$user = new User();
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);

$title = "Gestión de personal";

$db = new DB();
$pdo = $db->connect();


?>

<?php require_once '../../base/header.php'; ?>
<link rel="stylesheet" href="/SGH/public/layouts/modules/personalPanel/css/personal.css">


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
          <button class="btn-red" onclick="back.style.display = 'none'; newPersonal.style.display = 'none'; newPersonalForm.reset(); $('#selectServicio').val(null).trigger('change'); $('#selectEspecialidad').val(null).trigger('change'); $('#selectCargo').val(null).trigger('change'); $('#selectRol').val(null).trigger('change');" style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
        </div>
        <h3>Declarar nuevo personal</h3>
        <form action="/SGH/public/layouts/modules/personalPanel/controllers/addPersonal.php" method="post" class="backForm" id="newPersonalForm">
          <div style="margin-top: 15vw;">
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
                echo '<option value=' . $row['cargo'] . '>' . $row['cargo'] . '</option>';
              }

              ?>
            </select>
          </div>
          <div>
            <label for="selectRol">Rol</label>
            <select id="selectRol" class="select2" name="rol" style="width: 100%;" required>
              <option value="" selected disabled>Seleccionar un rol...</option>

              <?php

              switch ($user->getRol()) {
                case 'Administrador':
                  echo '<option value="Sin rol">Sin rol</option>
                  <option value="Administrador">Administrador</option>
                  <option value="Dirección">Dirección</option>
                  <option value="Deposito">Deposito</option>
                  <option value="Mantenimiento">Mantenimiento</option>
                  <option value="Patrimoniales">Patrimoniales</option>
                  <option value="Informatica">Informatica</option>
                  <option value="Jefe de servicio">Jefe de servicio</option>
                  <option value="Docencia e investigación">Docencia e investigación</option>';
                  break;

                case 'Dirección':
                  echo '<option value="Sin rol">Sin rol</option>
                    <option value="Administrador">Administrador</option>
                    <option value="Dirección">Dirección</option>
                    <option value="Deposito">Deposito</option>
                    <option value="Mantenimiento">Mantenimiento</option>
                    <option value="Patrimoniales">Patrimoniales</option>
                    <option value="Informatica">Informatica</option>
                    <option value="Jefe de servicio">Jefe de servicio</option>
                    <option value="Docencia e investigación">Docencia e investigación</option>';
                  break;

                default:
                  echo '<option value="Sin rol">Sin rol</option>';
                  break;
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
          <button class="btn-red" onclick="back.style.display = 'none'; editPersonal.style.display = 'none'; newPersonalForm.reset(); $('#selectServicio').val(null).trigger('change'); $('#selectEspecialidad').val(null).trigger('change'); $('#selectCargo').val(null).trigger('change'); $('#selectRol').val(null).trigger('change'); $('#jefeCheckeado').prop('disabled', true);" style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
        </div>

        <script>
          function setDatos(id, apellido, nombre, dni, servicio, cargo, especialidad, mn, mp, rol) {
            $('#back').css('display', 'flex');
            $('#editPersonal').css('display', 'flex');

            $('#editid').val(id);
            $('#editapellido').val(apellido);
            $('#editnombre').val(nombre);
            $('#editdni').val(dni);
            $('#editselectServicio').val(servicio).trigger('change');
            $('#editmn').val(mn);
            $('#editmp').val(mp);
            $('#editselectcargo').val(cargo).trigger('change');
            $('#editselectrol').val(rol).trigger('change');

            // Llama a la función editselectChange para actualizar el select de especialidades
            editselectChange(especialidad);
            jefeCheck(dni);
          }
        </script>

        <h3>Editar personal</h3>
        <form action="/SGH/public/layouts/modules/personalPanel/controllers/modifyPersonal.php" method="post" class="backForm" id="editPersonalForm">
          <input type="hidden" name="editid" id="editid">
          <div style="margin-top: 15vw;">
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
                echo '<option value=' . $row['cargo'] . '>' . $row['cargo'] . '</option>';
              }
              ?>
            </select>
          </div>
          <div>
            <label for="editselectrol">Rol</label>
            <select id="editselectrol" class="select2" name="editrol" style="width: 100%;" required>
              <option value="" disabled selected>Seleccione rol...</option>

              <?php

              switch ($user->getRol()) {
                case 'Administrador':
                  echo '<option value="Sin rol">Sin rol</option>
                  <option value="Administrador">Administrador</option>
                  <option value="Dirección">Dirección</option>
                  <option value="Deposito">Deposito</option>
                  <option value="Mantenimiento">Mantenimiento</option>
                  <option value="Patrimoniales">Patrimoniales</option>
                  <option value="Informatica">Informatica</option>
                  <option value="Jefe de servicio">Jefe de servicio</option>
                  <option value="Docencia e investigación">Docencia e investigación</option>';
                  break;

                case 'Dirección':
                  echo '<option value="Sin rol">Sin rol</option>
                    <option value="Administrador">Administrador</option>
                    <option value="Dirección">Dirección</option>
                    <option value="Deposito">Deposito</option>
                    <option value="Mantenimiento">Mantenimiento</option>
                    <option value="Patrimoniales">Patrimoniales</option>
                    <option value="Informatica">Informatica</option>
                    <option value="Jefe de servicio">Jefe de servicio</option>
                    <option value="Docencia e investigación">Docencia e investigación</option>';
                  break;

                default:
                  echo '<option value="Sin rol">Sin rol</option>';
                  break;
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

        <script>
          function setDatosPase(id, apellido, nombre, dni) {
            $('#back').css('display', 'flex');
            $('#newPase').css('display', 'flex');

            $('#paseId').val(id);
            $('#paseApellido').val(apellido);
            $('#paseNombre').val(nombre);
            $('#paseDni').val(dni);
          }
        </script>

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

        <script>
          function setLicencia(apellido, nombre, dni) {

            $('#back').css('display', 'flex');
            $('#newLicencia').css('display', 'flex');

            $('#licenciaApellido').val(apellido);
            $('#licenciaNombre').val(nombre);
            $('#licenciaDni').val(dni);
            $('#licenciaDniHidden').val(dni);
          }
        </script>

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

        <script>
          function setDatosFinContrato(apellido, nombre, dni) {
            $('#back').css('display', 'flex');
            $('#newFinContrato').css('display', 'flex');

            $('#finContratoApellido').val(apellido);
            $('#finContratoNombre').val(nombre);
            $('#finContratoDni').val(dni);
            $('#finContratoDniHidden').val(dni);
          }
        </script>

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

        <script>
          function setDatosJubilar(apellido, nombre, dni) {
            $('#back').css('display', 'flex');
            $('#newJubilacion').css('display', 'flex');

            $('#jubilarApellido').val(apellido);
            $('#jubilarNombre').val(nombre);
            $('#jubilarDni').val(dni);
            $('#jubilarDniHidden').val(dni);
          }
        </script>

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

      <div style="display: flex; flex-direction: row; justify-content: space-evenly; width: calc(100% - 20.8vw)">
        <select name="selectServicioFilter" id="selectServicioFilter" class="select2" placeholder="Seleccionar un servicio para filtrar" style="width: 45%;">
          <?php
          if ($user->getRol() == 'Administrador' || $user->getRol() == 'Dirección') {
            // Si el usuario tiene el id del servicio igual a 1 o el rol es administrador, generamos todos los servicios
            echo '<option value="" selected disabled>Seleccionar un servicio...</option>';

            $getServicios = "SELECT id, servicio FROM servicios WHERE estado = 'Activo'";
            $stmtServicios = $pdo->query($getServicios);

            while ($row = $stmtServicios->fetch(PDO::FETCH_ASSOC)) {
              echo '<option value=' . $row['id'] . '>' . $row['servicio'] . '</option>';
            }
          } else {
            // Si no, generamos solo el servicio al que corresponde el usuario
            $servicioUsuario = $user->getServicio();
            $getServicioUsuario = "SELECT id, servicio FROM servicios WHERE id = ?";
            $stmtServicioUsuario = $pdo->prepare($getServicioUsuario);
            $stmtServicioUsuario->execute([$servicioUsuario]);
            $rowServicioUsuario = $stmtServicioUsuario->fetch(PDO::FETCH_ASSOC);

            echo '<option value="' . $rowServicioUsuario['id'] . '" selected>' . $rowServicioUsuario['servicio'] . '</option>';
          }
          ?>
        </select>

        <input type="text" name="searchInput" id="searchInput" style="width: 45%; height: 3vw;" placeholder="Buscar por DNI o nombre...">

        <script>
          $(document).ready(function() {
            $("#selectServicioFilter").select2();
            $('#selectServicioFilter').val(<?php echo $user->getServicio(); ?>).trigger('change');

            // Función para generar los botones de paginación
            function generarBotonesPaginacion(total_paginas) {
              var contenedorPaginacion = document.getElementById("contenedorPaginacion");

              contenedorPaginacion.innerHTML = "";

              // Generar botones de paginación
              for (var i = 1; i <= total_paginas; i++) {
                var botonPagina = document.createElement("button");
                botonPagina.textContent = i;
                botonPagina.setAttribute("class", "btn-green paginationBtn");
                botonPagina.setAttribute("data-pagina", i);
                botonPagina.addEventListener("click", function() {
                  var pagina = this.getAttribute("data-pagina");
                  actualizarTabla(pagina);
                });
                contenedorPaginacion.appendChild(botonPagina);
              }
            }

            // Función para actualizar la tabla con los resultados filtrados
            function actualizarTabla(pagina, searchTerm, selectServicioFilter) {
              // Ocultar la tabla mientras se cargan los nuevos resultados
              $("#tablaPersonal").hide();
              $(".lds-dual-ring").show(); // Mostrar el elemento de carga

              // Realizar la solicitud AJAX al controlador PHP para actualizar la tabla
              $.ajax({
                url: "controllers/buscar_personal.php",
                type: "GET",
                dataType: "html",
                data: {
                  pagina: pagina,
                  searchTerm: searchTerm,
                  selectServicioFilter: selectServicioFilter
                },
                success: function(response) {
                  // Actualizar la tabla con los nuevos resultados
                  $("#tablaPersonal").html(response);
                  // Mostrar la tabla después de cargar los nuevos resultados
                  $("#tablaPersonal").show();
                  $(".lds-dual-ring").hide(); // Ocultar el elemento de carga


                  // Generar botones de paginación
                  generarBotonesPaginacion(response.total_paginas);
                },
                error: function(xhr, status, error) {
                  console.log("Error al realizar la solicitud: " + error);
                }
              });
            }

            // Evento change del select para actualizar la tabla al cambiar el servicio
            $("#selectServicioFilter").on("change", function() {
              var selectServicioFilterValue = $(this).val(); // Obtener el valor seleccionado del select2
              actualizarTabla(1, $("#searchInput").val(), selectServicioFilterValue); // Llamar a actualizarTabla con el nuevo valor
            });

            // Cargar la tabla con los resultados iniciales
            actualizarTabla(1, $("#searchInput").val(), $("#selectServicioFilter").val());

            // Función para realizar la búsqueda en tiempo real con retardo
            var timeout = null;
            $("#searchInput").on("input", function() {
              clearTimeout(timeout); // Limpiar el temporizador existente si hay alguno
              // Configurar un nuevo temporizador para retrasar la búsqueda
              timeout = setTimeout(function() {
                // Obtener el valor del campo de búsqueda
                var searchTerm = $("#searchInput").val();

                // Obtener el valor seleccionado del select2
                var selectServicioFilterValue = $("#selectServicioFilter").val();

                // Llamar a la función actualizarTabla para enviar la solicitud al servidor
                actualizarTabla(1, searchTerm, selectServicioFilterValue);
              }, 500); // Retardo de 500 milisegundos (0.5 segundos)
            });

            // Función para cambiar de página al hacer clic en los botones de paginación
            function cambiarPagina(pagina) {
              // Obtener el valor del campo de búsqueda
              var searchTerm = $("#searchInput").val();

              // Obtener el valor seleccionado del select2
              var selectServicioFilter = $("#selectServicioFilter").val();

              // Llamar a la función actualizarTabla para enviar la solicitud al servidor con la nueva página
              actualizarTabla(pagina, searchTerm, selectServicioFilter);
            }

            // Código JavaScript para la paginación
            $("#contenedorPaginacion").on("click", ".paginationBtn", function() {
              var pagina = $(this).data("pagina");
              var searchTerm = $("#searchInput").val();
              var selectServicioFilter = $("#selectServicioFilter").val();
              actualizarTabla(pagina, searchTerm, selectServicioFilter);
            });
          });
        </script>
      </div>
    </div>

    <div id="tablaPersonal"></div>
    <div class="lds-dual-ring" style="transform: translate(36vw, 0);"></div>
    <div id="contenedorPaginacion"></div>

    <script>
      function cambiarPaginar(pagina) {
        cambiarPagina(pagina);
      }

      // Función para cambiar de página al hacer clic en los botones de paginación
      function cambiarPagina(pagina) {
        // Obtener el valor del campo de búsqueda
        var searchTerm = $("#searchInput").val();

        // Obtener el valor seleccionado del select2
        var selectServicioFilter = $("#selectServicioFilter").val();

        // Llamar a la función actualizarTabla para enviar la solicitud al servidor con la nueva página
        actualizarTabla(pagina, searchTerm, selectServicioFilter);
      }

      // Función para actualizar la tabla con los resultados filtrados
      function actualizarTabla(pagina, searchTerm, selectServicioFilter) {
        // Ocultar la tabla mientras se cargan los nuevos resultados
        $("#tablaPersonal").hide();
        $(".lds-dual-ring").show(); // Mostrar el elemento de carga

        // Realizar la solicitud AJAX al controlador PHP para actualizar la tabla
        $.ajax({
          url: "controllers/buscar_personal.php",
          type: "GET",
          dataType: "html",
          data: {
            pagina: pagina,
            searchTerm: searchTerm,
            selectServicioFilter: selectServicioFilter
          },
          success: function(response) {
            // Actualizar la tabla con los nuevos resultados
            $("#tablaPersonal").html(response);
            // Mostrar la tabla después de cargar los nuevos resultados
            $("#tablaPersonal").show();
            $(".lds-dual-ring").hide(); // Ocultar el elemento de carga


            // Generar botones de paginación
            generarBotonesPaginacion(response.total_paginas);
          },
          error: function(xhr, status, error) {
            console.log("Error al realizar la solicitud: " + error);
          }
        });
      }

      // Función para generar los botones de paginación
      function generarBotonesPaginacion(total_paginas) {
              var contenedorPaginacion = document.getElementById("contenedorPaginacion");

              contenedorPaginacion.innerHTML = "";

              // Generar botones de paginación
              for (var i = 1; i <= total_paginas; i++) {
                var botonPagina = document.createElement("button");
                botonPagina.textContent = i;
                botonPagina.setAttribute("class", "btn-green paginationBtn");
                botonPagina.setAttribute("data-pagina", i);
                botonPagina.addEventListener("click", function() {
                  var pagina = this.getAttribute("data-pagina");
                  actualizarTabla(pagina);
                });
                contenedorPaginacion.appendChild(botonPagina);
              }
            }
    </script>


  </div>
</div>

<script src="/SGH/public/layouts/modules/personalPanel/js/personal.js"></script>
<?php require_once '../../base/footer.php'; ?>