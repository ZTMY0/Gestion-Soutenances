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
  <title>Directeur - Signatures PV</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Signature électronique des PV</h1>
    <a class="btn btn-outline-secondary btn-sm" href="index.php">Retour</a>
  </div>

  <div class="alert alert-info">
    Objectif : signer numériquement les PV (simulation SHA-256) et archiver le PDF signé.
  </div>

  <table class="table table-striped">
    <thead><tr><th>Projet</th><th>Statut PV</th><th>Action</th></tr></thead>
    <tbody>
      <tr>
        <td>—</td>
        <td><span class="badge bg-secondary">pv_genere</span></td>
        <td><button class="btn btn-sm btn-primary" disabled>Signer</button></td>
      </tr>
    </tbody>
  </table>

  <p class="text-muted mb-0">(Simulation UI — la signature sera reliée au stockage PV.)</p>
</body>
</html>
