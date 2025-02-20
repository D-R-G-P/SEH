<?php
session_start(); // Inicia la sesión (si aún no se ha iniciado)

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Verifica que el formulario se ha enviado por el método POST

    // Si no hay errores, procesa el formulario
    $licenciaDni = $_POST["licenciaDniHidden"];
    $licenciaDesde = $_POST["licenciaDesde"];
    $licenciaHasta = $_POST["licenciaHasta"];
    $licenciaTipo = $_POST["licenciaTipo"];

    // Realiza la conexión a la base de datos (utiliza tu propia lógica para la conexión)
    require_once '../../../../../app/db/db.php';

    $db = new DB();
    $pdo = $db->connect();

    try {

        // Prepara la consulta SQL para la inserción
        $stmt = $pdo->prepare("INSERT INTO licencias (dni, fecha_desde, fecha_hasta, tipo_licencia) VALUES (?, ?, ?, ?)");
        $stmt->execute([$licenciaDni, $licenciaDesde, $licenciaHasta, $licenciaTipo]);

        // Cierra la conexión a la base de datos
        $pdo = null;

        // Almacena un mensaje de éxito en la sesión y redirige a una página de éxito
        $_SESSION['toast_message'] = [
            'message' => 'Licencia asignada correctamente',
            'type' => 'success'
        ];
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    } catch (PDOException $e) {
        // Si hay un error en la base de datos, almacena el mensaje de error y redirige al formulario
        $_SESSION['toast_message'] = [
            'message' => 'Error al conectar a la base de datos: ' . $e->getMessage(),
            'type' => 'error'
        ];
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }
} else {
    // Si alguien trata de acceder directamente a este script sin enviar el formulario, redirige al formulario
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}
