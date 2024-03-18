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
    $_SESSION['success_message'] = "ID: $id, Sistema: $sistema, Estado: $estado"; // Mensaje de éxito

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

                $_SESSION['success_message'] = '<div class="notisContent"><div class="notis" id="notis" style="justify-content: center;">Acceso a "'.$sistema.'" actualizado correctamente</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
            } else {
                $_SESSION['warning_message'] = "No se encontraron resultados para el ID proporcionado: $id"; // Mensaje de advertencia
            }
        } catch (PDOException $e) {
            $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error al actualizar el JSON: ' . $e->getMessage() . '.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
        }
    } else {
        $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error en la conexion a la base de datos.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
    }
} else {
    $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error al conectar a la base de datos' . $e->getMessage() . '.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
}

// Redireccionar de nuevo a la página anterior
header("Location: " . $_SERVER['HTTP_REFERER']);
?>
