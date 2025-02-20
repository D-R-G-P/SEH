<?php
session_start(); // Iniciar la sesión
// Verificar si se recibieron los archivos
if (isset($_POST['docsDniHidden'])) {
    // Obtener el DNI del input hidden
    $dni = $_POST['docsDniHidden'];
    $servicio = $_POST['docsServicio'];

    // Directorio de destino para los documentos
    $directorio_destino = '../../../../../app/hsiDocs/';

    // Procesar cada archivo y moverlo al directorio de destino
    $archivos_subidos = [];
    foreach ($_FILES as $key => $archivo) {
        // Obtener el nombre del archivo y su extensión
        $nombre_archivo = basename($archivo['name']);
        $extension = pathinfo($nombre_archivo, PATHINFO_EXTENSION);

        // Generar el nuevo nombre del archivo con el formato: DNI_NOMBREARCHIVO
        $nuevo_nombre = $dni . '-' . $key . '.' . $extension;

        // Mover el archivo al directorio de destino
        if (move_uploaded_file($archivo['tmp_name'], $directorio_destino . $nuevo_nombre)) {
            // Archivo subido correctamente
            $archivos_subidos[$key] = $nuevo_nombre; // Usar la clave del archivo como índice en el array de archivos subidos
        } else {
            // Error al subir el archivo
            $archivos_subidos[$key] = 'Error al subir ' . $nombre_archivo;
        }
    }

    // Actualizar solo los archivos subidos en el registro de la base de datos
    require_once '../../../../../app/db/db.php';
    $db = new DB();
    $pdo = $db->connect();

    // Consultar el registro correspondiente en la base de datos
    $query = "SELECT * FROM hsi WHERE dni = :dni";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':dni' => $dni]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Obtener el JSON actual de documentos
    $documentos_array = json_decode($row['documentos'], true);

    // Actualizar el estado de los archivos subidos a "pendiente"
    foreach ($archivos_subidos as $key => $archivo_subido) {
        // Obtener el tamaño del archivo en KB
        $tamano_archivo_kb = $_FILES[$key]['size'] / 2;

        // Verificar si el tamaño del archivo es mayor a 100KB
        if ($tamano_archivo_kb > 100) {
            // Buscar y actualizar el documento correspondiente en el JSON
            foreach ($documentos_array as &$documento) {
                switch ($key) {
                    case 'docsDni':
                        $documento_nombre = "Copia de DNI";
                        break;
                    case 'docsMatricula':
                        $documento_nombre = "Copia de matrícula profesional";
                        break;
                    case 'docsAnexoI':
                        $documento_nombre = "Solicitud de alta de usuario para HSI (ANEXO I)";
                        break;
                    case 'docsAnexoII':
                        $documento_nombre = "Declaración Jurada - Convenio de confidecialidad usuarios HSI (ANEXO II)";
                        break;
                    case 'docsPrescriptor':
                        $documento_nombre = "Declaración Jurada - Usuario prescriptor";
                        break;
                    default:
                        $documento_nombre = "";
                }
                // Verificar si el documento coincide con el nombre del archivo subido
                if ($documento['documento'] === $documento_nombre) {
                    $documento['activo'] = 'pendiente';
                    break; // Salir del bucle interno si se actualiza el documento
                }
            }
        }
    }

    // Convertir el JSON actualizado de documentos a formato JSON
    $documentos_json = json_encode($documentos_array);

    // Actualizar el registro en la base de datos con el JSON actualizado
    $update_query = "UPDATE hsi SET documentos = :documentos WHERE dni = :dni";
    $update_stmt = $pdo->prepare($update_query);
    $update_stmt->execute([':documentos' => $documentos_json, ':dni' => $dni]);

    // Registro exitoso, mostrar un mensaje de éxito
    $_SESSION['toast_message'] = [
        'message' => 'Archivos subidos exitosamente, aguarde a su verificación.',
        'type' => 'success'
    ];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit(); // Finalizar el script después de la redirección
} else {
    // Error al registrar el usuario, mostrar un mensaje de error
    $_SESSION['toast_message'] = [
        'message' => 'Por favor, suba todos los archivos obligatorios.',
        'type' => 'error'
    ];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit(); // Finalizar el script después de la redirección
}
