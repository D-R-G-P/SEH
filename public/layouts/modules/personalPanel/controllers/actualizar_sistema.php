<?php
require_once '../../../../../app/db/db.php';

session_start(); // Iniciar sesión para usar variables de sesión

// Verificar si se recibieron los parámetros GET esperados
if(isset($_GET['id']) && isset($_GET['sistema']) && isset($_GET['estado'])) {
    // Obtener los valores de los parámetros GET
    $id = $_GET['id'];
    $sistema = $_GET['sistema'];
    $estado = $_GET['estado'];

    // Almacenar los mensajes en variables de sesión
    $_SESSION['toast_message'] = [
        'message' => "ID: $id, Sistema: $sistema, Estado: $estado",
        'type' => 'info'
    ];

    $estadomod = ($estado == "si") ? "no" : "si";

    // Crear una instancia de la clase DB para conectarse a la base de datos
    $db = new DB();
    $pdo = $db->connect();

    // Verificar si la conexión fue exitosa
    if($pdo) {
        try {
            // Modificar el JSON en la base de datos
            $sql = "SELECT sistemas FROM personal WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if($row) {
                // Decodificar el JSON
                $sistemas_array = json_decode($row["sistemas"], true);

                // Iterar sobre el array de sistemas y actualizar el estado del sistema correspondiente
                foreach ($sistemas_array as &$sistema_item) {
                    if ($sistema_item['sistema'] === $sistema) {
                        $sistema_item['activo'] = $estadomod;
                        break; // Terminar el bucle una vez que se ha encontrado y actualizado el sistema
                    }
                }

                // Codificar el array de nuevo a JSON
                $sistemas_json_updated = json_encode($sistemas_array);

                // Actualizar el JSON en la base de datos
                $sql_update = "UPDATE personal SET sistemas=? WHERE id=?";
                $stmt_update = $pdo->prepare($sql_update);
                $stmt_update->execute([$sistemas_json_updated, $id]);

                $_SESSION['toast_message'] = [
                    'message' => 'Acceso a "'.$sistema.'" actualizado correctamente',
                    'type' => 'success'
                ];
            } else {
                $_SESSION['toast_message'] = [
                    'message' => "No se encontraron resultados para el ID proporcionado: $id",
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
            'message' => 'Error en la conexion a la base de datos.',
            'type' => 'error'
        ];
    }
} else {
    $_SESSION['toast_message'] = [
        'message' => 'Error al conectar a la base de datos.',
        'type' => 'error'
    ];
}

// Redireccionar de nuevo a la página anterior
header("Location: " . $_SERVER['HTTP_REFERER']);
?>
