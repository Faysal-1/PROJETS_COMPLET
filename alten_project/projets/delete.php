<?php
require_once '../config.php';

// Récupération de l'ID du projet
$projet_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$projet_id) {
    redirect('list.php?error=' . urlencode('ID projet manquant'));
}

// Récupération des données du projet
try {
    $stmt = $pdo->prepare("SELECT p.*, c.nom as client_nom FROM projets p 
                          JOIN clients c ON p.client_id = c.id 
                          WHERE p.id = ?");
    $stmt->execute([$projet_id]);
    $projet = $stmt->fetch();
    
    if (!$projet) {
        redirect('list.php?error=' . urlencode('Projet introuvable'));
    }
    
    // Vérifier s'il y a des documents associés
    $stmt = $pdo->prepare("SELECT COUNT(*) as nb_documents FROM documents WHERE projet_id = ?");
    $stmt->execute([$projet_id]);
    $nb_documents = $stmt->fetch()['nb_documents'];
    
} catch(PDOException $e) {
    redirect('list.php?error=' . urlencode('Erreur lors de la récupération du projet'));
}

// Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        // Commencer une transaction
        $pdo->beginTransaction();
        
        // Supprimer d'abord tous les documents du projet
        $stmt = $pdo->prepare("DELETE FROM documents WHERE projet_id = ?");
        $stmt->execute([$projet_id]);
        
        // Supprimer le projet
        $stmt = $pdo->prepare("DELETE FROM projets WHERE id = ?");
        $stmt->execute([$projet_id]);
        
        // Valider la transaction
        $pdo->commit();
        
        $success = "Projet et tous ses documents supprimés avec succès !";
        redirect('list.php?success=' . urlencode($success));
        
    } catch(PDOException $e) {
        // Annuler la transaction en cas d'erreur
        $pdo->rollBack();
        $error = "Erreur lors de la suppression : " . $e->getMessage();
    }
}

$page_title = 'Supprimer le Projet';
$base_url = '../';

include '../header.php';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-danger"><i class="fas fa-trash me-2"></i>Supprimer le Projet</h1>
            <p class="text-muted">Suppression définitive de <?php echo htmlspecialchars($projet['nom']); ?></p>
        </div>
        <div>
            <a href="list.php" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i>Retour à la liste
            </a>
            <a href="edit.php?id=<?php echo $projet_id; ?>" class="btn btn-outline-primary">
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
                            Vous êtes sur le point de supprimer définitivement le projet 
                            <strong><?php echo htmlspecialchars($projet['nom']); ?></strong> 
                            et toutes les données associées.
                        </p>
                    </div>

                    <!-- Project Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-primary">Informations du Projet</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Nom:</strong></td>
                                    <td><?php echo htmlspecialchars($projet['nom']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Client:</strong></td>
                                    <td>
                                        <a href="../clients/edit.php?id=<?php echo $projet['client_id']; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($projet['client_nom']); ?>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Responsable:</strong></td>
                                    <td><?php echo htmlspecialchars($projet['responsable']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Budget:</strong></td>
                                    <td><?php echo number_format($projet['budget'], 2, ',', ' '); ?> MAD</td>
                                </tr>
                                <tr>
                                    <td><strong>Statut:</strong></td>
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
                                </tr>
                                <tr>
                                    <td><strong>Priorité:</strong></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $projet['priorite'] == 'critique' ? 'danger' : 
                                                ($projet['priorite'] == 'haute' ? 'warning' : 
                                                ($projet['priorite'] == 'normale' ? 'info' : 'secondary')); 
                                        ?>">
                                            <?php echo ucfirst($projet['priorite']); ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="text-danger">Données qui seront supprimées</h6>
                            
                            <div class="list-group">
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-project-diagram text-danger me-1"></i>Projet</span>
                                    <span class="badge bg-danger">1</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-file-alt text-warning me-1"></i>Documents</span>
                                    <span class="badge bg-warning"><?php echo $nb_documents; ?></span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-dollar-sign text-success me-1"></i>Budget</span>
                                    <span class="badge bg-success"><?php echo number_format($projet['budget'], 0, ',', ' '); ?> MAD</span>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <h6 class="text-info">Dates du Projet</h6>
                                <div class="small">
                                    <div><strong>Début:</strong> <?php echo date('d/m/Y', strtotime($projet['date_debut'])); ?></div>
                                    <div><strong>Fin prévue:</strong> <?php echo date('d/m/Y', strtotime($projet['date_fin_prevue'])); ?></div>
                                    <?php if ($projet['date_fin_reelle']): ?>
                                        <div><strong>Fin réelle:</strong> <?php echo date('d/m/Y', strtotime($projet['date_fin_reelle'])); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($nb_documents > 0): ?>
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-exclamation-circle me-1"></i>Impact de la suppression</h6>
                            <ul class="mb-0">
                                <li><strong><?php echo $nb_documents; ?> document(s)</strong> seront supprimés définitivement du système</li>
                                <li>Le budget de <strong><?php echo number_format($projet['budget'], 0, ',', ' '); ?> MAD</strong> sera perdu des statistiques</li>
                                <li>Toutes les données historiques du projet seront effacées</li>
                                <li>Cette action ne peut pas être annulée</li>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Project Description -->
                    <?php if (!empty($projet['description'])): ?>
                        <div class="mb-4">
                            <h6 class="text-secondary">Description du Projet</h6>
                            <div class="bg-light p-3 rounded">
                                <p class="mb-0 small"><?php echo nl2br(htmlspecialchars($projet['description'])); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Confirmation Form -->
                    <form method="POST" class="mt-4">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="understand" required>
                            <label class="form-check-label" for="understand">
                                <strong>Je comprends que cette action supprimera définitivement le projet</strong>
                            </label>
                        </div>
                        
                        <?php if ($nb_documents > 0): ?>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="understand_docs" required>
                            <label class="form-check-label" for="understand_docs">
                                <strong>Je comprends que <?php echo $nb_documents; ?> document(s) seront également supprimés</strong>
                            </label>
                        </div>
                        <?php endif; ?>
                        
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="confirm" required>
                            <label class="form-check-label" for="confirm">
                                <strong>Je confirme vouloir supprimer définitivement ce projet et toutes ses données</strong>
                            </label>
                        </div>

                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="list.php" class="btn btn-outline-secondary me-2">
                                    <i class="fas fa-times me-1"></i>Annuler
                                </a>
                                <a href="edit.php?id=<?php echo $projet_id; ?>" class="btn btn-outline-primary">
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
    const understandDocsCheck = document.getElementById('understand_docs');
    const confirmCheck = document.getElementById('confirm');
    const deleteBtn = document.getElementById('deleteBtn');
    
    function updateDeleteButton() {
        let allChecked = understandCheck.checked && confirmCheck.checked;
        
        // Si il y a des documents, vérifier aussi cette case
        if (understandDocsCheck) {
            allChecked = allChecked && understandDocsCheck.checked;
        }
        
        deleteBtn.disabled = !allChecked;
    }
    
    understandCheck.addEventListener('change', updateDeleteButton);
    confirmCheck.addEventListener('change', updateDeleteButton);
    
    if (understandDocsCheck) {
        understandDocsCheck.addEventListener('change', updateDeleteButton);
    }
    
    // Confirmation finale avant suppression
    deleteBtn.addEventListener('click', function(e) {
        const projectName = '<?php echo addslashes($projet['nom']); ?>';
        const confirmMessage = `DERNIÈRE CONFIRMATION: Êtes-vous absolument certain de vouloir supprimer le projet "${projectName}" et toutes ses données ?`;
        
        if (!confirm(confirmMessage)) {
            e.preventDefault();
        }
    });
});
</script>

<?php include '../footer.php'; ?>