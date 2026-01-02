<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinateur') {
    header("Location: ../auth/login.php");
    exit();
}

// Requête complexe pour récupérer le planning complet avec les noms
try {
    $sql = "SELECT 
                p.titre AS projet_titre, 
                u_etud.nom AS etudiant_nom,
                u_p1.nom AS president_nom, 
                u_p2.nom AS examinateur_nom,
                j.date_soutenance,
                j.salle
            FROM jury_soutenance j
            JOIN projets p ON j.projet_id = p.id
            JOIN users u_etud ON p.etudiant_id = u_etud.id
            JOIN users u_p1 ON j.prof1_id = u_p1.id
            JOIN users u_p2 ON j.prof2_id = u_p2.id
            ORDER BY j.date_soutenance ASC";
    
    $stmt = $pdo->query($sql);
    $jurys = $stmt->fetchAll();
} catch (PDOException $e) {
    // Si la table n'existe pas encore, on crée un tableau vide pour éviter le crash
    $jurys = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Planning des Jurys</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
    <?php 
    $dir = __DIR__ . '/../';
    $navbar = file_exists($dir . 'layout/navbar_coordinateur.php') ? $dir . 'layout/navbar_coordinateur.php' : $dir . 'layouts/navbar_coordinateur.php';
    include $navbar; 
    ?>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="fas fa-calendar-alt me-2 text-primary"></i>Planning des Soutenances</h3>
            <div>
                <button onclick="window.print()" class="btn btn-outline-secondary btn-sm me-2">
                    <i class="fas fa-print"></i> Imprimer
                </button>
                <a href="index.php" class="btn btn-secondary btn-sm">← Retour</a>
            </div>
        </div>

        <?php if (empty($jurys)): ?>
            <div class="alert alert-info shadow-sm border-0 p-5 text-center">
                <i class="fas fa-robot fa-3x mb-3 text-muted"></i>
                <h4>Aucun jury généré pour le moment</h4>
                <p class="mb-0 text-muted">L'algorithme d'affectation n'a pas encore été lancé ou aucun projet n'est prêt.</p>
            </div>
        <?php else: ?>
            <div class="card shadow border-0">
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Date & Heure</th>
                                <th>Étudiant / Projet</th>
                                <th>Jury (Président / Exam.)</th>
                                <th>Salle</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jurys as $j): ?>
                                <tr>
                                    <td class="fw-bold text-primary">
                                        <?php echo date('d/m/Y H:i', strtotime($j['date_soutenance'])); ?>
                                    </td>
                                    <td>
                                        <span class="fw-bold"><?php echo htmlspecialchars($j['etudiant_nom']); ?></span><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($j['projet_titre']); ?></small>
                                    </td>
                                    <td>
                                        <i class="fas fa-user-tie me-1 text-secondary"></i> <?php echo htmlspecialchars($j['president_nom']); ?><br>
                                        <i class="fas fa-user-check me-1 text-secondary"></i> <?php echo htmlspecialchars($j['examinateur_nom']); ?>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($j['salle']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>