<?php
session_start();
require_once '../../../config/database.php';
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'directeur') {
    header("Location: ../auth/login.php"); exit();
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Directeur - Validation planning</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Validation Stratégique du Planning</h1>
    <a class="btn btn-outline-secondary btn-sm" href="index.php">Retour</a>
  </div>

  <div class="alert alert-warning">
    Vue globale du planning des soutenances. Le Directeur peut approuver ou demander correction.
  </div>

  <div class="d-flex gap-2">
    <button class="btn btn-success" disabled>✅ Approuver tout le planning</button>
    <button class="btn btn-outline-danger" disabled>✉️ Demander correction</button>
  </div>

  <p class="text-muted mt-3 mb-0">
    (Simulation UI — l’implémentation back-end sera reliée à la table soutenances / statuts.)
  </p>
</body>
</html>
