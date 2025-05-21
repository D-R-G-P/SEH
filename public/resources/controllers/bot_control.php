<?php
header('Content-Type: application/json');

$botPath = realpath(__DIR__ . "/../../../app/whatsapp_bot"); // Ruta dinámica
$action = $_GET['action'] ?? '';

if ($action === 'start') {
    $command = "cd $botPath && start /B node bot.js > bot_log.txt 2>&1";
    exec($command, $output, $return_var);
    echo json_encode(["status" => "Bot iniciado.", "output" => $output, "error_code" => $return_var]);
    exit;
}

if ($action === 'stop') {
    exec("taskkill /F /IM node.exe", $output, $return_var);
    echo json_encode(["status" => "Bot detenido.", "output" => $output, "error_code" => $return_var]);
    exit;
}

if ($action === 'restart') {
    exec("taskkill /F /IM node.exe", $output, $return_var);
    sleep(2);
    $command = "cd $botPath && start /B node bot.js > bot_log.txt 2>&1";
    exec($command, $output, $return_var);
    echo json_encode(["status" => "Bot reiniciado.", "output" => $output, "error_code" => $return_var]);
    exit;
}

if ($action === 'status') {
    exec("tasklist | findstr node.exe", $output, $return_var);
    $running = !empty($output);

    // Verificar si existe el QR (si el bot está esperando escaneo)
    $qrPath = __DIR__ . "/../../../app/whatsapp_bot/qrcodes/qr.png"; 
    $waitingForQR = file_exists($qrPath);

    // Determinar el estado
    $status = $running ? ($waitingForQR ? "waiting_qr" : "active") : "inactive";

    echo json_encode(["status" => $status]);
    exit;
}


if ($action === 'check_qr') {
    $qrPath = $botPath . "/qrcodes/qr.png"; // Ruta del QR dentro de la carpeta del bot
    echo json_encode(["exists" => file_exists($qrPath)]);
    exit;
}

echo json_encode(["error" => "Acción no válida"]);
exit;
