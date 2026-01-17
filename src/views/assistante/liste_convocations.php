<?php
session_start();
// Sécurité Assistante
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'assistante') {
    header("Location: ../auth/login.php");
    exit();
}
// Include database connection
require_once '../../../config/database.php';

$soutenances_planifiees = [];
try {
    $sql = "SELECT s.id, s.date_soutenance, p.titre as projet_titre, u.nom as etudiant_nom, u.prenom as etudiant_prenom, sal.nom as salle_nom
            FROM soutenances s
            JOIN projets p ON s.projet_id = p.id
            JOIN users u ON p.etudiant_id = u.id
            JOIN salles sal ON s.salle_id = sal.id
            WHERE s.statut IN ('planifie', 'publie') -- Only show planned or published soutenances
            ORDER BY s.date_soutenance ASC";
    $stmt = $pdo->query($sql);
    $soutenances_planifiees = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching planned soutenances for assistante: " . $e->getMessage());
    // Optionally display a user-friendly error message
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Liste des Convocations | Assistante</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4 border-bottom">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold text-primary" href="index.php">LOGISTIQUE PFE</a>
            <div class="d-flex align-items-center">
                <span class="me-3 text-muted">Assistante Administrative</span>
                <a href="../auth/logout.php" class="btn btn-sm btn-danger">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-primary"><i class="fas fa-print me-2"></i>Liste des Convocations</h1>
            <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-2"></i>Retour au Dashboard</a>
        </div>

        <div class="card shadow-sm h-100">
            <div class="card-body p-4">
                <p class="text-muted mb-4">Cliquez sur "Générer PDF" pour imprimer la convocation d'une soutenance.</p>
                <?php if (empty($soutenances_planifiees)): ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle me-2"></i>Aucune soutenance planifiée pour le moment.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Date & Heure</th>
                                    <th>Étudiant & Projet</th>
                                    <th>Salle</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($soutenances_planifiees as $soutenance): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?= date('d/m/Y', strtotime($soutenance['date_soutenance'])) ?></div>
                                            <small class="text-muted"><?= date('H:i', strtotime($soutenance['date_soutenance'])) ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($soutenance['etudiant_nom'] . ' ' . $soutenance['etudiant_prenom']) ?></div>
                                            <small class="text-muted text-truncate" style="max-width: 250px; display: block;"><?= htmlspecialchars($soutenance['projet_titre']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($soutenance['salle_nom']) ?></td>
                                        <td class="text-end">
                                            <a href="generer_pdf.php?id=<?= $soutenance['id'] ?>" target="_blank" class="btn btn-primary btn-sm">
                                                <i class="fas fa-file-pdf me-1"></i> Générer PDF
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
