<?php
// Vérification de session sécurisée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../../config/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'directeur') {
    // header("Location: ../auth/login.php"); exit(); // Décommenter en prod
}

// --- 1. RÉCUPÉRATION DES VRAIES DONNÉES ---

// A. Compter le total des soutenances programmées
$totalSoutenances = $pdo->query("SELECT COUNT(*) FROM soutenances")->fetchColumn();

// B. Récupérer les stats par Filière (Réel)
// On suppose que la table 'filieres' a une colonne 'nom' ou 'code'. Adapte si besoin.
$sqlStats = "SELECT f.code, COUNT(s.id) as total, GROUP_CONCAT(DISTINCT s.salle SEPARATOR ', ') as salles
             FROM soutenances s
             JOIN projets p ON s.projet_id = p.id
             JOIN filieres f ON p.filiere_id = f.id
             GROUP BY f.id";
$statsFiliere = $pdo->query($sqlStats)->fetchAll();

// Si aucune soutenance n'est programmée, on initialise un tableau vide
if (empty($statsFiliere) && $totalSoutenances == 0) {
    $statsFiliere = []; 
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Directeur - Validation planning</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light p-5">
  <div class="container bg-white p-5 rounded shadow-sm">
      <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <h1 class="h3 mb-0 text-primary"><i class="fas fa-calendar-check me-2"></i>Validation Stratégique du Planning</h1>
        <a class="btn btn-secondary btn-sm" href="index.php"><i class="fas fa-arrow-left me-2"></i>Retour</a>
      </div>

      <div class="alert alert-primary">
        <i class="fas fa-eye me-2"></i>Vue globale du planning des soutenances. Vous pouvez approuver la publication ou demander des corrections.
      </div>

      <div class="card bg-light border-0 p-4 text-center mb-4">
          <h4 class="fw-bold">Planning Session Juin 2026</h4>
          
          <?php if ($totalSoutenances > 0): ?>
              <p class="text-success fw-bold fs-5 mb-2">
                  <i class="fas fa-check-circle me-2"></i><?= $totalSoutenances ?> Soutenance(s) programmée(s)
              </p>
          <?php else: ?>
              <p class="text-danger fw-bold fs-5 mb-2">
                  <i class="fas fa-exclamation-triangle me-2"></i>Aucune soutenance programmée
              </p>
          <?php endif; ?>
          
          <div class="d-flex justify-content-center gap-3 mt-3">
            <button class="btn btn-success btn-lg px-4" <?= $totalSoutenances == 0 ? 'disabled' : '' ?>>
                <i class="fas fa-check-double me-2"></i>Approuver & Publier
            </button>
            <button class="btn btn-outline-danger btn-lg px-4">
                <i class="fas fa-envelope me-2"></i>Demander correction
            </button>
          </div>
      </div>
      
      <h5 class="mt-4 border-bottom pb-2">Aperçu rapide par Filière</h5>
      <table class="table table-hover mt-3">
          <thead class="table-light">
              <tr>
                  <th>Filière</th>
                  <th class="text-center">Nb Soutenances</th>
                  <th>Salles Utilisées</th>
                  <th class="text-end">État</th>
              </tr>
          </thead>
          <tbody>
              <?php if (count($statsFiliere) > 0): ?>
                  <?php foreach ($statsFiliere as $row): ?>
                      <tr>
                          <td class="fw-bold"><?= htmlspecialchars($row['code']) ?></td>
                          <td class="text-center">
                              <span class="badge bg-primary rounded-pill"><?= $row['total'] ?></span>
                          </td>
                          <td><small class="text-muted"><?= htmlspecialchars($row['salles']) ?></small></td>
                          <td class="text-end"><span class="badge bg-success">Prêt</span></td>
                      </tr>
                  <?php endforeach; ?>
              <?php else: ?>
                  <tr>
                      <td colspan="4" class="text-center text-muted py-3">
                          <em>Aucune donnée disponible pour le moment. Les coordinateurs doivent planifier les dates.</em>
                      </td>
                  </tr>
              <?php endif; ?>
          </tbody>
      </table>
  </div>
</body>
</html>