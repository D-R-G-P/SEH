<?php

require_once '../../../../app/db/db.php';
require_once '../../../../app/db/user_session.php';
require_once '../../../../app/db/user.php';

$user = new User();
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);

$title = "Información de equipos";

$db = new DB();
$pdo = $db->connect();

$servicioFilter = $user->getServicio();
$servicioFilter = "1";
?>

<?php require_once '../../base/header.php'; ?>
<link rel="stylesheet" href="/SGH/public/layouts/modules/equipos/css/equipos.css">
<div class="notisContent">
  <div class="notis" id="notis"></div>
</div>
<div class="content">


  <div class="back" id="back">

    <div class="divBackForm" id="addEquip">

      <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
        <button class="btn-red" onclick="back.style.display = 'none'; addEquip.style.display = 'none'; addEquipForm.reset(); $('#tipo_equipo').val(null).trigger('change');" style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
      </div>

      <h3 class="formTitle">Agregar equipo</h3>

      <form action="/SGH/public/layouts/modules/equipos/controllers/addEquipoForm.php" method="post" class="backForm">

        <div>
          <label for="marca">Marca</label>
          <input type="text" name="marca" id="marca" required>
        </div>

        <div>
          <label for="modelo">Modelo</label>
          <input type="text" name="modelo" id="modelo" required>
        </div>

        <div>
          <label for="tipo_equipo">Tipo de equipo</label>
          <div style="flex-direction: row; display: flex; align-items: center;">
            <select name="tipo_equipo" class="select2" id="tipo_equipo" style="width: calc(100% - 5vw);" required>
              <option value="" selected disabled>Seleccionar un tipo de equipo...</option>
              <?php

              // Realiza la consulta a la tabla servicios
              $getServicio = "SELECT id, tipo_equipo FROM tipos_equipo";
              $stmt = $pdo->query($getServicio);

              // Itera sobre los resultados y muestra las filas en la tabla
              while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '<option value="' . $row['tipo_equipo'] . '">' . $row['tipo_equipo'] . '</option>';
              }

              ?>
            </select>
            <button class="btn-green" style="width: 3vw; height: 3vw; display: flex; align-items: center; justify-content: center; margin-top: 0; margin-bottom: 0;" type="button" title="Agregar tipo de equipo" onclick="backTipo.style.display = 'flex';"><i class="fa-solid fa-plus"></i></button>
          </div>
        </div>

        <div>
          <label for="servicio">Servicio</label>
          <select name="servicio" class="select2" id="servicio" style="width: 95%;" required>
            <option value="" disabled selected>Seleccione un servicio...</option>
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

          <div style="display: flex; align-items: center;">
            <button style="width: fit-content;" class="btn-green" type="submit"><i class="fa-solid fa-plus"></i> Agregar equipo</button>
          </div>
        </div>

      </form>

      <div class="back" id="backTipo">
        <div class="divBackForm" id="addTipo" style="width: 30%;">
          <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw;">
            <button class="btn-red" onclick="backTipo.style.display = 'none'; addTipoForm.reset();" style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
          </div>

          <h3 class="formTitle">Agregar tipo de equipo</h3>
          <form class="backForm" id="addTipoForm" style="margin-top: -2vw;">
            <div>
              <label for="tipoAdd">Tipo de equipo</label>
              <input type="text" name="tipoAdd" id="tipoAdd">
            </div>

            <button type="submit" id="submitFormTipo" class="btn-green"><i class="fa-solid fa-plus"></i> Agregar tipo de equipo</button>
          </form>

        </div>
      </div>

      <div class="divBackForm" id="editEquip">

        <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
          <button class="btn-red" onclick="back.style.display = 'none'; editEquip.style.display = 'none'; editEquipForm.reset(); $('#tipo_equipo').val(null).trigger('change');" style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
        </div>

        <h3 class="formTitle">Editar equipo</h3>

        <form action="/SGH/public/layouts/modules/equipos/controllers/addEquipoForm.php" method="post" class="backForm">

          <div>
            <label for="marca">Marca</label>
            <input type="text" name="marca" id="marca" required>
          </div>

          <div>
            <label for="modelo">Modelo</label>
            <input type="text" name="modelo" id="modelo" required>
          </div>

          <div>
            <label for="tipo_equipo">Tipo de equipo</label>
            <div style="flex-direction: row; display: flex; align-items: center;">
              <select name="tipo_equipo" class="select2" id="tipo_equipo" style="width: calc(100% - 5vw);" required>
                <option value="" selected disabled>Seleccionar un tipo de equipo...</option>
                <?php

                // Realiza la consulta a la tabla servicios
                $getServicio = "SELECT id, tipo_equipo FROM tipos_equipo";
                $stmt = $pdo->query($getServicio);

                // Itera sobre los resultados y muestra las filas en la tabla
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                  echo '<option value="' . $row['tipo_equipo'] . '">' . $row['tipo_equipo'] . '</option>';
                }

                ?>
              </select>
              <button class="btn-green" style="width: 3vw; height: 3vw; display: flex; align-items: center; justify-content: center; margin-top: 0; margin-bottom: 0;" type="button" title="Agregar tipo de equipo" onclick="backTipo.style.display = 'flex';"><i class="fa-solid fa-plus"></i></button>
            </div>
          </div>

          <div>
            <label for="servicio">Servicio</label>
            <select name="servicio" class="select2" id="servicio" style="width: 95%;" required>
              <option value="" disabled selected>Seleccione un servicio...</option>
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

            <div style="display: flex; align-items: center;">
              <button style="width: fit-content;" class="btn-green" type="submit"><i class="fa-solid fa-plus"></i> Agregar equipo</button>
            </div>
          </div>

        </form>

        <div class="back" id="backTipo">
          <div class="divBackForm" id="addTipo" style="width: 30%;">
            <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw;">
              <button class="btn-red" onclick="backTipo.style.display = 'none'; addTipoForm.reset();" style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
            </div>

            <h3 class="formTitle">Agregar tipo de equipo</h3>
            <form class="backForm" id="addTipoForm" style="margin-top: -2vw;">
              <div>
                <label for="tipoAdd">Tipo de equipo</label>
                <input type="text" name="tipoAdd" id="tipoAdd">
              </div>

              <button type="submit" id="submitFormTipo" class="btn-green"><i class="fa-solid fa-plus"></i> Agregar tipo de equipo</button>
            </form>

          </div>
        </div>

      </div>
    </div>

    <div class="modulo" style="text-align: center;">
      <h3 style="margin-bottom: .5vw;">Sistema de gestión de equipos</h3>
      <p>Este sistema está oreintado a la gestion de los </br> equipos dentro del servicio</p>
    </div>

    <div class="modulo">
      <div>
        <button class="btn-green" onclick="back.style.display = 'flex'; addEquip.style.display = 'flex';"><i class="fa-solid fa-plus"></i> <b>Agregar equipo</b></button>
      </div>
      <div style="display: grid; grid-template-columns: repeat(3, 1fr);
grid-row-gap: 1vw">

        <?php

        $query = "SELECT equipos.*, servicios.servicio AS nombre_servicio 
          FROM equipos 
          LEFT JOIN servicios ON equipos.servicio = servicios.id 
          WHERE equipos.servicio = :servicioFilter";



        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':servicioFilter', $servicioFilter, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
          // Si no hay resultados con estado 'news'
          echo 'No hay equipos registrados';
        } else {
          while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            echo '<div class="equipModule modulo">
          <p style="position: absolute; color: lightgrey; font-size: .8vw; width: fit-content;">ID ' . $row['id'] . '</p>
            <div class="first">
                <b>' . $row['marca'] . ' - ' . $row['modelo'] . '</b>
            </div>

            <hr>

            <div class="seccond">
                <b>' . $row['tipo'] . '</b>
                <div>';

            switch ($row['estado']) {
              case 'operativo':
                $style = "operativo";
                $text = "Equipo operativo";
                break;
              case 'problema':
                $style = "problema";
                $text = "Equipo con problema";
                break;
              case 'desoperativo':
                $style = "desoperativo";
                $text = "Equipo no operativo";
                break;
              default:
                $style = "";
                $text = "Error";
                break;
            }


            echo '<p class="' . $style . '">' . $text . '</p>
                    <p>' . $row['problema'] . '</p>
                </div>
                <b>' . $row['nombre_servicio'] . '</b>
            </div>

            <hr>

            <div class="third">
                <button class="btn-green" onclick="editEquip(' . $row['id'] . ', ' . $row['marca'] . ', ' . $row['modelo'] . ', ' . $row['tipo'] . ', ' . $row['servicio'] . ')"><i class="fa-solid fa-pencil"></i> Editar</button>
                <button class="btn-yellow"><i class="fa-solid fa-triangle-exclamation"></i> Informar problema</button>
            </div>
        </div>';
          }
        }

        ?>
      </div>

    </div>

  </div>

  <script src="/SGH/public/layouts/modules/equipos/js/equipos.js"></script>
  <?php require_once '../../base/footer.php'; ?>