<?php

require_once '../../../../app/db/db.php';
require_once '../../../../app/db/user_session.php';
require_once '../../../../app/db/user.php';

$user = new User();
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);

$title = "Solicitudes de HSI";

$db = new DB();
$pdo = $db->connect();

$servicioFilter = $user->getServicio();

?>

<?php require_once '../../base/header.php'; ?>
<link rel="stylesheet" href="/SGH/public/layouts/modules/hsiPanel/css/hsi.css">

<div class="content">

  <div class="modulo" style="text-align: center;">
    <h3 style="margin-bottom: .5vw;">Sistema de gestión de HSI</h3>
    <p>Este sistema está oreintado a la gestion y </br> solicitud de los usuarios de HSI del personal a cargo.</p>
  </div>

  <div class="back" id="back" style="display: flex;">
    <div class="divBackForm" id="neUser" style="display: none;">
      <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
        <button class="btn-red" onclick="back.style.display = 'none'; neUser.style.display = 'none'; newUserForm.reset(); $('#dniSelect').val(null).trigger('change'); $('#servicioSelect').val(null).trigger('change'); $('#permisosSelect').val(null).trigger('change');" style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
      </div>
      <h3>Solicitar nuevo usuario</h3>

      <form action="/SGH/public/layouts/modules/hsiPanel/controllers/newUser.php" method="post" class="backForm" id="newUserForm">

        <div>
          <label for="dni">DNI</label>
          <select name="dni" id="dniSelect" required style="width: 95%;">
            <option value="" selected disabled>Seleccionar agente...</option>
            <?php

            if ($user->getRol() == "Administrador" || $user->getRol() == "Direccion") {
              // Realiza la consulta a la tabla servicios
              $getPersonal = "SELECT apellido, nombre, dni FROM personal";
              $stmt = $pdo->query($getPersonal);

              // Itera sobre los resultados y muestra las filas en la tabla
              while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '<option value=' . $row['dni'] . '>' . $row['apellido'] . ' ' . $row['nombre'] . ' - ' . $row['dni'] . '</option>';
              }
            } else {
              // Realiza la consulta a la tabla servicios
              $getPersonal = "SELECT apellido, nombre, dni FROM personal WHERE servicio_id = $servicioFilter";
              $stmt = $pdo->query($getPersonal);

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

            if ($user->getRol() == "Administrador" || $user->getRol() == "Direccion") {
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
          <select name="permisos[]" id="permisosSelect" style="width: 95%;" multiple="multiple" placeholder="Seleccionar permiso(s)" required>
            <option value="Especialista Médix">Especialista Médix</option>
            <option value="Profesional de la Salud">Profesional de la Salud</option>
            <option value="Administrativx">Administrativx</option>
            <option value="Enfermero">Enfermero</option>
            <option value="Enfermerx Adultx Mayor">Enfermerx Adultx Mayor</option>
            <option value="Administrador de Agenda">Administrador de Agenda</option>
            <option value="Especialista odontológico">Especialista odontológico</option>
            <option value="Administrador de Camas">Administrador de Camas</option>
            <option value="Personal de Imágenes">Personal de Imágenes</option>
            <option value="Personal de Laboratorio">Personal de Laboratorio</option>
            <option value="Personal de Farmacia">Personal de Farmacia</option>
            <option value="Personal de Estadística">Personal de Estadística</option>
          </select>
        </div>

        <div style="display: flex; flex-direction: row; justify-content: center;">
          <button type="button" class="btn-green" onclick="confirmUsuario.style.display = 'flex'"><b><i class="fa-solid fa-plus"></i> Solicitar nuevo usuario</b></button>
        </div>

        <div id="confirmUsuario" class="divBackForm" style="display: none;">
          <h4>Antes de solicitar el usuario</h4>
          <p style="margin-top: 1vw;">Es necesario que descargue los anexos correspondientes</p>

          <div class="pdfs" style="display: flex; flex-direction: row; align-content: center; flex-wrap: wrap; justify-content: space-evenly; margin: 2vw 0">
            <a href="/SGH/public/layouts/modules/hsiPanel/docs/Anexo 1.pdf" style="color: #000;" target="_blank"><i class="fa-solid fa-file-pdf"></i> Anexo I</a>
            <a href="/SGH/public/layouts/modules/hsiPanel/docs/Anexo 2.pdf" style="color: #000;" target="_blank"><i class="fa-solid fa-file-pdf"></i> Anexo II</a>
          </div>

          <div style="display: flex; flex-direction: row; justify-content: center;">
            <button class="btn-red" type="button" onclick="confirmUsuario.style.display = 'none';"><i class="fa-solid fa-xmark"></i> Cancelar</button>

            <button class="btn-green" type="submit"><i class="fa-solid fa-plus"></i>
              Solicitar usuario</button>
          </div>
        </div>
      </form>
    </div>

    <div class="divBackForm" id="addDocsDiv" style="display: none;">
      <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
        <button class="btn-red" onclick="back.style.display = 'none'; addDocsDiv.style.display = 'none'; addDocsForm.reset();" style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
      </div>
      <h3>Agregar documentación</h3>
      <p style="color: red;">* documentos obligatorios</p>

      <form action="/SGH/public/layouts/modules/hsiPanel/controllers/docsUpload.php" class="backForm" method="post" id="addDocsForm" enctype="multipart/form-data">
        <input type="hidden" name="docsDniHidden" id="docsDniHidden">
        <div>
          <label for="docsDni">Documento Nacional de Identidad <br> (Frente y dorso en un archivo) <b style="color: red;">*</b></label>
          <input type="file" name="docsDni" id="docsDni" required>
        </div>
        <div>
          <label for="docsMatricula">Matricula Profesional <br> (frente y dorso en un archivo) si corresponde</label>
          <input type="file" name="docsMatricula" id="docsMatricula">
        </div>
        <div>
          <label for="docsAnexoI">Solicitud de alta de usuario para HSI <br> (ANEXO I) <b style="color: red;">*</b></label>
          <input type="file" name="docsAnexoI" id="docsAnexoI" required>
        </div>
        <div>
          <label for="docsAnexoII">Declaración Jurada - Convenio de confidencialidad usuarios HSI <br> (ANEXO II) <b style="color: red;">*</b></label>
          <input type="file" name="docsAnexoII" id="docsAnexoII" required>
        </div>

        <button class="btn-green" type="submit"><i class="fa-solid fa-file-arrow-up"></i> Subir archivos</button>
      </form>
    </div>

    <div class="divBackForm editModule">
      <h3>Información de usuario</h3>

      <div class="cuerpoInfo">
        <div><b>Apellido del agente:</b> Lamas</div>
        <div><b>Nombre del agente:</b> Cristian Jonathan</div>
        <div><b>Documento del agente:</b> 43.255.000</div>
        <div><b>Servicio:</b> Dirección de Redes y Gestión de Personas</div>
        <div><b>Mail:</b> crslamas@gmail.com</div>
        <div><b>Telefono:</b> 221 438-0474</div>
      </div>
      <div><b>Documentos</b></div>
      <div><b>Permisos</b></div>
    </div>
  </div>

  <div class="modulo">

    <div class="inlineDiv">
      <button class="btn-green" onclick="newUser()"><b><i class="fa-solid fa-plus"></i> Solicitar nuevo usuario</b></button>
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
                $permisosNew_array = json_decode($rowNews['permisos'], true);

                if ($permisosNew_array !== null) {
                  $permisos_activos = [];
                  foreach ($permisosNew_array as $permisoNew) {
                    $nombre_permiso = $permisoNew['permiso'];
                    $activo = $permisoNew['activo'];

                    if ($activo == "si") {
                      $permisos_activos[] = '<i class="fa-solid fa-chevron-right"></i> ' . $nombre_permiso;
                    }
                  }
                  echo implode('<br>', $permisos_activos);
                }
                echo '</td>';

                echo '<td class="table-middle">' .  $rowNews['observaciones'] . '</td>';

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

                echo '<td class="table-middle table-left" style="width: max-content;"><div style="display: grid; grid-template-columns: auto min-content; align-items: center;">';
                $permisosWorking_array = json_decode($rowWorking['permisos'], true);

                if ($permisosWorking_array !== null) {
                  $permisos_activos = [];
                  foreach ($permisosWorking_array as $permisoWorking) {
                    $nombre_permiso = $permisoWorking['permiso'];
                    $activo = $permisoWorking['activo'];

                    if ($activo == "si") {
                      // Determinar el símbolo a mostrar según el estado activo
                      $icono = '<i class="fa-solid fa-chevron-right"></i>';

                      // Imprimir el nombre del permiso y el icono en las dos columnas del grid
                      $permisos_activos[] =
                        '<div>' . $icono . '</div>' .
                        '<div style="width: max-content">' . $nombre_permiso . '</div>';
                    }
                  }
                  echo implode('', $permisos_activos);
                }
                echo '</div></td>';



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
                      default:
                        $documento_nombre = $documento; // Si no hay una coincidencia, mantener el nombre original
                        break;
                    }

                    switch ($activo) {
                      case 'no':
                        $simbolo =  '<i class="fa-solid fa-xmark"></i>';
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
            <?php
            $queryNews = "SELECT * FROM hsi WHERE servicio = :servicioFilter AND estado = 'habilitado'";
            $stmtNews = $pdo->prepare($queryNews);
            $stmtNews->bindParam(':servicioFilter', $servicioFilter, PDO::PARAM_INT);
            $stmtNews->execute();

            if ($stmtNews->rowCount() == 0) {
              // Si no hay resultados con estado 'news'
              echo '<tr><td colspan="7">No hay usuarios habilitados</td></tr>';
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
                $permisosNew_array = json_decode($rowNews['permisos'], true);

                if ($permisosNew_array !== null) {
                  $permisos_activos = [];
                  foreach ($permisosNew_array as $permisoNew) {
                    $nombre_permiso = $permisoNew['permiso'];
                    $activo = $permisoNew['activo'];

                    if ($activo == "si") {
                      $permisos_activos[] = '<i class="fa-solid fa-chevron-right"></i> ' . $nombre_permiso;
                    }
                  }
                  echo implode('<br>', $permisos_activos);
                }
                echo '</td>';

                echo '<td class="table-center table-middle">
                <button class="btn-green" onclick=""><i class="fa-solid fa-hand-pointer"></i></button>
            </td>';

                echo '</tr>';
              }
            }
            ?>

          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<script src="/SGH/public/layouts/modules/hsiPanel/js/hsi.js"></script>
<?php require_once '../../base/footer.php'; ?>


<script id="JSON">
  [{
      "permiso": "Especialista Médix",
      "activo": "no"
    },
    {
      "permiso": "Profesional de la Salud",
      "activo": "no"
    },
    {
      "permiso": "Administrativx",
      "activo": "no"
    },
    {
      "permiso": "Enfermero",
      "activo": "no"
    },
    {
      "permiso": "Enfermerx Adultx Mayor",
      "activo": "no"
    },
    {
      "permiso": "Administrador de Agenda",
      "activo": "no"
    },
    {
      "permiso": "Especialista odontológico",
      "activo": "no"
    },
    {
      "permiso": "Administrador de Camas",
      "activo": "no"
    },
    {
      "permiso": "Personal de Imágenes",
      "activo": "no"
    },
    {
      "permiso": "Personal de Laboratorio",
      "activo": "no"
    },
    {
      "permiso": "Personal de Farmacia",
      "activo": "no"
    },
    {
      "permiso": "Personal de Estadística",
      "activo": "no"
    },
    {
      "permiso": "Administrador institucional",
      "activo": "no"
    }
  ]

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