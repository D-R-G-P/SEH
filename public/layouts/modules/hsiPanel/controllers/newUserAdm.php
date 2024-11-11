<?php
session_start(); // Iniciar la sesión

// Verificar si se recibieron los datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar si se recibieron todos los campos necesarios
    if (isset($_POST['dni'], $_POST['servicio'], $_POST['email'], $_POST['phone'], $_POST['permisos'])) {
        // Incluir el archivo de conexión a la base de datos
        require_once '../../../../../app/db/db.php';

        // Crear una instancia de la clase DB para la conexión a la base de datos
        $db = new DB();
        $pdo = $db->connect();

        // Obtener los datos del formulario
        $dni = $_POST['dni'];
        $servicio = $_POST['servicio'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $permisos = $_POST['permisos'];

        // Verificar si el DNI ya existe en la base de datos
        $stmt_check_dni = $pdo->prepare("SELECT dni FROM hsi WHERE dni = :dni");
        $stmt_check_dni->execute([':dni' => $dni]);
        $existing_dni = $stmt_check_dni->fetchColumn();

        if ($existing_dni) {
            $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis" style="text-align: center;">El agente ya cuenta con usuario, solicite la reactivación mediante pedido.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 25000);} window.addEventListener("DOMContentLoaded", () => { loadInfo("' . $dni . '"); });</script>';
            header("Location: ../hsiAdmin.php");
            exit();
        }

        try {
            // Documentos por defecto (como JSON)
            $documentos = json_encode([
                ["documento" => "Copia de DNI", "activo" => "no"],
                ["documento" => "Copia de matrícula profesional", "activo" => "no"],
                ["documento" => "Solicitud de alta de usuario para HSI (ANEXO I)", "activo" => "no"],
                ["documento" => "ANEXO II", "activo" => "no"], 
                ["documento" => "Prescriptor", "activo" => "no"]
            ]);

            // Validación del JSON antes de la inserción
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Error en la creación del JSON para el campo documentos: " . json_last_error_msg());
            }

            // Crear una consulta preparada para insertar los datos en la tabla principal
            $query = "INSERT INTO hsi (dni, servicio, mail, telefono, observaciones, estado, new, fecha_solicitud, documentos) 
                      VALUES (:dni, :servicio, :email, :phone, :observaciones, :estado, :new, :fecha_solicitud, :documentos)";
            
            // Parámetros de la consulta
            $params = [
                ':dni' => $dni,
                ':servicio' => $servicio,
                ':email' => $email,
                ':phone' => $phone,
                ':observaciones' => 'Usuario pendiente de aprobación',
                ':estado' => 'working',
                ':new' => 'no',
                ':fecha_solicitud' => date('Y-m-d'),
                ':documentos' => $documentos
            ];

            // Ejecutar la consulta para registrar el usuario en la tabla hsi
            $stmt = $pdo->prepare($query);
            if ($stmt->execute($params)) {
                // Insertar permisos en la tabla usuarios_roles_hsi
                $query_roles = "INSERT INTO usuarios_roles_hsi (dni, id_rol) VALUES (:dni, :id_rol)";
                $stmt_roles = $pdo->prepare($query_roles);

                foreach ($permisos as $permiso_id) {
                    $stmt_roles->execute([':dni' => $dni, ':id_rol' => $permiso_id]);
                }

                // Registro exitoso, mostrar un mensaje de éxito
                $_SESSION['success_message'] = '<div class="notisContent"><div class="notis" id="notis" style="text-align: center;">Usuario solicitado, verifique su bandeja</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
                header("Location: ../hsiAdmin.php");
                exit();
            } else {
                throw new Exception('Error al registrar el usuario en la tabla hsi.');
            }
        } catch (PDOException $e) {
            // Capturar y mostrar el error específico de base de datos
            echo "Error de base de datos: " . $e->getMessage();
        } catch (Exception $e) {
            // Capturar cualquier otro error general
            echo "Error: " . $e->getMessage();
        }
    } else {
        // No se recibieron todos los campos necesarios, mostrar un mensaje de error
        $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Por favor verifique todos los datos obligatorios.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
        header("Location: ../hsiAdmin.php");
        exit(); // Finalizar el script después de la redirección
    }
}
