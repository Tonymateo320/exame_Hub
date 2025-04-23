<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está autenticado y es profesor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'profesor') {
    $_SESSION['message'] = 'No tienes permiso para eliminar exámenes.';
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php');
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

// Verificar si el examen existe y pertenece al profesor actual
$sql = 'SELECT id, archivo_ruta, subido_por FROM examenes WHERE id = ?';
$exam = fetch_assoc($sql, [$exam_id]);

if (!$exam) {
    $_SESSION['message'] = 'El examen solicitado no existe.';
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php');
    exit();
}

// Verificar si el profesor actual es el propietario del examen
if ($exam['subido_por'] != $_SESSION['user_id']) {
    $_SESSION['message'] = 'Solo puedes eliminar tus propios exámenes.';
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php');
    exit();
}

// Eliminar el archivo físico si existe
if (file_exists($exam['archivo_ruta'])) {
    unlink($exam['archivo_ruta']);
}

// Eliminar el registro de la base de datos
$sql = 'DELETE FROM examenes WHERE id = ?';
$result = query($sql, [$exam_id]);

if ($result) {
    $_SESSION['message'] = 'Examen eliminado correctamente.';
    $_SESSION['message_type'] = 'success';
} else {
    $_SESSION['message'] = 'Error al eliminar el examen.';
    $_SESSION['message_type'] = 'danger';
}

// Redirigir a la página principal
header('Location: index.php');
exit();