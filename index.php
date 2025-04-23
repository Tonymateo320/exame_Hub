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

// Obtener filtros desde la URL
$subject_filter = isset($_GET['subject']) ? intval($_GET['subject']) : 0;
$year_filter = isset($_GET['year']) ? intval($_GET['year']) : 0;

// Preparar la consulta base para los exámenes
$sql = 'SELECT e.id, e.titulo, e.anio, e.archivo_ruta, e.fecha_subida, 
               m.nombre_materia, u.nombre_completo as profesor, e.subido_por
        FROM examenes e
        JOIN materias m ON e.id_materia = m.id
        JOIN usuarios u ON e.subido_por = u.id
        WHERE 1=1';
$params = [];

// Aplicar filtros si existen
if ($subject_filter > 0) {
    $sql .= ' AND e.id_materia = ?';
    $params[] = $subject_filter;
}

if ($year_filter > 0) {
    $sql .= ' AND e.anio = ?';
    $params[] = $year_filter;
}

// Ordenar por fecha de subida (más reciente primero)
$sql .= ' ORDER BY e.fecha_subida DESC';

// Ejecutar la consulta
$exams = fetch_all($sql, $params);

// Obtener lista de materias para el filtro
$subjects = fetch_all('SELECT id, nombre_materia FROM materias ORDER BY nombre_materia');

// Obtener años únicos para el filtro
$years_query = fetch_all('SELECT DISTINCT anio FROM examenes ORDER BY anio DESC');
$years = array_column($years_query, 'anio');

// Incluir la cabecera
include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="filter-section fade-in">
            <h4><i class="fas fa-filter me-2"></i>Filtrar Exámenes</h4>
            <form method="GET" action="index.php" class="row g-3">
                <div class="col-md-5">
                    <label for="subject-filter" class="form-label">Materia</label>
                    <select class="form-select" id="subject-filter" name="subject">
                        <option value="0">Todas las materias</option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?php echo $subject['id']; ?>" <?php echo ($subject_filter == $subject['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($subject['nombre_materia']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label for="year-filter" class="form-label">Año</label>
                    <select class="form-select" id="year-filter" name="year">
                        <option value="0">Todos los años</option>
                        <?php foreach ($years as $year): ?>
                            <option value="<?php echo $year; ?>" <?php echo ($year_filter == $year) ? 'selected' : ''; ?>>
                                <?php echo $year; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Buscar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="alerts-container">
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php 
        // Limpiar el mensaje después de mostrarlo
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        ?>
    <?php endif; ?>
</div>

<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="fas fa-book me-2"></i>Exámenes Disponibles</h2>
    </div>
    <?php if ($is_teacher): ?>
    <div class="col-md-4 text-end">
        <a href="upload.php" class="btn btn-success">
            <i class="fas fa-upload me-2"></i>Subir Nuevo Examen
        </a>
    </div>
    <?php endif; ?>
</div>

<?php if (empty($exams)): ?>
    <div class="alert alert-info fade-in" role="alert">
        <i class="fas fa-info-circle me-2"></i>
        No se encontraron exámenes con los filtros seleccionados.
        <?php if ($subject_filter > 0 || $year_filter > 0): ?>
            <a href="index.php" class="alert-link">Ver todos los exámenes</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach ($exams as $index => $exam): ?>
            <div class="col fade-in">
                <div class="card h-100 exam-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title"><?php echo htmlspecialchars($exam['titulo']); ?></h5>
                            <span class="badge bg-year"><?php echo $exam['anio']; ?></span>
                        </div>
                        <div class="text-center my-3">
                            <i class="fas fa-file-pdf exam-icon"></i>
                        </div>
                        <p class="card-text mb-1">
                            <i class="fas fa-book me-2"></i>
                            <span class="badge bg-subject"><?php echo htmlspecialchars($exam['nombre_materia']); ?></span>
                        </p>
                        <p class="card-text exam-meta mb-1">
                            <i class="fas fa-chalkboard-teacher me-2"></i>
                            Profesor: <?php echo htmlspecialchars($exam['profesor']); ?>
                        </p>
                        <p class="card-text exam-meta">
                            <i class="fas fa-calendar-alt me-2"></i>
                            Subido: <?php echo date('d/m/Y', strtotime($exam['fecha_subida'])); ?>
                        </p>
                    </div>
                    <div class="card-footer bg-transparent">
                        <div class="d-flex justify-content-between">
                            <a href="view.php?id=<?php echo $exam['id']; ?>" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye me-1"></i>Ver
                            </a>
                            <a href="download.php?id=<?php echo $exam['id']; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-download me-1"></i>Descargar
                            </a>
                            <?php if ($is_teacher && $_SESSION['user_id'] == $exam['subido_por']): ?>
                            <a href="delete.php?id=<?php echo $exam['id']; ?>" class="btn btn-danger btn-sm delete-exam">
                                <i class="fas fa-trash me-1"></i>Eliminar
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
// Incluir el pie de página
include 'includes/footer.php';
?>