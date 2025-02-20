<?php
session_start(); // Inicia la sesión (si aún no se ha iniciado)

if ($_POST['equipId'] === "") {
    $_SESSION['toast_message'] = [
        'message' => 'Error al obtener el ID del equipo.',
        'type' => 'error'
    ];
    header("Location: ../equipos.php");
    exit;
}

if ($_POST['state'] === "") {
    $id = $_POST["equipId"];
    $_SESSION['toast_message'] = [
        'message' => 'Debe indicar un estado para realizar esta acción.',
        'type' => 'error'
    ];
    header("Location: ../equipos.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Verifica que el formulario se ha enviado por el método POST

    // Si no hay errores, procesa el formulario
    $id = $_POST["equipId"];
    $estado = $_POST["state"];
    $problema = $_POST["problemState"];

    if ($problema === "") {
        $problema = "Sin problema reportado";
    }

    // Realiza la conexión a la base de datos (utiliza tu propia lógica para la conexión)
    require_once '../../../../../app/db/db.php';

    $db = new DB();
    $pdo = $db->connect();

    try {

        // Prepara la consulta SQL para la inserción
        $stmt = $pdo->prepare("UPDATE equipos SET estado = ?, problema = ? WHERE id = ?");
        $stmt->execute([$estado, $problema, $id]);

        // Cierra la conexión a la base de datos
        $pdo = null;

        // Almacena un mensaje de éxito en la sesión y redirige a una página de éxito
        $_SESSION['toast_message'] = [
            'message' => 'Estado actualizado correctamente',
            'type' => 'success'
        ];
        header("Location: ../equipos.php");
        exit;
    } catch (PDOException $e) {
        // Si hay un error en la base de datos, almacena el mensaje de error y redirige al formulario
        $_SESSION['toast_message'] = [
            'message' => 'Error al conectar a la base de datos: ' . $e->getMessage(),
            'type' => 'error'
        ];
        header("Location: ../equipos.php");
        exit;
    }
} else {
    // Si alguien trata de acceder directamente a este script sin enviar el formulario, redirige al formulario
    header("Location: ../equipos.php");
    exit;
}
