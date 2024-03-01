<?php

require_once '../../../../app/db/db.php';
require_once '../../../../app/db/user_session.php';
require_once '../../../../app/db/user.php';
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
              <option value="" disabled selected>Seleccione rol...</option>
              <option value="Administrador">Administrador</option>
              <option value="Dirección">Dirección</option>
              <option value="Deposito">Deposito</option>
              <option value="Mantenimiento">Mantenimiento</option>
              <option value="Patrimoniales">Patrimoniales</option>
              <option value="Informatica">Informatica</option>
              <option value="Jefe de servicio">Jefe de servicio</option>
              <option value="Docencia e investigación">Docencia e investigación</option>
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
              <option value="Administrador">Administrador</option>
              <option value="Dirección">Dirección</option>
              <option value="Deposito">Deposito</option>
              <option value="Mantenimiento">Mantenimiento</option>
              <option value="Patrimoniales">Patrimoniales</option>
              <option value="Informatica">Informatica</option>
              <option value="Jefe de servicio">Jefe de servicio</option>
              <option value="Docencia e investigación">Docencia e investigación</option>
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


    <div>
      <button class="btn-green" onclick="back.style.display = 'flex'; newPersonal.style.display = 'flex';"><b><i class="fa-solid fa-plus"></i> Declarar nuevo personal</b></button>
    </div>

    <script>
      function updateSistem(id, sistema, estado) {
        // Construir la URL con los parámetros
        var url = 'controllers/actualizar_sistema.php?id=' + encodeURIComponent(id) + '&sistema=' + encodeURIComponent(sistema) + '&estado=' + encodeURIComponent(estado);

        // Redireccionar a la URL
        window.location.href = url;
      }

      function updatePassword(id, dni) {
        // Construir la URL con los parámetros
        var url = 'controllers/actualizar_contrasena.php?id=' + encodeURIComponent(id) + '&dni=' + encodeURIComponent(dni);

        // Redireccionar a la URL
        window.location.href = url;
      }
    </script>


    <?php
    // Establece la cantidad de resultados por página
    $resultados_por_pagina = 20;

    // Obtiene el número de página actual
    $pagina_actual = isset($_GET['pagina']) ? $_GET['pagina'] : 1;

    // Calcula el desplazamiento
    $desplazamiento = ($pagina_actual - 1) * $resultados_por_pagina;

    // Realiza la consulta a la tabla servicios con el límite y el desplazamiento
    $getPersonal = "SELECT * FROM personal WHERE estado != 'Eliminado' LIMIT $resultados_por_pagina OFFSET $desplazamiento";
    $stmtPersonal = $pdo->query($getPersonal);

    // Itera sobre los resultados y muestra las filas en la tabla
    while ($row = $stmtPersonal->fetch(PDO::FETCH_ASSOC)) {

    ?>
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
            <th class="table-middle table-center">Sistemas</th>
            <th class="table-middle table-center">Rol</th>
            <th class="table-middle table-center">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php

          // Realiza la consulta a la tabla servicios
          $getPersonal = "SELECT * FROM personal WHERE estado != 'Eliminado'";
          $stmtPersonal = $pdo->query($getPersonal);

          // Itera sobre los resultados y muestra las filas en la tabla
          while ($row = $stmtPersonal->fetch(PDO::FETCH_ASSOC)) {
            echo '<tr>';
            echo '<td class="table-center table-middle">' . $row['id'] . '</td>';

            $fechaHoy = date("Y-m-d");

            $stmtLicencias = $pdo->prepare("SELECT tipo_licencia, fecha_desde, fecha_hasta FROM licencias WHERE dni = ? AND fecha_desde <= ? AND fecha_hasta >= ?");
            $stmtLicencias->execute([$row['dni'], $fechaHoy, $fechaHoy]);
            $licencias = $stmtLicencias->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($licencias)) {
              echo '<td class="table-middle">';
              echo '<div style="display: flex; flex-direction: row; align-items: center;"><div>' . $row['apellido'] . ' ' . $row['nombre'] . '</div>';

              echo '<button class="avisoWarButton" onclick="avisoLicencia(' . $row['id'] . ')">
            <i class="fa-solid fa-triangle-exclamation"></i>
          </button>';

              foreach ($licencias as $licencia) {
                // Formatear las fechas
                $fecha_desde = date("d/m/Y", strtotime($licencia['fecha_desde']));
                $fecha_hasta = date("d/m/Y", strtotime($licencia['fecha_hasta']));

                echo '<div id="aviso-' . $row['id'] . '" class="avisoWar" style="position: relative">
            
            <div class="aviso"><h4>El agente se encuentra de licencia.</h4></br>
                <b>Tipo de licencia:</b> ' . $licencia['tipo_licencia'] . '. </br>
                <div style="margin-top: .3vw;"><b>Desde:</b> ' . $fecha_desde . '</br><b>Hasta:</b> ' . $fecha_hasta . '.</div>
            </div>
            
        </div>';
              }

              echo '</div></td>';
            } else {
              echo '<td class="table-middle">' . $row['apellido'] . ' ' . $row['nombre'] . '</td>';
            }

            echo '<td class="table-middle table-center">' . $row['dni'] . '</td>';

            if ($row['servicio_id'] != "0") {
              // Realiza una consulta para obtener el nombre y apellido del jefe de servicio
              $getservicioQuery = "SELECT servicio FROM servicios WHERE id = ?";
              $getservicioStmt = $pdo->prepare($getservicioQuery);
              $getservicioStmt->execute([$row['servicio_id']]);
              $servicioInfo = $getservicioStmt->fetch(PDO::FETCH_ASSOC);
              // Muestra el nombre y apellido del jefe de servicio
              if ($servicioInfo) {
                echo '<td class="table-middle">' . $servicioInfo['servicio'] . '</td>';
              } else {
                echo '<div>No se encontró la información del servicio</div>';
              }
            } else {
              echo '<td class="table-middle"> No hay servicio asignado';
            }
            echo '</td>';

            echo '<td class="table-middle"> ' . $row['especialidad'] . '</td>';

            echo '<td class="table-middle"> M.N: ' . $row['mn'] . ' </br> M.P: ' . $row['mp'] . '</td>';

            echo '<td class="table-middle"> ' . $row['cargo'] . '</td>';

            echo '<td class="table-middle"><div style="display: flex; flex-direction: column; align-items: stretch;">';


            // Suponiendo que $sistemas es el array que contiene los datos de la base de datos
            // Convertir la cadena JSON en un array PHP
            $sistemas_array = json_decode($row['sistemas'], true);

            // Verificar si la conversión fue exitosa
            if ($sistemas_array !== null) {
              // Iterar sobre el array de sistemas
              foreach ($sistemas_array as $sistema) {
                // Acceder a cada propiedad del objeto sistema
                $nombre_sistema = $sistema['sistema'];
                $activo = $sistema['activo'];

                // Determinar la clase del botón en función del estado activo
                $claseBoton = ($activo == 'si') ? 'btn-green' : 'btn-red';

                // Determinar el icono del botón en función del nombre del sistema
                switch ($nombre_sistema) {
                  case 'Deposito':
                    $icono = 'fa-box';
                    $variables = "" . $row['id'] . ", 'Deposito', '" . $activo . "'";
                    break;
                  case 'Mantenimiento':
                    $icono = 'fa-screwdriver-wrench';
                    $variables = "" . $row['id'] . ", 'Mantenimiento', '" . $activo . "'";
                    break;
                  case 'Informatica':
                    $icono = 'fa-computer';
                    $variables = "" . $row['id'] . ", 'Informatica', '" . $activo . "'";
                    break;
                  default:
                    $icono = 'fa-question';
                    break;
                }

                // Imprimir el botón con la clase y el icono correspondientes
                echo '<button class="' . $claseBoton . '" onclick="updateSistem(' . $variables . ')"><i class="fa-solid ' . $icono . '"></i> ' . $nombre_sistema . '</button>';
              }
            } else {
              // Manejar el caso en el que la conversión de JSON falla
              echo 'Error al decodificar la cadena JSON.';
            }


            // Botón "Generar contraseña" fuera del bucle foreach
            echo '<button class="btn-yellow" onclick="updatePassword(' . $row['id'] . ', \'' . $row['dni'] . '\')" style="width: max-content"><i class="fa-solid fa-key"></i> Generar contraseña</button>';


            echo '</div></td>';


            echo '<td class="table-middle"> ' . $row['rol'] . '</td>';

            echo '<td class="table-middle table-center">
          
                <div class="contenedor-de-botones">

                  <button class="btn-green" title="Abrir menu de acciones"  onclick="menuPersona(' . $row['id'] . ')"><i class="fa-solid fa-hand-pointer"></i></button>
                
                  <div class="buttons-div" id="menu-' . $row['id'] . '">
                
                    <button class="btn-green" title="Editar" onclick="setDatos(\'' . $row['id'] . '\', \'' . $row['apellido'] . '\', \'' . $row['nombre'] . '\', \'' . $row['dni'] . '\', \'' . $row['servicio_id'] . '\', \'' . $row['cargo'] . '\', \'' . $row['especialidad'] . '\', \'' . $row['mn'] . '\', \'' . $row['mp'] . '\', \'' . $row['rol'] . '\')"><i class="fa-solid fa-pen"></i> Editar</button>
                    
                    <button class="btn-green" title="Pase" onclick="setDatosPase(\'' . $row['id'] . '\', \'' . $row['apellido'] . '\', \'' . $row['nombre'] . '\', \'' . $row['dni'] . '\')"><i class="fa-solid fa-right-from-bracket"></i> Pase</button>
                    
                    <button class="btn-green" title="Licencias" onclick="setLicencia(\'' . $row['apellido'] . '\', \'' . $row['nombre'] . '\', \'' . $row['dni'] . '\')"><i class="fa-solid fa-person-walking-luggage"></i> Licencias</button>
                    
                    <button class="btn-yellow" title="Jubilar" onclick="setDatosJubilar(\'' . $row['apellido'] . '\', \'' . $row['nombre'] . '\', \'' . $row['dni'] . '\')"><i class="fa-solid fa-person-walking-with-cane"></i> Jubilar</button>
                    
                    <button class="btn-yellow" title="Fin contrato" onclick="setDatosFinContrato(\'' . $row['apellido'] . '\', \'' . $row['nombre'] . '\', \'' . $row['dni'] . '\')"><i class="fas fa-calendar-times"></i> Fin contrato</button>
                  
                    </div>
                </div>
              </td>';

            echo '</tr>';
          }
          ?>

        </tbody>
      </table>

    <?php
    }


    // Calcula la cantidad total de páginas
    $total_registros = $pdo->query("SELECT COUNT(*) FROM personal WHERE estado != 'Eliminado'")->fetchColumn();
    $total_paginas = ceil($total_registros / $resultados_por_pagina);

    // Muestra la paginación
    echo '<div class="pagination" style="margin-top: 1vw; display: flex; flex-direction: row; flex-wrap: wrap;">';
    for ($i = 1; $i <= $total_paginas; $i++) {
      echo '<a class="btn-green" style="display: flex; width: max-content; height: 2.3vw; text-align: center; justify-content: center; text-decoration: none;" href="?pagina=' . $i . '">Página ' . $i . '</a>';
    }
    echo '</div>';
    ?>
  </div>
</div>

<script src="/SGH/public/layouts/modules/personalPanel/js/personal.js"></script>
<?php require_once '../../base/footer.php'; ?>