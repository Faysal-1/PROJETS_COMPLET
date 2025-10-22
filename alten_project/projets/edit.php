<?php
require_once '../config.php';

$page_title = 'Modifier un Projet';
$base_url = '../';

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
} catch(PDOException $e) {
    redirect('list.php?error=' . urlencode('Erreur lors de la récupération du projet'));
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = sanitize($_POST['nom']);
    $description = sanitize($_POST['description']);
    $client_id = (int)$_POST['client_id'];
    $date_debut = sanitize($_POST['date_debut']);
    $date_fin_prevue = sanitize($_POST['date_fin_prevue']);
    $date_fin_reelle = !empty($_POST['date_fin_reelle']) ? sanitize($_POST['date_fin_reelle']) : null;
    $budget = floatval($_POST['budget']);
    $statut = sanitize($_POST['statut']);
    $priorite = sanitize($_POST['priorite']);
    $responsable = sanitize($_POST['responsable']);
    
    $errors = [];
    
    // Validation
    if (empty($nom)) {
        $errors[] = "Le nom du projet est obligatoire.";
    }
    
    if (empty($description)) {
        $errors[] = "La description du projet est obligatoire.";
    }
    
    if ($client_id <= 0) {
        $errors[] = "Veuillez sélectionner un client.";
    }
    
    if (empty($date_debut)) {
        $errors[] = "La date de début est obligatoire.";
    }
    
    if (empty($date_fin_prevue)) {
        $errors[] = "La date de fin prévue est obligatoire.";
    }
    
    if (!empty($date_debut) && !empty($date_fin_prevue)) {
        if (strtotime($date_debut) >= strtotime($date_fin_prevue)) {
            $errors[] = "La date de fin prévue doit être postérieure à la date de début.";
        }
    }
    
    if (!empty($date_fin_reelle) && !empty($date_debut)) {
        if (strtotime($date_fin_reelle) < strtotime($date_debut)) {
            $errors[] = "La date de fin réelle ne peut pas être antérieure à la date de début.";
        }
    }
    
    if ($budget < 0) {
        $errors[] = "Le budget ne peut pas être négatif.";
    }
    
    if (empty($responsable)) {
        $errors[] = "Le responsable du projet est obligatoire.";
    }
    
    // Validation du statut avec date de fin réelle
    if ($statut === 'termine' && empty($date_fin_reelle)) {
        $errors[] = "Une date de fin réelle est requise pour un projet terminé.";
    }
    
    // Vérifier que le client existe et est actif
    if (empty($errors) && $client_id > 0) {
        try {
            $stmt = $pdo->prepare("SELECT statut FROM clients WHERE id = ?");
            $stmt->execute([$client_id]);
            $client = $stmt->fetch();
            if (!$client) {
                $errors[] = "Le client sélectionné n'existe pas.";
            } elseif ($client['statut'] !== 'actif') {
                $errors[] = "Le client sélectionné n'est pas actif.";
            }
        } catch(PDOException $e) {
            $errors[] = "Erreur lors de la vérification du client.";
        }
    }
    
    // Mise à jour en base
    if (empty($errors)) {
        try {
            $sql = "UPDATE projets SET nom = ?, description = ?, client_id = ?, date_debut = ?, 
                    date_fin_prevue = ?, date_fin_reelle = ?, budget = ?, statut = ?, 
                    priorite = ?, responsable = ?, date_modification = NOW() 
                    WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nom, $description, $client_id, $date_debut, $date_fin_prevue, 
                           $date_fin_reelle, $budget, $statut, $priorite, $responsable, $projet_id]);
            
            $success = "Projet modifié avec succès !";
            
            // Redirection après succès
            redirect('list.php?success=' . urlencode($success));
            
        } catch(PDOException $e) {
            $errors[] = "Erreur lors de la modification du projet : " . $e->getMessage();
        }
    }
}

// Récupération des statistiques du projet
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as nb_documents FROM documents WHERE projet_id = ?");
    $stmt->execute([$projet_id]);
    $nb_documents = $stmt->fetch()['nb_documents'];
} catch(PDOException $e) {
    $nb_documents = 0;
}

// Récupération de la liste des clients actifs
try {
    $stmt = $pdo->query("SELECT id, nom FROM clients WHERE statut = 'actif' ORDER BY nom");
    $clients = $stmt->fetchAll();
} catch(PDOException $e) {
    $clients = [];
}

include '../header.php';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-edit me-2"></i>Modifier le Projet</h1>
            <p class="text-muted">Modification de <?php echo htmlspecialchars($projet['nom']); ?></p>
        </div>
        <div>
            <a href="list.php" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i>Retour à la liste
            </a>
            <a href="../documents/list.php?projet_id=<?php echo $projet_id; ?>" class="btn btn-outline-info">
                <i class="fas fa-file-alt me-1"></i>Voir les documents
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Main Form -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informations du Projet</h6>
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

                    <form method="POST" class="needs-validation" novalidate data-autosave="edit-project-<?php echo $projet_id; ?>">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="nom" class="form-label">
                                    <i class="fas fa-project-diagram me-1"></i>Nom du Projet *
                                </label>
                                <input type="text" class="form-control" id="nom" name="nom" 
                                       value="<?php echo htmlspecialchars($_POST['nom'] ?? $projet['nom']); ?>" 
                                       required maxlength="150"
                                       placeholder="Ex: Modernisation du SI">
                                <div class="invalid-feedback">
                                    Veuillez saisir le nom du projet.
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="client_id" class="form-label">
                                    <i class="fas fa-building me-1"></i>Client *
                                </label>
                                <select class="form-select" id="client_id" name="client_id" required>
                                    <option value="">Sélectionner un client</option>
                                    <?php foreach($clients as $client): ?>
                                        <option value="<?php echo $client['id']; ?>" 
                                                <?php echo (($_POST['client_id'] ?? $projet['client_id']) == $client['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($client['nom']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Veuillez sélectionner un client.
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left me-1"></i>Description *
                            </label>
                            <textarea class="form-control" id="description" name="description" rows="4" 
                                      required maxlength="1000"
                                      placeholder="Description détaillée du projet..."><?php echo htmlspecialchars($_POST['description'] ?? $projet['description']); ?></textarea>
                            <div class="form-text">Maximum 1000 caractères</div>
                            <div class="invalid-feedback">
                                Veuillez saisir une description du projet.
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="date_debut" class="form-label">
                                    <i class="fas fa-calendar-alt me-1"></i>Date de Début *
                                </label>
                                <input type="date" class="form-control" id="date_debut" name="date_debut" 
                                       value="<?php echo htmlspecialchars($_POST['date_debut'] ?? $projet['date_debut']); ?>" 
                                       required>
                                <div class="invalid-feedback">
                                    Veuillez saisir la date de début.
                                </div>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="date_fin_prevue" class="form-label">
                                    <i class="fas fa-calendar-check me-1"></i>Date de Fin Prévue *
                                </label>
                                <input type="date" class="form-control" id="date_fin_prevue" name="date_fin_prevue" 
                                       value="<?php echo htmlspecialchars($_POST['date_fin_prevue'] ?? $projet['date_fin_prevue']); ?>" 
                                       required>
                                <div class="invalid-feedback">
                                    Veuillez saisir la date de fin prévue.
                                </div>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="date_fin_reelle" class="form-label">
                                    <i class="fas fa-calendar-times me-1"></i>Date de Fin Réelle
                                </label>
                                <input type="date" class="form-control" id="date_fin_reelle" name="date_fin_reelle" 
                                       value="<?php echo htmlspecialchars($_POST['date_fin_reelle'] ?? $projet['date_fin_reelle']); ?>">
                                <div class="form-text">Requis si statut = Terminé</div>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="budget" class="form-label">
                                    <i class="fas fa-dollar-sign me-1"></i>Budget (MAD)
                                </label>
                                <input type="number" class="form-control" id="budget" name="budget" 
                                       value="<?php echo htmlspecialchars($_POST['budget'] ?? $projet['budget']); ?>" 
                                       min="0" step="0.01"
                                       placeholder="0.00">
                                <div class="form-text">Budget en dirhams marocains</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="statut" class="form-label">
                                    <i class="fas fa-flag me-1"></i>Statut
                                </label>
                                <select class="form-select" id="statut" name="statut" required>
                                    <?php 
                                    $current_statut = $_POST['statut'] ?? $projet['statut'];
                                    $statuts = [
                                        'planifie' => 'Planifié',
                                        'en_cours' => 'En cours',
                                        'termine' => 'Terminé',
                                        'suspendu' => 'Suspendu',
                                        'annule' => 'Annulé'
                                    ];
                                    foreach($statuts as $value => $label):
                                    ?>
                                        <option value="<?php echo $value; ?>" <?php echo $current_statut === $value ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="priorite" class="form-label">
                                    <i class="fas fa-exclamation-circle me-1"></i>Priorité
                                </label>
                                <select class="form-select" id="priorite" name="priorite" required>
                                    <?php 
                                    $current_priorite = $_POST['priorite'] ?? $projet['priorite'];
                                    $priorites = [
                                        'basse' => 'Basse',
                                        'normale' => 'Normale',
                                        'haute' => 'Haute',
                                        'critique' => 'Critique'
                                    ];
                                    foreach($priorites as $value => $label):
                                    ?>
                                        <option value="<?php echo $value; ?>" <?php echo $current_priorite === $value ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="responsable" class="form-label">
                                    <i class="fas fa-user-tie me-1"></i>Responsable *
                                </label>
                                <input type="text" class="form-control" id="responsable" name="responsable" 
                                       value="<?php echo htmlspecialchars($_POST['responsable'] ?? $projet['responsable']); ?>" 
                                       required maxlength="100"
                                       placeholder="Nom du responsable">
                                <div class="invalid-feedback">
                                    Veuillez saisir le nom du responsable.
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary" onclick="history.back()">
                                <i class="fas fa-times me-1"></i>Annuler
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Enregistrer les Modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Project Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-info-circle me-1"></i>Informations Projet
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-6">
                            <div class="h4 text-primary"><?php echo $nb_documents; ?></div>
                            <div class="text-muted small">Documents</div>
                        </div>
                        <div class="col-6">
                            <?php
                            $duree = 0;
                            if ($projet['date_debut'] && $projet['date_fin_prevue']) {
                                $debut = new DateTime($projet['date_debut']);
                                $fin = new DateTime($projet['date_fin_prevue']);
                                $duree = $debut->diff($fin)->days;
                            }
                            ?>
                            <div class="h4 text-success"><?php echo $duree; ?></div>
                            <div class="text-muted small">Jours prévus</div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="small text-muted">
                        <div class="mb-2">
                            <strong>Client:</strong><br>
                            <a href="../clients/edit.php?id=<?php echo $projet['client_id']; ?>" class="text-decoration-none">
                                <?php echo htmlspecialchars($projet['client_nom']); ?>
                            </a>
                        </div>
                        <div class="mb-2">
                            <strong>Créé le:</strong><br>
                            <?php echo date('d/m/Y à H:i', strtotime($projet['date_creation'])); ?>
                        </div>
                        <div>
                            <strong>Dernière modification:</strong><br>
                            <?php echo date('d/m/Y à H:i', strtotime($projet['date_modification'])); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-chart-line me-1"></i>Progression
                    </h6>
                </div>
                <div class="card-body">
                    <?php
                    $progress = 0;
                    $status_colors = [
                        'planifie' => 'info',
                        'en_cours' => 'primary', 
                        'termine' => 'success',
                        'suspendu' => 'warning',
                        'annule' => 'danger'
                    ];
                    
                    switch($projet['statut']) {
                        case 'planifie': $progress = 10; break;
                        case 'en_cours': $progress = 50; break;
                        case 'termine': $progress = 100; break;
                        case 'suspendu': $progress = 25; break;
                        case 'annule': $progress = 0; break;
                    }
                    
                    $color = $status_colors[$projet['statut']] ?? 'secondary';
                    ?>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small">Avancement</span>
                            <span class="small"><?php echo $progress; ?>%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-<?php echo $color; ?>" 
                                 style="width: <?php echo $progress; ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <span class="badge bg-<?php echo $color; ?> fs-6">
                            <?php echo ucfirst(str_replace('_', ' ', $projet['statut'])); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-bolt me-1"></i>Actions Rapides
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="../documents/upload.php?projet_id=<?php echo $projet_id; ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-upload me-1"></i>Ajouter Document
                        </a>
                        <a href="../documents/list.php?projet_id=<?php echo $projet_id; ?>" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-file-alt me-1"></i>Voir Documents
                        </a>
                        <a href="../clients/edit.php?id=<?php echo $projet['client_id']; ?>" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-building me-1"></i>Voir Client
                        </a>
                        <?php if ($nb_documents == 0): ?>
                        <a href="delete.php?id=<?php echo $projet_id; ?>" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-trash me-1"></i>Supprimer Projet
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validation en temps réel et améliorations UX
document.addEventListener('DOMContentLoaded', function() {
    const dateDebutInput = document.getElementById('date_debut');
    const dateFinPrevueInput = document.getElementById('date_fin_prevue');
    const dateFinReelleInput = document.getElementById('date_fin_reelle');
    const statutSelect = document.getElementById('statut');
    const nomInput = document.getElementById('nom');
    
    // Validation des dates
    function validateDates() {
        // Date début vs fin prévue
        if (dateDebutInput.value && dateFinPrevueInput.value) {
            const dateDebut = new Date(dateDebutInput.value);
            const dateFinPrevue = new Date(dateFinPrevueInput.value);
            
            if (dateDebut >= dateFinPrevue) {
                dateFinPrevueInput.setCustomValidity('La date de fin prévue doit être postérieure à la date de début');
                dateFinPrevueInput.classList.add('is-invalid');
            } else {
                dateFinPrevueInput.setCustomValidity('');
                dateFinPrevueInput.classList.remove('is-invalid');
            }
        }
        
        // Date début vs fin réelle
        if (dateDebutInput.value && dateFinReelleInput.value) {
            const dateDebut = new Date(dateDebutInput.value);
            const dateFinReelle = new Date(dateFinReelleInput.value);
            
            if (dateFinReelle < dateDebut) {
                dateFinReelleInput.setCustomValidity('La date de fin réelle ne peut pas être antérieure à la date de début');
                dateFinReelleInput.classList.add('is-invalid');
            } else {
                dateFinReelleInput.setCustomValidity('');
                dateFinReelleInput.classList.remove('is-invalid');
            }
        }
    }
    
    // Gestion du statut "Terminé"
    function handleStatutChange() {
        if (statutSelect.value === 'termine') {
            dateFinReelleInput.required = true;
            dateFinReelleInput.parentElement.querySelector('.form-text').innerHTML = 
                '<span class="text-danger">Requis pour un projet terminé</span>';
            
            // Auto-remplir avec la date du jour si vide
            if (!dateFinReelleInput.value) {
                dateFinReelleInput.value = new Date().toISOString().split('T')[0];
            }
        } else {
            dateFinReelleInput.required = false;
            dateFinReelleInput.parentElement.querySelector('.form-text').innerHTML = 
                'Requis si statut = Terminé';
        }
        validateDates();
    }
    
    dateDebutInput.addEventListener('change', validateDates);
    dateFinPrevueInput.addEventListener('change', validateDates);
    dateFinReelleInput.addEventListener('change', validateDates);
    statutSelect.addEventListener('change', handleStatutChange);
    
    // Auto-capitalisation du nom
    nomInput.addEventListener('input', function() {
        this.value = this.value.replace(/\b\w/g, l => l.toUpperCase());
    });
    
    // Initialiser la validation du statut
    handleStatutChange();
});
</script>

<?php include '../footer.php'; ?>