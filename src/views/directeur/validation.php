<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'directeur') {
    header("Location: ../auth/login.php");
    exit();
}

$success = '';
$error = '';
$directeur_id = (int)($_SESSION['user_id'] ?? 0);

/**
 * Règles :
 * - Approuver => projets.statut = 'valide'
 * - Demander correction => projets.statut = 'correction_demandee' + insert demandes_correction
 * - Publier => soutenances.statut = 'publie' + générer pv si absent
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'approve') {
            $projet_id = (int)($_POST['projet_id'] ?? 0);
            if ($projet_id <= 0) throw new Exception("Projet invalide.");

            $pdo->prepare("UPDATE projets SET statut='valide' WHERE id=?")->execute([$projet_id]);
            $success = "Projet approuvé.";

        } elseif ($action === 'correction') {
            $projet_id = (int)($_POST['projet_id'] ?? 0);
            $msg = trim($_POST['message'] ?? '');

            if ($projet_id <= 0) throw new Exception("Projet invalide.");
            if ($msg === '') throw new Exception("Message obligatoire.");

            $pdo->beginTransaction();

            $pdo->prepare("UPDATE projets SET statut='correction_demandee' WHERE id=?")->execute([$projet_id]);

            $pdo->prepare("INSERT INTO demandes_correction (directeur_id, projet_id, message)
                           VALUES (?, ?, ?)")
                ->execute([$directeur_id, $projet_id, $msg]);

            $pdo->commit();
            $success = "Demande de correction envoyée.";

        } elseif ($action === 'publish') {
            $soutenance_id = (int)($_POST['soutenance_id'] ?? 0);
            if ($soutenance_id <= 0) throw new Exception("Soutenance invalide.");

            $pdo->beginTransaction();

            $pdo->prepare("UPDATE soutenances SET statut='publie' WHERE id=?")->execute([$soutenance_id]);

            // Générer PV si absent
            $pdo->prepare("INSERT INTO pv (soutenance_id)
                           SELECT ? FROM DUAL
                           WHERE NOT EXISTS (SELECT 1 FROM pv WHERE soutenance_id=?)")
                ->execute([$soutenance_id, $soutenance_id]);

            $pdo->commit();
            $success = "Soutenance publiée (PV généré si nécessaire).";

        } else {
            throw new Exception("Action invalide.");
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = $e->getMessage();
    }
}

// Liste : projets + soutenance (si existe) + PV (si existe)
$sql = "
SELECT
    p.id AS projet_id,
    p.titre,
    p.statut AS statut_projet,
    p.encadrant_id,
    s.id AS soutenance_id,
    s.date_soutenance,
    s.salle,
    s.statut AS statut_soutenance,
    pv.id AS pv_id,
    pv.statut AS statut_pv
FROM projets p
LEFT JOIN soutenances s ON s.projet_id = p.id
LEFT JOIN pv ON pv.soutenance_id = s.id
ORDER BY p.created_at DESC, p.id DESC
";
$rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Directeur - Validation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">

<h1 class="h4 mb-3">Validation (Directeur)</h1>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<div class="card">
    <div class="table-responsive">
        <table class="table table-striped mb-0 align-middle">
            <thead>
            <tr>
                <th>Projet</th>
                <th>Statut projet</th>
                <th>Soutenance</th>
                <th>Statut soutenance</th>
                <th>PV</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Aucun projet</td></tr>
            <?php else: ?>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td>
                            <div class="fw-bold">#<?= (int)$r['projet_id'] ?> — <?= htmlspecialchars($r['titre'] ?? '-') ?></div>
                            <div class="text-muted small">
                                Encadrant: <?= $r['encadrant_id'] ? ('#'.(int)$r['encadrant_id']) : '—' ?>
                            </div>
                        </td>

                        <td>
                            <span class="badge bg-secondary"><?= htmlspecialchars($r['statut_projet'] ?? '-') ?></span>
                        </td>

                        <td>
                            <?php if (!empty($r['soutenance_id'])): ?>
                                #<?= (int)$r['soutenance_id'] ?><br>
                                <span class="text-muted small"><?= htmlspecialchars($r['date_soutenance'] ?? '-') ?> | <?= htmlspecialchars($r['salle'] ?? '-') ?></span>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>

                        <td>
                            <span class="badge bg-info text-dark"><?= htmlspecialchars($r['statut_soutenance'] ?? '-') ?></span>
                        </td>

                        <td>
                            <?php if (!empty($r['pv_id'])): ?>
                                <span class="badge <?= ($r['statut_pv']==='pv_signe') ? 'bg-success' : 'bg-warning text-dark' ?>">
                                    <?= htmlspecialchars($r['statut_pv']) ?>
                                </span>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>

                        <td class="d-flex gap-2 flex-wrap">
                            <!-- Approuver -->
                            <form method="post">
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="projet_id" value="<?= (int)$r['projet_id'] ?>">
                                <button class="btn btn-sm btn-success">Approuver</button>
                            </form>

                            <!-- Publier (si soutenance existe) -->
                            <?php if (!empty($r['soutenance_id'])): ?>
                                <form method="post">
                                    <input type="hidden" name="action" value="publish">
                                    <input type="hidden" name="soutenance_id" value="<?= (int)$r['soutenance_id'] ?>">
                                    <button class="btn btn-sm btn-primary">Publier</button>
                                </form>
                            <?php endif; ?>

                            <!-- Toggle correction -->
                            <button class="btn btn-sm btn-warning" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#corr<?= (int)$r['projet_id'] ?>">
                                Demander correction
                            </button>
                        </td>
                    </tr>

                    <tr class="collapse" id="corr<?= (int)$r['projet_id'] ?>">
                        <td colspan="6">
                            <form method="post" class="mt-2">
                                <input type="hidden" name="action" value="correction">
                                <input type="hidden" name="projet_id" value="<?= (int)$r['projet_id'] ?>">
                                <div class="mb-2">
                                    <textarea name="message" class="form-control" rows="3" required
                                              placeholder="Expliquez précisément la correction demandée..."></textarea>
                                </div>
                                <button class="btn btn-sm btn-warning">Envoyer</button>
                            </form>
                        </td>
                    </tr>

                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<a class="btn btn-link mt-3" href="index.php">← Retour</a>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
