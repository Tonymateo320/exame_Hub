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

// Obtener información del usuario
$user_id = $_SESSION['user_id'];
$sql = 'SELECT nombre_usuario, nombre_completo, tipo, fecha_registro FROM usuarios WHERE id = ?';
$user = fetch_assoc($sql, [$user_id]);

// Si es profesor, obtener sus exámenes subidos
$exams = [];
if ($_SESSION['user_type'] === 'profesor') {
    $sql = 'SELECT e.id, e.titulo, e.anio, e.fecha_subida, m.nombre_materia 
            FROM examenes e
            JOIN materias m ON e.id_materia = m.id
            WHERE e.subido_por = ?
            ORDER BY e.fecha_subida DESC';
    $exams = fetch_all($sql, [$user_id]);
}

// Procesar cambio de contraseña si se envió el formulario
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validar datos
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_msg = 'Todos los campos son obligatorios.';
    } elseif (strlen($new_password) < 6) {
        $error_msg = 'La nueva contraseña debe tener al menos 6 caracteres.';
    } elseif ($new_password !== $confirm_password) {
        $error_msg = 'Las contraseñas nuevas no coinciden.';
    } else {
        // Verificar contraseña actual
        $sql = 'SELECT contraseña FROM usuarios WHERE id = ?';
        $result = fetch_assoc($sql, [$user_id]);
        
        if (!$result || !password_verify($current_password, $result['contraseña'])) {
            $error_msg = 'La contraseña actual es incorrecta.';
        } else {
            // Actualizar contraseña
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = 'UPDATE usuarios SET contraseña = ? WHERE id = ?';
            $update_result = query($sql, [$hashed_password, $user_id]);
            
            if ($update_result) {
                $success_msg = 'Contraseña actualizada correctamente.';
            } else {
                $error_msg = 'Error al actualizar la contraseña.';
            }
        }
    }
}

// Incluir la cabecera
include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2><i class="fas fa-user-circle me-2"></i>Mi Perfil</h2>
            <a href="index.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Volver
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm fade-in">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-id-card me-2"></i>Información de Usuario</h4>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="avatar mb-3">
                        <?php if ($_SESSION['user_type'] === 'profesor'): ?>
                            <i class="fas fa-chalkboard-teacher fa-5x text-primary"></i>
                        <?php else: ?>
                            <i class="fas fa-user-graduate fa-5x text-primary"></i>
                        <?php endif; ?>
                    </div>
                    <h4><?php echo htmlspecialchars($user['nombre_completo']); ?></h4>
                    <span class="badge <?php echo $_SESSION['user_type'] === 'profesor' ? 'bg-success' : 'bg-info'; ?> mb-2">
                        <?php echo ucfirst($_SESSION['user_type']); ?>
                    </span>
                </div>
                
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <i class="fas fa-user me-2 text-primary"></i>
                        <strong>Usuario:</strong> <?php echo htmlspecialchars($user['nombre_usuario']); ?>
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-calendar-alt me-2 text-primary"></i>
                        <strong>Miembro desde:</strong> <?php echo date('d/m/Y', strtotime($user['fecha_registro'])); ?>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Cambiar contraseña -->
        <div class="card shadow-sm mt-4 fade-in">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-key me-2"></i>Cambiar Contraseña</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error_msg); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success_msg)): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($success_msg); ?>
                    </div>
                <?php endif; ?>
            
                <form method="POST" action="profile.php">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Contraseña Actual</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Nueva Contraseña</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                        <div class="form-text">Mínimo 6 caracteres</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirmar Nueva Contraseña</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" name="change_password" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Actualizar Contraseña
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <?php if ($_SESSION['user_type'] === 'profesor'): ?>
            <div class="card shadow-sm fade-in">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-file-pdf me-2"></i>Mis Exámenes Subidos</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($exams)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>Aún no has subido ningún examen.
                            <a href="upload.php" class="alert-link">Subir un examen ahora</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Título</th>
                                        <th>Materia</th>
                                        <th>Año</th>
                                        <th>Fecha de Subida</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($exams as $exam): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($exam['titulo']); ?></td>
                                            <td>
                                                <span class="badge bg-subject">
                                                    <?php echo htmlspecialchars($exam['nombre_materia']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $exam['anio']; ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($exam['fecha_subida'])); ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="view.php?id=<?php echo $exam['id']; ?>" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Ver">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="download.php?id=<?php echo $exam['id']; ?>" class="btn btn-outline-success" data-bs-toggle="tooltip" title="Descargar">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                    <a href="delete.php?id=<?php echo $exam['id']; ?>" class="btn btn-outline-danger delete-exam" data-bs-toggle="tooltip" title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-3">
                            <a href="upload.php" class="btn btn-success">
                                <i class="fas fa-upload me-2"></i>Subir Nuevo Examen
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="card shadow-sm fade-in">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información de Estudiante</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <p class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Como estudiante, puedes:</p>
                        <ul class="mt-2">
                            <li>Ver y descargar exámenes de todas las materias</li>
                            <li>Filtrar exámenes por materia y año</li>
                            <li>Prepararte mejor para tus evaluaciones</li>
                        </ul>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="index.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-search me-2"></i>Explorar Exámenes
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Incluir el pie de página
include 'includes/footer.php';
?>