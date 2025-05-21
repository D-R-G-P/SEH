<?php

function cargarEntorno($archivo)
{
    if (!file_exists($archivo)) {
        die("El archivo .env no existe.");
    }

    $lineas = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lineas as $linea) {
        if (strpos(trim($linea), '#') === 0) continue; // Ignorar comentarios
        list($clave, $valor) = explode('=', $linea, 2);
        $_ENV[trim($clave)] = trim($valor);
    }
}

// Cargar variables de entorno
cargarEntorno(dirname(__DIR__, 2) . '/.env'); // ✅ Sube dos niveles hasta SGH/

class DB
{
    private $host;
    private $port;
    private $db;
    private $user;
    private $password;
    private $charset;

    public function __construct()
    {
        $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        $this->port = $_ENV['DB_PORT'] ?? '3306'; // Puerto por defecto MySQL
        $this->db = $_ENV['DB_NAME'] ?? 'seh';
        $this->user = $_ENV['DB_USER'] ?? 'root';
        $this->password = $_ENV['DB_PASS'] ?? 'root';
        $this->charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';
    }

    function connect()
    {
        try {
            $connection = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db . ";charset=" . $this->charset;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($connection, $this->user, $this->password, $options);
            return $pdo;
        } catch (PDOException $e) {
            die('Error de conexión: ' . $e->getMessage());
        }
    }
}
