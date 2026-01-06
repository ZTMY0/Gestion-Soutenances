<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'prof') {
    header("Location: ../auth/login.php");
    exit();
}

$prof_id = $_SESSION['user_id'];

// Stats encadrement
$stmt = $pdo->prepare("SELECT COUNT(*) FROM projets WHERE encadrant_id = ?");
$stmt->execute([$prof_id]);
$nbEncadres = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM projets WHERE encadrant_id = ? AND statut = 'valide_encadrant'");
$stmt->execute([$prof_id]);
$nbValides = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM projets WHERE encadrant_id = ? AND statut = 'soutenu'");
$stmt->execute([$prof_id]);
$nbSoutenus = $stmt->fetchColumn();

// Stats par filière
$stmt = $pdo->prepare("SELECT f.nom, f.code, COUNT(*) as total 
                       FROM projets p 
                       JOIN filieres f ON p.filiere_id = f.id 
                       WHERE p.encadrant_id = ? 
                       GROUP BY f.id");
$stmt->execute([$prof_id]);
$parFiliere = $stmt->fetchAll();

// Stats jurys
$nbJurys = 0;
$juryStats = [];
$moyenneNotes = null;
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM jurys WHERE prof_id = ?");
    $stmt->execute([$prof_id]);
    $nbJurys = $stmt->fetchColumn();
    
    // Par rôle
    $stmt = $pdo->prepare("SELECT role_jury, COUNT(*) as total FROM jurys WHERE prof_id = ? GROUP BY role_jury");
    $stmt->execute([$prof_id]);
    $juryStats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Moyenne des notes données
    $stmt = $pdo->prepare("SELECT AVG(s.note_finale) 
                           FROM soutenances s
                           JOIN jurys j ON j.soutenance_id = s.id
                           WHERE j.prof_id = ? AND s.note_finale IS NOT NULL");
    $stmt->execute([$prof_id]);
    $moyenneNotes = $stmt->fetchColumn();
} catch (PDOException $e) {
    // Tables n'existent peut-être pas
}

// Disponibilités
$stmt = $pdo->prepare("SELECT COUNT(*) FROM disponibilites_profs WHERE prof_id = ?");
$stmt->execute([$prof_id]);
$nbDispos = $stmt->fetchColumn();

// Données pour graphique par mois (soutenances)
$soutenancesParMois = [];
try {
    $stmt = $pdo->prepare("SELECT MONTH(s.date_soutenance) as mois, COUNT(*) as total 
                           FROM soutenances s
                           JOIN jurys j ON j.soutenance_id = s.id
                           WHERE j.prof_id = ? AND YEAR(s.date_soutenance) = YEAR(CURDATE())
                           GROUP BY MONTH(s.date_soutenance)
                           ORDER BY mois");
    $stmt->execute([$prof_id]);
    $soutenancesParMois = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {}

$moisLabels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
$moisData = [];
for ($i = 1; $i <= 12; $i++) {
    $moisData[] = $soutenancesParMois[$i] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Statistiques - UEMF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../public/assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    
    <!-- NAVBAR HARMONISÉE -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-2">
        <div class="container">
            <a class="navbar-brand text-uppercase fw-bold" href="index.php">
                <i class="fas fa-graduation-cap me-2"></i>UEMF Professeur
            </a>
            <div class="d-flex align-items-center text-white-50">
                <span class="me-3 small">Pr. <?= htmlspecialchars($_SESSION['user_nom']) ?></span>
                <a href="../auth/logout.php" class="btn btn-sm btn-logout">
                    <i class="fas fa-sign-out-alt me-1"></i>
                    <span class="d-none d-md-inline">Déconnexion</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- HERO HARMONISÉ -->
    <div class="dashboard-hero">
        <div class="container">
            <div class="d-flex justify-content-between align-items-end">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-chart-bar me-2"></i>
                        Mes Statistiques
                    </h2>
                    <p class="mb-0 opacity-75">Vue d'ensemble de votre activité académique</p>
                </div>
                <a href="index.php" class="btn btn-sm btn-outline-light d-none d-md-inline">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>
        </div>
    </div>

    <div class="container pb-5">

        <!-- STATS PRINCIPALES -->
        <div class="row g-4 mb-5">
            <div class="col-md-3 col-sm-6">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="stat-number"><?= $nbEncadres ?></div>
                    <div class="stat-label">Projets encadrés</div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-number"><?= $nbValides ?></div>
                    <div class="stat-label">Validés</div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="stat-card">
                    <div class="stat-icon danger">
                        <i class="fas fa-gavel"></i>
                    </div>
                    <div class="stat-number"><?= $nbJurys ?></div>
                    <div class="stat-label">Participations jury</div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-number"><?= $nbDispos ?></div>
                    <div class="stat-label">Créneaux disponibles</div>
                </div>
            </div>
        </div>

        <!-- GRAPHIQUES -->
        <div class="row g-4 mb-5">
            <!-- Encadrement par filière -->
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-0 pt-3">
                        <h6 class="fw-bold mb-0">
                            <i class="fas fa-graduation-cap text-primary me-2"></i>
                            Encadrement par filière
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($parFiliere)): ?>
                            <canvas id="filiereChart" height="200"></canvas>
                        <?php else: ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-chart-pie fa-3x mb-3 opacity-50"></i>
                                <p class="mb-0">Aucun projet encadré pour le moment</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Rôles dans les jurys -->
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-0 pt-3">
                        <h6 class="fw-bold mb-0">
                            <i class="fas fa-users text-info me-2"></i>
                            Rôles dans les jurys
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($juryStats)): ?>
                            <div class="row text-center g-3">
                                <div class="col-4">
                                    <div class="p-3 rounded bg-light">
                                        <i class="fas fa-crown fa-2x text-danger mb-2"></i>
                                        <h4 class="mb-0 fw-bold"><?= $juryStats['president'] ?? 0 ?></h4>
                                        <small class="text-muted fw-semibold">Président</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-3 rounded bg-light">
                                        <i class="fas fa-file-alt fa-2x text-success mb-2"></i>
                                        <h4 class="mb-0 fw-bold"><?= $juryStats['rapporteur'] ?? 0 ?></h4>
                                        <small class="text-muted fw-semibold">Rapporteur</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-3 rounded bg-light">
                                        <i class="fas fa-search fa-2x text-primary mb-2"></i>
                                        <h4 class="mb-0 fw-bold"><?= $juryStats['examinateur'] ?? 0 ?></h4>
                                        <small class="text-muted fw-semibold">Examinateur</small>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($moyenneNotes): ?>
                            <div class="text-center mt-4 p-3 rounded bg-primary text-white">
                                <p class="mb-2 small fw-semibold opacity-75">Moyenne des notes attribuées</p>
                                <div class="badge bg-white text-primary px-4 py-2 fs-5">
                                    <i class="fas fa-star me-1"></i>
                                    <?= number_format($moyenneNotes, 1) ?>/20
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-gavel fa-3x mb-3 opacity-50"></i>
                                <p class="mb-0">Aucune participation aux jurys</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphique activité annuelle -->
        <div class="card mb-5 shadow-sm border-0">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="fw-bold mb-0">
                    <i class="fas fa-chart-line text-success me-2"></i>
                    Activité jury <?= date('Y') ?>
                </h6>
            </div>
            <div class="card-body">
                <canvas id="activityChart" height="80"></canvas>
            </div>
        </div>

        <!-- RÉSUMÉ DÉTAILLÉ -->
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0 pt-3">
                        <h6 class="fw-bold mb-0">
                            <i class="fas fa-clipboard-list text-primary me-2"></i>
                            Résumé Encadrement
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="small">
                                    <i class="fas fa-folder text-primary me-2"></i>
                                    Total projets
                                </span>
                                <strong><?= $nbEncadres ?></strong>
                            </li>
                            <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="small">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    Projets validés
                                </span>
                                <strong><?= $nbValides ?></strong>
                            </li>
                            <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="small">
                                    <i class="fas fa-graduation-cap text-info me-2"></i>
                                    Projets soutenus
                                </span>
                                <strong><?= $nbSoutenus ?></strong>
                            </li>
                            <li class="d-flex justify-content-between align-items-center py-2">
                                <span class="small">
                                    <i class="fas fa-percent text-warning me-2"></i>
                                    Taux de réussite
                                </span>
                                <strong>
                                    <?= $nbEncadres > 0 ? round(($nbSoutenus / $nbEncadres) * 100, 1) : 0 ?>%
                                </strong>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0 pt-3">
                        <h6 class="fw-bold mb-0">
                            <i class="fas fa-trophy text-warning me-2"></i>
                            Performance
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center py-4">
                            <div class="mb-3">
                                <i class="fas fa-medal fa-4x mb-3 text-warning"></i>
                                <h5 class="fw-bold mb-2">Excellent travail !</h5>
                                <p class="text-muted mb-0 small">Vous avez contribué à la formation de <?= $nbEncadres ?> étudiants</p>
                            </div>
                            
                            <?php if($nbJurys > 0): ?>
                            <div class="alert alert-success mb-0 small">
                                <i class="fas fa-star me-2"></i>
                                <?= $nbJurys ?> participation<?= $nbJurys > 1 ? 's' : '' ?> aux jurys de soutenance
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Configuration globale Chart.js
        Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
        Chart.defaults.color = '#64748b';
        
        // Graphique filières (Doughnut)
        <?php if (!empty($parFiliere)): ?>
        new Chart(document.getElementById('filiereChart'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_column($parFiliere, 'code')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($parFiliere, 'total')) ?>,
                    backgroundColor: [
                        '#004d99',
                        '#198754',
                        '#f59e0b',
                        '#ef4444',
                        '#8b5cf6',
                        '#ec4899'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 12,
                            usePointStyle: true,
                            font: { size: 11, weight: '600' }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 10,
                        borderRadius: 4,
                        titleFont: { size: 13, weight: 'bold' },
                        bodyFont: { size: 12 },
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + ' projets';
                            }
                        }
                    }
                }
            }
        });
        <?php endif; ?>

        // Graphique activité (Bar)
        new Chart(document.getElementById('activityChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($moisLabels) ?>,
                datasets: [{
                    label: 'Soutenances',
                    data: <?= json_encode($moisData) ?>,
                    backgroundColor: '#004d99',
                    borderRadius: 4,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 10,
                        borderRadius: 4,
                        titleFont: { size: 13, weight: 'bold' },
                        bodyFont: { size: 12 }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: { size: 11 }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 } }
                    }
                }
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>