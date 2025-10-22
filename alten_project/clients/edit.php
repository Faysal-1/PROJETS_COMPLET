<?php
require_once '../config.php';

$page_title = 'Modifier un Client';
$base_url = '../';

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
} catch(PDOException $e) {
    redirect('list.php?error=' . urlencode('Erreur lors de la récupération du client'));
}

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
    
    // Vérifier si l'email existe déjà (sauf pour ce client)
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM clients WHERE email = ? AND id != ?");
            $stmt->execute([$email, $client_id]);
            if ($stmt->fetch()) {
                $errors[] = "Un autre client avec cet email existe déjà.";
            }
        } catch(PDOException $e) {
            $errors[] = "Erreur lors de la vérification de l'email.";
        }
    }
    
    // Mise à jour en base
    if (empty($errors)) {
        try {
            $sql = "UPDATE clients SET nom = ?, email = ?, telephone = ?, adresse = ?, 
                    secteur_activite = ?, statut = ?, date_modification = NOW() 
                    WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nom, $email, $telephone, $adresse, $secteur_activite, $statut, $client_id]);
            
            $success = "Client modifié avec succès !";
            
            // Redirection après succès
            redirect('list.php?success=' . urlencode($success));
            
        } catch(PDOException $e) {
            $errors[] = "Erreur lors de la modification du client : " . $e->getMessage();
        }
    }
}

// Récupération des statistiques du client
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as nb_projets FROM projets WHERE client_id = ?");
    $stmt->execute([$client_id]);
    $nb_projets = $stmt->fetch()['nb_projets'];
    
    $stmt = $pdo->prepare("SELECT SUM(budget) as budget_total FROM projets WHERE client_id = ?");
    $stmt->execute([$client_id]);
    $budget_total = $stmt->fetch()['budget_total'] ?? 0;
} catch(PDOException $e) {
    $nb_projets = 0;
    $budget_total = 0;
}

include '../header.php';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-user-edit me-2"></i>Modifier le Client</h1>
            <p class="text-muted">Modification des informations de <?php echo htmlspecialchars($client['nom']); ?></p>
        </div>
        <div>
            <a href="list.php" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i>Retour à la liste
            </a>
            <a href="../projets/list.php?client_id=<?php echo $client_id; ?>" class="btn btn-outline-info">
                <i class="fas fa-project-diagram me-1"></i>Voir les projets
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

                    <form method="POST" class="needs-validation" novalidate data-autosave="edit-client-<?php echo $client_id; ?>">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nom" class="form-label">
                                    <i class="fas fa-building me-1"></i>Nom du Client *
                                </label>
                                <input type="text" class="form-control" id="nom" name="nom" 
                                       value="<?php echo htmlspecialchars($_POST['nom'] ?? $client['nom']); ?>" 
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
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? $client['email']); ?>" 
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
                                       value="<?php echo htmlspecialchars($_POST['telephone'] ?? $client['telephone']); ?>" 
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
                                    <?php 
                                    $secteurs = [
                                        'Conseil en ingénierie', 'Industrie chimique', 'Services financiers',
                                        'Télécommunications', 'Automobile', 'Aéronautique', 'Énergie', 
                                        'Santé', 'Transport', 'Autre'
                                    ];
                                    $current_secteur = $_POST['secteur_activite'] ?? $client['secteur_activite'];
                                    foreach($secteurs as $secteur): 
                                    ?>
                                        <option value="<?php echo $secteur; ?>" <?php echo $current_secteur === $secteur ? 'selected' : ''; ?>>
                                            <?php echo $secteur; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="adresse" class="form-label">
                                <i class="fas fa-map-marker-alt me-1"></i>Adresse
                            </label>
                            <textarea class="form-control" id="adresse" name="adresse" rows="3" 
                                      maxlength="500"
                                      placeholder="Adresse complète du client"><?php echo htmlspecialchars($_POST['adresse'] ?? $client['adresse']); ?></textarea>
                            <div class="form-text">Maximum 500 caractères</div>
                        </div>

                        <div class="mb-4">
                            <label for="statut" class="form-label">
                                <i class="fas fa-toggle-on me-1"></i>Statut
                            </label>
                            <select class="form-select" id="statut" name="statut" required>
                                <?php $current_statut = $_POST['statut'] ?? $client['statut']; ?>
                                <option value="actif" <?php echo $current_statut === 'actif' ? 'selected' : ''; ?>>Actif</option>
                                <option value="inactif" <?php echo $current_statut === 'inactif' ? 'selected' : ''; ?>>Inactif</option>
                            </select>
                            <div class="form-text">
                                <?php if ($nb_projets > 0): ?>
                                    <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                                    Ce client a <?php echo $nb_projets; ?> projet(s) associé(s)
                                <?php else: ?>
                                    Un client actif peut avoir des projets assignés
                                <?php endif; ?>
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
            <!-- Client Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-info-circle me-1"></i>Informations Client
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="h4 text-primary"><?php echo $nb_projets; ?></div>
                            <div class="text-muted small">Projets</div>
                        </div>
                        <div class="col-6">
                            <div class="h4 text-success"><?php echo number_format($budget_total, 0, ',', ' '); ?></div>
                            <div class="text-muted small">Budget Total (MAD)</div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="small text-muted">
                        <div class="mb-2">
                            <strong>Créé le:</strong><br>
                            <?php echo date('d/m/Y à H:i', strtotime($client['date_creation'])); ?>
                        </div>
                        <div>
                            <strong>Dernière modification:</strong><br>
                            <?php echo date('d/m/Y à H:i', strtotime($client['date_modification'])); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-bolt me-1"></i>Actions Rapides
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="../projets/add.php?client_id=<?php echo $client_id; ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>Nouveau Projet
                        </a>
                        <a href="mailto:<?php echo htmlspecialchars($client['email']); ?>" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-envelope me-1"></i>Envoyer Email
                        </a>
                        <?php if ($client['telephone']): ?>
                        <a href="tel:<?php echo htmlspecialchars($client['telephone']); ?>" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-phone me-1"></i>Appeler
                        </a>
                        <?php endif; ?>
                        <a href="../projets/list.php?client_id=<?php echo $client_id; ?>" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-project-diagram me-1"></i>Voir Projets
                        </a>
                    </div>
                </div>
            </div>

            <!-- Danger Zone -->
            <?php if ($nb_projets == 0): ?>
            <div class="card shadow mb-4 border-danger">
                <div class="card-header py-3 bg-danger text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-exclamation-triangle me-1"></i>Zone Dangereuse
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small">
                        Ce client n'a aucun projet associé. Vous pouvez le supprimer en toute sécurité.
                    </p>
                    <a href="delete.php?id=<?php echo $client_id; ?>" 
                       class="btn btn-danger btn-sm w-100"
                       onclick="return confirmDelete('Êtes-vous sûr de vouloir supprimer ce client définitivement ?')">
                        <i class="fas fa-trash me-1"></i>Supprimer le Client
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Validation en temps réel
document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.getElementById('email');
    const nomInput = document.getElementById('nom');
    const originalEmail = '<?php echo addslashes($client['email']); ?>';
    
    // Validation email en temps réel
    emailInput.addEventListener('blur', function() {
        if (this.value && this.value !== originalEmail) {
            // Simulation de vérification AJAX
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