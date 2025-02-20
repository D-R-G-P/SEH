<?php
require_once '../../../../../app/db/db.php';

session_start(); // Iniciar sesión para usar variables de sesión

// Verificar si se recibió el parámetro GET esperado
if(isset($_GET['id']) && isset($_GET['dni'])) {
    // Obtener el valor del parámetro GET
    $id = $_GET['id'];
    $dni = $_GET['dni'];
    $pr = "si";

    // Generar la contraseña MD5 a partir del DNI
    $contrasena_md5 = md5($dni);

    // Crear una instancia de la clase DB para conectarse a la base de datos
    $db = new DB();
    $pdo = $db->connect();

    // Verificar si la conexión fue exitosa
    if($pdo) {
        try {
            // Actualizar la contraseña en la base de datos
            $sql_update = "UPDATE personal SET password=?, pr = ? WHERE id=?";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute([$contrasena_md5, $pr, $id]);

            $_SESSION['toast_message'] = [
                'message' => 'Contraseña actualizada correctamente. Deberá ingresar con su dni separado por punto (<b>'.$dni.'</b>)',
                'type' => 'success',
                'duration' => 25000
            ];
        } catch (PDOException $e) {
            $_SESSION['toast_message'] = [
                'message' => 'Error al generar la contraseña: ' . $e->getMessage() . '.',
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
