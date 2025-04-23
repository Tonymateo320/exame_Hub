// Esperar a que el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips de Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Validación de formulario de subida
    const uploadForm = document.getElementById('upload-form');
    if (uploadForm) {
        uploadForm.addEventListener('submit', function(event) {
            const fileInput = document.getElementById('pdf-file');
            const allowedExtension = '.pdf';
            const maxSize = 10 * 1024 * 1024; 
            
            if (fileInput.files.length > 0) {
                const fileName = fileInput.files[0].name;
                const fileSize = fileInput.files[0].size;
                
                // Verificar extensión
                if (!fileName.toLowerCase().endsWith(allowedExtension)) {
                    event.preventDefault();
                    showAlert('Solo se permiten archivos PDF.', 'danger');
                    return;
                }
                
                // Verificar tamaño
                if (fileSize > maxSize) {
                    event.preventDefault();
                    showAlert('El archivo no debe superar los 10MB.', 'danger');
                    return;
                }
            }
        });
    }

    // Función para mostrar alertas
    window.showAlert = function(message, type = 'info') {
        const alertsContainer = document.getElementById('alerts-container');
        if (alertsContainer) {
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            alertsContainer.appendChild(alert);
            
            // Auto-eliminar después de 5 segundos
            setTimeout(() => {
                alert.classList.remove('show');
                setTimeout(() => {
                    alertsContainer.removeChild(alert);
                }, 300);
            }, 5000);
        }
    };

    // Confirmación de eliminación
    const deleteButtons = document.querySelectorAll('.delete-exam');
    if (deleteButtons && deleteButtons.length > 0) {
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(event) {
                if (!confirm('¿Estás seguro de que deseas eliminar este examen? Esta acción no se puede deshacer.')) {
                    event.preventDefault();
                }
            });
        });
    }

    // Vista previa del archivo seleccionado
    const fileInput = document.getElementById('pdf-file');
    const filePreview = document.getElementById('file-preview');
    
    if (fileInput && filePreview) {
        fileInput.addEventListener('change', function() {
            if (fileInput.files.length > 0) {
                const fileName = fileInput.files[0].name;
                const fileSize = (fileInput.files[0].size / 1024 / 1024).toFixed(2); // Convertir a MB
                
                filePreview.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-file-pdf me-2"></i>
                        <strong>${fileName}</strong> (${fileSize} MB)
                    </div>
                `;
            } else {
                filePreview.innerHTML = '';
            }
        });
    }

    // Filtros dinámicos
    const subjectFilter = document.getElementById('subject-filter');
    const yearFilter = document.getElementById('year-filter');
    
    if (subjectFilter || yearFilter) {
        [subjectFilter, yearFilter].forEach(filter => {
            if (filter) {
                filter.addEventListener('change', function() {
                    applyFilters();
                });
            }
        });
    }

    function applyFilters() {
        const subject = subjectFilter ? subjectFilter.value : '';
        const year = yearFilter ? yearFilter.value : '';
        
        // Redirigir con los filtros aplicados
        const url = new URL(window.location.href);
        
        if (subject) {
            url.searchParams.set('subject', subject);
        } else {
            url.searchParams.delete('subject');
        }
        
        if (year) {
            url.searchParams.set('year', year);
        } else {
            url.searchParams.delete('year');
        }
        
        window.location.href = url.toString();
    }

    // Animaciones de entrada
    const animatedElements = document.querySelectorAll('.fade-in');
    if (animatedElements) {
        animatedElements.forEach((element, index) => {
            // Retrasar cada elemento un poco más para crear un efecto en cascada
            setTimeout(() => {
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }, 100 * index);
        });
    }
});