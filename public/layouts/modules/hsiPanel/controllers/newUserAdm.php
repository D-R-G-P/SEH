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

        // Verificar si el DNI ya existe en la base de datos
        $stmt_check_dni = $pdo->prepare("SELECT dni FROM hsi WHERE dni = :dni");
        $stmt_check_dni->execute([':dni' => $dni]);
        $existing_dni = $stmt_check_dni->fetchColumn();

        if ($existing_dni) {
            $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis" style="text-align: center;">El agente ya cuenta con usuario, solicite la reactivación mediante pedido.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 25000);} window.addEventListener("DOMContentLoaded", () => { loadInfo("' . $dni . '"); });</script>';
            header("Location: ../hsiAdmin.php");
            exit(); // Finalizar el script después de la redirección
        }

        // Obtener el JSON de permisos existente
        $permisos_json = '[
            {
                "permiso": "Especialista Médix",
                "activo": "no"
            },
            {
                "permiso": "Profesional de la Salud",
                "activo": "no"
            },
            {
                "permiso": "Prescriptor",
                "activo": "no"
            },
            {
                "permiso": "Administrativx",
                "activo": "no"
            },
            {
                "permiso": "Enfermero",
                "activo": "no"
            },
            {
                "permiso": "Enfermerx Adultx Mayor",
                "activo": "no"
            },
            {
                "permiso": "Administrador de Agenda",
                "activo": "no"
            },
            {
                "permiso": "Especialista odontológico",
                "activo": "no"
            },
            {
                "permiso": "Administrador de Camas",
                "activo": "no"
            },
            {
                "permiso": "Personal de Imágenes",
                "activo": "no"
            },
            {
                "permiso": "Personal de Laboratorio",
                "activo": "no"
            },
            {
                "permiso": "Personal de Farmacia",
                "activo": "no"
            },
            {
                "permiso": "Personal de Estadística",
                "activo": "no"
            },
            {
                "permiso": "Administrador institucional",
                "activo": "no"
            }
        ]';

        // Decodificar el JSON de permisos
        $permisos_array = json_decode($permisos_json, true);

        // Procesar los permisos seleccionados
        foreach ($permisos_array as &$permiso) {
            // Verificar si el permiso está entre los seleccionados
            if (in_array($permiso['permiso'], $_POST['permisos'])) {
                $permiso['activo'] = "si"; // Establecer el permiso como activo
            } else {
                $permiso['activo'] = "no"; // Establecer el permiso como inactivo
            }
        }

        // Convertir el array de permisos actualizado en formato JSON
        $permisos_json = json_encode($permisos_array);

        // Crear una consulta preparada para insertar los datos en la base de datos
        $query = "INSERT INTO hsi (dni, servicio, mail, telefono, permisos, documentos, observaciones, estado, new, fecha_solicitud) VALUES (:dni, :servicio, :email, :phone, :permisos, :documentos, :observaciones, :estado, :new, :fecha_solicitud)";

        // Documentos por defecto
        $documentos = json_encode([
            ["documento" => "Copia de DNI", "activo" => "no"],
            ["documento" => "Copia de matrícula profesional", "activo" => "no"],
            ["documento" => "Solicitud de alta de usuario para HSI (ANEXO I)", "activo" => "no"],
            ["documento" => "Declaración Jurada - Convenio de confidecialidad usuarios HSI (ANEXO II)", "activo" => "no"],
            ["documento" => "Declaración Jurada - Usuario prescriptor", "activo" => "no"]
        ]);

        // Parámetros de la consulta preparada
        $params = [
            ':dni' => $dni,
            ':servicio' => $servicio,
            ':email' => $email,
            ':phone' => $phone,
            ':permisos' => $permisos_json,
            ':documentos' => $documentos,
            ':observaciones' => 'Usuario pendiente de aprobación',
            ':estado' => 'working',
            ':new' => 'no',
            ':fecha_solicitud' => date('Y-m-d')
        ];

        // Ejecutar la consulta preparada para registrar el usuario
        $stmt = $pdo->prepare($query);
        if ($stmt->execute($params)) {
            // Registro exitoso, mostrar un mensaje de éxito
            $_SESSION['success_message'] = '<div class="notisContent"><div class="notis" id="notis" style="text-align: center;">Usuario solicitado, verifique su bandeja</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
            header("Location: ../hsiAdmin.php");
            exit(); // Finalizar el script después de la redirección
        } else {
            // Error al registrar el usuario, mostrar un mensaje de error
            $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error al registrar el usuario.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
            header("Location: ../hsiAdmin.php");
            exit(); // Finalizar el script después de la redirección
        }
    } else {
        // No se recibieron todos los campos necesarios, mostrar un mensaje de error
        $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Por favor verifique todos los datos obligatorios.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
        header("Location: ../hsiAdmin.php");
        exit(); // Finalizar el script después de la redirección
    }
}