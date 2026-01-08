<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'directeur') {
    header("Location: ../auth/login.php"); exit();
}

$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    die("POST REÇU : " . htmlspecialchars(json_encode($_POST)));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'sign') {
    $pv_id = (int)($_POST['pv_id'] ?? 0);
   } if ($pv_id <= 0) {
        $error = "PV invalide.";
    } else {
        // On signe "logiquement" : hash basé sur (pv_id + soutenance_id + date)
       $stmt = $pdo->prepare("SELECT id, soutenance_id, statut FROM pv WHERE id = ?");
        $stmt->execute([$pv_id]);
        $pv = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pv) {
            $error = "PV introuvable.";
        } elseif ($pv['statut'] === 'pv_signe') {
            $error = "PV déjà signé.";
        } else {
           $payload = $pv['id'] . '|' . $pv['soutenance_id'] . '|' . date('c');
            $hash = hash('sha256', $payload);

            $up = $pdo->prepare("UPDATE pv SET statut='pv_signe', signature_hash=?, signed_at=NOW() WHERE id=?");
            $up->execute([$hash, $pv_id]);
            if ($up->rowCount() === 0) {
    $error = "DEBUG: UPDATE a touché 0 ligne. pv_id=" . $pv_id;
} else {

            $success = "PV signé avec succès. Hash: $hash";
        }
    }
}

$stmt = $pdo->query("SELECT id, soutenance_id, statut, signature_hash, signed_at
                     FROM pv
                     ORDER BY created_at DESC");
$pvs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Directeur - Signatures PV</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">
  <h1 class="h4 mb-3">Signature électronique des PV</h1>

  <?php if($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

  <div class="card">
    <div class="card-header">PV disponibles</div>
    <div class="table-responsive">
      <table class="table table-striped mb-0">
        <thead><tr>
          <th>ID</th><th>Soutenance</th><th>Statut</th><th>Hash</th><th>Signé le</th><th>Action</th>
        </tr></thead>
        <tbody>
        <?php foreach($pvs as $pv): ?>
          <tr>
            <td><?= (int)$pv['id'] ?></td>
            <td><?= htmlspecialchars($pv['soutenance_id']) ?></td>
            <td><span class="badge <?= $pv['statut']==='pv_signe'?'bg-success':'bg-secondary' ?>">
              <?= htmlspecialchars($pv['statut']) ?>
            </span></td>
            <td class="small"><?= htmlspecialchars($pv['signature_hash'] ?? '-') ?></td>
            <td><?= htmlspecialchars($pv['signed_at'] ?? '-') ?></td>
            <td>
              <?php if($pv['statut'] !== 'pv_signe'): ?>
                <form method="post" class="d-inline">
                  <input type="hidden" name="action" value="sign">
                  <input type="hidden" name="pv_id" value="<?= (int)$pv['id'] ?>">
                  <button class="btn btn-sm btn-primary">Signer</button>
                </form>
              <?php else: ?>
                <span class="text-muted">—</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <a class="btn btn-link mt-3" href="index.php">← Retour</a>
</body>
</html>

