<?php
require_once '../../../../../app/db/db.php';

$db = new DB();
$pdo = $db->connect();

$id = $_POST['id']; // ID del grupo de permisos

// Obtener el grupo de permisos
$stmt = $pdo->prepare("SELECT id, nombre FROM subroles WHERE id = ?");
$stmt->execute([$id]);
$grupo = $stmt->fetch();
if (!$grupo) {
    echo "<h4>No se encontró el grupo con ID $id</h4>";
    exit;
}

// Obtener todos los roles
$con_roles = $pdo->prepare("SELECT id, nombre FROM roles");
$con_roles->execute();
$roles = $con_roles->fetchAll(PDO::FETCH_ASSOC);

// Obtener permisos guardados (roles y subroles activados)
$permisosQuery = $pdo->prepare("SELECT enabled_rol_id, enabled_subrol_id FROM grupos_permisos WHERE subrol_id = ?");
$permisosQuery->execute([$id]);
$permisos = [];

foreach ($permisosQuery->fetchAll(PDO::FETCH_ASSOC) as $permiso) {
    $permisos[$permiso['enabled_rol_id']][] = $permiso['enabled_subrol_id'];
}
?>

<div style="display: flex; flex-direction: row; justify-content: center;">
<h4 style="font-size: 2vw;">Editando grupo "<?= htmlspecialchars($grupo['nombre']) ?>"</h4>
</div>
<input type="hidden" id="grupoId" value="<?= htmlspecialchars($id) ?>">


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
            <?php foreach ($roles as $role): ?>
                <?php
                $roleId = $role['id'];
                $isChecked = isset($permisos[$roleId]); // ¿Está activo el rol?
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
                        <?php
                        // Obtener subroles del rol actual
                        $con_subroles = $pdo->prepare("SELECT id, nombre FROM subroles WHERE rol_id = ?");
                        $con_subroles->execute([$roleId]);
                        $subroles = $con_subroles->fetchAll(PDO::FETCH_ASSOC);

                        if ($subroles) {
                            ?>
                            <select id="subrol-<?= $roleId ?>" class="select2 tableGenSel" multiple style="min-width: 30vw; max-width: 50vw;" <?= $isChecked ? '' : 'disabled' ?>>
                                <?php foreach ($subroles as $subrole): ?>
                                    <option value="<?= $subrole['id'] ?>" <?= in_array($subrole['id'], $permisos[$roleId] ?? []) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($subrole['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php
                        } else {
                            echo '<span style="color: grey;">Sin subroles</span>';
                        }
                        ?>
                    </td>
                </tr>

            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="assets/js/grupos_permisos.js"></script>
