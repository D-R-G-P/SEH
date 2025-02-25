<?php

require_once '../../../../app/db/db.php';
require_once '../../../../app/db/user_session.php';
require_once '../../../../app/db/user.php';
require_once 'controllers/search_user.php';
require_once '../../../config.php';

$user = new User();
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);

requireRole(['administrador', 'direccion', 'hsi']);

$title = "Solicitudes de HSI";

$db = new DB();
$pdo = $db->connect();

$servicioFilter = $user->getServicio();

// Obtener el parámetro 'selectServicioFilter' de la URL, si no está se establece en null
$sel = isset($_GET['selectServicioFilter']) ? $_GET['selectServicioFilter'] : null;

// Si el parámetro 'selectServicioFilter' no coincide con el servicio del usuario
// y el usuario no tiene rol de "Administrador" ni "Dirección"
if (($sel != $user->getServicio()) && (hasAccess(['administrador', 'direccion'])) || !$sel) {

  // Asignar el servicio del usuario a 'selectServicioFilter' si no es válido
  $selectServicioFilter = $user->getServicio();

  // Redirigir con el nuevo parámetro selectServicioFilter
  $url = "hsi.php?pagina=$pagina";
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
<link rel="stylesheet" href="/SGH/public/layouts/modules/hsiPanel/css/hsi.css">

<script>
  servicioUser = <?= $servicioFilter ?>
</script>

<div class="content">

  <div class="modulo" style="text-align: center;">
    <h3 style="margin-bottom: .5vw;">Sistema de gestión de HSI</h3>
    <p>Este sistema está oreintado a la gestion y </br> solicitud de los usuarios de HSI del personal a cargo.</p>
  </div>

  <?php
  // Función para obtener los permisos por DNI
  $dni = $user->getDni();

  $getInstAdm = "SELECT dni, id_rol FROM usuarios_roles_hsi WHERE dni = :dni";
  $stmtInstAdm = $pdo->prepare($getInstAdm);
  $stmtInstAdm->bindParam(':dni', $dni, PDO::PARAM_STR);
  $stmtInstAdm->execute();

  $tienePermisoAdmin = false; // Variable para saber si el usuario tiene el permiso de "Administrador institucional"
  while ($rowInstAdm = $stmtInstAdm->fetch(PDO::FETCH_ASSOC)) {
    if ($rowInstAdm['id_rol'] === 1) {
      $tienePermisoAdmin = true;
      break;
    }
  }

  // Si el usuario tiene el permiso de "Administrador institucional", mostrar el botón
  if ($tienePermisoAdmin) {
    echo '<div class="admInst" style="position: relative; top: -6vw; left: -29vw;">';
    echo '<a class="btn-tematico" style="text-decoration: none;" href="hsiAdmin.php"><i class="fa-solid fa-toolbox"></i> <b>Acceder a panel de administrador</b></a>';
    echo '</div>';
  }
  ?>


  <div class="back" id="back">

    <div class="divBackForm" id="neUser" style="display: none;">
      <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
        <button class="btn-red"
          onclick="back.style.display = 'none'; neUser.style.display = 'none'; newUserForm.reset(); $('#dniSelect').val(null).trigger('change'); $('#servicioSelect').val(null).trigger('change'); $('#permisosSelect').val(null).trigger('change');"
          style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
      </div>
      <h3>Solicitar nuevo usuario</h3>

      <form action="/SGH/public/layouts/modules/hsiPanel/controllers/newUser.php" method="post" class="backForm"
        id="newUserForm">

        <div>
          <label for="dniSelect">DNI</label>
          <select name="dni" id="dniSelect" required style="width: 95%;">
            <option value="" selected disabled>Seleccionar agente...</option>
            <?php

            if (hasAccess(['administrador', 'direccion'])) {
              // Realiza la consulta a la tabla personal, excluyendo los dni que están en la tabla hsi
              $getPersonal = "SELECT apellido, nombre, dni FROM personal WHERE CONVERT(dni USING utf8mb4) COLLATE utf8mb4_spanish_ci NOT IN (SELECT CONVERT(dni USING utf8mb4) COLLATE utf8mb4_spanish_ci FROM hsi)";
              $stmt = $pdo->query($getPersonal);

              // Itera sobre los resultados y muestra las filas en la tabla
              while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '<option value=' . $row['dni'] . '>' . $row['apellido'] . ' ' . $row['nombre'] . ' - ' . $row['dni'] . '</option>';
              }
            } else {
              // Realiza la consulta a la tabla personal, excluyendo los dni que están en la tabla hsi y filtrando por servicio
              $getPersonal = "SELECT apellido, nombre, dni FROM personal WHERE servicio_id = :servicioFilter AND CONVERT(dni USING utf8mb4) COLLATE utf8mb4_spanish_ci NOT IN (SELECT CONVERT(dni USING utf8mb4) COLLATE utf8mb4_spanish_ci FROM hsi)";
              $stmt = $pdo->prepare($getPersonal);
              $stmt->execute(['servicioFilter' => $servicioFilter]);

              // Itera sobre los resultados y muestra las filas en la tabla
              while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '<option value=' . $row['dni'] . '>' . $row['apellido'] . ' ' . $row['nombre'] . ' - ' . $row['dni'] . '</option>';
              }
            }

            ?>


          </select>
        </div>

        <div>
          <label for="servicioSelect">Servicio</label>
          <select name="servicio" id="servicioSelect" style="width: 95%;">
            <option value="" selected disabled>Seleccionar un servicio...</option>
            <?php

            if (hasAccess(['administrador', 'direccion'])) {
              // Realiza la consulta a la tabla servicios
              $getServicio = "SELECT id, servicio FROM servicios";
              $stmt = $pdo->query($getServicio);

              // Itera sobre los resultados y muestra las filas en la tabla
              while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '<option value=' . $row['id'] . '>' . $row['servicio'] . '</option>';
              }
            } else {
              // Realiza la consulta a la tabla servicios
              $getServicio = "SELECT id, servicio FROM servicios WHERE id = $servicioFilter";
              $stmt = $pdo->query($getServicio);

              // Itera sobre los resultados y muestra las filas en la tabla
              while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '<option value=' . $row['id'] . '>' . $row['servicio'] . '</option>';
              }
            }

            ?>
          </select>
        </div>
        <div>
          <label for="email">E-mail</label>
          <input type="email" name="email" id="email" required>
        </div>
        <div>
          <label for="phone">Telefono (WhatsApp)</label>
          <input type="tel" name="phone" id="phone" required>
        </div>
        <div>
          <label for="permisosSelect">Permisos</label>
          <select name="permisos[]" id="permisosSelect" style="width: 95%;" multiple="multiple"
            placeholder="Seleccionar permiso(s)" required>

            <?php

            if ($tienePermisoAdmin === true) {
              $getRoles = "SELECT * FROM roles_hsi WHERE estado = 'activo'";
            } else {
              $getRoles = "SELECT * FROM roles_hsi WHERE estado = 'activo' AND id != 1";
            }
            $stmt = $pdo->query($getRoles);

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
              echo '<option value=' . $row['id'] . '>' . $row['rol'] . '</option>';
            }

            ?>

          </select>
        </div>

        <div style="display: flex; flex-direction: row; justify-content: center;">
          <button type="button" class="btn-green" onclick="confirmUsuario.style.display = 'flex'"><b><i
                class="fa-solid fa-plus"></i> Solicitar nuevo usuario</b></button>
        </div>

        <div id="confirmUsuario" class="divBackForm" style="display: none;">
          <h4>Antes de solicitar el usuario</h4>
          <p style="margin-top: 1vw;">Es necesario que descargue los anexos correspondientes</p>

          <div class="pdfs"
            style="display: flex; flex-direction: row; align-content: center; flex-wrap: wrap; justify-content: space-evenly; margin: 2vw 0">
            <a href="/SGH/public/layouts/modules/hsiPanel/docs/Anexo 1.pdf" style="color: #000;" target="_blank"><i
                class="fa-solid fa-file-pdf"></i> Anexo I</a>
            <a href="/SGH/public/layouts/modules/hsiPanel/docs/Anexo 2.pdf" style="color: #000;" target="_blank"><i
                class="fa-solid fa-file-pdf"></i> Anexo II</a>
          </div>

          <div style="display: flex; flex-direction: row; justify-content: center;">
            <button class="btn-red" type="button" onclick="confirmUsuario.style.display = 'none';"><i
                class="fa-solid fa-xmark"></i> Cancelar</button>

            <button class="btn-green" type="submit"><i class="fa-solid fa-plus"></i>
              Solicitar usuario</button>
          </div>
        </div>
      </form>
    </div>

    <div class="divBackForm" id="addDocsDiv" style="display: none;">
      <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
        <button class="btn-red"
          onclick="back.style.display = 'none'; addDocsDiv.style.display = 'none'; addDocsForm.reset();"
          style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
      </div>
      <h3>Agregar documentación</h3>
      <p style="color: red;">* documentos obligatorios en formato pdf</p>

      <form action="/SGH/public/layouts/modules/hsiPanel/controllers/docsUpload.php" class="backForm" method="post"
        id="addDocsForm" enctype="multipart/form-data">
        <input type="hidden" name="docsDniHidden" id="docsDniHidden">
        <div style="margin-top: 6vw;">
          <label for="docsDni">Documento Nacional de Identidad <br> (Frente y dorso en un archivo) <b
              style="color: red;">*</b></label>
          <input type="file" name="docsDni" id="docsDni" accept="application/pdf">
        </div>
        <div>
          <label for="docsMatricula">Matricula Profesional <br> (frente y dorso en un archivo) si corresponde</label>
          <input type="file" name="docsMatricula" id="docsMatricula" accept="application/pdf">
        </div>
        <div>
          <label for="docsAnexoI">Solicitud de alta de usuario para HSI <br> (ANEXO I) <b
              style="color: red;">*</b></label>
          <input type="file" name="docsAnexoI" id="docsAnexoI" accept="application/pdf">
        </div>
        <div>
          <label for="docsAnexoII">Declaración Jurada - Convenio de confidencialidad usuarios HSI <br> (ANEXO II) <b
              style="color: red;">*</b></label>
          <input type="file" name="docsAnexoII" id="docsAnexoII" accept="application/pdf">
        </div>
        <div>
          <label for="docsPrescriptor">Declaración Jurada - Usuario prescriptor</label>
          <input type="file" name="docsPrescriptor" id="docsPrescriptor" accept="application/pdf">
        </div>

        <button class="btn-green" type="submit"><i class="fa-solid fa-file-arrow-up"></i> Subir archivos</button>
      </form>
    </div>


    <div class="divBackForm infoModule" id="infoModule" style="display: none;">
      <div class="close"
        style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw; margin-bottom: -3.5vw;">
        <button class="btn-red"
          onclick="back.style.display = 'none'; infoModule.style.display = 'none'; infoForm.reset();"
          style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
      </div>
      <h3>Información de usuario</h3>

      <div class="cuerpoInfo" id="infoUsuario">
      </div>
    </div>

    <div class="divBackForm" id="printModal" style="display: none;">
      <div class="close"
        style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw; margin-bottom: -3.5vw;">
        <button class="btn-red"
          onclick="back.style.display = 'none'; printModal.style.display = 'none'; printModalForm.reset();"
          style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
      </div>
      <h3>Impresión de informe</h3>

      <form action="controllers/print.php" method="post" class="backForm" id="printModalForm" target="_blank">

        <div>
          <label for="printServicio">Seleccionar servicio</label>
          <select name="printServicio" id="printServicio" class="select2">
            <?php

            if (hasAccess(['administrador', 'direccion'])) {
              // Realiza la consulta a la tabla servicios
              $getServicio = "SELECT id, servicio FROM servicios";
              $stmt = $pdo->query($getServicio);

              // Itera sobre los resultados y muestra las filas en la tabla
              while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '<option value=' . $row['id'] . '>' . $row['servicio'] . '</option>';
              }
            } else {
              // Realiza la consulta a la tabla servicios
              $getServicio = "SELECT id, servicio FROM servicios WHERE id = $servicioFilter";
              $stmt = $pdo->query($getServicio);

              // Itera sobre los resultados y muestra las filas en la tabla
              while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '<option value=' . $row['id'] . '>' . $row['servicio'] . '</option>';
              }
            }

            ?>
          </select>
        </div>

        <div style="display: flex; flex-direction: row; justify-content: flex-start; align-items: center;">
          <input type="checkbox" name="pendientes" id="pendientes" style="width: auto; margin-right: 1vw;">
          <label for="pendientes">¿Incluir pendientes?</label>
        </div>

        <div style="display: flex; flex-direction: row; justify-content: flex-start; align-items: center;">
          <input type="checkbox" name="activos" id="activos" style="width: auto; margin-right: 1vw;">
          <label for="activos">¿Incluir activos?</label>
        </div>

        <div style="display: flex; flex-direction: row; justify-content: flex-start; align-items: center;">
          <input type="checkbox" name="deshabilitados" id="deshabilitados" style="width: auto; margin-right: 1vw;">
          <label for="deshabilitados">¿Incluir deshabilitados?</label>
        </div>

        <button class="btn-green"><i class="fa-solid fa-print"></i> Imprimir informe</button>

      </form>
    </div>

  </div>


  <div class="modulo">

    <div class="inlineDiv">
      <button class="btn-green" onclick="newUser()"><b><i class="fa-solid fa-plus"></i> Solicitar nuevo
          usuario</b></button>

      <button class="btn-tematico" onclick="printInforme()"><i class="fa-solid fa-print"></i> <b>Imprimir informe de
          usuarios</b></button>
    </div>


    <div class="tables">

      <div class="news">

        <h4>Notificaciones</h4>
        <table id="news">

          <thead>
            <tr>

              <th class="table-middle table-center">ID</th>
              <th class="table-middle">Apellido</th>
              <th class="table-middle">Nombre</th>
              <th class="table-middle">DNI</th>
              <th class="table-middle">Servicio</th>
              <th class="table-middle">Permisos</th>
              <th class="table-middle">Observaciones</th>
              <th class="table-middle">Acciones</th>

            </tr>
          </thead>
          <tbody>

            <?php
            $queryNews = "SELECT * FROM hsi WHERE servicio = :servicioFilter AND new = 'si'";
            $stmtNews = $pdo->prepare($queryNews);
            $stmtNews->bindParam(':servicioFilter', $servicioFilter, PDO::PARAM_INT);
            $stmtNews->execute();

            if ($stmtNews->rowCount() == 0) {
              // Si no hay resultados con estado 'news'
              echo '<tr><td colspan="8">No hay notificaciones pendientes</td></tr>';
            } else {
              while ($rowNews = $stmtNews->fetch(PDO::FETCH_ASSOC)) {
                echo '<tr>';

                echo '<td class="table-center table-middle">' . $rowNews['id'] . '</td>';

                $stmtDniNews = $pdo->prepare("SELECT nombre, apellido FROM personal WHERE dni = ?");
                $stmtDniNews->execute([$rowNews['dni']]);
                $rowDatNews = $stmtDniNews->fetch(PDO::FETCH_ASSOC);

                if ($rowDatNews) {
                  echo '<td class="table-center table-middle">' . $rowDatNews['apellido'] . '</td>';
                  echo '<td class="table-center table-middle">' . $rowDatNews['nombre'] . '</td>';
                } else {
                  echo '<td colspan="2">Error al obtener los datos</td>';
                }

                echo '<td class="table-center table-middle">' . $rowNews['dni'] . '</td>';

                $stmtServicioNews = $pdo->prepare("SELECT servicio FROM servicios WHERE id = ?");
                $stmtServicioNews->execute([$rowNews['servicio']]);
                $rowServicioNews = $stmtServicioNews->fetch(PDO::FETCH_ASSOC);

                if ($rowServicioNews) {
                  echo '<td class="table-center table-middle">' . $rowServicioNews['servicio'] . '</td>';
                } else {
                  echo '<td>Error al obtener los datos</td>';
                }

                echo '<td class="table-left table-middle">';
                $getRolesAct = "SELECT u.id, u.id_rol, r.rol AS nombre_rol FROM usuarios_roles_hsi u JOIN roles_hsi r ON u.id_rol = r.id WHERE u.dni = :dni";

                $stmtRolesAct = $pdo->prepare($getRolesAct);
                $stmtRolesAct->execute([':dni' => $rowNews['dni']]);

                while ($row = $stmtRolesAct->fetch(PDO::FETCH_ASSOC)) {
                  echo '<div style="text-wrap-mode: nowrap;"><i class="fa-solid fa-chevron-right"></i>' . htmlspecialchars($row['nombre_rol']) . '</div>';
                }

                echo '</td>';

                echo '<td class="table-middle">' . $rowNews['observaciones'] . '</td>';

                echo '<td class="table-center table-middle">
                <button onclick="checkNews(' . $rowNews['id'] . ')" class="btn-green" title="Marcar como visto"><i class="fa-solid fa-check"></i></button>
            </td>';

                echo '</tr>';
              }
            }
            ?>



          </tbody>

        </table>

      </div>

      <div class="working">

        <h4>Pendientes</h4>
        <table id="working">

          <thead>
            <tr>

              <th>ID</th>
              <th>Apellido</th>
              <th>Nombre</th>
              <th>DNI</th>
              <th>Servicio</th>
              <th>Permisos</th>
              <th>Documentos</th>
              <th>Acciones</th>

            </tr>
          </thead>
          <tbody>
            <?php
            $queryWorking = "SELECT * FROM hsi WHERE servicio = :servicioFilter AND estado = 'working'";
            $stmtWorking = $pdo->prepare($queryWorking);
            $stmtWorking->bindParam(':servicioFilter', $servicioFilter, PDO::PARAM_INT);
            $stmtWorking->execute();

            if ($stmtWorking->rowCount() == 0) {
              // Si no hay resultados con estado 'news'
              echo '<tr><td colspan="8">No hay usuarios pendientes</td></tr>';
            } else {
              while ($rowWorking = $stmtWorking->fetch(PDO::FETCH_ASSOC)) {
                echo '<tr>';

                echo '<td class="table-center table-middle">' . $rowWorking['id'] . '</td>';

                $stmtDniWorking = $pdo->prepare("SELECT nombre, apellido FROM personal WHERE dni = ?");
                $stmtDniWorking->execute([$rowWorking['dni']]);
                $rowDatWorking = $stmtDniWorking->fetch(PDO::FETCH_ASSOC);

                if ($rowDatWorking) {
                  echo '<td class="table-center table-middle">' . $rowDatWorking['apellido'] . '</td>';
                  echo '<td class="table-center table-middle">' . $rowDatWorking['nombre'] . '</td>';
                } else {
                  echo '<td colspan="2">Error al obtener los datos</td>';
                }

                echo '<td class="table-center table-middle">' . $rowWorking['dni'] . '</td>';

                $stmtServicioWorking = $pdo->prepare("SELECT servicio FROM servicios WHERE id = ?");
                $stmtServicioWorking->execute([$rowWorking['servicio']]);
                $rowServicioWorking = $stmtServicioWorking->fetch(PDO::FETCH_ASSOC);

                if ($rowServicioWorking) {
                  echo '<td class="table-center table-middle">' . $rowServicioWorking['servicio'] . '</td>';
                } else {
                  echo '<td>Error al obtener los datos</td>';
                }

                echo '<td class="table-left table-middle">';
                $getRolesAct = "SELECT u.id, u.id_rol, r.rol AS nombre_rol FROM usuarios_roles_hsi u JOIN roles_hsi r ON u.id_rol = r.id WHERE u.dni = :dni";

                $stmtRolesAct = $pdo->prepare($getRolesAct);
                $stmtRolesAct->execute([':dni' => $rowWorking['dni']]); // Pasar el parámetro :dni
            
                while ($row = $stmtRolesAct->fetch(PDO::FETCH_ASSOC)) {
                  echo '<div style="text-wrap-mode: nowrap;"><i class="fa-solid fa-chevron-right"></i>' . htmlspecialchars($row['nombre_rol']) . '</div>';
                }

                echo '</td>';



                echo '<td class="table-middle table-left" style="width: max-content;"><div style="display: grid; grid-template-columns: auto min-content; align-items: center;">';
                $documentos_array = json_decode($rowWorking['documentos'], true);

                if ($documentos_array !== null) {
                  foreach ($documentos_array as $documentoWorking) {
                    $documento = $documentoWorking['documento'];
                    $activo = $documentoWorking['activo'];

                    // Utilizar un switch para cambiar el nombre del documento en cada caso
                    switch ($documento) {
                      case 'Copia de DNI':
                        $documento_nombre = 'D.N.I';
                        break;
                      case 'Copia de matrícula profesional':
                        $documento_nombre = 'Matricula';
                        break;
                      case 'Solicitud de alta de usuario para HSI (ANEXO I)':
                        $documento_nombre = 'ANEXO I';
                        break;
                      case 'Declaración Jurada - Convenio de confidecialidad usuarios HSI (ANEXO II)':
                        $documento_nombre = 'ANEXO II';
                        break;
                      case 'Declaración Jurada - Usuario prescriptor':
                        $documento_nombre = 'Prescriptor';
                        break;
                      default:
                        $documento_nombre = $documento; // Si no hay una coincidencia, mantener el nombre original
                        break;
                    }

                    switch ($activo) {
                      case 'no':
                        $simbolo = '<i class="fa-solid fa-xmark"></i>';
                        break;
                      case 'pendiente':
                        $simbolo = '<i class="fa-regular fa-clock"></i>';
                        break;
                      case 'verificado':
                        $simbolo = '<i class="fa-solid fa-check"></i>';
                        break;
                      default:
                        $simbolo = '<i class="fa-solid fa-question"></i>';
                        break;
                    }

                    // Imprimir el nombre del documento y el símbolo en las dos columnas del grid
                    echo '<div>' . $documento_nombre . ':</div>';
                    echo '<div>' . $simbolo . '</div>';
                  }
                }
                echo '</div></td>';


                echo '<td class="table-center table-middle"><button title="Subir documentación" class="btn-green" onclick="addDocs(\'' . $rowWorking['dni'] . '\')" style="width: 3vw; height: 3vw;"><i style="font-size: 2vw;" class="fa-solid fa-file-arrow-up"></i></button></td>';


                echo '</tr>';
              }
            }
            ?>

          </tbody>

        </table>

      </div>

      <div class="habilitado">
        <h4>Habilitados</h4>
        <div style="width: 100%;">

          <form action="hsi.php#habilitado" method="get" id="formFiltro" style="display: flex; flex-direction: row; flex-wrap: nowrap; align-items: center; overflow-y: hidden;">
            <input type="hidden" name="pagina"
              value="<?php echo isset($_GET['pagina']) ? htmlspecialchars($_GET['pagina']) : 1; ?>" id="pageInput">

            <div style="display: grid; grid-template-columns: repeat(2, 1fr); grid-template-rows: 1fr; grid-column-gap: 1vw; grid-row-gap: 0px; overflow-y: hidden;">
              <select name="selectServicioFilter" id="selectServicioFilter" class="select2" onchange="pageInput.value = 1; this.form.submit()" <?php if (!hasAccess(['administrador', 'direccion'])) {
                echo "disabled";
              } ?>>

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

              <input type="text" name="searchInput" id="searchInput" style="width: 100%;"
                placeholder="Buscar por DNI o nombre..."
                value="<?php echo isset($_GET['searchInput']) ? htmlspecialchars($_GET['searchInput']) : ''; ?>">

            </div>
            <button type="submit" class="btn-green"><i class="fa-solid fa-magnifying-glass"></i></button>
          </form>


        </div>




        <!-- Agregar la tabla HTML -->
        <table id="habilitado">
          <thead>
            <tr>
              <th>ID</th>
              <th>Apellido</th>
              <th>Nombre</th>
              <th>DNI</th>
              <th>Servicio</th>
              <th>Permisos</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($totalregistros >= 1): ?>
              <?php foreach ($registros as $reg): ?>
                <tr>
                  <!-- ID -->
                  <td class="table-center table-middle"><?= htmlspecialchars($reg['id']) ?></td>

                  <!-- Apellido -->
                  <td class="table-center table-middle"><?= htmlspecialchars($reg['apellido']) ?></td>

                  <!-- Nombre -->
                  <td class="table-center table-middle"><?= htmlspecialchars($reg['nombre']) ?></td>

                  <!-- DNI -->
                  <td class="table-center table-middle"><?= htmlspecialchars($reg['dni']) ?></td>

                  <!-- Servicio -->
                  <td class="table-center table-middle">
                    <?php
                    // Obtener el nombre del servicio con una consulta externa
                    $stmtServicio = $pdo->prepare("SELECT servicio FROM servicios WHERE id = ?");
                    $stmtServicio->execute([$reg['servicio']]);
                    $servicio = $stmtServicio->fetch(PDO::FETCH_ASSOC);
                    echo $servicio ? htmlspecialchars($servicio['servicio']) : "Error al obtener los datos";
                    ?>
                  </td>

                  <!-- Permisos -->
                  <td class="table-left table-middle">
                    <?php
                    // Obtener los roles asociados al usuario
                    $stmtRoles = $pdo->prepare("SELECT r.rol AS nombre_rol 
                                                    FROM usuarios_roles_hsi u 
                                                    JOIN roles_hsi r ON u.id_rol = r.id 
                                                    WHERE u.dni = :dni");
                    $stmtRoles->execute([':dni' => $reg['dni']]);
                    while ($rol = $stmtRoles->fetch(PDO::FETCH_ASSOC)) {
                      echo '<div><i class="fa-solid fa-chevron-right"></i> ' . htmlspecialchars($rol['nombre_rol']) . '</div>';
                    }
                    ?>
                  </td>

                  <!-- Acciones -->
                  <td class="table-center table-middle">
                    <button class="btn-green" onclick="loadInfo('<?= htmlspecialchars($reg['dni']) ?>')">
                      <i class="fa-solid fa-hand-pointer"></i>
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td class="table-center table-middle" colspan="7">No hay registros para mostrar.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>


        <!-- Agregar controles de paginación -->
        <div class="pagination">
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
                  <li class="page-item"><a class="page-link"
                      href="hsi.php?pagina=<?php echo $pagina - 1; ?>&selectServicioFilter=<?php echo isset($_GET['selectServicioFilter']) ? urlencode($_GET['selectServicioFilter']) : ''; ?>&searchInput=<?php echo isset($_GET['searchInput']) ? urlencode($_GET['searchInput']) : ''; ?>">&laquo;</a>
                  </li>
                <?php endif; ?>

                <!-- Página 1 -->
                <?php if ($inicio_pagina > 1): ?>
                  <li class="page-item"><a class="page-link"
                      href="hsi.php?pagina=1&selectServicioFilter=<?php echo isset($_GET['selectServicioFilter']) ? urlencode($_GET['selectServicioFilter']) : ''; ?>&searchInput=<?php echo isset($_GET['searchInput']) ? urlencode($_GET['searchInput']) : ''; ?>">1</a>
                  </li>
                  <li class="page-item disabled"><span class="page-link">...</span></li>
                <?php endif; ?>

                <!-- Páginas intermedias -->
                <?php for ($i = $inicio_pagina; $i <= $fin_pagina; $i++): ?>
                  <?php if ($pagina == $i): ?>
                    <li class="page-item active"><a class="page-link"
                        href="hsi.php?pagina=<?php echo $i; ?>&selectServicioFilter=<?php echo isset($_GET['selectServicioFilter']) ? urlencode($_GET['selectServicioFilter']) : ''; ?>&searchInput=<?php echo isset($_GET['searchInput']) ? urlencode($_GET['searchInput']) : ''; ?>"><?php echo $i; ?></a>
                    </li>
                  <?php else: ?>
                    <li class="page-item"><a class="page-link"
                        href="hsi.php?pagina=<?php echo $i; ?>&selectServicioFilter=<?php echo isset($_GET['selectServicioFilter']) ? urlencode($_GET['selectServicioFilter']) : ''; ?>&searchInput=<?php echo isset($_GET['searchInput']) ? urlencode($_GET['searchInput']) : ''; ?>"><?php echo $i; ?></a>
                    </li>
                  <?php endif; ?>
                <?php endfor; ?>

                <!-- Última página -->
                <?php if ($fin_pagina < $numeropaginas): ?>
                  <li class="page-item disabled"><span class="page-link">...</span></li>
                  <li class="page-item"><a class="page-link"
                      href="hsi.php?pagina=<?php echo $numeropaginas; ?>&selectServicioFilter=<?php echo isset($_GET['selectServicioFilter']) ? urlencode($_GET['selectServicioFilter']) : ''; ?>&searchInput=<?php echo isset($_GET['searchInput']) ? urlencode($_GET['searchInput']) : ''; ?>"><?php echo $numeropaginas; ?></a>
                  </li>
                <?php endif; ?>

                <!-- Botón de "siguiente" -->
                <?php if ($pagina == $numeropaginas): ?>
                  <li class="page-item disabled"><a class="page-link" href="#">&raquo;</a></li>
                <?php else: ?>
                  <li class="page-item"><a class="page-link"
                      href="hsi.php?pagina=<?php echo $pagina + 1; ?>&selectServicioFilter=<?php echo isset($_GET['selectServicioFilter']) ? urlencode($_GET['selectServicioFilter']) : ''; ?>&searchInput=<?php echo isset($_GET['searchInput']) ? urlencode($_GET['searchInput']) : ''; ?>">&raquo;</a>
                  </li>
                <?php endif; ?>
              </ul>
            </nav>

          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

</div>

<script src="/SGH/public/layouts/modules/hsiPanel/js/hsi.js"></script>
<?php require_once '../../base/footer.php'; ?>


<script id="JSON">
  [{
    "documento": "Copia de DNI",
    "activo": "no"
  },
  {
    "documento": "Copia de matrícula profesional",
    "activo": "no"
  },
  {
    "documento": "Solicitud de alta de usuario para HSI (ANEXO I)",
    "activo": "no"
  },
    {
      "documento": "Declaración Jurada - Convenio de confidecialidad usuarios HSI (ANEXO II)",
      "activo": "no"
    }
  ]
</script>