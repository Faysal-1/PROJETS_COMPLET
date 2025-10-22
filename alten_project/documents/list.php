<?php
require_once '../config.php';

$page_title = 'Liste des Documents';
$base_url = '../';

// Gestion des filtres
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$projet_filter = isset($_GET['projet_id']) ? (int)$_GET['projet_id'] : 0;
$type_filter = isset($_GET['type']) ? sanitize($_GET['type']) : '';

// Construction de la requête
$sql = "SELECT d.*, p.nom as projet_nom, c.nom as client_nom 
        FROM documents d 
        JOIN projets p ON d.projet_id = p.id 
        JOIN clients c ON p.client_id = c.id 
        WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (d.nom_original LIKE ? OR d.description LIKE ? OR p.nom LIKE ? OR c.nom LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_fill(0, 4, $searchTerm);
}

if ($projet_filter > 0) {
    $sql .= " AND d.projet_id = ?";
    $params[] = $projet_filter;
}

if (!empty($type_filter)) {
    $sql .= " AND d.type_fichier = ?";
    $params[] = $type_filter;
}

$sql .= " ORDER BY d.date_upload DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $documents = $stmt->fetchAll();
    
    // Statistiques
    $stmt = $pdo->query("SELECT 
        COUNT(*) as total,
        SUM(taille_fichier) as taille_totale,
        COUNT(CASE WHEN type_fichier = 'application/pdf' THEN 1 END) as pdf_count,
        COUNT(CASE WHEN type_fichier LIKE '%excel%' OR type_fichier LIKE '%spreadsheet%' THEN 1 END) as excel_count
        FROM documents");
    $stats = $stmt->fetch();
    
    // Liste des projets pour le filtre
    $stmt = $pdo->query("SELECT p.id, p.nom, c.nom as client_nom 
                        FROM projets p 
                        JOIN clients c ON p.client_id = c.id 
                        ORDER BY p.nom");
    $projets = $stmt->fetchAll();
    
} catch(PDOException $e) {
    $error = "Erreur lors de la récupération des documents : " . $e->getMessage();
}

// Fonction pour formater la taille des fichiers
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

include '../header.php';
?>

<div class="container-fluid">
    <?php if (isset($error)): ?>
        <?php echo showAlert($error, 'danger'); ?>
    <?php endif; ?>
    
    <?php if (isset($_GET['success'])): ?>
        <?php echo showAlert($_GET['success'], 'success'); ?>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <?php echo showAlert($_GET['error'], 'danger'); ?>
    <?php endif; ?>
    
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-file-alt me-2"></i>Gestion des Documents</h1>
            <p class="text-muted">Liste et gestion de tous les documents</p>
        </div>
        <div>
            <a href="upload.php" class="btn btn-primary">
                <i class="fas fa-upload me-1"></i>Nouveau Document
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Documents
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $stats['total'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Fichiers PDF
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $stats['pdf_count'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-pdf fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Fichiers Excel
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $stats['excel_count'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-excel fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Taille Totale
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo formatFileSize($stats['taille_totale'] ?? 0); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hdd fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtres et Recherche</h6>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-5">
                    <label for="search" class="form-label">Rechercher</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="searchInput" name="search" 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Nom de fichier, description, projet...">
                        <button class="btn btn-outline-secondary" type="submit" id="searchButton">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="projet_id" class="form-label">Projet</label>
                    <select class="form-select" name="projet_id">
                        <option value="">Tous les projets</option>
                        <?php if (isset($projets)): ?>
                            <?php foreach($projets as $projet): ?>
                                <option value="<?php echo $projet['id']; ?>" 
                                        <?php echo $projet_filter == $projet['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($projet['nom'] . ' (' . $projet['client_nom'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="type" class="form-label">Type</label>
                    <select class="form-select" name="type">
                        <option value="">Tous les types</option>
                        <option value="application/pdf" <?php echo $type_filter === 'application/pdf' ? 'selected' : ''; ?>>PDF</option>
                        <option value="application/vnd.ms-excel" <?php echo $type_filter === 'application/vnd.ms-excel' ? 'selected' : ''; ?>>Excel (.xls)</option>
                        <option value="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" <?php echo $type_filter === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' ? 'selected' : ''; ?>>Excel (.xlsx)</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter me-1"></i>Filtrer
                    </button>
                    <a href="list.php" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Documents Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Liste des Documents</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                    <div class="dropdown-header">Actions:</div>
                    <a class="dropdown-item" href="#" onclick="exportToExcel('documentsTable')">
                        <i class="fas fa-file-excel fa-sm fa-fw mr-2 text-gray-400"></i>
                        Exporter Excel
                    </a>
                    <a class="dropdown-item" href="#" onclick="printPage()">
                        <i class="fas fa-print fa-sm fa-fw mr-2 text-gray-400"></i>
                        Imprimer
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($documents)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-file-alt fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-600">Aucun document trouvé</h5>
                    <p class="text-muted">
                        <?php if (!empty($search) || $projet_filter || !empty($type_filter)): ?>
                            Aucun document ne correspond aux critères de recherche.
                        <?php else: ?>
                            Commencez par uploader votre premier document.
                        <?php endif; ?>
                    </p>
                    <a href="upload.php" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i>Uploader un Document
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="documentsTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Document</th>
                                <th>Projet</th>
                                <th>Type</th>
                                <th>Taille</th>
                                <th>Date Upload</th>
                                <th>Uploadé par</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($documents as $document): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-2">
                                            <?php if (strpos($document['type_fichier'], 'pdf') !== false): ?>
                                                <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                            <?php elseif (strpos($document['type_fichier'], 'excel') !== false || strpos($document['type_fichier'], 'spreadsheet') !== false): ?>
                                                <i class="fas fa-file-excel fa-2x text-success"></i>
                                            <?php else: ?>
                                                <i class="fas fa-file fa-2x text-secondary"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <div class="font-weight-bold"><?php echo htmlspecialchars($document['nom_original']); ?></div>
                                            <?php if ($document['description']): ?>
                                                <div class="text-muted small"><?php echo htmlspecialchars(substr($document['description'], 0, 50)) . '...'; ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <a href="../projets/edit.php?id=<?php echo $document['projet_id']; ?>" class="text-decoration-none font-weight-bold">
                                            <?php echo htmlspecialchars($document['projet_nom']); ?>
                                        </a>
                                    </div>
                                    <div class="text-muted small">
                                        <i class="fas fa-building me-1"></i><?php echo htmlspecialchars($document['client_nom']); ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if (strpos($document['type_fichier'], 'pdf') !== false): ?>
                                        <span class="badge bg-danger">PDF</span>
                                    <?php elseif (strpos($document['type_fichier'], 'excel') !== false || strpos($document['type_fichier'], 'spreadsheet') !== false): ?>
                                        <span class="badge bg-success">Excel</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Autre</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="font-weight-bold"><?php echo formatFileSize($document['taille_fichier']); ?></div>
                                </td>
                                <td>
                                    <div><?php echo date('d/m/Y', strtotime($document['date_upload'])); ?></div>
                                    <div class="text-muted small"><?php echo date('H:i', strtotime($document['date_upload'])); ?></div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user me-1 text-muted"></i>
                                        <?php echo htmlspecialchars($document['uploade_par'] ?: 'Non défini'); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="<?php echo htmlspecialchars($document['chemin_fichier']); ?>" 
                                           class="btn btn-outline-primary" 
                                           data-bs-toggle="tooltip" title="Télécharger"
                                           download="<?php echo htmlspecialchars($document['nom_original']); ?>">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <a href="<?php echo htmlspecialchars($document['chemin_fichier']); ?>" 
                                           class="btn btn-outline-info" 
                                           data-bs-toggle="tooltip" title="Voir"
                                           target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="delete.php?id=<?php echo $document['id']; ?>" 
                                           class="btn btn-outline-danger" 
                                           data-bs-toggle="tooltip" title="Supprimer"
                                           onclick="return confirmDelete('Êtes-vous sûr de vouloir supprimer ce document ?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Results Info -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        <span id="resultsCount"><?php echo count($documents); ?> résultat(s) affiché(s)</span>
                    </div>
                    <div class="text-muted">
                        Total: <?php echo $stats['total'] ?? 0; ?> documents - <?php echo formatFileSize($stats['taille_totale'] ?? 0); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>