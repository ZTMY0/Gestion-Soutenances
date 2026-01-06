<?php
// CORRECTION : On vérifie si une session existe avant de la démarrer
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../../config/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'directeur') {
    // header("Location: ../auth/login.php"); exit(); // Décommenter en prod
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Directeur - Paramètres</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light p-5">
  <div class="container bg-white p-5 rounded shadow-sm">
      <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <h1 class="h3 mb-0 text-dark"><i class="fas fa-cogs me-2"></i>Paramètres Système</h1>
        <a class="btn btn-secondary btn-sm" href="index.php"><i class="fas fa-arrow-left me-2"></i>Retour</a>
      </div>

      <div class="alert alert-info shadow-sm">
        <i class="fas fa-info-circle me-2"></i>Configuration globale de la plateforme de gestion des PFE.
      </div>

      <form class="row g-4 mt-2" method="post">
        <div class="col-md-6">
          <label class="form-label fw-bold text-secondary">Durée standard soutenance (min)</label>
          <input class="form-control" type="number" value="60">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-bold text-secondary">Date limite dépôt rapport</label>
          <input class="form-control" type="date">
        </div>
        
        <div class="col-12">
            <div class="card p-3 border bg-light">
                <label class="form-label fw-bold mb-2">Options Avancées</label>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="authModif" checked>
                    <label class="form-check-label" for="authModif">Autoriser les coordinateurs à modifier le planning</label>
                </div>
                <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" id="notifEmail">
                    <label class="form-check-label" for="notifEmail">Envoyer notification email automatique aux étudiants</label>
                </div>
            </div>
        </div>

        <div class="col-12 mt-4 text-end">
          <button class="btn btn-primary px-4 shadow-sm"><i class="fas fa-save me-2"></i>Enregistrer les modifications</button>
        </div>
      </form>
  </div>
</body>
</html>