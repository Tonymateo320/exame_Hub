<?php
/**
 * Script de inicialización para ExamHub
 * Este script se ejecuta una sola vez para configurar la aplicación
 */

// Incluir la conexión a la base de datos
require_once 'includes/db.php';

// Verificar si hay materias en la base de datos
$check_query = "SELECT COUNT(*) as count FROM materias";
$result = fetch_assoc($check_query);

// Si no hay materias, agregar las materias predeterminadas
if ($result['count'] == 0) {
    $materias = [
        'Matemáticas',
        'Programación',
        'Bases de Datos',
        'Sistemas Operativos',
        'Redes',
        'Inteligencia Artificial',
        'Desarrollo Web',
        'Algoritmos y Estructuras de Datos',
        'Ingeniería de Software',
        'Seguridad Informática',
        'Física',
        'Química',
        'Biología',
        'Historia',
        'Economía',
        'Contabilidad',
        'Administración',
        'Derecho',
        'Inglés',
        'Comunicación'
    ];
    
    // Insertar cada materia
    $insert_query = "INSERT INTO materias (nombre_materia) VALUES (?)";
    foreach ($materias as $materia) {
        query($insert_query, [$materia]);
    }
    
    echo "<p>Se han agregado " . count($materias) . " materias a la base de datos.</p>";
} else {
    echo "<p>Las materias ya están configuradas en la base de datos.</p>";
}

// Crear el directorio de subidas si no existe
$upload_dir = 'uploads/exams/';
if (!file_exists($upload_dir)) {
    if (mkdir($upload_dir, 0777, true)) {
        echo "<p>Se ha creado el directorio de subidas: {$upload_dir}</p>";
    } else {
        echo "<p style='color: red;'>Error: No se pudo crear el directorio de subidas: {$upload_dir}</p>";
    }
} else {
    echo "<p>El directorio de subidas ya existe: {$upload_dir}</p>";
}

// Verificar permisos del directorio de subidas
if (is_writable($upload_dir)) {
    echo "<p>El directorio de subidas tiene permisos de escritura correctos.</p>";
} else {
    echo "<p style='color: red;'>Error: El directorio de subidas no tiene permisos de escritura. Por favor, ejecute: chmod 777 {$upload_dir}</p>";
}

// Mostrar mensaje de éxito
echo "<p>Inicialización completada. Ahora puede <a href='index.php'>acceder al sistema</a>.</p>";

// Crear un usuario administrador predeterminado si no existe ningún profesor
$check_admin = "SELECT COUNT(*) as count FROM usuarios WHERE tipo = 'profesor'";
$admin_result = fetch_assoc($check_admin);

if ($admin_result['count'] == 0) {
    $admin_username = 'admin';
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $admin_name = 'Administrador';
    
    $insert_admin = "INSERT INTO usuarios (nombre_usuario, contraseña, nombre_completo, tipo) VALUES (?, ?, ?, 'profesor')";
    query($insert_admin, [$admin_username, $admin_password, $admin_name]);
    
    echo "<p>Se ha creado un usuario administrador por defecto:</p>";
    echo "<ul>";
    echo "<li><strong>Usuario:</strong> admin</li>";
    echo "<li><strong>Contraseña:</strong> admin123</li>";
    echo "</ul>";
    echo "<p style='color: red;'>¡Importante! Cambie la contraseña después de iniciar sesión por primera vez.</p>";
}