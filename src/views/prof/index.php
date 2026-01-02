<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'prof') {
    header("Location: ../auth/login.php"); exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Espace Professeur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark px-4">
        <span class="navbar-brand">Espace Prof : <?php echo $_SESSION['user_nom']; ?></span>
        <a href="../../controllers/AuthController.php?logout=1" class="btn btn-danger btn-sm">Déconnexion</a>
    </nav>
    <div class="container mt-5 text-center">
        <h1>Bienvenue, cher collègue</h1>
        <p class="lead">Le module de gestion des jurys et des notes sera bientôt disponible.</p>
        <div class="alert alert-info">Attente de la génération du planning par le coordinateur.</div>
    </div>
</body>
</html>