<?php
// SÉCURITÉ & CONFIGURATION
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../config/session_check.php';
require_once __DIR__ . '/../../../config/database.php';

// Vérification du rôle
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'etudiant') {
    header("Location: ../auth/login.php"); exit();
}

// --- 1. LOGIQUE AUTOMATIQUE DU DOMAINE ---
// Récupération de l'ID filière depuis la session
$filiere_id = $_SESSION['filiere_id'] ?? 0;
$domaine_automatique = "Non défini";

// ⚠️ MAPPING CORRIGÉ SELON TES ID ⚠️
$filiere_map = [
    1 => 'Cyber',       // Cybersécurité
    2 => 'AI',          // Intelligence Artificielle
    3 => 'BigData',     // Big Data
    4 => 'Robotique',   // Systèmes Embarqués / Robotique
    5 => 'Fullstack'    // Développement Fullstack
];

// Détection du code domaine
if (array_key_exists($filiere_id, $filiere_map)) {
    $domaine_code = $filiere_map[$filiere_id];
} else {
    $domaine_code = 'Autre'; 
}

// Affichage joli pour l'utilisateur
$display_map = [
    'Cyber' => 'Cybersécurité',
    'AI' => 'Intelligence Artificielle', 
    'BigData' => 'Big Data',
    'Robotique' => 'Robotique',
    'Fullstack' => 'Fullstack',
    'Autre' => 'Non spécifié'
];
$domaine_visuel = $display_map[$domaine_code] ?? $domaine_code;


// --- 2. RÉCUPÉRATION DES PROFS ---
try {
    $profs = $pdo->query("SELECT id, nom, prenom, specialite FROM users WHERE role = 'prof' ORDER BY nom ASC")->fetchAll();
} catch (PDOException $e) {
    $profs = [];
}

// --- 3. TRAITEMENT DU FORMULAIRE ---
if (isset($_POST['submit_projet'])) {
    $binome_id = null;
    if (!empty($_POST['binome'])) {
        $stmt_binome = $pdo->prepare("SELECT id FROM users WHERE email = ? AND role = 'etudiant'");
        $stmt_binome->execute([$_POST['binome']]);
        $binome_user = $stmt_binome->fetch(PDO::FETCH_ASSOC);
        if ($binome_user) {
            $binome_id = $binome_user['id'];
        } else {
            $error = "Erreur : L'email du binôme n'appartient pas à un étudiant enregistré.";
            goto end_submit_logic; // Jump to the end to prevent further processing
        }
    }

    try {
        // Corrected SQL
        $sql = "INSERT INTO projets (titre, description, mots_cles, etudiant_id, binome_id, filiere_id, encadrant_pref1_id, encadrant_pref2_id, encadrant_pref3_id, statut, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'inscrit', NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['titre'],
            $_POST['description'],
            $_POST['technos'], // Mapped from technologies
            $_SESSION['user_id'],
            $binome_id, // Determined above
            $filiere_id,
            $_POST['p1'] ?: null, // encadrant_pref1_id
            $_POST['p2'] ?: null, // encadrant_pref2_id
            $_POST['p3'] ?: null  // encadrant_pref3_id
        ]);
        
        header("Location: index.php"); exit();
    } catch (PDOException $e) {
        $error = "Erreur lors de l'enregistrement : " . $e->getMessage();
    }
}
end_submit_logic:
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Soumettre un sujet | UEMF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .section-title {
            color: #004d99;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
            margin-bottom: 1rem;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 0.5rem;
        }
        .form-label { font-weight: 600; color: #495057; font-size: 0.9rem; }
        .card-header-custom { background-color: #004d99; color: white; }
        .form-control:focus, .form-select:focus { border-color: #004d99; box-shadow: 0 0 0 0.25rem rgba(0, 77, 153, 0.15); }
        .form-control:disabled, .form-control[readonly] {
            background-color: #e9ecef;
            color: #6c757d;
            font-weight: bold;
            cursor: not-allowed;
        }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-2 mb-5">
        <div class="container">
            <a class="navbar-brand text-uppercase fw-bold" href="index.php">UEMF Espace PFE</a>
            <div class="d-flex align-items-center text-white-50">
                <span class="me-3 small text-uppercase"><i class="fas fa-user-graduate me-2"></i><?php echo htmlspecialchars($_SESSION['user_nom'] ?? 'Étudiant'); ?></span>
                <a href="../auth/logout.php" class="text-white"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-lg border-0 rounded-3">
                    
                    <div class="card-header card-header-custom py-3 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-file-signature me-2"></i>Proposition de Sujet PFE</h5>
                        <a href="index.php" class="text-white-50 hover-white" title="Annuler"><i class="fas fa-times fa-lg"></i></a>
                    </div>

                    <div class="card-body p-5">
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger d-flex align-items-center mb-4">
                                <i class="fas fa-exclamation-circle me-3 fa-2x"></i>
                                <div><?= htmlspecialchars($error) ?></div>
                            </div>
                        <?php endif; ?>

                        <div class="alert alert-light border-start border-2 border-primary shadow-sm mb-2">
                            <small class="text-muted">Votre sujet sera soumis au coordinateur pour validation.</small>
                        </div>

                        <form method="POST">
                            
                            <div class="section-title mt-2">1. Détails du Projet</div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-8">
                                    <label class="form-label">Titre du projet <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white text-muted"><i class="fas fa-heading"></i></span>
                                        <input type="text" name="titre" class="form-control" placeholder="Ex: Mise en place d'un SIEM..." required>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Filière</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-network-wired"></i></span>
                                        <input type="text" class="form-control bg-light border-start-0 ps-0" value="<?= htmlspecialchars($domaine_visuel) ?>" readonly disabled>
                                    </div>
                                
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description technique <span class="text-danger">*</span></label>
                                <textarea name="description" class="form-control" rows="5" placeholder="Contexte, problématique et objectifs du projet..." required></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Tech Stack <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-muted"><i class="fas fa-code"></i></span>
                                    <input type="text" name="technos" class="form-control" placeholder="Ex: Python, TensorFlow, Docker, React..." required>
                                </div>
                                <div class="form-text">Séparez par des virgules.</div>
                            </div>

                            <div class="section-title mt-5">2. Binôme (Optionnel)</div>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Email du binôme</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white text-muted"><i class="fas fa-user-friends"></i></span>
                                        <input type="email" name="binome" class="form-control" placeholder="etudiant@ueuromed.org">
                                    </div>
                                    <div class="form-text">Laissez vide si vous travaillez seul(e).</div>
                                </div>
                            </div>

                            <div class="section-title mt-5">3. Préférences d'Encadrement</div>
                            <p class="small text-muted mb-3">Sélectionnez jusqu'à 3 enseignants.</p>
                            
                            <div class="row g-3 mb-4">
                                <?php for($i=1; $i<=3; $i++): ?>
                                <div class="col-md-4">
                                    <div class="p-3 border rounded bg-light">
                                        <label class="form-label mb-2 text-primary">Choix n°<?= $i ?></label>
                                        <select name="p<?= $i ?>" class="form-select form-select-sm">
                                            <option value="">-- Aucun --</option>
                                            <?php foreach($profs as $p): 
                                                $nom = strtoupper($p['nom'] ?? '');
                                                $prenom = ucfirst(strtolower($p['prenom'] ?? ''));
                                                $specialite = $p['specialite'] ?? '';
                                            ?>
                                                <option value="<?= $p['id'] ?>">
                                                    Pr. <?= htmlspecialchars($nom . ' ' . $prenom) ?> 
                                                    <?= $specialite ? '(' . htmlspecialchars($specialite) . ')' : '' ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <?php endfor; ?>
                            </div>

                            <hr class="my-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="index.php" class="btn btn-outline-secondary px-4">
                                    <i class="fas fa-arrow-left me-2"></i>Annuler
                                </a>
                                <button type="submit" name="submit_projet" class="btn btn-primary btn-lg px-5 fw-bold" style="background-color: #004d99; border-color: #004d99;">
                                    <i class="fas fa-paper-plane me-2"></i>Soumettre le sujet
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>