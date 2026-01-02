<?php
session_start();
require_once '../../../config/database.php';

// 1. SECURITE : Seul un étudiant peut voir cette page
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'etudiant') {
    // Si c'est un prof ou admin qui clique, on le renvoie chez lui
    header("Location: ../auth/login.php");
    exit();
}

// Traitement du formulaire
if (isset($_POST['submit_projet'])) {
    // 2. CORRECTION : On récupère les données BRUTES (sans htmlspecialchars ici)
    // C'est MySQL qui gère la sécurité via prepare(), et l'affichage via htmlspecialchars() plus tard.
    $titre = $_POST['titre'];
    $desc = $_POST['description'];
    
    $etudiant_id = $_SESSION['user_id'];
    $filiere_id = $_SESSION['filiere_id'] ?? 1; // Utilise la filière de la session si dispo
    $annee = "2025-2026";

    $stmt = $pdo->prepare("INSERT INTO projets (titre, description, etudiant_id, filiere_id, annee_universitaire, statut) VALUES (?, ?, ?, ?, ?, 'inscrit')");
    $stmt->execute([$titre, $desc, $etudiant_id, $filiere_id, $annee]);

    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Soumettre un sujet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <div class="card shadow p-4 mx-auto" style="max-width: 600px;">
        <h3 class="mb-4"> Nouveau Sujet PFE</h3>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Titre du projet</label>
                <input type="text" name="titre" class="form-control" required placeholder="Ex: Application de gestion...">
            </div>
            <div class="mb-3">
                <label class="form-label">Description détaillée</label>
                <textarea name="description" class="form-control" rows="5" required placeholder="Décrivez les technologies et l'objectif..."></textarea>
            </div>
            <button type="submit" name="submit_projet" class="btn btn-success w-100">Envoyer pour validation</button>
            <a href="index.php" class="btn btn-link w-100 mt-2">Annuler</a>
        </form>
    </div>
</body>
</html>