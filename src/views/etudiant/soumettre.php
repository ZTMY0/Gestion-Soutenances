<?php
session_start();
require_once '../../../config/database.php';

// SÉCURITÉ
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'etudiant') {
    header("Location: ../auth/login.php");
    exit();
}

// Récupérer la liste des profs pour les préférences
$stmt = $pdo->query("SELECT id, nom FROM users WHERE role = 'prof'");
$profs = $stmt->fetchAll();

// TRAITEMENT
if (isset($_POST['submit_projet'])) {
    $titre = trim($_POST['titre']);
    $desc = trim($_POST['description']);
    $technos = trim($_POST['technos']); 
    $binome_email = trim($_POST['binome_email']); 
    
    // Récupération des IDs des profs choisis (ou NULL si vide)
    $pref1 = !empty($_POST['pref1']) ? $_POST['pref1'] : null;
    $pref2 = !empty($_POST['pref2']) ? $_POST['pref2'] : null;
    $pref3 = !empty($_POST['pref3']) ? $_POST['pref3'] : null;

    $etudiant_id = $_SESSION['user_id'];
    $filiere_id = $_SESSION['filiere_id'] ?? 1; // Fallback si non défini
    $annee = "2025-2026"; // À dynamiser plus tard si besoin

    // Requete SQL Mise à jour avec TOUS les champs requis par la spec
    $sql = "INSERT INTO projets (
                titre, description, technologies, binome_email, 
                etudiant_id, filiere_id, annee_universitaire, statut,
                encadrant_pref1_id, encadrant_pref2_id, encadrant_pref3_id
            ) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'inscrit', ?, ?, ?)";
            
    $stmt = $pdo->prepare($sql);
    
    try {
        $stmt->execute([
            $titre, $desc, $technos, $binome_email, 
            $etudiant_id, $filiere_id, $annee, 
            $pref1, $pref2, $pref3
        ]);
        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        $error = "Erreur lors de l'enregistrement : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Soumettre un sujet PFE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light container mt-5 mb-5">
    <div class="card shadow mx-auto" style="max-width: 800px;">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0"><i class="fas fa-file-alt me-2"></i>Nouvelle Fiche PFE</h3>
        </div>
        <div class="card-body p-4">
            
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                
                <h5 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-project-diagram me-2"></i>1. Détails du Projet</h5>
                <div class="mb-3">
                    <label class="form-label fw-bold">Titre du sujet <span class="text-danger">*</span></label>
                    <input type="text" name="titre" class="form-control" required placeholder="Ex: Conception d'une architecture Microservices...">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Description & Objectifs <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control" rows="4" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Mots-clés / Technologies <span class="text-danger">*</span></label>
                    <input type="text" name="technos" class="form-control" required placeholder="Ex: React, Node.js, Docker, AI...">
                    <small class="text-muted">Ces mots-clés aideront à l'affectation automatique de l'encadrant.</small>
                </div>

                <h5 class="text-primary border-bottom pb-2 mt-4 mb-3"><i class="fas fa-user-friends me-2"></i>2. Binôme (Optionnel)</h5>
                <div class="mb-3">
                    <label class="form-label">Email de votre binôme</label>
                    <input type="email" name="binome_email" class="form-control" placeholder="son.email@etu.uemf.ma">
                    <small class="text-muted">Laissez vide si vous réalisez le projet seul(e).</small>
                </div>

                <h5 class="text-primary border-bottom pb-2 mt-4 mb-3"><i class="fas fa-chalkboard-teacher me-2"></i>3. Préférences d'Encadrant (Optionnel)</h5>
                <p class="small text-muted mb-3">Indiquez vos préférences. L'affectation finale dépendra de la charge et des spécialités des professeurs.</p>
                
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Choix n°1</label>
                        <select name="pref1" class="form-select">
                            <option value="">-- Aucun --</option>
                            <?php foreach($profs as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Choix n°2</label>
                        <select name="pref2" class="form-select">
                            <option value="">-- Aucun --</option>
                            <?php foreach($profs as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Choix n°3</label>
                        <select name="pref3" class="form-select">
                            <option value="">-- Aucun --</option>
                            <?php foreach($profs as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <hr class="my-4">
                <div class="d-grid gap-2">
                    <button type="submit" name="submit_projet" class="btn btn-success btn-lg">
                        <i class="fas fa-check-circle me-2"></i> Soumettre le Projet
                    </button>
                    <a href="index.php" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>