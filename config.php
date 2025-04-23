<?php
/**
 * ExamHub - Configuración global
 */

// Modo de desarrollo (cambiar a false en producción)
define('DEBUG_MODE', true);

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'examhub');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuración de la aplicación
define('APP_NAME', 'ExamHub');
define('APP_VERSION', '1.0.0');

// Rutas
define('BASE_URL', 'http://localhost/examhub/');
define('UPLOAD_DIR', 'uploads/exams/');

// Límites
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_EXTENSIONS', ['pdf']);

// Configuración de errores según el modo
if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_WARNING);
}