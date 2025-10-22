<?php
require_once '../config.php';

$page_title = 'Liste des Clients';
$base_url = '../';

// Gestion de la recherche et du filtrage
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';

// Construction de la requête
$sql = "SELECT * FROM clients WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (nom LIKE ? OR email LIKE ? OR telephone LIKE ? OR secteur_activite LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_fill(0, 4, $searchTerm);
}

if (!empty($status_filter)) {
    $sql .= " AND statut = ?";
    $params[] = $status_filter;
}

$sql .= " ORDER BY date_creation DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $clients = $stmt->fetchAll();
    
    // Statistiques
    $stmt = $pdo->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN statut = 'actif' THEN 1 ELSE 0 END) as actifs,
        SUM(CASE WHEN statut = 'inactif' THEN 1 ELSE 0 END) as inactifs
        FROM clients");
    $stats = $stmt->fetch();
    
} catch(PDOException $e) {
    $error = "Erreur lors de la récupération des clients : " . $e->getMessage();
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
            <h1 class="h3 mb-0"><i class="fas fa-users me-2"></i>Gestion des Clients</h1>
            <p class="text-muted">Liste et gestion de tous les clients</p>
        </div>
        <div>
            <a href="add.php" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Nouveau Client
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Clients
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $stats['total']; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Clients Actifs
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $stats['actifs']; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Clients Inactifs
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $stats['inactifs']; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-times fa-2x text-gray-300"></i>
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
                <div class="col-md-6">
                    <label for="search" class="form-label">Rechercher</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="searchInput" name="search" 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Nom, email, téléphone, secteur...">
                        <button class="btn btn-outline-secondary" type="submit" id="searchButton">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Statut</label>
                    <select class="form-select" name="status">
                        <option value="">Tous les statuts</option>
                        <option value="actif" <?php echo $status_filter === 'actif' ? 'selected' : ''; ?>>Actif</option>
                        <option value="inactif" <?php echo $status_filter === 'inactif' ? 'selected' : ''; ?>>Inactif</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
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

    <!-- Clients Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Liste des Clients</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                    <div class="dropdown-header">Actions:</div>
                    <a class="dropdown-item" href="#" onclick="exportToExcel('clientsTable')">
                        <i class="fas fa-file-excel fa-sm fa-fw mr-2 text-gray-400"></i>
                        Exporter Excel
                    </a>
                    <a class="dropdown-item" href="#" onclick="exportToPDF('clientsTable')">
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
            <?php if (empty($clients)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-users fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-600">Aucun client trouvé</h5>
                    <p class="text-muted">
                        <?php if (!empty($search) || !empty($status_filter)): ?>
                            Aucun client ne correspond aux critères de recherche.
                        <?php else: ?>
                            Commencez par ajouter votre premier client.
                        <?php endif; ?>
                    </p>
                    <a href="add.php" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Ajouter un Client
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="clientsTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Contact</th>
                                <th>Secteur</th>
                                <th>Statut</th>
                                <th>Date Création</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($clients as $client): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="status-indicator status-<?php echo $client['statut']; ?>"></div>
                                        <div>
                                            <div class="font-weight-bold"><?php echo htmlspecialchars($client['nom']); ?></div>
                                            <div class="text-muted small">ID: <?php echo $client['id']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <i class="fas fa-envelope me-1 text-muted"></i>
                                        <a href="mailto:<?php echo htmlspecialchars($client['email']); ?>">
                                            <?php echo htmlspecialchars($client['email']); ?>
                                        </a>
                                    </div>
                                    <?php if ($client['telephone']): ?>
                                    <div class="mt-1">
                                        <i class="fas fa-phone me-1 text-muted"></i>
                                        <a href="tel:<?php echo htmlspecialchars($client['telephone']); ?>">
                                            <?php echo htmlspecialchars($client['telephone']); ?>
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo htmlspecialchars($client['secteur_activite'] ?: 'Non défini'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $client['statut'] === 'actif' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($client['statut']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div><?php echo date('d/m/Y', strtotime($client['date_creation'])); ?></div>
                                    <div class="text-muted small"><?php echo date('H:i', strtotime($client['date_creation'])); ?></div>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="edit.php?id=<?php echo $client['id']; ?>" 
                                           class="btn btn-outline-primary" 
                                           data-bs-toggle="tooltip" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="../projets/list.php?client_id=<?php echo $client['id']; ?>" 
                                           class="btn btn-outline-info" 
                                           data-bs-toggle="tooltip" title="Voir les projets">
                                            <i class="fas fa-project-diagram"></i>
                                        </a>
                                        <a href="delete.php?id=<?php echo $client['id']; ?>" 
                                           class="btn btn-outline-danger" 
                                           data-bs-toggle="tooltip" title="Supprimer"
                                           onclick="return confirmDelete('Êtes-vous sûr de vouloir supprimer ce client ?')">
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
                        <span id="resultsCount"><?php echo count($clients); ?> résultat(s) affiché(s)</span>
                    </div>
                    <div class="text-muted">
                        Total: <?php echo $stats['total']; ?> clients
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>