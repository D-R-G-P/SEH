<?php
// Include database connection
include_once '../../../../../app/db/db.php';  // Archivo de configuración para conectar a la base de datos
include_once '../../../../config.php';  // Archivo de configuración

// Conexión a la base de datos
$db = new DB();
$pdo = $db->connect();

session_start(); // Iniciar la sesión

// Validar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['sender'], $_POST['observaciones'])) {
    // Obtener y sanitizar valores del formulario
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $sender = filter_input(INPUT_POST, 'sender', FILTER_SANITIZE_STRING);
    $observaciones = filter_input(INPUT_POST, 'observaciones', FILTER_SANITIZE_STRING);

    // Validar valores
    if (empty($id) || empty($sender)) {
        $_SESSION['toast_message'] = [
            'message' => 'Datos inválidos.',
            'type' => 'error'
        ];
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // Conectar a la base de datos
    $db = new DB();
    $pdo = $db->connect();

    // Determinar columna según el sender
    if ($sender == 'reclamante') {
        $estadoColumn = "observaciones_reclamante";
        $act = "reclamante";
    } else if ($sender == 'destino') {
        $estadoColumn = "observaciones_destino";
        $act = "destino";
    }

    if ($estadoColumn === null) {
        $_SESSION['toast_message'] = [
            'message' => 'Datos inválidos.',
            'type' => 'error'
        ];
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // Actualizar el estado
    try {
        $query = "UPDATE mantenimiento SET $estadoColumn = :observaciones WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':observaciones', $observaciones, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $_SESSION['toast_message'] = [
                'message' => 'Estado cambiado correctamente.',
                'type' => 'success'
            ];
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        } else {
            $_SESSION['toast_message'] = [
                'message' => 'Error al actualizar el estado. Por favor, inténtalo de nuevo.',
                'type' => 'error'
            ];
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['toast_message'] = [
            'message' => 'Error al procesar el pedido: ' . $e->getMessage() . '. Por favor, inténtalo de nuevo.',
            'type' => 'error'
        ];
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }
} else {
    echo "Acceso no permitido.";
    exit;
}
