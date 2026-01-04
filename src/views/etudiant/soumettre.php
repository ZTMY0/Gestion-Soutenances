<?php
session_start();
require_once '../../../config/database.php';

// Sécurité
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'etudiant') {
    header("Location: ../auth/login.php"); exit();
}

// Récupérer les profs
$profs = $pdo->query("SELECT id, nom, prenom, specialite FROM users WHERE role = 'prof'")->fetchAll();

// Traitement
if (isset($_POST['submit_projet'])) {
    try {
        $sql = "INSERT INTO projets (titre, description, domaine, technologies, binome_email, etudiant_id, filiere_id, encadrant_pref1_id, encadrant_pref2_id, encadrant_pref3_id, statut, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'inscrit', NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['titre'], $_POST['description'], $_POST['domaine'], $_POST['technos'], $_POST['binome'],
            $_SESSION['user_id'], $_SESSION['filiere_id'],
            $_POST['p1'] ?: null, $_POST['p2'] ?: null, $_POST['p3'] ?: null
        ]);
        
        header("Location: index.php"); exit();
    } catch (PDOException $e) {
        $error = "Erreur : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Inscription PFE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light py-5">
    <div class="container">
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white p-4">
                <h3 class="mb-0"><i class="fas fa-file-signature me-2"></i>Inscription au Projet de Fin d'Études</h3>
            </div>
            <div class="card-body p-4">
                <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                
                <form method="POST">
                    <h5 class="text-primary border-bottom pb-2 mb-3">1. Le Projet</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Titre du projet *</label>
                            <input type="text" name="titre" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Domaine Thématique *</label>
                            <select name="domaine" class="form-select" required>
                                <option value="IA">Intelligence Artificielle</option>
                                <option value="Cyber">Cybersécurité</option>
                                <option value="Dev">Développement Web/Mobile</option>
                                <option value="BigData">Big Data</option>
                                <option value="Reseau">Réseaux & Télécoms</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Description détaillée *</label>
                        <textarea name="description" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Mots-clés / Technologies *</label>
                        <input type="text" name="technos" class="form-control" placeholder="Ex: React, Python, Docker..." required>
                    </div>

                    <h5 class="text-primary border-bottom pb-2 mt-4 mb-3">2. Binôme (Optionnel)</h5>
                    <div class="mb-3">
                        <label>Email de l'autre étudiant</label>
                        <input type="email" name="binome" class="form-control" placeholder="etudiant@ueuromed.org">
                    </div>

                    <h5 class="text-primary border-bottom pb-2 mt-4 mb-3">3. Préférences d'Encadrant</h5>
                    <div class="row">
                        <?php for($i=1; $i<=3; $i++): ?>
                        <div class="col-md-4">
                            <label class="small fw-bold">Choix <?= $i ?></label>
                            <select name="p<?= $i ?>" class="form-select">
                                <option value="">-- Aucun --</option>
                                <?php foreach($profs as $p): ?>
                                    <option value="<?= $p['id'] ?>">Pr. <?= $p['nom'] ?> (<?= $p['specialite'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endfor; ?>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" name="submit_projet" class="btn btn-success btn-lg">Valider l'inscription</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>