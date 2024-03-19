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

        // Procesar los permisos seleccionados
        $permisos_json = []; // Array para almacenar los permisos

        // Verificar si $_POST['permisos'] es un array (más de una selección) o un único valor (una selección)
        if (is_array($_POST['permisos'])) {
            // Si hay múltiples selecciones, procesar cada una individualmente
            foreach ($_POST['permisos'] as $permiso) {
                // Agregar cada permiso seleccionado al array de permisos
                $permisos_json[] = [
                    "permiso" => $permiso,
                    "activo" => "si" // Activar los permisos seleccionados
                ];
            }
        } else {
            // Si hay una única selección, agregarla al array de permisos
            $permisos_json[] = [
                "permiso" => $_POST['permisos'],
                "activo" => "si" // Activar el permiso seleccionado
            ];
        }

        // Convertir el array de permisos en formato JSON
        $permisos_json = json_encode($permisos_json);

        // Crear una consulta preparada para insertar los datos en la base de datos
        $query = "INSERT INTO hsi (dni, servicio, mail, telefono, permisos, documentos, observaciones, estado, new, fecha_solicitud) VALUES (:dni, :servicio, :email, :phone, :permisos, :documentos, :observaciones, :estado, :new, :fecha_solicitud)";

        // Documentos por defecto
        $documentos = json_encode([
            ["documento" => "Copia de DNI", "activo" => "no"],
            ["documento" => "Copia de matrícula profesional", "activo" => "no"],
            ["documento" => "Solicitud de alta de usuario para HSI (ANEXO I)", "activo" => "no"],
            ["documento" => "Declaración Jurada - Convenio de confidecialidad usuarios HSI (ANEXO II)", "activo" => "no"]
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
            header("Location: ../hsi.php");
            exit(); // Finalizar el script después de la redirección
        } else {
            // Error al registrar el usuario, mostrar un mensaje de error
            $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error al registrar el usuario.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
            header("Location: ../hsi.php");
            exit(); // Finalizar el script después de la redirección
        }
    } else {
        // No se recibieron todos los campos necesarios, mostrar un mensaje de error
        $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Por favor verifique todos los datos obligatorios.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
        header("Location: ../hsi.php");
        exit(); // Finalizar el script después de la redirección
    }
}
