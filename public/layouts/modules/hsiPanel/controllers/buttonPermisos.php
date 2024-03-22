<?php
require_once '../../../../../app/db/db.php';

session_start(); // Iniciar sesión para usar variables de sesión

// Verificar si se recibieron los parámetros GET esperados
if(isset($_GET['dni']) && isset($_GET['permiso']) && isset($_GET['estado'])) {
    // Obtener los valores de los parámetros GET
    $dni = $_GET['dni'];
    $permiso = $_GET['permiso'];
    $estado = $_GET['estado'];

    $estadomod = ($estado == "si") ? "no" : "si";

    // Crear una instancia de la clase DB para conectarse a la base de datos
    $db = new DB();
    $pdo = $db->connect();

    // Verificar si la conexión fue exitosa
    if($pdo) {
        try {
            // Modificar el JSON en la base de datos
            $sql = "SELECT permisos FROM hsi WHERE dni = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$dni]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if($row) {
                // Decodificar el JSON
                $permisos_array = json_decode($row["permisos"], true);

                // Iterar sobre el array de permisos y actualizar el estado del permiso correspondiente
                foreach ($permisos_array as &$permiso_item) {
                    if ($permiso_item['permiso'] === $permiso) {
                        $permiso_item['activo'] = $estadomod;
                        break; // Terminar el bucle una vez que se ha encontrado y actualizado el permiso
                    }
                }

                // Codificar el array de nuevo a JSON
                $permisos_json_updated = json_encode($permisos_array);

                // Actualizar el JSON en la base de datos
                $sql_update = "UPDATE hsi SET permisos = ? WHERE dni = ?";
                $stmt_update = $pdo->prepare($sql_update);
                $stmt_update->execute([$permisos_json_updated, $dni]);

                $_SESSION['success_message'] = '<div class="notisContent"><div class="notis" id="notis" style="justify-content: center;">Permiso "'.$permiso.'" actualizado correctamente</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
            } else {
                $_SESSION['warning_message'] = "No se encontraron resultados para el DNI proporcionado: $dni"; // Mensaje de advertencia
            }
        } catch (PDOException $e) {
            $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error al actualizar el JSON: ' . $e->getMessage() . '.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
        }
    } else {
        $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error en la conexión a la base de datos.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
    }
} else {
    $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">No se recibieron todos los parámetros necesarios.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
}

// Redireccionar de nuevo a la página anterior
header("Location: " . $_SERVER['HTTP_REFERER']);
?>
