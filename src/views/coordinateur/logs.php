<?php
// SÉCURITÉ
ini_set('display_errors', 0); // Mode Prod
error_reporting(E_ALL);
session_start();
require_once __DIR__ . '/../../../config/database.php';

// VERIFICATION RÔLE : Coordinateur uniquement
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'coordinateur') {
    header("Location: ../auth/login.php"); exit();
}

// Récupération des logs (les 50 derniers suffisent pour la gestion courante)
$sql = "SELECT logs.*, u.nom, u.prenom, u.role 
        FROM audit_logs logs 
        LEFT JOIN users u ON logs.user_id = u.id 
        ORDER BY logs.created_at DESC 
        LIMIT 50";
$logs = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Historique des Actions | Coordinateur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light py-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="fas fa-history text-primary me-2"></i>Journal d'Activité</h2>
                <p class="text-muted mb-0">Suivi des validations, suppressions et dépôts.</p>
            </div>
            <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Retour</a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th>Date</th>
                            <th>Utilisateur</th>
                            <th>Action</th>
                            <th>Détails</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($logs)): ?>
                            <tr><td colspan="4" class="text-center py-5 text-muted">Rien à signaler.</td></tr>
                        <?php else: ?>
                            <?php foreach($logs as $log): ?>
                                <tr>
                                    <td class="text-nowrap small text-muted">
                                        <?= date('d/m H:i', strtotime($log['created_at'])) ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($log['nom'] . ' ' . $log['prenom']) ?></div>
                                        <span class="badge bg-light text-dark border" style="font-size: 0.7em;"><?= $log['role'] ?></span>
                                    </td>
                                    <td>
                                        <?php 
                                            $color = 'secondary';
                                            if(strpos($log['action'], 'VALIDATION') !== false) $color = 'success';
                                            if(strpos($log['action'], 'SUPPRESSION') !== false) $color = 'danger';
                                            if(strpos($log['action'], 'DEPOT') !== false) $color = 'info';
                                        ?>
                                        <span class="badge bg-<?= $color ?>"><?= htmlspecialchars($log['action']) ?></span>
                                    </td>
                                    <td class="small text-muted">
                                        <?= htmlspecialchars($log['details']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>