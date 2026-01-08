<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'directeur') {
  header("Location: ../auth/login.php"); exit();
}

$success = '';
$error = '';

// Charger paramètres existants en tableau clé=>valeur
$stmt = $pdo->query("SELECT cle, valeur FROM parametres");
$params = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
  $params[$row['cle']] = $row['valeur'];
}

$date_limite_rapport = $params['date_limite_rapport'] ?? '';
$duree_soutenance_min = $params['duree_soutenance_min'] ?? '';

// Sauvegarde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $newDate = trim($_POST['date_limite_rapport'] ?? '');
  $newDuree = trim($_POST['duree_soutenance_min'] ?? '');

  if ($newDate === '' || $newDuree === '') {
    $error = "Tous les champs sont obligatoires.";
  } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $newDate)) {
    $error = "Format de date invalide (YYYY-MM-DD).";
  } elseif (!ctype_digit($newDuree) || (int)$newDuree <= 0) {
    $error = "Durée invalide (nombre entier > 0).";
  } else {
    $pdo->beginTransaction();
    try {
      $up = $pdo->prepare("UPDATE parametres SET valeur=?, updated_at=NOW() WHERE cle=?");

      $up->execute([$newDate, 'date_limite_rapport']);
      $up->execute([(string)(int)$newDuree, 'duree_soutenance_min']);

      $pdo->commit();
      $success = "Paramètres enregistrés avec succès.";

      $date_limite_rapport = $newDate;
      $duree_soutenance_min = (string)(int)$newDuree;
    } catch (Exception $e) {
      $pdo->rollBack();
      $error = "Erreur sauvegarde paramètres.";
    }
  }
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
  <h1 class="h4 mb-3">Paramètres (Directeur)</h1>

  <?php if($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

  <form method="post" class="card p-3">
    <div class="mb-3">
      <label class="form-label fw-bold">Date limite dépôt rapport</label>
      <input type="date" name="date_limite_rapport" class="form-control" value="<?= htmlspecialchars($date_limite_rapport) ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label fw-bold">Durée soutenance (minutes)</label>
      <input type="number" name="duree_soutenance_min" class="form-control" min="1" value="<?= htmlspecialchars($duree_soutenance_min) ?>" required>
    </div>

    <button class="btn btn-primary">Enregistrer</button>
  </form>

  <a class="btn btn-link mt-3" href="index.php">← Retour</a>
</body>
</html>
