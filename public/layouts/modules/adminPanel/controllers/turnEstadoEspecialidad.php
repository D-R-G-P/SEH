<?php
// Realiza la conexión a la base de datos (utiliza tu propia lógica para la conexión)
require_once '../../../../../app/db/db.php';

$db = new DB();
$pdo = $db->connect();

session_start();

// Verifica si se han enviado los parámetros id y action en la URL
if (isset($_GET['id']) && isset($_GET['action'])) {
    // Obtén los valores de id y action desde la URL
    $id = $_GET['id'];
    $action = $_GET['action'];

    try {
        // Prepara la consulta SQL para actualizar el estado según la acción
        $estado = '';
        switch ($action) {
            case 'activar':
                $estado = 'Activo';
                $stmt = $pdo->prepare("UPDATE especialidades SET estado = ? WHERE id = ?");
                $stmt->execute([$estado, $id]);
                break;
            case 'desactivar':
                $estado = 'Inactivo';
                $stmt = $pdo->prepare("UPDATE especialidades SET estado = ? WHERE id = ?");
                $stmt->execute([$estado, $id]);
                break;
            case 'eliminar':
                $estado = 'Eliminado';
                $stmt = $pdo->prepare("UPDATE especialidades SET estado = ? WHERE id = ?");
                $stmt->execute([$estado, $id]);
                break;
            default:
                throw new Exception('Acción no válida');
        }

        // Almacena un mensaje de éxito en la sesión
        $_SESSION['toast_message'] = [
            'message' => 'Cambio realizado correctamente.',
            'type' => 'success'
        ];

        // Redirige de vuelta a donde viniste
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    } catch (PDOException $e) {
        // Si hay un error en la base de datos, almacena el mensaje de error en la sesión
        $_SESSION['toast_message'] = [
            'message' => 'Error en la base de datos: ' . $e->getMessage(),
            'type' => 'error'
        ];

        // Redirige de vuelta a donde viniste
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    } catch (Exception $e) {
        // Si hay un error en la acción, almacena el mensaje de error en la sesión
        $_SESSION['toast_message'] = [
            'message' => 'Error: ' . $e->getMessage(),
            'type' => 'error'
        ];

        // Redirige de vuelta a donde viniste
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
} else {
    // Si no se enviaron los parámetros necesarios, muestra un mensaje de error y redirige
    $_SESSION['toast_message'] = [
        'message' => 'Error al enviar los parametros.',
        'type' => 'error'
    ];

    // Redirige de vuelta a donde viniste
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
