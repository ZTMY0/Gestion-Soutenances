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
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM jury_soutenance WHERE prof_id = ?");
    $stmt->execute([$prof_id]);
    $nbJurys = $stmt->fetchColumn();
    
    // Par rôle
    $stmt = $pdo->prepare("SELECT role, COUNT(*) as total FROM jury_soutenance WHERE prof_id = ? GROUP BY role");
    $stmt->execute([$prof_id]);
    $juryStats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Moyenne des notes données
    $stmt = $pdo->prepare("SELECT AVG(note) FROM jury_soutenance WHERE prof_id = ? AND note IS NOT NULL");
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
                           JOIN jury_soutenance js ON js.soutenance_id = s.id
                           WHERE js.prof_id = ? AND YEAR(s.date_soutenance) = YEAR(CURDATE())
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
    <title>Mes Statistiques - Espace Professeur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark px-4 shadow-sm mb-4">
        <div class="d-flex align-items-center">
            <span class="navbar-brand mb-0 h1">
                <i class="fas fa-chalkboard-teacher me-2"></i>Espace Professeur
            </span>
        </div>
        <div class="d-flex align-items-center">
            <span class="text-white me-3 d-none d-md-block">
                <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['user_nom']); ?>
            </span>
            <a href="../auth/logout.php" class="btn btn-outline-light btn-sm">
                <i class="fas fa-sign-out-alt me-1"></i>Déconnexion
            </a>
        </div>
    </nav>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="fas fa-chart-bar text-primary me-2"></i>Mes Statistiques</h2>
                <p class="text-muted mb-0">Vue d'ensemble de votre activité</p>
            </div>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Retour
            </a>
        </div>

        <!-- Stats principales -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-user-graduate fa-2x mb-2"></i>
                        <h2 class="mb-0"><?= $nbEncadres ?></h2>
                        <small>Projets encadrés</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <h2 class="mb-0"><?= $nbValides ?></h2>
                        <small>Validés</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-gavel fa-2x mb-2"></i>
                        <h2 class="mb-0"><?= $nbJurys ?></h2>
                        <small>Participations jury</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-check fa-2x mb-2"></i>
                        <h2 class="mb-0"><?= $nbDispos ?></h2>
                        <small>Créneaux disponibles</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Encadrement par filière -->
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-graduation-cap text-primary me-2"></i>Encadrement par filière</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($parFiliere)): ?>
                            <canvas id="filiereChart" height="200"></canvas>
                        <?php else: ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-chart-pie fa-3x mb-3 opacity-50"></i>
                                <p>Aucun projet encadré pour le moment</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Rôles dans les jurys -->
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-users text-info me-2"></i>Rôles dans les jurys</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($juryStats)): ?>
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="border rounded p-3">
                                        <i class="fas fa-crown text-danger fa-2x mb-2"></i>
                                        <h3 class="mb-0"><?= $juryStats['president'] ?? 0 ?></h3>
                                        <small class="text-muted">Président</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border rounded p-3">
                                        <i class="fas fa-file-alt text-success fa-2x mb-2"></i>
                                        <h3 class="mb-0"><?= $juryStats['rapporteur'] ?? 0 ?></h3>
                                        <small class="text-muted">Rapporteur</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border rounded p-3">
                                        <i class="fas fa-search text-primary fa-2x mb-2"></i>
                                        <h3 class="mb-0"><?= $juryStats['examinateur'] ?? 0 ?></h3>
                                        <small class="text-muted">Examinateur</small>
                                    </div>
                                </div>
                            </div>
                            <?php if ($moyenneNotes): ?>
                            <div class="text-center mt-4">
                                <p class="text-muted mb-1">Moyenne des notes données</p>
                                <span class="badge bg-success fs-4"><?= number_format($moyenneNotes, 1) ?>/20</span>
                            </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-gavel fa-3x mb-3 opacity-50"></i>
                                <p>Aucune participation aux jurys</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphique activité annuelle -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-chart-line text-success me-2"></i>Activité jury <?= date('Y') ?></h5>
            </div>
            <div class="card-body">
                <canvas id="activityChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <script>
        // Graphique filières
        <?php if (!empty($parFiliere)): ?>
        new Chart(document.getElementById('filiereChart'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_column($parFiliere, 'code')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($parFiliere, 'total')) ?>,
                    backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#fd7e14']
                }]
            },
            options: {
                plugins: { legend: { position: 'bottom' } }
            }
        });
        <?php endif; ?>

        // Graphique activité
        new Chart(document.getElementById('activityChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($moisLabels) ?>,
                datasets: [{
                    label: 'Soutenances',
                    data: <?= json_encode($moisData) ?>,
                    backgroundColor: '#0d6efd'
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
