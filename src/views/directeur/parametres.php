<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'directeur') {
    header("Location: ../auth/login.php");
    exit();
}
?>
<h1>Paramètres système</h1>

<ul>
  <li>⏱ Durée standard soutenance : 60 min</li>
  <li>📅 Date limite de dépôt</li>
  <li>👤 Gestion des comptes coordinateurs</li>
</ul>
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
  <title>Directeur - Paramètres</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Paramètres & Gestion des comptes</h1>
    <a class="btn btn-outline-secondary btn-sm" href="index.php">Retour</a>
  </div>

  <div class="alert alert-info">
    Configuration des règles métier : durée soutenance, dates limites, activation/désactivation coordinateurs.
  </div>

  <form class="row g-3" method="post">
    <div class="col-md-4">
      <label class="form-label">Durée standard soutenance (min)</label>
      <input class="form-control" type="number" value="60" disabled>
    </div>
    <div class="col-md-4">
      <label class="form-label">Date limite dépôt rapport</label>
      <input class="form-control" type="date" disabled>
    </div>
    <div class="col-12">
      <button class="btn btn-primary" disabled>Enregistrer</button>
    </div>
  </form>

  <p class="text-muted mt-3 mb-0">(Simulation UI — à relier à une table settings.)</p>
</body>
</html>
