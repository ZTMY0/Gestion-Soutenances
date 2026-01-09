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

// RÉCUPÉRATION DES PROFS
try {
    $profs = $pdo->query("SELECT id, nom, prenom, specialite FROM users WHERE role = 'prof' ORDER BY nom ASC")->fetchAll();
} catch (PDOException $e) {
    $profs = [];
}

// TRAITEMENT DU FORMULAIRE
if (isset($_POST['submit_projet'])) {
    try {
        $sql = "INSERT INTO projets (titre, description, domaine, technologies, binome_email, etudiant_id, filiere_id, encadrant_pref1_id, encadrant_pref2_id, encadrant_pref3_id, statut, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'inscrit', NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['titre'], 
            $_POST['description'], 
            $_POST['domaine'], 
            $_POST['technos'], 
            $_POST['binome'],
            $_SESSION['user_id'], 
            $_SESSION['filiere_id'] ?? 1, 
            $_POST['p1'] ?: null, 
            $_POST['p2'] ?: null, 
            $_POST['p3'] ?: null
        ]);
        
        header("Location: index.php"); exit();
    } catch (PDOException $e) {
        $error = "Erreur lors de l'enregistrement : " . $e->getMessage();
    }
}
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
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-2 mb-5">
        <div class="container">
            <a class="navbar-brand text-uppercase fw-bold" href="index.php">UEMF Espace PFE</a>
            <div class="d-flex align-items-center text-white-50">
                <span class="me-3 small text-uppercase"><i class="fas fa-user-graduate me-2"></i><?php echo $_SESSION['user_nom'] ?? 'Étudiant'; ?></span>
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
                                <div><?= $error ?></div>
                            </div>
                        <?php endif; ?>

                        <div class="alert alert-light border-start border-4 border-primary shadow-sm mb-4">
                            <small class="text-muted">Veuillez remplir ce formulaire avec précision. Votre sujet sera soumis au coordinateur pour validation.</small>
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
                                    <label class="form-label">Domaine <span class="text-danger">*</span></label>
                                    <select name="domaine" class="form-select" required>
                                        <option value="" selected disabled>Choisir...</option>
                                        <option value="IA">Intelligence Artificielle</option>
                                        <option value="Cyber">Cybersécurité</option>
                                        <option value="Dev">Dév. Web & Mobile</option>
                                        <option value="BigData">Big Data & Cloud</option>
                                        <option value="Robotique">Systèmes Embarqués</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description technique <span class="text-danger">*</span></label>
                                <textarea name="description" class="form-control" rows="5" placeholder="Contexte, problématique et objectifs du projet..." required></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Technologies clés <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-muted"><i class="fas fa-code"></i></span>
                                    <input type="text" name="technos" class="form-control" placeholder="Ex: Python, TensorFlow, Docker, React..." required>
                                </div>
                                <div class="form-text">Séparez les technologies par des virgules.</div>
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
                            <p class="small text-muted mb-3">Sélectionnez jusqu'à 3 enseignants. Leurs spécialités sont indiquées entre parenthèses.</p>
                            
                            <div class="row g-3 mb-4">
                                <?php for($i=1; $i<=3; $i++): ?>
                                <div class="col-md-4">
                                    <div class="p-3 border rounded bg-light">
                                        <label class="form-label mb-2 text-primary">Choix n°<?= $i ?></label>
                                        <select name="p<?= $i ?>" class="form-select form-select-sm">
                                            <option value="">-- Aucun --</option>
                                            <?php foreach($profs as $p): 
                                                // CORRECTION DE L'ERREUR : Gestion des valeurs NULL
                                                $nom = strtoupper($p['nom'] ?? '');
                                                $prenom = ucfirst(strtolower($p['prenom'] ?? ''));
                                                $specialite = $p['specialite'] ?? ''; // Évite l'erreur null
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