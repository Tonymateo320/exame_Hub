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
$sql = 'SELECT e.id, e.titulo, e.anio, e.archivo_ruta, e.fecha_subida, 
               m.nombre_materia, u.nombre_completo as profesor, u.id as profesor_id
        FROM examenes e
        JOIN materias m ON e.id_materia = m.id
        JOIN usuarios u ON e.subido_por = u.id
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

// Incluir la cabecera
include 'includes/header.php';
?>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card shadow-sm fade-in">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0"><i class="fas fa-file-pdf me-2"></i><?php echo htmlspecialchars($exam['titulo']); ?></h3>
            </div>
            
            <div class="card-body p-4">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p><strong><i class="fas fa-book me-2"></i>Materia:</strong> 
                            <span class="badge bg-subject"><?php echo htmlspecialchars($exam['nombre_materia']); ?></span>
                        </p>
                        <p><strong><i class="fas fa-calendar-alt me-2"></i>Año:</strong> 
                            <span class="badge bg-year"><?php echo $exam['anio']; ?></span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong><i class="fas fa-chalkboard-teacher me-2"></i>Subido por:</strong> 
                            <?php echo htmlspecialchars($exam['profesor']); ?>
                        </p>
                        <p><strong><i class="fas fa-clock me-2"></i>Fecha de subida:</strong> 
                            <?php echo date('d/m/Y H:i', strtotime($exam['fecha_subida'])); ?>
                        </p>
                    </div>
                </div>
                
                <div class="ratio ratio-16x9 mb-4">
                    <?php if (file_exists($exam['archivo_ruta'])): ?>
                        <iframe src="<?php echo $exam['archivo_ruta']; ?>" allowfullscreen></iframe>
                    <?php else: ?>
                        <div class="d-flex justify-content-center align-items-center bg-light text-center p-5">
                            <div>
                                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                                <h4>El archivo PDF no está disponible</h4>
                                <p class="text-muted">El archivo puede haber sido eliminado o movido.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Volver
                    </a>
                    <div>
                        <a href="download.php?id=<?php echo $exam['id']; ?>" class="btn btn-primary">
                            <i class="fas fa-download me-2"></i>Descargar PDF
                        </a>
                        <?php if ($is_teacher && $_SESSION['user_id'] == $exam['profesor_id']): ?>
                        <a href="delete.php?id=<?php echo $exam['id']; ?>" class="btn btn-danger delete-exam">
                            <i class="fas fa-trash me-2"></i>Eliminar
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir el pie de página
include 'includes/footer.php';
?>