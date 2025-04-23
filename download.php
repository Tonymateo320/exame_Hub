<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Incluir la conexión a la base de datos
require_once 'includes/db.php';

// Verificar si se proporcionó un ID válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = 'ID de examen no válido.';
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php');
    exit();
}

$exam_id = intval($_GET['id']);

// Obtener información del examen
$sql = 'SELECT e.id, e.titulo, e.archivo_ruta, m.nombre_materia 
        FROM examenes e 
        JOIN materias m ON e.id_materia = m.id 
        WHERE e.id = ?';
$exam = fetch_assoc($sql, [$exam_id]);

// Verificar si el examen existe
if (!$exam) {
    $_SESSION['message'] = 'El examen solicitado no existe.';
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php');
    exit();
}

// Verificar si el archivo existe
if (!file_exists($exam['archivo_ruta'])) {
    $_SESSION['message'] = 'El archivo del examen no está disponible.';
    $_SESSION['message_type'] = 'warning';
    header('Location: index.php');
    exit();
}

// Preparar el nombre de archivo para descargar
$file_name = slugify($exam['titulo'] . ' - ' . $exam['nombre_materia']) . '.pdf';

// Establecer las cabeceras para la descarga
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $file_name . '"');
header('Content-Length: ' . filesize($exam['archivo_ruta']));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

// Verificar el tipo MIME del archivo
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $exam['archivo_ruta']);
finfo_close($finfo);

// Asegurarse de que es un PDF
if ($mime_type === 'application/pdf') {
    // Enviar el archivo al navegador
    readfile($exam['archivo_ruta']);
    exit();
} else {
    // Si no es un PDF, mostrar error
    $_SESSION['message'] = 'El archivo no es un PDF válido.';
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php');
    exit();
}

// Función para generar un slug para el nombre del archivo
function slugify($text) {
    // Convertir a minúsculas
    $text = strtolower($text);
    
    // Reemplazar caracteres no alfanuméricos con guiones
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    
    // Eliminar guiones al principio y al final
    $text = trim($text, '-');
    
    return $text;
}