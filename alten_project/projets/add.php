<?php
require_once '../config.php';

$page_title = 'Ajouter un Projet';
$base_url = '../';

// Récupération du client pré-sélectionné si fourni
$preselected_client = isset($_GET['client_id']) ? (int)$_GET['client_id'] : 0;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = sanitize($_POST['nom']);
    $description = sanitize($_POST['description']);
    $client_id = (int)$_POST['client_id'];
    $date_debut = sanitize($_POST['date_debut']);
    $date_fin_prevue = sanitize($_POST['date_fin_prevue']);
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
            $errors[] = "La date de fin doit être postérieure à la date de début.";
        }
    }
    
    if ($budget < 0) {
        $errors[] = "Le budget ne peut pas être négatif.";
    }
    
    if (empty($responsable)) {
        $errors[] = "Le responsable du projet est obligatoire.";
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
    
    // Insertion en base
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO projets (nom, description, client_id, date_debut, date_fin_prevue, 
                    budget, statut, priorite, responsable) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nom, $description, $client_id, $date_debut, $date_fin_prevue, 
                           $budget, $statut, $priorite, $responsable]);
            
            $success = "Projet ajouté avec succès !";
            
            // Redirection après succès
            header("Location: list.php?success=" . urlencode($success));
            exit();
            
        } catch(PDOException $e) {
            $errors[] = "Erreur lors de l'ajout du projet : " . $e->getMessage();
        }
    }
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
            <h1 class="h3 mb-0"><i class="fas fa-plus-circle me-2"></i>Ajouter un Projet</h1>
            <p class="text-muted">Créer un nouveau projet dans le système</p>
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

                    <form method="POST" class="needs-validation" novalidate data-autosave="add-project">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="nom" class="form-label">
                                    <i class="fas fa-project-diagram me-1"></i>Nom du Projet *
                                </label>
                                <input type="text" class="form-control" id="nom" name="nom" 
                                       value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>" 
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
                                                <?php echo (($_POST['client_id'] ?? $preselected_client) == $client['id']) ? 'selected' : ''; ?>>
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
                                      placeholder="Description détaillée du projet..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                            <div class="form-text">Maximum 1000 caractères</div>
                            <div class="invalid-feedback">
                                Veuillez saisir une description du projet.
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="date_debut" class="form-label">
                                    <i class="fas fa-calendar-alt me-1"></i>Date de Début *
                                </label>
                                <input type="date" class="form-control" id="date_debut" name="date_debut" 
                                       value="<?php echo htmlspecialchars($_POST['date_debut'] ?? date('Y-m-d')); ?>" 
                                       required>
                                <div class="invalid-feedback">
                                    Veuillez saisir la date de début.
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="date_fin_prevue" class="form-label">
                                    <i class="fas fa-calendar-check me-1"></i>Date de Fin Prévue *
                                </label>
                                <input type="date" class="form-control" id="date_fin_prevue" name="date_fin_prevue" 
                                       value="<?php echo htmlspecialchars($_POST['date_fin_prevue'] ?? ''); ?>" 
                                       required>
                                <div class="invalid-feedback">
                                    Veuillez saisir la date de fin prévue.
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="budget" class="form-label">
                                    <i class="fas fa-dollar-sign me-1"></i>Budget (MAD)
                                </label>
                                <input type="number" class="form-control" id="budget" name="budget" 
                                       value="<?php echo htmlspecialchars($_POST['budget'] ?? '0'); ?>" 
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
                                    <option value="planifie" <?php echo ($_POST['statut'] ?? 'planifie') === 'planifie' ? 'selected' : ''; ?>>Planifié</option>
                                    <option value="en_cours" <?php echo ($_POST['statut'] ?? '') === 'en_cours' ? 'selected' : ''; ?>>En cours</option>
                                    <option value="termine" <?php echo ($_POST['statut'] ?? '') === 'termine' ? 'selected' : ''; ?>>Terminé</option>
                                    <option value="suspendu" <?php echo ($_POST['statut'] ?? '') === 'suspendu' ? 'selected' : ''; ?>>Suspendu</option>
                                    <option value="annule" <?php echo ($_POST['statut'] ?? '') === 'annule' ? 'selected' : ''; ?>>Annulé</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="priorite" class="form-label">
                                    <i class="fas fa-exclamation-circle me-1"></i>Priorité
                                </label>
                                <select class="form-select" id="priorite" name="priorite" required>
                                    <option value="basse" <?php echo ($_POST['priorite'] ?? '') === 'basse' ? 'selected' : ''; ?>>Basse</option>
                                    <option value="normale" <?php echo ($_POST['priorite'] ?? 'normale') === 'normale' ? 'selected' : ''; ?>>Normale</option>
                                    <option value="haute" <?php echo ($_POST['priorite'] ?? '') === 'haute' ? 'selected' : ''; ?>>Haute</option>
                                    <option value="critique" <?php echo ($_POST['priorite'] ?? '') === 'critique' ? 'selected' : ''; ?>>Critique</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="responsable" class="form-label">
                                    <i class="fas fa-user-tie me-1"></i>Responsable *
                                </label>
                                <input type="text" class="form-control" id="responsable" name="responsable" 
                                       value="<?php echo htmlspecialchars($_POST['responsable'] ?? ''); ?>" 
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
                                <i class="fas fa-save me-1"></i>Enregistrer le Projet
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
                    <h6>Champs obligatoires</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success me-1"></i>Nom du projet</li>
                        <li><i class="fas fa-check text-success me-1"></i>Client</li>
                        <li><i class="fas fa-check text-success me-1"></i>Description</li>
                        <li><i class="fas fa-check text-success me-1"></i>Date de début</li>
                        <li><i class="fas fa-check text-success me-1"></i>Date de fin prévue</li>
                        <li><i class="fas fa-check text-success me-1"></i>Responsable</li>
                    </ul>
                    
                    <hr>
                    
                    <h6>Conseils</h6>
                    <ul class="small text-muted">
                        <li>Utilisez un nom descriptif et unique</li>
                        <li>La description doit être claire et complète</li>
                        <li>Vérifiez que les dates sont cohérentes</li>
                        <li>Le budget peut être ajusté plus tard</li>
                        <li>Choisissez la priorité appropriée</li>
                    </ul>
                </div>
            </div>

            <!-- Status Guide -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-question-circle me-1"></i>Guide des Statuts
                    </h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="mb-2">
                            <span class="badge bg-info me-2">Planifié</span>
                            Projet en phase de planification
                        </div>
                        <div class="mb-2">
                            <span class="badge bg-primary me-2">En cours</span>
                            Projet actuellement en développement
                        </div>
                        <div class="mb-2">
                            <span class="badge bg-success me-2">Terminé</span>
                            Projet livré et finalisé
                        </div>
                        <div class="mb-2">
                            <span class="badge bg-warning me-2">Suspendu</span>
                            Projet temporairement arrêté
                        </div>
                        <div class="mb-2">
                            <span class="badge bg-danger me-2">Annulé</span>
                            Projet définitivement arrêté
                        </div>
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
                        $stmt = $pdo->query("SELECT COUNT(*) as total FROM projets");
                        $total_projets = $stmt->fetch()['total'];
                        
                        $stmt = $pdo->query("SELECT COUNT(*) as total FROM projets WHERE DATE(date_creation) = CURDATE()");
                        $projets_today = $stmt->fetch()['total'];
                        
                        $stmt = $pdo->query("SELECT COUNT(*) as total FROM clients WHERE statut = 'actif'");
                        $clients_actifs = $stmt->fetch()['total'];
                    } catch(PDOException $e) {
                        $total_projets = 0;
                        $projets_today = 0;
                        $clients_actifs = 0;
                    }
                    ?>
                    <div class="text-center">
                        <div class="row">
                            <div class="col-4">
                                <div class="h4 text-primary"><?php echo $total_projets; ?></div>
                                <div class="text-muted small">Total projets</div>
                            </div>
                            <div class="col-4">
                                <div class="h4 text-success"><?php echo $projets_today; ?></div>
                                <div class="text-muted small">Aujourd'hui</div>
                            </div>
                            <div class="col-4">
                                <div class="h4 text-info"><?php echo $clients_actifs; ?></div>
                                <div class="text-muted small">Clients actifs</div>
                            </div>
                        </div>
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
    const dateFinInput = document.getElementById('date_fin_prevue');
    const budgetInput = document.getElementById('budget');
    const nomInput = document.getElementById('nom');
    
    // Validation des dates
    function validateDates() {
        if (dateDebutInput.value && dateFinInput.value) {
            const dateDebut = new Date(dateDebutInput.value);
            const dateFin = new Date(dateFinInput.value);
            
            if (dateDebut >= dateFin) {
                dateFinInput.setCustomValidity('La date de fin doit être postérieure à la date de début');
                dateFinInput.classList.add('is-invalid');
            } else {
                dateFinInput.setCustomValidity('');
                dateFinInput.classList.remove('is-invalid');
                dateFinInput.classList.add('is-valid');
            }
        }
    }
    
    dateDebutInput.addEventListener('change', validateDates);
    dateFinInput.addEventListener('change', validateDates);
    
    // Auto-suggestion de date de fin (3 mois après le début par défaut)
    dateDebutInput.addEventListener('change', function() {
        if (this.value && !dateFinInput.value) {
            const dateDebut = new Date(this.value);
            dateDebut.setMonth(dateDebut.getMonth() + 3);
            dateFinInput.value = dateDebut.toISOString().split('T')[0];
            validateDates();
        }
    });
    
    // Formatage du budget
    budgetInput.addEventListener('blur', function() {
        if (this.value) {
            this.value = parseFloat(this.value).toFixed(2);
        }
    });
    
    // Auto-capitalisation du nom
    nomInput.addEventListener('input', function() {
        this.value = this.value.replace(/\b\w/g, l => l.toUpperCase());
    });
});
</script>

<?php include '../footer.php'; ?>