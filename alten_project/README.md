# Application Web de Gestion Clients /Projets / Documents - ALTEN Maroc

## Description du Projet

Ce projet consiste à développer une interface web interne pour **ALTEN Maroc** afin de gérer efficacement :
- **Clients** et leurs informations
- **Projets** associés aux clients
- **Documents** liés aux projets (PDF, Excel)

### Objectifs
- Interface moderne et intuitive
- CRUD complet pour toutes les entités
- Gestion des fichiers avec upload sécurisé
- Dashboard avec statistiques en temps réel
- Responsive design pour tous les appareils

## Technologies Utilisées

### Frontend
- **HTML5** - Structure sémantique
- **CSS3** - Styles modernes avec animations
- **JavaScript ES6** - Interactivité et validation
- **Bootstrap 5.3** - Framework CSS responsive
- **Font Awesome 6.4** - Icônes
- **Chart.js** - Graphiques interactifs

### Backend
- **PHP 7.4+** - Logique serveur
- **MySQL 8.0** - Base de données relationnelle
- **PDO** - Accès sécurisé aux données

### Outils
- **Git** - Versioning
- **XAMPP/WAMP/Laragon** - Environnement de développement

## Architecture du Projet

alten_project/
│
├── index.php              # Dashboard principal
├── config.php             # Configuration BDD + fonctions utilitaires
├── header.php             # En-tête commun avec navigation
├── footer.php             # Pied de page commun
├── alten_db.sql           # Schema et données de test
│
├── clients/               # Module Gestion Clients
│   ├── list.php             # Liste avec filtres et recherche
│   ├── add.php              # Formulaire d'ajout
│   ├── edit.php             # Formulaire de modification
│   └── delete.php           # Suppression sécurisée
│
├── projets/               # Module Gestion Projets
│   ├── list.php             # Liste avec filtres avancés
│   ├── add.php              # Création de projet
│   ├── edit.php             # Modification avec suivi
│   └── delete.php           # Suppression avec vérifications
│
├── documents/             # Module Gestion Documents
│   ├── list.php             # Liste avec prévisualisation
│   ├── upload.php           # Upload avec drag & drop
│   └── delete.php           # Suppression fichier + BDD
│
├── uploads/               # Stockage des fichiers uploadés
│   └── .gitkeep
│
└── assets/
    ├── css/
    │   └── style.css         # Styles personnalisés
    └── js/
        └── script.js         # JavaScript personnalisé