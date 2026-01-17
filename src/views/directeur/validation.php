<?php
// SÉCURITÉ & CONFIGURATION
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
// Utilisation de __DIR__ pour garantir le chemin correct sur le serveur Linux
require_once __DIR__ . '/../../../config/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'directeur') {
    header("Location: ../auth/login.php");
    exit();
}

$success = '';
$error = '';
$directeur_id = (int)($_SESSION['user_id'] ?? 0);

// --- TRAITEMENT DES ACTIONS ---
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
            $pdo->prepare("INSERT INTO demandes_correction (directeur_id, projet_id, message) VALUES (?, ?, ?)")
                ->execute([$directeur_id, $projet_id, $msg]);
            $pdo->commit();
            $success = "Demande de correction envoyée.";

        } elseif ($action === 'publish') {
            $soutenance_id = (int)($_POST['soutenance_id'] ?? 0);
            if ($soutenance_id <= 0) throw new Exception("Soutenance invalide.");

            $pdo->beginTransaction();
            // On met le statut 'publie' (assurez-vous que la colonne existe, voir correctif précédent)
            $pdo->prepare("UPDATE soutenances SET statut='publie' WHERE id=?")->execute([$soutenance_id]);

            // Générer PV si absent
            $pdo->prepare("INSERT INTO pv (soutenance_id) SELECT ? FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pv WHERE soutenance_id=?)")
                ->execute([$soutenance_id, $soutenance_id]);

            $pdo->commit();
            $success = "Soutenance publiée (PV généré).";
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = $e->getMessage();
    }
}

// --- RÉCUPÉRATION DES DONNÉES AVEC LES NOMS ---
$sql = "
SELECT
    p.id AS projet_id,
    p.titre,
    p.statut AS statut_projet,
    p.encadrant_id,
    CONCAT(u.prenom, ' ', u.nom) AS nom_enc,
    s.id AS soutenance_id,
    s.date_soutenance,
    s.salle_id,
    sal.nom AS nom_salle,
    s.statut AS statut_soutenance,
    pv.id AS pv_id,
    pv.statut AS statut_pv
FROM projets p
LEFT JOIN users u ON p.encadrant_id = u.id  -- Jointure pour avoir le nom du prof
LEFT JOIN soutenances s ON s.projet_id = p.id
LEFT JOIN salles sal ON s.salle_id = sal.id -- Add this join
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light py-4">

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 fw-bold text-dark">Validation & Publication</h1>
        <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-2"></i>Retour</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger shadow-sm"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success shadow-sm"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                <tr>
                    <th style="width: 30%;">Projet & Encadrement</th>
                    <th>Statut Projet</th>
                    <th>Soutenance</th>
                    <th>PV</th>
                    <th style="width: 25%;">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-5">Aucun projet à traiter.</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-dark mb-1">
                                    #<?= (int)$r['projet_id'] ?> — <?= htmlspecialchars($r['titre'] ?? 'Sans titre') ?>
                                </div>
                                <div class="text-muted small">
                                    <i class="fas fa-user-tie me-1"></i>
                                    <?php if($r['encadrant_id']): ?>
                                        <span class="text-primary fw-bold">Pr. <?= htmlspecialchars($r['nom_enc']) ?></span>
                                    <?php else: ?>
                                        <span class="text-warning">Non assigné</span>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td>
                                <?php 
                                    $badges = [
                                        'valide' => 'bg-success',
                                        'correction_demandee' => 'bg-warning text-dark',
                                        'inscrit' => 'bg-secondary',
                                        'pret_soutenance' => 'bg-info text-dark'
                                    ];
                                    $bg = $badges[$r['statut_projet']] ?? 'bg-secondary';
                                ?>
                                <span class="badge <?= $bg ?>"><?= htmlspecialchars($r['statut_projet'] ?? '-') ?></span>
                            </td>

                            <td>
                                <?php if (!empty($r['soutenance_id'])): ?>
                                    <div class="small fw-bold">
                                        <?= date('d/m/Y H:i', strtotime($r['date_soutenance'])) ?>
                                    </div>
                                    <div class="text-muted small">Salle <?= htmlspecialchars($r['nom_salle']) ?></div>
                                    <?php if($r['statut_soutenance'] == 'publie'): ?>
                                        <span class="badge bg-primary proper-badge mt-1" style="font-size: 0.7rem;">Publiée</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark mt-1" style="font-size: 0.7rem;">Prévue</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if (!empty($r['pv_id'])): ?>
                                    <?php if($r['statut_pv'] === 'pv_signe'): ?>
                                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Signé</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark border">En attente</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="d-flex gap-2 flex-column">
                                    <?php if($r['statut_projet'] !== 'valide'): ?>
                                    <div class="btn-group btn-group-sm">
                                        <form method="post">
                                            <input type="hidden" name="action" value="approve">
                                            <input type="hidden" name="projet_id" value="<?= $r['projet_id'] ?>">
                                            <button class="btn btn-outline-success" title="Valider le projet"><i class="fas fa-check"></i></button>
                                        </form>
                                        <button class="btn btn-outline-warning" type="button" data-bs-toggle="collapse" data-bs-target="#corr<?= $r['projet_id'] ?>" title="Demander correction">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                    <?php endif; ?>

                                    <?php if (!empty($r['soutenance_id']) && $r['statut_soutenance'] !== 'publie'): ?>
                                        <form method="post">
                                            <input type="hidden" name="action" value="publish">
                                            <input type="hidden" name="soutenance_id" value="<?= $r['soutenance_id'] ?>">
                                            <button class="btn btn-primary btn-sm w-100"><i class="fas fa-bullhorn me-2"></i>Publier</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>

                        <tr class="collapse bg-light" id="corr<?= $r['projet_id'] ?>">
                            <td colspan="5" class="p-3">
                                <form method="post" class="d-flex gap-2">
                                    <input type="hidden" name="action" value="correction">
                                    <input type="hidden" name="projet_id" value="<?= $r['projet_id'] ?>">
                                    <textarea name="message" class="form-control form-control-sm" rows="1" required placeholder="Motif de la correction..."></textarea>
                                    <button class="btn btn-sm btn-warning text-nowrap">Envoyer</button>
                                </form>
                            </td>
                        </tr>

                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>