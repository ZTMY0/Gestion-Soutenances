<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinateur') {
    header("Location: ../auth/login.php");
    exit();
}

// Stats pour les cartes
$nbAttente = $pdo->query("SELECT COUNT(*) FROM projets WHERE statut = 'inscrit'")->fetchColumn();
$nbValides = $pdo->query("SELECT COUNT(*) FROM projets WHERE statut = 'valide_encadrant'")->fetchColumn();

// Stats pour le graphique (Etudiants par filière)
$sqlStats = "SELECT f.code, COUNT(u.id) as total 
             FROM users u 
             JOIN filieres f ON u.filiere_id = f.id 
             WHERE u.role = 'etudiant' 
             GROUP BY f.code";
$stmtStats = $pdo->query($sqlStats);
$labels = []; $dataCount = [];
while($row = $stmtStats->fetch()) {
    $labels[] = $row['code'];
    $dataCount[] = $row['total'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Dashboard Coordinateur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
    <?php 
    // FIX SÉCURISÉ POUR LA NAVBAR
    $dir = __DIR__ . '/../';
    $navbar = file_exists($dir . 'layout/navbar_coordinateur.php') 
              ? $dir . 'layout/navbar_coordinateur.php' 
              : $dir . 'layouts/navbar_coordinateur.php';
    include $navbar; 
    ?>

    <div class="container mt-5">
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-warning text-dark shadow border-0 h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-clock fa-2x mb-2"></i>
                        <h5>Projets en attente</h5>
                        <h2 class="display-4 fw-bold"><?php echo $nbAttente; ?></h2>
                        <a href="projets.php" class="btn btn-sm btn-dark mt-2">Gérer les projets</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white shadow border-0 h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <h5>Projets Validés</h5>
                        <h2 class="display-4 fw-bold"><?php echo $nbValides; ?></h2>
                        <p class="mb-0">Prêts pour le planning</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white shadow border-0 h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-users-cog fa-2x mb-2"></i>
                        <h5>Administration</h5>
                        <p>Importation des données</p>
                        <div class="d-grid gap-2">
                            <a href="import_etudiants.php" class="btn btn-light btn-sm fw-bold">Étudiants</a>
                            <a href="import_profs.php" class="btn btn-dark btn-sm fw-bold">Professeurs</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow p-4 border-0 mb-5">
            <h5 class="text-center mb-4"><i class="fas fa-chart-pie me-2"></i>Effectifs EIDIA par Filière</h5>
            <div style="height: 350px;">
                <canvas id="myChart"></canvas>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('myChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($dataCount); ?>,
                    backgroundColor: ['#0d6efd', '#6610f2', '#6f42c1', '#d63384', '#dc3545', '#fd7e14']
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    </script>
</body>
</html>