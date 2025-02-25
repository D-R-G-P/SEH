<?php
require_once '../../../../../app/db/db.php';
require_once '../../../../../app/db/user_session.php';
require_once '../../../../../app/db/user.php';

$db = new DB();
$pdo = $db->connect();
$user = new User();
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);

$dni_adm = $user->getDni();

// Obtener subroles del usuario administrador
$adm_subroles_stmt = $pdo->prepare("SELECT `subrol_id` FROM `usuarios_subroles` WHERE `dni` = ? AND `rol_id` = ?");
$adm_subroles_stmt->execute([$dni_adm, "12"]);
$adm_subroles = $adm_subroles_stmt->fetchAll(PDO::FETCH_ASSOC);

$sub_id = array_column($adm_subroles, 'subrol_id');

$dni = $_POST['dni'] ?? null;

if (!$dni) {
    die("DNI no proporcionado.");
}

// Obtener datos del usuario
$user_stmt = $pdo->prepare("SELECT dni, nombre, apellido FROM personal WHERE dni = ?");
$user_stmt->execute([$dni]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Usuario no encontrado.");
}

$roles_unicos = [];

if (!empty($sub_id)) {
    $placeholders = implode(',', array_fill(0, count($sub_id), '?'));
    $query = "
        SELECT 
            gr.enabled_rol_id, 
            r.nombre AS enabled_rol_nombre, 
            gr.enabled_subrol_id, 
            sr.nombre AS enabled_subrol_nombre
        FROM grupos_permisos gr
        LEFT JOIN roles r ON gr.enabled_rol_id = r.id
        LEFT JOIN subroles sr ON gr.enabled_subrol_id = sr.id
        WHERE gr.subrol_id IN ($placeholders)
    ";
    $roles_habilitados = $pdo->prepare($query);
    $roles_habilitados->execute($sub_id);

    while ($row = $roles_habilitados->fetch(PDO::FETCH_ASSOC)) {
        $roleId = $row['enabled_rol_id'];
        if (!isset($roles_unicos[$roleId])) {
            $roles_unicos[$roleId] = [
                'nombre' => $row['enabled_rol_nombre'],
                'subroles' => []
            ];
        }
        if ($row['enabled_subrol_id']) {
            $roles_unicos[$roleId]['subroles'][] = [
                'id' => $row['enabled_subrol_id'],
                'nombre' => $row['enabled_subrol_nombre']
            ];
        }
    }
}

// Obtener el rol del usuario
$rolUr_stmt = $pdo->prepare("
    SELECT ur.rol_id 
    FROM usuarios_roles ur
    WHERE ur.dni = ?
");
$rolUr_stmt->execute([$dni]);
$roles_usuario = array_column($rolUr_stmt->fetchAll(PDO::FETCH_ASSOC), 'rol_id');

// Obtener los subroles del usuario
$subrolUr_stmt = $pdo->prepare("
    SELECT us.subrol_id
    FROM usuarios_subroles us
    WHERE us.dni = ?
");
$subrolUr_stmt->execute([$dni]);
$subroles_usuario = array_column($subrolUr_stmt->fetchAll(PDO::FETCH_ASSOC), 'subrol_id');

?>

<div style="display: flex; flex-direction: row; justify-content: center;">
    <h4>Editando permisos de "<?= htmlspecialchars($user['apellido'] . ' ' . $user['nombre']) ?>"</h4>
</div>
<input type="hidden" id="userDni" value="<?= htmlspecialchars($dni) ?>">

<div class="tabla" style="height: 30%; overflow-y: auto;">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th colspan="2">Rol</th>
                <th>Subroles</th>
            </tr>
        </thead>
        <tbody>
    <?php
    $procesados = []; // Para evitar duplicados

    foreach ($roles_unicos as $roleId => $role):
        // Evitar la duplicación del rol en la tabla
        if (isset($procesados[$roleId])) {
            continue;
        }
        $procesados[$roleId] = true;

        // Verificar si el usuario ya tiene asignado este rol
        $isChecked = in_array($roleId, $roles_usuario);
    ?>

    <tr>
        <td class="table-middle table-center"><?= htmlspecialchars($roleId) ?></td>
        <td class="table-middle"><?= htmlspecialchars($role['nombre']) ?></td>

        <td class="table-middle table-center">
            <label class="switch">
                <input type="checkbox" id="rol-<?= $roleId ?>" class="role-checkbox" <?= $isChecked ? 'checked' : '' ?>>
                <span class="slider round"></span>
            </label>
        </td>

        <td>
            <?php if (!empty($role['subroles'])): ?>
                <select id="subrol-<?= $roleId ?>" class="select2 table-select" multiple style="min-width: 30vw; max-width: 50vw;" <?= $isChecked ? '' : 'disabled' ?>>
                    <?php
                    $subrolesProcesados = []; // Para evitar subroles duplicados en el select

                    foreach ($role['subroles'] as $subrole):
                        if (isset($subrolesProcesados[$subrole['id']])) {
                            continue;
                        }
                        $subrolesProcesados[$subrole['id']] = true;
                    ?>
                        <option value="<?= $subrole['id'] ?>" <?= in_array($subrole['id'], $subroles_usuario) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($subrole['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php else: ?>
                <span style="color: grey;">Sin subroles</span>
            <?php endif; ?>
        </td>
    </tr>

    <?php endforeach; ?>
</tbody>



    </table>
</div>