<?php
// Include database connection
include_once '../../../../../app/db/db.php';  // Archivo de configuración para conectar a la base de datos
include_once '../../../../config.php';  // Archivo de configuración

// Conexión a la base de datos
$db = new DB();
$pdo = $db->connect();

session_start(); // Iniciar la sesión

// Verificar que todos los campos estén llenos
if (isset($_POST['accept']) && $_POST['accept'] == 'on') {
    $reclamante = $_POST['reclamante'];
    $servicio = $_POST['solicitudServicio'];
    $destino = $_POST['destino'];
    $short_description = $_POST['short_description'];
    $prioridad = $_POST['prioridad'];
    $interno = $_POST['interno'];
    $mail = $_POST['mail'];
    $ubicacion = $_POST['ubicacion'];
    $descripcion_detallada = $_POST['descripcion_detallada'];
    $observaciones_reclamante = isset($_POST['observaciones_reclamante']) ? $_POST['observaciones_reclamante'] : '';
    $observaciones_destino = isset($_POST['observaciones_destino']) ? $_POST['observaciones_destino'] : '';

    // Validación adicional (si es necesario)

    try {
        // Insertar los datos en la base de datos
        $stmt = $pdo->prepare("INSERT INTO mantenimiento (reclamante, servicio, destino, short_description, prioridad, interno, mail, ubicacion, long_description, observaciones_reclamante, observaciones_destino, estado_reclamante, estado_destino, new_reclamante, new_destino) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, '', '', 'Pendiente', 'Pendiente', '', '')");
        $stmt->execute([$reclamante, $servicio, $destino, $short_description, $prioridad, $interno, $mail, $ubicacion, $descripcion_detallada]);

        $_SESSION['toast_message'] = [
            'message' => 'La solicitud fue procesada correctamente.',
            'type' => 'success'
        ];
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit(); // Finalizar el script después de la redirección

    } catch (PDOException $e) {
        // Manejar errores de conexión o ejecución
        $_SESSION['toast_message'] = [
            'message' => 'Error al procesar el pedido. Por favor, inténtalo de nuevo. (Error: ' . $e->getMessage() . ')',
            'type' => 'error'
        ];
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit(); // Finalizar el script después de la redirección
    }
} else {
    $_SESSION['toast_message'] = [
        'message' => 'Error al procesar el pedido. Marca el checkbox y vuelve a intentarlo.',
        'type' => 'error'
    ];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit(); // Finalizar el script después de la redirección
}
?>
