<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinateur') {
    header("Location: ../auth/login.php"); exit();
}

// --- DONNÉES ---
$nbAttente = $pdo->query("SELECT COUNT(*) FROM projets WHERE statut = 'inscrit' OR statut = 'rapport_soumis'")->fetchColumn();
$nbPrets = $pdo->query("SELECT COUNT(*) FROM projets p WHERE (p.statut = 'valide' OR p.statut = 'pret_soutenance' OR (p.statut = 'valide_encadrant' AND p.rapport_chemin IS NOT NULL AND p.rapport_chemin != '')) AND p.id NOT IN (SELECT projet_id FROM soutenances)")->fetchColumn();
$nbPlanifiees = $pdo->query("SELECT COUNT(*) FROM soutenances")->fetchColumn();

// Chart & Avancement
$stmtChart = $pdo->query("SELECT f.code, COUNT(p.id) as total FROM filieres f LEFT JOIN projets p ON p.filiere_id = f.id GROUP BY f.id, f.code");
$labels = []; $dataCount = [];
while($row = $stmtChart->fetch()) { $labels[] = $row['code']; $dataCount[] = $row['total']; }

$avancement = $pdo->query("SELECT f.code, COUNT(p.id) as total_projets, SUM(CASE WHEN s.id IS NOT NULL THEN 1 ELSE 0 END) as sout_planifiees FROM filieres f LEFT JOIN projets p ON p.filiere_id = f.id LEFT JOIN soutenances s ON s.projet_id = p.id GROUP BY f.id, f.code")->fetchAll();

$dateDuJour = date('d M Y');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Pilotage PFE | UEMF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-2">
        <div class="container">
            <span class="navbar-text text-white-50">Espace Coordinateur UEMF</span>
            <div class="ms-auto">
                <a href="../auth/logout.php" class="text-white-50 text-decoration-none hover-white"><i class="fas fa-sign-out-alt me-1"></i>Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="dashboard-hero">
        <div class="container">
            <div class="d-flex justify-content-between align-items-end">
                <div>
                    <h2 class="mb-1">Bonjour, Coordinateur</h2>
                    <p class="mb-0">Situation des PFE au <strong><?= $dateDuJour ?></strong>.</p>
                </div>
                <div class="d-none d-md-block">
                    <a href="affectation.php" class="btn btn-outline-light btn-sm me-2 opacity-75"><i class="fas fa-magic me-2"></i>IA Auto-Affectation</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container pb-5">
        
        <div class="row g-4 mb-5">
            
            <div class="col-md-4">
                <a href="projets.php" class="text-decoration-none">
                    <div class="stat-card-modern">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="stat-modern-label">Dossiers reçus</div>
                                <div class="stat-modern-value"><?= $nbAttente ?></div>
                                <div class="stat-badge warning"><i class="fas fa-eye me-1"></i>À valider</div>
                            </div>
                            <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning">
                                <i class="fas fa-inbox fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="planification.php" class="text-decoration-none">
                    <div class="stat-card-modern">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="stat-modern-label">Prêts pour Soutenance</div>
                                <div class="stat-modern-value text-success"><?= $nbPrets ?></div>
                                <div class="stat-badge success"><i class="fas fa-calendar-plus me-1"></i>Fixer une date</div>
                            </div>
                            <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="jurys.php" class="text-decoration-none">
                    <div class="stat-card-modern">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="stat-modern-label">Soutenances Calées</div>
                                <div class="stat-modern-value text-primary"><?= $nbPlanifiees ?></div>
                                <div class="stat-badge primary"><i class="fas fa-gavel me-1"></i>Gérer les Jurys</div>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                                <i class="fas fa-calendar-check fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="row g-4">
            
            <div class="col-lg-8">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-chart-bar me-2 text-primary"></i>État d'avancement par Filière</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted">
                                    <tr>
                                        <th class="ps-4 text-uppercase small border-0">Filière</th>
                                        <th class="text-center text-uppercase small border-0">Volumétrie</th>
                                        <th class="text-center text-uppercase small border-0">Planifiés</th>
                                        <th class="text-end pe-4 text-uppercase small border-0">Progression</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($avancement as $row): 
                                        $percent = ($row['total_projets'] > 0) ? round(($row['sout_planifiees']/$row['total_projets'])*100) : 0;
                                    ?>
                                    <tr>
                                        <td class="ps-4 fw-bold text-dark"><?= $row['code'] ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border"><?= $row['total_projets'] ?> sujets</span>
                                        </td>
                                        <td class="text-center fw-bold text-primary"><?= $row['sout_planifiees'] ?></td>
                                        <td class="pe-4">
                                            <div class="d-flex align-items-center justify-content-end">
                                                <span class="me-3 small fw-bold"><?= $percent ?>%</span>
                                                <div class="progress" style="width: 100px; height: 6px;">
                                                    <div class="progress-bar bg-gradient-primary" style="width: <?= $percent ?>%; background-color: var(--uemf-blue);"></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-chart-pie me-2 text-primary"></i>Répartition</h6>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center p-4">
                        <div style="width: 100%; height: 250px;">
                            <canvas id="myChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5 text-center border-top pt-4">
            <p class="text-muted small mb-3 text-uppercase fw-bold">Administration & Support</p>
            
            <a href="tickets.php" class="btn btn-sm btn-outline-danger me-2 mb-2">
                <i class="fas fa-headset me-1"></i> Tickets Support
                <span class="badge bg-danger rounded-pill">!</span>
            </a>

            <div class="d-inline-block border-start border-end px-3 mx-2 mb-2">
                <a href="gestion_etudiants.php" class="btn btn-sm btn-outline-dark me-1">
                    <i class="fas fa-user-lock me-1"></i> Reset Étudiants
                </a>
                <a href="gestion_profs.php" class="btn btn-sm btn-outline-dark">
                    <i class="fas fa-chalkboard-teacher me-1"></i> Reset Profs
                </a>
            </div>

            <a href="import_etudiants.php" class="btn btn-sm btn-outline-secondary me-1 mb-2">
                <i class="fas fa-file-csv me-1"></i> Import Etu
            </a>
            <a href="import_profs.php" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fas fa-file-csv me-1"></i> Import Prof
            </a>
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
                    backgroundColor: ['#004d99', '#c9a227', '#198754', '#6c757d', '#00264d', '#17a2b8'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, padding: 15, font: { size: 11 } } }
                }
            }
        });
    </script>
</body>
</html>