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

// Variables para almacenar errores
$error_msg = '';
$username = '';

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Validación básica
    if (empty($username) || empty($password)) {
        $error_msg = 'Por favor, completa todos los campos.';
    } else {
        // Buscar usuario en la base de datos
        $stmt = $pdo->prepare('SELECT id, nombre_usuario, contraseña, nombre_completo, tipo FROM usuarios WHERE nombre_usuario = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['contraseña'])) {
            // Autenticación exitosa - Almacenar datos de sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['nombre_usuario'];
            $_SESSION['fullname'] = $user['nombre_completo'];
            $_SESSION['user_type'] = $user['tipo'];
            
            // Redirigir al index
            header('Location: index.php');
            exit();
        } else {
            $error_msg = 'Credenciales incorrectas. Por favor, inténtalo de nuevo.';
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
    </div>

    <div class="card auth-card">
        <div class="auth-header bg-light">
            <div class="auth-icon">
                <i class="fas fa-sign-in-alt"></i>
            </div>
            <h2>Iniciar Sesión</h2>
            <p class="text-muted">Accede a tu cuenta de ExamHub</p>
        </div>
        
        <div class="card-body p-4">
            <form method="post" action="login.php">
                <div class="mb-3">
                    <label for="username" class="form-label">Nombre de Usuario</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                    </button>
                </div>
            </form>
        </div>
        
        <div class="card-footer bg-light text-center p-3">
            ¿No tienes una cuenta? <a href="register.php">Regístrate</a>
        </div>
    </div>
</div>

<?php
// Incluir el pie de página
include 'includes/footer.php';
?>