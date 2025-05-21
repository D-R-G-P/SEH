/**
 * @file db_conn.js
 * @description Establece y exporta un pool de conexiones a la base de datos MySQL
 * utilizando mysql2/promise para soportar async/await y mejorar el manejo de concurrencia.
 */

// --- Dependencias ---
const mysql = require('mysql2');
const path = require('path');

// --- Cargar Variables de Entorno ---
// Carga las variables desde un archivo .env ubicado dos niveles arriba del directorio actual.
// ¡Asegúrate de que la ruta a tu archivo .env sea correcta!
require('dotenv').config({ path: path.join(__dirname, '../../.env') });

// --- Configuración del Pool de Conexiones ---
// Lee las credenciales de la base de datos desde las variables de entorno.
const dbConfig = {
    host: process.env.DB_HOST,          // Host de la base de datos (ej: 'localhost', '127.0.0.1')
    port: process.env.DB_PORT || 3306,  // Puerto de la base de datos (default MySQL: 3306)
    user: process.env.DB_USER,          // Usuario de la base de datos
    password: process.env.DB_PASS,      // Contraseña del usuario de la BD
    database: process.env.DB_NAME,      // Nombre de la base de datos a usar
    waitForConnections: true,           // Esperar si todas las conexiones del pool están ocupadas (en lugar de dar error inmediato)
    connectionLimit: 10,                // Número máximo de conexiones en el pool (ajustar según carga esperada y recursos del servidor BD)
    queueLimit: 0,                      // Límite de solicitudes en cola esperando una conexión (0 = sin límite)
    charset: 'utf8mb4'                  // Set de caracteres recomendado para soportar emojis y caracteres especiales
};

// --- Validación de Configuración ---
// Verifica que las variables de entorno esenciales estén definidas.
if (!dbConfig.host || !dbConfig.user || !dbConfig.database) {
    console.error("❌ Error: Faltan variables de entorno esenciales para la conexión a la base de datos (DB_HOST, DB_USER, DB_NAME).");
    console.error("   Asegúrate de que el archivo .env esté correctamente configurado en la ruta:", path.join(__dirname, '../../.env'));
    process.exit(1); // Detener la aplicación si falta configuración crítica
}
// Nota: DB_PASS puede estar vacía si el usuario no tiene contraseña.

// --- Crear el Pool de Conexiones ---
// mysql.createPool() crea un pool que gestiona múltiples conexiones.
const pool = mysql.createPool(dbConfig);

// --- Obtener Versión del Pool con Promesas ---
// pool.promise() envuelve el pool para que sus métodos devuelvan Promesas,
// permitiendo el uso de async/await para las consultas.
const promisePool = pool.promise();

// --- Verificación de Conexión Inicial (Opcional pero útil) ---
// Intenta obtener una conexión del pool para verificar que la configuración es correcta.
promisePool.getConnection()
    .then(connection => {
        console.log("✅ Pool de conexiones a la base de datos creado y conexión inicial exitosa!");
        console.log(`   Conectado a: ${dbConfig.database}@${dbConfig.host}:${dbConfig.port}`);
        connection.release(); // ¡Importante! Liberar la conexión de prueba para devolverla al pool.
    })
    .catch(err => {
        console.error("❌ Error CRÍTICO al conectar con la base de datos usando el pool:");
        console.error(`   Código: ${err.code}`);
        console.error(`   Mensaje: ${err.message}`);
        console.error("   Verifica las credenciales y la accesibilidad de la base de datos en .env");
        process.exit(1); // Detener la aplicación si la conexión inicial falla
    });

// --- Exportar el Pool con Promesas ---
// Otros módulos importarán `promisePool` para realizar consultas a la base de datos.
module.exports = promisePool;
