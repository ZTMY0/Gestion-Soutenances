<?php
// public/index.php
require_once '../config/database.php';

// Redirection temporaire vers le login (ou une page d'accueil)
// Pour l'instant, on affiche juste des liens pour faciliter le dev de ton équipe
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gestion Soutenances - Dev Index</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <div class="alert alert-success">
         <strong>Système Opérationnel</strong> - Base de données connectée.
    </div>
    <h1>Bienvenue sur le projet</h1>
    <p>Sélectionnez votre espace de travail :</p>
    <div class="list-group">
        <a href="../src/views/auth/login.php" class="list-group-item list-group-item-action"> Page de Connexion (Auth)</a>
        <a href="../src/views/etudiant/" class="list-group-item list-group-item-action"> Espace Étudiant (Nizar)</a>
        <a href="../src/views/prof/" class="list-group-item list-group-item-action"> Espace Professeur (Abdel)</a>
        <a href="../src/views/coordinateur/" class="list-group-item list-group-item-action"> Espace Coordinateur (Ihab)</a>
    </div>
</body>
</html>