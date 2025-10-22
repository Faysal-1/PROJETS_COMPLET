<?php
require_once '../config.php';

$page_title = 'Ajouter un Client';
$base_url = '../';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = sanitize($_POST['nom']);
    $email = sanitize($_POST['email']);
    $telephone = sanitize($_POST['telephone']);
    $adresse = sanitize($_POST['adresse']);
    $secteur_activite = sanitize($_POST['secteur_activite']);
    $statut = sanitize($_POST['statut']);
    
    $errors = [];
    
    // Validation
    if (empty($nom)) {
        $errors[] = "Le nom du client est obligatoire.";
    }
    
    if (empty($email)) {
        $errors[] = "L'email est obligatoire.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email n'est pas valide.";
    }
    
    if (!empty($telephone) && !preg_match('/^[\+]?[0-9\s\-\(\)]+$/', $telephone)) {
        $errors[] = "Le numéro de téléphone n'est pas valide.";
    }
    
    // Vérifier si l'email existe déjà
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM clients WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = "Un client avec cet email existe déjà.";
            }
        } catch(PDOException $e) {
            $errors[] = "Erreur lors de la vérification de l'email.";
        }
    }
    
    // Insertion en base
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO clients (nom, email, telephone, adresse, secteur_activite, statut) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nom, $email, $telephone, $adresse, $secteur_activite, $statut]);
            
            $success = "Client ajouté avec succès !";
            
            // Redirection après succès
            header("Location: list.php?success=" . urlencode($success));
            exit();
            
        } catch(PDOException $e) {
            $errors[] = "Erreur lors de l'ajout du client : " . $e->getMessage();
        }
    }
}

include '../header.php';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-user-plus me-2"></i>Ajouter un Client</h1>
            <p class="text-muted">Créer un nouveau client dans le système</p>
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
                    <h6 class="m-0 font-weight-bold text-primary">Informations du Client</h6>
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

                    <form method="POST" class="needs-validation" novalidate data-autosave="add-client">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nom" class="form-label">
                                    <i class="fas fa-building me-1"></i>Nom du Client *
                                </label>
                                <input type="text" class="form-control" id="nom" name="nom" 
                                       value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>" 
                                       required maxlength="100"
                                       placeholder="Ex: ALTEN Maroc">
                                <div class="invalid-feedback">
                                    Veuillez saisir le nom du client.
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-1"></i>Email *
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                       required maxlength="150"
                                       placeholder="contact@client.com">
                                <div class="invalid-feedback">
                                    Veuillez saisir une adresse email valide.
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="telephone" class="form-label">
                                    <i class="fas fa-phone me-1"></i>Téléphone
                                </label>
                                <input type="tel" class="form-control" id="telephone" name="telephone" 
                                       value="<?php echo htmlspecialchars($_POST['telephone'] ?? ''); ?>" 
                                       maxlength="20"
                                       placeholder="+212 522 123 456">
                                <div class="form-text">Format: +212 522 123 456</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="secteur_activite" class="form-label">
                                    <i class="fas fa-industry me-1"></i>Secteur d'Activité
                                </label>
                                <select class="form-select" id="secteur_activite" name="secteur_activite">
                                    <option value="">Sélectionner un secteur</option>
                                    <option value="Conseil en ingénierie" <?php echo ($_POST['secteur_activite'] ?? '') === 'Conseil en ingénierie' ? 'selected' : ''; ?>>Conseil en ingénierie</option>
                                    <option value="Industrie chimique" <?php echo ($_POST['secteur_activite'] ?? '') === 'Industrie chimique' ? 'selected' : ''; ?>>Industrie chimique</option>
                                    <option value="Services financiers" <?php echo ($_POST['secteur_activite'] ?? '') === 'Services financiers' ? 'selected' : ''; ?>>Services financiers</option>
                                    <option value="Télécommunications" <?php echo ($_POST['secteur_activite'] ?? '') === 'Télécommunications' ? 'selected' : ''; ?>>Télécommunications</option>
                                    <option value="Automobile" <?php echo ($_POST['secteur_activite'] ?? '') === 'Automobile' ? 'selected' : ''; ?>>Automobile</option>
                                    <option value="Aéronautique" <?php echo ($_POST['secteur_activite'] ?? '') === 'Aéronautique' ? 'selected' : ''; ?>>Aéronautique</option>
                                    <option value="Énergie" <?php echo ($_POST['secteur_activite'] ?? '') === 'Énergie' ? 'selected' : ''; ?>>Énergie</option>
                                    <option value="Santé" <?php echo ($_POST['secteur_activite'] ?? '') === 'Santé' ? 'selected' : ''; ?>>Santé</option>
                                    <option value="Transport" <?php echo ($_POST['secteur_activite'] ?? '') === 'Transport' ? 'selected' : ''; ?>>Transport</option>
                                    <option value="Autre" <?php echo ($_POST['secteur_activite'] ?? '') === 'Autre' ? 'selected' : ''; ?>>Autre</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="adresse" class="form-label">
                                <i class="fas fa-map-marker-alt me-1"></i>Adresse
                            </label>
                            <textarea class="form-control" id="adresse" name="adresse" rows="3" 
                                      maxlength="500"
                                      placeholder="Adresse complète du client"><?php echo htmlspecialchars($_POST['adresse'] ?? ''); ?></textarea>
                            <div class="form-text">Maximum 500 caractères</div>
                        </div>

                        <div class="mb-4">
                            <label for="statut" class="form-label">
                                <i class="fas fa-toggle-on me-1"></i>Statut
                            </label>
                            <select class="form-select" id="statut" name="statut" required>
                                <option value="actif" <?php echo ($_POST['statut'] ?? 'actif') === 'actif' ? 'selected' : ''; ?>>Actif</option>
                                <option value="inactif" <?php echo ($_POST['statut'] ?? '') === 'inactif' ? 'selected' : ''; ?>>Inactif</option>
                            </select>
                            <div class="form-text">Un client actif peut avoir des projets assignés</div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary" onclick="history.back()">
                                <i class="fas fa-times me-1"></i>Annuler
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Enregistrer le Client
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
                        <li><i class="fas fa-check text-success me-1"></i>Nom du client</li>
                        <li><i class="fas fa-check text-success me-1"></i>Adresse email</li>
                        <li><i class="fas fa-check text-success me-1"></i>Statut</li>
                    </ul>
                    
                    <hr>
                    
                    <h6>Conseils</h6>
                    <ul class="small text-muted">
                        <li>Utilisez un nom descriptif pour le client</li>
                        <li>L'email doit être unique dans le système</li>
                        <li>Le téléphone peut inclure l'indicatif pays</li>
                        <li>Sélectionnez le secteur d'activité approprié</li>
                    </ul>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-chart-line me-1"></i>Statistiques
                    </h6>
                </div>
                <div class="card-body">
                    <?php
                    try {
                        $stmt = $pdo->query("SELECT COUNT(*) as total FROM clients");
                        $total_clients = $stmt->fetch()['total'];
                        
                        $stmt = $pdo->query("SELECT COUNT(*) as total FROM clients WHERE DATE(date_creation) = CURDATE()");
                        $clients_today = $stmt->fetch()['total'];
                    } catch(PDOException $e) {
                        $total_clients = 0;
                        $clients_today = 0;
                    }
                    ?>
                    <div class="text-center">
                        <div class="h4 text-primary"><?php echo $total_clients; ?></div>
                        <div class="text-muted">Total clients</div>
                        <hr>
                        <div class="h5 text-success"><?php echo $clients_today; ?></div>
                        <div class="text-muted small">Ajoutés aujourd'hui</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validation en temps réel
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.needs-validation');
    const emailInput = document.getElementById('email');
    const nomInput = document.getElementById('nom');
    
    // Validation email en temps réel
    emailInput.addEventListener('blur', function() {
        if (this.value) {
            // Simulation de vérification AJAX
            // Dans un vrai projet, vous feriez un appel AJAX pour vérifier l'unicité
            if (this.checkValidity()) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        }
    });
    
    // Auto-capitalisation du nom
    nomInput.addEventListener('input', function() {
        this.value = this.value.replace(/\b\w/g, l => l.toUpperCase());
    });
});
</script>

<?php include '../footer.php'; ?>