<?php
require_once __DIR__ . '/../../../app/db/db.php';

function generarSelectorUnidades($contenedor, $recorridoId, $idSeleccionado = 1)
{
    $db = new DB();
    $pdo = $db->connect();

    // Obtener la jerarquía hasta la raíz del nodo seleccionado
    $stmt = $pdo->prepare("WITH RECURSIVE path AS (
        SELECT id, nombre, u_padre FROM arquitectura WHERE id = :id AND estado = 'activo'
        UNION ALL
        SELECT a.id, a.nombre, a.u_padre FROM arquitectura a 
        INNER JOIN path p ON p.u_padre = a.id AND a.estado = 'activo'
    ) 
    SELECT id, nombre FROM path ORDER BY id ASC;");

    $stmt->execute(['id' => $idSeleccionado]);
    $recorrido = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener el nombre del nodo seleccionado
    $nombreSeleccionado = "Seleccione una unidad"; // Valor por defecto si no se encuentra
    if (!empty($recorrido)) {
        $nombreSeleccionado = $recorrido[count($recorrido) - 1]['nombre']; // Último elemento del recorrido
        if ($nombreSeleccionado == "Hospital Interzonal General de Agudos General San Martín") { $nombreSeleccionado = "HIGA General San Martín"; } else { $nombreSeleccionado; }
    }

    // Obtener hijos del nodo seleccionado
    $stmtHijas = $pdo->prepare("SELECT id, nombre FROM arquitectura WHERE u_padre = :id AND estado = 'activo'");
    $stmtHijas->execute(['id' => $idSeleccionado]);
    $unidadesHijas = $stmtHijas->fetchAll(PDO::FETCH_ASSOC);

    // Asegurar que el hospital siempre esté presente en el recorrido
    if (empty($recorrido) || $recorrido[0]['id'] != 1) {
        array_unshift($recorrido, ['id' => 1, 'nombre' => 'HIGA General San Martín']);
    }

    // Generar HTML del recorrido (breadcrumb)
    ob_start();
    ?>
    <div class="recorrido-unidades">
        <?php foreach ($recorrido as $index => $unidad): ?>
            <span class="btn-tematico"
                onclick="volverANivel(<?= $unidad['id'] ?>, '<?= htmlspecialchars($contenedor) ?>', '<?= htmlspecialchars($recorridoId) ?>')">
                <?php if ($unidad['nombre'] == "Hospital Interzonal General de Agudos General San Martín") { echo "HIGA General San Martín"; } else { echo $unidad['nombre']; } ?>
            </span>
            <?php if ($index < count($recorrido) - 1): ?>
                <i class="fa-solid fa-arrow-right"></i>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <?php
    $htmlRecorrido = ob_get_clean();

    // Generar HTML del selector
    ob_start();
    ?>
    <div class="contenedor-unidades">
        <label for="unidad_select_<?= htmlspecialchars($contenedor) ?>">Nodo padre <b style="color: red;">*</b></label>
        <select id="unidad_select_<?= htmlspecialchars($contenedor) ?>" class="select2 unidad-select"
            data-contenedor="<?= htmlspecialchars($contenedor) ?>" data-recorrido="<?= htmlspecialchars($recorridoId) ?>"
            onchange="actualizarUnidades(this.value, '<?= htmlspecialchars($contenedor) ?>', '<?= htmlspecialchars($recorridoId) ?>')">
            <option value="">Seleccione una unidad</option>
            <?php foreach ($unidadesHijas as $unidad): ?>
                <option value="<?= $unidad['id'] ?>"><?= htmlspecialchars($unidad['nombre']) ?></option>
            <?php endforeach; ?>
        </select>

        <input type="hidden" name="unidad_id" id="unidad_id_<?= htmlspecialchars($contenedor) ?>"
            value="<?= $idSeleccionado ?>">
    </div>

    <script>inicializarSelects("<?= addslashes($nombreSeleccionado) ?>")</script> <!-- Se pasa el nombre seleccionado -->
    <?php
    $htmlSelector = ob_get_clean();

    // Devolver JSON con los dos bloques de HTML
    return json_encode([
        'recorrido' => $htmlRecorrido,
        'selector' => $htmlSelector
    ]);
}

// Si se llama desde AJAX
if (isset($_GET['id'], $_GET['contenedor'], $_GET['recorrido'])) {
    header('Content-Type: application/json');
    echo generarSelectorUnidades($_GET['contenedor'], $_GET['recorrido'], intval($_GET['id']));
    exit;
}
