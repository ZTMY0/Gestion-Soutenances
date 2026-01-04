<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'directeur') {
    header("Location: ../auth/login.php"); exit();
}

// Exemple de récupération de vrais PV (si la table existait)
// $pvs = $pdo->query("SELECT * FROM pvs WHERE statut = 'attente_signature'")->fetchAll();
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Directeur - Signatures PV</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light p-5">
  <div class="container bg-white p-5 rounded shadow-sm">
      <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <h1 class="h3 mb-0 text-success"><i class="fas fa-file-signature me-2"></i>Signature électronique des PV</h1>
        <a class="btn btn-secondary btn-sm" href="index.php"><i class="fas fa-arrow-left me-2"></i>Retour</a>
      </div>

      <div class="alert alert-warning">
        <i class="fas fa-lock me-2"></i>Les documents signés seront verrouillés et archivés définitivement.
      </div>

      <table class="table table-hover align-middle mt-4">
        <thead class="table-light">
            <tr>
                <th>Projet / Étudiant</th>
                <th>Date Soutenance</th>
                <th>Note Finale</th>
                <th>Statut PV</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
          <tr>
            <td>
                <strong>Système de détection d'intrusions</strong><br>
                <small class="text-muted">Zouhair Reda</small>
            </td>
            <td>10/06/2026</td>
            <td>18/20</td>
            <td><span class="badge bg-warning text-dark">En attente signature</span></td>
            <td><button class="btn btn-sm btn-success"><i class="fas fa-pen-nib me-2"></i>Signer</button></td>
          </tr>
          </tbody>
      </table>
  </div>
</body>
</html>