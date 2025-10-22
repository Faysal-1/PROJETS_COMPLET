<?php
require_once 'config.php';

$page_title = 'Dashboard';

// Récupération des statistiques
try {
    // Statistiques clients
    $stmt = $pdo->query("SELECT COUNT(*) as total_clients FROM clients WHERE statut = 'actif'");
    $total_clients = $stmt->fetch()['total_clients'];
    
    // Statistiques projets
    $stmt = $pdo->query("SELECT 
        COUNT(*) as total_projets,
        SUM(CASE WHEN statut = 'en_cours' THEN 1 ELSE 0 END) as projets_en_cours,
        SUM(CASE WHEN statut = 'termine' THEN 1 ELSE 0 END) as projets_termines,
        SUM(budget) as budget_total
        FROM projets");
    $stats_projets = $stmt->fetch();
    
    // Statistiques documents
    $stmt = $pdo->query("SELECT COUNT(*) as total_documents FROM documents");
    $total_documents = $stmt->fetch()['total_documents'];
    
    // Projets récents
    $stmt = $pdo->query("SELECT p.*, c.nom as client_nom 
                        FROM projets p 
                        JOIN clients c ON p.client_id = c.id 
                        ORDER BY p.date_creation DESC 
                        LIMIT 5");
    $projets_recents = $stmt->fetchAll();
    
    // Projets par statut pour le graphique
    $stmt = $pdo->query("SELECT statut, COUNT(*) as count FROM projets GROUP BY statut");
    $projets_par_statut = $stmt->fetchAll();
    
} catch(PDOException $e) {
    $error = "Erreur lors de la récupération des données : " . $e->getMessage();
}

include 'header.php';
?>

<div class="container-fluid">
    <?php if (isset($error)): ?>
        <?php echo showAlert($error, 'danger'); ?>
    <?php endif; ?>
    
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h1>
            <p class="text-muted">Vue d'ensemble de votre activité</p>
        </div>
        <div class="text-muted">
            <i class="fas fa-calendar me-1"></i><?php echo date('d/m/Y H:i'); ?>
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
                                Clients Actifs
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($total_clients); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
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
                                Projets Actifs
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($stats_projets['projets_en_cours']); ?>
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
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Budget Total
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($stats_projets['budget_total'], 0, ',', ' '); ?> MAD
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
                                Documents
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($total_documents); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Recent Projects -->
    <div class="row">
        <!-- Projects Status Chart -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Répartition des Projets</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="projectStatusChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <?php foreach($projets_par_statut as $stat): ?>
                            <span class="mr-2">
                                <i class="fas fa-circle text-<?php 
                                    echo $stat['statut'] == 'en_cours' ? 'primary' : 
                                        ($stat['statut'] == 'termine' ? 'success' : 
                                        ($stat['statut'] == 'planifie' ? 'info' : 'warning')); 
                                ?>"></i> <?php echo ucfirst(str_replace('_', ' ', $stat['statut'])); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Projects -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Projets Récents</h6>
                    <a href="projets/list.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye me-1"></i>Voir tout
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Projet</th>
                                    <th>Client</th>
                                    <th>Statut</th>
                                    <th>Budget</th>
                                    <th>Date Début</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($projets_recents as $projet): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <div class="font-weight-bold"><?php echo htmlspecialchars($projet['nom']); ?></div>
                                                <div class="text-muted small"><?php echo htmlspecialchars(substr($projet['description'], 0, 50)) . '...'; ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($projet['client_nom']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $projet['statut'] == 'en_cours' ? 'primary' : 
                                                ($projet['statut'] == 'termine' ? 'success' : 
                                                ($projet['statut'] == 'planifie' ? 'info' : 'warning')); 
                                        ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $projet['statut'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo number_format($projet['budget'], 0, ',', ' '); ?> MAD</td>
                                    <td><?php echo date('d/m/Y', strtotime($projet['date_debut'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actions Rapides</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <a href="clients/add.php" class="btn btn-outline-primary btn-lg w-100">
                                <i class="fas fa-user-plus fa-2x mb-2"></i><br>
                                Ajouter un Client
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="projets/add.php" class="btn btn-outline-success btn-lg w-100">
                                <i class="fas fa-plus-circle fa-2x mb-2"></i><br>
                                Nouveau Projet
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="documents/upload.php" class="btn btn-outline-info btn-lg w-100">
                                <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i><br>
                                Uploader Document
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Chart.js pour le graphique des projets
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('projectStatusChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: [<?php echo "'" . implode("','", array_map(function($s) { return ucfirst(str_replace('_', ' ', $s['statut'])); }, $projets_par_statut)) . "'"; ?>],
                datasets: [{
                    data: [<?php echo implode(',', array_column($projets_par_statut, 'count')); ?>],
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                    hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#f4b619', '#e02d1b'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                maintainAspectRatio: false,
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10,
                },
                legend: {
                    display: false
                },
                cutoutPercentage: 80,
            },
        });
    }
});
</script>

<?php include 'footer.php'; ?>