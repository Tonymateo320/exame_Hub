<?php
// Iniciar sesión
session_start();

// Si el usuario ya está autenticado, redirigir a la página principal
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Incluir la conexión a la base de datos
require_once 'includes/db.php';

// Variables para almacenar los valores del formulario y errores
$username = '';
$fullname = '';
$user_type = '';
$error_msg = '';
$success_msg = '';

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener y limpiar los datos del formulario con verificación
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $fullname = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $user_type = isset($_POST['user_type']) ? $_POST['user_type'] : '';
    
    // Validación de los datos
    if (empty($username) || empty($fullname) || empty($password) || empty($user_type)) {
        $error_msg = 'Todos los campos son obligatorios.';
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $error_msg = 'El nombre de usuario debe tener entre 3 y 50 caracteres.';
    } elseif (strlen($password) < 6) {
        $error_msg = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($password !== $confirm_password) {
        $error_msg = 'Las contraseñas no coinciden.';
    } elseif ($user_type !== 'estudiante' && $user_type !== 'profesor') {
        $error_msg = 'Tipo de usuario no válido.';
    } else {
        // Verificar si el nombre de usuario ya existe
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE nombre_usuario = ?');
        $stmt->execute([$username]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            $error_msg = 'Este nombre de usuario ya está registrado. Por favor, elige otro.';
        } else {
            // Insertar nuevo usuario en la base de datos
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare('INSERT INTO usuarios (nombre_usuario, contraseña, nombre_completo, tipo) VALUES (?, ?, ?, ?)');
            $result = $stmt->execute([$username, $hashed_password, $fullname, $user_type]);
            
            if ($result) {
                $success_msg = 'Registro exitoso. Ahora puedes iniciar sesión.';
                // Limpiar datos del formulario después del registro exitoso
                $username = '';
                $fullname = '';
                $user_type = '';
            } else {
                $error_msg = 'Error al registrar usuario. Por favor, inténtalo de nuevo.';
            }
        }
    }
}

// Incluir la cabecera
include 'includes/header.php';
?>

<div class="auth-container">
    <div id="alerts-container">
        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error_msg); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($success_msg); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    </div>

    <div class="card auth-card">
        <div class="auth-header bg-light">
            <div class="auth-icon">
                <i class="fas fa-user-plus"></i>
            </div>
            <h2>Registro en ExamHub</h2>
            <p class="text-muted">Crea una cuenta para acceder a los exámenes</p>
        </div>
        
        <div class="card-body p-4">
            <form method="post" action="register.php">
                <div class="mb-3">
                    <label for="username" class="form-label">Nombre de Usuario</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="fullname" class="form-label">Nombre Completo</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                        <input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo htmlspecialchars($fullname); ?>" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="form-text">La contraseña debe tener al menos 6 caracteres.</div>
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Tipo de Usuario</label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="user_type" id="student" value="estudiante" <?php echo $user_type === 'estudiante' ? 'checked' : ''; ?> required>
                            <label class="form-check-label" for="student">
                                <i class="fas fa-user-graduate me-1"></i> Estudiante
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="user_type" id="teacher" value="profesor" <?php echo $user_type === 'profesor' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="teacher">
                                <i class="fas fa-chalkboard-teacher me-1"></i> Profesor
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus me-2"></i>Registrarse
                    </button>
                </div>
            </form>
        </div>
        
        <div class="card-footer bg-light text-center p-3">
            ¿Ya tienes una cuenta? <a href="login.php">Iniciar Sesión</a>
        </div>
    </div>
</div>

<?php
// Incluir el pie de página
include 'includes/footer.php';
?>