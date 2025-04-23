<?php
// Incluir archivo de configuración si existe
if (file_exists(dirname(__FILE__) . '/../config.php')) {
    require_once dirname(__FILE__) . '/../config.php';
}

// Configuración de la base de datos
$host = defined('DB_HOST') ? DB_HOST : 'localhost';
$db_name = defined('DB_NAME') ? DB_NAME : 'examhub';
$username = defined('DB_USER') ? DB_USER : 'root';
$password = defined('DB_PASS') ? DB_PASS : '';
$charset = 'utf8mb4';

// Opciones de PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Cadena de conexión DSN
$dsn = "mysql:host=$host;dbname=$db_name;charset=$charset";

// Conectar a la base de datos
try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // Si hay un error, mostrar mensaje y terminar
    die('Error de conexión: ' . $e->getMessage());
}

// Función para realizar consultas seguras
function query($sql, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

// Función para obtener el ID del último registro insertado
function last_insert_id() {
    global $pdo;
    return $pdo->lastInsertId();
}

// Función para obtener un solo registro
function fetch_assoc($sql, $params = []) {
    $stmt = query($sql, $params);
    return $stmt->fetch();
}

// Función para obtener múltiples registros
function fetch_all($sql, $params = []) {
    $stmt = query($sql, $params);
    return $stmt->fetchAll();
}