<?php

require_once '../../../../app/db/db.php';
require_once '../../../../app/db/user_session.php';
require_once '../../../../app/db/user.php';
require_once '../../../config.php';
require_once '../../../resources/encrypter/decrypt.php';

cargarEntorno(dirname(__DIR__, 4) . '/.env');

$user = new User();
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);

requireRole(['administrador', 'direccion', 'gestion_camas']);

$title = "Gestión de Camas";

$db = new DB();
$pdo = $db->connect();

define('HOSPITAL_ID', 81);
$hospital_id = HOSPITAL_ID;
define('PABELLON_ID', 71);
$pabellon_id = PABELLON_ID;
define('PISO_ID', 72);
$piso_id = PISO_ID;
define('SALA_ID', 44);
$sala_id = SALA_ID;
define('HABITACION_ID', 47);
$habitacion_id = HABITACION_ID;
define('EMERGENCIAS_ID', 50);
$emergencias_id = EMERGENCIAS_ID;

?>
<?php require_once '../../base/header.php'; ?>
<link rel="stylesheet" href="/SGH/public/layouts/modules/gestion_camas/css/gestion_camas.css">

<div class="content">

  <div class="back" id="back">
    <div class="modulo" id="info_cama" style="background-color: #fff; display: none;"></div>

    <div class="modulo" id="info_paciente" style="background-color: #fff; display: none;"></div>
  </div>


  <div class="modulo" style="display: flex; justify-content: center; align-items: center;">
    <h2>Referencias</h2>
    <?php if (hasSubAccess(['administrador_camas'])) { ?>
      <button class="btn-green" id="new_bed" style="position: absolute; top: .4vw; left: 18.7vw;"><i
          class="fa-solid fa-plus"></i>
        Crear cama</button>
    <?php } ?>
    <div class="referencias">
      <div class="camas_reference">
        <span class="camas-reference-title">Referencia de camas</span>
        <div class="camas_reference_children">
          <div>
            <div class="cama_libre"></div>
            <span>Libre</span>
          </div>

          <div>
            <div class="cama_ocupada"></div>
            <span>Ocupada</span>
          </div>

          <div>
            <div class="cama_reservada"></div>
            <span>Reservada</span>
          </div>

          <div>
            <div class="cama_bloqueada"></div>
            <span>Bloqueada</span>
          </div>
        </div>
      </div>

      <div class="unidad_reference">
        <span class="unidad-reference-title">Referencia de unidades</span>
        <div class="unidad_reference_children">
          <div>
            <div class="unidad_hospital"></div>
            <span>Hospital</span>
          </div>

          <div>
            <div class="unidad_pabellon"></div>
            <span>Pabellón</span>
          </div>

          <div>
            <div class="unidad_planta"></div>
            <span>Planta</span>
          </div>

          <div>
            <div class="unidad_sala"></div>
            <span>Sala</span>
          </div>

          <div>
            <div class="unidad_habitacion"></div>
            <span>Habitación</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php

  $hospitales = "SELECT id, nombre FROM arquitectura WHERE tipo_sitio_id = :hospital_id AND estado = 'activo'";
  $hospitales = $pdo->prepare($hospitales);
  $hospitales->bindParam(':hospital_id', $hospital_id);
  $hospitales->execute();


  foreach ($hospitales as $hospital) { ?>



    <div class="hospital">
      <div class="hospital_name" id="hospital_<?php echo $hospital['id']; ?>">
        <span><i class="fa-solid fa-hospital"></i> <?php echo $hospital['nombre']; ?></span>
      </div>

      <?php

      $current_hospital_id = $hospital['id'];

      $pabellones = "SELECT id, nombre, u_padre FROM arquitectura WHERE tipo_sitio_id = :pabellon_id AND u_padre = :u_padre AND estado = 'activo'";
      $pabellones = $pdo->prepare($pabellones);
      $pabellones->bindParam(':pabellon_id', $pabellon_id);
      $pabellones->bindParam(':u_padre', $current_hospital_id);
      $pabellones->execute();

      foreach ($pabellones as $pabellon) {

        ?>

        <div class="hospital_lat">
          <div class="hospital_children">
            <div class="pabellon_name" id="pabellon_<?php echo $pabellon['id']; ?>">
              <span><i class="fa-solid fa-building"></i> <?php echo $pabellon['nombre']; ?></span>
            </div>

            <div class="pabellon_lat">
              <div class="pabellon_children">

                <?php

                $current_pabellon_id = $pabellon['id'];

                $pisos = "SELECT id, nombre, u_padre FROM arquitectura WHERE tipo_sitio_id = :piso_id AND u_padre = :u_padre AND estado = 'activo'";
                $pisos = $pdo->prepare($pisos);
                $pisos->bindParam(':piso_id', $piso_id);
                $pisos->bindParam(':u_padre', $current_pabellon_id);
                $pisos->execute();

                foreach ($pisos as $piso) {

                  ?>

                  <div class="piso_name" id="piso_<?php echo $piso['id']; ?>">
                    <span><i class="fa-solid fa-layer-group"></i> <?php echo $piso['nombre']; ?></span>
                  </div>

                  <div class="piso_lat">
                    <div class="piso_children">


                      <?php

                      $current_piso_id = $piso['id'];

                      $salas = "SELECT id, nombre, u_padre FROM arquitectura WHERE tipo_sitio_id = :sala_id AND u_padre = :u_padre AND estado = 'activo'";
                      $salas = $pdo->prepare($salas);
                      $salas->bindParam(':sala_id', $sala_id);
                      $salas->bindParam(':u_padre', $current_piso_id);
                      $salas->execute();

                      foreach ($salas as $sala) {

                        ?>

                        <div class="sala_name" id="sala_<?php echo $sala['id']; ?>">
                          <span><i class="fa-solid fa-bed-pulse"></i> <?php echo $sala['nombre']; ?></span>
                        </div>

                        <div class="habitacion_lat">
                          <div class="piso_sub-children">

                            <?php

                            $current_sala_id = $sala['id'];

                            $habitaciones = "SELECT id, nombre, u_padre FROM arquitectura WHERE tipo_sitio_id = :habitacion_id AND u_padre = :u_padre AND estado = 'activo'";
                            $habitaciones = $pdo->prepare($habitaciones);
                            $habitaciones->bindParam(':habitacion_id', $habitacion_id);
                            $habitaciones->bindParam(':u_padre', $current_sala_id);
                            $habitaciones->execute();

                            foreach ($habitaciones as $habitacion) {

                              ?>

                              <div class="sala_children">

                                <div class="habitacion_name" id="habitacion_<?php echo $habitacion['id']; ?>">
                                  <span><i class="fa-solid fa-door-closed"></i>
                                    <?php echo $habitacion['nombre']; ?></span>
                                </div>

                                <div class="cama">

                                  <?php
                                  $current_habitacion_id = $habitacion['id'];
                                  $camas = "SELECT id, name, bed_status FROM beds WHERE ubicacion_arquitectura_id = :u_padre AND bed_status != 'eliminado'";
                                  $camas = $pdo->prepare($camas);
                                  $camas->bindParam(':u_padre', $current_habitacion_id);
                                  $camas->execute();
                                  foreach ($camas as $cama) {
                                    $cama_id = $cama['id'];
                                    $cama_nombre = $cama['name'];
                                    $cama_estado = $cama['bed_status'];
                                    ?>

                                    <div class="cama_item <?php echo 'cama_' . $cama_estado; ?>" id="cama_<?php echo $cama_id; ?>"
                                      data-cama-nombre="<?php echo $cama_nombre; ?>" data-cama-estado="<?php echo $cama_estado; ?>">
                                      <i class="fa-solid fa-bed"></i>
                                      <span style="font-size: 1.1vw;"><?php echo $cama_nombre; ?></span>

                                      <?php if ($cama_estado == 'Ocupada') {
                                        $get_patient = "SELECT p.name, p.last_name, p.document FROM patients_admitteds pa JOIN patients p ON pa.patient_id = p.id WHERE pa.bed_id = :bed_id AND pa.date_discharged IS NULL";
                                        $get_patient = $pdo->prepare($get_patient);
                                        $get_patient->bindParam(':bed_id', $cama_id);
                                        $get_patient->execute();

                                        $patient_data = $get_patient->fetch(PDO::FETCH_ASSOC);

                                        if ($patient_data) {
                                          echo '<span style="margin-top: .3vw;">' . decryptData(htmlspecialchars($patient_data['name'])) . ' ' . decryptData(htmlspecialchars($patient_data['last_name'])) . '</span>
																							<span>' . decryptData(htmlspecialchars($patient_data['document'])) . '</span>';
                                        }
                                      } ?>
                                    </div>

                                  <?php } ?>

                                </div>

                              </div>

                            <?php } ?>

                          </div>
                        </div>

                      <?php } ?>
                    </div>
                  </div>

                <?php } ?>

              </div>
            </div>
          </div>
        </div>

      <?php } ?>
    </div>
  <?php } ?>

</div>

<script src="/SGH/public/layouts/modules/gestion_camas/js/gestion_camas.js"></script>
<script src="/SGH/public/resources/unidades/unidades.js"></script>
<?php require_once '../../base/footer.php'; ?>