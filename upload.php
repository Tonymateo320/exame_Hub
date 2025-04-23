<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está autenticado y es profesor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'profesor') {
    // Redirigir al inicio con mensaje de error
    $_SESSION['message'] = 'Solo los profesores pueden subir exámenes.';
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php');
    exit();
}

// Incluir la conexión a la base de datos
require_once 'includes/db.php';

// Obtener lista de materias para el formulario
$subjects = fetch_all('SELECT id, nombre_materia FROM materias ORDER BY nombre_materia');

// Configuración para la subida de archivos
$upload_dir = 'uploads/exams/';
$max_file_size = 10 * 1024 * 1024; // 10MB en bytes
$allowed_extension = 'pdf';

// Inicializar variables
$title = '';
$subject_id = '';
$year = date('Y'); // Año actual por defecto

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $title = trim($_POST['title']);
    $subject_id = intval($_POST['subject_id']);
    $year = intval($_POST['year']);
    
    // Validar datos
    $errors = [];
    
    if (empty($title)) {
        $errors[] = 'El título del examen es obligatorio.';
    }
    
    if ($subject_id <= 0) {
        $errors[] = 'Debes seleccionar una materia válida.';
    }
    
    if ($year < 2000 || $year > date('Y') + 1) {
        $errors[] = 'El año debe estar entre 2000 y ' . (date('Y') + 1) . '.';
    }
    
    // Validar el archivo
    if (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] != 0) {
        $errors[] = 'Debes seleccionar un archivo PDF válido.';
    } else {
        // Verificar tamaño
        if ($_FILES['pdf_file']['size'] > $max_file_size) {
            $errors[] = 'El archivo no debe superar los 10MB.';
        }
        
        // Verificar extensión
        $file_extension = strtolower(pathinfo($_FILES['pdf_file']['name'], PATHINFO_EXTENSION));
        if ($file_extension !== strtolower($allowed_extension)) {
            $errors[] = 'Solo se permiten archivos PDF.';
        }
    }
    
    // Si no hay errores, procesar la subida
    if (empty($errors)) {
        // Crear directorio si no existe
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                $errors[] = 'Error al crear el directorio de subidas. Por favor, contacte al administrador.';
            }
        }
        
        // Verificar permisos de escritura
        if (!is_writable($upload_dir)) {
            $errors[] = 'No hay permisos de escritura en el directorio de subidas. Por favor, contacte al administrador.';
        }
        
        // Generar nombre de archivo único
        $file_name = uniqid('exam_') . '_' . time() . '.' . $file_extension;
        $file_path = $upload_dir . $file_name;
        
        // Mover el archivo subido
        if (move_uploaded_file($_FILES['pdf_file']['tmp_name'], $file_path)) {
            // Guardar información en la base de datos
            $sql = 'INSERT INTO examenes (titulo, id_materia, anio, archivo_ruta, subido_por) 
                    VALUES (?, ?, ?, ?, ?)';
            $result = query($sql, [$title, $subject_id, $year, $file_path, $_SESSION['user_id']]);
            
            if ($result) {
                // Éxito
                $_SESSION['message'] = '¡Examen subido correctamente!';
                $_SESSION['message_type'] = 'success';
                header('Location: index.php');
                exit();
            } else {
                $errors[] = 'Error al guardar la información en la base de datos.';
                // Eliminar el archivo si hubo error
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
        } else {
            $errors[] = 'Error al subir el archivo. Por favor, inténtalo de nuevo.';
        }
    }
}

// Incluir la cabecera
include 'includes/header.php';
?>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card shadow-sm fade-in">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0"><i class="fas fa-upload me-2"></i>Subir Nuevo Examen</h3>
            </div>
            
            <div class="card-body p-4">
                <?php if (isset($errors) && !empty($errors)): ?>
                    <div class="alert alert-danger">
                        <strong>Error:</strong>
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form id="upload-form" method="POST" action="upload.php" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label">Título del Examen</label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
                        <div class="form-text">Ejemplo: Primer Parcial 2023</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="subject_id" class="form-label">Materia</label>
                        <select class="form-select" id="subject_id" name="subject_id" required>
                            <option value="">Seleccionar materia</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?php echo $subject['id']; ?>" <?php echo ($subject_id == $subject['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($subject['nombre_materia']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="year" class="form-label">Año</label>
                        <select class="form-select" id="year" name="year" required>
                            <?php for ($y = date('Y') + 1; $y >= 2000; $y--): ?>
                                <option value="<?php echo $y; ?>" <?php echo ($year == $y) ? 'selected' : ''; ?>>
                                    <?php echo $y; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="pdf-file" class="form-label">Archivo PDF</label>
                        <input type="file" class="form-control" id="pdf-file" name="pdf_file" accept=".pdf" required>
                        <div class="form-text">Máximo 10MB. Solo archivos PDF.</div>
                        <div id="file-preview" class="mt-2"></div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload me-2"></i>Subir Examen
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir el pie de página
include 'includes/footer.php';
?>