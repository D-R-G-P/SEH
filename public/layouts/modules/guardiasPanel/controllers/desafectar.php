<?php
require_once '../../../../../app/db/db.php';

session_start(); // Iniciar sesión para usar variables de sesión

// Verificar si se recibió el parámetro GET esperado
if(isset($_GET['id'])) {
    // Obtener el valor del parámetro GET
    $id = $_GET['id'];
    $estado = "desafectado";

    // Crear una instancia de la clase DB para conectarse a la base de datos
    $db = new DB();
    $pdo = $db->connect();

    // Verificar si la conexión fue exitosa
    if($pdo) {
        try {
            // Actualizar la contraseña en la base de datos
            $sql_update = "UPDATE guardias SET estado=? WHERE id=?";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute([$estado, $id]);

            $_SESSION['toast_message'] = [
                'message' => 'Agente desafectado correctamente',
                'type' => 'success'
            ];
        } catch (PDOException $e) {
            $_SESSION['toast_message'] = [
                'message' => 'Error al desafectar al agente: ' . $e->getMessage(),
                'type' => 'error'
            ];
        }
    } else {
        $_SESSION['toast_message'] = [
            'message' => 'Error al conectar a la base de datos.',
            'type' => 'error'
        ];
    }
} else {
    $_SESSION['toast_message'] = [
        'message' => 'Error al obtener los parametros.',
        'type' => 'error'
    ];
}

// Redireccionar de nuevo a la página anterior
header("Location: " . $_SERVER['HTTP_REFERER']);
?>
