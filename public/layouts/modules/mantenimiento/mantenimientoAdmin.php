<?php


require_once '../../../../app/db/db.php';
require_once '../../../../app/db/user_session.php';
require_once '../../../../app/db/user.php';
require_once '../../../config.php';

$user = new User();
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);

requireRole(['administrador', 'direccion', 'mantenimiento']);
requireSubRole(['personal_mantenimiento', 'personal_arquitectura', 'personal_informatica', 'personal_ingenieria_clinica']);

$title = "Administración mantenimiento";

$db = new DB();
$pdo = $db->connect();

$gestionMode = true;

require_once 'controllers/searchSol.php';

// Obtener el parámetro 'selectServicioFilter' de la URL, si no está se establece en null
$sel = isset($_GET['selectServicioFilter']) ? $_GET['selectServicioFilter'] : null;

// Si el parámetro 'selectServicioFilter' no coincide con el servicio del usuario
// y el usuario no tiene rol de "Administrador" ni "Dirección"
if (!$sel) {

    // Asignar el servicio del usuario a 'selectServicioFilter' si no es válido
    $selectServicioFilter = "clr";

    // Redirigir con el nuevo parámetro selectServicioFilter
    $url = "mantenimientoAdmin.php?pagina=$pagina";
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
<link rel="stylesheet" href="/SGH/public/layouts/modules/mantenimiento/css/mantenimiento.css">

<div class="content">

    <div class="modulo" style="text-align: center;">
        <h3>Solicitudes de administración de mantenimiento</h3>
        <p>Este sistema está orientado a la gestión y administración <br> de las solicitudes de mantenimiento, arquitectura, <br> informática e ingeniería clínica.</p>
    </div>

    <div class="admInst" style="position: relative; top: -6vw; left: -29vw;">
        <a class="btn-tematico" style="text-decoration: none;" href="mantenimiento.php">
            <i class="fa-solid fa-toolbox"></i> <b>Acceder a panel general</b>
        </a>
    </div>

    <!-- FORMS -->
    <div id="back" class="back" style="display: none;">

        <div id="newSolicitud" class="divBackForm" style="display: none;">
            <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
                <button class="btn-red" onclick="back.style.display = 'none'; newSolicitud.style.display = 'none'; newSolicitudForm.reset(); $('#solicitudServicio').val(null).trigger('change');" style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
            </div>

            <h3 class="formTitle">Realizar solicitud</h3>

            <form action="/SGH/public/layouts/modules/mantenimiento/controllers/newSolicitud.php" id="newSolicitudForm" method="post" class="backForm">
                <input type="hidden" name="reclamante" value="<?php echo $user->getDni(); ?>" required>
                <div style="margin-top: 30vw;">
                    <label for="solicitudServicio">Servicio</label>
                    <select name="solicitudServicio" id="solicitudServicio" class="select2" required>
                        <?php
                        // Si el usuario tiene el id del servicio igual a 1 o el rol es administrador, generamos todos los servicios
                        echo '<option value="" selected disabled>Seleccionar un servicio...</option>';

                        $getServicios = "SELECT id, servicio FROM servicios WHERE estado = 'Activo'";
                        $stmtServicios = $pdo->query($getServicios);

                        while ($row = $stmtServicios->fetch(PDO::FETCH_ASSOC)) {
                            echo '<option value=' . $row['id'] . '>' . $row['servicio'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <div style="display: flex; flex-direction: row;"><label for="destino">Destinatario</label> <a href="infosol.html" target="_blank" style="margin-left: .3vw;"><i class="fa-solid fa-circle-info"></i></a></div>
                    <select name="destino" id="destino" class="select2" required>
                        <option value="" selected disabled>Seleccionar un destinatario...</option>
                        <option value="mantenimiento">Mantenimiento</option>
                        <option value="arquitectura">Arquitectura</option>
                        <option value="informatica">Informatica</option>
                        <option value="ingenieria_clinica">Ingeniería Clínica</option>
                    </select>
                </div>
                <div>
                    <label for="short_description">Breve descripción del problema</label>
                    <input type="text" name="short_description" id="short_description" style="width: 100%;" required></input>
                </div>
                <div>
                    <label for="prioridad">Prioridad</label>
                    <select name="prioridad" id="prioridad" class="select2" required>
                        <option value="" selected disabled>Seleccionar una prioridad...</option>
                        <option value="Baja">Baja</option>
                        <option value="Media">Media</option>
                        <option value="Alta">Alta</option>
                        <option value="Critica">Crítica</option>
                    </select>
                </div>

                <hr style="border-width: .12vw; color: #000; width: 100%; margin-top: 1vw;">

                <div>
                    <label for="interno">Interno</label>
                    <input type="number" name="interno" id="interno" style="width: 100%;" required></input>
                </div>
                <div>
                    <label for="mail">E-mail</label>
                    <input type="email" name="mail" id="mail" style="width: 100%;" required></input>
                </div>

                <hr style="border-width: .12vw; color: #000; width: 100%; margin-top: 1vw;">

                <div>
                    <label for="ubicacion">Ubicación detallada</label>
                    <textarea name="ubicacion" id="ubicacion" style="min-height: 7vw; resize: none; width: 100%;" required></textarea>
                </div>
                <div>
                    <label for="descripcion_detallada">Descripción detallada</label>
                    <textarea name="descripcion_detallada" id="descripcion_detallada" style="min-height: 7vw; resize: none; width: 100%;" required></textarea>
                </div>


                <hr style="border-width: .12vw; color: #000; width: 100%; margin-top: 1vw;">

                <div style="display: flex; flex-direction: row; align-items: flex-start;">
                    <input type="checkbox" name="accept" id="accept" class="accept-checkbox-input" onchange="checkAccept()">
                    <label for="accept" class="accept-checkbox">
                        Entiendo que la información de este formulario se compartirá con el destinatario del problema y no volveré a completarlo otra vez por la misma solicitud.
                    </label>
                </div>

                <div style="display: flex; flex-direction: row; justify-content: center;">
                    <button type="submit" id="btn-send" disabled class="btn-green"><b><i class="fa-solid fa-plus"></i> Realizar solicitud</b></button>
                </div>
            </form>
        </div>

        <div id="infoCaseBase" class="divBackForm" style="display: none; width: 80%; overflow-y: auto;">


            <div id="info"></div>

        </div>

    </div>

    <div class="modulo">
        <div id="pending">

            <div id="tabla" style="margin-top: 1vw;">
                <div style="width: 100%;">
                    <h4>Pendientes</h4>

                    <form action="mantenimientoAdmin.php" method="get" id="formFiltro" style="display: flex; flex-direction: row; flex-wrap: nowrap; align-items: center; overflow-y: hidden;">
                        <input type="hidden" name="pagina" value="<?php echo isset($_GET['pagina']) ? htmlspecialchars($_GET['pagina']) : 1; ?>" id="pageInput">

                        <div>
                            <select name="selectServicioFilter" id="selectServicioFilter" class="select2" onchange="pageInput.value = 1; this.form.submit()">

                                <?php
                                $selectedServicio = isset($_GET['selectServicioFilter']) ? htmlspecialchars($_GET['selectServicioFilter']) : '';

                                echo '<option value="" selected disabled>Seleccionar un servicio...</option>';
                                echo '<option value="clr"' . ($selectedServicio === 'clr' ? ' selected' : '') . '>Seleccionar todos los servicios</option>';

                                $getServicios = "SELECT id, servicio FROM servicios WHERE estado = 'Activo'";
                                $stmtServicios = $pdo->query($getServicios);

                                while ($row = $stmtServicios->fetch(PDO::FETCH_ASSOC)) {
                                    $selected = ($selectedServicio == $row['id']) ? ' selected' : '';
                                    echo '<option value="' . $row['id'] . '"' . $selected . '>' . $row['servicio'] . '</option>';
                                }
                                ?>
                            </select>

                        </div>
                        <button type="submit" class="btn-green"><i class="fa-solid fa-magnifying-glass"></i></button>
                    </form>


                </div>




                <!-- Agregar la tabla HTML -->
                <table id="pending">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Servicio</th>
                            <th>Fecha</th>
                            <th>Problema</th>
                            <th>Prioridad</th>
                            <th>Destino</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $servicioFilter = $_GET['selectServicioFilter'];

                        $queryNews = "SELECT * FROM mantenimiento WHERE estado_reclamante != 'Completado'";
                        if ($servicioFilter != "clr") {
                            $queryNews .= " AND servicio = :servicioFilter";
                        }

                        if (hasAccess(['administrador', 'direccion'])) {
                            $queryNews .= "";
                        } else if (hasSubAccess(['personal_mantenimiento'])) {
                            $queryNews .= " AND destino = 'mantenimiento'";
                        } else if (hasSubAccess(['personal_arquitectura'])) {
                            $queryNews .= " AND destino = 'arquitectura'";
                        } else if (hasSubAccess(['personal_informatica'])) {
                            $queryNews .= " AND destino = 'informatica'";
                        } else if (hasSubAccess(['personal_ingenieria_clinica'])) {
                            $queryNews .= " AND destino = 'ingenieria_clinica'";
                        } else if (hasSubAccess(['auditoria'])) {
                            $queryNews .= " AND destino = 'auditoria'";
                        }

                        $stmtNews = $pdo->prepare($queryNews);
                        if ($servicioFilter != "clr") {
                            $stmtNews->bindParam(':servicioFilter', $servicioFilter, PDO::PARAM_INT);
                        }
                        $stmtNews->execute();

                        if ($stmtNews->rowCount() == 0) {
                            // Si no hay resultados con estado 'news'
                            echo '<tr><td colspan="6" style="text-align: center;">No hay notificaciones pendientes</td></tr>';
                        } else {
                            while ($rowNews = $stmtNews->fetch(PDO::FETCH_ASSOC)) {
                                echo '<tr>';

                                echo '<td class="table-center table-middle">' . $rowNews['id'] . '</td>';

                                $stmtServicioNews = $pdo->prepare("SELECT servicio FROM servicios WHERE id = ?");
                                $stmtServicioNews->execute([$rowNews['servicio']]);
                                $rowServicioNews = $stmtServicioNews->fetch(PDO::FETCH_ASSOC);

                                if ($rowServicioNews) {
                                    echo '<td class="table-center table-middle">' . $rowServicioNews['servicio'] . '</td>';
                                } else {
                                    echo '<td>Error al obtener los datos</td>';
                                }

                                $date = new DateTime($rowNews['fecha_registro']);
                                $formattedDate = $date->format('d/m/Y H:i');

                                echo '<td class="table-center table-middle">' . $formattedDate . '</td>';

                                echo '<td class="table-center table-middle">' . $rowNews['short_description'] . '</td>';

                                echo '<td class="table-center table-middle">' . $rowNews['prioridad'] . '</td>';

                                echo '<td class="table-center table-middle">';
                                switch ($rowNews['destino']) {
                                    case 'mantenimiento':
                                        echo 'Mantenimiento';
                                        break;
                                    case 'arquitectura':
                                        echo 'Arquitectura';
                                        break;
                                    case 'informatica':
                                        echo 'Informática';
                                        break;
                                    case 'ingenieria_clinica':
                                        echo 'Ingeniería Clínica';
                                        break;
                                    case 'auditoria':
                                        echo 'Auditoría';
                                        break;
                                    default:
                                        echo 'Error al obtener los datos';
                                        break;
                                }
                                echo '</td>';

                                echo '<td class="table-center table-middle">
                                        <button class="btn-green" onclick="loadInfo(' . htmlspecialchars(json_encode($rowNews['id'])) . ', \'destino\');';

                                if (!hasAccess(['administrador', 'direccion'])) {
                                    echo 'user_register(' . htmlspecialchars(json_encode($rowNews['id'])) . ', ' . htmlspecialchars(json_encode($user->getDni())) . ')';
                                }

                                echo '">
                                            <i class="fa-solid fa-hand-pointer"></i>
                                        </button>
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

    <div class="modulo">
        <div id="pending">

            <div id="tabla" style="margin-top: 1vw;">
                <div style="width: 100%;">
                    <h4>Finalizados</h4>
                </div>

                <!-- Agregar la tabla HTML -->
                <table id="ended">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Servicio</th>
                            <th>Fecha</th>
                            <th>Problema</th>
                            <th>Prioridad</th>
                            <th>Destino</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($totalregistros >= 1): ?>
                            <?php foreach ($registros as $reg): ?>
                                <tr>
                                    <!-- ID -->
                                    <td class="table-center table-middle"><?= htmlspecialchars($reg['id']) ?></td>

                                    <!-- Servicio -->
                                    <td class="table-center table-middle"><?= htmlspecialchars($reg['nombre_servicio']) ?></td>

                                    <!-- Fecha -->
                                    <td class="table-center table-middle">
                                        <?php
                                        $fechaRegistro = $reg['fecha_registro'];
                                        $formateada = (new DateTime($fechaRegistro))->format('d/m/Y H:i');
                                        ?>
                                        <?= htmlspecialchars($formateada) ?>
                                    </td>

                                    <!-- Problema -->
                                    <td class="table-center table-middle"><?= htmlspecialchars($reg['short_description']) ?></td>

                                    <!-- Permisos -->
                                    <td class="table-center table-middle"><?= htmlspecialchars($reg['prioridad']) ?></td>

                                    <!-- Destino -->
                                    <td>
                                        <?php
                                        echo '<td class="table-center table-middle">';
                                        switch ($reg['destino']) {
                                            case 'mantenimiento':
                                                echo 'Mantenimiento';
                                                break;
                                            case 'arquitectura':
                                                echo 'Arquitectura';
                                                break;
                                            case 'informatica':
                                                echo 'Informática';
                                                break;
                                            case 'ingenieria_clinica':
                                                echo 'Ingeniería Clínica';
                                                break;
                                            case 'auditoria':
                                                echo 'Auditoría';
                                                break;
                                            default:
                                                echo 'Error al obtener los datos';
                                                break;
                                        }
                                        echo '</td>';
                                        ?>
                                    </td>

                                    <!-- Acciones -->
                                    <td class="table-center table-middle">
                                        <button class="btn-green" onclick="loadInfo('<?= htmlspecialchars($reg['id']) ?>', 'destino')">
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
                                    <li class="page-item"><a class="page-link" href="mantenimiento.php?pagina=<?php echo $pagina - 1; ?>&selectServicioFilter=<?php echo isset($_GET['selectServicioFilter']) ? urlencode($_GET['selectServicioFilter']) : ''; ?>&searchInput=<?php echo isset($_GET['searchInput']) ? urlencode($_GET['searchInput']) : ''; ?>">&laquo;</a></li>
                                <?php endif; ?>

                                <!-- Página 1 -->
                                <?php if ($inicio_pagina > 1): ?>
                                    <li class="page-item"><a class="page-link" href="mantenimiento.php?pagina=1&selectServicioFilter=<?php echo isset($_GET['selectServicioFilter']) ? urlencode($_GET['selectServicioFilter']) : ''; ?>&searchInput=<?php echo isset($_GET['searchInput']) ? urlencode($_GET['searchInput']) : ''; ?>">1</a></li>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif; ?>

                                <!-- Páginas intermedias -->
                                <?php for ($i = $inicio_pagina; $i <= $fin_pagina; $i++): ?>
                                    <?php if ($pagina == $i): ?>
                                        <li class="page-item active"><a class="page-link" href="mantenimiento.php?pagina=<?php echo $i; ?>&selectServicioFilter=<?php echo isset($_GET['selectServicioFilter']) ? urlencode($_GET['selectServicioFilter']) : ''; ?>&searchInput=<?php echo isset($_GET['searchInput']) ? urlencode($_GET['searchInput']) : ''; ?>"><?php echo $i; ?></a></li>
                                    <?php else: ?>
                                        <li class="page-item"><a class="page-link" href="mantenimiento.php?pagina=<?php echo $i; ?>&selectServicioFilter=<?php echo isset($_GET['selectServicioFilter']) ? urlencode($_GET['selectServicioFilter']) : ''; ?>&searchInput=<?php echo isset($_GET['searchInput']) ? urlencode($_GET['searchInput']) : ''; ?>"><?php echo $i; ?></a></li>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <!-- Última página -->
                                <?php if ($fin_pagina < $numeropaginas): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <li class="page-item"><a class="page-link" href="mantenimiento.php?pagina=<?php echo $numeropaginas; ?>&selectServicioFilter=<?php echo isset($_GET['selectServicioFilter']) ? urlencode($_GET['selectServicioFilter']) : ''; ?>&searchInput=<?php echo isset($_GET['searchInput']) ? urlencode($_GET['searchInput']) : ''; ?>"><?php echo $numeropaginas; ?></a></li>
                                <?php endif; ?>

                                <!-- Botón de "siguiente" -->
                                <?php if ($pagina == $numeropaginas): ?>
                                    <li class="page-item disabled"><a class="page-link" href="#">&raquo;</a></li>
                                <?php else: ?>
                                    <li class="page-item"><a class="page-link" href="mantenimiento.php?pagina=<?php echo $pagina + 1; ?>&selectServicioFilter=<?php echo isset($_GET['selectServicioFilter']) ? urlencode($_GET['selectServicioFilter']) : ''; ?>&searchInput=<?php echo isset($_GET['searchInput']) ? urlencode($_GET['searchInput']) : ''; ?>">&raquo;</a></li>
                                <?php endif; ?>
                            </ul>
                        </nav>

                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <script src="/SGH/public/layouts/modules/mantenimiento/js/mantenimiento.js"></script>
    <?php require_once '../../base/footer.php'; ?>