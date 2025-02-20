<?php
require_once '../../../../../app/db/db.php';

session_start(); // Iniciar sesión para usar variables de sesión

// Verificar si se recibió el parámetro GET esperado
if(isset($_GET['dni']) && isset($_GET['action'])) {
    // Obtener el valor del parámetro GET
    $dni = $_GET['dni'];
    $action = $_GET['action'];
    $new = "si";
    $fecha = date("d/m/Y H:i");

    switch ($action) {
        case 'baja':
            $observaciones = "Usuario deshabilitado.";
            $estado = "disabled";
            $residente = "";
            break;
        case 'password':
            $observaciones = "Contraseña reiniciada, credenciales enviadas al mail del agente. Cuenta con 48 horas para acceder. (".$fecha.")";
            $estado = "habilitado";
            $residente = "";
            break;
        case 'habilita':
            $observaciones = "Usuario creado correctamente, las credenciales fueron enviadas al whatsapp/mail del agente. Cuenta con 48 horas para acceder. (".$fecha.")";
            $estado = "habilitado";
            $residente = "";
            break;
        case 'habilitar':
            $observaciones = "Usuario creado correctamente, las credenciales fueron enviadas al whatsapp/mail del agente. Cuenta con 48 horas para acceder. (".$fecha.") </br> Usuario habilitado como residente, vencerá al final del año.";
            $estado = "habilitado";
            $residente = "si";
            break;
    }

    // Crear una instancia de la clase DB para conectarse a la base de datos
    $db = new DB();
    $pdo = $db->connect();

    // Verificar si la conexión fue exitosa
    if($pdo) {
        try {
            // Actualizar la contraseña en la base de datos
            $sql_update = "UPDATE hsi SET observaciones = ?, new = ?, estado = ?, residente = ? WHERE dni = ?";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute([$observaciones, $new, $estado, $residente, $dni]);

            $_SESSION['toast_message'] = [
                'message' => 'Notificación enviada correctamente',
                'type' => 'success'
            ];
        } catch (PDOException $e) {
            $_SESSION['toast_message'] = [
                'message' => 'Error al enviar la notificación',
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