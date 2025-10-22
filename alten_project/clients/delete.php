<?php
require_once '../config.php';

// Récupération de l'ID du client
$client_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$client_id) {
    redirect('list.php?error=' . urlencode('ID client manquant'));
}

// Récupération des données du client
try {
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
    $stmt->execute([$client_id]);
    $client = $stmt->fetch();
    
    if (!$client) {
        redirect('list.php?error=' . urlencode('Client introuvable'));
    }
    
    // Vérifier s'il y a des projets associés
    $stmt = $pdo->prepare("SELECT COUNT(*) as nb_projets FROM projets WHERE client_id = ?");
    $stmt->execute([$client_id]);
    $nb_projets = $stmt->fetch()['nb_projets'];
    
} catch(PDOException $e) {
    redirect('list.php?error=' . urlencode('Erreur lors de la récupération du client'));
}

// Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        // Commencer une transaction
        $pdo->beginTransaction();
        
        // Supprimer d'abord tous les documents liés aux projets du client
        $stmt = $pdo->prepare("DELETE d FROM documents d 
                              INNER JOIN projets p ON d.projet_id = p.id 
                              WHERE p.client_id = ?");
        $stmt->execute([$client_id]);
        
        // Supprimer tous les projets du client
        $stmt = $pdo->prepare("DELETE FROM projets WHERE client_id = ?");
        $stmt->execute([$client_id]);
        
        // Supprimer le client
        $stmt = $pdo->prepare("DELETE FROM clients WHERE id = ?");
        $stmt->execute([$client_id]);
        
        // Valider la transaction
        $pdo->commit();
        
        $success = "Client et toutes ses données associées supprimés avec succès !";
        redirect('list.php?success=' . urlencode($success));
        
    } catch(PDOException $e) {
        // Annuler la transaction en cas d'erreur
        $pdo->rollBack();
        $error = "Erreur lors de la suppression : " . $e->getMessage();
    }
}

$page_title = 'Supprimer le Client';
$base_url = '../';

include '../header.php';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-danger"><i class="fas fa-user-times me-2"></i>Supprimer le Client</h1>
            <p class="text-muted">Suppression définitive de <?php echo htmlspecialchars($client['nom']); ?></p>
        </div>
        <div>
            <a href="list.php" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i>Retour à la liste
            </a>
            <a href="edit.php?id=<?php echo $client_id; ?>" class="btn btn-outline-primary">
                <i class="fas fa-edit me-1"></i>Modifier plutôt
            </a>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-1"></i><?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Warning Card -->
            <div class="card shadow mb-4 border-danger">
                <div class="card-header py-3 bg-danger text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-exclamation-triangle me-2"></i>Attention - Suppression Définitive
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-triangle me-1"></i>Cette action est irréversible !</h5>
                        <p class="mb-0">
                            Vous êtes sur le point de supprimer définitivement le client 
                            <strong><?php echo htmlspecialchars($client['nom']); ?></strong> 
                            et toutes les données associées.
                        </p>
                    </div>

                    <!-- Client Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-primary">Informations du Client</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Nom:</strong></td>
                                    <td><?php echo htmlspecialchars($client['nom']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td><?php echo htmlspecialchars($client['email']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Téléphone:</strong></td>
                                    <td><?php echo htmlspecialchars($client['telephone'] ?: 'Non renseigné'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Secteur:</strong></td>
                                    <td><?php echo htmlspecialchars($client['secteur_activite'] ?: 'Non défini'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Statut:</strong></td>
                                    <td>
                                        <span class="badge bg-<?php echo $client['statut'] === 'actif' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($client['statut']); ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="text-danger">Données qui seront supprimées</h6>
                            <?php
                            try {
                                // Compter les projets
                                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM projets WHERE client_id = ?");
                                $stmt->execute([$client_id]);
                                $nb_projets = $stmt->fetch()['count'];
                                
                                // Compter les documents
                                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM documents d 
                                                      INNER JOIN projets p ON d.projet_id = p.id 
                                                      WHERE p.client_id = ?");
                                $stmt->execute([$client_id]);
                                $nb_documents = $stmt->fetch()['count'];
                                
                                // Calculer le budget total
                                $stmt = $pdo->prepare("SELECT SUM(budget) as total FROM projets WHERE client_id = ?");
                                $stmt->execute([$client_id]);
                                $budget_total = $stmt->fetch()['total'] ?? 0;
                                
                            } catch(PDOException $e) {
                                $nb_projets = 0;
                                $nb_documents = 0;
                                $budget_total = 0;
                            }
                            ?>
                            
                            <div class="list-group">
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-user text-danger me-1"></i>Client</span>
                                    <span class="badge bg-danger">1</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-project-diagram text-warning me-1"></i>Projets</span>
                                    <span class="badge bg-warning"><?php echo $nb_projets; ?></span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-file-alt text-info me-1"></i>Documents</span>
                                    <span class="badge bg-info"><?php echo $nb_documents; ?></span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-dollar-sign text-success me-1"></i>Budget Total</span>
                                    <span class="badge bg-success"><?php echo number_format($budget_total, 0, ',', ' '); ?> MAD</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($nb_projets > 0): ?>
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-exclamation-circle me-1"></i>Impact de la suppression</h6>
                            <ul class="mb-0">
                                <li><strong><?php echo $nb_projets; ?> projet(s)</strong> seront supprimés définitivement</li>
                                <li><strong><?php echo $nb_documents; ?> document(s)</strong> seront supprimés du système</li>
                                <li>Le budget total de <strong><?php echo number_format($budget_total, 0, ',', ' '); ?> MAD</strong> sera perdu</li>
                                <li>Toutes les données historiques seront effacées</li>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Confirmation Form -->
                    <form method="POST" class="mt-4">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="understand" required>
                            <label class="form-check-label" for="understand">
                                <strong>Je comprends que cette action est irréversible</strong>
                            </label>
                        </div>
                        
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="confirm" required>
                            <label class="form-check-label" for="confirm">
                                <strong>Je confirme vouloir supprimer définitivement ce client et toutes ses données</strong>
                            </label>
                        </div>

                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="list.php" class="btn btn-outline-secondary me-2">
                                    <i class="fas fa-times me-1"></i>Annuler
                                </a>
                                <a href="edit.php?id=<?php echo $client_id; ?>" class="btn btn-outline-primary">
                                    <i class="fas fa-edit me-1"></i>Modifier plutôt
                                </a>
                            </div>
                            <button type="submit" name="confirm_delete" class="btn btn-danger" id="deleteBtn" disabled>
                                <i class="fas fa-trash me-1"></i>Supprimer Définitivement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const understandCheck = document.getElementById('understand');
    const confirmCheck = document.getElementById('confirm');
    const deleteBtn = document.getElementById('deleteBtn');
    
    function updateDeleteButton() {
        deleteBtn.disabled = !(understandCheck.checked && confirmCheck.checked);
    }
    
    understandCheck.addEventListener('change', updateDeleteButton);
    confirmCheck.addEventListener('change', updateDeleteButton);
    
    // Confirmation finale avant suppression
    deleteBtn.addEventListener('click', function(e) {
        if (!confirm('DERNIÈRE CONFIRMATION: Êtes-vous absolument certain de vouloir supprimer ce client et toutes ses données ?')) {
            e.preventDefault();
        }
    });
});
</script>

<?php include '../footer.php'; ?>