<?php
require_once '../../../../../app/db/db.php';

session_start(); // Iniciar sesión para usar variables de sesión

// Verificar si se recibieron los parámetros GET esperados
if (isset($_GET['permiso']) && isset($_GET['dni']) && isset($_GET['servicio'])) {
    // Obtener los valores de los parámetros GET
    $dni = $_GET['dni'];
    $permiso = $_GET['permiso']; // El id del rol (que en este caso es el permiso)
    $servicio = $_GET['servicio'];

    // Crear una instancia de la clase DB para conectarse a la base de datos
    $db = new DB();
    $pdo = $db->connect();

    // Verificar si la conexión fue exitosa
    if ($pdo) {
        try {
            // Verificar si el usuario ya tiene el permiso (en este caso, el rol con id_rol)
            $sql_check = "SELECT * FROM usuarios_roles_hsi WHERE dni = :dni AND id_rol = :id_rol";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->bindParam(':dni', $dni);
            $stmt_check->bindParam(':id_rol', $permiso); // Usamos id_rol como permiso
            $stmt_check->execute();

            // Si el permiso ya existe, se elimina
            if ($stmt_check->rowCount() > 0) {
                // Eliminar el permiso
                $sql_delete = "DELETE FROM usuarios_roles_hsi WHERE dni = :dni AND id_rol = :id_rol";
                $stmt_delete = $pdo->prepare($sql_delete);
                $stmt_delete->bindParam(':dni', $dni);
                $stmt_delete->bindParam(':id_rol', $permiso); // Usamos id_rol como permiso
                $stmt_delete->execute();

                // Mensaje de éxito si el permiso se eliminó
                $_SESSION['toast_message'] = [
                    'message' => 'Permiso eliminado correctamente',
                    'type' => 'success'
                ];
            } else {
                // Si no tiene el permiso, lo agrega
                $sql_insert = "INSERT INTO usuarios_roles_hsi (dni, id_rol) VALUES (:dni, :id_rol)";
                $stmt_insert = $pdo->prepare($sql_insert);
                $stmt_insert->bindParam(':dni', $dni);
                $stmt_insert->bindParam(':id_rol', $permiso); // Usamos id_rol como permiso
                $stmt_insert->execute();

                // Mensaje de éxito si el permiso se agregó
                $_SESSION['toast_message'] = [
                    'message' => 'Permiso agregado correctamente',
                    'type' => 'success'
                ];
                $_SESSION['load_info'] = [
                    'dni' => $dni,
                    'servicio' => $servicio
                ];
            }

        } catch (PDOException $e) {
            $_SESSION['toast_message'] = [
                'message' => 'Error al modificar el permiso: ' . $e->getMessage(),
                'type' => 'error'
            ];
        }
    } else {
        $_SESSION['toast_message'] = [
            'message' => 'Error en la conexión a la base de datos.',
            'type' => 'error'
        ];
    }
} else {
    $_SESSION['toast_message'] = [
        'message' => 'No se recibieron todos los parámetros necesarios.',
        'type' => 'error'
    ];
}

// Redireccionar de nuevo a la página anterior
header("Location: " . $_SERVER['HTTP_REFERER']);
?>
