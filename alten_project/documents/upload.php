<?php
require_once '../config.php';

$page_title = 'Uploader un Document';
$base_url = '../';

// Récupération du projet pré-sélectionné si fourni
$preselected_projet = isset($_GET['projet_id']) ? (int)$_GET['projet_id'] : 0;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projet_id = (int)$_POST['projet_id'];
    $description = sanitize($_POST['description']);
    $uploade_par = sanitize($_POST['uploade_par']);
    
    $errors = [];
    
    // Validation
    if ($projet_id <= 0) {
        $errors[] = "Veuillez sélectionner un projet.";
    }
    
    if (empty($uploade_par)) {
        $errors[] = "Veuillez saisir le nom de la personne qui uploade le document.";
    }
    
    // Vérification du fichier
    if (!isset($_FILES['fichier']) || $_FILES['fichier']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Veuillez sélectionner un fichier à uploader.";
    } else {
        $fichier = $_FILES['fichier'];
        
        // Vérifier la taille (max 10MB)
        if ($fichier['size'] > 10 * 1024 * 1024) {
            $errors[] = "Le fichier est trop volumineux. Taille maximale : 10MB.";
        }
        
        // Vérifier le type de fichier - Version compatible sans fileinfo
        $types_autorises = [
            'application/pdf',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
        
        // Obtenir l'extension du fichier
        $extension = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));
        $extensions_autorisees = ['pdf', 'xls', 'xlsx'];
        
        if (!in_array($extension, $extensions_autorisees)) {
            $errors[] = "Extension de fichier non autorisée. Seuls les fichiers .pdf, .xls et .xlsx sont acceptés.";
        }
        
        // Mapper l'extension au type MIME
        $extension_to_mime = [
            'pdf' => 'application/pdf',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
        
        $type_fichier = isset($extension_to_mime[$extension]) ? $extension_to_mime[$extension] : $fichier['type'];
        
        // Validation supplémentaire du type MIME fourni par le navigateur
        if (!in_array($type_fichier, $types_autorises) && !in_array($fichier['type'], $types_autorises)) {
            $errors[] = "Type de fichier non autorisé. Seuls les fichiers PDF et Excel sont acceptés.";
        }
    }
    
    // Vérifier que le projet existe
    if (empty($errors) && $projet_id > 0) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM projets WHERE id = ?");
            $stmt->execute([$projet_id]);
            if (!$stmt->fetch()) {
                $errors[] = "Le projet sélectionné n'existe pas.";
            }
        } catch(PDOException $e) {
            $errors[] = "Erreur lors de la vérification du projet.";
        }
    }
    
    // Upload du fichier
    if (empty($errors)) {
        try {
            // Créer le dossier uploads s'il n'existe pas
            $upload_dir = '../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Générer un nom unique pour le fichier
            $extension = pathinfo($fichier['name'], PATHINFO_EXTENSION);
            $nom_fichier = uniqid() . '_' . time() . '.' . $extension;
            $chemin_fichier = $upload_dir . $nom_fichier;
            
            // Déplacer le fichier uploadé
            if (move_uploaded_file($fichier['tmp_name'], $chemin_fichier)) {
                // Insérer en base de données
                $sql = "INSERT INTO documents (nom_fichier, nom_original, type_fichier, taille_fichier, 
                        projet_id, chemin_fichier, description, uploade_par) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $nom_fichier,
                    $fichier['name'],
                    $type_fichier,
                    $fichier['size'],
                    $projet_id,
                    $chemin_fichier,
                    $description,
                    $uploade_par
                ]);
                
                $success = "Document uploadé avec succès !";
                
                // Redirection après succès
                header("Location: list.php?success=" . urlencode($success));
                exit();
                
            } else {
                $errors[] = "Erreur lors de l'upload du fichier.";
            }
            
        } catch(PDOException $e) {
            $errors[] = "Erreur lors de l'enregistrement : " . $e->getMessage();
        }
    }
}

// Récupération de la liste des projets
try {
    $stmt = $pdo->query("SELECT p.id, p.nom, c.nom as client_nom 
                        FROM projets p 
                        JOIN clients c ON p.client_id = c.id 
                        WHERE p.statut IN ('planifie', 'en_cours') 
                        ORDER BY p.nom");
    $projets = $stmt->fetchAll();
} catch(PDOException $e) {
    $projets = [];
}

include '../header.php';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-cloud-upload-alt me-2"></i>Uploader un Document</h1>
            <p class="text-muted">Ajouter un nouveau document au système</p>
        </div>
        <div>
            <a href="list.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Retour à la liste
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Main Form -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informations du Document</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-exclamation-triangle me-1"></i>Erreurs détectées :</h6>
                            <ul class="mb-0">
                                <?php foreach($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate data-autosave="upload-document">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="projet_id" class="form-label">
                                    <i class="fas fa-project-diagram me-1"></i>Projet *
                                </label>
                                <select class="form-select" id="projet_id" name="projet_id" required>
                                    <option value="">Sélectionner un projet</option>
                                    <?php foreach($projets as $projet): ?>
                                        <option value="<?php echo $projet['id']; ?>" 
                                                <?php echo (($_POST['projet_id'] ?? $preselected_projet) == $projet['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($projet['nom'] . ' (' . $projet['client_nom'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Veuillez sélectionner un projet.
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="uploade_par" class="form-label">
                                    <i class="fas fa-user me-1"></i>Uploadé par *
                                </label>
                                <input type="text" class="form-control" id="uploade_par" name="uploade_par" 
                                       value="<?php echo htmlspecialchars($_POST['uploade_par'] ?? 'Fayssal Rajouani'); ?>" 
                                       required maxlength="100"
                                       placeholder="Nom de la personne">
                                <div class="invalid-feedback">
                                    Veuillez saisir le nom de la personne.
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="fichier" class="form-label">
                                <i class="fas fa-file me-1"></i>Fichier *
                            </label>
                            <div class="file-upload-area" id="fileUploadArea">
                                <div class="text-center">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                    <h5>Glissez-déposez votre fichier ici</h5>
                                    <p class="text-muted">ou cliquez pour sélectionner un fichier</p>
                                    <p class="small text-muted">
                                        Formats acceptés: PDF, Excel (.xls, .xlsx)<br>
                                        Taille maximale: 10MB
                                    </p>
                                </div>
                                <input type="file" class="d-none" id="fileInput" name="fichier" 
                                       accept=".pdf,.xls,.xlsx,application/pdf,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" 
                                       required>
                            </div>
                            <div class="invalid-feedback">
                                Veuillez sélectionner un fichier.
                            </div>
                            
                            <!-- File Info Display -->
                            <div id="fileInfo" class="mt-3 d-none">
                                <div class="alert alert-info">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-file fa-2x me-3"></i>
                                        <div>
                                            <div class="font-weight-bold" id="fileName"></div>
                                            <div class="small text-muted">
                                                Taille: <span id="fileSize"></span> | 
                                                Type: <span id="fileType"></span>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger ms-auto" onclick="clearFile()">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left me-1"></i>Description
                            </label>
                            <textarea class="form-control" id="description" name="description" rows="4" 
                                      maxlength="500"
                                      placeholder="Description du document (optionnel)..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                            <div class="form-text">Maximum 500 caractères</div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary" onclick="history.back()">
                                <i class="fas fa-times me-1"></i>Annuler
                            </button>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-upload me-1"></i>Uploader le Document
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Help Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-info-circle me-1"></i>Aide
                    </h6>
                </div>
                <div class="card-body">
                    <h6>Formats acceptés</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-file-pdf text-danger me-1"></i>PDF (.pdf)</li>
                        <li><i class="fas fa-file-excel text-success me-1"></i>Excel (.xls, .xlsx)</li>
                    </ul>
                    
                    <hr>
                    
                    <h6>Limites</h6>
                    <ul class="small text-muted">
                        <li>Taille maximale: 10MB</li>
                        <li>Un seul fichier à la fois</li>
                        <li>Seuls les projets actifs sont disponibles</li>
                    </ul>
                    
                    <hr>
                    
                    <h6>Conseils</h6>
                    <ul class="small text-muted">
                        <li>Utilisez des noms de fichiers descriptifs</li>
                        <li>Ajoutez une description pour faciliter la recherche</li>
                        <li>Vérifiez que le projet est correct</li>
                    </ul>
                </div>
            </div>

            <!-- Upload Progress -->
            <div class="card shadow mb-4 d-none" id="uploadProgress">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-upload me-1"></i>Upload en cours
                    </h6>
                </div>
                <div class="card-body">
                    <div class="progress mb-3">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 0%" id="progressBar"></div>
                    </div>
                    <div class="text-center">
                        <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                        <span id="progressText">Préparation...</span>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-chart-bar me-1"></i>Statistiques
                    </h6>
                </div>
                <div class="card-body">
                    <?php
                    try {
                        $stmt = $pdo->query("SELECT COUNT(*) as total FROM documents");
                        $total_documents = $stmt->fetch()['total'];
                        
                        $stmt = $pdo->query("SELECT COUNT(*) as total FROM documents WHERE DATE(date_upload) = CURDATE()");
                        $documents_today = $stmt->fetch()['total'];
                        
                        $stmt = $pdo->query("SELECT COUNT(*) as total FROM projets WHERE statut IN ('planifie', 'en_cours')");
                        $projets_actifs = $stmt->fetch()['total'];
                    } catch(PDOException $e) {
                        $total_documents = 0;
                        $documents_today = 0;
                        $projets_actifs = 0;
                    }
                    ?>
                    <div class="text-center">
                        <div class="row">
                            <div class="col-4">
                                <div class="h4 text-primary"><?php echo $total_documents; ?></div>
                                <div class="text-muted small">Total docs</div>
                            </div>
                            <div class="col-4">
                                <div class="h4 text-success"><?php echo $documents_today; ?></div>
                                <div class="text-muted small">Aujourd'hui</div>
                            </div>
                            <div class="col-4">
                                <div class="h4 text-info"><?php echo $projets_actifs; ?></div>
                                <div class="text-muted small">Projets actifs</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Gestion de l'upload de fichier avec drag & drop
document.addEventListener('DOMContentLoaded', function() {
    const fileUploadArea = document.getElementById('fileUploadArea');
    const fileInput = document.getElementById('fileInput');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const fileType = document.getElementById('fileType');
    const submitBtn = document.getElementById('submitBtn');
    const form = document.querySelector('form');
    
    // Click to upload
    fileUploadArea.addEventListener('click', function() {
        fileInput.click();
    });
    
    // Drag and drop events
    fileUploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        fileUploadArea.classList.add('dragover');
    });
    
    fileUploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        fileUploadArea.classList.remove('dragover');
    });
    
    fileUploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        fileUploadArea.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFileSelection(files[0]);
        }
    });
    
    // File input change
    fileInput.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            handleFileSelection(e.target.files[0]);
        }
    });
    
    // Handle file selection
    function handleFileSelection(file) {
        // Validate file extension
        const allowedExtensions = ['pdf', 'xls', 'xlsx'];
        const fileExtension = file.name.split('.').pop().toLowerCase();
        
        if (!allowedExtensions.includes(fileExtension)) {
            showNotification('Extension de fichier non autorisée. Seuls les fichiers .pdf, .xls et .xlsx sont acceptés.', 'error');
            return;
        }
        
        // Validate file size (max 10MB)
        if (file.size > 10 * 1024 * 1024) {
            showNotification('Le fichier est trop volumineux. Taille maximale : 10MB', 'error');
            return;
        }
        
        // Update UI
        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);
        fileType.textContent = getFileTypeLabel(fileExtension);
        
        fileInfo.classList.remove('d-none');
        fileUploadArea.style.display = 'none';
        
        showNotification('Fichier sélectionné avec succès', 'success');
    }
    
    // Clear file selection
    window.clearFile = function() {
        fileInput.value = '';
        fileInfo.classList.add('d-none');
        fileUploadArea.style.display = 'block';
    };
    
    // Get file type label
    function getFileTypeLabel(extension) {
        switch(extension) {
            case 'pdf': return 'PDF';
            case 'xls': return 'Excel (.xls)';
            case 'xlsx': return 'Excel (.xlsx)';
            default: return 'Inconnu';
        }
    }
    
    // Format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    // Form submission with progress
    form.addEventListener('submit', function(e) {
        if (!fileInput.files.length) {
            e.preventDefault();
            showNotification('Veuillez sélectionner un fichier', 'error');
            return;
        }
        
        // Show upload progress
        const uploadProgress = document.getElementById('uploadProgress');
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        
        uploadProgress.classList.remove('d-none');
        submitBtn.disabled = true;
        
        // Simulate progress (in real implementation, use XMLHttpRequest for real progress)
        let progress = 0;
        const interval = setInterval(() => {
            progress += Math.random() * 30;
            if (progress > 90) progress = 90;
            
            progressBar.style.width = progress + '%';
            progressText.textContent = `Upload en cours... ${Math.round(progress)}%`;
        }, 200);
        
        // The form will submit normally, this is just for UX
        setTimeout(() => {
            clearInterval(interval);
            progressBar.style.width = '100%';
            progressText.textContent = 'Finalisation...';
        }, 2000);
    });
});
</script>

<?php include '../footer.php'; ?>