<?php
require_once '../config.php';

$page_title = 'Liste des Projets';
$base_url = '../';

// Gestion des filtres
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$client_filter = isset($_GET['client_id']) ? (int)$_GET['client_id'] : 0;
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$priority_filter = isset($_GET['priority']) ? sanitize($_GET['priority']) : '';

// Construction de la requête
$sql = "SELECT p.*, c.nom as client_nom FROM projets p 
        JOIN clients c ON p.client_id = c.id 
        WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (p.nom LIKE ? OR p.description LIKE ? OR c.nom LIKE ? OR p.responsable LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_fill(0, 4, $searchTerm);
}

if ($client_filter > 0) {
    $sql .= " AND p.client_id = ?";
    $params[] = $client_filter;
}

if (!empty($status_filter)) {
    $sql .= " AND p.statut = ?";
    $params[] = $status_filter;
}

if (!empty($priority_filter)) {
    $sql .= " AND p.priorite = ?";
    $params[] = $priority_filter;
}

$sql .= " ORDER BY p.date_creation DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $projets = $stmt->fetchAll();
    
    // Statistiques
    $stmt = $pdo->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN statut = 'en_cours' THEN 1 ELSE 0 END) as en_cours,
        SUM(CASE WHEN statut = 'termine' THEN 1 ELSE 0 END) as termines,
        SUM(CASE WHEN statut = 'planifie' THEN 1 ELSE 0 END) as planifies,
        SUM(budget) as budget_total
        FROM projets");
    $stats = $stmt->fetch();
    
    // Liste des clients pour le filtre
    $stmt = $pdo->query("SELECT id, nom FROM clients WHERE statut = 'actif' ORDER BY nom");
    $clients = $stmt->fetchAll();
    
} catch(PDOException $e) {
    $error = "Erreur lors de la récupération des projets : " . $e->getMessage();
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
            <h1 class="h3 mb-0"><i class="fas fa-project-diagram me-2"></i>Gestion des Projets</h1>
            <p class="text-muted">Liste et gestion de tous les projets</p>
        </div>
        <div>
            <a href="add.php" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Nouveau Projet
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
                                Total Projets
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $stats['total']; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-project-diagram fa-2x text-gray-300"></i>
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
                                En Cours
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $stats['en_cours']; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-play-circle fa-2x text-gray-300"></i>
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
                                Budget Total
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($stats['budget_total'], 0, ',', ' '); ?> MAD
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                                Terminés
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $stats['termines']; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                <div class="col-md-4">
                    <label for="search" class="form-label">Rechercher</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="searchInput" name="search" 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Nom, description, client, responsable...">
                        <button class="btn btn-outline-secondary" type="submit" id="searchButton">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-2">
                    <label for="client_id" class="form-label">Client</label>
                    <select class="form-select" name="client_id">
                        <option value="">Tous les clients</option>
                        <?php foreach($clients as $client): ?>
                            <option value="<?php echo $client['id']; ?>" 
                                    <?php echo $client_filter == $client['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($client['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Statut</label>
                    <select class="form-select" name="status">
                        <option value="">Tous les statuts</option>
                        <option value="planifie" <?php echo $status_filter === 'planifie' ? 'selected' : ''; ?>>Planifié</option>
                        <option value="en_cours" <?php echo $status_filter === 'en_cours' ? 'selected' : ''; ?>>En cours</option>
                        <option value="termine" <?php echo $status_filter === 'termine' ? 'selected' : ''; ?>>Terminé</option>
                        <option value="suspendu" <?php echo $status_filter === 'suspendu' ? 'selected' : ''; ?>>Suspendu</option>
                        <option value="annule" <?php echo $status_filter === 'annule' ? 'selected' : ''; ?>>Annulé</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="priority" class="form-label">Priorité</label>
                    <select class="form-select" name="priority">
                        <option value="">Toutes les priorités</option>
                        <option value="basse" <?php echo $priority_filter === 'basse' ? 'selected' : ''; ?>>Basse</option>
                        <option value="normale" <?php echo $priority_filter === 'normale' ? 'selected' : ''; ?>>Normale</option>
                        <option value="haute" <?php echo $priority_filter === 'haute' ? 'selected' : ''; ?>>Haute</option>
                        <option value="critique" <?php echo $priority_filter === 'critique' ? 'selected' : ''; ?>>Critique</option>
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

    <!-- Projects Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Liste des Projets</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                    <div class="dropdown-header">Actions:</div>
                    <a class="dropdown-item" href="#" onclick="exportToExcel('projetsTable')">
                        <i class="fas fa-file-excel fa-sm fa-fw mr-2 text-gray-400"></i>
                        Exporter Excel
                    </a>
                    <a class="dropdown-item" href="#" onclick="exportToPDF('projetsTable')">
                        <i class="fas fa-file-pdf fa-sm fa-fw mr-2 text-gray-400"></i>
                        Exporter PDF
                    </a>
                    <a class="dropdown-item" href="#" onclick="printPage()">
                        <i class="fas fa-print fa-sm fa-fw mr-2 text-gray-400"></i>
                        Imprimer
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($projets)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-project-diagram fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-600">Aucun projet trouvé</h5>
                    <p class="text-muted">
                        <?php if (!empty($search) || $client_filter || !empty($status_filter) || !empty($priority_filter)): ?>
                            Aucun projet ne correspond aux critères de recherche.
                        <?php else: ?>
                            Commencez par ajouter votre premier projet.
                        <?php endif; ?>
                    </p>
                    <a href="add.php" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Ajouter un Projet
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="projetsTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Projet</th>
                                <th>Client</th>
                                <th>Statut</th>
                                <th>Priorité</th>
                                <th>Budget</th>
                                <th>Dates</th>
                                <th>Responsable</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($projets as $projet): ?>
                            <tr>
                                <td>
                                    <div>
                                        <div class="font-weight-bold"><?php echo htmlspecialchars($projet['nom']); ?></div>
                                        <div class="text-muted small"><?php echo htmlspecialchars(substr($projet['description'], 0, 60)) . '...'; ?></div>
                                    </div>
                                </td>
                                <td>
                                    <a href="../clients/edit.php?id=<?php echo $projet['client_id']; ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($projet['client_nom']); ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $projet['statut'] == 'en_cours' ? 'primary' : 
                                            ($projet['statut'] == 'termine' ? 'success' : 
                                            ($projet['statut'] == 'planifie' ? 'info' : 
                                            ($projet['statut'] == 'suspendu' ? 'warning' : 'danger'))); 
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $projet['statut'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $projet['priorite'] == 'critique' ? 'danger' : 
                                            ($projet['priorite'] == 'haute' ? 'warning' : 
                                            ($projet['priorite'] == 'normale' ? 'info' : 'secondary')); 
                                    ?>">
                                        <?php echo ucfirst($projet['priorite']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="font-weight-bold"><?php echo number_format($projet['budget'], 0, ',', ' '); ?> MAD</div>
                                </td>
                                <td>
                                    <div class="small">
                                        <div><strong>Début:</strong> <?php echo date('d/m/Y', strtotime($projet['date_debut'])); ?></div>
                                        <div><strong>Fin prévue:</strong> <?php echo date('d/m/Y', strtotime($projet['date_fin_prevue'])); ?></div>
                                        <?php if ($projet['date_fin_reelle']): ?>
                                            <div><strong>Fin réelle:</strong> <?php echo date('d/m/Y', strtotime($projet['date_fin_reelle'])); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user-tie me-1 text-muted"></i>
                                        <?php echo htmlspecialchars($projet['responsable']); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="edit.php?id=<?php echo $projet['id']; ?>" 
                                           class="btn btn-outline-primary" 
                                           data-bs-toggle="tooltip" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="../documents/list.php?projet_id=<?php echo $projet['id']; ?>" 
                                           class="btn btn-outline-info" 
                                           data-bs-toggle="tooltip" title="Voir les documents">
                                            <i class="fas fa-file-alt"></i>
                                        </a>
                                        <a href="delete.php?id=<?php echo $projet['id']; ?>" 
                                           class="btn btn-outline-danger" 
                                           data-bs-toggle="tooltip" title="Supprimer"
                                           onclick="return confirmDelete('Êtes-vous sûr de vouloir supprimer ce projet ?')">
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
                        <span id="resultsCount"><?php echo count($projets); ?> résultat(s) affiché(s)</span>
                    </div>
                    <div class="text-muted">
                        Total: <?php echo $stats['total']; ?> projets
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>