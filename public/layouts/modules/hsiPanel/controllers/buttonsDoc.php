<?php
require_once '../../../../../app/db/db.php';

session_start(); // Iniciar sesión para usar variables de sesión

// Verificar si se recibieron los parámetros GET esperados
if(isset($_GET['dni']) && isset($_GET['documento']) && isset($_GET['action']) && isset($_GET['servicio'])) {
    // Obtener los valores de los parámetros GET
    $dni = $_GET['dni'];
    $documento = $_GET['documento'];
    $action = $_GET['action'];
    $servicio = $_GET['servicio'];

    switch ($action) {
        case 'verificar':
                $estado = "verificado";
                break;
            case 'desverificar':
                $estado = "pendiente";
                break;
            case 'anular':
                $estado = "no";
                break;
    }

    // Crear una instancia de la clase DB para conectarse a la base de datos
    $db = new DB();
    $pdo = $db->connect();

    // Verificar si la conexión fue exitosa
    if($pdo) {
        try {
            // Modificar el JSON en la base de datos
            $sql = "SELECT documentos FROM hsi WHERE dni = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$dni]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if($row) {
                // Decodificar el JSON
                $documentos_array = json_decode($row["documentos"], true);

                // Iterar sobre el array de documentos y actualizar el estado del documento correspondiente
                foreach ($documentos_array as &$documento_item) {
                    if ($documento_item['documento'] === $documento) {
                        $documento_item['activo'] = $estado;
                        break; // Terminar el bucle una vez que se ha encontrado y actualizado el documento
                    }
                }

                // Codificar el array de nuevo a JSON
                $documentos_json_updated = json_encode($documentos_array);

                // Actualizar el JSON en la base de datos
                $sql_update = "UPDATE hsi SET documentos = ? WHERE dni = ?";
                $stmt_update = $pdo->prepare($sql_update);
                $stmt_update->execute([$documentos_json_updated, $dni]);

                $_SESSION['toast_message'] = [
                    'message' => 'Estado de "'.$documento.'" actualizado correctamente',
                    'type' => 'success'
                ];

            } else {
                $_SESSION['toast_message'] = [
                    'message' => "No se encontraron resultados para el DNI proporcionado: $dni",
                    'type' => 'warning'
                ];
            }
        } catch (PDOException $e) {
            $_SESSION['toast_message'] = [
                'message' => 'Error al actualizar el JSON: ' . $e->getMessage(),
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
