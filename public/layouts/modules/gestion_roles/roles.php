<?php

require_once '../../../../app/db/db.php';
require_once '../../../../app/db/user_session.php';
require_once '../../../../app/db/user.php';
require_once '../../../config.php';
require_once 'controllers/search_personal_list.php';


$user = new User();
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);

requireRole(['administrador', 'direccion', 'gestion_roles']);

$title = "Gestión de Roles";

$db = new DB();
$pdo = $db->connect();

// Obtener el parámetro 'selectServicioFilter' de la URL, si no está se establece en null
$sel = $_GET['selectServicioFilter'] ?? null;
$servicio_usuario = $user->getServicio();

// Si el parámetro 'selectServicioFilter' no coincide con el servicio del usuario
// y el usuario no tiene rol de "Administrador" ni "Dirección"
if (($sel != $servicio_usuario || !$sel) && !hasAccess(['administrador', 'direccion'])) {

    // Asignar el servicio del usuario a 'selectServicioFilter' si no es válido
    $selectServicioFilter = $user->getServicio();

    // Redirigir con el nuevo parámetro selectServicioFilter
    $url = "roles.php?pagina=$pagina";
    if ($selectServicioFilter) {
        // Asegurarse de que el valor del servicio sea correctamente escapado para la URL
        $url .= "&selectServicioFilter=" . urlencode($selectServicioFilter);
    }

    // Redirigir al usuario a la URL con el servicio correcto
    header("Location: $url");
    exit();
}

?>
<script>const admusr = "<?= $user->getDni() ?>"</script>
<?php require_once '../../base/header.php'; ?>
<link rel="stylesheet" href="/SGH/public/layouts/modules/gestion_roles/css/roles.css">

<div class="content">

    <div class="modulo" style="text-align: center;">
        <h3>Gestión de Roles</h3>
        <p>Este modulo está destinado a otorgar acceso a las diferentes utilidades del sistema.</p>
    </div>

    <div class="back" id="back">

        <!-- Vista de grupos de roles -->
        <div class="divBackForm" id="roles_groups"
            style="width: auto; overflow-y: auto; padding: 0 4vw 2vw 4vw; display: none;">
            <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
                <button class="btn-red" onclick="back.style.display = 'none'; roles_groups.style.display = 'none';"
                    style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
            </div>
            <div id="rolGenView" style="overflow-y: auto;"></div>
        </div>

        <div class="divBackForm" id="roles_users" style="width: auto; overflow-y: auto; padding: 0 4vw 2vw 4vw;">
            <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
                <button class="btn-red" onclick="back.style.display = 'none'; roles_users.style.display = 'none';"
                    style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
            </div>
            <div id="rolAdmView" style="overflow-y: auto;"></div>
        </div>
    </div>

    <?php if (hasAccess(['administrador'])) { ?>
        <div class="modulo">

            <h4>Grupos de Roles</h4>

            <div class="subroles_grupo" id="subroles_grupo">
                <?php
                $stmt = $pdo->prepare("SELECT id, nombre FROM subroles WHERE rol_id = 12");
                $stmt->execute();
                $roles = $stmt->fetchAll();
                foreach ($roles as $rol) {
                    echo '<button class="btn-tematico" onclick="toggle_group_view(' . $rol['id'] . ')">' . $rol["nombre"] . '</button>';
                }
                ?>
            </div>

        </div>
    <?php } ?>

    <div style="width: 100%; height: auto; display: flex; flex-direction: column">

        <div
            style="display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%; height: 100%;">
            <div
                style="display: flex; flex-direction: column; align-items: center; width: fit-content; max-width: 60vw; padding: 1.5vw; background-color: #f8d7da; border: 2px solid #f1aeb5; border-radius: 0.8vw; box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1); text-align: center;">
                <h3 style="color: #842029; font-size: 1.5vw; margin: 0;">¡ATENCIÓN!</h3>
                <p style="color: #842029; font-size: 1.2vw; margin: 0.5vw 0 0; max-width: 50vw;">
                    Las acciones realizadas por el usuario en base al rol otorgado serán responsabilidad del jefe de
                    servicio,
                    considerando que fue éste quien le otorgó el acceso.
                </p>
            </div>
        </div>


        <form action="roles.php" method="GET" id="formFiltro"
            style="display: flex; flex-direction: row; flex-wrap: nowrap; align-items: center; overflow-y: hidden; width: 100%; margin-top: 1vw; height: 4.5vw;">
            <input type="hidden" name="pagina" id="pageInput"
                value="<?= isset($_GET['pagina']) ? htmlspecialchars($_GET['pagina']) : 1; ?>">

            <div
                style="display: grid; grid-template-columns: repeat(2, 1fr); grid-column-gap: 1vw; overflow-y: hidden;">
                <select name="selectServicioFilter" id="selectServicioFilter" class="select2"
                    <?= (!hasAccess(['administrador', 'direccion'])) ? 'disabled' : ''; ?>
                    onchange="pageInput.value = 1; this.form.submit()">

                    <?php
                    $selectedServicio = isset($_GET['selectServicioFilter']) ? htmlspecialchars($_GET['selectServicioFilter']) : '';

                    if (hasAccess(['administrador', 'direccion'])) {
                        echo '<option value="" disabled>Seleccionar un servicio...</option>';
                        echo '<option value="clr"' . ($selectedServicio === 'clr' ? ' selected' : '') . '>Todos los servicios</option>';

                        $stmtServicios = $pdo->query("SELECT id, servicio FROM servicios WHERE estado = 'Activo'");
                        while ($row = $stmtServicios->fetch(PDO::FETCH_ASSOC)) {
                            $selected = ($selectedServicio == $row['id']) ? ' selected' : '';
                            echo '<option value="' . $row['id'] . '"' . $selected . '>' . $row['servicio'] . '</option>';
                        }
                    } else {
                        $servicioUsuario = $user->getServicio();
                        $stmtServicioUsuario = $pdo->prepare("SELECT id, servicio FROM servicios WHERE id = ?");
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
                    value="<?= isset($_GET['searchInput']) ? htmlspecialchars($_GET['searchInput']) : ''; ?>">
            </div>

            <button type="submit" class="btn-green"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>

    </div>

    <table style="margin-top: .5vw;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombres y Apellidos</th>
                <th>DNI</th>
                <th>Servicio</th>
                <th>Roles</th>
                <th>Subroles</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php

            if ($totalregistros == 0) {
                echo '<tr><td colspan="7" style="text-align: center;">No se encontraron registros</td></tr>';
            } else {

                foreach ($registros as $reg): ?>
                    <tr>
                        <td class="table-middle"><?= htmlspecialchars($reg['personal_id']); ?></td>
                        <td class="table-middle"><?= htmlspecialchars($reg['nombre'] . ' ' . $reg['apellido']); ?></td>
                        <td class="table-middle"><?= htmlspecialchars($reg['dni']); ?></td>
                        <td class="table-middle"><?= htmlspecialchars($reg['servicio'] ?: 'Sin servicio'); ?></td>
                        <td class="table-middle"><?= htmlspecialchars($reg['roles'] ?: 'Sin rol'); ?></td>
                        <td class="table-middle"><?= htmlspecialchars($reg['subroles'] ?: 'Sin subrol'); ?></td>
                        <td class="table-middle table-center">
                            <button class="btn-green" title="Editar" onclick="toggle_user_view('<?= $reg['dni']; ?>')"><i
                                    class="fa-solid fa-hand-pointer"></i></button>
                        </td>
                    </tr>
                <?php endforeach;
            } ?>
        </tbody>
    </table>

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
                            href="roles.php?pagina=<?php echo $pagina - 1; ?>&selectServicioFilter=<?php echo isset($_GET['selectServicioFilter']) ? urlencode($_GET['selectServicioFilter']) : ''; ?>&searchInput=<?php echo isset($_GET['searchInput']) ? urlencode($_GET['searchInput']) : ''; ?>">&laquo;</a>
                    </li>
                <?php endif; ?>

                <!-- Página 1 -->
                <?php if ($inicio_pagina > 1): ?>
                    <li class="page-item"><a class="page-link"
                            href="roles.php?pagina=1&selectServicioFilter=<?php echo isset($_GET['selectServicioFilter']) ? urlencode($_GET['selectServicioFilter']) : ''; ?>&searchInput=<?php echo isset($_GET['searchInput']) ? urlencode($_GET['searchInput']) : ''; ?>">1</a>
                    </li>
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                <?php endif; ?>

                <!-- Páginas intermedias -->
                <?php for ($i = $inicio_pagina; $i <= $fin_pagina; $i++): ?>
                    <?php if ($pagina == $i): ?>
                        <li class="page-item active"><a class="page-link"
                                href="roles.php?pagina=<?php echo $i; ?>&selectServicioFilter=<?php echo isset($_GET['selectServicioFilter']) ? urlencode($_GET['selectServicioFilter']) : ''; ?>&searchInput=<?php echo isset($_GET['searchInput']) ? urlencode($_GET['searchInput']) : ''; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php else: ?>
                        <li class="page-item"><a class="page-link"
                                href="roles.php?pagina=<?php echo $i; ?>&selectServicioFilter=<?php echo isset($_GET['selectServicioFilter']) ? urlencode($_GET['selectServicioFilter']) : ''; ?>&searchInput=<?php echo isset($_GET['searchInput']) ? urlencode($_GET['searchInput']) : ''; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endif; ?>
                <?php endfor; ?>

                <!-- Última página -->
                <?php if ($fin_pagina < $numeropaginas): ?>
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                    <li class="page-item"><a class="page-link"
                            href="roles.php?pagina=<?php echo $numeropaginas; ?>&selectServicioFilter=<?php echo isset($_GET['selectServicioFilter']) ? urlencode($_GET['selectServicioFilter']) : ''; ?>&searchInput=<?php echo isset($_GET['searchInput']) ? urlencode($_GET['searchInput']) : ''; ?>"><?php echo $numeropaginas; ?></a>
                    </li>
                <?php endif; ?>

                <!-- Botón de "siguiente" -->
                <?php if ($pagina == $numeropaginas): ?>
                    <li class="page-item disabled"><a class="page-link" href="#">&raquo;</a></li>
                <?php else: ?>
                    <li class="page-item"><a class="page-link"
                            href="roles.php?pagina=<?php echo $pagina + 1; ?>&selectServicioFilter=<?php echo isset($_GET['selectServicioFilter']) ? urlencode($_GET['selectServicioFilter']) : ''; ?>&searchInput=<?php echo isset($_GET['searchInput']) ? urlencode($_GET['searchInput']) : ''; ?>">&raquo;</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>

    <?php endif; ?>


    <script>
        function cambiarPagina(pagina) {
            document.getElementById('pagina').value = pagina;
            document.getElementById('formFiltro').submit();
        }
    </script>




</div>
<script src="/SGH/public/layouts/modules/gestion_roles/js/roles.js"></script>
<?php require_once '../../base/footer.php'; ?>